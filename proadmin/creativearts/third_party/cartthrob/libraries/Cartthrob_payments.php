<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_THIRD.'cartthrob/libraries/cartthrob_payments/Cartthrob_token.php';

/**
 * @property CI_Controller $EE
 * @property Cartthrob_cart $cart
 * @property Cartthrob_store $store
 */

/**
 * Cartthrob_payments class
 *
 * This class executes gateway methods for CartThrob
 * This class does NOT return information about a gateway. For that purpose use Api_cartthrob_payments_gateways.php 
 * 
 * Usage: (in this example a gateway is set, and the gateway create_token  method is executed); 
 *  
 * Cartthrob_payments->set_gateway(gateway_name)->create_token(params); 
 *
 * @package default
 * @author Rob Sanchez, Chris Newton
 **/

class Cartthrob_payments
{
	private $gateway;
	private $paths = array();
	private $errors = array();
	private $total;
	private $current_status  = NULL; 
	private $third_party_path;
	
	private $modules = array();
	
	public $cartthrob, $store, $cart;
	
	public $pending_group_id = 4; 
	public function __construct($params = array())
	{
		$this->EE =& get_instance();
		
		$this->EE->load->library('cartthrob_loader');
		
		include_once PATH_THIRD.'cartthrob/config.php';
		
		require_once PATH_THIRD.'cartthrob/payment_gateways/Cartthrob_payment_gateway.php';
		
		$this->third_party_path = ($this->EE->config->item('cartthrob_third_party_path')) ? rtrim($this->EE->config->item('cartthrob_third_party_path'), '/').'/' : PATH_THIRD.'cartthrob/third_party/';
		
		$this->paths[] = $this->third_party_path.'payment_gateways/';
		$this->paths[] = PATH_THIRD.'cartthrob/payment_gateways/';
		
		if ( ! function_exists('json_decode'))
		{
			$this->EE->load->library('services_json');
		}
		
		
		$available_modules = array(
			'subscriptions',
		);
		
		foreach ($available_modules as $module)
		{
			$class = 'Cartthrob_'.$module;
			
			$short_name = strtolower($class);
			
			if (file_exists(PATH_THIRD.$short_name.'/libraries/'.$class.'.php'))
			{
				$this->EE->load->add_package_path(PATH_THIRD.$short_name.'/');
				
				$this->EE->load->library($short_name);
				
				$this->modules[$module] =& $this->EE->$short_name;
				
				$this->EE->load->remove_package_path(PATH_THIRD.$short_name.'/');
			}
			else
			{
				$this->modules[$module] = FALSE;
			}
		}
		$this->EE->load->add_package_path(PATH_THIRD."cartthrob/");
		// loading these here, because it looks liek the package path is lost at some point causing the loading of these later to fail. 
		$this->EE->load->library('form_builder'); 
		$this->EE->load->library('cartthrob_emails');
		$this->EE->load->library('template_helper');
		$this->EE->load->helper('array');
		
	}
	
	public function add_error($key, $value = FALSE)
	{
		if (is_array($key))
		{
			foreach ($key as $k => $v)
			{
				$this->add_error($k, $v);
			}
		}
		else
		{
			if ($value === FALSE)
			{
				$this->errors[] = $key;
			}
			else
			{
				$this->errors[$key] = $value;
			}
		}
		
		return $this;
	}

	/**
	 * get the first error
	 * 
	 * @return string|false    
	 */
	public function error()
	{
		return reset($this->errors);
	}

	public function errors()
	{
		return $this->errors;
	}
	
	public function set_total($total)
	{
		$this->total = $total;
		return $this;
	}
	
	public function total()
	{
		return $this->total;
	}
	
	//this is only for gateways who need the total when the checkout form is rendered, like stripe
	public function get_total()
	{
		return $this->EE->cartthrob->cart->total();
	}
	
	//this is for loading third party libraries, usually api wrappers, in payment_gateways/vendor
	public function library_path()
	{
		return $this->vendor_path();
	}
	
	public function vendor_path()
	{
		return PATH_THIRD.'cartthrob/payment_gateways/libraries/';
	}
	
	public function config($key = FALSE)
	{
		return $this->EE->cartthrob->store->config($key);
	}
	
	public function theme_path()
	{
		return $this->EE->config->item('theme_folder_url');
	}
	
	public function order($order = FALSE)
	{
		return $this->EE->cartthrob->cart->order($order);
	}
	
	public function payment_url()
	{
		return $this->EE->functions->fetch_site_index(0, 0).QUERY_MARKER.'ACT='.$this->EE->functions->fetch_action_id('Cartthrob', 'checkout_action');
	}
	
	public function paths()
	{
		return $this->paths;
	}
	
	/**
	 * deprecated, alias for charge()
	 */
	public function process_payment($credit_card_number)
	{
		return $this->charge($credit_card_number);
	}
	
	public function charge($credit_card_number)
	{
		if ($this->total <= 0)
		{
			return array(
				'processing' => FALSE,
				'authorized' => TRUE,
				'declined' => FALSE,
				'failed' => FALSE,
				'error_message' => '',
				'transaction_id' => time()
			);
		}
		
		if ( ! $this->gateway)
		{
			return array(
				'processing' => FALSE,
				'authorized' => FALSE,
				'declined' => FALSE,
				'failed' => TRUE,
				'error_message' => $this->lang('invalid_payment_gateway'),
				'transaction_id' => ''
			);
		}
		
		// the old method, process_payment
		if ( ! $this->is_valid_gateway_method('charge'))
		{
			return $this->gateway->process_payment($credit_card_number);
		}
		
		return $this->gateway->charge($credit_card_number);
	}
	
	public function refund($transaction_id = NULL, $amount = NULL, $credit_card_number = NULL)
	{
		if ( ! $this->gateway)
		{
			return array(
				'processing' => FALSE,
				'authorized' => FALSE,
				'declined' => FALSE,
				'failed' => TRUE,
				'error_message' => $this->lang('invalid_payment_gateway'),
				'transaction_id' => ''
			);
		}
		if (!($amount + 0))
		{
			$amount = NULL; 
		}
		return $this->gateway->refund($transaction_id, $amount, $credit_card_number);
	}
	
	public function charge_token($token, $customer_id = NULL, $offsite=FALSE)
	{
		if ($this->total <= 0)
		{
			return array(
				'authorized' => TRUE,
				'failed' => FALSE,
				'declined' => FALSE,
				'transaction_id' => time(),
				'error_message' => '',
			);
		}
		
		if ( ! $this->gateway)
		{
			return array(
				'authorized' => FALSE,
				'failed' => TRUE,
				'declined' => FALSE,
				'transaction_id' => '',
				'error_message' => $this->lang('invalid_payment_gateway'),
			);
		}
		
		if ( ! $this->is_valid_gateway_method('charge_token'))
		{
			return array(
				'authorized' => FALSE,
				'failed' => TRUE,
				'declined' => FALSE,
				'transaction_id' => '',
				'error_message' => $this->lang('gateway_does_not_support_subscriptions'),//@TODO lang
			);
		}
		
		return $this->gateway->charge_token($token, $customer_id, $offsite);
	}
	
	public function create_token($credit_card_number)
	{
		if ( ! $this->is_valid_gateway_method('create_token'))
		{
			return array(
				'processing' => FALSE,
				'authorized' => FALSE,
				'declined' => FALSE,
				'failed' => TRUE,
				'error_message' => $this->lang('invalid_payment_gateway'),
				'transaction_id' => ''
			);
		}
		
		return $this->gateway->create_token($credit_card_number);
	}
	
	public function create_recurrent_billing($subscription_amount, $credit_card_number, $sub_data)
	{
		if ( ! $this->is_valid_gateway_method('create_recurrent_billing'))
		{
			return array(
				'processing' => FALSE,
				'authorized' => FALSE,
				'declined' => FALSE,
				'failed' => TRUE,
				'error_message' => $this->lang('invalid_payment_gateway'),
				'transaction_id' => ''
			);
		}
		
		return $this->gateway->create_recurrent_billing($subscription_amount, $credit_card_number, $sub_data);
	}
	
	public function update_recurrent_billing($id, $credit_card_number)
	{
		if ( ! $this->is_valid_gateway_method('update_recurrent_billing'))
		{
			return array(
				'processing' => FALSE,
				'authorized' => FALSE,
				'declined' => FALSE,
				'failed' => TRUE,
				'error_message' => $this->lang('invalid_payment_gateway'),
				'transaction_id' => ''
			);
		}
		
		$this->EE->load->model("order_model");
		$this->EE->load->model("vault_model");
		
		$auth =  $this->gateway->update_recurrent_billing($id, $credit_card_number);
		
		if ($auth['authorized']) 
		{
			if ($auth['transaction_id'])
			{
				$data['sub_id']  = $auth['transaction_id']; 
			}
			
			$this->EE->vault_model->update_vault($data, $id); 
		}
		return $auth; 
	}
	
	public function is_valid_gateway_method($method)
	{
		return $this->gateway && method_exists($this->gateway, $method) && is_callable(array($this->gateway, $method));
	}
	
	public function update_subscriptions($data, $id = NULL)
	{
		//@TODO see Cartthrob_authorize_net
	}

	public function update_vault_data($data, $id = NULL)
	{
		if ( ! is_array($data))
		{
			return FALSE; 
		}
		
		$this->EE->load->model('vault_model');
		
		return $this->EE->vault_model->update_vault($data, $id); 
	}
	
	public function delete_recurrent_billing($id)
	{
		$auth =  array(
			'processing' => FALSE,
			'authorized' => FALSE,
			'declined' => FALSE,
			'failed' => TRUE,
			'error_message' => $this->lang('invalid_payment_gateway'),
			'transaction_id' => ''
		);
		
		if ( ! $this->gateway && !is_callable(array($this->gateway, "delete_recurrent_billing")))
		{
			return $auth; 
		}

		$this->EE->load->model("vault_model");

		$auth =  $this->gateway->delete_recurrent_billing($id);
		

		if ($auth['authorized'])
		{
			$this->EE->vault_model->delete_vault(NULL, NULL, NULL, $id);
		}
		return $auth; 
	}
	
	public function subscription_info($subscription_data, $key, $default = FALSE)
	{
		return (isset($subscription_data[$key])) ? $subscription_data[$key] : $default;
	}
	
	public function required_fields()
	{
		return ($this->gateway) ? $this->gateway->required_fields : array();
	}
	
	public function set_gateway($gateway)
	{
		static $loaded_gateways = array();
		
		if (strpos($gateway, 'Cartthrob_') !== 0)
		{
			$gateway = 'Cartthrob_'.$gateway;
		}
		
		if ( ! is_object($this->gateway) || get_class($this->gateway) != $gateway)
		{
			$this->gateway = NULL;
			
			foreach ($this->paths as $path)
			{
				if (file_exists($path.$gateway.EXT))
				{
					if ( ! in_array($gateway, $loaded_gateways))
					{
						require_once $path.$gateway.EXT;
					}
					
					$loaded_gateways[] = $gateway;
					
					$this->gateway = new $gateway;
					
					$this->gateway->set_core($this);
					
					if ($path === $this->third_party_path.'payment_gateways/')
					{
						$this->load_lang(strtolower($gateway), $this->third_party_path);
					}
					else
					{
						$this->load_lang(strtolower($gateway));
					}
					
					$this->gateway->initialize();
				}
			}
		}
		
		return $this;
	}
	
