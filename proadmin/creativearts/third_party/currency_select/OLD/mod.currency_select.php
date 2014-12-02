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
 * Cartthrob Currency Select Module Front End File
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Sixthsense
 * @link		http://www.sixth.co.in
 */

class  Cartthrob_currency_select{
	
	public $return_data;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
	}
	
	// ----------------------------------------------------------------
	
	
	public function currency_select(){			
		$this->EE->load->helper('form');
		$name = ($this->EE->TMPL->fetch_param('name')) ? $this->EE->TMPL->fetch_param('name') : 'currency_code';		
		$currencies = array(
		'INR' => 'INR',
		'USD' => 'USD',
		'GBP' => 'GBP',
		'EUR' => 'EUR',
		'AUD' => 'AUD',
		'CAD' => 'CAD',
		);	
		
		$attrs = array();		
		if ($this->EE->TMPL->fetch_param('id')){
			$attrs['id'] = $this->EE->TMPL->fetch_param('id');
		}		
		if ($this->EE->TMPL->fetch_param('class'))	{
			$attrs['class'] = $this->EE->TMPL->fetch_param('class');
		}
		$attrs['onchange'] = 'this.form.submit()';		
		$extra = '';		
		if ($attrs){
			$extra .= _attributes_to_string($attrs);
		}
		if ($this->EE->TMPL->fetch_param('extra')){
			if (substr($this->EE->TMPL->fetch_param('extra'), 0, 1) !== ' '){
				$extra .= ' ';
			}			
			$extra .= $this->EE->TMPL->fetch_param('extra');
		}
		$selected = ($this->EE->TMPL->fetch_param('selected')) ? $this->EE->TMPL->fetch_param('selected') : $this->EE->TMPL->fetch_param('default');
		
		return form_dropdown(
			$name,
			$currencies,
			$selected,
			$extra
		);
	}
	
	
	
	
	public function convert(){
	
	// get the site config library
	$this->EE->load->add_package_path(PATH_THIRD.'siteconfig/');
	$this->EE->load->library('siteconfig');
	
	// get the current product price
	$number = $this->EE->TMPL->fetch_param('price');
	$number = sanitize_number($number);
	
	
	// fetch the conversion values
	$from = strtolower($this->EE->TMPL->fetch_param('from'));
	$to = strtolower($this->EE->TMPL->fetch_param('to'));
	
	
	// validate now
	 // no conversions for base currency
		// check if any posted data exists?
	if($to == "inr"){
	$conversion_rate = 1;
	}else{
	$conversion_rate = $this->EE->siteconfig->item($to.'_'.$from);	
	}
	$conversion_rate = intval($conversion_rate);

	$price = $number/$conversion_rate;
	
//	return number_format($price,2);
	return $this->EE->number->format($price).'/'.$to; 
	}
	
	
	
	
	public function converted(){
	//load cartthrob core
		
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
		$this->EE->cartthrob = Cartthrob_core::instance('ee', array(
				'cart' => $params['cart'],
			));
			
	// the customers currency currently selected.		
	$current_currency = $params['cart']['customer_info']['currency_code'];
	$current_currency = strtolower($current_currency);
	$base_currency = "inr";
	
	
	$number = $this->EE->TMPL->fetch_param('price');
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
	
	$price = number_format($number/$conversion_rate,2);
	
	$prefix = $this->get_prefix($params['cart']['customer_info']['currency_code']);
	
	return $prefix.''.$price;
	}
	
	
	public function get_prefix($currency_code){	
	$currency_code = strtolower($currency_code);	
	$prefix = ""; 	
	switch ($currency_code)
			{
				case "eur":
					$prefix = "&#8364;";
					break;
				case "usd":
					$prefix = "$";
					break;
				case "gbp":
					$prefix = "&#163;";
					break;
				case "aud":
					$prefix = "$";					
					break;				
				default: $prefix = "&#8377;"; 
			}
	return $prefix;		
	}
	
	
	
	
	
	/*
	public function form(){
	 // Find the entry_id of the teacher to add the form for  
	$return	  = $this->EE->TMPL->fetch_param('return');
   	// Build an array to hold the form's hidden fields
    $hidden_fields = array(     
		"ACT" => $this->EE->functions->fetch_action_id( 'Cartthrob_currency_select', 'cartthrob_currency_select_process' ),
		"RET" => $return
    );
	// Build an array with the form data
    $form_data = array(
        "id" => $this->EE->TMPL->form_id,
        "class" => $this->EE->TMPL->form_class,
        "hidden_fields" => $hidden_fields
    );

    // Fetch contents of the tag pair, ie, the form contents
    $tagdata = $this->currency_select();

    $form = $this->EE->functions->form_declaration($form_data) . 
        $tagdata . "</form>";

    return $form;
	
	}
	*/
	
	
	
	
	
	
	/*
	// process the form now
	public function cartthrob_currency_select_process(){
	$this->EE->load->library('number');		
	return $this->EE->cartthrob->cart->set_config('number_format_defaults_prefix', $prefix);	
	}
	*/
	
	
	
}
/* End of file mod.cartthrob_currency_select.php */
/* Location: /system/expressionengine/third_party/cartthrob_currency_select/mod.cartthrob_currency_select.php */