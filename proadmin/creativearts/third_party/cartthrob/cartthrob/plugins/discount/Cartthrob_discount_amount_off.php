<?php if ( ! defined('CARTTHROB_PATH')) Cartthrob_core::core_error('No direct script access allowed');

class Cartthrob_discount_amount_off extends Cartthrob_discount
{
	public $title = 'amount_off';
	
	public $settings = array(
		array(
			'name' => 'amount_off',
			'short_name' => 'amount_off',
			'note' => 'amount_off_note',
			'type' => 'text'
		)
	);
	
	public function get_discount()
	{
		return $this->core->sanitize_number($this->plugin_settings('amount_off'));
	}
}