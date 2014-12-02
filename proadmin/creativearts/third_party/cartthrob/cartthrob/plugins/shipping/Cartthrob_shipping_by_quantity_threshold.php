<?php if ( ! defined('CARTTHROB_PATH')) Cartthrob_core::core_error('No direct script access allowed');

class Cartthrob_shipping_by_quantity_threshold extends Cartthrob_shipping
{
	public $title = 'title_by_quantity_threshold';
	public $classname = __CLASS__;
	public $note = 'costs_are_set_at';
	public $settings = array(   
		array(
			'name' => 'calculate_costs',
			'short_name' => 'mode',
			'type' => 'radio',
			'default' => 'price',
			'options' => array(
				'price' => 'use_rate_as_shipping_cost',
				'rate' => 'multiply_rate_by_quantity'
			)
		),
		array(
			'name' => 'thresholds',
			'short_name' => 'thresholds',
			'type' => 'matrix',
			'settings' => array(
				array(
					'name' => 'rate',
					'short_name' => 'rate',
					'note' => 'rate_example',
					'type' => 'text'
				),
				array(
					'name' => 'quantity_threshold',
					'short_name' => 'threshold',
					'type' => 'text'
				)
			)
		)
	);

	public function get_shipping()
	{
		$total_items = $this->core->cart->count_all(array('no_shipping' => FALSE));
		
		$rate = $this->threshold($total_items, $this->get_thresholds());
		
		return ($this->plugin_settings('mode') == 'rate') ? $total_items * $rate : $rate;
	}
}