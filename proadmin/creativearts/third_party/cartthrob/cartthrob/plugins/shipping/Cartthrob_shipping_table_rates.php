<?php if ( ! defined('CARTTHROB_PATH')) Cartthrob_core::core_error('No direct script access allowed');

class Cartthrob_shipping_table_rates extends Cartthrob_shipping
{
	public $title = 'table_rates';
	public $classname = __CLASS__;
	public $note = 'location_threshold_overview';
	public $settings = array(
			
		array(
			'name' => 'primary_location_field',
			'short_name' => 'location_field',
			'type' => 'select',
			'default'	=> 'country_code',
			'options' => array(
				'zip' => 'zip',
				'state'	=> 'state', 
				'region' => 'Region',
				'country_code' => 'settings_country_code',
				'shipping_zip' => 'shipping_zip',
				'shipping_state' => 'shipping_state',
				'shipping_region' => 'shipping_region', 
				'shipping_country_code' => 'settings_shipping_country_code'
			)
		),
		array(
			'name' => 'backup_location_field',
			'short_name' => 'backup_location_field',
			'type' => 'select',
			'default'	=> 'country_code',
			'options' => array(
				'zip' => 'zip',
				'state'	=> 'state', 
				'region' => 'Region',
				'country_code' => 'settings_country_code',
				'shipping_zip' => 'shipping_zip',
				'shipping_state' => 'shipping_state',
				'shipping_region' => 'shipping_region', 
				'shipping_country_code' => 'settings_shipping_country_code'
			)
		),
		array(
			'name' => 'thresholds',
			'short_name' => 'thresholds',
			'type' => 'matrix',
			'settings' => array(
				array(
					'name'			=>	'country_code',
					'short_name'	=>	'location',
					'type'			=>	'text',	
				),
				array(
					'name' => '1kg',
					'short_name' => '1kg',
					'note' => '1kg',
					'type' => 'text'
				),
				array(
					'name' => '2kg',
					'short_name' => '2kg',
					'note' => '2kg',
					'type' => 'text',					
				),
				array(
					'name' => '3kg',
					'short_name' => '3kg',
					'note' => '3kg',
					'type' => 'text'
				),
				array(
					'name' => '4kg',
					'short_name' => '4kg',
					'note' => '4kg',
					'type' => 'text'
				),
				array(
					'name' => '5kg',
					'short_name' => '5kg',
					'note' => '5kg',
					'type' => 'text'
				),
				array(
					'name' => '6kg',
					'short_name' => '6kg',
					'note' => '6kg',
					'type' => 'text'
				),
				array(
					'name' => '7kg',
					'short_name' => '7kg',
					'note' => '7kg',
					'type' => 'text'
				),
								array(
					'name' => '8kg',
					'short_name' => '8kg',
					'note' => '8kg',
					'type' => 'text'
				),
								array(
					'name' => '9kg',
					'short_name' => '9kg',
					'note' => '9kg',
					'type' => 'text'
				),
								array(
					'name' => '10kg',
					'short_name' => '10kg',
					'note' => '10kg',
					'type' => 'text'
				),
				array(
					'name' => '11kg',
					'short_name' => '11kg',
					'note' => '<span style="color:#FF0">11+/per Kg</span>',
					'type' => 'text'
				),
				array(
					'name' => '21kg',
					'short_name' => '21kg',
					'note' => '<span style="color:#FF0">21+/per Kg</span>',
					'type' => 'text'
				)
			)
		)
	);