	public function load_lang($which, $path = NULL)
	{
		static $user_lang;
		
		if (is_null($path))
		{
			$path = PATH_THIRD.'cartthrob/';
		}
		
		if (is_null($user_lang))
		{
			if ( ! empty($this->EE->session->userdata['language']))
			{
				$user_lang = $this->EE->session->userdata['language'];
			}
			else if ($this->EE->input->cookie('language'))
			{
				$user_lang = $this->EE->input->cookie('language');
			}
			else if ($this->EE->config->item('deft_lang'))
			{
				$user_lang = $this->EE->config->item('deft_lang');
			}
			else
			{
				$user_lang = 'english';
			}
			
			$user_lang = $this->EE->security->sanitize_filename($user_lang);
		}
		
		$this->EE->lang->load($which, $user_lang, FALSE, TRUE, $path, FALSE);
	}
	
	public function gateway()
	{
		return $this->gateway;
	}
	
	/* utilities for the payment gateways */
	
	public function log($msg, $type = FALSE)
	{
		$this->EE->load->model('log_model');
		
		return $this->EE->log_model->log($msg, $type);
	}
	
	public function create_url($path)
	{
		return $this->EE->functions->create_url($path);
	}
	
	// required by Authorize.net SIM
	public function fetch_template($template)
	{
		$this->EE->load->library('template_helper');
		return $this->EE->template_helper->fetch_template($template); 
	}
	
	// required by Authorize.net SIM
	public function parse_template($template, $vars = array())
	{
		$this->EE->load->library('template_helper');
		return $this->EE->template_helper->fetch_and_parse($template, $vars); 
	}
	
	public function lang($key)
	{
		return $this->EE->lang->line($key);
	}
	
	public function year_2($year)
	{
		if (strlen($year > 2))
		{
			return substr($year, -2);
		}
		
		return str_pad($year, 2, '0', STR_PAD_LEFT);
	}
	
	public function year_4($year)
	{
		$length = strlen($year);
		
		switch($length)
		{
			case 3:
				return '2'.$year;
			case 2:
				return '20'.$year;
			case 1:
				return '200'.$year;
			case ($length > 4):
				return substr($year, -4);
		}
		
		return $year;
	}
	
	public function alpha2_country_code($country_code)
	{
		$this->EE->load->library('locales');
		
		return $this->EE->locales->alpha2_country_code($country_code);
	}
	
	public function alpha3_country_code($country_code)
	{
		$this->EE->load->library('locales');
		
		return $this->EE->locales->alpha3_country_code($country_code);
	}
	
	public function curl_transaction($url, $data = FALSE, $header = FALSE, $mode = 'POST', $suppress_errors = FALSE, $options = NULL)
	{
		if ( ! function_exists('curl_exec'))
		{
			return show_error(lang('curl_not_installed'));
		}
		
		// CURL Data to institution
		$curl = curl_init($url);
		
		if ($this->EE->config->item('cartthrob:curl_proxy'))
		{
			curl_setopt($curl, CURLOPT_PROXY, $this->EE->config->item('cartthrob:curl_proxy'));
			
			if ($this->EE->config->item('cartthrob:curl_proxy_port'))
			{
				curl_setopt($curl, CURLOPT_PROXYPORT, $this->EE->config->item('cartthrob:curl_proxy_port'));
			}
		}
		
		if ($header)
		{
			if (!is_array($header))
			{
				$header = array($header); 
			}
			curl_setopt($curl, CURLOPT_HEADER, 1);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		}
		else
		{
			// set to 0 to eliminate header info from response
			curl_setopt($curl, CURLOPT_HEADER, 0);
		}
		
		// Returns response data instead of TRUE(1)
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		
		if ($data)
		{
			if ($mode === 'POST')
			{
				// use HTTP POST to send form data
				curl_setopt($curl, CURLOPT_POST, 1);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
			}
			else
			{
				// check for query  string
				if (strrpos($url, "?") === FALSE)
				{
					curl_setopt($curl, CURLOPT_URL, $url.'?'.$data); 
				}
				else
				{
					curl_setopt($curl, CURLOPT_URL, $url.$data);
				}
				
				curl_setopt($curl, CURLOPT_HTTPGET, 1);
			}
		}
		else
		{
			// if there's no data passed in, then it's a GET
			curl_setopt($curl, CURLOPT_HTTPGET, 1);
		}
		
		// curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.5) Gecko/20041107 Firefox/1.0');
		// Turn off the server and peer verification (PayPal TrustManager Concept).
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		
		if (is_array($options))
		{
			foreach ($options as $key => $value)
			{
				curl_setopt($curl, $key, $value);
			}
		}
		// execute post and get results
		$response = curl_exec($curl);

		if ( ! $response)
		{
			$error = curl_error($curl).' ('.curl_errno($curl).')';
		}

		curl_close($curl);
		
		if ( ! $suppress_errors && ! empty($error))
		{
			return show_error($error);
		}
		
		return $response; 
	}
	
	/**
	 * curl_post
	 *
	 * @param array $data (string:url, array:params, array:options)
	 * @return string
	 * @author Rob Sanchez
	 * 
	 * $this->EE->curl->simple_post('http://example.com', array('foo'=>'bar'), array(CURLOPT_BUFFERSIZE => 10));  
	 * @see http://codeigniter.com/wiki/Curl_library/
	 * @see http://uk3.php.net/manual/en/function.curl-setopt.php
	 */
	public function curl_post($url, $params = array(), $options = array())
	{
		if (is_array($url))
		{
			$options = (isset($url[2])) ? $url[2] : array();
			$params = (isset($url[1])) ? $url[1] : array();
			$url = $url[0];
		}
		
		$this->EE->load->library('curl');
		
		return $this->EE->curl->simple_post($url, $params, $options);
	}
	/**
	 * curl_get
	 *
	 * @param array $data (string:url, array:options)
	 * @return string
	 * @author Rob Sanchez
	 * 
	 * $this->curl->simple_get('http://example.com', array(CURLOPT_PORT => 8080)); 
	 * @see http://codeigniter.com/wiki/Curl_library/
	 * @see http://uk3.php.net/manual/en/function.curl-setopt.php
	 */
	public function curl_get($url, $options = array())
	{
		if (is_array($url))
		{
			$options = (isset($url[1])) ? $url[1] : array();
			$url = $url[0];
		}
		
		$this->EE->load->library('curl');
		
		return $this->EE->curl->simple_get($url, $options);
	}
	
	public function curl_error_message()
	{
		$this->EE->load->library('curl');
		
		return $this->EE->curl->error_string;
	}
	
	public function curl_error_code()
	{
		$this->EE->load->library('curl');
		
		return $this->EE->curl->error_code;
	}
	
	public function customer_id()
	{
		return $this->EE->session->userdata('member_id');
	}
	
	public function order_id()
	{
		return $this->order('order_id');
	}
	
	public function input_post($key, $xss_clean = FALSE)
	{
		return $this->EE->input->post($key, $xss_clean);
	}
	
	public function input_get($key, $xss_clean = FALSE)
	{
		return $this->EE->input->get($key, $xss_clean);
	}
	
	public function input_get_post($key, $xss_clean = FALSE)
	{
		return $this->EE->input->get_post($key, $xss_clean);
	}
	
	public function input_cookie($key, $xss_clean = FALSE)
	{
		return $this->EE->input->cookie($key, $xss_clean);
	}
	
	public function xss_clean($data)
	{
		return $this->EE->security->xss_clean($data);
	}
	
	public function split_url_string($data,  $split_character = '&')
	{
		$this->EE->load->helper('data_formatting');
		return split_url_string($data, $split_character);
	}
	
	public function convert_response_xml($xml)
	{
		$this->EE->load->helper('data_formatting');
		return convert_response_xml($xml);
	}
	
	public function get_formatted_phone($phone)
	{
		$this->EE->load->helper('data_formatting');
		return get_formatted_phone($phone);
	}
	
	public function data_array_to_string($data)
	{
		if (function_exists('http_build_query'))
		{
			return http_build_query($data, '', '&');
		}

		$string = '';
		
		while (list($key, $val) = each($data)) 
		{
			$string .= $key."=".urlencode(stripslashes(str_replace("\n", "\r\n", $val))).'&';
		}
		
		if ($string)
		{
			$string = substr($data, 0, -1);
		}
		
		return $string;
	}
	
	public function clear_cart($cart_id = NULL)
	{
		if ($cart_id)
		{
			$this->relaunch_cart($cart_id ); 
		}
		
		$this->EE->cartthrob->cart->clear()
					  ->clear_coupon_codes()
					  ->clear_totals()
					  ->save();
	}
	public function response_script($gateway, $segments = array())
	{
		if (substr($gateway, 0, 10) == 'Cartthrob_')
		{
			$gateway = substr($gateway, 10);
		}
		
		if ( ! $extload = $this->EE->config->item('cartthrob:extload_path'))
		{
			$extload = URL_THIRD_THEMES.'cartthrob/lib/extload.php'; 
		}
		
		$extload .= "/".$gateway; 
		
		foreach ($segments as $item)
		{
			$extload .="/".$item; 
		}
		return $extload; 
	}
 	/**
 	 * update_order
 	 *
 	 * updates the order in session
 	 *
 	 * @param array $data (key => $value)
 	 * @return void
 	 * @author Chris Newton
 	 */
	public function update_order($data)
	{
		$this->EE->cartthrob->cart->update_order($data);
	}
	/**
	 * update_order_by_id
	 *
	 * updates an order entry's data
	 * 
	 * @param string $entry_id 
	 * @param array $order_data (key => $value)
	 * @return string entry_id
	 * @author Chris Newton
	 */
	public function update_order_by_id($entry_id, $order_data)
	{
		$this->EE->load->model("order_model");
		return $this->EE->order_model->update_order($entry_id, $order_data); 
		
	}
	public function get_language_abbrev($language)
	{
		$this->EE->load->library('languages');
		
		return $this->EE->languages->get_language_abbrev($language);
	}
	// deprecated
	public function relaunch_session_full($session_id)
	{
		$this->relaunch_session($session_id); 
	}
	public function relaunch_session($session_id)
	{
 		if ($session_id != @session_id())
		{
			@session_destroy(); 
			@session_id($session_id);
			@session_start();
		}
		
		$this->EE->load->model('order_model'); 
		$order_id = $this->EE->order_model->get_order_id_from_session($session_id); 

		$this->relaunch_cart_snapshot($order_id); 
 	}
	
	public function get_notify_url($gateway, $method = FALSE)
	{
		$this->EE->load->library('encrypt');
		
		if (substr($gateway, 0, 10) == 'Cartthrob_')
		{
			$gateway = substr($gateway, 10);
		}

		$notify_url = $this->EE->functions->fetch_site_index(0, 0).QUERY_MARKER.'ACT='.$this->EE->functions->insert_action_ids($this->EE->functions->fetch_action_id('Cartthrob', 'payment_return_action')).'&G='.base64_encode($this->EE->encrypt->encode($gateway));

		if ($method)
		{
			$notify_url .= "&M=".base64_encode($this->EE->encrypt->encode($method));
		}
		
		return $notify_url; 
	}
	
