<?php if ( ! defined('CARTTHROB_PATH')) Cartthrob_core::core_error('No direct script access allowed');

class Cartthrob_shipping_single_flat_rate extends Cartthrob_shipping
{
	public $title = 'single_flat_rate';
 	public $settings = array(   
		array(
			'name' => 'rate',
			'short_name' => 'rate',
			'type' => 'text',
		),
 	);

	public function get_shipping()
	{
		return $this->plugin_settings('rate');   
	}
}