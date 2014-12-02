<?php if ( ! defined('CARTTHROB_PATH')) Cartthrob_core::core_error('No direct script access allowed');

abstract class Cartthrob_core
{
	public $cart;
	public $store;
	private $cache;
	public $hooks;
	protected $config = array();
	private $lang = array();
	private $errors = array();
	public $cart_defaults = array();
	public $item_defaults = array();
	public $product_defaults = array();
	public $customer_info_defaults = array();
	
	public static $_drivers = array('core', 'payment');
	public static $_plugins = array('shipping', 'discount', 'price', 'tax');
	public static $_utilities = array('registered_discount');
	public static $_plugin_paths = array();
	
	/* inherited methods */
	
	public function config($args = NULL)
	{
		$args = (is_array($args)) ? $args : func_get_args();
		
		$config = $this->config;
		
		foreach ($args as $key)
		{
			if (isset($config[$key]))
			{
				$config = $config[$key];
			}
			else
			{
				return FALSE;
			}
		}
		
		return $config;
	}
	
	public function set_config($key, $value = FALSE)
	{
		if (is_array($key))
		{
			foreach ($key as $k => $v)
			{
				$this->config[$k] = $v;
			}
		}
		else
		{
			$this->config[$key] = $value;
		}
		
		return $this;
	}
	
	public function override_config($override_config)
	{
		if ( ! is_array($override_config))
		{
			return;
		}
		
		$this->config = $this->array_merge($this->config, $override_config);
	}
	
	public function cache_pop($key)
	{
		$data = $this->cache($key);
		
		$this->clear_cache($key);
		
		return $data;
	}
	
	public function set_error($error)
	{
		$this->errors[] = $error;
		return $this;
	}
	
	public function set_errors($errors)
	{
		$this->errors = $errors;
		return $this;
	}
	
	public function errors()
	{
		return $this->errors;
	}
	
	public function clear_errors()
	{
		$this->errors = array();
		return $this;
	}
	
	public function lang($key)
	{
		return (isset($this->lang[$key])) ? $this->lang[$key] : $key;
	}
	
	public function cache($key)
	{
		if (is_array($key) && $key)
		{
			$cache =& $this->cache;
			
			foreach ($key as $value)
			{
				if ( ! isset($cache[$value]))
				{
					return FALSE;
				}
				
				$cache = $cache[$value];
			}
			
			return $cache;
		}
		
		return (isset($this->cache[$key])) ? $this->cache[$key] : FALSE;
	}
	
	public function set_cache($key, $value)
	{
		if ( ! is_array($key))
		{
			$key = array($key);
		}
		
		$cache =& $this->cache;
		
		foreach ($key as $k)
		{
			if ( ! isset($cache[$k]))
			{
				$cache[$k] = NULL;
			}
			
			$cache =& $cache[$k];
		}
		
		$cache = $value;
		
		return $this;
	}
	
	public function clear_cache($key = FALSE)
	{
		if ($key === FALSE)
		{
			$this->cache = array();
		}
		else if (is_array($key) && count($key) > 1)
		{
			$cache =& $this->cache;
			
			for ($i = 0; $i < count($key) - 1; $i++)
			{
				if ( ! isset($cache[$key[$i]]))
				{
					return;
				}
				
				$cache =& $cache[$key[$i]];
			}
			
			unset($cache[end($key)]);
		}
		else
		{
			unset($this->cache[$key]);
		}
	}
	
	/* static methods */
	
	public static function instance($driver, $params = array())
	{
		if (empty($driver))
		{
			Cartthrob_core::core_error('No driver specified.');
		}

		spl_autoload_register('Cartthrob_core::autoload');
		
		$driver = 'Cartthrob_core_'.$driver;
		
		$instance = new $driver($params);
		
		if (isset($params['config']))
		{
			$instance->config = $params['config'];
		}
		
		//the sequence is important here!
		
		$instance->set_child('hooks', $instance->hooks);
		
		$instance->set_child('store');
		
		$cart = (isset($params['cart'])) ? $params['cart'] : array();
		
		$instance->set_child('cart', $cart);

		spl_autoload_unregister('Cartthrob_core::autoload');
		
		return $instance;
	}
	
