<?php if ( ! defined('CARTTHROB_PATH')) Cartthrob_core::core_error('No direct script access allowed');

abstract class Cartthrob_child
{
	protected $core;
	protected $defaults = array();
	protected $errors = array();
	
	public function initialize($params = array(), $defaults = array())
	{
		$this->set_defaults($defaults);
		
		$this->prepare_params($params);
		
		foreach ($this->defaults as $key => $value)
		{
			$this->$key = (isset($params[$key])) ? $params[$key] : $value;
		}
	}
	
	public function errors()
	{
		return $this->errors;
	}
	
	public function set_error($error)
	{
		$this->errors[] = $error;
		
		return $this;
	}
	
	public function clear_errors()
	{
		$this->errors = array();
		
		return $this;
	}
	
	public function prepare_params(&$params)
	{
		return $this;
	}
	
	public function __call($method, $args)
	{
		if ($this->parent_class())
		{
			$_method = $this->parent_class().'_'.$method;
		}
		else
		{
			$_method = Cartthrob_core::get_class($this).'_'.$method;
		}

		try
		{
			if ( ! in_array($_method, get_class_methods($this->core) ))
			{
				throw new Exception('Call to undefined method %s::%s() in %s on line %s');
			}
			elseif ( ! is_callable(array($this->core, $_method)))
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
		
 		array_push($args, $this);
		
 		return call_user_func_array(array($this->core, $_method), $args);
	}
	
	public function defaults($key = FALSE)
	{
		if ($key === FALSE)
		{
			return $this->defaults;
		}
		
		return (isset($this->defaults[$key])) ? $this->defaults[$key] : FALSE;
	}
	
	public function default_keys()
	{
		return array_keys($this->defaults);
	}
	
	public function to_array()
	{
		$data = array();
		
		foreach ($this->defaults as $key => $value)
		{
			$data[$key] = $this->$key;
		}
		
		return $data;
	}
	
	public function is_null()
	{
		foreach ($this->defaults as $key => $value)
		{
			if ($this->$key !== $value)
			{
				return FALSE;
			}
		}
		
		return TRUE;
	}
	
	public function serialize()
	{
		return serialize($this->to_array());
	}
	
	public function unserialize($data)
	{
		$this->initialize(unserialize($data));
	}
	
	public function set_core(Cartthrob_core $core)
	{
		$this->core = $core;
	}
	
	public function set_defaults($key, $value = NULL)
	{
		if (is_array($key))
		{
			foreach ($key as $k => $v)
			{
				$this->set_defaults($k, $v);
			}
		}
		else
		{
			$this->defaults[$key] = $value;
		}
	}
	
	public function parent_class()
	{
		static $parent_class;
		
		if (is_null($parent_class))
		{
			$parent_class = FALSE;
			
			$classname = Cartthrob_core::get_class($this);
			
			$parts = explode('_', $classname);
			
			if (count($parts) > 1)
			{
				$parent_class = $parts[0];
			}
		}
		
		return $parent_class;
	}
	
	public function subclass()
	{
		static $subclass;
		
		if (is_null($subclass))
		{
			$subclass = FALSE;
		
			if ($parent_class = $this->parent_class())
			{
				$subclass = substr(Cartthrob_core::get_class($this), strlen($parent_class) + 1);
			}
		}
		
		return $subclass;
	}
}

/*
abstract class Cartthrob_child_serializable extends Cartthrob_child implements Serializable
{
	public function to_array()
	{
		return get_object_vars($this);
	}
	
	public function serialize()
	{
		return serialize($this->to_array());
	}
	
	public function unserialize($data)
	{
		$this->initialize(unserialize($data));
	}
}
*/