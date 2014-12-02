<?php if ( ! defined('CARTTHROB_PATH')) Cartthrob_core::core_error('No direct script access allowed');

class Cartthrob_shipping_per_location_rates extends Cartthrob_shipping
{
 	public $title = 'title_per_location_rates';
	public $note = 'per_location_rates_note';
	public $overview = 'per_location_rates_overview'; 
	
	public $settings = array(
			array(
				'name'	=> 'default_cost_per_item',
				'short_name'	=> 'default_rate',
				'type'			=> 'text',
			),
			array(
				'name'	=> 'charge_default_by',
				'short_name'	=> 'default_type',
				'type'			=> 'select',
				'default' 		=> 'flat',
				'options'		=> array(
					'flat'	=> "by_item",
					'weight' => "by_weight",
					'order' => "by_order"
					),
			),
			array(
				'name' => 'charge_by_location',
				'short_name' => 'location_field',
				'type' => 'select',
				'default'	=> 'billing',
				'options' => array(
					'billing' => 'billing_address',
					'shipping' => 'shipping_address'
	 			)
			),
			array(
				'name'	=> 'rates',
				'short_name'	=> 'rates',
				'type'			=> 'matrix',
				'settings'		=> array(
					array(
						'name' 			=> 'cost',
						'short_name' 	=> 'rate',
						'type' 			=> 'text'
					),
					array(
						'name'			=> 'type',
						'short_name'	=> 'type',
						'type'			=> 'select',
						'default'		=> 'flat',
						'options'		=> array(
							'flat'	=> "by_item",
							'weight' => "by_weight",
							'order' => "by_order"
							
							),
					),
					array(
						'name'			=>	'location_zip_regions',
						'short_name'	=>	'zip',
						'type'			=>	'text',	
					),
					array(	
						'name'			=> 'location_states', 
						'short_name'	=> 'state',
						'type'			=> 'text',
					),
					array(	
						'name'			=> 'location_countries', 
						'short_name'	=> 'country',
						'type'			=> 'text',
						
					),
					array(
						'name' 			=> 'product_entry_ids',
						'short_name' 	=> 'entry_ids',
						'type' 			=> 'text'
					),
					array(
						'name' 			=> 'product_cat_ids',
						'short_name' 	=> 'cat_ids',
						'type' 			=> 'text'
					),
					/*array(
						'name'			=> 'product_channel',
						'short_name'		=> 'channel_id',
						'type'			=> 'select',
						'attributes'		=> array(
							'class'	=> 'all_channels',
						),
					),*/
					array(
						'name'			=> 'product_channel_content',
						'short_name'	=> 'field_value',
						'type'			=> 'text',
					),
					array(
						'name' 			=> 'in_channel_field',
						'short_name' 	=> 'field_name',
						'type' 			=> 'select',
						'attributes' => array(
							'class' => 'all_fields',
						),
					),
				),
			),
		);
	protected $default_rate = 0;
	
