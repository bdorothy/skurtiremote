<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Api_cartthrob_payment_gateways class
 *
 * This class returns information about gateways available in CartThrob
 * Among other things, it returns gateway fields HTML, and the list of available gateways
 * This class does NOT instantiate a gateway, or call gateway methods. For that purpose use Cartthrob_payments.php 
 * 
 * Usage: (in this example a gateway is set, and the gateway fields HTML is returned); 
 *  
 * Api_cartthrob_payment_gateways->set_gateway(gateway_name)->gateway_fields(); 
 *
 * @package default
 * @author Rob Sanchez, Chris Newton
 **/
class Api_cartthrob_payment_gateways// extends Api_cartthrob_plugins
{
	protected $gateway;
	protected $gateways;
	
	public function __construct()
	{
		//parent::__construct();
		
		$this->EE =& get_instance();
		
		$this->EE->load->library('cartthrob_loader');
		
		$this->reset_gateway();
		
		$this->EE->load->library('cartthrob_payments');
	}
	
	public function template()
	{
		if ( ! $this->gateway)
		{
			return FALSE;
		}
		
		return $this->EE->cartthrob->store->config($this->gateway.'_settings', 'gateway_fields_template');
	}
	
	public function gateway_fields($clear_customer_info = FALSE, $fields_group="fields", $required_fields_group = "required_fields")
	{
		if ($this->template())
		{
			return '{embed="'.$this->template().'"}';
		}
		
		$this->EE->load->library('locales');
		$this->EE->load->helper(array('form', 'url'));
		
		if ($clear_customer_info)
		{
			$this->EE->cartthrob->cart->clear_customer_info();
		}
		
		$data['states'] = $this->EE->locales->states();
		$data['countries'] = $this->EE->locales->countries();
		
		$data['sections'] = array(
			'billing' => array(
				'first_name',
				'last_name',
				'address',
				'address2',
				'city',
				'state',
				'zip',
				'country',
				'country_code',
				'company',
				'region',
			),
			'shipping' => array(
				'shipping_first_name',
				'shipping_last_name',
				'shipping_address',
				'shipping_address2',
				'shipping_city',
				'shipping_state',
				'shipping_zip',
				'shipping_country',
				'shipping_country_code',
				'shipping_company',
				'shipping_region',
			),
			'member' => array(
				'username ',
				'screen_name',
				'password',
				'password_confirm ',
				'create_member',
				'group_id',
			),
			'additional_info' => array(
				'phone',
				'email_address',
				'ip_address',
				'description',
				'language',
				'currency_code',
				'description'
			),
			'payment' => array(
				'card_type',
				'credit_card_number',
				'card_code',
				'issue_number',
				'CVV2',
				'bday_month',
				'bday_day',
				'bday_year',
			),
			'checking_payment' => array(
				'po_number',
				'card_code',
				'transaction_type',
				'bank_account_number',
				'check_type',
				'account_type',
				'routing_number',
				'bank_name',
				'bank_account_name',
			),
			'payment_expiration' => array(
				'expiration_month',
				'expiration_year',
			),
			'payment_begin' => array(
				'begin_month',
				'begin_year',
			),
		 	'subscription' => array(
				'subscription_name',
				'subscription_price',
				'subscription_total_occurrences',
				'subscription_trial_price',
				'subscription_trial_occurrences',
				'subscription_start_date',
				'subscription_end_date',
				'subscription_interval_length',
				'subscription_interval_units',
				'subscription_allow_modification',
				'subscription_type',
			)
		);
		
		$data['extra_fields'] = $this->gateway('extra_fields', array());
		if (!empty($data['extra_fields']))
		{
			$data['sections']['extra_fields'] = $data['extra_fields']; 
			
		}
		
		$gateway_fields = $this->gateway($fields_group, array());
		
		foreach ($data['sections'] as $section => $fields)
		{
			foreach ($fields as $i => $field)
			{
				if ( ! in_array($field, $gateway_fields))
				{
					unset($data['sections'][$section][$i]);
				}
			}
			
			if (empty($data['sections'][$section]))
			{
				unset($data['sections'][$section]);
			}
		}
		
		if ($this->EE->cartthrob->store->config('gateways_format'))
		{
			$data['field_format'] = $this->EE->cartthrob->store->config('gateways_format');
		}
		
		$data['nameless_fields'] = $this->gateway('nameless_fields', array());
 		
		for ($i = 1; $i <= 12; $i++)
		{
			if ($i < 10)
			{
				$i = '0'.$i;
			}
			
			$data['months'][(string) $i] = lang('month_'.$i);
		}
		
		$data['bday_year'] = array();
		
		for ($year = date('Y')-100; $year < date('Y') - 10; $year++)
		{
			$data['bday_year'][$year] = $year;
		}
		
		ksort($data['bday_year']);

		$data['bday_day'] = array();
		
		for ($day = 1; $day <= 31 ; $day ++ )
		{
			if (strlen($day)<2)
			{
				$day_key = "0". $day; 
			}
			else
			{
				$day_key = $day; 
			}
			$data['bday_day'][$day_key] = $day;
		}
		
		ksort($data['bday_day']);
		
		
		$data['exp_years'] = array();
		
		for ($year = date('Y'); $year < date('Y') + 10; $year++)
		{
			$data['exp_years'][$year] = $year;
		}
		
		$data['begin_years'] = array();
		
		for ($year = date('Y'); $year > date('Y') - 15; $year--)
		{
			$data['begin_years'][$year] = $year;
		}
		
		ksort($data['begin_years']); 
		
		$data['subscription_interval_units'] = array('days' => 'Days', 'weeks'=> 'Weeks', 'months'=> 'Months', 'years' => 'Years');
		$card_types = $this->gateway('card_types');

		$account_types = $this->gateway('account_types');
		
		if ( ! $card_types)
		{
			$card_types = array(
				'visa',
				'mc',
				'amex',
				'discover'
			);	
		}
		
		if ( ! $account_types)
		{
			$account_types = array(
				'savings',
				'business_checking',
				'checking',
			);	
		}
		
		
		foreach ($card_types as $key => $card_type)
		{
			if (!is_numeric($key))
			{
				$data['card_types'][$key] = lang($card_type);
			}
			else
			{
				$data['card_types'][$card_type] = lang($card_type);
			}
		}
		foreach ($account_types as $key => $account_type)
		{
			if (!is_numeric($key))
			{
				
				$data['account_types'][$key] = lang($account_type);
			}
			else
			{
				
				$data['account_types'][$account_type] = lang($account_type);
			}
		}
		
		
		$data['hidden'] = '';
		
		foreach ($this->gateway('hidden', array()) as $hidden)
		{
			$data['hidden'] .= form_hidden($hidden, $this->EE->cartthrob->cart->customer_info($hidden))."\n";
		}
		
		$data['required_fields'] = $this->gateway($required_fields_group, array());
		
		if (version_compare(APP_VER, '2.2', '<'))
		{
			$orig_view_path = $this->EE->load->_ci_view_path;
			
			$this->EE->load->_ci_view_path = PATH_THIRD.'cartthrob/views/';
			
			$output = $this->EE->load->view('gateway_fields', $data, TRUE);
			
			$this->EE->load->_ci_view_path = $orig_view_path;
		}
		else
		{
			$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		
			$output = $this->EE->load->view('gateway_fields', $data, TRUE);
		}
		
		return $output;
	}
	
