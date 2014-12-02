<?php if ( ! defined('CARTTHROB_PATH')) Cartthrob_core::core_error('No direct script access allowed');

class Cartthrob_shipping_by_weight_threshold extends Cartthrob_shipping
{
	public $title = 'title_by_weight_threshold';
	public $classname = __CLASS__;
	public $note = 'by_weight_threshold_note';
	public $settings = array(	
		array(
			'name' => 'calculate_costs',
			'short_name' => 'mode',
			'type' => 'radio',
			'default' => 'price',
			'options' => array(
				'price' => 'use_rate_as_shipping_cost',
				'rate' => 'multiply_rate_and_weight'
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
					'name' => 'weight_threshold',
					'short_name' => 'threshold',
					'note' => 'weight_threshold_example',
					'type' => 'text'
				)
			)
		)
	);

	public function get_shipping()
	{
		$weight = $this->core->cart->shippable_weight();
		
		$rate = $this->threshold($weight, $this->get_thresholds());
		
		return ($this->plugin_settings('mode') == 'rate') ? $weight * $rate : $rate;
	}
}