<?php
if (!defined('ABSPATH')) {
	exit();
}

/**
 * WC Wallee Admin class
 */
class WC_Wallee_Admin_Order_Completion {

	public static function init(){
		add_action('woocommerce_order_item_add_line_buttons', array(
			__CLASS__,
			'render_execute_completion_button' 
		));
		
		add_action('wp_ajax_woocommerce_wallee_execute_completion', array(
			__CLASS__,
			'execute_completion' 
		));
		
		add_action('wallee_five_minutes_cron', array(
			__CLASS__,
			'update_completions' 
		));
		
		add_action('wallee_update_running_jobs', array(
			__CLASS__,
			'update_for_order' 
		));
	}

	public static function render_execute_completion_button(WC_Order $order){
		$gateway = wc_get_payment_gateway_by_order($order);
		if ($gateway instanceof WC_Wallee_Gateway) {
			$transaction_info = WC_Wallee_Entity_Transaction_Info::load_by_order_id($order->get_id());
			if ($transaction_info->get_state() == \Wallee\Sdk\Model\TransactionState::AUTHORIZED) {
				echo '<button type="button" class="button wallee-completion-button action-wallee-completion-cancel" style="display:none">' .
						 __('Cancel', 'woocommerce-wallee') . '</button>';
				echo '<button type="button" class="button button-primary wallee-completion-button action-wallee-completion-execute" style="display:none">' .
						 __('Execute Completion', 'woocommerce-wallee') . '</button>';
				echo '<label for="completion_restock_not_completed_items" style="display:none">' .
						 __('Restock not completed items', 'woocommerce-wallee') . '</label>';
				echo '<input type="checkbox" id="completion_restock_not_completed_items" name="restock_not_completed_items" checked="checked" style="display:none">';
				echo '<label for="refund_amount" style="display:none">' . __('Completion Amount', 'woocommerce-wallee') . '</label>';
			}
		}
	}

