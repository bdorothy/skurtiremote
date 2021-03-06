<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2003 - 2011, EllisLab, Inc.
 * @license		http://expressionengine.com/user_guide/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.0
 * @filesource
 */
 
// ------------------------------------------------------------------------

/**
 * Currency Select Extension
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Extension
 * @author		Sixthsense
 * @link		http://www.sixth.co.in
 */

class Currency_select_ext {
	
	public $settings 		= array();
	public $description		= 'Currency Select EXT';
	public $docs_url		= 'http://www.sixth.co.in';
	public $name			= 'Currency Select';
	public $settings_exist	= 'n';
	public $version			= '3.2';
	
	private $EE;
	
	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	public function __construct($settings = '')
	{
		$this->EE =& get_instance();
		$this->settings = $settings;
	}// ----------------------------------------------------------------------
	
	/**
	 * Activate Extension
	 *
	 * This function enters the extension into the exp_extensions table
	 *
	 * @see http://codeigniter.com/user_guide/database/index.html for
	 * more information on the db class.
	 *
	 * @return void
	 */
	public function activate_extension()
	{
		// Setup custom settings in this array.
		$this->settings = array();
		
		$hooks = array(
			'cartthrob_save_customer_info_start'	=> 'cartthrob_save_customer_info_start',
			'cartthrob_pre_process'					=> 'cartthrob_pre_process',
		);

		foreach ($hooks as $hook => $method)
		{
			$data = array(
				'class'		=> __CLASS__,
				'method'	=> $method,
				'hook'		=> $hook,
				'settings'	=> serialize($this->settings),
				'version'	=> $this->version,
				'enabled'	=> 'y'
			);

			$this->EE->db->insert('extensions', $data);			
		}
	}	

	// ----------------------------------------------------------------------
	
	/**
	 * channel_entries_query_resus
	 *
	 * @param 
	 * @return 
	 */
	public function cartthrob_save_customer_info_start()
	{
	$currency = ee()->input->post('currency_code');	
	$this->EE->cartthrob->cart->set_customer_info("currency_code", $currency);

	switch ($currency)
			{
				case "EUR":
					$country_code = "DE";
					break;
				case "USD":
					$country_code = "US";
					break;
				case "GBP":
					$country_code = "GB";
					break;
				case "AUD":
					$country_code = "AU";					
					break;		
				case "CAD":
					$country_code = "CA";					
					break;	
				case "INR":
					$country_code = "IN";					
					break;				
			}
	if (isset($country_code) && $country_code != ""){
	$this->EE->cartthrob->cart->set_customer_info("country_code", $country_code);
	}
	
	
	
	}

	// ----------------------------------------------------------------------
	