	/**
	 * gateway_exit_offsite
	 *
	 * sends a customer offsite to finish a payment transaction
	 * 
	 * @param array $post_array 
	 * @param string $url 
	 * @return void
	 * @author Chris Newton 
	 * @since 1.0
	 * @access public 
	 */
	function gateway_exit_offsite($post_array=NULL, $url=FALSE, $jump_url= FALSE)
	{
		$this->save_cart_snapshot($this->order('entry_id')); 
		$this->set_status_offsite(array(), $this->order('order_id'),  $send_email=FALSE);
		
		if ($jump_url !== FALSE)
		{
			echo $this->jump_form($jump_url, $post_array, $hide_jump_form=TRUE, $this->lang('jump_header'), $this->lang('jump_alert'), $this->lang('jump_submit')); 
			exit; 
		}
		elseif ($url !== FALSE)
		{
			if ($post_array)
			{
				$url .= '?'.$this->data_array_to_string($post_array);	
			}
			$this->EE->functions->redirect($url);
		}
		else
		{
			return; 
		}

	}
    public function set_order_meta($order_id, $internal_status = NULL, $ee_status = NULL, $transaction_id = NULL, $error_message = NULL, $data = array())
	{
 		$this->EE->load->model('order_model');
 		
		if ($internal_status)
		{
			if ($internal_status === 'authorized' || $internal_status === 'completed')
			{
				// garbage cleanup
				$this->EE->order_model->update_order($order_id, array('cart' => ''));
			}
			
			$this->EE->order_model->set_order_status($order_id, $internal_status);
		}
		
		if ($transaction_id)
		{
			$this->EE->order_model->set_order_transaction_id($order_id, $transaction_id);
		}
		
		if ($error_message)
		{
			$this->EE->order_model->set_order_error_message($order_id, $error_message);
		}

		if ($this->EE->cartthrob->store->config('save_orders'))
		{
			if ($ee_status)
			{
				$data['status'] = $ee_status; 
			}
			
			if ($transaction_id !== NULL)
			{
				$data['transaction_id'] = $transaction_id; 
			}
			
			if ($error_message !== NULL)
			{
				$data['error_message'] = $error_message; 
			}
			
			$this->EE->order_model->update_order($order_id, $data);
		}
	}
	
	public function set_purchased_items_status($ee_status=NULL, $order_id, $transaction_id = NULL )
	{
		if (empty($ee_status))
		{
			return NULL; 
		}
		if ($this->EE->cartthrob->store->config('save_purchased_items') && $this->order('purchased_items'))
		{
			$this->EE->load->model('purchased_items_model');

			foreach ($this->order('purchased_items') as $entry_id)
			{
				if (is_array($entry_id))
				{
					if ( array_key_exists('entry_id', $entry_id))
					{
						$var = NULL; 
						$entry_id = $var = $entry_id['entry_id'];
					}
					else
					{
						// @TODO... this should be an error
						return NULL; 
					}
				}
				$this->EE->purchased_items_model->update_purchased_item($entry_id, array(
					'status' => $ee_status
				));
			}

		}
	}
	
	public function round($number)
	{
		return $this->EE->cartthrob->round($number);
	}
	/* @NOTE remember that the cart has to have been saved first. This happens automatically in gateway exit offsite using save_cart_snapshot. If that's not used though, you'll have to manually save the cart. */
	public function relaunch_cart_snapshot($order_id)
	{
 		$this->EE->load->model('order_model'); 
		$data = $this->EE->order_model->get_cart_from_order($order_id);
		
		if ($data)
		{
			unset($this->EE->cartthrob); 
			$this->EE->cartthrob = Cartthrob_core::instance('ee', array('cart' => $data));
			return $data; 
		}
		return NULL; 
	}
	public function relaunch_cart($cart_id = NULL, $order_id = NULL)
	{
		if ($order_id && !$cart_id)
		{
			$this->EE->load->model('order_model'); 
			$cart_id = $this->EE->order_model->get_order_cart_id($order_id); 
		}

 		$this->EE->load->model('cart_model'); 
		$data = $this->EE->cart_model->read_cart($cart_id);

		if ($data)
		{
			unset($this->EE->cartthrob); 
			$this->EE->cartthrob = Cartthrob_core::instance('ee', array('cart' => $data));
			$this->EE->load->library('cartthrob_session', array('core' => $this, 'use_regenerate_id' => FALSE, 'use_fingerprint' => FALSE));
			$this->EE->cartthrob_session->set_cart_id($cart_id); 

			if (!empty($data['language']))
			{
				$this->EE->load->library('languages');
				$this->EE->languages->set_language($data['language']);
			}
			
			return $data; 
		}			

		return NULL; 
	}
	public function save_cart_snapshot($order_id, $inventory_processed = FALSE, $discounts_processed = FALSE)
	{
		$this->EE->load->model('order_model'); 
		// for backward compatibility I'm saving the session id in the order table.
		// systems that previously used session id to relaunch the session will at least be able to 
		// continue to use the same identifier. The CT session will be relaunched using the order id tied to the session. 
		$session_id = @session_id(); 
		if (!$session_id)
		{
			@session_start();
			$session_id = @session_id(); 
		}
 		$this->EE->order_model->save_cart_snapshot($order_id, $inventory_processed, $discounts_processed, $this->EE->cartthrob->cart_array(), $this->cart_id(), $session_id ); 
		
	}
	public function cart_id()
	{
		return $this->EE->cartthrob->cart->id();
	}
	public function get_order_status($order_id)
	{
		if ($this->current_status == NULL)
		{
			$this->EE->load->model("order_model");
	 		return $this->current_status = $this->EE->order_model->get_order_status($order_id);   
		}
		else
		{
			return $this->current_status; 
		}
		
	}
	// @TODO deprecate
	public function save_cart()
	{
		$this->EE->cartthrob->cart->save();
	}
	// @TODO deprecate
	public function process_cart()
	{
		$this->EE->cartthrob->process_discounts()
				->process_inventory();
	}
	// @TODO deprecate
	public function set_status_authorized($auth, $order_id,  $send_email=NULL)
	{
		$this->EE->load->helper('array');
		
		$auth = array_merge(
			array(
				'processing' => FALSE,
				'authorized' => TRUE,
				'declined' => FALSE,
				'failed' => FALSE,
				'error_message' => '',
				'transaction_id' => NULL, 
			),
			$auth
		);
		$customer_info = $this->EE->cartthrob->cart->customer_info();

		if ($this->get_order_status($order_id) != "completed" && $this->get_order_status($order_id) != "authorized")
		{        
			
 			$this->update_order(array('auth' => $auth));
			$this->set_order_meta($order_id, 'authorized', $this->EE->cartthrob->store->config('orders_default_status'), element('transaction_id', $auth),element('error_message', $auth) ); 
 
			$this->set_purchased_items_status($this->EE->cartthrob->store->config('purchased_items_default_status'), $order_id); 
 
			// PROCESS 
			$this->process_cart(); 

			$this->EE->load->model('order_model'); 
			$cart_id = $this->EE->order_model->get_order_cart_id($order_id);
			$this->clear_cart($cart_id); 

			if ($this->EE->extensions->active_hook('cartthrob_on_authorize') === TRUE)
			{
				$edata = $this->EE->extensions->call('cartthrob_on_authorize');
				if ($this->EE->extensions->end_script === TRUE) return;
			}
			// SEND EMAIL

			if ($send_email !==FALSE)
			{
				$this->EE->load->library('cartthrob_emails');
				if (is_array($send_email))
				{
					$this->send_email($send_email); 
 				}
				else
				{
					$emails = $this->EE->cartthrob_emails->get_email_for_event("completed"); 
					if (!empty($emails))
					{
						foreach ($emails as $email_content)
						{
							$this->EE->cartthrob_emails->send_email($email_content, $this->EE->cartthrob->cart->order()); 
						}
					}
				}
 			}
		}
		$this->save_cart(); 
		
		return NULL; 
	}
	public function send_email($email_content)
	{
		$this->EE->load->library('cartthrob_emails');
		$this->EE->cartthrob_emails->send_email_from_array($email_content, $this->EE->cartthrob->cart->order()); 
		
	}
	public function set_status_declined($auth, $order_id,   $send_email=NULL)
	{
		
		$auth = array_merge(
			array(
				'processing' => FALSE,
				'authorized' => FALSE,
				'declined' => TRUE,
				'failed' => FALSE,
				'error_message' => '',
				'transaction_id' => NULL, 
			),
			$auth
		);
		$customer_info = $this->EE->cartthrob->cart->customer_info();

		if ($this->get_order_status($order_id) != "completed" && $this->get_order_status($order_id) != "authorized")
		{
 			$this->update_order(array('auth' => $auth));
			
			$this->set_order_meta($order_id,'declined', $this->EE->cartthrob->store->config('orders_declined_status'), element('transaction_id', $auth),element('error_message', $auth)); 
 
			$this->set_purchased_items_status($this->EE->cartthrob->store->config('purchased_items_declined_status'), $order_id ); 
			
 			// SEND EMAIL
			if ($send_email !==FALSE )
			{
				$this->EE->load->library('cartthrob_emails');
				if (is_array($send_email))
				{
					$this->send_email($send_email); 
				}
				else
				{
					$emails = $this->EE->cartthrob_emails->get_email_for_event("declined"); 
					if (!empty($emails))
					{
						foreach ($emails as $email_content)
						{
							$this->EE->cartthrob_emails->send_email($email_content, $this->EE->cartthrob->cart->order()); 
						}
					}
				}
 			}
			
			if ($this->EE->extensions->active_hook('cartthrob_on_decline') === TRUE)
			{
				$this->EE->extensions->call('cartthrob_on_decline');
				if ($this->EE->extensions->end_script === TRUE) return;
			}
		}
		$this->save_cart(); 
		
		return NULL; 
	}
	
