<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (! class_exists('Get_settings'))
{
	class Get_settings
	{
		/**
		* @var string either module, extension, or child
		*/
		public $type = 'module';

		/**
		* @var string set this if using 'child' type (see Get_settings::$type)
		*/
		public $parent_namespace;

		public function __construct()
		{
			$this->EE =& get_instance();
		}

		public function extension_settings($namespace, $by_site_id = FALSE)
		{
			if (isset($this->EE->session->cache[$namespace]['settings'][$this->EE->config->item('site_id')]))
			{	
				return $this->EE->session->cache[$namespace]['settings'][$this->EE->config->item('site_id')];
			}

			$query = $this->EE->db->where('class', ucwords($namespace).'_ext')
				->limit(1)
				->get('extensions');

			$this->EE->session->cache[$namespace]['settings'][$this->EE->config->item('site_id')] = array();

			if ($query->num_rows() > 0)
			{
				$settings = @unserialize($query->row('settings'));

				$query->free_result();

				if ($by_site_id)
				{
					$settings = isset($settings[$this->EE->config->item('site_id')]) ? $settings[$this->EE->config->item('site_id')] : array();
				}

				$this->EE->session->cache[$namespace]['settings'][$this->EE->config->item('site_id')] = $settings ? $settings : array();
			}

			return $this->EE->session->cache[$namespace]['settings'][$this->EE->config->item('site_id')];
		}

		/**
		* Use when your settings are part of another addon's settings
		*
		* ex. $this->EE->get_settings->child_settings('cartthrob', 'cartthrob_wish_list')
		* 
		* @return Type    Description
		*/
		public function child_settings($parent_namespace, $namespace, $saved_settings = FALSE)
		{
			$parent_settings = $this->settings($parent_namespace, $saved_settings);

			return isset($parent_settings[$namespace]) ? $parent_settings[$namespace] : array();
		}

		// looks for $namespace.default_settings config array
		// looks in db for $namespace._settings
		// looks in third_party/$namespace/config/config.php
		public function settings($namespace, $saved_settings = FALSE)
		{
			if ($this->type === 'extension')
			{
				return $this->extension_settings($namespace);
			}
			else if ($this->type === 'child')
			{
				return $this->child_settings($this->parent_namespace, $namespace, $saved_settings);
			}

			$settings = array(); 

			if ( ! $saved_settings)
			{
				if (isset($this->EE->session->cache[$namespace]['settings'][$this->EE->config->item('site_id')]))
				{	
					return $this->EE->session->cache[$namespace]['settings'][$this->EE->config->item('site_id')];
				}
				@include  PATH_THIRD.$namespace.'/config'.EXT; 

				$this->EE->config->load(PATH_THIRD.$namespace.'/config/config'.EXT, FALSE, TRUE);

				$settings = $this->EE->config->item($namespace.'_default_settings');
				if (empty($settings))
				{
					@include PATH_THIRD.$namespace.'/config/config'.EXT; 
					if (!empty($config[$namespace."_default_settings"]))
					{
						$settings = $config[$namespace."_default_settings"]; 
					}
				}
			}

			if ($this->EE->db->table_exists($namespace.'_settings'))
			{
				foreach ($this->EE->db->where('site_id', $this->EE->config->item('site_id'))->get($namespace.'_settings')->result() as $row)
				{
					if ($row->serialized)
					{
						$row->value = unserialize($row->value);
					}

					$settings[$row->key] = $row->value;
				}
				

				// don't want to set the cache to ON if there are no settings. 
				if ($settings)
				{
					$this->EE->session->cache[$namespace]['settings'][$this->EE->config->item('site_id')] = $settings;
				}
			}
			return $settings;
		}
		
		public function get_setting($namespace, $setting_name)
		{
			$settings = $this->settings($namespace); 
			if (isset($settings[$setting_name]))
			{
				return $settings[$setting_name]; 
			}
			return NULL; 
		}
		public function get($namespace, $setting_name)
		{
			$settings = $this->settings($namespace); 
			if (isset($settings[$setting_name]))
			{
				return $settings[$setting_name]; 
			}
			return NULL; 
		}
	}
}