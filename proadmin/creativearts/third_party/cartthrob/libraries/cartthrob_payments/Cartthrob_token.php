<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_THIRD.'cartthrob/libraries/Cartthrob_payments.php';

class Cartthrob_token
{
	protected $token;
	protected $customer_id;
	protected $error_message = '';
	protected $offsite = FALSE; 
	
	public function __construct($params = array())
	{
		foreach (array_keys(get_object_vars($this)) as $key)
		{
			if (isset($params[$key]))
			{
				$this->$key = $params[$key];
			}
		}
	}
	
	public function __toString()
	{
		return $this->token();
	}
	
	public function token()
	{
		return $this->token;
	}
	
	public function customer_id()
	{
		return $this->customer_id;
	}
	
	public function error_message()
	{
		return $this->error_message;
	}
	
	public function offsite()
	{
		return $this->offsite; 
	}
	public function set_token($token)
	{
		$this->token = $token;
		
		return $this;
	}
	
	public function set_customer_id($customer_id)
	{
		$this->customer_id = $customer_id;
		
		return $this;
	}
	
	public function set_error_message($error_message)
	{
		$this->error_message = $error_message;
		
		return $this;
	}
	
	public function set_offsite($offsite=TRUE)
	{
		$this->offsite = $offsite;
		
		return $this;
	}
}