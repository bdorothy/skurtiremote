<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CreativeArts - by LiveStore
 *
 * @package		CreativeArts
 * @author		LiveStore Dev Team
 * @copyright	Copyright (c) 2003 - 2012, LiveStore, Inc.
 * @license		http://creativearts.com/user_guide/license.html
 * @link		http://creativearts.com
 * @since		Version 2.0
 */
 
// ------------------------------------------------------------------------

/**
 * CreativeArts Stats Library
 *
 * @package		CreativeArts
 * @subpackage	Core
 * @category	Libraries
 * @author		LiveStore Dev Team
 * @link		http://creativearts.com
 */

class EE_Ls_currency_calculator {


	// --------------------------------------------------------------------
	
	public function converted_numeric($number = "",$current_currency = ""){	
	// the customers currency currently selected.	
	if($current_currency == ""){
	$current_currency = strtolower($this->get_currency_info('currency_code'));
	}	
	
	$base_currency = "inr";
	if($number == ""){
	$number = ee()->TMPL->fetch_param('price');
	}
	$number = sanitize_number($number);
	// get the site config library
	ee()->load->add_package_path(PATH_THIRD.'siteconfig/');
	ee()->load->library('siteconfig');	
	if($current_currency == "inr"){
	$conversion_rate = 1;
	}else{
	$conversion_rate = ee()->siteconfig->item($current_currency.'_'.$base_currency);	
	}	
	$conversion_rate = floatval($conversion_rate);
	$price = $number/$conversion_rate;
	$price =  round($price,2);//number_format((float)(round($price * 2)/2), 2, '.', '');
	//$price = ceil($price / 10) * 10;
	return $price;
	}
	
	
	
	
	public function get_currency_info($get){
	ee()->load->add_package_path(PATH_THIRD.'cartthrob');		
	ee()->load->model('customer_model');		
	//load the settings into CI
	ee()->load->model('cartthrob_settings_model');				
	//load the session
	ee()->load->library('cartthrob_session');				
	//get the cart id from the session
	$cart_id = ee()->cartthrob_session->cart_id();			
	ee()->load->model('cart_model');				
	$params['cart'] = ee()->cart_model->read_cart($cart_id);				
	$existing_customer_info = (isset($params['cart']['customer_info'])) ? $params['cart']['customer_info'] : NULL;
	$params['cart']['customer_info'] = ee()->customer_model->get_customer_info($existing_customer_info);		
	//normally we'd want to instantiate with a config array,
	//but the Cartthrob_core_ee driver overrides the use of the config array and uses the cartthrob_settings_model's config cache
	return $params['cart']['customer_info'][$get];
	}
	

}