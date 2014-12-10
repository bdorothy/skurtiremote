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
 * Currency Select Module Front End File
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Sixthsense
 * @link		http://www.sixth.co.in
 */

class Currency_select {
	
	public $return_data;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
	}
	
	// ----------------------------------------------------------------

	public function select_list(){			
		$this->EE->load->helper('form');
		$name = ($this->EE->TMPL->fetch_param('name')) ? $this->EE->TMPL->fetch_param('name') : 'currency_code';		
		$currencies = array(
		'INR' => '&#8377; INR',
		'USD' => '$ USD',
		'GBP' => '&#163; GBP',
		'EUR' => '&#8364; EUR',
		'AUD' => '$ AUD',
		'CAD' => '$ CAD',
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
	
	
	
	public function arithmatic(){
	$num1= $this->EE->TMPL->fetch_param('num1');
	$num2= $this->EE->TMPL->fetch_param('num2');
	$price = $num1*$num2;	
	return floatval($price);
	}
	
	// this is for price slider setting max price as per customers currency
	public function price_max($wholesale="no",$retail="yes"){
	$wholesale = $this->EE->TMPL->fetch_param('wholesale');
	$show_retail = $this->EE->TMPL->fetch_param('show_retail');
	
	if ($wholesale == "yes"){
	ee()->db->select('GREATEST(MAX(field_id_2), MAX(field_id_83)) as field_id_2'); // to change this later with site config library
	}else{
	ee()->db->select_max('field_id_2'); 
	}
	$query = ee()->db->get('exp_channel_data');	
	if ($query->num_rows() > 0){
	   foreach ($query->result() as $row){
	$max_price =  $row->field_id_2; 
	   }
	} 
	$max_price =  $this->converted_numeric($max_price);	
	$max_price = ceil($max_price / 10) * 10;
	return $max_price;
	}
	
	
	
	public function price_to(){	
	$wholesale = $this->EE->TMPL->fetch_param('wholesale');
	if ($wholesale == "yes"){
	$price = explode('|',ee()->input->get_post('r:wp'));	
	}else{
	$price = explode('|',ee()->input->get_post('r:rp'));	
	}
	
	if(isset($price[1])){
	$price_to = $this->converted_numeric($price[1]);
	}else{
	$price_to = $this->price_max();
	}
	return $price_to;
	}
	
	public function price_from(){
	$wholesale = $this->EE->TMPL->fetch_param('wholesale');
	if ($wholesale == "yes"){
	$price = explode('|',ee()->input->get_post('r:wp'));	
	}else{
	$price = explode('|',ee()->input->get_post('r:rp'));
	}
	$price_from = $this->converted_numeric($price[0]);
	return $price_from;
	}
	
	
	public function converted_numeric($number = ""){
	
	// the customers currency currently selected.		
	$current_currency = strtolower($this->get_currency_code());	
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
	
	$conversion_rate = floatval($conversion_rate);
	$price = $number/$conversion_rate;
	$price = round($price,2);//round($price * 1)/1; number_format((float)(round($price * 2)/2), 2, '.', '')
	//$price = ceil($price / 10) * 10;
	return $price;
	}
	
	
	public function converted_prefix(){	
	$currency_code = $this->get_currency_code();
	return $prefix = $currency_code.' '.$this->get_prefix($currency_code);
	}
	
	public function converted_symbol(){	
	$currency_code = $this->get_currency_code();
	return $prefix = $this->get_prefix($currency_code);
	}
	
	// show converted currency_code

	public function converted(){
	return ($this->converted_prefix().' '.$this->converted_numeric());
	}
	
	// wholesale converted_numeric
	public function wholesale_converted(){
	$wp = ee()->TMPL->fetch_param('wp');
	if ($wp != 0 && $wp != ""){
	return ($this->converted_prefix().' '.$this->converted_numeric($wp));
	}else{
	// calculate all the related products price total	
	$entry_id = ee()->TMPL->fetch_param('entry_id');
	$query = ee()->db->query('
	select sum(ls_channel_data.field_id_2) as wp
	from exp_relationships, ls_channel_data
	where exp_relationships.parent_id = 121 
	and exp_channel_data.entry_id = ls_relationships.child_id');
	if ($query->num_rows() > 0){	
	$row = $query->row(); 
	$wp =  $row->wp;
	} 	
	return ($this->converted_prefix().' '.$this->converted_numeric($wp));
	}
	}
	
	
	// wholesale converted_numeric
	public function wholesale_converted_numeric(){
	$wp = ee()->TMPL->fetch_param('wp');
	if ($wp != 0 && $wp != ""){
	return $wp;
	}else{
	// calculate all the related products price total	
	$entry_id = ee()->TMPL->fetch_param('entry_id');
	$query = ee()->db->query('
	select sum(ls_channel_data.field_id_2) as wp
	from exp_relationships, ls_channel_data
	where exp_relationships.parent_id = 121 
	and exp_channel_data.entry_id = ls_relationships.child_id');
	if ($query->num_rows() > 0){	
	$row = $query->row(); 
	$wp =  $row->wp;
	} 	
	return $wp;
	}
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
				case "cad":
					$prefix = "$";					
					break;		
				default: $prefix = "&#8377;"; 
			}
	return $prefix;		
	}
	
	
	
	public function get_currency_code(){
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

	return $params['cart']['customer_info']['currency_code'];
	}
	
	
	
	
	public function savings(){
	$op = $this->EE->TMPL->fetch_param('op');
	$sp = $this->EE->TMPL->fetch_param('sp');
	$percent = 100 - ($sp/$op)*100;
	return round($percent,0).'%	';
	}
	
}
/* End of file mod.currency_select.php */
/* Location: /system/expressionengine/third_party/currency_select/mod.currency_select.php */