<?php 
class Cartthrob_echo_nvp extends Cartthrob_payment_gateway
{
	public $title = 'echo_nvp_title';
 	public $overview = 'ech_nvp_overview';
	public $language_file = TRUE;
 	public $settings = array(
		array(
			'name' => 'echo_nvp_id', 
			'short_name' => 'merchant_echo_id', 
			'type' => 'text',  
			'default' => '123>1234567', 
		),
		array(
			'name' => 'echo_nvp_pin', 
			'short_name' => 'merchant_echo_pin', 
			'type' => 'text',  
			'default' => '12345678', 
		),
		array(
			'name' => 'transaction_type', 
			'short_name' => 'transaction_type', 
			'type' => 'select',
			'default' => 'ES', 
			'options'	=> array(
				'AD' => 'echo_nvp_tt_avs',
				'AS' => 'echo_nvp_tt_auth',
				'AV' => 'echo_nvp_tt_auth_avs',
				'CR' => 'echo_nvp_tt_credit',
				'DS' => 'echo_nvp_tt_deposit',
				'ES' => 'echo_nvp_tt_auth_and_deposit',
				'EV' => 'echo_nvp_tt_auth_and_deposit_avs',
				'CK' => 'echo_nvp_tt_system_check'
				),  
		),
		array(
			'name' => 'mode', 
			'short_name' => 'mode', 
			'type' => 'radio',  
			'default'	=> 'test',
			'options'	=> array(
					'test'	=> 'test',
					'live'	=> 'live'
				),
		),
	);
	
	public $required_fields = array(
		'first_name',
		'last_name',
		'CVV2',
		'expiration_year',
		'expiration_month',
		'zip',
		'state',
		'country_code',
		'city',
		'address',
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
		'expiration_month',
		'expiration_year',
 	);

