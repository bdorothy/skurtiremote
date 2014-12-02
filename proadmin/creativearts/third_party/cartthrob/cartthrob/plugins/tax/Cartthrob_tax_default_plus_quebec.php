<?php if ( ! defined('CARTTHROB_PATH')) Cartthrob_core::core_error('No direct script access allowed');

class Cartthrob_tax_default_plus_quebec extends Cartthrob_tax
{
	public $title = 'tax_by_location_with_quebec';
	public $overview = 'tax_quebec_overview';
	public $settings = array(
		array(
			'name' => 'quebec_gst',
			'short_name' => 'tax_gst',
			'type' => 'text',
			'default'	=> '5'
		), 
		array(
			'name' => 'quebec_qst',
			'short_name' => 'tax_qst',
			'type' => 'text',
			'default'	=> '8.5'
		),
		array(
			'name' => 'quebec_tax_shipping',
			'short_name' => 'tax_quebec_shipping',
			'type' => 'radio',
			'default'	=> 'no',
			'options' => array('no' => 'no', 'yes'=> 'yes'),
		),
		array(
			'name' => 'quebec_descriptive_name',
			'short_name' => 'tax_quebec_name',
			'type' => 'text',
			'default'	=> 'Consumption Tax (GST & QST)'
		),
		array(
			'name' => 'quebec_effective_rate',
			'short_name' => 'tax_quebec_effective_rate',
			'type' => 'text',
			'default'	=> '13.925'
		),
		array(
			'name' => 'tax_by_location_settings',
			'short_name' => 'tax_settings',
			'type' => 'matrix',
			'settings' => array(
				array(
					'name' => 'name',
					'short_name' => 'name',
					'type' =>'text',	
				),
				array(
					'name' => 'tax_percent',
					'short_name' => 'rate',
					'type' => 'text'
				),
				array(
					'name' => 'state_country',
					'short_name' => 'state',
					'type' => 'select',
					'attributes' => array(
						'class' => 'states_and_countries',
					),
					'options' => array(),
				),
				array(
					'name' => 'zip_region',
					'short_name' => 'zip',
					'type' => 'text',
				),
				array(
					'name' => 'tax_shipping',
					'short_name' => 'tax_shipping',
					'type' => 'checkbox',
				),
			)
		)
	);
	
	protected $tax_data;
	
	public function get_tax($price)
	{
		$prefix = ($this->core->store->config('tax_use_shipping_address')) ? 'shipping_' : '';
		
		if ($this->core->cart->customer_info($prefix.'state') && "QC" == $this->core->cart->customer_info($prefix.'state'))
		{
			$gst_total = $price *  ($this->core->sanitize_number($this->plugin_settings('tax_gst'))/100) ;
			return $gst_total * ($this->core->sanitize_number($this->plugin_settings('tax_qst'))/100) ;
		}
		else
		{
			return  $price * $this->tax_rate();
		}
	}
	
	public function tax_name()
	{
		$prefix = ($this->core->store->config('tax_use_shipping_address')) ? 'shipping_' : '';
		if ($this->core->cart->customer_info($prefix.'state') && "QC" == $this->core->cart->customer_info($prefix.'state'))
		{
			return $this->plugin_settings('tax_quebec_name'); 
		}
		return $this->tax_data('name');
	}
	
	public function tax_rate()
	{
		$prefix = ($this->core->store->config('tax_use_shipping_address')) ? 'shipping_' : '';
		if ($this->core->cart->customer_info($prefix.'state') && "QC" == $this->core->cart->customer_info($prefix.'state'))
		{
			return $this->plugin_settings('tax_quebec_effective_rate'); 
		}
		return $this->core->sanitize_number($this->tax_data('rate'))/100;
	}
	
	public function tax_shipping()
	{
		$prefix = ($this->core->store->config('tax_use_shipping_address')) ? 'shipping_' : '';
		if ($this->core->cart->customer_info($prefix.'state') && "QC" == $this->core->cart->customer_info($prefix.'state'))
		{
			return $this->plugin_settings('tax_quebec_shipping'); 
		}
		return (bool) $this->tax_data('tax_shipping');
	}
	
	public function tax_data($key = FALSE)
	{
		if (is_null($this->tax_data))
		{
			$this->tax_data = array();
			
			$prefix = ($this->core->store->config('tax_use_shipping_address')) ? 'shipping_' : '';
		
			foreach ($this->plugin_settings('tax_settings', array()) as $tax_data)
			{	
				//zip code first
				if ($this->core->cart->customer_info($prefix.'zip') && $tax_data['zip'] == $this->core->cart->customer_info($prefix.'zip'))
				{
					$this->tax_data = $tax_data;
					break;
				}
				elseif ($this->core->cart->customer_info($prefix.'region') && $tax_data['zip'] == $this->core->cart->customer_info($prefix.'region'))
				{
					$this->tax_data = $tax_data;
					break;
				}
				elseif ($this->core->cart->customer_info($prefix.'state') && $tax_data['state'] == $this->core->cart->customer_info($prefix.'state'))
				{
					$this->tax_data = $tax_data;
					break;
				}
				elseif ($this->core->cart->customer_info($prefix.'country_code') && $tax_data['state'] == $this->core->cart->customer_info($prefix.'country_code'))
				{
					$this->tax_data = $tax_data;
					break;
				}
				//elseif (array_key_exists('global', $tax_data))
				// 'global' is set in the state dropdown so it's not an array key, it's a value of $tax_data['state']
				elseif (in_array('global', $tax_data))
				{
					$this->tax_data = $tax_data;
					break;
				}
			}
			
		}
		
		if ($key === FALSE)
		{
			return $this->tax_data;
		}
		
		return (isset($this->tax_data[$key])) ? $this->tax_data[$key] : FALSE;
	}
}
