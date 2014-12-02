<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @property CI_Controller $EE
 */
class Cartthrob_shipping_plugins
{
	private $shipping_plugin;
	
	public function __construct($params = array())
	{
		$this->EE =& get_instance();
		$this->EE->load->library('cartthrob_loader');
		$this->EE->load->library('cartthrob_payments');
	}
	
	public function alpha2_country_code($country_code)
	{
		return $this->EE->cartthrob_payments->alpha2_country_code($country_code);
	}
	
	public function alpha3_country_code($country_code)
	{
		return $this->EE->cartthrob_payments->alpha3_country_code($country_code);
	}
	public function curl_transaction($url, $data = FALSE, $header = FALSE, $mode = 'POST', $suppress_errors = FALSE, $options = NULL)
	{
		return $this->EE->cartthrob_payments->curl_transaction($url, $data, $header, $mode, $suppress_errors, $options); 
		
	}
	function live_rates_options($option_values=array(), $option_names=array(), $option_prices=array(), $errors=NULL, $selected_option= NULL)
	{
		$output = NULL; 
		if ( ! isset($this->EE->TMPL))
		{
			$this->EE->load->library('template', NULL, 'TMPL');
		}
		
		if (! $this->EE->TMPL->tagdata)
		{
 			$id = ($this->EE->TMPL->fetch_param('id')) 				? 'id="'.$this->EE->TMPL->fetch_param('id').'"' 				: '';
			$class = ($this->EE->TMPL->fetch_param('class')) 		? 'class="'.$this->EE->TMPL->fetch_param('class').'"' 			: '';
			$onchange = ($this->EE->TMPL->fetch_param('onchange'))	? 'onchange="'.$this->EE->TMPL->fetch_param('onchange').'"' 	: '';
			$extra = ($this->EE->TMPL->fetch_param('extra'))		? $this->EE->TMPL->fetch_param('extra') 						: '';
 			
			$output .= '<select name="shipping[product]" '.$id." ".$class." ".$onchange." ".$extra.">\n";
			
			foreach ($option_values as $key=> $value)
			{
				// make sure a price is set
				if (!empty($option_prices[$key]))
				{
					$output .= "\t"; 
					$output .= '<option value="'.$key.'"'.(($selected_option == $key )?'selected="selected"': "").'>'.$option_names[$key].'</option>'; 
					$output .= "\n";
				}
			}
			$output .= "</select>\n";
 		}
		else
		{
			$count = 0; 
 			foreach ($option_values as $key=> $value)
			{
				$variables['selected'] 		= ($key == $selected_option) ? ' selected="selected"' : '';
				$variables['checked'] 		= ($key == $selected_option) ? ' checked="checked"' : '';
				$variables['option_value']	= $key; 
				$variables['option_name']	= $option_names[$key]; 
				$variables['price']			= $option_prices[$key]; 
				
				$cond['first_item'] = ($count==0 ? TRUE : FALSE); 
				$cond['selected'] = (bool) $selected;
				$cond['checked'] = (bool) $checked;
				$cond['price'] = (bool) $price;
				$cond['rate_title'] = (bool) $rate_title;
				$cond['rate_short_name'] = (bool) $key;
				$cond['last_item'] = ($count == count($shipping_options))?TRUE: FALSE;
				
				$tagdata.= $this->parse_variables($variables); 
 				$tagdata.= $this->EE->functions->prep_conditionals($tagdata, $cond);
				$count ++; 
			}	
			$output.= $tagdata; 
		}
		
		return $output; 
 	}
	function customer_location_defaults($location, $default = FALSE)
	{
		if ($this->EE->cartthrob->cart->customer_info('shipping_'.$location))
		{
			return $this->EE->cartthrob->cart->customer_info('shipping_'.$location); 
		}
		elseif ($this->EE->cartthrob->cart->customer_info($location))
		{
			return $this->EE->cartthrob->cart->customer_info($location); 
		}
		else
		{
			// looking through custom data for this information.
			if ($this->EE->cartthrob->cart->custom_data($location))
			{
				return $this->EE->cartthrob->cart->custom_data($location); 
			}
			else
			{
				// deliberately set it to false, because we might want 0,"", or NULL returned. 
				if ($default !==FALSE)
				{	
					return $default; 
				}
				else
				{
					return $this->EE->cartthrob->store->config('default_location', $location);
				}
			}
		}
	}
}