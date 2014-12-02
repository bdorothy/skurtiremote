<?php 
class Cartthrob_paypal_pro extends Cartthrob_payment_gateway
{
	public $title = 'paypal_pro_title';
	public $affiliate = 'paypal_pro_affiliate'; 
	public $language_file = TRUE;
	public $overview = "paypal_pro_overview"; 
	public $settings = array(
		array(
			'name' =>  'paypal_pro_api_username',
			'short_name' => 'api_username', 
			'type' => 'text', 
			'default' => '', 
		),
		array(
			'name' =>  'paypal_pro_api_password',
			'short_name' => 'api_password', 
			'type' => 'text', 
			'default' => '', 
		),
		array(
			'name' =>  'paypal_pro_signature',
			'short_name' => 'api_signature', 
			'type' => 'text', 
			'default' => '', 
		),
		
		array(
			'name' => "paypal_pro_sandbox_api_username",
			'short_name' => 'test_username', 
			'type' => 'text', 
			'default' => '', 
		),
		array(
			'name' => "paypal_pro_sandbox_api_password",
			'short_name' => 'test_password', 
			'type' => 'text', 
			'default' => '', 
		),
		array(
			'name' => "paypal_pro_sandbox_signature",
			'short_name' => 'test_signature', 
			'type' => 'text', 
			'default' => '', 
		),
		array(
			'name' => "paypal_pro_payment_action",
			'short_name' => 'payment_action', 
			'type' => 'radio', 
			'default' => 'Sale',
			'options'	=> array(
				'Sale' => 'sale',
				'Authorization' => 'authorization'
				) 
		),
		
		array(
			'name' =>  'mode',
			'short_name' => 'test_mode', 
			'type' => 'radio', 
			'default' => 'test',
			'options' => array(
					'test'=> "sandbox",
					'live'=> "live"
				),
		),
		array(
			'name'	=> 'paypal_pro_country',
			'short_name'	=> 'country',
			'type'	=> 'radio',
			'default'	=> 'us',
			'options'	=> array(
				'us'=> 'United States',
				'ca'=> 'Canada',
				'uk'=> 'Great Britain'
				)
		),
		
		array(
			'name' => "paypal_pro_api_version",
			'short_name' => 'api_version', 
			'type' => 'radio',
			'default' => '60.0', 
			'options' => array(
				'57.0'	=> '57.0',
				'60.0'	=> '60.0'
				), 
		),
	);
	
	public $required_fields = array(
		'credit_card_number',
		'expiration_month',
		'expiration_year',
		'card_type',
		'first_name',
		'last_name',
		'address',
		'city',
		'zip',
		'country_code',
 		'phone',
		'email_address',
	);
	