	public function set_status_failed($auth, $order_id,   $send_email=NULL)
	{
		$auth = array_merge(
			array(
				'processing' => FALSE,
				'authorized' => FALSE,
				'declined' => FALSE,
				'failed' => TRUE,
				'error_message' => 'Unknown Failure', // @TODO lang
				'transaction_id' => NULL, 
			),
			$auth
		);
		$customer_info = $this->EE->cartthrob->cart->customer_info();

		if ($this->get_order_status($order_id) != "completed" && $this->get_order_status($order_id) != "authorized")
		{
			$this->update_order(array('auth' => $auth));
 			
 
 			$this->set_order_meta($order_id,'failed', $this->EE->cartthrob->store->config('orders_failed_status'), element('transaction_id', $auth),element('error_message', $auth)); 
 
			$this->set_purchased_items_status($this->EE->cartthrob->store->config('purchased_items_failed_status'), $order_id ); 
 
			// SEND EMAIL
 			if ($send_email !==FALSE )
			{
				$this->EE->load->library('cartthrob_emails');
				if (is_array($send_email))
				{
					$this->send_email($send_email); 
				}
				else
				{
					$emails = $this->EE->cartthrob_emails->get_email_for_event("failed"); 
					if (!empty($emails))
					{
						foreach ($emails as $email_content)
						{
							$this->EE->cartthrob_emails->send_email($email_content, $this->EE->cartthrob->cart->order()); 
						}
					}
				}
 			}

			if ($this->EE->extensions->active_hook('cartthrob_on_fail') === TRUE)
			{
				$this->EE->extensions->call('cartthrob_on_fail');
				if ($this->EE->extensions->end_script === TRUE) return;
			}
			
		}
		$this->save_cart(); 
		
		
		return NULL; 
	}
	//@TODO add a reference to this function on order model sav_cart_to_order($entry_id, $inventory_processed=FALSE, $discounts_processed=FALSE, $cart = NULL) so that paypal can update this info
	public function set_status_processing($auth, $order_id, $send_email=NULL)
	{
		$auth = array_merge(
			array(
				'processing' => TRUE,
				'authorized' => FALSE,
				'declined' => FALSE,
				'failed' => FALSE,
				'error_message' => '',
				'transaction_id' => NULL, 
			),
			$auth
		);
		$customer_info = $this->EE->cartthrob->cart->customer_info();

		if ($this->get_order_status($order_id) != "completed" && $this->get_order_status($order_id) != "authorized" && $this->get_order_status($order_id)!="pending")
		{
			$this->update_order(array('auth' => $auth));
 			
  			$this->set_order_meta($order_id,'processing', $this->EE->cartthrob->store->config('orders_processing_status'),  element('transaction_id', $auth),element('error_message', $auth) ); 
 
			$this->set_purchased_items_status($this->EE->cartthrob->store->config('purchased_items_processing_status'), $order_id ); 

 
			if ($send_email !==FALSE )
			{
				$this->EE->load->library('cartthrob_emails');
				if (is_array($send_email))
				{
					$this->send_email($send_email); 
				}
				else
				{
					$emails = $this->EE->cartthrob_emails->get_email_for_event("processing"); 
					if (!empty($emails))
					{
						foreach ($emails as $email_content)
						{
							$this->EE->cartthrob_emails->send_email($email_content, $this->EE->cartthrob->cart->order()); 
						}
					}
				}
 			}

			if ($this->EE->extensions->active_hook('cartthrob_on_processing') === TRUE)
			{
				$this->EE->extensions->call('cartthrob_on_processing');
				if ($this->EE->extensions->end_script === TRUE) return;
			}
		}
		$this->save_cart(); 
		
		
		return NULL; 
	}
	public function set_status_pending($auth, $order_id,   $send_email=NULL)
	{
		$auth = array_merge(
			array(
				'processing' => TRUE,
				'authorized' => FALSE,
				'declined' => FALSE,
				'failed' => FALSE,
				'error_message' => $this->lang('status_pending'),
				'transaction_id' => '', 
			),
			$auth
		);
		if ($this->get_order_status($order_id) != "completed" && $this->get_order_status($order_id) != "authorized")
		{
			$this->update_order(array('auth' => $auth));
 			
 			$this->set_order_meta($order_id,'pending', $this->EE->cartthrob->store->config('orders_status_pending'),element('transaction_id', $auth),element('error_message', $auth) ); 
 			$this->set_purchased_items_status($this->EE->cartthrob->store->config('purchased_items_status_pending'), $order_id ); 

			if ($send_email !==FALSE )
			{
				if (is_array($send_email))
				{
 					$this->send_email($send_email); 
				}
				else
				{
					$emails = $this->EE->cartthrob_emails->get_email_for_event("pending"); 
					if (!empty($emails))
					{
						foreach ($emails as $email_content)
						{
							$this->EE->cartthrob_emails->send_email($email_content, $this->EE->cartthrob->cart->order()); 
						}
					}
				}
 			}
		}
		$this->save_cart(); 
		
		
		return NULL; 
	}
	public function set_status_expired($auth, $order_id, $send_email=NULL)
	{
		$auth = array_merge(
			array(
				'processing' => FALSE,
				'authorized' => FALSE,
				'declined' => FALSE,
				'failed' => TRUE,
				'error_message' => $this->lang('status_expired'),
				'transaction_id' => '', 
			),
			$auth
		);
		if ($this->get_order_status($order_id) != "completed" && $this->get_order_status($order_id) != "authorized")
		{
			$this->update_order(array('auth' => $auth));
 			
 			$this->set_order_meta($order_id,'expired', $this->EE->cartthrob->store->config('orders_status_expired'), element('transaction_id', $auth),element('error_message', $auth)); 
 			$this->set_purchased_items_status($this->EE->cartthrob->store->config('purchased_items_status_expired'), $order_id ); 

			if ($send_email !==FALSE )
			{
				if (is_array($send_email))
				{
 					$this->send_email($send_email); 
				}
				else
				{
					$emails = $this->EE->cartthrob_emails->get_email_for_event("expired"); 
					if (!empty($emails))
					{
						foreach ($emails as $email_content)
						{
							$this->EE->cartthrob_emails->send_email($email_content, $this->EE->cartthrob->cart->order()); 
						}
					}
				}
 			}
		}
		$this->save_cart(); 
		
		
		return NULL; 
	}
	public function set_status_canceled($auth, $order_id,  $send_email=NULL)
	{
		$auth = array_merge(
			array(
				'processing' => FALSE,
				'authorized' => FALSE,
				'declined' => FALSE,
				'failed' => TRUE,
				'error_message' => $this->lang('status_canceled'),
				'transaction_id' => '', 
			),
			$auth
		);

		$this->update_order(array('auth' => $auth));
		
		$this->set_order_meta($order_id, 'canceled', $this->EE->cartthrob->store->config('orders_status_canceled'),element('transaction_id', $auth),element('error_message', $auth) ); 
		$this->set_purchased_items_status($this->EE->cartthrob->store->config('purchased_items_status_canceled'), $order_id ); 

		if ($send_email !==FALSE )
		{
			if (is_array($send_email))
			{
				$this->send_email($send_email); 
			}
			else
			{
				$emails = $this->EE->cartthrob_emails->get_email_for_event("canceled"); 
				if (!empty($emails))
				{
					foreach ($emails as $email_content)
					{
						$this->EE->cartthrob_emails->send_email($email_content, $this->EE->cartthrob->cart->order()); 
					}
				}
			}
		}
		
 		$this->save_cart(); 
		
 
		return NULL; 
	}
	
	public function set_status_offsite($auth, $order_id,  $send_email=NULL)
	{
		$auth = array_merge(
			array(
				'processing' => TRUE,
				'authorized' => FALSE,
				'declined' => FALSE,
				'failed' => FALSE,
				'error_message' => $this->lang('status_offsite'),
				'transaction_id' => '', 
			),
			$auth
		);

		$this->update_order(array('auth' => $auth));
		
		$this->set_order_meta($order_id, 'offsite', $this->EE->cartthrob->store->config('orders_status_offsite'),element('transaction_id', $auth),element('error_message', $auth) ); 
		$this->set_purchased_items_status($this->EE->cartthrob->store->config('purchased_items_status_offsite'), $order_id ); 

		if ($send_email !==FALSE )
		{
			if (is_array($send_email))
			{
				$this->send_email($send_email); 
			}
			else
			{
				$emails = $this->EE->cartthrob_emails->get_email_for_event("offsite"); 
				if (!empty($emails))
				{
					foreach ($emails as $email_content)
					{
						$this->EE->cartthrob_emails->send_email($email_content, $this->EE->cartthrob->cart->order()); 
					}
				}
			}
		}
		
 		$this->save_cart(); 
		
 
		return NULL; 
	}
	public function set_status_voided($auth, $order_id,  $send_email=NULL)
	{
		$auth = array_merge(
			array(
				'processing' => FALSE,
				'authorized' => FALSE,
				'declined' => FALSE,
				'failed' => TRUE,
				'error_message' => $this->lang('status_voided'),
				'transaction_id' => '', 
			),
			$auth
		);

		$this->update_order(array('auth' => $auth));
		
		$this->set_order_meta($order_id,'voided', $this->EE->cartthrob->store->config('orders_status_voided'), element('transaction_id', $auth),element('error_message', $auth) ); 
		$this->set_purchased_items_status($this->EE->cartthrob->store->config('purchased_items_status_voided'), $order_id ); 

		if ($send_email !==FALSE )
		{
			if (is_array($send_email))
			{
				$this->send_email($send_email); 
			}
			else
			{
				$emails = $this->EE->cartthrob_emails->get_email_for_event("voided"); 
				if (!empty($emails))
				{
					foreach ($emails as $email_content)
					{
						$this->EE->cartthrob_emails->send_email($email_content, $this->EE->cartthrob->cart->order()); 
					}
				}
			}
		}
		$this->save_cart(); 
		
		
		
		return NULL; 
	}
	public function set_status_refunded($auth, $order_id,  $send_email=NULL)
	{
		$auth = array_merge(
			array(
				'processing' => FALSE,
				'authorized' => FALSE,
				'declined' => FALSE,
				'failed' => FALSE,
				'refunded'	=> TRUE,
				'error_message' => $this->lang('status_refunded'),
				'transaction_id' => '', 
			),
			$auth
		);
		$this->update_order(array('auth' => $auth));
		
		$this->set_order_meta($order_id,'refunded', $this->EE->cartthrob->store->config('orders_status_refunded'),element('transaction_id', $auth),element('error_message', $auth) ); 
		$this->set_purchased_items_status($this->EE->cartthrob->store->config('purchased_items_status_refunded'), $order_id ); 

		if ($send_email !==FALSE )
		{
			if (is_array($send_email))
			{
				$this->send_email($send_email); 
			}
			else
			{
				$emails = $this->EE->cartthrob_emails->get_email_for_event("refunded"); 
				if (!empty($emails))
				{
					foreach ($emails as $email_content)
					{
						$this->EE->cartthrob_emails->send_email($email_content, $this->EE->cartthrob->cart->order()); 
					}
				}
			}
		}
		$this->save_cart(); 
		
		
		return NULL; 
	}
	public function set_status_reversed($auth, $order_id,   $send_email=NULL)
	{
		$auth = array_merge(
			array(
				'processing' => FALSE,
				'authorized' => FALSE,
				'declined' => FALSE,
				'failed' => TRUE,
				'error_message' => $this->lang('status_reversed'),
				'transaction_id' => '', 
			),
			$auth
		);
		
		$this->update_order(array('auth' => $auth));
		
		$this->set_order_meta($order_id,'reversed', $this->EE->cartthrob->store->config('orders_status_reversed'), element('transaction_id', $auth),element('error_message', $auth)); 
		$this->set_purchased_items_status($this->EE->cartthrob->store->config('purchased_items_status_reversed'), $order_id ); 

		if ($send_email !==FALSE )
		{
			if (is_array($send_email))
			{
				$this->send_email($send_email); 
			}
			else
			{
				$emails = $this->EE->cartthrob_emails->get_email_for_event("reversed"); 
				if (!empty($emails))
				{
					foreach ($emails as $email_content)
					{
						$this->EE->cartthrob_emails->send_email($email_content, $this->EE->cartthrob->cart->order()); 
					}
				}
			}
		}
		$this->save_cart(); 
		
		
 		return NULL; 
	}
 
