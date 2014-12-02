<?php if ( ! defined('CARTTHROB_PATH')) Cartthrob_core::core_error('No direct script access allowed');
class Cartthrob_hooks extends Cartthrob_child
{
	public $hooks = array(), $hook, $value, $end = FALSE;
	
	public $enabled = TRUE;
	
	public function disable()
	{
		$this->enabled = FALSE;
	}
	
	public function enable()
	{
		$this->enabled = TRUE;
	}
	
	public function initialize($hooks = array(), $defaults = array())
	{
		return $this->set_hooks($hooks);
	}
	
	public function set_hook($hook)
	{
		$this->hook = $hook;
		
		return $this;
	}
	
	public function set_hooks($hooks)
	{
		if (is_array($hooks))
		{
			$this->hooks = $hooks;
		}
		
		return $this;
	}
	
	public function add_hook($hook)
	{
		if (is_array($hook))
		{
			foreach ($hooks as $hook)
			{
				$this->add_hook($hook);
			}
		}
		else
		{
			$this->hooks[] = $hook;
		}
		
		return $this;
	}
	
	public function value()
	{
		return $this->value;
	}
	
	public function end()
	{
		return $this->end;
	}
	
	public function set_end($end = TRUE)
	{
		$this->end = $end;
		
		return $this;
	}
	
	public function set_value($value)
	{
		$this->value = $value;
		
		return $this;
	}
	
	public function run()
	{
		$this->end = FALSE;
		$this->value = NULL;
		
		if (in_array($this->hook, $this->hooks) && method_exists($this->core, $this->hook))
		{
			$args = func_get_args();
			
			if (count($args) > 0)
			{
				$this->set_value(call_user_func_array(array($this->core, $this->hook), $args));
			}
			else
			{
				//a little faster
				$this->set_value($this->core->{$this->hook}());
			}
			
			return TRUE;
		}
		
		return FALSE;
	}
}