	public static function execute_completion(){
		ob_start();
		
		global $wpdb;
		
		check_ajax_referer('order-item', 'security');
		
		if (!current_user_can('edit_shop_orders')) {
			wp_die(-1);
		}
		
		$order_id = absint($_POST['order_id']);
		$order = WC_Order_Factory::get_order($order_id);
		$completion_amount = wc_format_decimal(sanitize_text_field($_POST['completion_amount']), wc_get_price_decimals());
		$line_item_qtys = json_decode(sanitize_text_field(stripslashes($_POST['line_item_qtys'])), true);
		$line_item_totals = json_decode(sanitize_text_field(stripslashes($_POST['line_item_totals'])), true);
		$line_item_tax_totals = json_decode(sanitize_text_field(stripslashes($_POST['line_item_tax_totals'])), true);
		$restock_not_completed_items = 'true' === $_POST['restock_not_completed_items'];
		$current_completion_id = null;
		$transaction_info = null;
		try {
			
			// Prepare line items which we are completed
			$line_items = array();
			$item_ids = array_unique(array_merge(array_keys($line_item_qtys, $line_item_totals)));
			foreach ($item_ids as $item_id) {
				$line_items[$item_id] = array(
					'qty' => 0,
					'completion_total' => 0,
					'completion_tax' => array() 
				);
			}
			foreach ($line_item_qtys as $item_id => $qty) {
				$line_items[$item_id]['qty'] = max($qty, 0);
			}
			foreach ($line_item_totals as $item_id => $total) {
				$line_items[$item_id]['completion_total'] = wc_format_decimal($total);
			}
			foreach ($line_item_tax_totals as $item_id => $tax_totals) {
				$line_items[$item_id]['completion_tax'] = array_filter(array_map('wc_format_decimal', $tax_totals));
			}
			
			foreach($line_items as $item_id => $ignore){
				if(isset($line_items[$item_id]['qty']) && $line_items[$item_id]['qty'] == 0 && $line_items[$item_id]['completion_total'] == 0){
					unset($line_items[$item_id]);
				}
			}
			
			//Validate input first;
			$total_items_sum = 0;
			foreach ($line_items as $item) {
				
				foreach ($item['completion_tax'] as $rate_id => $amount) {
					
					$percent = WC_Tax::get_rate_percent($rate_id);
					$rate = rtrim($percent, '%');
					
					$tax_amount = $item['completion_total'] * $rate / 100;
					if (wc_format_decimal($tax_amount, wc_get_price_decimals()) != wc_format_decimal($amount, wc_get_price_decimals())) {
						throw new Exception(__('The tax rate can not be changed.', 'woocommerce-wallee'));
					}
				}
				$total_items_sum += $item['completion_total'] + array_sum($item['completion_tax']);
			}
			
			if (wc_format_decimal($completion_amount, wc_get_price_decimals()) != wc_format_decimal($total_items_sum, wc_get_price_decimals())) {
				throw new Exception(__('The line item total does not correspond to the total amount to complete.', 'woocommerce-wallee'));
			}
			
			wc_transaction_query("start");
			$transaction_info = WC_Wallee_Entity_Transaction_Info::load_by_order_id($order_id);
			if (!$transaction_info->get_id()) {
				throw new Exception(__('Could not load corresponding wallee transaction'));
			}
			
			WC_Wallee_Helper::instance()->lock_by_transaction_id($transaction_info->get_space_id(), $transaction_info->get_transaction_id());
			$transaction_info = WC_Wallee_Entity_Transaction_Info::load_by_transaction($transaction_info->get_space_id(), 
					$transaction_info->get_transaction_id(), $transaction_info->get_space_id());
			
			if ($transaction_info->get_state() != \Wallee\Sdk\Model\TransactionState::AUTHORIZED) {
				throw new Exception(__('The transaction is not in a state to be completed.', 'woocommerce-wallee'));
			}
			
			if (WC_Wallee_Entity_Completion_Job::count_running_completion_for_transaction($transaction_info->get_space_id(), 
					$transaction_info->get_transaction_id()) > 0) {
				throw new Exception(__('Please wait until the existing completion is processed.', 'woocommerce-wallee'));
			}
			if (WC_Wallee_Entity_Void_Job::count_running_void_for_transaction($transaction_info->get_space_id(), 
					$transaction_info->get_transaction_id()) > 0) {
				throw new Exception(__('There is a void in process. The order can not be completed.', 'woocommerce-wallee'));
			}
			
			$completion_job = new WC_Wallee_Entity_Completion_Job();
			$completion_job->set_items($line_items);
			$completion_job->set_restock($restock_not_completed_items);
			$completion_job->set_space_id($transaction_info->get_space_id());
			$completion_job->set_transaction_id($transaction_info->get_transaction_id());
			$completion_job->set_state(WC_Wallee_Entity_Completion_Job::STATE_CREATED);
			$completion_job->set_order_id($order_id);
			$completion_job->set_amount($completion_amount);
			$completion_job->save();
			$current_completion_id = $completion_job->get_id();
			wc_transaction_query("commit");
		}
		catch (Exception $e) {
			wc_transaction_query("rollback");
			wp_send_json_error(array(
				'error' => $e->getMessage() 
			));
			return;
		}
		
		try {
			self::update_line_items($current_completion_id);
			self::send_completion($current_completion_id);
			
			wp_send_json_success(
					array(
						'message' => __('The completion is updated automatically once the result is available.', 'woocommerce-wallee') 
					));
		}
		catch (Exception $e) {
			wp_send_json_error(array(
				'error' => $e->getMessage() 
			));
		}
	}

