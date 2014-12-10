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
 * Low Search Category Filter Extension
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Extension
 * @author		Sixthsense
 * @link		http://www.sixth.co.in
 */

class Ls_mtt_search_category_filter_ext {
	
	public $settings 		= array();
	public $description		= 'Alters the categories based on complex filters';
	public $docs_url		= 'http://www.sixth.co.in';
	public $name			= 'Low Search Category Filter';
	public $settings_exist	= 'n';
	public $version			= '3.2.0';
	
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
			'mtt_search_catch_search'	=> 'ls_mtt_search_catch_search'			
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
	 * test2
	 *
	 * @param 
	 * @return 
	 */
	public function ls_mtt_search_catch_search($data)
	{

	// get all the categories
	if(isset($data['category'])){
	$categories = explode('|',$data['category']);

	// GET MAIN CATEGORY ID FOR THIS SEARCH
	if(isset($data['mc'])){
	$mc = $data['mc'];
	
	// check if main category is parent ?
	// pending this thing
	
	// remove the main category (if is parent) from the array
	if(($key = array_search($mc, $categories)) !== false) {
    unset($categories[$key]);
	}
	$categories = implode('|',$categories);
	$data['category'] = $categories;
	
	}
	///	
	}
	elseif(isset($data['mc'])){
	$mc = $data['mc'];
	$data['category'] = ($mc);
	}
	
	
	if (isset($data['r:rp'])){
	// NOW MANIPULATE THE PRICE
	
	// which currency code the customer is using?
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

	$currency_code = $params['cart']['customer_info']['currency_code'];
	$price = $data['r:rp'];
	$price = explode('|',$price);
	
	$from = $price[0];
	$to = $price[1];
	
	$from = $this->converted_numeric($currency_code,$from);
	$to = $this->converted_numeric($currency_code,$to);
	
	$new_price = implode('|',array($from,$to));
	$data['r:rp'] = $new_price;
	}
	//
	
	
	
	if (isset($data['r:wp'])){
	// NOW MANIPULATE THE PRICE
	
	// which currency code the customer is using?
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

	$currency_code = $params['cart']['customer_info']['currency_code'];
	$price = $data['r:wp'];
	$price = explode('|',$price);
	
	$from = $price[0];
	$to = $price[1];
	
	$from = $this->converted_numeric($currency_code,$from);
	$to = $this->converted_numeric($currency_code,$to);
	
	$new_price = implode('|',array($from,$to));
	$data['r:wp'] = $new_price;
	}
	//
	
	
	// unset the submit button
	if(isset($data['go'])){	
	unset($data['go']);
	}
	
	return $data;	
	}

	// ----------------------------------------------------------------------
	
	/**
	 * test1
	 *
	 * @param 
	 * @return 
	 */
	public function converted_numeric($currency_code,$price){
	// the customers currency currently selected.		
	$current_currency = strtolower($currency_code);	
	$base_currency = "inr";
	
	
	$number = $price;
	
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
	
	$price = $number * $conversion_rate;
	
	
	return $price;
	}
	
	
	
	
	

	// ----------------------------------------------------------------------

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

/* End of file ext.low_search_category_filter.php */
/* Location: /system/expressionengine/third_party/low_search_category_filter/ext.low_search_category_filter.php */