<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_THIRD.'cartthrob/libraries/api/Api_cartthrob_plugins'.EXT;

class Api_cartthrob_discount_plugins extends Api_cartthrob_plugins
{
	public function get_plugins()
	{
		$this->EE->load->helper(array('data_formatting', 'file'));
		
		$plugins = array();
	
		include_once PATH_THIRD.'cartthrob/cartthrob/Cartthrob.php';
		require_once CARTTHROB_CORE_PATH.'Cartthrob_child.php';
		require_once CARTTHROB_CORE_PATH.'Cartthrob_discount.php';
	
		$paths[] = CARTTHROB_DISCOUNT_PLUGIN_PATH;
		
		if ($this->EE->config->item('cartthrob_third_party_path'))
		{
			$paths[] = rtrim($this->EE->config->item('cartthrob_third_party_path'), '/').'/discount_plugins/';
		}
		else
		{
			$paths[] = PATH_THIRD.'cartthrob/third_party/discount_plugins/';
		}
		
		$language = set($this->EE->session->userdata('language'), $this->EE->input->cookie('language'), $this->EE->config->item('deft_lang'), 'english');
		
		foreach ($paths as $i => $path)
		{
			if ( ! is_dir($path))
			{
				continue;
			}
			
			$lang_path = ($i !== 0) ? realpath($path.'../').'/' : FALSE;
			
			foreach (get_filenames($path, TRUE) as $file)
			{
				$class = basename($file, EXT);
				
				if (strpos($class, 'Cartthrob_discount_') !== 0 || strpos($class, '~') !== FALSE)
				{
					continue;
				}
				
				//exclude the first path, which is the base cartthrob plugin path
				if ($i !== 0)
				{
					if ($language !== 'english' && file_exists($path.'../language/'.$language.'/'.$class.'_lang.php'))
					{
						$this->EE->lang->load(strtolower($class), $language, FALSE, TRUE, $path.'../', FALSE);
					}
					else if (file_exists($path.'../language/english/'.$class.'_lang.php'))
					{
						$this->EE->lang->load(strtolower($class), 'english', FALSE, TRUE, $path.'../', FALSE);
					}
				}
				
				$plugin = $this->EE->cartthrob->create_child($this->EE->cartthrob, $this->EE->cartthrob->get_class($class));
				
				$plugins[$class] = get_object_vars($plugin);
			}
		}
		
		return $plugins;
	}
	
	public function set_plugin_settings($plugin_settings)
	{
		if ($this->plugin)
		{
			$this->plugin->plugin_settings = $plugin_settings;
		}
		
		return $this;
	}
	
	public function global_settings($key = FALSE)
	{
		if ($key === FALSE)
		{
			return Cartthrob_discount::$global_settings;
		}
		
		return (isset(Cartthrob_discount::$global_settings[$key])) ? Cartthrob_discount::$global_settings[$key] : FALSE;
	}
}