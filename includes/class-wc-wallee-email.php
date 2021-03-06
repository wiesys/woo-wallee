<?php
if (!defined('ABSPATH')) {
	exit();
}

/**
 * This class handles the email settings fo wallee.
 */
class WC_Wallee_Email {

	/**
	 * Register email hooks
	 */
	public static function init(){
		add_filter('woocommerce_email_enabled_new_order', array(
			__CLASS__,
			'send_email_for_order' 
		), 10, 2);
		add_filter('woocommerce_email_enabled_cancelled_order', array(
			__CLASS__,
			'send_email_for_order' 
		), 10, 2);
		add_filter('woocommerce_email_enabled_failed_order', array(
			__CLASS__,
			'send_email_for_order' 
		), 10, 2);
		add_filter('woocommerce_email_enabled_customer_on_hold_order', array(
			__CLASS__,
			'send_email_for_order' 
		), 10, 2);
		add_filter('woocommerce_email_enabled_customer_processing_order', array(
			__CLASS__,
			'send_email_for_order' 
		), 10, 2);
		add_filter('woocommerce_email_enabled_customer_completed_order', array(
			__CLASS__,
			'send_email_for_order' 
		), 10, 2);
		add_filter('woocommerce_email_enabled_customer_partially_refunded_order', array(
			__CLASS__,
			'send_email_for_order' 
		), 10, 2);
		add_filter('woocommerce_email_enabled_customer_refunded_order', array(
			__CLASS__,
			'send_email_for_order' 
		), 10, 2);
		
		add_filter('woocommerce_before_resend_order_emails', array(
			__CLASS__,
			'before_resend_email' 
		), 10, 1);
		add_filter('woocommerce_after_resend_order_emails', array(
			__CLASS__,
			'after_resend_email' 
		), 10, 2);
		
		add_filter( 'woocommerce_email_actions', array( __CLASS__, 'add_email_actions' ), 10, 1 );
		add_filter( 'woocommerce_email_classes', array( __CLASS__, 'add_email_hooks' ), 10, 1 );
	}

	public static function send_email_for_order($enabled, $order){
		if (!($order instanceof WC_Order)) {
			return $enabled;
		}
		if (isset($GLOBALS['_wallee_resend_email']) && $GLOBALS['_wallee_resend_email']) {
			return $enabled;
		}
		$send = get_option("wc_wallee_shop_email", "yes");
		if ($send != "yes") {
			return false;
		}
		return $enabled;
	}

	public static function before_resend_email($order){
		$GLOBALS['_wallee_resend_email'] = true;
	}

	public static function after_resend_email($order, $email){
		unset($GLOBALS['_wallee_resend_email']);
	}
	
	public static function add_email_actions($actions){
		$to_add = array(
			'woocommerce_order_status_wallee-redirected_to_processing',
			'woocommerce_order_status_wallee-redirected_to_completed',
			'woocommerce_order_status_wallee-redirected_to_on-hold',
			'woocommerce_order_status_wallee-redirected_to_wallee-waiting',
			'woocommerce_order_status_wallee-redirected_to_wallee-manual',
			'woocommerce_order_status_wallee-manual_to_cancelled',
			'woocommerce_order_status_wallee-waiting_to_cancelled',
			'woocommerce_order_status_wallee-redirected_to_on-hold',
			'woocommerce_order_status_wallee-redirected_to_processing',
			'woocommerce_order_status_wallee-manual_to_processing',
			'woocommerce_order_status_wallee-waiting_to_processing'
		);
		$actions = array_merge($actions, $to_add);
		return $actions;
	}
	
	public static function add_email_hooks($emails){
		foreach($emails as $key => $emailObject){
			switch($key){
				case 'WC_Email_New_Order':
					add_action( 'woocommerce_order_status_wallee-redirected_to_processing_notification', array( $emailObject, 'trigger' ), 10, 2 );
					add_action( 'woocommerce_order_status_wallee-redirected_to_completed_notification', array( $emailObject, 'trigger' ), 10, 2 );
					add_action( 'woocommerce_order_status_wallee-redirected_to_on-hold_notification', array( $emailObject, 'trigger' ), 10, 2 );
					add_action( 'woocommerce_order_status_wallee-redirected_to_wallee-waiting_notification', array( $emailObject, 'trigger' ), 10, 2 );
					add_action( 'woocommerce_order_status_wallee-redirected_to_wallee-manual_notification', array( $emailObject, 'trigger' ), 10, 2 );
					
					break;
					
				case 'WC_Email_Cancelled_Order':
					add_action( 'woocommerce_order_status_wallee-manual_to_cancelled_notification', array( $emailObject, 'trigger' ), 10, 2 );
					add_action( 'woocommerce_order_status_wallee-waiting_to_cancelled_notification', array( $emailObject, 'trigger' ), 10, 2 );
					break;
					
				case 'WC_Email_Customer_On_Hold_Order':
					add_action( 'woocommerce_order_status_wallee-redirected_to_on-hold_notification', array( $emailObject, 'trigger' ), 10, 2 );
					break;
					
				case 'WC_Email_Customer_Processing_Order':
					add_action( 'woocommerce_order_status_wallee-redirected_to_processing_notification', array( $emailObject, 'trigger' ), 10, 2 );
					add_action( 'woocommerce_order_status_wallee-manual_to_processing_notification', array( $emailObject, 'trigger' ), 10, 2 );
					add_action( 'woocommerce_order_status_wallee-waiting_to_processing_notification', array( $emailObject, 'trigger' ), 10, 2 );
					break;
					
				case 'WC_Email_Customer_Completed_Order':
					//Order complete are always send independent of the source status
					break;
					
				case 'WC_Email_Failed_Order':
				case 'WC_Email_Customer_Refunded_Order':
				case 'WC_Email_Customer_Invoice':
					//Do nothing for now
					break;
			}
		}
		
		return $emails;
	}
}

WC_Wallee_Email::init();