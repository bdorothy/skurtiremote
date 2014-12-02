<?php
abstract class Cartthrob_payment_gateway
{
	public $title = '';
	public $affiliate = '';
	public $overview = '';
	public $settings = array();
	public $required_fields = array();
	public $fields = array();
	public $hidden = array();
	public $card_type = array();
	public $html = '';
	public $language_file = FALSE;
	public $form_extra = '';
	public $nameless_fields = array();
	public $extra_fields = array(); 
	//the core library for payments,
	//in this case, it's Cartthrob_payments
	protected $core;
	
	public function charge($credit_card_number)
	{
		return $this->process_payment($credit_card_number);
	}
	
	//deprecated, use charge()
	public function process_payment($credit_card_number)
	{
		return TRUE;
	}
	
	//so you can call cartthrob_payments methods more easily
	public function __call($method, $args)
	{
		try
		{
			if ( ! method_exists($this->core, $method))
			{
				throw new Exception('Call to undefined method %s::%s() in %s on line %s');
			}
			else if ( ! is_callable(array($this->core, $method)))
			{
				throw new Exception('Call to private method %s::%s() in %s on line %s');
			}
		}
		catch(Exception $e)
		{
			$backtrace = $e->getTrace();
			$backtrace = $backtrace[1];
			return trigger_error(sprintf($e->getMessage(), $backtrace['class'], $backtrace['function'], $backtrace['file'], $backtrace['line']));
		}
		
		return call_user_func_array(array($this->core, $method), $args);
	}
	
	public function initialize()
	{
	}
	
	public function plugin_settings($key, $default = FALSE)
	{
		$settings = $this->core->config(get_class($this).'_settings');
		
		if ($key === FALSE)
		{
			return ($settings) ? $settings : $default;
		}
		
		return (isset($settings[$key])) ? $settings[$key] : $default;
	}
	
	public function set_core($core)
	{
		if (is_object($core))
		{
			$this->core = $core;
		}
		
		return $this;
	}
}