	public function set_gateway($gateway)
	{
		$this->gateway = 'Cartthrob_'.Cartthrob_core::get_class($gateway);
		
		return $this;
	}
	
	public function reset_gateway()
	{
		$this->gateway = $this->EE->cartthrob->store->config('payment_gateway');
		
		return $this;
	}
	
	public function gateway($key = FALSE, $default = FALSE)
	{
		$gateway_vars = FALSE;
		
		foreach ($this->gateways() as $vars)
		{
			if ($vars['classname'] === $this->gateway)
			{
				$gateway_vars = $vars;
				break;
			}
		}
		
		$return = ($key !== FALSE) ? element($key, $gateway_vars) : $gateway_vars;
		
		if ($return === FALSE)
		{
			return $default;
		}
		
		return $return;
	}

	public function subscription_gateways()
	{
		$gateways = array();

		foreach ($this->gateways() as $gateway)
		{
			if (method_exists($gateway['classname'], 'create_token') && method_exists($gateway['classname'], 'charge_token'))
			{
				$gateways[] = $gateway;
			}
		}

		return $gateways;
	}
	
	public function gateways()
	{
		$this->EE->load->helper(array('data_formatting', 'file'));
		if (is_null($this->gateways))
		{
			$this->gateways = array();
			
			$loaded_gateways = array();
			
			foreach ($this->EE->cartthrob_payments->paths() as $path)
			{
				if ( ! is_dir($path))
				{
					continue;
				}
				
				foreach (get_filenames($path, TRUE) as $file)
				{
					$class = basename($file, EXT);
					
					if ($class === 'Cartthrob_payment_gateway' || ! preg_match('/^Cartthrob_/', $class) || in_array($class, $loaded_gateways))
					{
						continue;
					}
					
					$loaded_gateways[] = $class;
					
					require_once $file;
					
					$this->EE->cartthrob_payments->set_gateway($class);
					
					$gateway_vars = get_object_vars($this->EE->cartthrob_payments->gateway());
					
					unset($gateway_vars['core']);
					
					$gateway_vars['classname'] = $class;
					
					$this->gateways[] = $gateway_vars;
				}
			}
		}
		
		return $this->gateways;
	}
}