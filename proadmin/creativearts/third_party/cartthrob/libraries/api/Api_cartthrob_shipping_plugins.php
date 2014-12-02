<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_THIRD.'cartthrob/libraries/api/Api_cartthrob_plugins'.EXT;

class Api_cartthrob_shipping_plugins extends Api_cartthrob_plugins
{
	protected $shipping_plugin;
	protected $shipping_plugins;
	
	public function __construct()
	{
		$this->EE =& get_instance();
		
		
		$this->reset_shipping_plugin();
		
 		$this->EE->load->library('cartthrob_shipping_plugins');
	}
	public function reset_shipping_plugin()
	{
		$this->shipping_plugin = $this->EE->cartthrob->store->config('shipping_plugin');
		$this->set_plugin($this->EE->cartthrob->store->config('shipping_plugin'));

		return $this;
	}
	
	public function title()
	{
		return ($this->plugin()) ? $this->plugin()->title : '';
	}
	
	public function html()
	{
		return ($this->plugin()) ? $this->plugin()->html : '';
	}
	
	public function overview()
	{
		return ($this->plugin()) ? $this->plugin()->overview : '';
	}
	
	public function note()
	{
		return ($this->plugin()) ? $this->plugin()->note : '';
	}
	
	public function required_fields()
	{
		return ($this->plugin()) ? $this->plugin()->required_fields : array();
	}
	
	public function shipping_options()
	{
		$shipping_options = method_exists($this->plugin(), 'plugin_shipping_options') ? $this->plugin()->plugin_shipping_options() : array();
		// making sure that all shipping options returned at least have consistent minimum info
		foreach ($shipping_options as $key=> $value)
		{
			$default_options = array(
				'rate_title'	=> 'default', //@TODO make this run off of lang files. 
				'rate_price'	=> 0,
				'price'			=> 0,
				'rate_short_name'	=> 'default', // @TODO make this run off of lang files. 
				); 
			// if the option is an array, we'll make sure it has the defaults'
			if (is_array($value))
			{
				array_merge($default_options, $value);
				$shipping_options[$key]= $value; 
			}
			else
			{
				// if it's not an array, we don't want it. Delete it. 
				unset($shipping_options[$key]); 
			}
		}
		if (count($shipping_options > 0))
		{
			return $shipping_options;
		}
		return NULL; 
	}
	
	public function get_live_rates($rate = NULL)
	{
		return (method_exists($this->plugin(), 'get_live_rates')) ? $this->plugin()->get_live_rates($rate) : FALSE;
	}
	
	public function set_shipping($cost = NULL)
	{
		return (method_exists($this->plugin(), 'set_shipping')) ? $this->plugin()->set_shipping($cost) : FALSE;
	}
	
	public function default_shipping_option()
	{
		return (method_exists($this->plugin(), 'default_shipping_option')) ? $this->plugin()->default_shipping_option() : '';
	}
	
	
}