<?php if ( ! defined('CARTTHROB_PATH')) Cartthrob_core::core_error('No direct script access allowed');

class Cartthrob_shipping_by_weight_global_rate extends Cartthrob_shipping
{
 	public $title = 'title_by_weight_global_rate';
	public $classname = __CLASS__;
	public $note = 'by_weight_global_rate_note';
	public $settings = array(
			array(
				'name' => 'rate', 
				'note' => 'rate_example',
				'short_name' => 'rate',
				'type' => 'text'
			)
		); 
	
	public function get_shipping()
	{
		return $this->core->cart->shippable_weight() * $this->plugin_settings('rate');
	}
}