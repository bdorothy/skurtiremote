<?php if ( ! defined('CARTTHROB_PATH')) Cartthrob_core::core_error('No direct script access allowed');

abstract class Cartthrob_tax extends Cartthrob_child
{
	public $title = '';
	public $note = '';
	public $overview = '';
	public $html = '';
	public $settings = array();
	
	public function initialize($params = array(), $defaults = array())
	{
		return $this;
	}
	
	public function plugin_settings($key, $default = FALSE)
	{
		$settings = $this->core->store->config(get_class($this).'_settings');
		
		if ($key === FALSE)
		{
			return ($settings) ? $settings : $default;
		}
		
		return (isset($settings[$key])) ? $settings[$key] : $default;
	}
	
	abstract public function get_tax($price);
}