	public static function add_plugin_path($type, $path)
	{
		if ( ! isset(self::$_plugin_paths[$type]) || ! is_array(self::$_plugin_paths[$type]))
		{
			self::$_plugin_paths[$type] = array();
		}
		
		if ( ! in_array($path, self::$_plugin_paths[$type]))
		{
			self::$_plugin_paths[$type][] = $path;
		}
	}
	
	public static function create_child(Cartthrob_core $core, $class, $params = array(), $defaults = array())
	{
		spl_autoload_register('Cartthrob_core::autoload');
		
		//$child = self::create_object($name, array(), $path);
		$class = 'Cartthrob_'.Cartthrob_core::get_class($class);
		
		$child = new $class;
		
		$child->set_core($core);
		
		$child->initialize($params, $defaults);

		spl_autoload_unregister('Cartthrob_core::autoload');
		
		return $child;
	}
	
	public static function get_class($class)
	{
		if (is_object($class))
		{
			$class = get_class($class);
		}
		
		if (strpos($class, 'Cartthrob_') === 0)
		{
			$class = substr($class, 10);
		}
		
		return $class;
	}
	
	public static function autoload($class)
	{
		if (strpos($class, 'Cartthrob_') !== 0)
		{
			return;
		}
		
		$short_class = Cartthrob_core::get_class($class);
		
		//grab first "node" of class name
		$parts = explode('_', $short_class);
		$type = current($parts);
		
		$class = 'Cartthrob_'.$short_class;

		if (in_array($short_class, self::$_utilities))
		{
			$paths = array(CARTTHROB_CORE_PATH."Cartthrob_{$short_class}.php");
		}
		else
		{
			$paths = array(CARTTHROB_CORE_PATH."Cartthrob_{$type}.php");
			
			if (in_array($type, Cartthrob_core::$_drivers))
			{
				$paths[] = CARTTHROB_DRIVER_PATH."{$type}/{$class}.php";
			}
			else if (in_array($type, Cartthrob_core::$_plugins))
			{
				$path_added = FALSE;
				
				if (isset(self::$_plugin_paths[$type]) && is_array(self::$_plugin_paths[$type]))
				{
					foreach (self::$_plugin_paths[$type] as $path)
					{
						if (file_exists($path."{$class}.php"))
						{
							$path_added = TRUE;
							$paths[] = $path."{$class}.php";
							break;
						}
					}
				}
				
				if ( ! $path_added)
				{
					$paths[] = CARTTHROB_PLUGIN_PATH."{$type}/{$class}.php";
				}
			}
			else if (count($parts) > 1)
			{
				$paths[] = CARTTHROB_CORE_PATH.'Cartthrob_child.php';
				$paths[] = CARTTHROB_CORE_PATH."{$type}/{$class}.php";
			}
		}
		
		foreach ($paths as $path)
		{
			if ( ! file_exists($path))
			{
				Cartthrob_core::core_error(sprintf('File %s not found.', basename($path)));
			}
			
			require_once $path;
		}
		
		if ( ! class_exists($class))
		{
			Cartthrob_core::core_error(sprintf('Class %s not found.', $class));
		}
	}
	
	protected static function core_error($error)
	{
		trigger_error($error);
		//exit($error);
	}
	
