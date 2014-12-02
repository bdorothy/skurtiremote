<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cartthrob_settings_model extends CI_Model
{
	private $cache;
	
	protected $default_settings = array();
	
	public function __construct($params = array())
	{
		include PATH_THIRD.'cartthrob/config/config.php';
		
		$this->default_settings = $config['cartthrob_default_settings'];
		
		if ( ! isset($params['settings']))
		{
			$params['settings'] = $this->get_settings();
		}
		
		foreach ($params['settings'] as $key => $value)
		{
			$this->config->set_item('cartthrob:'.$key, $value);
		}
	}
	
	/**
	 * loads the settings into CI's config object
	 * 
	 * @return void
	 */
	public function load_settings($settings = NULL)
	{
	}
	
	/**
	 * public access to the default settings
	 * 
	 * @return array    the default settings as defined in the default_settings @property
	 */
	public function default_settings()
	{
		return $this->default_settings;
	}
	
	/**
	 * Sets both the cache (which is referred to by reference in Cartthrob_core_ee)
	 * and the CI cache object's value (with a "cartthrob:" prefix)
	 * 
	 * @param string $key
	 * @param mixed $value
	 * 
	 * @return void
	 */
	public function set_item($key, $value = FALSE)
	{
		if (is_array($key))
		{
			foreach ($key as $k => $v)
			{
				$this->cache[$this->config->item('site_id')][$k] = $v;
				
				$this->config->set_item('cartthrob:'.$k, $v);
			}
		}
		else
		{
			$this->cache[$this->config->item('site_id')][$key] = $value;
			
			$this->config->set_item('cartthrob:'.$key, $value);
		}
	}
	
	/**
	 * get saved settings from the database and cache, and defaults where settings aren't defined
	 * 
	 * @return array    saved settings
	 */
	public function &get_settings($site_id = NULL)
	{
		if (is_null($site_id))
		{
			$site_id = $this->config->item('site_id');
		}
		
		if (isset($this->cache[$site_id]))
		{
			return $this->cache[$site_id];
		}
		
		$settings = $this->default_settings;
		
		// make sure the table exists first
		if ($this->db->table_exists('cartthrob_settings'))
		{
			$query = $this->db->where('site_id', $site_id)
					  ->get('cartthrob_settings');

			foreach ($query->result() as $row)
			{
				$config_key = 'cartthrob:'.$row->key;

				//xxxxxxxx it's been overridden in the config file <----- this isn't true
				// this stupid model ALSO sets ALL of the default / saved items to cartthrob:whatever... so basically 
				// it's impossible to save certain effing settings if they've already been saved once!!!!!
				/*
				if (isset($this->config->config[$config_key]))
				{
					$settings[$row->key] = $this->config->config[$config_key];
				}
				else
				{
					if ($row->serialized)
					{
						$data = @unserialize($row->value);
					}
					else
					{
						$data = $row->value; 
					}
 	
					$settings[ $row->key] =  $data;
 
				}
				*/ 
				if ($row->serialized)
				{
					$data = @unserialize($row->value);
				}
				else
				{
					$data = $row->value; 
				}
				$settings[ $row->key] =  $data;
			}

			$query->free_result();	
		}
		
		$this->cache[$site_id] = $settings;
		
		return $this->cache[$site_id];
	}
}