	public $fields = array(
		'first_name',
		'last_name',
		'address',
		'address2',
		'city',
		'state',
		'zip',
		'country_code',
		'shipping_first_name',
		'shipping_last_name',
		'shipping_address',
		'shipping_address2',
		'shipping_city',
		'shipping_state',
		'shipping_zip',
		'shipping_country_code',
		'phone',
		'email_address',
		'card_type',
		'credit_card_number',
		'CVV2',
		'expiration_month',
		'expiration_year',
		'issue_number',
		'begin_month',
		'begin_year',
		);
	public $hidden = array('currency_code'); 
	public $card_types = array(
		'visa',
		'mc',
		'amex',
		'discover',
		'maestro',
		'solo'
		); 
  	public function initialize()
	{
		if ($this->plugin_settings('country') == 'us')
		{
			unset($this->fields[array_search('begin_month', $this->fields)]);
			unset($this->fields[array_search('begin_year', $this->fields)]);
			unset($this->fields[array_search('issue_number', $this->fields)]);
			
			unset($this->card_types[array_search('maestro', $this->card_types)]);
			unset($this->card_types[array_search('solo', $this->card_types)]);
 			
		}
 	}
	/**
	 * process_payment
	 *
	 * @param string $credit_card_number 
	 * @return mixed | array | bool An array of error / success messages  is returned, or FALSE if all fails.
	 * @author Chris Newton
	 * @access private
	 * @since 1.0.0
	 */
	public function charge($credit_card_number)
	{

		switch ($this->order('card_type'))
		{
			case "mc": 
				$card_type="MasterCard"; 
				break;
			case 'discover': 
				$card_type="Discover"; 
				break;
			case 'visa': 
				$card_type="Visa"; 
				break;
			case 'amex': 
				$card_type="Amex"; 
				break;
			case 'maestro': 
				$card_type="Maestro"; 
				break;
			case 'solo': 
				$card_type="Solo"; 
				break;
			default: $card_type="Visa";  
		}
		
		if ($this->plugin_settings('test_mode') == "test") 
		{
			// Sandbox server for use with API signatures;use for testing your API
			//$this->_paypal_server = "https://api.sandbox.paypal.com/nvp"; 
			$this->_paypal_server = "https://api-3t.sandbox.paypal.com/nvp"; 
			$this->_API_UserName = urlencode($this->plugin_settings('test_username'));
			$this->_API_Password = urlencode($this->plugin_settings('test_password'));
			$this->_API_Signature = urlencode($this->plugin_settings('test_signature'));
		}
		else
		{
			// PayPal "live" production server for usewith API signatures
			$this->_paypal_server = "https://api-3t.paypal.com/nvp"; 
			$this->_API_UserName = urlencode($this->plugin_settings('api_username'));
			$this->_API_Password = urlencode($this->plugin_settings('api_password'));
			$this->_API_Signature = urlencode($this->plugin_settings('api_signature'));
		}
		
		$resp['authorized'] 	= 	FALSE; 
		$resp['declined']		=	FALSE; 
		$resp['failed']			=	FALSE; 
		$resp['error_message']	= 	NULL; 
		$resp['transaction_id']	=	NULL;
		
		$post_array = array(
		'METHOD' 			=> 'DoDirectPayment', 
		'VERSION'			=> urlencode($this->plugin_settings('api_version')),
		'PWD'				=> $this->_API_Password,
		'USER'				=> $this->_API_UserName,
		'SIGNATURE'			=> $this->_API_Signature,
		'PAYMENTACTION' 	=> $this->plugin_settings('payment_action'),
		'EMAIL'				=> $this->order('email_address'),
		'AMT' 				=> $this->total(),
		'ACCT' 				=> $credit_card_number,
		'EXPDATE' 			=> str_pad($this->order('expiration_month'), 2, '0', STR_PAD_LEFT).str_pad($this->order('expiration_year'), 4, '20', STR_PAD_LEFT),
		'FIRSTNAME' 		=> $this->order('first_name'),
		'LASTNAME' 			=> $this->order('last_name'),
		'STREET' 			=> $this->order('address'),
		'STREET2'			=> $this->order('address2'),
		'CITY' 				=> $this->order('city'),
		'STATE' 			=> strtoupper($this->order('state')),
		'ZIP' 				=> $this->order('zip'),
		'CVV2' 				=> $this->order('CVV2'),
		'COUNTRYCODE' 		=> ($this->order('country_code') ? $this->alpha2_country_code($this->order('country_code')) : "US"),
		'CURRENCYCODE' 		=> ($this->order('currency_code') ? $this->order('currency_code') : "USD"),
		'CREDITCARDTYPE'	=> $card_type,
		'SHIPTONAME'		=> ($this->order('shipping_first_name') 		? $this->order('shipping_first_name') . " ". $this->order('shipping_last_name') : $this->order('first_name') ." ". $this->order('last_name')),
		'SHIPTOSTREET'		=> ($this->order('shipping_address') 			? $this->order('shipping_address') : $this->order('address')),
		'SHIPTOSTREET2'		=> ($this->order('shipping_address2') 			? $this->order('shipping_address2') : $this->order('address2')),
		'SHIPTOCITY'		=> ($this->order('shipping_city') 				? $this->order('shipping_city') : $this->order('city')),
		'SHIPTOSTATE'		=> ($this->order('shipping_state')				? strtoupper($this->order('shipping_state')) : strtoupper($this->order('state'))),
		'SHIPTOZIP'			=> ($this->order('shipping_zip') 				? $this->order('shipping_zip') : $this->order('zip')),
		'SHIPTOCOUNTRY'		=> ($this->order('shipping_country_code') 		? $this->alpha2_country_code($this->order('shipping_country_code')) : $this->alpha2_country_code($this->order('country_code'))),
		'RETURNFMFDETAILS' 	=> '0',
		'IPADDRESS' 		=> $this->order('ip_address')
		
		); 
		if (!empty($post_array['SHIPTONAME']) && empty($post_array['SHIPTOSTATE']))
		{
			$post_array['SHIPTOSTATE'] = "--"; 
		}
		
		if ($this->plugin_settings('country')=="uk" )
		{
			$post_array['STARTDATE']	= ($this->order('begin_month') ? str_pad($this->order('begin_month'), 2, '0', STR_PAD_LEFT) . $this->order('begin_year'): ""); 
			$post_array['ISSUENUMBER']	= $this->order('issue_number');
			
		}

		$data = 	$this->data_array_to_string($post_array);
		$connect = 	$this->curl_transaction($this->_paypal_server,$data); 
		if (!$connect)
		{
			exit($this->lang('curl_gateway_failure'));
		}
		$transaction =  $this->split_url_string($connect);
 
		$declined = FALSE;
		$failed = FALSE;
		if (is_array($transaction))
		{
			if ("SUCCESS" == strtoupper($transaction['ACK']) || "SUCCESSWITHWARNING" == strtoupper($transaction["ACK"])) 
			{
				$authorized = TRUE; 
				$declined = FALSE; 
				$transaction_id = $transaction['TRANSACTIONID']; 
				$error_message = '';
			} 
			else  
			{
				$authorized = FALSE; 
				$declined = TRUE; 
				$transaction_id = 0;
				$error_message = ($transaction['L_LONGMESSAGE0']) ? $transaction['L_LONGMESSAGE0'] : $this->lang('unknown_error');
			}
			$resp['authorized']		=	$authorized;
			$resp['error_message']	=	$error_message;
			$resp['failed']			=	$failed;
			$resp['declined']		=	$declined;
			$resp['transaction_id'] =	$transaction_id;
		}
		else
		{
			$resp['authorized']		=	FALSE;
			$resp['error_message']	=	$this->lang('paypal_pro_contact_admin');
			$resp['failed']			=	TRUE;
		}
		return $resp;
	}// END
}// END Class