	/**
	 * gateway_order_update
	 *
	 * @param array $auth_array 
	 * @param string $order_id 
	 * @return void
	 * @author Chris Newton
	 * @since 1.0.0
	 */
	public function gateway_order_update($auth, $order_id, $return_url = NULL, $send_email=NULL)
	{
		$auth = array_merge(
			array(
				'processing' => FALSE,
				'authorized' => FALSE,
				'declined' => FALSE,
				'failed' => TRUE,
				'error_message' => '',
				'transaction_id' => '',
				'expired'	=> FALSE, 
				'canceled'	=> FALSE, 
				'voided'	=> FALSE,
				'pending'	=> FALSE,
				'refunded'	=> FALSE,
			),
			$auth
		);
		

		if ($this->get_order_status($order_id) != "completed" && $this->get_order_status($order_id) != "authorized")
		{        
			/////// AUTHORIZED
			if ($auth['authorized'] )
			{     
					$this->set_status_authorized($auth, $order_id,   $send_email); 
	 		}
			///////// DECLINED
			elseif ($auth['declined'])
			{            
				$this->set_status_declined($auth, $order_id,  $send_email); 
			}
			////////// PROCESSING
			elseif ($auth['processing'])
			{       
				$this->set_status_processing($auth, $order_id, $send_email); 
			}
			elseif ($auth['expired'])
			{       
				$this->set_status_expired($auth, $order_id, $send_email); 
			}
			elseif ($auth['canceled'])
			{       
				$this->set_status_canceled($auth, $order_id, $send_email); 
			}
			elseif ($auth['voided'])
			{       
				$this->set_status_voided($auth, $order_id, $send_email); 
			}
			elseif ($auth['refunded'])
			{       
				$this->set_status_refunded($auth, $order_id, $send_email); 
			}
			elseif ($auth['pending'])
			{       
				$this->set_status_pending($auth, $order_id, $send_email); 
			}
			////////// FAILED
			else
			{        
				$this->set_status_failed($auth, $order_id,  $send_email); 
			}
		}  
 		
		$this->save_cart();
		// REDIRECT
		if ($return_url)
		{
			return $this->final_redirect($return_url); 
		}
	}
	
	public function module_enabled($module)
	{
		return ! empty($this->modules[$module]);
	}
	
	public function apply($module, $function)
	{
		if ( ! $this->module_enabled($module))
		{
			return FALSE;
		}
		
		if ( ! method_exists($this->modules[$module], $function) || ! is_callable(array($this->modules[$module], $function)))
		{
			return FALSE;
		}
		
		$args = func_get_args();
		
		return call_user_func_array(array($this->modules[$module], $function), array_slice($args, 2));
	}
	
