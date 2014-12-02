<?php if ( ! defined('CARTTHROB_PATH')) Cartthrob_core::core_error('No direct script access allowed');

class Cartthrob_discount_percentage_off extends Cartthrob_discount
{
	public $title = 'percentage_off';
	public $settings = array(
		array(
			'name' => 'percentage_off',
			'short_name' => 'percentage_off',
			'note' => 'percentage_off_note',
			'type' => 'text'
		)
	);
	
	public function get_discount()
	{
		return $this->core->cart->subtotal() * ($this->core->sanitize_number($this->plugin_settings('percentage_off')) / 100);
	}
}