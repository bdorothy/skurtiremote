<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//requires EE and CartThrob
class Number
{
	public $decimals = 0;
	public $dec_point = '.';
	public $thousands_sep = ',';
	public $allow_negative = TRUE;
	public $prefix = '';
	public $format = TRUE;
	public $prefix_position = "BEFORE"; 
	
	public function __construct()
	{
		$this->EE =& get_instance();
		
		$this->EE->load->model('cartthrob_settings_model');
		
		$this->EE->load->helper('data_formatting');
		
		$this->reset();
	}
	
	public function set_allow_negative($allow_negative = TRUE)
	{
		$this->allow_negative = $allow_negative;
		
		return $this;
	}
	
	public function set_format($format = TRUE)
	{
		$this->format = $format;
		
		return $this;
	}
	
	public function set_prefix_position($position = "AFTER")
	{
		$this->prefix_position = $position;
		
		return $this;
	}
	
	public function set_dec_point($dec_point)
	{
		$this->dec_point = $dec_point;
		
		return $this;
	}
	
	public function set_decimals($decimals)
	{
		$this->decimals = $decimals;
		
		return $this;
	}
	
	public function set_thousands_sep($thousands_sep)
	{
		$this->thousands_sep = $thousands_sep;
		
		return $this;
	}
	
	public function set_prefix($prefix)
	{
		$this->prefix = $prefix;
		
		return $this;
	}
	
	public function reset()
	{
		$this->decimals = $this->EE->config->item('cartthrob:number_format_defaults_decimals');
		
		$this->dec_point = $this->EE->config->item('cartthrob:number_format_defaults_dec_point');
		
		$this->thousands_sep = $this->EE->config->item('cartthrob:number_format_defaults_thousands_sep');
		
		$this->prefix = $this->EE->config->item('cartthrob:number_format_defaults_prefix');

		$this->prefix_position = ($this->EE->config->item('cartthrob:number_format_defaults_prefix_position') ? $this->EE->config->item('cartthrob:number_format_defaults_prefix_position') : "BEFORE"); 
		
		$this->allow_negative = TRUE;
		
		return $this;
	}
	
	public function set_params($params)
	{
		if (is_array($params))
		{
			$defaults = get_class_vars(__CLASS__);
			
			foreach ($params as $key => $value)
			{
				if (array_key_exists($key, $defaults))
				{
					if (is_bool($defaults[$key]) && ! is_bool($value))
					{
						$this->$key = bool_string($value);
					}
					else
					{
						$this->$key = $value;
					}
				}
			}
		}
		
		return $this;
	}
	
	/**
	 * Formats a number
	 *
	 * @access public
	 * @param int $this->EE->TMPL->fetch_param('number')
	 * @param int $this->EE->TMPL->fetch_param('decimals')
	 * @param string $this->EE->TMPL->fetch_param('dec_point')
	 * @param string $this->EE->TMPL->fetch_param('thousands_sep')
	 * @param string $this->EE->TMPL->fetch_param('prefix')
	 * @return string
	 * @since 1.0.0
	 * @author Rob Sanchez, Chris Newton, Chris Barrett
	**/
	public function format($number)
	{
		if (isset($this->EE->TMPL) && isset($this->EE->TMPL->tagparams))
		{
			$this->set_params($this->EE->TMPL->tagparams);
		}

		$number = sanitize_number($number, $this->allow_negative);
		
		if ( ! $this->format)
		{
			return $number;
		}
		
		$prefix = $this->prefix;
		
		$space = (isset($this->EE->TMPL) && bool_string($this->EE->TMPL->fetch_param('add_space_after_prefix'))) ? ' ' : '';
		
		if ($number < 0)
		{
			$prefix = '-'.$prefix;
			
			$number *= -1;
		}
		if ($this->prefix_position=="AFTER")
		{
			$number = number_format($number, $this->decimals, $this->dec_point, $this->thousands_sep)." ".$prefix;
		}
		else
		{
			$number = $prefix.$space.number_format($number, $this->decimals, $this->dec_point, $this->thousands_sep);
		}
		
		$this->reset();
		
		return $number;
	}
}