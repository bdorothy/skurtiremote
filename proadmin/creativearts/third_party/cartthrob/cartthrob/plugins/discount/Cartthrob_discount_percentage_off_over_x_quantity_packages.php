<?php if ( ! defined('CARTTHROB_PATH')) Cartthrob_core::core_error('No direct script access allowed');

class Cartthrob_discount_percentage_off_over_x_quantity_packages extends Cartthrob_discount
{
	public $title = 'percentage_off_over_x_quantity_packages';
	public $settings = array(
		array(
			'name' => 'percentage_off',
			'short_name' => 'percentage_off',
			'note' => 'percentage_off_note',
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
		$subtotal = 0; 
		$quantity = 0; 
		foreach ($this->core->cart->items() as $row_id => $item)
		{
			if ($item->sub_items())
			{
				$subtotal += $item->price_subtotal(); 
				$quantity += $item->quantity(); 
			}
		}
		
		if ($quantity >= $this->core->sanitize_number($this->plugin_settings('order_over')))
		{
			return $subtotal * ($this->core->sanitize_number($this->plugin_settings('percentage_off')) / 100);
		}
		
		return 0;
		
	}
}