	public function cartthrob_pre_process($data){
	// get customer selected currency
	$current_currency = strtolower($this->get_currency_info('currency_code'));
	$country_code = strtolower($this->get_currency_info('country_code'));
	$gateway = $data['gateway']; //Cartthrob_paypal_express
	$base_currency = "inr";
	// if the customer falls in any range of our country selected 
	
	// manipulate the currencies now as per customers selected currency:
	
	// cases 1. If the customer has selected any currency from drop down than change totals according to the facevalue between inr to that currency.
	
	
	// if selected currency is inr and gateway is paypal...then change the value of price total to $dollars by default // (an nri might shop in rupees)
	if($current_currency == "inr" && $gateway == "Cartthrob_paypal_express" && $country_code != "IN"){
	// convert the values
	$data['tax'] = $this->converted_numeric($data['tax'],"usd");
	$data['shipping'] = $this->converted_numeric($data['shipping'],"usd");
	$data['discount'] = $this->converted_numeric($data['discount'],"usd");
	$data['shipping_plus_tax'] = $this->converted_numeric($data['shipping_plus_tax'],"usd");
	$data['subtotal'] = $this->converted_numeric($data['subtotal'],"usd");
	$data['subtotal_plus_tax'] = $this->converted_numeric($data['subtotal_plus_tax'],"usd");
	$data['total'] = $this->converted_numeric($data['total'],"usd");
	$this->EE->cartthrob->cart->set_customer_info("currency_code", "USD");
	$this->EE->set_total($data['total']);

	}
	

	// change currencies for any other than : inr by default to their respective currencies
	if($current_currency != "inr" &&  $gateway == "Cartthrob_paypal_express"){
	$data['tax'] = $this->converted_numeric($data['tax'],$current_currency);
	$data['shipping'] = $this->converted_numeric($data['shipping'],$current_currency);
	$data['discount'] = $this->converted_numeric($data['discount'],$current_currency);
	$data['shipping_plus_tax'] = $this->converted_numeric($data['shipping_plus_tax'],$current_currency);
	$data['subtotal'] = $this->converted_numeric($data['subtotal'],$current_currency);
	$data['subtotal_plus_tax'] = $this->converted_numeric($data['subtotal_plus_tax'],$current_currency);
	$data['total'] = $this->converted_numeric($data['total'],$current_currency);
	$this->EE->cartthrob->cart->set_customer_info("currency_code", strtoupper($current_currency));	
	
	}
	
	
	
	
	
	
	// cases 2. The customer requesting paypal should not be allowed in INR transactions for any country. So change the totals to Dollars by default
	
	// get customer selected country
	
	return( $data);

	}

	// ----------------------------------------------------------------------
	
	public function get_currency_info($get){
	$this->EE->load->add_package_path(PATH_THIRD.'cartthrob');		
	$this->EE->load->model('customer_model');		
	//load the settings into CI
	$this->EE->load->model('cartthrob_settings_model');				
	//load the session
	$this->EE->load->library('cartthrob_session');				
	//get the cart id from the session
	$cart_id = $this->EE->cartthrob_session->cart_id();			
	$this->EE->load->model('cart_model');				
	$params['cart'] = $this->EE->cart_model->read_cart($cart_id);				
	$existing_customer_info = (isset($params['cart']['customer_info'])) ? $params['cart']['customer_info'] : NULL;
	$params['cart']['customer_info'] = $this->EE->customer_model->get_customer_info($existing_customer_info);		
	//normally we'd want to instantiate with a config array,
	//but the Cartthrob_core_ee driver overrides the use of the config array and uses the cartthrob_settings_model's config cache

	return $params['cart']['customer_info'][$get];
	}
	
	// ----------------------------------------------------------------------

	
	
	public function converted_numeric($number = "",$current_currency = ""){	
	// the customers currency currently selected.	
	if($current_currency != "" && $current_currency != "usd"){
	$current_currency = strtolower($this->get_currency_info('currency_code'));
	}	
	$base_currency = "inr";
	if($number == ""){
	$number = $this->EE->TMPL->fetch_param('price');
	}
	$number = sanitize_number($number);
	// get the site config library
	$this->EE->load->add_package_path(PATH_THIRD.'siteconfig/');
	$this->EE->load->library('siteconfig');	
	if($current_currency == "inr"){
	$conversion_rate = 1;
	}else{
	$conversion_rate = $this->EE->siteconfig->item($current_currency.'_'.$base_currency);	
	}	
	$conversion_rate = intval($conversion_rate);
	$price = $number/$conversion_rate;
	$price = round($price,2);
	//$price = ceil($price / 10) * 10;
	return $price;
	}
	
	
	/**
	 * Disable Extension
	 *
	 * This method removes information from the exp_extensions table
	 *
	 * @return void
	 */
	function disable_extension()
	{
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->delete('extensions');
	}

	// ----------------------------------------------------------------------

	/**
	 * Update Extension
	 *
	 * This function performs any necessary db updates when the extension
	 * page is visited
	 *
	 * @return 	mixed	void on update / false if none
	 */
	function update_extension($current = '')
	{
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}
	}	
	
	// ----------------------------------------------------------------------
}

/* End of file ext.currency_select.php */
/* Location: /system/expressionengine/third_party/currency_select/ext.currency_select.php */