	protected static function update_line_items($completion_job_id){
		global $wpdb;
		$completion_job = WC_Wallee_Entity_Completion_Job::load_by_id($completion_job_id);
		wc_transaction_query("start");
		WC_Wallee_Helper::instance()->lock_by_transaction_id($completion_job->get_space_id(), $completion_job->get_transaction_id());
		//Reload void job;
		$completion_job = WC_Wallee_Entity_Completion_Job::load_by_id($completion_job_id);
		
		if ($completion_job->get_state() != WC_Wallee_Entity_Completion_Job::STATE_CREATED) {
			//Already updated in the meantime
			wc_transaction_query("rollback");
			return;
		}
		try {
			$line_items = WC_Wallee_Service_Line_Item::instance()->get_items_from_backend($completion_job->get_items(), $completion_job->get_amount(), 
					WC_Order_Factory::get_order($completion_job->get_order_id()));
			WC_Wallee_Service_Transaction::instance()->update_line_items($completion_job->get_space_id(), $completion_job->get_transaction_id(), 
					$line_items);
			$completion_job->set_state(WC_Wallee_Entity_Completion_Job::STATE_ITEMS_UPDATED);
			$completion_job->save();
			wc_transaction_query("commit");
		}
		catch (Exception $e) {
			$completion_job->set_state(WC_Wallee_Entity_Completion_Job::STATE_DONE);
			$completion_job->save();
			wc_transaction_query("commit");
			throw $e;
		}
	}

	protected static function send_completion($completion_job_id){
		global $wpdb;
		$completion_job = WC_Wallee_Entity_Completion_Job::load_by_id($completion_job_id);
		wc_transaction_query("start");
		WC_Wallee_Helper::instance()->lock_by_transaction_id($completion_job->get_space_id(), $completion_job->get_transaction_id());
		//Reload void job;
		$completion_job = WC_Wallee_Entity_Completion_Job::load_by_id($completion_job_id);
		
		if ($completion_job->get_state() != WC_Wallee_Entity_Completion_Job::STATE_ITEMS_UPDATED) {
			//Already sent in the meantime
			wc_transaction_query("rollback");
			return;
		}
		try {
			$completion_service = new \Wallee\Sdk\Service\TransactionCompletionService(WC_Wallee_Helper::instance()->get_api_client());
			
			$completion = $completion_service->completeOnline($completion_job->get_space_id(), 
					$completion_job->get_transaction_id());
			$completion_job->set_completion_id($completion->getId());
			$completion_job->set_state(WC_Wallee_Entity_Completion_Job::STATE_SENT);
			$completion_job->save();
			wc_transaction_query("commit");
		}
		catch (Exception $e) {
			$completion_job->set_state(WC_Wallee_Entity_Completion_Job::STATE_DONE);
			$completion_job->save();
			wc_transaction_query("commit");
			throw $e;
		}
	}

	public static function update_for_order(WC_Order $order){
		$space_id = $order->get_meta('_wallee_linked_space_id', true);
		$transaction_id = $order->get_meta('_wallee_transaction_id', true);
		
		$completion_job = WC_Wallee_Entity_Completion_Job::load_running_completion_for_transaction($space_id, $transaction_id);
		
		if ($completion_job->get_state() == WC_Wallee_Entity_Completion_Job::STATE_CREATED) {
			self::update_line_items($completion_job->get_id());
			self::send_completion($completion_job->get_id());
		}
		elseif ($completion_job->get_state() == WC_Wallee_Entity_Completion_Job::STATE_ITEMS_UPDATED) {
			self::send_completion($completion_job->get_id());
		}
	}

	public static function update_completions(){
		$to_process = WC_Wallee_Entity_Completion_Job::load_not_sent_job_ids();
		foreach ($to_process as $id) {
			try {
				self::update_line_items($id);
				self::send_completion($id);
			}
			catch (Exception $e) {
				$message = sprintf(__('Error updating completion job with id %d: %s', 'woocommerce-wallee'), $id, $e->getMessage());
				WooCommerce_Wallee::instance()->log($message, WC_Log_Levels::ERROR);
			}
		}
	}
}
WC_Wallee_Admin_Order_Completion::init();