	/**
	 * validate and then attempt to charge the payment
	 *
	 * gateway		
	 * credit_card_number	required
	 * tax			required
	 * shipping		required
	 * subtotal		required
	 * total		required
	 * discount		required
	 * expiration_date	
	 * group_id		
	 * create_user		
	 * subscription		
	 * subscription_options	
	 * member_id		
	 * vault		
	 * 
	 * @param array   $options 
	 * 
	 * @return     FALSE if errors are encountered. errors can be found in $cartthrob_payments->errors()
	 * 		or $auth array
	 */
	public function checkout_start(array $options)
	{
		$this->EE->load->helper('array');
		$this->EE->load->library('form_builder'); 
		$this->EE->load->model('order_model');
		$this->EE->load->model('cartthrob_members_model');
		
		// rebill
		$order_id = element('order_id', $options);
		// rebill
		$subscription_id = element('subscription_id', $options);

		// admin update
		$update_order_id = element('update_order_id', $options);
		
		// subscription update
		$update_subscription_id = element('update_subscription_id', $options);
		
		// 2 whether this is a sub or not basedon the subscription id. $sub
		// $order_data needs to be from entry. 
		// member_id set in options
		
		// this is to update an order by passing in the order id. this has nothing to do with Rebills or Subscriptions
		if ($update_order_id)
		{
			if ($this->EE->order_model->can_update_order($update_order_id))
			{
				$order_data = $this->EE->order_model->get_order_from_entry($update_order_id);
				$order_data = array_merge($order_data, $this->EE->cartthrob->cart->customer_info()); 
				//relaunch the cart from this order
				$this->EE->cartthrob->cart = Cartthrob_core::create_child($this->EE->cartthrob, 'cart', $order_data);
				// is this data not IN the order_data already? 
				$order_entry = $this->EE->order_model->get_order($order_id);
				$options['member_id'] = $order_entry['author_id'];
				unset($order_entry);
				$order_id = $update_order_id; 
			}
			else
			{
				$this->add_error(lang('you_do_not_have_sufficient_permissions_to_update_this_order'));
				return FALSE;
			}
		}
		elseif($order_id)
		{
			$order_data = $this->apply('subscriptions', 'subscription_order_data', $order_id); 
		}
		elseif($update_subscription_id)
		{
			$this->EE->load->model('subscription_model');
			
			$subscription = $this->EE->subscription_model->get_subscription($update_subscription_id);

			$temp_order_id = element('order_id', $subscription); 
			if ($this->EE->order_model->can_update_order($temp_order_id))
			{
				$order_data = $this->EE->order_model->get_order_from_entry($temp_order_id);
				$order_data = array_merge($order_data, $this->EE->cartthrob->cart->customer_info()); 
				// is this data not IN the order_data already? 
				$order_data['subscription_options'] = element('subscription_options', $options); 
				
				if (element('allow_modification', $order_data['subscription_options']))
				{
					unset($order_data['subscription_options']['allow_modification']); 
				}
				$order_data['subscription'] = element('subscription', $options); 
				$order_entry = $this->EE->order_model->get_order($temp_order_id);
				$options['member_id'] = $order_entry['author_id'];
				$options['gateway'] = element('payment_gateway', $order_data); 
				
				//relaunch the cart from this order
				$this->EE->cartthrob->cart = Cartthrob_core::create_child($this->EE->cartthrob, 'cart', $order_data);
				
				unset($order_entry);
			}
			else
			{
				$this->add_error(lang('you_do_not_have_sufficient_permissions_to_update_this_order') ." c2");
				return FALSE;
			}
		}
 		
		if (!$update_subscription_id && !$subscription_id && empty($order_data) && $this->EE->cartthrob->cart->is_empty() && ! $this->EE->config->item('cartthrob:allow_empty_cart_checkout'))
		{
			$this->add_error(lang('empty_cart'));
			
			return FALSE;
		}
		
		if ($gateway = element('gateway', $options))
		{
			$this->EE->cartthrob_payments->set_gateway($gateway);
		}
		
		if ( ! $this->gateway())
		{
			$this->add_error(lang('invalid_payment_gateway'));
			
			return FALSE;
		}
		
		$this->EE->cartthrob->cart->check_inventory();
		
		if ($this->EE->cartthrob->errors())
		{
			$this->add_error($this->EE->cartthrob->errors());
			
			return FALSE;
		}
		
		$credit_card_number = element('credit_card_number', $options);

		$entry_id = '';
		
		$this->EE->load->library('api/api_cartthrob_tax_plugins');
		
		$expiration_date = element('expiration_date', $options);
		
		$group_id = element('group_id', $options, 5);
		
		$admin = in_array($this->EE->session->userdata('group_id'), $this->EE->config->item('cartthrob:admin_checkout_groups'));
		
		if ($admin &&  $this->EE->cartthrob->cart->customer_info('email_address') == element('create_email', $options) )
		{
			// admin is checking out with own member info while create_user is turned on. 
			// we're tuning create user off
 			if (element('create_user', $options))
			{
				unset ($options['create_user']); 
			}
		}
		
		if (element('create_user', $options) && ( ! $this->EE->session->userdata('member_id') || $admin))
		{
			// sending the initial set of customer supplied data
			$options['create_user'] = $this->EE->cartthrob_members_model->validate_member(
				element('create_username', $options),
				element('create_email', $options),
				element('create_screen_name', $options),
				element('create_password', $options),
				element('create_password_confirm', $options),
				element('create_group_id', $options),
				element('create_language', $options)
			);
			
			// should only be an FALSE if errors are returned
			if ($options['create_user'] === FALSE)
			{
				$this->add_error($this->EE->cartthrob_members_model->errors);
				
				return FALSE;
			}
		}
		else
		{
			// person's already logged in and not an admin. 
			// if we leave create user on, some redirect gateways that respawn the cart are left looking 
			// to update the member id with a blank member id. 
			$options['create_user'] = FALSE; 
		}

		if (!empty($order_data))
		{
			$this->set_total($order_data['total']);
			
			if ($this->EE->extensions->active_hook('cartthrob_pre_process') === TRUE)
			{
				$this->EE->extensions->call('cartthrob_pre_process', $options);
				if ($this->EE->extensions->end_script === TRUE) return;
			}
		}
		else
		{
			$this->EE->cartthrob->cart->set_calculation_caching(FALSE);
			
			$tax = isset($options['tax']) ? $options['tax'] : $this->EE->cartthrob->cart->tax();
			$shipping = isset($options['shipping']) ? $options['shipping'] : $this->EE->cartthrob->cart->shipping();
			$subtotal = isset($options['subtotal']) ? $options['subtotal'] : $this->EE->cartthrob->cart->subtotal();
			$discount = isset($options['discount']) ? $options['discount'] : $this->EE->cartthrob->cart->discount();
			$total = isset($options['total']) ? $options['total'] : $this->EE->cartthrob->cart->total();
			// only missing if tax or price were manually passed. 
			$subtotal_plus_tax = isset($options['subtotal_plus_tax']) ? $options['subtotal_plus_tax'] : $subtotal + $tax;
			// only missing if tax or shipping were manually passed.
			if (isset($options['shipping_plus_tax']))
			{
				$shipping_plus_tax = $options['shipping_plus_tax'];
			}
			else
			{
				$subtotal_plus_shipping = $subtotal + $shipping;
				// need to find the effective tax rate, since we may be ignoring the tax plugin itself by using a manual tax value.
				$tax_rate = $subtotal_plus_shipping > 0 ? $tax / ($subtotal + $shipping) : 0;
				$shipping_plus_tax = $shipping + ($tax_rate * $shipping);
			}
			
			$this->set_total($total);
			
			if ($this->EE->extensions->active_hook('cartthrob_pre_process') === TRUE)
			{
				$this->EE->extensions->call('cartthrob_pre_process', $options);
				if ($this->EE->extensions->end_script === TRUE)  return;
			}
			
			
			$vars = array(
				'shipping'					=> $shipping,
				'shipping_plus_tax'			=> $shipping_plus_tax,
				'tax'						=> $tax,
				'subtotal'					=> $subtotal,
				'subtotal_plus_tax'			=> $subtotal_plus_tax, 
				'discount'					=> $discount,
				'total'						=> $this->total(),
				'credit_card_number'		=> $credit_card_number,
				'create_user'				=> element('create_user', $options),
				'group_id'					=> $group_id, 
				'member_id'					=> $this->EE->session->userdata('member_id'), 
				'subscription'				=> element('subscription', $options),
				'subscription_options'		=> element('subscription_options', $options, array()),
				'payment_gateway'			=> $gateway,
				'subscription_id'			=> element('subscription_id', $options),
			);
			
			$order_data = $this->EE->order_model->order_data_array($vars);
		}
		
 		if (!$update_subscription_id && $this->EE->cartthrob->store->config('save_orders'))
		{
 			// this is passed from process_subscription
			if (isset($options['subscription_options']) && !empty($options['subscription_options']))
			{
				$order_data['entry_id']= NULL;
				$order_data['auth'] = array(); 
				$order_data['invoice_number']= NULL; 
				$order_data['title']= NULL; 
				$order_data['transaction_id']= NULL; 
				$order_data['processing']= NULL; 
				$order_data['authorized']= NULL; 
				$order_data['declined']= NULL; 
				$order_data['failed']= NULL; 
				$order_data['error_message']= NULL; 

				$shipping = $this->EE->cartthrob->cart->shipping(); 
				$subtotal = $this->EE->cartthrob->cart->subtotal(); 
				$discount = $this->EE->cartthrob->cart->discount(); 
				
				$tax = $this->EE->cartthrob->cart->tax(); 
				$subtotal_plus_tax = $subtotal + $tax; 
				$subtotal_plus_shipping =  $subtotal + $shipping; 
 				$shipping_plus_tax = $this->EE->cartthrob->cart->shipping_plus_tax();
				$tax_rate = $subtotal_plus_shipping > 0 ? $tax / ($subtotal + $shipping) : 0;
				
				$order_data['shipping'] = $shipping; 
				$order_data['discount'] = $discount; 
				$order_data['tax'] = $tax; 
				$order_data['subtotal'] = $subtotal; 
				$order_data['subtotal_plus_tax'] = $subtotal_plus_tax; 
				$order_data['subtotal_plus_shipping'] = $subtotal_plus_shipping; 
				$order_data['tax_rate'] = $tax_rate; 
				$order_data['shipping_plus_tax'] = $shipping_plus_tax;
				
				if (element('member_id', $options))
				{
					$order_data['member_id'] = element('member_id', $options); 
					$order_data['author_id'] = element('member_id', $options); 
				}
				// @TODO confirm that this is setting the total the way we want, and not recalculating everythin
				$total =  $this->EE->cartthrob->cart->total();
				$order_data['total']= $total;
  				$order_id = NULL; 
				$this->set_total($order_data['total']);
			}
 			if ( ! $order_id)
			{
				if ( ! empty($expiration_date))
				{
					$order_data['expiration_date'] = $expiration_date;
				}
				
				$this->EE->load->model('order_model');
				
				$order_entry = $this->EE->order_model->create_order($order_data);
				
				$order_data['entry_id'] = $order_data['order_id'] = $order_entry['entry_id'];
				
				$order_data['title'] = $order_data['invoice_number'] = $order_entry['title'];
				
				unset($order_data['expiration_date']);
			}
		}
		else
		{
			$order_data['title'] = $order_data['invoice_number'] = '';
		}
		
		
		//save order to session
		$this->EE->cartthrob->cart->set_order($order_data);
		
		//you can provide a vault in the options array, instead of fetching/creating one
		$vault = element('vault', $options);
		
		$force_vault = element('force_vault', $options);
		
		/**
		 * Subscriptions Start
		 */

		$has_subscription = $this->apply('subscriptions', 'subscriptions_initialize', element('subscription', $options), element('subscription_options', $options, array()));
		
		/**
		 * Subscriptions End
		 */
		$member_id = element('member_id', $options, $this->EE->session->userdata('member_id'));
		
		if ($has_subscription || $force_vault)
		{
			// no member data here. create a random member
			if ( ! $member_id && !isset($options['create_user']) )
			{
				$options['create_user'] = TRUE; // this will tell the next bit to create a member
			}
			
			// creating and logging in the user if there's a sub / vault
			if (isset($options['create_user']) && $options['create_user'] == TRUE)
			{
				if (!$member_id)
				{
					$member_id = $this->create_member($options['create_user']); 
					unset($options['create_user']); 
				}
				$group_id = "4"; 
				if (element('create_group_id', $options)  && ! empty($member_id))
				{
					$group_id = element('create_group_id', $options);
				}
				
				// have to set the member group here, or they can't be logged in
 				$this->EE->cartthrob_members_model->set_member_group($member_id, $group_id); 
				// admins... you get booted
				// we're logging this person in... if there's an error and they "create_user" again, it'll be ignored, because they're logged in already.
				$this->EE->cartthrob_members_model->login_member($member_id);
			
				$this->EE->session->cache['cartthrob']['member_id'] = $member_id;
				
				if (!empty( $order_data['order_id']))
				{
					$this->save_member_with_order($member_id, $order_data['order_id'], $this->order() ); 
				}
			}
			
			//if there's not already a vault provided, fetch an existing one
			//if there's not an existing vault, make one
			if ( ! $vault || $update_subscription_id)
			{
				$this->EE->load->model('vault_model');
				$vault = $this->EE->vault_model->get_member_vault($member_id, $gateway);
				// if we're updating or there's not vault saved either.. create one.
				if ($update_subscription_id || (!$vault ||  empty($vault['token'])))
				{
					// if this is an offsite token generation system like SagePay server, we lose them here. checkout complete offsite needs to handle this. 
					$token = $this->create_token($credit_card_number); 
					
					if (is_object($token) && $token->error_message())
					{
						return array(
							'processing' => FALSE,
							'authorized' => FALSE,
							'declined' => FALSE,
							'failed' => TRUE,
							'error_message' => $token->error_message(),
							'transaction_id' => '',
						);
					}
					elseif (!is_object($token))
					{
						$error =  $this->EE->lang->line('token_method_returning_bad_response'); 
						
						if (isset($token['error_message']))
						{
							$error = $token['error_message'];
						}
						return array(
							'processing' => FALSE,
							'authorized' => FALSE,
							'declined' => FALSE,
							'failed' => TRUE,
							'error_message' => $error, 
							'transaction_id' => '',
						);
					}
					
					$new_vault = array(
						'customer_id' => $token->customer_id(),  
						'token' => $token->token(),
						'order_id' => $this->EE->cartthrob->cart->order('order_id'),
						'member_id' => $this->EE->cartthrob_members_model->get_member_id(),
						'gateway' => $gateway,
						'last_four' => substr($credit_card_number, -4),
					);
					
					if (!empty($vault['id']))
					{
						// if we were returned something without a token, we don't want to update this
						// this might happen if a member existed, and were somehow using the vault id of a different member. 
						// not that we want THAT to happen either by accident, but it's possible it might happen on purpose.
						if (!empty($new_vault['token']))
						{
							$vault['id'] = $this->EE->vault_model->update($new_vault, $vault['id']);
						}
					}
					elseif (!empty($new_vault['token']))
					{
						$vault['id'] = $this->EE->vault_model->update($new_vault);
					}
					if (!empty($new_vault['token']) && !empty($vault['id']) && $update_subscription_id)
					{
						
						$sub_update_data['vault_id'] = $vault['id'];
						$this->EE->load->model('subscription_model');
						$this->EE->subscription_model->update($sub_update_data, $update_subscription_id);
					}
					$vault = array_merge($vault,$new_vault); 
				}
			}
		}
		
		if ($vault)
		{
			$this->EE->cartthrob->cart->update_order(array(
				'vault_id' => $vault['id'],
			));
			
			$this->EE->order_model->update_order(
				$this->EE->cartthrob->cart->order('entry_id'),
				array(
					'vault_id' => $vault['id'],
				)
			);
			
			$this->EE->cartthrob->cart->save();
		}
		
		if (element('force_processing', $options))
		{
			return array(
				'processing' => TRUE,
				'authorized' => FALSE,
				'declined' => FALSE,
				'failed' => FALSE,
				'error_message' => '',
				'transaction_id' => '',
			);
		}
		if ($update_subscription_id)
		{
			$this->EE->cartthrob->cart->update_order(array(
				'subscription_update_id' => $update_subscription_id,
			));
		}

		if ($vault && !$update_subscription_id)
		{	
			if (isset($token) && $token->offsite())
			{
				$transaction = $this->EE->cartthrob_payments->charge_token($vault['token'], $vault['customer_id'], $offsite= TRUE);
			}
			else
			{
				$transaction = $this->EE->cartthrob_payments->charge_token($vault['token'], $vault['customer_id']);
 			}
			
			if (isset($transaction['authorized']) &&  $transaction['authorized'] != TRUE)
			{
				// this is a bad token. We need to disable it. 
				// this could cause problems if 
 				if (isset($vault['id']) && $vault['id'])
				{
					if ((isset($transaction['failed']) && $transaction['failed'] === TRUE ) || (isset($transaction['declined']) && $transaction['declined'] === TRUE )  )
					{
						// by deleting this, it's possible that rebills using the same token will fail, unless the user immediately updates their vault
						$this->EE->load->model('vault_model');
						$update_data = array(
							'token'	=> NULL
						); 
						$this->EE->vault_model->update($update_data, $vault['id']); 
					}
				}
				return $transaction;
			}

		}
		elseif($update_subscription_id)
		{
			// if it's offsite, we need to bill it now to get the token. otherwise a token isn't actually created
			if (isset($token) && $token->offsite())
			{
				$this->EE->cartthrob->cart->set_meta('last_bill_date', $this->EE->localize->now); 
				$this->EE->cartthrob->cart->set_meta('used_occurrences', 1); // we'll figure out whether this is trial or regular price later
				$transaction = $this->EE->cartthrob_payments->charge_token($vault['token'], $vault['customer_id'], $offsite= TRUE);
			}
			else
			{
				$transaction = array(
					'processing' => FALSE,
					'authorized' => TRUE, // this shoudl be true, because if creating a vault went wrong, it should respond with an error
					'declined' => FALSE,
					'failed' => FALSE,
					'error_message' => '',
					'transaction_id' => '',
				);
				// we don't want to ALSO bill it now that we've created it. 
				//$transaction = $this->EE->cartthrob_payments->charge_token($vault['token'], $vault['customer_id']);
			}
		}
		else //a normal payment
		{
			if ($this->EE->cartthrob->store->config('modulus_10_checking') && ! modulus_10_check($credit_card_number))
			{	
				$this->add_error($this->EE->lang->line('validation_card_modulus_10'));
				
				return FALSE;
			}
			
			$this->EE->cartthrob->cart->save();
			
			// IF the payment gateway directs users offsite, we will lose them at this point.
			// so the second half of the process is offloaded. 
			$transaction = $this->EE->cartthrob_payments->charge($credit_card_number);
		}
		return $transaction;
	}
	public function save_member_with_order($member_id, $order_id, $order_data = NULL)
	{
		$this->EE->load->model('cartthrob_members_model');
		
		$this->EE->cartthrob->cart->update_order(array('member_id' => $member_id));
		$this->update_order_by_id($order_id, array("author_id" => $member_id));

		$this->EE->cartthrob->cart->save();

		$this->EE->cartthrob->save_customer_info();
		
		if ($this->EE->cartthrob->store->config('save_member_data') && $order_data)
		{
			$this->EE->cartthrob_members_model->update_member($member_id, $order_data );
		}
	}
	public function create_member($options = array(), $order_data = NULL )
	{
 		$this->EE->load->model('cartthrob_members_model');
		// could accidentally be a boolean if member details weren't already put together. 
		if (!$options || !is_array($options))
		{
			// no user data. lets create some
			$options = $this->EE->cartthrob_members_model->generate_random_member_data(); 
		}
		
		$this->EE->cartthrob->cart->update_order(array('create_user' => $options));
		#$this->EE->cartthrob->cart->save();
		
		$temp_user = array_merge($options, array('group_id' => $this->pending_group_id) ); 
 		$member_id = $this->EE->cartthrob_members_model->create_member($temp_user);

		return $member_id; 
	}
	// @deprecated
	public function set_member_group($member_id, $group_id)
	{
		$this->EE->load->model('cartthrob_members_model');
		$this->EE->cartthrob_members_model->set_member_group($member_id, $group_id);
	}

	// @deprecated
	public function activate_member($member_id, $group_id = NULL)
	{
		$this->EE->load->model('cartthrob_members_model');
		$this->EE->cartthrob_members_model->activate_member($member_id, $group_id);
	}
	// @TODO sage uses cancelled status. need to update this to handle that. 
	
