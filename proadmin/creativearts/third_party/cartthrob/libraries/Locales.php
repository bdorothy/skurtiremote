<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Locales
{
	public function __construct()
	{
		$this->EE =& get_instance();
		
		if (file_exists(PATH_THIRD.'cartthrob/config/my_locales.php'))
		{
			$this->EE->config->load('my_locales');
		}
		else
		{
			$this->EE->config->load('locales');
		}
	}
	
	public function states($country = FALSE)
	{
		$states = array();
		
		if ( ! $country)
		{
			$country = $this->EE->config->item('default_state_country');
		}
		
		$all_states = $this->EE->config->item('states');
		
		if ($country && $all_states)
		{
			if ( ! is_array($country))
			{
				$country = explode('|', $country);
			}
			
			foreach ($country as $key)
			{
				if (isset($all_states[$key]))
				{
					$states = $states + $all_states[$key];
				}
			}
		}
		
		return $states;
	}
	
	public function countries($alpha2 = FALSE, $country_codes = TRUE, $all = FALSE)
	{
		$locales_countries = $this->EE->config->item('cartthrob:locales_countries');
		
		if ($alpha2 && $country_codes)
		{
			$alpha2_country_codes = $this->EE->config->item('country_codes');
		}
		
		foreach ($this->EE->config->item('countries') as $country_code => $country)
		{
			if ($all || ! $locales_countries || in_array($country_code, $locales_countries))
			{
				if ( ! $country_codes)
				{
					$key = $country;
				}
				else if ($alpha2)
				{
					$key = is_array($alpha2_country_codes[$country_code]) ? current($alpha2_country_codes[$country_code]) : $alpha2_country_codes[$country_code];
				}
				else
				{
					$key = $country_code;
				}
				
				$countries[$key] = $country;
			}
		}
		
		return $countries;
	}
	
	public function all_countries($alpha2 = FALSE, $country_codes = TRUE)
	{
		return $this->countries($alpha2, $country_codes, TRUE);
	}
	
	// alpha2_country_code => alpha3_country_code(s)
	public function country_codes()
	{
		return $this->EE->config->item('country_codes');
	}
	
	public function country_code($country, $alpha2 = FALSE)
	{
		$countries = $this->all_countries($alpha2);
		
		if ( ! $key = array_search($country, $countries))
		{
			return FALSE;
		}
		
		return $countries[$key];
	}
	
	public function alpha3_country_code($country_code)
	{
		if (strlen($country_code) === 3)
		{
			return $country_code;
		}
		
		$country_code = strtoupper($country_code);
		
		$key = FALSE;
		
		foreach ($this->country_codes() as $alpha3 => $alpha2)
		{
			if (is_array($alpha2))
			{
				if (in_array($country_code, $alpha2))
				{
					$key = $alpha3;
					
					break;
				}
			}
			else
			{
				if ($country_code === $alpha2)
				{
					$key = $alpha3;
					
					break;
				}
			}
		}
		
		return $key ? $key : $country_code;
	}
	
	public function alpha2_country_code($country_code)
	{
		if (strlen($country_code) === 2)
		{
			return $country_code;
		}
		
		$country_codes = $this->country_codes();
		
		if ( ! isset($country_codes[$country_code]))
		{
			return $country_code;
		}
		
		return is_array($country_codes[$country_code]) ? current($country_codes[$country_code]) : $country_codes[$country_code];
	}
	
	public function country_from_country_code($country_code)
	{
		$country_code = $this->alpha3_country_code($country_code);
		$countries = $this->all_countries();
		
		return (isset($countries[$country_code])) ? $countries[$country_code] : $country_code;
	}
	
	public function currency_codes()
	{
		return $this->EE->config->item('currency_codes');
	}
	function iso_currency_code($currency_code)
	{
		$codes = $this->currency_codes(); 
		if (array_key_exists($currency_code, $codes))
		{
			return $codes[$currency_code][1]; 
		}
		return NULL;
	}
}