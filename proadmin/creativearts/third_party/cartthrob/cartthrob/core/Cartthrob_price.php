<?php if ( ! defined('CARTTHROB_PATH')) Cartthrob_core::core_error('No direct script access allowed');

abstract class Cartthrob_price extends Cartthrob_child
{
	var $title = '';
	var $settings = array();
	var $data = array();
	var $classname = '';
	var $type = '';
	var $markup = FALSE;
	
	function __construct()
	{
		$this->EE =& get_instance();
		$this->classname = get_class($this);
		$this->type = preg_replace('/^Cartthrob_/', '', $this->classname);
	}
	
	function data($key)
	{
		return (isset($this->data[$key])) ? $this->data[$key] : FALSE;
	}
	
	function adjust_price($price)
	{
		return $price;
	}
}
