<?php
if (!defined('ABSPATH')) {
	exit();
}

/**
 * Webhook processor to handle transaction state transitions.
 */
class WC_Wallee_Webhook_Transaction extends WC_Wallee_Webhook_Order_Related_Abstract {

	/**
	 *
	 * @see WC_Wallee_Webhook_Order_Related_Abstract::load_entity()
	 * @return \Wallee\Sdk\Model\Transaction
	 */
	protected function load_entity(WC_Wallee_Webhook_Request $request){
		$transaction_service = new \Wallee\Sdk\Service\TransactionService(WC_Wallee_Helper::instance()->get_api_client());
		return $transaction_service->read($request->get_space_id(), $request->get_entity_id());
	}

	protected function get_order_id($transaction){
		/* @var \Wallee\Sdk\Model\Transaction $transaction */
		return $transaction->getMerchantReference();
	}

	protected function get_transaction_id($transaction){
		/* @var \Wallee\Sdk\Model\Transaction $transaction */
		return $transaction->getId();
	}

	protected function process_order_related_inner(WC_Order $order, $transaction){
		/* @var \Wallee\Sdk\Model\Transaction $transaction */
		$transaction_info = WC_Wallee_Entity_Transaction_Info::load_by_order_id($order->get_id());
		if ($transaction->getState() != $transaction_info->get_state()) {
			switch ($transaction->getState()) {
				case \Wallee\Sdk\Model\Transaction::STATE_CONFIRMED:
				case \Wallee\Sdk\Model\Transaction::STATE_PROCESSING:
					
					$this->confirm($transaction, $order);
					break;
				case \Wallee\Sdk\Model\Transaction::STATE_AUTHORIZED:
					$this->authorize($transaction, $order);
					break;
				case \Wallee\Sdk\Model\Transaction::STATE_DECLINE:
					$this->decline($transaction, $order);
					break;
				case \Wallee\Sdk\Model\Transaction::STATE_FAILED:
					$this->failed($transaction, $order);
					break;
				case \Wallee\Sdk\Model\Transaction::STATE_FULFILL:
					if (!$order->get_meta("_wallee_authorized", true)) {
						$this->authorize($transaction, $order);
					}
					$this->fulfill($transaction, $order);
					break;
				case \Wallee\Sdk\Model\Transaction::STATE_VOIDED:
					$this->voided($transaction, $order);
					break;
				case \Wallee\Sdk\Model\Transaction::STATE_COMPLETED:
					$this->waiting($transaction, $order);
					break;
				default:
					// Nothing to do.
					break;
			}
		}
		
		WC_Wallee_Service_Transaction::instance()->update_transaction_info($transaction, $order);
	}

	protected function confirm(\Wallee\Sdk\Model\Transaction $transaction, WC_Order $order){
		$status = apply_filters('wc_wallee_confirmed_status', 'wallee-redirected', $order);
		$order->update_status($status);
		wc_maybe_reduce_stock_levels($order->get_id());
	}

	protected function authorize(\Wallee\Sdk\Model\Transaction $transaction, WC_Order $order){
		$status = apply_filters('wc_wallee_authorized_status', 'on-hold', $order);
		$order->add_meta_data("_wallee_authorized", "true", true);
		$order->update_status($status);
		
		if (isset(WC()->cart)) {
			WC()->cart->empty_cart();
		}
		}

	protected function waiting(\Wallee\Sdk\Model\Transaction $transaction, WC_Order $order){
		if (!$order->get_meta('_wallee_manual_check', true)) {
			$status = apply_filters('wc_wallee_completed_status', 'wallee-waiting', $order);
			$order->update_status($status);
		}
	}

	protected function decline(\Wallee\Sdk\Model\Transaction $transaction, WC_Order $order){
		$status = apply_filters('wc_wallee_decline_status', 'cancelled', $order);
		$order->update_status($status);
		WC_Wallee_Helper::instance()->maybe_restock_items_for_cancelled_order($order);
	}

	protected function failed(\Wallee\Sdk\Model\Transaction $transaction, WC_Order $order){
		$status = apply_filters('wc_wallee_failed_status', 'cancelled', $order);
		$order->update_status($status);
		WC_Wallee_Helper::instance()->maybe_restock_items_for_cancelled_order($order);
	}

	protected function fulfill(\Wallee\Sdk\Model\Transaction $transaction, WC_Order $order){
		$order->payment_complete($transaction->getId());
		//Sets the status to procesing or complete depending on items
	}

	protected function voided(\Wallee\Sdk\Model\Transaction $transaction, WC_Order $order){
		$status = apply_filters('wc_wallee_voided_status', 'cancelled', $order);
		$order->update_status($status);

	}
}