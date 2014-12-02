<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CartThrob Addons
 *
 * @package CartThrob2
 */
class Cartthrob_addons
{
	/**
	 * @var array container for addon module/plugin instances; class => object
	 */
	private $addons = array();
	
	/**
	 * @var array the list of available methods; method => class
	 */
	private $methods = array();
	
	/**
	 * @var object the last module/plugin instance checked in method_exists
	 */
	private $cached_addon;
	
	public function __construct()
	{
		$this->EE =& get_instance();
		
		$valid_addons = array();
		
		if ($this->EE->extensions->active_hook('cartthrob_addon_register'))
		{
			$valid_addons = $this->EE->extensions->call('cartthrob_addon_register', $valid_addons);
		}
 		foreach ($valid_addons as $short_name)
		{
			if (strpos($short_name, "cartthrob_") !== FALSE)
			{
				$short_name = str_replace("cartthrob_", "", $short_name); 
			}
			$paths = array(
				PATH_THIRD.'cartthrob_'.$short_name.'/mod.cartthrob_'.$short_name.'.php',
				PATH_THIRD.'cartthrob_'.$short_name.'/pi.cartthrob_'.$short_name.'.php',
			);
			
			foreach ($paths as $path)
			{
				if (@file_exists($path))
				{
					require $path;
					
					$this->register('Cartthrob_'.$short_name);
					
					break;
				}
			}
		}
	}
	
	public function method_exists($method)
	{
		if ( ! $class = $this->get_class_from_method($method))
		{
			return FALSE;
		}
		
		if ( ! $addon =& $this->get_addon_from_class($class))
		{
			return FALSE;
		}
		
		if ( ! method_exists($addon, $method))
		{
			return FALSE;
		}
		
		$this->cached_addon =& $addon;
		
		return TRUE;
	}
	
	/**
	 * call a cartthrob addon's method from the cartthrob module
	 * pass args via TMPL class
	 * 
	 * @param string $method name of the template tag method
	 * 
	 * @return mixed
	 */
	public function call($method)
	{
		if (is_null($this->cached_addon))
		{
			if ( ! $this->method_exists($method))
			{
				return;
			}
		}
		
		$result = $this->cached_addon->$method();
		
		$this->cached_addon = NULL;
		
		return $result;
	}
	
	/**
	 * register a module/plugin so that you can use it's tags with cartthrob
	 * 
	 * @param string|object $class the classname of the module/plugin
	 * 
	 * @return void
	 */
	public function register($class)
	{
		if (is_object($class))
		{
			$object = $class;
			
			$class = get_class($object);
		}
		else
		{
			$object = new $class;
		}
		
		$this->addons[$class] = $object;
		
		foreach (get_class_methods($class) as $method)
		{
			//"private" or magic method, skip
			if (strncmp($method, '_', 1) === 0)
			{
				continue;
			}
			
			$this->methods[$method] = $class;
		}
	}
	
	private function get_addon_from_class($class)
	{
		return isset($this->addons[$class]) ? $this->addons[$class] : NULL;
	}
	
	private function get_class_from_method($method)
	{
		return isset($this->methods[$method]) ? $this->methods[$method] : NULL;
	}
}