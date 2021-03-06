<?php
if (!defined('ABSPATH')) {
	exit();
}

class WC_Wallee_Webhook_Entity {
	private $id;
	private $name;
	private $states;
	private $notify_every_change;
	private $handler_class_name;

	public function __construct($id, $name, array $states, $handler_class_name, $notify_every_change = false){
		$this->id = $id;
		$this->name = $name;
		$this->states = $states;
		$this->notify_every_change = $notify_every_change;
		$this->handler_class_name = $handler_class_name;
	}

	public function get_id(){
		return $this->id;
	}

	public function get_name(){
		return $this->name;
	}

	public function get_states(){
		return $this->states;
	}

	public function is_notify_every_change(){
		return $this->notify_every_change;
	}

	public function get_handler_class_name(){
		return $this->handler_class_name;
	}
}