	// @TODO make sure that $this->EE->cartthrob->cart->whatever works. Might need 	
	public function checkout_complete($auth, $template = NULL, $return = NULL, $stop_processing = FALSE)
	{
		$secure_forms = TRUE; 
		/*
		NOTES: regarding an active session
		1.  logging in customer requires an active session. if run from a cul-de-sac payment gateway, the user won't be logged-in when they leave the gateway
		2. Process discounts & inventory. Does this requires an active session. If so the session needs to be relaunched to handle this. 
		so...use checkout_complete_offsite
		*/
		
		$auth = array_merge(
			array(
				'processing' => FALSE,
				'authorized' => FALSE,
				'declined' => FALSE,
				'failed' => TRUE,
				'error_message' => '',
				'transaction_id' => '',
			),
			$auth
		);
		
		$this->EE->cartthrob->cart->update_order(array_merge($auth, array('auth' => $auth)));

		$order_id = $this->EE->cartthrob->cart->order('order_id');
		
		$this->EE->session->set_flashdata($auth);
		
		//since we use the authorized variables as tag conditionals in submitted_order_info,
		//we won't throw any errors from here on out
		$this->EE->form_builder->set_show_errors(FALSE);
		
		if (isset($_POST['ERR']))
		{
			unset($_POST['ERR']);
		}
		$admin = in_array($this->EE->session->userdata('group_id'), $this->EE->config->item('cartthrob:admin_checkout_groups'));
		$admin_id = NULL; 
		if ($admin)
		{
			$admin_id = $this->EE->session->userdata('member_id'); 
		}

		// checking to see if this is already complete to keep from getting multiple emails or other processing duplication errors. 
		$this->EE->load->model('order_model');
		
		$order_status = $this->EE->order_model->get_order_status($order_id);
		// update 
 		if ($this->EE->cartthrob->cart->order('subscription_update_id'))
		{
			$this->apply('subscriptions', 'subscriptions_start', $auth);
			$this->apply('subscriptions', 'subscriptions_complete', $auth);

				/*
				$update_data = array(
					'status' => ($this->EE->cartthrob->store->config('orders_default_status')) ? $this->EE->cartthrob->store->config('orders_default_status') : 'open',
					'transaction_id' => element('transaction_id', $auth),
					'edit_date' => $this->EE->localize->now
				);
				*/ 
				$this->EE->load->model('order_model');
				$order_data = $this->EE->order_model->order_data_array(array());
				$order_data = array_merge($order_data, $this->EE->cartthrob->cart->customer_info()); 
				$order_data['title'] = $order_data['items'] = $order_data['custom_data'] = $order_data['subscription_options']  = $order_data['invoice_number'] = '';
				$order_data = array_filter($order_data); // getting rid of the empties.
 				
				$this->EE->order_model->update_order($order_id, $order_data);
				$this->EE->cartthrob->cart->set_order($order_data);
 
				$this->EE->cartthrob->cart->update_order($order_data);
	 			$this->EE->cartthrob->cart->save();

			if ($this->EE->cartthrob->store->config('save_orders'))
			{
				$this->set_order_meta($order_id, 'authorized', $this->EE->cartthrob->store->config('orders_default_status'), element('transaction_id', $auth),element('error_message', $auth), array() );
			}
		
 			if (! $template && ! $return)
			{
				$this->EE->form_builder->set_return($this->EE->cartthrob->cart->order('authorized_redirect'));
 			}
		}
		// rebill
		elseif ($this->EE->cartthrob->cart->order('existing_subscription_items'))
		{
			$this->apply('subscriptions', 'subscriptions_start', $auth);
			$this->apply('subscriptions', 'subscriptions_complete', $auth);
			if ($auth['authorized'])
			{
				$this->set_status_authorized($auth, $order_id,  $send_email=FALSE); 
			}
		}
 		elseif ($order_status === 'authorized' || $order_status === 'completed')
		{
			if ($this->EE->cartthrob->store->config('save_orders'))
			{
				$this->set_order_meta($order_id, 'authorized', $this->EE->cartthrob->store->config('orders_default_status'), element('transaction_id', $auth),element('error_message', $auth), array() );
			}
		
 			if (! $template && ! $return)
			{
				$this->EE->form_builder->set_return($this->EE->cartthrob->cart->order('authorized_redirect'));
 			}
 		}
		else
		{
			$this->apply('subscriptions', 'subscriptions_start', $auth);

			if ( ! $auth['authorized'])
			{
				$this->apply('subscriptions', 'subscriptions_complete', $auth);
			}

			if ($auth['authorized'])
			{
				$this->EE->load->model('cartthrob_members_model');

				if ($this->EE->cartthrob->cart->order('create_user') && (! $this->EE->session->userdata('member_id') ||  $admin))
				{
					$member_id = $this->create_member($this->EE->cartthrob->cart->order('create_user')); 
					$group_id = element('group_id', $this->EE->cartthrob->cart->order('create_user')); 

					// going to log in this new member and save the data
					if ($admin_id)
					{
	 					$this->EE->cartthrob_members_model->login_member($member_id);
						$secure_forms = FALSE; // we have to set this to false, due to EE's use of session id in secure forms checking. 
					}

					$this->EE->cartthrob->save_customer_info();
					$this->EE->cartthrob->cart->save(); 

					$this->save_member_with_order($member_id, $this->order('entry_id'), $this->order() ); 
					if ($group_id && ! empty($member_id))
					{
						$this->EE->cartthrob_members_model->activate_member($member_id, $group_id ); 
						$secure_forms = FALSE; // we have to set this to false, due to EE's use of session id in secure forms checking. 
					}

					if ($member_id)
					{
						$this->EE->session->cache['cartthrob']['member_id'] = $member_id;

						$update_data['author_id'] = $member_id;
					}
					$this->update_order_by_id($order_id, $update_data);

				}
				elseif($this->EE->cartthrob->cart->meta('checkout_as_member'))
				{
					$member_id = $this->EE->cartthrob->cart->meta('checkout_as_member'); 
					// going to log in this new member and save the data
					if ($admin_id)
					{
	 					$this->EE->cartthrob_members_model->login_member($member_id);
						$secure_forms = FALSE; // we have to set this to false, due to EE's use of session id in secure forms checking. 
					}
					$this->EE->cartthrob->cart->set_meta('checkout_as_member', FALSE); 
					$this->EE->cartthrob->save_customer_info();
					$this->EE->cartthrob->cart->save(); 

					$this->save_member_with_order($member_id, $this->order('entry_id'), $this->order() );
				}

				$update_data = array(
					'status' => ($this->EE->cartthrob->store->config('orders_default_status')) ? $this->EE->cartthrob->store->config('orders_default_status') : 'open',
					'transaction_id' => element('transaction_id', $auth)
				);
				if ($this->EE->cartthrob->store->config('save_orders'))
				{
					$this->set_order_meta($order_id, 'authorized', $this->EE->cartthrob->store->config('orders_default_status'), element('transaction_id', $auth),element('error_message', $auth), $update_data );
				}

				if ($this->EE->cartthrob->store->config('save_purchased_items') && $this->EE->cartthrob->cart->order('items'))
				{
					$this->EE->load->model('purchased_items_model');

					$purchased_items = array();

					foreach ($this->EE->cartthrob->cart->order('items') as $row_id => $item)
					{
						//if it's a package, we'll make purchased items from the sub_items and not the package itself
						if ( ! empty($item['sub_items']))
						{
							foreach ($item['sub_items'] as $_row_id => $_item)
							{
								$_item['package_id'] = $item['entry_id']; 

								$purchased_items[$row_id.':'.$_row_id] = $this->EE->purchased_items_model->create_purchased_item($_item, $order_id, $this->EE->cartthrob->store->config('purchased_items_default_status'));
							}
							
							// this will also save the package
							if ($this->EE->cartthrob->store->config('save_packages_too'))
							{
								$purchased_items[$row_id] = $this->EE->purchased_items_model->create_purchased_item($item, $order_id, $this->EE->cartthrob->store->config('purchased_items_default_status'));
							}
						}
						else
						{
							$purchased_items[$row_id] = $this->EE->purchased_items_model->create_purchased_item($item, $order_id, $this->EE->cartthrob->store->config('purchased_items_default_status'));
						}
					}

					$this->EE->cartthrob->cart->update_order(array('purchased_items' => $purchased_items));
				}

				$this->apply('subscriptions', 'subscriptions_complete', $auth);

				////////////// begin permissions /////////////////////////////
				$permissions = array(); 
				
				foreach ($this->EE->cartthrob->cart->order('items') as $row_id => $item)
				{
					// subs takes care of its own permissions. skip permission items
 					if ( ! empty($item['meta']['permissions']) && empty($item['meta']['subscription']))
					{
						$this->EE->load->model('permissions_model'); 

						$perms = array(); 
						if (!is_array($item['meta']['permissions']))
						{
							$perms = explode('|', $item['meta']['permissions']);
						}
						else
						{
							if (isset($item['meta']['permissions']))
							{
								$perms = (array) $item['meta']['permissions']; 
							}
						}
						
						foreach ($perms as $perm)
						{
							$id = $this->EE->permissions_model->update(array(
								'permission' => $perm,
								'order_id' => $this->EE->cartthrob->cart->order('entry_id'),
								'member_id' => $this->EE->cartthrob_members_model->get_member_id(),
								'item_id' => $item['product_id'],
							));
						}
						
					}
				}
				
 				////////////// end permissions /////////////////////////////
				if ($this->EE->extensions->active_hook('cartthrob_on_authorize') === TRUE)
				{
					$this->EE->extensions->call('cartthrob_on_authorize');
					if ($this->EE->extensions->end_script === TRUE) return;
				}

				// @NOTE 2. (see above)
				$this->EE->cartthrob->process_discounts()->process_inventory();
				// @NOTE 2. (see above)
				$this->EE->cartthrob->cart->clear()
							  ->clear_coupon_codes()
							  ->clear_totals();
				// turning this off for next order
				$this->EE->cartthrob->cart->set_customer_info('use_billing_info', '0');

				$emails = $this->EE->cartthrob_emails->get_email_for_event("completed"); 
				if (!empty($emails))
				{
					foreach ($emails as $email_content)
					{
						$this->EE->cartthrob_emails->send_email($email_content, $this->order()); 
					}
				}

				if (! $template && ! $return)
				{
					$this->EE->form_builder->set_return($this->EE->cartthrob->cart->order('authorized_redirect'));
				}
			}
			elseif ($auth['declined'])
			{
				if ($this->EE->cartthrob->store->config('save_orders'))
				{
		 			$this->set_order_meta($order_id,
											'declined', 
											$this->EE->cartthrob->store->config('orders_declined_status'), 
											element('transaction_id', $auth),
											$this->EE->lang->line('declined').': '.element('error_message', $auth)
										);
				}

				if ($this->EE->cartthrob->store->config('save_purchased_items') && $this->EE->cartthrob->cart->order('purchased_items'))
				{
					foreach ($this->EE->cartthrob->cart->order('purchased_items') as $entry_id)
					{
						$this->EE->load->model('purchased_items_model');
						$this->EE->purchased_items_model->update_purchased_item($entry_id, array(
							'status' => $this->EE->cartthrob->store->config('purchased_items_declined_status')
						));
					}
				}

				if ($this->EE->extensions->active_hook('cartthrob_on_decline') === TRUE)
				{
					$this->EE->extensions->call('cartthrob_on_decline');
					if ($this->EE->extensions->end_script === TRUE) return;
				}
				$emails = $this->EE->cartthrob_emails->get_email_for_event("declined"); 
				if (!empty($emails))
				{
					foreach ($emails as $email_content)
					{
						$this->EE->cartthrob_emails->send_email($email_content, $this->order()); 
					}
				}

				if (! $template && ! $return )
				{
					$this->EE->form_builder->set_return($this->EE->cartthrob->cart->order('declined_redirect'))
							       ->add_error(element('error_message', $auth));
				}
			}
			elseif ($auth['processing'])
			{
				if ($this->EE->cartthrob->store->config('save_orders'))
				{
		 			$this->set_order_meta($order_id,
											'processing', 
											$this->EE->cartthrob->store->config('orders_processing_status'), 
											element('transaction_id', $auth),
											$this->EE->lang->line('processing').': '.element('error_message', $auth)
										);
				}

				if ($this->EE->cartthrob->store->config('save_purchased_items') && $this->EE->cartthrob->cart->order('purchased_items'))
				{
					foreach ($this->EE->cartthrob->cart->order('purchased_items') as $entry_id)
					{
						$this->EE->load->model('purchased_items_model');
						$this->EE->purchased_items_model->update_purchased_item($entry_id, array(
							'status' => $this->EE->cartthrob->store->config('purchased_items_processing_status')
						));
					}
				}

				if ($this->EE->extensions->active_hook('cartthrob_on_processing') === TRUE)
				{
					$this->EE->extensions->call('cartthrob_on_processing');
					if ($this->EE->extensions->end_script === TRUE) return;
				}

				$this->EE->cartthrob->cart->clear()
							  ->clear_coupon_codes()
							  ->clear_totals();

				// turning this off for next order
				$this->EE->cartthrob->cart->set_customer_info('use_billing_info', '0');

				$emails = $this->EE->cartthrob_emails->get_email_for_event("processing"); 
				if (!empty($emails))
				{
					foreach ($emails as $email_content)
					{
						$this->EE->cartthrob_emails->send_email($email_content, $this->order()); 
					}
				}

				if (! $template && ! $return)
				{
					$this->EE->form_builder->set_return($this->EE->cartthrob->cart->order('processing_redirect'))
							       ->add_error(element('error_message', $auth));
				}
			}
			elseif ($auth['failed'])
			{
				if ($this->EE->cartthrob->store->config('save_orders'))
				{
					$this->set_order_meta(	$order_id, 
											'failed', 
											$this->EE->cartthrob->store->config('orders_failed_status'), 
											element('transaction_id', $auth),
											$this->EE->lang->line('failed').': '.element('error_message', $auth)
											);
				}

				if ($this->EE->cartthrob->store->config('save_purchased_items') && $this->EE->cartthrob->cart->order('purchased_items'))
				{
					foreach ($this->EE->cartthrob->cart->order('purchased_items') as $entry_id)
					{
						$this->EE->load->model('purchased_items_model');
						$this->EE->purchased_items_model->update_purchased_item($entry_id, array(
							'status' => $this->EE->cartthrob->store->config('purchased_items_failed_status')
						));
					}
				}
				if ($this->EE->extensions->active_hook('cartthrob_on_fail') === TRUE)
				{
					$this->EE->extensions->call('cartthrob_on_fail');
					if ($this->EE->extensions->end_script === TRUE) return;
				}

				$emails = $this->EE->cartthrob_emails->get_email_for_event("failed"); 
				if (!empty($emails))
				{
					foreach ($emails as $email_content)
					{
						$this->EE->cartthrob_emails->send_email($email_content, $this->order()); 
					}
				}

				if (! $template && ! $return)
				{
					$this->EE->form_builder->set_return($this->EE->cartthrob->cart->order('failed_redirect'))
							       ->add_error(element('error_message', $auth));
				}
			}
		}

		if (! $admin || !isset($member_id))
		{
			$this->EE->cartthrob->cart->save(); 
		}
		// if you're just an admin, we don't want to log you back in, or else your old cart will pop back up and never erase.
		elseif ($admin_id && isset($member_id))
		{
			$this->EE->load->model('cartthrob_members_model'); 
			// making sure the admin's logged back in. earlier we log in the new temp user to save their details
			$this->EE->cartthrob_members_model->login_member($admin_id);
			// now we can save the cart. 
			// added this in after it came to my attention that the cart was not clearing upon successful transaction for admins
			// saving it... will save the cart clearing. 
			$this->EE->cartthrob->cart->save(); 

			$secure_forms = FALSE; // we have to set this to false, due to EE's use of session id in secure forms checking. 
		}
		else
		{
			$this->EE->cartthrob->cart->save(); 
			$secure_forms = FALSE; // we have to set this to false, due to EE's use of session id in secure forms checking. 
		}
		
 		if ( $return )
		{
			if ( ! preg_match('#^https?://#', $return))
			{
				$return = $this->EE->functions->create_url($return);
			}
			
			$this->EE->functions->redirect($return);
			exit; 
		}
		elseif ( $template )
		{
			echo $this->EE->template_helper->parse_template( $this->EE->template_helper->fetch_template( $template ) ); 
			exit;
		}
		elseif ($stop_processing)
		{
			// return null. if you exit; it'll make it so that gateways cant' do their own thing. 
			return NULL; 
		}
		
 		$this->EE->form_builder->action_complete($validate= FALSE, $secure_forms);
		return NULL; 
	}
 	/**
	 * checkout_complete_offsite
	 *
	 * @param array $auth 
	 * @param string $order_id 
	 * @param string $completion_type  return|template|stop_processing.
	 * @return void
	 * @author Chris Newton
	 */
 	public function checkout_complete_offsite($auth, $order_id, $completion_type = NULL)
	{
		$template_url = NULL;
		
		$return_url = NULL;
		
		$stop_processing = FALSE; 
		
		$this->relaunch_cart(NULL, $order_id);

		/*
		// ok. we're relaunching the cart. there's not going to be an option NOT to relaunch the cart. Because.. in too many cases, people get back to the site and the cart s not cleared
		// and people get all freaked out and annoyed. So I'm commenting this whole thing out, and replacing it with $this->relaunch_cart
		
		
		// if we just relaunch the snapshot, the cart will not be cleared... so this should only be used on systems where manipulating the CURRENT cart isn't necessary.
		$this->relaunch_cart_snapshot($order_id);
		if ($this->order('create_user') || $completion_type === 'template' || ! $completion_type || $completion_type == 'null')
		{
			$this->relaunch_cart(NULL, $order_id);
		}
		*/ 

		if ($completion_type === 'return')
		{
			#$return_url = $this->order('return'); 
		}
		
		if ($completion_type === 'template')
		{
			// authorized_return, declined_return, failed_return don't work with this. So stop using it. we should deprecate those anyway.
			$template_url = $this->order('return'); 
		}
		// some gateways like sage server, output an "OK" status and a redirect URL and need to stop there. 
		if ($completion_type === 'stop_processing')
		{
			$stop_processing = TRUE; 
		}
		
		$this->checkout_complete($auth, $template_url, $return_url, $stop_processing);
	}
	
