<?php if ( ! defined('CARTTHROB_PATH')) Cartthrob_core::core_error('No direct script access allowed');

class Cartthrob_shipping_flat_rates extends Cartthrob_shipping
{
	public $title = 'title_flat_rates';
	public $note = 'flat_rates_note';
	public $settings = array(
		array(
			'name' => 'rates',
			'short_name' => 'rates',
			'type' => 'matrix',
			'settings' => array(
				array(
					'name' => 'default_setting',
					'short_name' => 'default',
					'type' => 'checkbox',
					'options' => array(
  						'extra' => ' onclick="if ($(this).is(\':checked\')) {  $(this).parent().parent().parent().parent().find(\'label input:checkbox\').not(this).attr(\'checked\',\'\') }"',
					)
				),
				array(
					'name' => 'setting_short_name',
					'short_name' => 'short_name',
					'type' => 'text'
				),
				array(
					'name' => 'setting_title',
					'short_name' => 'title',
					'type' => 'text'
				),
				array(
					'name' => 'cost_per_transaction',
					'short_name' => 'rate',
					'type' => 'text'
				),
				array(
					'name' => 'shipping_is_free_at',
					'short_name' => 'free_price',
					'type' => 'text'
				),
			)
		)
	);
 
	protected $rates = array();
	protected $free_rates = array();
	protected $shipping_option = '';
	protected $default_shipping_option = '';
	
	public function initialize()
	{
		foreach ($this->plugin_settings('rates') as $rate)
		{
			if ( ! empty($rate['default']))
			{
				$this->default_shipping_option = $rate['short_name'];
			}
			
			$this->rate_titles[$rate['short_name']] = $rate['title'];
			$this->rates[$rate['short_name']] = $rate['rate'];
			
			if (isset($rate['free_price']) && $rate['free_price'])
			{
				$this->free_rates[$rate['short_name']] = $rate['free_price'];
			}
		}
	}
	
	public function get_shipping()
	{
		if ($this->core->cart->count() <= 0 || $this->core->cart->shippable_subtotal() <= 0)
		{
			return 0;
		}
		
		$this->shipping_option = ($this->core->cart->shipping_info('shipping_option')) ? $this->core->cart->shipping_info('shipping_option') : $this->default_shipping_option;
	
		if ($this->shipping_option && array_key_exists($this->shipping_option, $this->rates))
		{
			if (array_key_exists($this->shipping_option, $this->free_rates) && ($this->core->cart->shippable_subtotal() > $this->free_rates[$this->shipping_option]))
			{
				return 0;
			}
			else
			{
				return $this->rates[$this->shipping_option];
			}
		}
		elseif ( ! $this->shipping_option)
		{
			return 0;
		}
		else
		{
			return max($this->rates);
		}
	}
	
	public function default_shipping_option()
	{
		return $this->default_shipping_option;
	}

	public function plugin_shipping_options()
	{
		$options = array();
		
		foreach ($this->rates as $rate_short_name => $price)
		{
			$options[] = array(
				'rate_short_name' => $rate_short_name,
				'price' => $price,
				'rate_price' => $price,
				'rate_title' => $this->rate_titles[$rate_short_name],
			);
		}
		
		return $options;
	}
}