	public function initialize()
	{
		 if ($this->plugin_settings('default_rate') )
		{
			$this->default_rate = $this->plugin_settings('default_rate'); 
		}
	}
	public function get_shipping()
	{
		$location = '';
 		$customer_info = $this->core->cart->customer_info();
		if ($this->plugin_settings('location_field') == 'billing')
		{
			$primary_loc 	= "";
			$backup_loc		= "shipping_"; 
		}
		else
		{
			$primary_loc 	= "shipping_";
			$backup_loc		= "";	
		}

		$country 			=  (!empty($customer_info[$primary_loc.'country_code'])	? $customer_info[$primary_loc.'country_code'] : $customer_info[$backup_loc.'country_code']);
		$state	 			=  (!empty($customer_info[$primary_loc.'state'])		? $customer_info[$primary_loc.'state'] : $customer_info[$backup_loc.'state']);
		$zip				=  (!empty($customer_info[$primary_loc.'zip'])			? $customer_info[$primary_loc.'zip'] : $customer_info[$backup_loc.'zip']);
 		$shipping = 0;
		
		foreach ($this->core->cart->shippable_items() as $row_id => $item)
		{
			if ( ! $item->product_id())
			{
				continue;
			}

			// Get all settings
			$location_shipping = 0; 

			foreach ($this->plugin_settings('rates') as $rate)
			{
				$locations['zip']		= explode(',',$rate['zip']);
				$locations['state']		= explode(',',$rate['state']);
				$locations['country']	= explode(',',$rate['country']);
				
				if ($rate['type'] == "weight")
				{
					$shipping_amount = $rate['rate'] * ($item->quantity() * $item->weight());
				}
				elseif ($rate['type'] == "flat")
				{
					$shipping_amount = $rate['rate'] * $item->quantity(); 
				}
				else
				{
					$shipping_amount = $rate['rate']; 
				}

				if ($this->plugin_settings('default_type') == "weight")
				{
					$default_amount = $this->default_rate  * ($item->quantity() * $item->weight());
				}
				elseif ( $this->plugin_settings('default_type') == "flat" )
				{
					$default_amount = $this->default_rate * $item->quantity(); 
				}
				else
				{
					$default_amount = $this->default_rate; 
				}
				
				// Make sure entry ids have been entered
				if (!empty($rate['entry_ids']))
				{
					// get list of entry ids
					$entry_ids = explode(',', $rate['entry_ids']);

					// check if item in cart is in this rate
					if (in_array('GLOBAL', $entry_ids) || in_array($item->product_id(), $entry_ids) )
					{	
						$associated_cost =  $this->location_shipping($locations, $zip, $state, $country, $shipping_amount); 
						
						if ($associated_cost !== FALSE)
						{
							$location_shipping = $associated_cost; 
							break;
						}
						else
						{
							continue;
						}
					}
					// if item isnt in this rate line, skip it
					else
					{
						continue;
					}
				}
				// Check Categories
				elseif (!empty($rate['cat_ids']))
				{
					$cats = explode(",",$rate['cat_ids']);
					if ($product = $this->core->store->product($item->product_id()))
					{
						foreach ($product->categories() as $cat_id)
						{
							if (in_array('GLOBAL', $cats) || in_array($cat_id,$cats))
							{
								$associated_cost =  $this->location_shipping($locations, $zip, $state, $country, $shipping_amount); 
								
								if ($associated_cost !== FALSE)
								{
									$location_shipping = $associated_cost; 
									break;
								}
								else
								{
									continue;
								}
								
							}
						}
					}
				}
				/*
				// @TODO activate this and the setting after channel attribute is added
 				// Check Weblogs				
				elseif (!empty($rate['channel_id']))
				{
					$channels = explode(",",$rate['channel_id']);
					
					if ( $product = $this->core->store->product($item->product_id()) )
					{
						$channel_id = $product->meta('channel_id');
						
						if (in_array('GLOBAL', $channels) || in_array($channel_id,$channels))
						{
							$associated_cost =  $this->location_shipping($locations, $zip, $state, $country, $shipping_amount); 

							if ($associated_cost !== FALSE)
							{
								$location_shipping += $associated_cost; 
								break;
							}
							else
							{
								continue;
							}
									
						}
					}
				}
				*/
				
				elseif (!empty($rate['field_value']) && !empty($rate['field_name']) && $rate['field_name'] != "0")
				{
					$content = explode(",",$rate['field_value']);
					
					$product = $this->core->store->product($item->product_id());
					
					if ($product && $product->meta($rate['field_name']) == $rate['field_value'])
					{
						$associated_cost =  $this->location_shipping($locations, $zip, $state, $country, $shipping_amount); 
						
						if ($associated_cost !== FALSE)
						{
							$location_shipping = $associated_cost; 
							break;
						}
						else
						{
							continue;
						}

					}
					elseif (in_array('GLOBAL', $content))
					{
						$associated_cost =  $this->location_shipping($locations, $zip, $state, $country, $shipping_amount); 
						
						if ($associated_cost !== FALSE)
						{
							$location_shipping = $associated_cost; 
							break;
						}
						else
						{
							continue;
						}
							
					}
					else
					{
						continue;
					}
				}
				else
				{
					continue;
				}


			}
			
			if ($location_shipping > 0)
			{
				if ($rate['type'] == "order")
				{
				 	return $location_shipping; 
				}
				$shipping +=$location_shipping; 
			}
			else
			{
				$shipping += $default_amount;
			}
			
			
		}// END checking cart items

		return $shipping; 
	}
	// END get_shipping

			/**
		 * _location_shipping
		 *
		 * checks location, and returns shipping cost
		 * @param array $locations 
		 * @param string $shipping_amount
		 * @return string 
		 * @author Chris Newton
		 */
	private function location_shipping($locations, $zip, $state, $country, $shipping_amount, $default=0)
	{
		if (in_array('GLOBAL', $locations['zip']) || (!empty($zip) && in_array( $zip, $locations['zip'])))
		{
			return $shipping_amount; 
		}
		elseif (in_array('GLOBAL', $locations['state']) || (!empty($state) && in_array( $state, $locations['state'])))
		{
			return $shipping_amount; 
		}
		elseif (in_array('GLOBAL', $locations['country']) || (!empty($country) && in_array( $country, $locations['country'])))
		{
			return $shipping_amount; 
		}
		elseif ($default)
		{
			return $default;
		}
		else
		{
			return FALSE; 
		}
		
	}
	// END location_shipping
}


/* End of file cartthrob.per_location_rates.php */
/* Location: ./system/modules/shipping_plugins/cartthrob.per_location_rates.php */