	public $hidden = array('currency_code'); 
	/**
	 * process_payment
	 *
 	 * @param string $credit_card_number 
 	 * @return mixed | array | bool An array of error / success messages  is returned, or FALSE if all fails.
	 * @author Chris Newton
	 * @access public
	 * @since 1.0.0
	 */
	public function charge($credit_card_number)
	{
		$total = $this->total()*100;
		
		if ($this->plugin_settings('mode') == "test")
		{
			$total = 100; 
		}
		$resp['authorized'] 	=	FALSE; 
		$resp['declined']		=	FALSE; 
		$resp['failed']			=	TRUE; 
		$resp['error_message']	= 	NULL; 
		$resp['transaction_id']	=	NULL;
		
		// sample GOOD credit card 4005550000000019
		// Transaction information must not be passed using http query string.
		
		
		$post_array = array(
			'billing_phone'					=> $this->order('phone'),
			'merchant_pin'					=> $this->plugin_settings('merchant_echo_pin'),
			'merchant_echo_id'				=> $this->plugin_settings('merchant_echo_id'),
			'order_type'					=> "S",
			'transaction_type'				=> $this->plugin_settings('transaction_type'), 
			'counter'						=> rand(1,2147483647), // used to prevent duplicate submissions
			'debug'							=> (($this->plugin_settings('mode') == "test") ? "T" : "F"), 
			'merchant_trace_nbr'			=> $this->order('entry_id'), 
			'billing_name'					=> $this->order('first_name') . " " . $this->order('last_name'),
			'billing_address1'              => $this->order('address'),
			'billing_address2'              => $this->order('address2'),
			'billing_city'                  => $this->order('city'),
			'billing_country'               => $this->alpha2_country_code($this->order('country_code')),
			'billing_state'                 => $this->order('state'),
			'billing_zip'                   => $this->order('zip'),
			'cc_number'                     => $credit_card_number,
			'ccexp_month'                   => str_pad($this->order('expiration_month'), 2, '0', STR_PAD_LEFT),
			'ccexp_year'                    => $this->year_4($this->order('expiration_year')),
			'cnp_security'					=> $this->order('CVV2'),
			);
		if ($this->order('ip_address'))
		{
			$post_array['billing_ip_address'] =	$this->order('ip_address');
		}
		
		if ($this->order('currency_code') == "USD")
		{
			$post_array['grand_total']		= $total; 
		}
		else
		{
			// manual says 840 is only one allowed
			$post_array['local_currency_code']		= "840"; 
			// no decimal point is allowed for foreign transactions
			$post_array['foreign_trans_amount']		= round($total*100);
			$post_array['trans_decimal_position']	= "2";
			 //  The 3 digit numeric currency code for the currency in which the Cardholder made his purchase
			$post_array['trans_currency_code']		= $this->order('currency_code');
			 // The currency in which goods are priced for the cardholder prior to any knowledge of the cardholder’s billing currency or currency preference.
			$post_array['pricing_currency_code']	= $this->order('currency_code');
			
			 // items for Dynamic Currency Conversions
			//$post_array['local_trans_amount']		= round($total*100);
			//$post_array['merchant_conversion_rate']	= 1; 
		}
		
		$data = $this->data_array_to_string($post_array); 
		
		$connect = $this->curl_transaction($this->_host, $data); 

		if (!$connect)
		{
			$resp['failed']			= TRUE;
			$resp['authorized']		= FALSE;
			$resp['declined']		= FALSE;
			$resp['error_message']	= $this->lang('curl_gateway_failure'); 
			
			return $resp;
		}
		else
		{
			$response = explode("\r\n\r\n", $connect, 2);
			
			$start		= strpos($response[0], "<ECHOTYPE3>"); 
			$end		= strpos($response[0], "</ECHOTYPE3>") + 12; 
			$echotype3	= substr($response[0], $start, $end - $start); 

			$echotype3 = $this->xml_to_array($echotype3);				
			
			// $echotype3['ECHOTYPE3']['auth_code']['data']
			// $echotype3['ECHOTYPE3']['decline_code']['data']
			// $echotype3['ECHOTYPE3']['order_number']['data']
			// $echotype3['ECHOTYPE3']['status']['data'] g (approved) d declined c cancelled t timeout
			// $echotype3['ECHOTYPE3']['term_code']['data'] numbers
			
			if (!empty($echotype3['ECHOTYPE3']['status']['data']))
			{
				switch ($echotype3['ECHOTYPE3']['status']['data'])
				{
					case "G": 
						$auth['authorized'] 	=	TRUE; 
						$auth['declined']		=	FALSE; 
						$auth['failed']			=	FALSE; 
						$auth['error_message']	= 	NULL; 
						$auth['transaction_id']	=	@$echotype3['ECHOTYPE3']['order_number']['data'];
						break; 
					case "D":
						$auth['authorized'] 	=	FALSE; 
						$auth['declined']		=	TRUE; 
						$auth['failed']			=	FALSE; 
						$auth['transaction_id']	=	NULL;
						
						switch(@$echotype3['ECHOTYPE3']['decline_code']['data'])
						{
							case "05": 
							case "06": 
								$err = $this->lang('echo_nvp_error_declined'); 
								break;
							case "13":
							case "51": 
							case "61":
							case "65":
								$err = $this->lang('echo_nvp_error_amount_exceeded');  
								break;
							case "14":
							case "78":
								$err = $this->lang('echo_nvp_error_not_valid'); 
								break;
							case "15":
							case "1015":
								$err = $this->lang('echo_nvp_error_number_not_valid'); 
								break; 
							case "19":
								$err =  $this->lang('echo_nvp_error_resubmit'); 
								break;
							case "54": 
								$err =  $this->lang('echo_nvp_error_expired'); 
								break;
							case "1016":
								$err =  $this->lang('echo_nvp_error_invalid_expiration'); 
								break; 
							case "1017":
								$err = $this->lang('echo_nvp_error_low_amount'); 
								break; 
							case "1511":
								$err =  $this->lang('echo_nvp_error_duplicate_transaction'); 
								break;
							default: 
							$err =  $this->lang('echo_nvp_error_error_occurred');  
							
						}
						$auth['error_message']	= 	$err .  $this->lang('echo_nvp_error_error_code') . @$echotype3['ECHOTYPE3']['decline_code']['data']; 
						
						break;
					default:
						$auth['authorized'] 	=	FALSE; 
						$auth['declined']		=	FALSE; 
						$auth['failed']			=	TRUE; 
						$auth['error_message']	= 	@$echotype3['ECHOTYPE3']['term_code']['data']; 
						$auth['transaction_id']	=	NULL;
				}
			}
		}
		return $auth;
	}
	// END
}
// END Class