	// @TODO deprecate
	public function final_redirect($return_url= NULL)
	{
		if ($return_url)
		{
			if ( ! preg_match('#^https?://#', $return_url))
			{
				$return_url = $this->EE->functions->create_url($return_url);
			}
			
			$this->EE->functions->redirect($return_url);
			exit; 
		}
		else
		{
			return TRUE; 
		}
	}
 	
	public function hook($hook, $params = NULL)
	{
		//this allows you to call hook('your_hook', $param1, $param2) or hook('your_hook', array($param1, $param2))
		if (func_num_args() > 2)
		{
			$params = func_get_args();
			
			array_shift($params);
		}
		
		if ( ! $this->EE->extensions->active_hook($hook))
		{
			return FALSE;
		}
		
		if ( ! is_null($params))
		{
			if ( ! is_array($params))
			{
				$params = array($params);
			}
			
			array_unshift($params, $hook);
			
			return call_user_func_array(array($this->EE->extensions, 'call'), $params);
		}
		
		return $this->EE->extensions->call($hook);
	}
 	public function jump_form($url, $fields_array = array(), $hide_jump_form=TRUE, $title =FALSE, $overview=FALSE, $submit_text=FALSE, $full_page=TRUE, $hidden_fields_array = array())
	{
		if ($overview ===FALSE)
		{
			$overview = $this->lang('jump_alert');
		}
		if ($title === FALSE)
		{
			$title = $this->lang('jump_header'); 
		}
		if ($submit_text === FALSE)
		{
			$submit_text = $this->lang('jump_finish'); 
		}
		
		
		$return_html = ""; 
		if ($full_page)
		{
			$jump_html[] = "<html><head>
			<script type='text/javascript'>
				window.onload = function(){ document.forms[0].submit(); };
			</script>
			</head><body>";
		}

		if ($hide_jump_form)
		{
			// hiding contents from JS users.
			$jump_html[] =  "<script type='text/javascript'>document.write('<div style=\'display:none\'>');</script>";
		}
		
		if ($full_page)
		{
			$jump_html[] =   "<h1>".$title."</h1>";
			$jump_html[] =   "<p>".$overview."</p>";  
		}
		
		$jump_html[] =  "<form name='jump' id='jump' method='POST' action='".$url."' >"; 
		foreach ($fields_array as $key=> $value)
		{
			if (is_array($value))
			{
				// authorize.net SIM requries the same field be sent over and over for line items. stupid.
				foreach ($value as $subkey=> $subvalue)
				{
					$jump_html[] ="<input type='text' name='".$key."' value='".$subvalue."' />";
				}
			}
			else
			{
				$jump_html[] = "<input type='text' name='".$key."' value='".$value."' />";
				
			}
		}
		foreach ($hidden_fields_array as $key=> $value)
		{
			if (is_array($value))
			{
				foreach ($value as $subkey=> $subvalue)
				{
					$jump_html[] ="<input type='hidden' name='".$key."' value='".$subvalue."' />";
				}
			}
			else
			{
				$jump_html[] = "<input type='hidden' name='".$key."' value='".$value."' />";
				
			}
		}
		$jump_html[] =   "<input type='submit' value='".$submit_text."' />"; 
		$jump_html[] =   "</form>"; 

		if ($hide_jump_form)
		{
			$jump_html[] =   "<script type='text/javascript'>document.write('</div>');</script>";
		}

		if ($full_page)
		{
			$jump_html[]=  "</body></html>";
		}

		// turned this into an array so I could add lines to the above code without 
		// inevitably forgetting .= and thus screwing up my output code. 
		foreach ($jump_html as $line)
		{
			$return_html .=$line;
		}
		return $return_html; 
	}
	/**
	 * xml_to_array
	 *
	 * @param string $xml 
	 * @param string $build_type 
	 * @return array
	 * @author Chris Newton
	 * @since 1.0
	 */
	public function xml_to_array($xml, $build_type = 'basic')
	{
		$this->EE->load->helper('data_formatting');
		return xml_to_array($xml, $build_type);
	}
	
	public function strip_punctuation($text)
	{
		return preg_replace('/[^a-zA-Z0-9\s-_]/', ' ', $text);
	}
}