	public function get_shipping()
	{
		$customer_info = $this->core->cart->customer_info(); 
		$location_field = $this->plugin_settings('location_field', 'shipping_country_code');
		$backup_location_field = $this->plugin_settings('backup_location_field', 'country_code');

		if ( ! empty($customer_info[$location_field]))
		{
			$location = $customer_info[$location_field];
		}
		else if ( ! empty($customer_info[$backup_location_field]))
		{
			$location = $customer_info[$backup_location_field];
		}
		else
		{
			$location = NULL; 
		}
		$shipping = 0;
		$weight = $this->core->cart->shippable_weight();
		$priced = FALSE;
		$last_rate = '';
		$threshold = $this->plugin_settings('thresholds');
		if($threshold != ""){
		foreach ($this->plugin_settings('thresholds') as $threshold_setting){
		
		$location_array	= preg_split('/\s*,\s*/', trim($threshold_setting['location']));
		
		$location_array = array($location_array[0]);
		
		
		// country rates are defined	
		if (in_array('GLOBAL', $location_array)){	
	
		switch ($weight) {
			// if weight 1kg to 1.99kg
			case $weight < 1.99:
			$shipping =  $threshold_setting['1kg'];
			break;
			
			// if weight 2kg to 2.99kg
			case $weight > 1.99 AND $weight < 2.99:
			$shipping =  $threshold_setting['2kg'];
			break;
			
			// if weight 3kg to 3.99kg
			case $weight > 2.99 AND $weight < 3.99:
			$shipping =  $threshold_setting['3kg'];
			break;	

			// if weight 4kg to 4.99kg
			case $weight > 3.99 AND $weight < 4.99:
			$shipping =  $threshold_setting['4kg'];
			break;					
			
			// if weight 4kg to 5.99kg
			case $weight > 4.99 AND $weight < 5.99:
			$shipping =  $threshold_setting['5kg'];
			break;			
			
			// if weight 5kg to 6.99kg
			case $weight > 5.99 AND $weight < 6.99:
			$shipping =  $threshold_setting['6kg'];
			break;	
			
			
			// if weight 6kg to 7.99kg
			case $weight > 6.99 AND $weight < 7.99:
			$shipping =  $threshold_setting['7kg'];
			break;
			
			// if weight 7kg to 8.99kg
			case $weight > 7.99 AND $weight < 8.99:
			$shipping =  $threshold_setting['8kg'];
			break;
			
			// if weight 8kg to 9.99kg
			case $weight > 8.99 AND $weight < 9.99:
			$shipping =  $threshold_setting['9kg'];
			break;
			
			// if weight 9kg to 10.99kg
			case $weight > 9.99 AND $weight < 10.99:
			$shipping =  $threshold_setting['10kg'];
			break;
			
			// if weight 11kg to 20.99kg
			case $weight > 10.99 AND $weight < 20.99:
			$shipping =  $threshold_setting['11kg'] * round($weight);
			break;		

			// if weight 21+ kg
			case $weight > 20.99:
			$shipping =  $threshold_setting['21kg'] * round($weight);
			break;			
		}
		
		
		}
		
		
		elseif (in_array($location,$location_array)) {
		switch ($weight) {
			// if weight 1kg to 1.99kg
			case $weight < 1.99:
			$shipping =  $threshold_setting['1kg'];
			break;
			
			// if weight 2kg to 2.99kg
			case $weight > 1.99 AND $weight < 2.99:
			$shipping =  $threshold_setting['2kg'];
			break;
			
			// if weight 3kg to 3.99kg
			case $weight > 2.99 AND $weight < 3.99:
			$shipping =  $threshold_setting['3kg'];
			break;	

			// if weight 4kg to 4.99kg
			case $weight > 3.99 AND $weight < 4.99:
			$shipping =  $threshold_setting['4kg'];
			break;					
			
			// if weight 4kg to 5.99kg
			case $weight > 4.99 AND $weight < 5.99:
			$shipping =  $threshold_setting['5kg'];
			break;			
			
			// if weight 5kg to 6.99kg
			case $weight > 5.99 AND $weight < 6.99:
			$shipping =  $threshold_setting['6kg'];
			break;	
			
			
			// if weight 6kg to 7.99kg
			case $weight > 6.99 AND $weight < 7.99:
			$shipping =  $threshold_setting['7kg'];
			break;
			
			// if weight 7kg to 8.99kg
			case $weight > 7.99 AND $weight < 8.99:
			$shipping =  $threshold_setting['8kg'];
			break;
			
			// if weight 8kg to 9.99kg
			case $weight > 8.99 AND $weight < 9.99:
			$shipping =  $threshold_setting['9kg'];
			break;
			
			// if weight 9kg to 10.99kg
			case $weight > 9.99 AND $weight < 10.99:
			$shipping =  $threshold_setting['10kg'];
			break;
			
			// if weight 11kg to 20.99kg
			case $weight > 10.99 AND $weight < 20.99:
			$shipping =  $threshold_setting['11kg'] * round($weight);
			break;		

			// if weight 21+ kg
			case $weight > 20.99:
			$shipping =  $threshold_setting['21kg'] * round($weight);
			break;		
		}
		}
		
		
		}
		}
		
		
		
		return $shipping;
	}
}