	/* utilities */
	public function set_child($name, $params = array())
	{
		static $children = array('hooks', 'store', 'cart');
		
		if ( ! in_array($name, $children))
		{
			return $this;
		}
		
		$this->$name = Cartthrob_core::create_child($this, $name, $params);
	}
	/**
	 * sanitize_number function
	 *
	 * @param float $number  The number to clean. 
	 * @param  bool $allow_negative By default this does not allow negative values. If you need negatives, make sure this is set to TRUE.  
	 * @param integer $integer By default does not require that the number be an integer
	 * @return float
	 * @author Newton
	 **/
	public function sanitize_number($number = NULL, $allow_negative = FALSE, $integer = FALSE)
	{
		if (is_int($number))
		{
			return $number;
		}
		
		if (is_float($number))
		{
			if ($integer)
			{
				//it IS an integer but is cast as float
				if ((int) $number === $number)
				{
					return $number;
				}
				else
				{
					$number = (string) $number;
				}
			}
			else
			{
				return $number;
			}
		}

		if ( ! $number || ! is_string($number))
		{
			return 0;
		}
		
		$regex = ($integer) ? '/[^\d]/' : '/[^\d\.]/';
		
		if ($integer)
		{
			$number = floor($number);
		}

		if (substr($number, 0, 1) === '-')
		{
			$number = preg_replace($regex, '', substr($number, 1));
			
			if ($allow_negative)
			{
				$number = '-'.$number;
			}
		}
		else
		{
			$number = preg_replace($regex, '', $number);
		}

		return floatval($number);
	}
	
	public function sanitize_integer($number = NULL, $allow_negative = FALSE)
	{
		return (int) $this->sanitize_number($number, $allow_negative, TRUE);
	}
	
	public function round($number)
	{
		$number = $this->sanitize_number($number, $allow_negative = TRUE);
		
		switch ($this->store->config('rounding_default'))
		{
			case 'swedish':
				if (phpversion() >=5.3)
				{
					return number_format(round(20*$number, PHP_ROUND_HALF_UP)/20, 2, '.', '') ;
				}
				return number_format(round(20*$number)/20, 2, '.', '') ;
				break;
			case 'new_zealand': 
				if (phpversion() >=5.3)
				{
					return number_format(round(20*$number, PHP_ROUND_HALF_UP)/20, 2, '.', '') ;
				}
				return number_format(round(10*$number)/10, 2, '.', '') ;
				break;
			case 'round_up': 
				$coefficient = pow(10, $this->store->config('number_format_defaults_decimals') );
				$number = ceil($number*$coefficient)/$coefficient;
				return number_format($number, $this->store->config('number_format_defaults_decimals'), '.', '');
				break;
			case 'round_down': 
				$coefficient = pow(10, $this->store->config('number_format_defaults_decimals') );
				$number = floor($number*$coefficient)/$coefficient;
				return number_format($number, $this->store->config('number_format_defaults_decimals'), '.', '');
				break;
			case 'round_up_extra_precision': 
				if (phpversion() >= 5.3)
				{
					$number = round($number,$this->store->config('number_format_defaults_decimals') + 1 , PHP_ROUND_HALF_UP);
				}
				else
				{
					$number = round($number,$this->store->config('number_format_defaults_decimals')  + 1);
				}
				return number_format($number, $this->store->config('number_format_defaults_decimals') + 1, '.', '');
				break;
			default: 
				if (phpversion() >= 5.3)
				{
					$number = round($number,$this->store->config('number_format_defaults_decimals') , PHP_ROUND_HALF_UP);
				}
				else
				{
					$number = round($number,$this->store->config('number_format_defaults_decimals'));
				}
				return number_format($number, $this->store->config('number_format_defaults_decimals'), '.', '');
		}
		
		return number_format($number, $this->store->config('number_format_defaults_decimals'), '.', '');
	}
	
	public function log($msg)
	{
	}

	public function caller($which = 0)
	{
		$which += 2;
		
		$backtrace = debug_backtrace();
		
		return (isset($backtrace[$which])) ? $backtrace[$which] : FALSE;
	}
	
	public function array_merge($a, $b)
	{
		foreach ($b as $key => $value)
		{
			if (is_array($value) && isset($a[$key]))
			{
				$a[$key] = $this->array_merge($a[$key], $value);
			}
			else
			{
				$a[$key] = $value;
			}
		}
		
		return $a;
	}
	
	/* abstract methods */
	
	abstract public function get_product($product_id);
	abstract public function get_categories();
	abstract public function save_cart();
	abstract public function validate_coupon_code($coupon_code);
}