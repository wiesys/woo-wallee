<?php
if (!defined('ABSPATH')) {
	exit();
}

/**
 * This entity holds data about a transaction on the gateway.
 * 
 * @method int get_id()
 * @method int get_transaction_id()
 * @method void set_transaction_id(int $id)
 * @method string get_state()
 * @method void set_state(string $state)
 * @method int get_space_id()
 * @method void set_space_id(int $id)
 * @method int get_space_view_id()
 * @method void set_space_view_id(int $id)
 * @method string get_language()
 * @method void set_language(string $language)
 * @method string get_currency()
 * @method void set_currency(string $currency)
 * @method float get_authorization_amount()
 * @method void set_authorization_amount(float $amount)
 * @method string get_image()
 * @method void set_image(string $image)
 * @method object get_labels()
 * @method void set_labels(map[string,string] $labels)
 * @method int get_payment_method_id()
 * @method void set_payment_method_id(int $id)
 * @method int get_connector_id()
 * @method void set_connector_id(int $id)
 * @method int get_order_id()
 * @method void set_order_id(int $id)
 * @method void set_failure_reason(map[string,string] $reasons)
 *  
 */
class WC_Wallee_Entity_Transaction_Info extends WC_Wallee_Entity_Abstract {

	protected static function get_field_definition(){
		return array(
			'transaction_id' => WC_Wallee_Entity_Resource_Type::INTEGER,
			'state' => WC_Wallee_Entity_Resource_Type::STRING,
			'space_id' => WC_Wallee_Entity_Resource_Type::INTEGER,
			'space_view_id' => WC_Wallee_Entity_Resource_Type::INTEGER,
			'language' => WC_Wallee_Entity_Resource_Type::STRING,
			'currency' => WC_Wallee_Entity_Resource_Type::STRING,
			'authorization_amount' => WC_Wallee_Entity_Resource_Type::DECIMAL,
			'image' => WC_Wallee_Entity_Resource_Type::STRING,
			'labels' => WC_Wallee_Entity_Resource_Type::OBJECT,
			'payment_method_id' => WC_Wallee_Entity_Resource_Type::INTEGER,
			'connector_id' => WC_Wallee_Entity_Resource_Type::INTEGER,
			'order_id' => WC_Wallee_Entity_Resource_Type::INTEGER,
			'failure_reason' => WC_Wallee_Entity_Resource_Type::OBJECT,
			'locked_at' => WC_Wallee_Entity_Resource_Type::DATETIME 
		);
	}

	protected static function get_table_name(){
		return 'woocommerce_wallee_transaction_info';
	}

	/**
	 * Returns the translated failure reason.
	 *
	 * @param string $locale
	 * @return string
	 */
	public function get_failure_reason($language = null){
		$value = $this->get_value('failure_reason');
		if (empty($value)) {
			return null;
		}
		return WC_Wallee_Helper::instance()->translate($value, $language);
	}

	public static function load_by_order_id($order_id){
		global $wpdb;
		$result = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . self::get_table_name() . " WHERE order_id = %d", $order_id), 
				ARRAY_A);
		if ($result !== null) {
			return new self($result);
		}
		return new self();
	}

	public static function load_by_transaction($space_id, $transaction_id){
		global $wpdb;
		$result = $wpdb->get_row(
				$wpdb->prepare("SELECT * FROM " . $wpdb->prefix . self::get_table_name() . " WHERE space_id = %d AND transaction_id = %d", $space_id, 
						$transaction_id), ARRAY_A);
		if ($result !== null) {
			return new self($result);
		}
		return new self();
	}
}