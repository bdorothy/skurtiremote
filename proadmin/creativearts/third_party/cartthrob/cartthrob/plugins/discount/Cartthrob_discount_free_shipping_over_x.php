<?php if ( ! defined('CARTTHROB_PATH')) Cartthrob_core::core_error('No direct script access allowed');

class Cartthrob_discount_free_shipping_over_x extends Cartthrob_discount
{
	public $title = 'free_shipping_over_x_title';
	public $settings = array(
		array(
			'name' => 'amount_off',
			'short_name' => 'amount_off',
			'note' => 'amount_off_note',
			'type' => 'text'
		),
		array(
			'name' => 'if_order_over',
			'short_name' => 'order_over',
			'note' => 'enter_required_minimum',
			'type' => 'text'
		)
	);
	
	public function get_discount()
	{
		if ($this->core->cart->subtotal() >= $this->core->sanitize_number($this->plugin_settings('order_over')))
		{
			$this->core->cart->set_discounted_shipping(0);
		}
		
		return 0; 
	}
	
}