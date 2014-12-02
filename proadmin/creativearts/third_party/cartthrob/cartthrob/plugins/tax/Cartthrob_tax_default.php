<?php if ( ! defined('CARTTHROB_PATH')) Cartthrob_core::core_error('No direct script access allowed');

class Cartthrob_tax_default extends Cartthrob_tax
{
	public $title = 'tax_by_location_legacy';
	public $settings = array(
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
		return $price * $this->tax_rate();
	}
	
	public function tax_name()
	{
		return $this->tax_data('tax_name');
	}
	
	public function tax_rate()
	{
		return $this->core->sanitize_number($this->tax_data('percent'))/100;
	}
	
	public function tax_shipping()
	{
		return (bool) $this->tax_data('shipping_is_taxable');
	}
	
	public function tax_data($key = FALSE)
	{
		if (is_null($this->tax_data))
		{
			$this->tax_data = array();
			
			$prefix = ($this->core->store->config('tax_use_shipping_address')) ? 'shipping_' : '';
		
			$locations = array(); 
			
			$tax_settings = $this->plugin_settings('tax_settings'); 
			
 			if ($this->core->cart->customer_info($prefix.'zip') )
			{
				$locations['zip'] = $this->core->cart->customer_info($prefix.'zip'); 
			}
			if ($this->core->cart->customer_info($prefix.'region'))
			{
				$locations['special'] = $this->core->cart->customer_info($prefix.'region'); 
			}
			if ($this->core->cart->customer_info($prefix.'state'))
			{
				$locations['state'] = $this->core->cart->customer_info($prefix.'state'); 
			}
			if ($this->core->cart->customer_info($prefix.'country_code'))
			{
				$locations['country'] = $this->core->cart->customer_info($prefix.'country_code'); 
			}
			if ($this->plugin_settings('use_tax_table')=="yes")
			{
				$tax_settings = $this->core->get_tax_rates($locations); 
			}

 			
			if (is_array($tax_settings))
			{
				foreach ($tax_settings as $tax_data)
				{	
	 				if ($this->plugin_settings('use_tax_table') !="yes")
					{
						$tax_data['shipping_is_taxable'] =  (isset($tax_data['tax_shipping'])) ? $tax_data['tax_shipping'] : FALSE; 
						$tax_data['tax_name'] = (isset($tax_data['name'])) ? $tax_data['name'] : FALSE; 
						$tax_data['percent'] = (isset($tax_data['rate'])) ? $tax_data['rate'] : FALSE; 
						$tax_data['country'] =(isset($tax_data['state'])) ? $tax_data['state'] : FALSE; 
						$tax_data['special'] =(isset($tax_data['zip'])) ? $tax_data['zip'] : FALSE; 
					}

					//region first
					if ($this->core->cart->customer_info($prefix.'region') && $tax_data['special'] == $this->core->cart->customer_info($prefix.'region'))
					{
						$this->tax_data = $tax_data;
						break;
					}
					// check to see if the zip has data
					elseif ($tax_data['zip'])
					{
						$zipcodes = array_map('trim',explode(',',$tax_data['zip']));
						if($this->core->cart->customer_info($prefix.'zip') && in_array($this->core->cart->customer_info($prefix.'zip'),$zipcodes))
						{
							$this->tax_data = $tax_data;
							break;
						}
					}
					elseif ($this->core->cart->customer_info($prefix.'state') && $tax_data['state'] == $this->core->cart->customer_info($prefix.'state'))
					{
						$this->tax_data = $tax_data;
						break;
					}
					elseif ($this->core->cart->customer_info($prefix.'country_code') && $tax_data['country'] == $this->core->cart->customer_info($prefix.'country_code'))
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
			
		}
 		if ($key === FALSE)
		{
			return $this->tax_data;
		}
		
		return (isset($this->tax_data[$key])) ? $this->tax_data[$key] : FALSE;
	}
}
