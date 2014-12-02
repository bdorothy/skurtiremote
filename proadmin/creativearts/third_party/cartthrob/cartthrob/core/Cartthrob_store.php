<?php if ( ! defined('CARTTHROB_PATH')) Cartthrob_core::core_error('No direct script access allowed');

class Cartthrob_store extends Cartthrob_child
{
	private $products;
	private $plugins;
	
	public function plugin($class)
	{
		if ( ! $class)
		{
			return NULL;
		}
		
		if (isset($this->plugins[$class]))
		{
			return $this->plugins[$class];
		}
		
		return $this->plugins[$class] = Cartthrob_core::create_child($this->core, $class);
	}
	
	public function tax_rate()
	{
		if ($plugin = $this->plugin($this->config('tax_plugin')))
		{
			return $plugin->tax_rate();
		}
		
		return 0;
	}
	
	public function tax_name()
	{
		if ($plugin = $this->plugin($this->config('tax_plugin')))
		{
			return $plugin->tax_name();
		}
		
		return '';
	}
	
	public function config()
	{
		return $this->core->config(func_get_args());
	}
	
	public function set_config($key, $value = FALSE)
	{
		$this->core->set_config($key, $value);
		
		return $this;
	}
	
	public function override_config($override_config)
	{
		$this->core->override_config($override_config);
		
		return $this;
	}
	
	public function product($product_id)
	{
		if (isset($this->products[$product_id]))
		{
			return $this->products[$product_id];
		}
		
		if ($product = $this->core->get_product($product_id))
		{
			return $this->products[$product_id] = $product;
		}
		
		return FALSE;
	}
}