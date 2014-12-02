<?php if ( ! defined('CARTTHROB_PATH')) Cartthrob_core::core_error('No direct script access allowed');

class Cartthrob_shipping_by_price_threshold extends Cartthrob_shipping
{
	public $title = 'title_price_threshold';
	public $classname = __CLASS__;
	public $note = 'price_threshold_overview';
	public $settings = array(
		array(
			'name' => 'set_shipping_cost_by',
			'short_name' => 'mode',
			'type' => 'radio',
			'default' => 'price',
			'options' => array(
				'price' => 'rate_amount',
				'rate' => 'rate_amount_times_cart_total'
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
					'name' => 'price_threshold',
					'short_name' => 'threshold',
					'note' => 'price_threshold_example',
					'type' => 'text'
				)
			)
		)
	);
	
	public function get_shipping()
	{
		$price = $this->core->cart->shippable_subtotal();
		
		$rate = $this->threshold($price, $this->get_thresholds());
		
		return ($this->plugin_settings('mode') == 'rate') ? $price * $rate : $rate;
	}
}