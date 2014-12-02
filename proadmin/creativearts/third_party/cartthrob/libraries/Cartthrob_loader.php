<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CI Wrapper for CartThrob
 *
 * Loads the settings, the session and then the cart
 *
 * @property Cartthrob_settings_model $cartthrob_settings_model
 * @property Cartthrob_session $cartthrob_session
 * @property Cart_model $cart_model
 * @property Customer_model $customer_model
 */
class Cartthrob_loader
{
	private $setup = array();
	
	public function __construct($params = array())
	{
		$this->EE =& get_instance();
		
		// need to check and see if CartThrob is actually installed first.
		$this->EE->load->library('addons');
		$modules = $this->EE->addons->get_installed();
		
		if ( ! isset($modules['cartthrob']['module_version']))
		{
			return; 
		}
		
		
		if ( ! isset($this->EE->cartthrob))
		{
			//if you don't provide a cart in the construct params (like an empty cart array),
			//initialize the session to get the cart
			if ( ! isset($params['cart']))
			{
				//load the settings into CI
				$this->EE->load->model('cartthrob_settings_model');
				
				//load the session
				$this->EE->load->library('cartthrob_session');
				
				//get the cart id from the session
				$cart_id = $this->EE->cartthrob_session->cart_id();
				
				$this->EE->load->model('cart_model');
				
				//get the cart data from the db
				$params['cart'] = $this->EE->cart_model->read_cart($cart_id);
				
				$this->EE->load->model('customer_model');
				
				$existing_customer_info = (isset($params['cart']['customer_info'])) ? $params['cart']['customer_info'] : NULL;
				
				$params['cart']['customer_info'] = $this->EE->customer_model->get_customer_info($existing_customer_info);
			}
			
			//load cartthrob core
			include_once PATH_THIRD.'cartthrob/cartthrob/Cartthrob.php';
			
			//normally we'd want to instantiate with a config array,
			//but the Cartthrob_core_ee driver overrides the use of the config array and uses the cartthrob_settings_model's config cache
			$this->EE->cartthrob = Cartthrob_core::instance('ee', array(
				'cart' => $params['cart'],
			));
		}
	}
	
	//@TODO deprecate
	public function setup(&$object)
	{
		if ( ! is_object($object))
		{
			return;
		}
		
		if ( ! in_array($object, $this->setup))
		{
			$this->setup[] =& $object;
		}
		
		$object->cartthrob =& $this->EE->cartthrob;
		$object->cart =& $this->EE->cartthrob->cart;
		$object->store =& $this->EE->cartthrob->store;
	}
	
	//@TODO deprecate
	public function setup_all($which = array())
	{
		if ( ! is_array($which))
		{
			$which = func_get_args();
		}
		
		foreach ($this->setup as &$object)
		{
			if ( ! $which || in_array('core', $which))
			{
				$object->cartthrob =& $this->EE->cartthrob;
			}
			
			if ( ! $which || in_array('cart', $which))
			{
				$object->cart =& $this->EE->cartthrob->cart;
			}
			
			if ( ! $which || in_array('store', $which))
			{
				$object->store =& $this->EE->cartthrob->store;
			}
		}
	}
}