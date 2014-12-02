<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (class_exists('Simple_cache')) return;

class Simple_cache
{
	public function __construct()
	{
		$this->EE =& get_instance();
	}
	
	public function set($name, $data, $dir = '')
	{
		if ( ! $dir)
		{
			$dir = APPPATH.'cache';
		}
		
		if (strpos($name, '/') !== FALSE)
		{
			$subdir = pathinfo($name, PATHINFO_DIRNAME);
			
			$name = pathinfo($name, PATHINFO_BASENAME);
			
			if ($subdir && substr($subdir, 0, 1) !== '/')
			{
				$subdir = '/'.$subdir;
			}
			
			$dir .= $subdir;
		}
		
		$this->EE->load->helper('file');
		
		if ( ! is_dir($dir))
		{
			mkdir($dir, DIR_WRITE_MODE);
			@chmod($dir, DIR_WRITE_MODE);	
		}
		
		$cache = array(
			'timestamp' => time(),
			'data' => $data
		);

		if (write_file($dir.'/'.$name, serialize($cache)))
		{
			@chmod($dir.'/'.$name, FILE_WRITE_MODE);			
		}
		
		return $data;
	}
	
	public function get($name, $cache_expire = 86400, $dir = '')
	{
		if ( ! $dir)
		{
			$dir = APPPATH.'cache';
		}
			
		if (substr($name, 0, 1) === '/')
		{
			$name = substr($name, 1);
		}
		
		$this->EE->load->helper('file');
		
		$contents = read_file($dir.'/'.$name);
	
		if ($contents !== FALSE)
		{
			$cache = unserialize($contents);

			if (($cache['timestamp'] + $cache_expire) > time())
			{
				return $cache['data'];
			}
		}
		
		return FALSE;
	}
}