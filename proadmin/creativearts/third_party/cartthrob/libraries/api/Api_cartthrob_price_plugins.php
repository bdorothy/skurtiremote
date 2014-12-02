<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Api_cartthrob_price_plugins extends Api
{
	var $default_plugin;
	var $plugins = array();
	var $paths = array();
	var $current_plugin = FALSE;
	
	function Api_cartthrob_price_plugins()
	{
		parent::Api();
		
		$this->load_default_plugin();
		
		$this->paths[] = PATH_THIRD.'cartthrob/price_plugins/';
		
		if ($this->EE->config->item('cartthrob_third_party_path'))
		{
			$this->paths[] = rtrim($this->EE->config->item('cartthrob_third_party_path'), '/').'/price_plugins/';
		}
		else
		{
			$this->paths[] = PATH_THIRD.'cartthrob/third_party/price_plugins/';
		}
	}
	
	function add_path($path)
	{
		if ( ! is_dir($path))
		{
			return FALSE;
		}
		
		if ( ! in_array($path, $this->paths))
		{
			$this->paths[] = $path;
		}
		
		return TRUE;
	}
	
	function set_current_plugin($classname)
	{
		if ( ! isset($this->plugins[$classname]))
		{
			return FALSE;
		}
		
		$this->current_plugin = $classname;
		
		return TRUE;
	}
	
	function &default_plugin()
	{
		return $this->default_plugin;
	}
	
	function default_global_settings($key = FALSE)
	{
		if ( ! $this->default_plugin)
		{
			return FALSE;
		}
		
		$global_settings = $this->default_plugin->global_settings();
		
		if ($key !== FALSE)
		{
			return $global_settings;
		}
		
		return (isset($global_settings[$key])) ? $global_settings[$key] : FALSE;
	}
	
	function load_default_plugin()
	{
		require_once(PATH_THIRD.'cartthrob/price_plugins/Cartthrob_price_plugin'.EXT);
		
		$this->default_plugin = new Cartthrob_price_plugin;
	}
	
	function &current_plugin()
	{
		if ( ! $this->current_plugin)
		{
			return FALSE;
		}
		
		return $this->plugin($this->current_plugin);
	}
	
	function load_plugins($classes = FALSE, $add = FALSE)
	{
		if ( ! $add)
		{
			$this->plugins = array();
		}
		
		if ($classes !== FALSE)
		{
			if ( ! is_array($classes))
			{
				$classes = array($classes);
			}
			
			foreach ($classes as $key => $value)
			{
				if ( ! preg_match('/^Cartthrob_/'))
				{
					$classes[$key] = 'Cartthrob_'.$value;
				}
			}
		}
		
		$this->EE->load->helper('file');
		
		foreach ($this->paths as $path)
		{
			if ( ! is_dir($path))
			{
				continue;
			}
			
			foreach (get_filenames($path, TRUE) as $file)
			{
				if ( ! preg_match('/^cartthrob\.(.*)/', basename($file, EXT), $match))
				{
					continue;
				}
				else
				{
					$type = $match[1];
					
					$classname = 'Cartthrob_'.$type;
				}
				
				if ($classes !== FALSE && ! in_array($classname, $classes))
				{
					continue;
				}
				
				require_once($file);
				
				if ( ! class_exists($classname))
				{
					continue;
				}
				
				$plugin = new $classname;
				
				$this->plugins[$type] = $plugin;
			}
		}
	}
	
	function &plugins()
	{
		return $this->plugins;
	}
	
	function &plugin($classname)
	{
		foreach ($this->plugins as $plugin)
		{
			if ($plugin->classname == $classname)
			{
				return $plugin;
			}
		}
		
		return FALSE;
	}
	
	function set_plugin_data($classname, $data)
	{
		if ( ! isset($this->plugins[$classname]))
		{
			return FALSE;
		}
		
		$this->plugins[$classname]->data = $data;
		
		return TRUE;
	}
}