<?php 
class Cartthrob_paypal_express extends Cartthrob_payment_gateway
{
	public $title = 'paypal_express_title';
	public $affiliate = 'paypal_express_affiliate'; 
	public $overview = "paypal_express_overview"; 
	public $settings = array(
		array(
			'name' =>  'paypal_express_api_username',
			'short_name' => 'api_username', 
			'type' => 'text', 
			'default' => '', 
		),
		array(
			'name' =>  'paypal_express_api_password',
			'short_name' => 'api_password', 
			'type' => 'text', 
			'default' => '', 
		),
		array(
			'name' =>  'paypal_express_signature',
			'short_name' => 'api_signature', 
			'type' => 'text', 
			'default' => '', 
		),
		
		array(
			'name' => "paypal_express_sandbox_api_username",
			'short_name' => 'test_username', 
			'type' => 'text', 
			'default' => '', 
		),
		array(
			'name' => "paypal_express_sandbox_api_password",
			'short_name' => 'test_password', 
			'type' => 'text', 
			'default' => '', 
		),
		array(
			'name' => "paypal_express_sandbox_signature",
			'short_name' => 'test_signature', 
			'type' => 'text', 
			'default' => '', 
		),
		array(
			'name' =>  'mode',
			'short_name' => 'mode', 
			'type' => 'radio', 
			'default' => 'test',
			'options' => array(
					'test'=> "sandbox",
					'live'=> "live"
				),
		),
		
		array(
			'name' => "paypal_express_allow_note", 
			'note'	=> 'paypal_express_allow_note_note',
			'short_name' => 'allow_note', 
			'type' => 'radio',
			'default' => 'no', 
			'options' => array(
				'no' => 'no', 
				'yes' => 'yes'
			)
		),
		array(
			'name' => "paypal_express_show_item_id", 
			'short_name' => 'show_item_id', 
			'type' => 'radio',
			'default' => 'yes', 
			'options' => array(
				'no' => 'no', 
				'yes' => 'yes'
			)
		),
		array(
			'name' => "paypal_express_show_item_options", 
			'short_name' => 'show_item_options', 
			'type' => 'radio',
			'default' => 'no', 
			'options' => array(
				'no' => 'no', 
				'yes' => 'yes'
			)
		),
		array(
			'name' => 'paypal_express_customization_settings_header',
			'short_name' => 'customization_settings_header',
			'type' => 'header',
		),
		
		array(
			'name' => 'paypal_express_header_image_url',
			'short_name' => 'header_image_url',
			'default'	=> '',
			'type' => 'text',
		), 
		array(
			'name' => 'paypal_express_header_border_color_hex_value',
			'short_name' => 'header_border_color',
			'default'	=> '',
			'type' => 'text',
		),
		array(
			'name' => 'paypal_express_header_background_color_hex_value',
			'short_name' => 'header_background_color',
			'default'	=> '',
			'type' => 'text',
		),
		/*
		array(
			'name' => 'paypal_express_subscription_settings_header',
			'short_name' => 'subscriptions_settings_header',
			'type' => 'header',
		),
		array(
			'name' => "paypal_express_developer_email",
			'note'	=> 'paypal_express_developer_email_note',
			'short_name' => 'dev_email', 
			'type' => 'text', 
			'default' => '', 
		),
		array(
			'name' =>  'paypal_subscription_memo',
			'note'	=> 'paypal_subscription_memo_note',
			'short_name' => 'subscription_memo', 
			'type' => 'text', 
			'default' => '', 
		),
		*/ 
		array(
			'name' => 'paypal_express_advanced_settings_header',
			'short_name' => 'advanced_settings_header',
			'type' => 'header',
		),
		array(
			'name' => 'paypal_account_preferences', 
			'short_name' => 'solutiontype', 
			'type' => 'radio',
			'default' => 'Mark',
			'options' => array(
				'Sole' => 'paypal_sole',
				'Mark' => 'paypal_mark'
			)
		),
		array(
			'name' => 'paypal_display_billing_page', 
			'short_name' => 'display_billing_page', 
			'type' => 'radio',
			'default' => 'Login',
			'note'	=> 'paypal_display_billing_page_note',
			'options' => array(
				'Login' => 'paypal_show_login',
				'Billing' => 'paypal_show_credit'
			)
		),
		array(
			'name' => "paypal_express_shipping_settings", 
			'short_name' => 'shipping_settings', 
			'type' => 'select',
			'note'	=> 'paypal_express_no_shipping_note',
			'default' => 'hide_shipping', 
			'options' => array(
				'hide_shipping'		=> 'paypal_express_hide_shipping_address',
				'editable_shipping'	=> 'paypal_express_editable_shipping',
				'static_shipping'	=> 'paypal_express_static_shipping',
				'paypal_shipping'	=> 'paypal_express_paypal_shipping',
			)
		),
		array(
			'name' => "paypal_express_payment_action",
			'short_name' => 'payment_action', 
			'type' => 'radio', 
			'default' => 'Sale',
			'options'	=> array(
				'Sale' => 'sale',
				'Authorization' => 'authorization'
				) 
		),
		
 	);
	
	public $required_fields = array(
	);
	
	public $fields = array(
		'first_name',
		'last_name',
		'address',
		'address2',
		'city',
		'state',
		'zip',
		'company',
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
		);
 	public $paypal_server; 
	public $API_UserName; 
	public $API_Password; 
	public $API_Signature; 
	public $paypal_offsite; 

	public function initialize()
	{
		if ($this->plugin_settings('mode') == "test") 
		{
			// Sandbox server for use with API signatures;use for testing your API
			//$this->_paypal_server = "https://api.sandbox.paypal.com/nvp"; 
			
			// it seems that PayPal requires an https connection these days... so the next bit's a bit irrelevant
			$this->paypal_server = (!function_exists('openssl_open') ? "https://api-3t.sandbox.paypal.com/nvp":"https://api-3t.sandbox.paypal.com/nvp" ); 
			$this->paypal_offsite = (!function_exists('openssl_open') ? "https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token=": "https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token=");
			$this->API_UserName = urlencode($this->plugin_settings('test_username'));
			$this->API_Password = urlencode($this->plugin_settings('test_password'));
			$this->API_Signature = urlencode($this->plugin_settings('test_signature'));
			$this->API_Subject = urlencode($this->plugin_settings('test_subject'));
			$this->endpoint = "https://svcs.sandbox.paypal.com"; 
			$this->application_id = "APP-80W284485P519543T"; // testing. 
		}
		else
		{
			// PayPal "live" production server for usewith API signatures
			$this->paypal_server = (!function_exists('openssl_open') ? "https://api-3t.paypal.com/nvp" : "https://api-3t.paypal.com/nvp" ); 
			$this->paypal_offsite =(!function_exists('openssl_open') ?  "https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=" :  "https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=" ); 
			$this->API_UserName = urlencode($this->plugin_settings('api_username'));
			$this->API_Password = urlencode($this->plugin_settings('api_password'));
			$this->API_Signature = urlencode($this->plugin_settings('api_signature'));
			$this->API_Subject = urlencode($this->plugin_settings('api_subject'));
			
			$this->endpoint = "https://svcs.paypal.com"; 
			$this->application_id = NULL; //@TODO get this
			
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
		$auth['authorized'] 	= 	FALSE; 
		$auth['declined']		=	FALSE; 
		$auth['failed']			=	FALSE; 
		$auth['error_message']	= 	NULL; 
		$auth['transaction_id']	=	NULL;
		
		$post_array = $this->assemble_post_array($method ="SetExpressCheckout", $version = "65.1"); 
		$data = 	$this->data_array_to_string($post_array);
		$connect = 	$this->curl_transaction($this->paypal_server,$data); 
		if (!$connect)
		{
			exit($this->lang('curl_gateway_failure'));
		}
		$transaction =  $this->split_url_string($connect);
 		$token =""; 
 
		if (is_array($transaction))
		{
			if ("SUCCESS" == strtoupper($transaction['ACK']) || "SUCCESSWITHWARNING" == strtoupper($transaction["ACK"])) 
			{
				$token = urldecode($transaction["TOKEN"]);
			} 
			else  
			{  
				if (!empty($transaction['L_LONGMESSAGE0']))
				{
					$auth['failed'] = TRUE; 
					$auth['error_message']	=$transaction['L_LONGMESSAGE0']. " ". $transaction['L_ERRORCODE0']; 
				}
				return $auth; 
			}
		}
		else
		{
			$auth['failed'] = TRUE; 
			$auth['error_message']	= $this->lang('paypal_express_did_not_respond') ;
			return $auth;
		}
		$this->gateway_exit_offsite(NULL, $this->paypal_offsite.$token.'&useraction=commit'); exit;
	}// END
	

	function cancel_payment($post)
	{
		$auth = array(
			'authorized' 	=> FALSE,
			'error_message'	=> $this->lang('paypal_express_you_cancelled'),  
			'failed'		=> TRUE,
			'declined'		=> FALSE,
			'transaction_id'=> NULL, 
			);
		$this->checkout_complete_offsite($auth, $this->order('entry_id')); 	
		exit;
	}
	// @TODO need to add methods for handling refunds like PayPal standard. 
	
	function confirm_payment($post)
	{
		
		$auth = array(
			'authorized' 	=> FALSE,
			'error_message'	=> NULL,
			'failed'		=> TRUE,
			'declined'		=> FALSE,
			'transaction_id'=> NULL, 
			);
		
		$post_array = array(
			'METHOD'	=> 'GetExpressCheckoutDetails',
			'VERSION'	=> urlencode("65.1"),
			'PWD'		=> $this->API_Password,
			'USER'		=> $this->API_UserName,
			'SIGNATURE'	=> $this->API_Signature,
			'TOKEN'		=> $post['token'],
			);
		
		$data = 	$this->data_array_to_string($post_array);
		
		$connect = 	$this->curl_transaction($this->paypal_server,$data); 
		
		if (!$connect)
		{
			exit( $this->lang('curl_gateway_failure'));
		}
		
		$transaction =  $this->split_url_string($connect);
 		$payer_id = NULL; 
		if (is_array($transaction))
		{
			if ("SUCCESS" == strtoupper($transaction['ACK']) || "SUCCESSWITHWARNING" == strtoupper($transaction["ACK"])) 
			{
				$payer_id =	$transaction['PAYERID'];
			} 
			else  
			{
				if (!empty($transaction['L_LONGMESSAGE0']))
				{
					$auth['failed'] = TRUE; 
					
					$auth['error_message']	=$transaction['L_LONGMESSAGE0']. " ". $transaction['L_ERRORCODE0']; 
				}
				$this->checkout_complete_offsite($auth, $this->order('entry_id')); 	
				exit;
			}
		}
		else
		{
			$auth['error_message']	= $this->lang('paypal_express_did_not_respond') ;
			$this->checkout_complete_offsite($auth, $this->order('entry_id')); 		
			exit;
		}
		
		$post_array = $this->assemble_post_array($method="DoExpressCheckoutPayment", $version = "65.1", $post['token'], $payer_id); 
		
		$data = 	$this->data_array_to_string($post_array);
		$connect = 	$this->curl_transaction($this->paypal_server,$data); 
		if (!$connect)
		{
			exit( $this->lang('curl_gateway_failure'));
		}
		$transaction =  $this->split_url_string($connect);
		
		if (is_array($transaction))
		{
			if ("SUCCESS" == strtoupper($transaction['ACK']) || "SUCCESSWITHWARNING" == strtoupper($transaction["ACK"])) 
			{
				$auth = array(
					'authorized' 	=> TRUE,
					'error_message'	=> NULL,
					'failed'		=> FALSE,
					'declined'		=> FALSE,
					'transaction_id'=> $transaction['PAYMENTINFO_0_TRANSACTIONID'], 
	 				);
	
				//$this->handle_subscriptions( $post['token']); 
			} 
			else  
			{
				if (!empty($transaction['L_LONGMESSAGE0']))
				{
					$auth['failed'] = TRUE; 
					
					$auth['error_message']	=$transaction['L_LONGMESSAGE0']. " ". $transaction['L_ERRORCODE0']; 
				}
			}
		}
		else
		{
			$auth['error_message']	= $this->lang('paypal_express_did_not_respond') ;
		}
		$this->checkout_complete_offsite($auth, $this->order('entry_id')); 	
		exit;
	}
	
	function assemble_post_array($method="SetExpressCheckout", $version = "65.1", $token = NULL, $payer_id= NULL)
	{
		if ($this->plugin_settings('allow_note')=="yes")
		{
			$allow_note = 1; 
		}
		else
		{
			$allow_note = 0; 
		}

		// added to handle situations when a person has an alternate total and goes to PayPal. 
		if ($method == "DoExpressCheckoutPayment" && $this->order('pp_alt_total'))
		{
			$total = $this->order('pp_alt_total'); 
		}
		else
		{
			if ($this->total())
			{
				$total = $this->total();
			}
			else
			{
				$total = $this->order('total');
			}
		}
		$info = array(
			'PAYMENTREQUEST_0_AMT'					=> round($total,2), 
			'PAYMENTREQUEST_0_ITEMAMT'				=> round($this->order('subtotal'),2),
			'PAYMENTREQUEST_0_TAXAMT'				=> round($this->order('tax'),2),
	 		'PAYMENTREQUEST_0_SHIPPINGAMT'			=> round($this->order('shipping'),2),
			'PAYMENTREQUEST_0_SHIPTONAME'			=> substr(($this->order('shipping_first_name') 		? $this->order('shipping_first_name') . " ". $this->order('shipping_last_name') : $this->order('first_name') ." ". $this->order('last_name')),0, 31),
			'PAYMENTREQUEST_0_SHIPTOSTREET'			=> substr(($this->order('shipping_address') 			? $this->order('shipping_address') : $this->order('address')), 0, 99),
			'PAYMENTREQUEST_0_SHIPTOSTREET2'		=> substr(($this->order('shipping_address2') 			? $this->order('shipping_address2') : $this->order('address2')), 0, 99),
			'PAYMENTREQUEST_0_SHIPTOCITY'			=> substr(($this->order('shipping_city') 				? $this->order('shipping_city') : $this->order('city')), 0, 40),
			'PAYMENTREQUEST_0_SHIPTOSTATE'			=> ($this->order('shipping_state')				? strtoupper($this->order('shipping_state')) : strtoupper($this->order('state'))),
			'PAYMENTREQUEST_0_SHIPTOZIP'			=> ($this->order('shipping_zip') 				? $this->order('shipping_zip') : $this->order('zip')),                                                                           
			'PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE'	=> $this->alpha2_country_code(($this->order('shipping_country_code') 		? $this->order('shipping_country_code') : $this->order('country_code'))),
			'PAYMENTREQUEST_0_SHIPTOPHONENUM'		=> $this->order('phone'),
			'EMAIL'									=> $this->order('email_address'),
			); 
			
 		$post_array = array(
			'METHOD'								=> $method,
			'VERSION'								=> urlencode($version),
			'PWD'  									=> $this->API_Password,
			'USER' 									=> $this->API_UserName,
			'SIGNATURE'								=> $this->API_Signature,
			'PAYMENTREQUEST_0_PAYMENTACTION'		=> $this->plugin_settings('payment_action'),
			'RETURNURL'								=> $this->get_notify_url(ucfirst(get_class($this)),'confirm_payment'),
			'CANCELURL'								=> $this->get_notify_url(ucfirst(get_class($this)),'cancel_payment') ,
			'PAYMENTREQUEST_0_CURRENCYCODE'			=> ($this->order('currency_code') ? $this->order('currency_code') : "USD"),
			'ALLOWNOTE'								=> $allow_note,
			'CHANNELTYPE'							=> 'Merchant', // non ebay item
			'PAYMENTREQUEST_0_ALLOWEDPAYMENTMETHOD'	=> 'InstantPaymentOnly',
		);
		

		// paypal won't just let us send the language. lame
		switch($this->order('language'))
		{
			case "EN":
				switch ($this->order('country_code'))
				{
					case "GBR": $post_array['LOCALECODE'] = "GB"; break;
					case "AUS": $post_array['LOCALECODE'] = "AU"; break; 
					case "USA": $post_array['LOCALECODE'] = "US"; break;
					case "CAN": $post_array['LOCALECODE'] = "CA"; break;
					default: $post_array['LOCALECODE'] = "US"; break;
				}
				break;
			case "DE": 
				switch ($this->order('country_code'))
				{
					case "AUT": $post_array['LOCALECODE'] = "AT"; break;
					case "DEU": $post_array['LOCALECODE'] = "DE"; break; 
					default: $post_array['LOCALECODE'] = "DE"; break;
				}
				break;
			case "ES": 
				$post_array['LOCALECODE'] = "ES"; 
				break;
			case "NL": 
				switch ($this->order('country_code'))
				{
					case "BEL": $post_array['LOCALECODE'] = "BE"; break;
					case "NED": $post_array['LOCALECODE'] = "NL"; break; 
					default: $post_array['LOCALECODE'] = "NL"; break;
				}
				break;
			case "IT": 
				$post_array['LOCALECODE'] = "IT"; 
				break;
			case "FR": 
				$post_array['LOCALECODE'] = "FR"; 
				break;
			case "ZH": 
				$post_array['LOCALECODE'] = "CN"; 
				break;
			case "PL": 
				$post_array['LOCALECODE'] = "PL"; 
				break;
			default: $post_array['LOCALECODE'] = "US"; 
		}
		
		switch( $this->plugin_settings('shipping_settings')  )
		{
			case "hide_shipping"; 
				$post_array['ADDROVERRIDE'] 	= 0; 
				$post_array['NOSHIPPING']		= 1; 
				unset($info['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE'],
					$info['PAYMENTREQUEST_0_SHIPTOZIP'],
					$info['PAYMENTREQUEST_0_SHIPTOSTATE'],
					$info['PAYMENTREQUEST_0_SHIPTOCITY'],
					$info['PAYMENTREQUEST_0_SHIPTOSTREET2'],
					$info['PAYMENTREQUEST_0_SHIPTOSTREET'],
					$info['PAYMENTREQUEST_0_SHIPTONAME']
					); 
				break;
			case "paypal_shipping"; 
				unset($info['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE'],
					$info['PAYMENTREQUEST_0_SHIPTOZIP'],
					$info['PAYMENTREQUEST_0_SHIPTOSTATE'],
					$info['PAYMENTREQUEST_0_SHIPTOCITY'],
					$info['PAYMENTREQUEST_0_SHIPTOSTREET2'],
					$info['PAYMENTREQUEST_0_SHIPTOSTREET'],
					$info['PAYMENTREQUEST_0_SHIPTONAME']
					); 
				$post_array['ADDROVERRIDE'] 	= 1; 
				$post_array['NOSHIPPING']		= 0; 
				break;
			case "static_shipping": 
				$post_array['ADDROVERRIDE'] 	= 1; 
				$post_array['NOSHIPPING']		= 0; 
			break; 
			
			case "editable_shipping": 
			default: 
				$post_array['ADDROVERRIDE'] 	= 0; 
				$post_array['NOSHIPPING']		= 0; 
 				break;
		}
		
		
		foreach ($info as $key => $value)
		{
			if (empty($value))
			{
				unset($info[$key]); 
			}
		}
		
		$post_array = array_merge($info, $post_array); 

		// style + display
		
		if ($this->plugin_settings('header_image_url'))
		{
			$post_array['HDRIMG'] = $this->plugin_settings('header_image_url'); 
		}
		if ($this->plugin_settings('header_background_color'))
		{
			$post_array['HDRBACKCOLOR'] = $this->plugin_settings('header_background_color'); 
		}
		if ($this->plugin_settings('header_border_color'))
		{
			$post_array['HDRBORDERCOLOR'] = $this->plugin_settings('header_border_color'); 
		}
 
		// making it so you can checkout with a CC
		if ($this->plugin_settings('solutiontype') == "Sole")
		{
			$post_array['SOLUTIONTYPE'] = "Sole"; 
			$post_array['LANDINGPAGE'] = ($this->plugin_settings('display_billing_page')? $this->plugin_settings('display_billing_page'): "Login"); 
		}
		// if it's not a SALE type, then we can't demand immediate payment
		if ($this->plugin_settings('payment_action')!="Sale")
		{
			unset($post_array['PAYMENTREQUEST_0_ALLOWEDPAYMENTMETHOD']); 
		}

		$item_array = array(); 

		foreach ($this->order('items') as $row_id => $item)
		{
			if ($item['price'] == 0) // paypal doesn't like 0 priced items, but it doesn't mind negative items
			{
				continue; 
			}
			if (!isset($count))
			{
				$count=0;
			}

			$item_array["L_PAYMENTREQUEST_0_NAME".$count]			= substr($item['title'], 0, 126);
 			$item_array["L_PAYMENTREQUEST_0_AMT".$count]			= round($this->round($item['price']), 2); 
			$item_array["L_PAYMENTREQUEST_0_QTY".$count] 			= $item['quantity']; 

			if ( $this->plugin_settings('show_item_options') == "yes" && !empty($item['item_options']))
			{
				$item_options = ""; 
				foreach($item['item_options'] as $key=> $value)
				{
					$item_options .= $key.": ". $value. ", "; 
				}
				$item_array["L_PAYMENTREQUEST_0_DESC".$count] 		= substr($item_options, 0, 126);  
			}
			if ($this->plugin_settings('show_item_id') == "yes")
			{
				if (empty($item['entry_id']))
				{
					$item_array["L_PAYMENTREQUEST_0_NUMBER".$count] 		= "000"; 
				}
				else
				{
					$item_array["L_PAYMENTREQUEST_0_NUMBER".$count] 		= $item['entry_id']; 
				}
 			}
			$count++;
		}	

		if ($this->order('discount') > 0)
		{
			// oh god, the discount's greater than the subtotal. WHAT DO WE DO NOW!?!?!
			// oh that's right paypal can't handle it. so we'll just send one line item with the
			// entire cart contents *sigh*
			if ($this->order('discount') > $this->order('subtotal'))
			{
				// killing off item array, shipping and tax. 
				$item_array= array(); 
				unset($post_array['PAYMENTREQUEST_0_SHIPPINGAMT']); 
				unset($post_array['PAYMENTREQUEST_0_TAXAMT']); 
				unset($post_array['PAYMENTREQUEST_0_ITEMAMT']); 
	 		}
			else
			{ 
				$post_array['PAYMENTREQUEST_0_ITEMAMT']				= round(($this->order('subtotal')-$this->order('discount')), 2); 
			
				$item_array["L_PAYMENTREQUEST_0_NAME".$count]			= $this->lang('discount'); 
	 			$item_array["L_PAYMENTREQUEST_0_AMT".$count]			= -round($this->order('discount'), 2);
				$item_array["L_PAYMENTREQUEST_0_QTY".$count] 			= 1;
				if ($this->plugin_settings('show_item_id') == "yes")
				{
					$item_array["L_PAYMENTREQUEST_0_NUMBER".$count] 			= "000"; 
				}
			}
		}
 
		// if the price is manually set, we want to kill the item totals and other values, because paypal does not like it when the item totals and the checkout total does not match. 
		if (!empty($_POST['PR']) || !empty($_POST['price']) || $this->order('pp_alt_total'))
		{
			if ($this->total())
			{
				$this->update_order(array('pp_alt_total' => $this->total()));
			}
	  		
			$item_array= array(); 
			unset($post_array['PAYMENTREQUEST_0_SHIPPINGAMT']); 
			unset($post_array['PAYMENTREQUEST_0_TAXAMT']); 
			unset($post_array['PAYMENTREQUEST_0_ITEMAMT']);
		}

		$post_array = array_merge($item_array, $post_array);
		
		if ($token)
		{
			$post_array['TOKEN'] = $token; 
		}
		if ($payer_id)
		{
			$post_array['PAYERID']	= $payer_id;
			$post_array['IPADDRESS'] = $_SERVER['SERVER_NAME'];
		}
		return $post_array; 
	}
	
	public function handle_subscriptions($token)
	{
		$subscription_items = array(); 
		if ($this->order('items'))
		{
			foreach ($this->order('items') as $row_id => $item)
			{
				if (!empty($item['meta']['subscription']))
				{
					$subscription_items[] = $item; 
				}
			}
		}
		if ($this->order('subscription'))
		{
			$sub['quantity'] = 1; 
			$sub['price'] = $this->order('total'); 
			$sub['meta']['subscription'] = $this->order('subscription'); 
 			$subscription_items = array($sub);  
		}
		if (empty($subscription_items))
		{
			return FALSE; 
		}
		
		foreach($subscription_items as $key => $item)
		{
			for ($i=0; $i < $item['quantity']; $i++)
			{
				$sub_total = $item['price']; 

				$sub_data = array(); 
				foreach ($item['meta']['subscription'] as $key=> $value)
				{
					$sub_data[$key] = $value; 
				}
				
				$recurrent_billing_auth = $this->create_recurrent_billing($sub_total, $credit_card_number, $sub_data, $token); 

				$save_data['description'] 	= $this->subscription_info($sub_data, 'subscription_name', $item['title']); 
				$save_data['timestamp']		= time(); 
				$save_data['order_id']		= $this->order('entry_id'); 
				$save_data['member_id']		= $this->order('member_id');
				
				if ($recurrent_billing_auth['authorized'])
				{
					$save_data['sub_id']			= $recurrent_billing_auth['transaction_id']; 
					$save_data['status']			= "open"; 
				}
				else
				{
					$save_data['status']			= "failed"; 
				}
				$save_data['gateway'] = ucfirst(get_class($this)); 

				$this->update_subscriptions($save_data); 
			}
		}
	}
	function create_recurrent_billing($subscription_amount, $credit_card_number, $sub_data, $token)
	{
		$auth['authorized']	 	= FALSE; 
		$auth['declined'] 		= FALSE; 
		$auth['transaction_id']	= NULL;
		$auth['failed']			= TRUE; 
		$auth['error_message']	= "";
		
 
		if (!empty($sub_data['subscription_interval_units']))
		{
 			if ($sub_data['subscription_interval_units'] !="months" 
				&& $sub_data['subscription_interval_units'] !="days" 
				&& $sub_data['subscription_interval_units'] !="weeks"
				&& $sub_data['subscription_interval_units'] !="semimonths"
				&& $sub_data['subscription_interval_units'] != "years")
			{
				$sub_data['subscription_interval_units'] = "months"; 
			}
		}
 		// authorize.net does not allow intervals longer than 12 for month based subs
		if ($sub_data['subscription_interval_units'] == "months" && $sub_data['subscription_interval'] > 12)
		{
			$sub_data['subscription_interval'] = 12;
		}
		if ($sub_data['subscription_interval_units'] == "days" && $sub_data['subscription_interval'] > 365)
		{
			$sub_data['subscription_interval'] = 365;
		}
		if ($sub_data['subscription_interval_units'] == "weeks" && $sub_data['subscription_interval'] > 52)
		{
			$sub_data['subscription_interval'] = 52;
		}
		if ($sub_data['subscription_interval_units'] == "semimonths" && $sub_data['subscription_interval'] > 24)
		{
			$sub_data['subscription_interval'] = 24;
		}
		if ($sub_data['subscription_interval_units'] == "years" && $sub_data['subscription_interval'] > 1)
		{
			$sub_data['subscription_interval'] = 1;
		}
		switch ($sub_data['subscription_interval'])
		{
			case "months": $units= "Month"; break;
			case "days": $units= "Day"; break;
			case "weeks": $units= "Week"; break;
			case "years": $units= "Year"; break;
			case "semimonths": $units= "SemiMonth"; break;
			default: $units = "Month"; 
		}
 
		date_default_timezone_set("UTC"); 
		$timestamp = strtotime("now");
		$date = date('Y-m-d', $timestamp).'T'.date('H:i:s', $timestamp).'Z';
		
		
		$post_array= array(
			'TOKEN'					=> $token, 
			'AMT'					=> round($subscription_amount,2),
			'CURRENCYCODE'			=> $this->order('currency_code'),
			'PROFILESTARTDATE'		=> urlencode($date),
			'BILLINGPERIOD'			=> $units,
			'BILLINGFREQUENCY'		=> $sub_data['subscription_interval'],
			'DESC'					=> $sub_data['description'],
			'EMAIL'					=> $this->order('email_address'),
			);

		$data = $this->data_array_to_string($post_array); 
		
		$connect = 	$this->curl_transaction($this->paypal_server,$data); 
		
		if (!$connect)
		{
			$auth['error_message'] = $this->lang('curl_gateway_failure');
 
			return $auth; 
		}
		
		if (!$connect)
		{
			exit( $this->lang('curl_gateway_failure'));
		}
		$transaction =  $this->split_url_string($connect);
		
		if (is_array($transaction))
		{
			if (!empty($transaction['PROFILEID']) && ("SUCCESS" == strtoupper($transaction['ACK']) || "SUCCESSWITHWARNING" == strtoupper($transaction["ACK"]))) 
			{
				$auth = array(
					'authorized' 	=> TRUE,
					'error_message'	=> NULL,
					'failed'		=> FALSE,
					'declined'		=> FALSE,
					'transaction_id'=> $transaction['PROFILEID'], 
	 				);
			} 
			else  
			{
				if (!empty($transaction['L_LONGMESSAGE0']))
				{
					$auth['failed'] = TRUE; 
					
					$auth['error_message']	=$transaction['L_LONGMESSAGE0']. " ". $transaction['L_ERRORCODE0'];  
				}
			}
		}
		else
		{
			$auth['error_message']	= $this->lang('paypal_express_did_not_respond') ;
		}
 
		return $auth;
	}
	/*
	public function create_token($credit_card_number)
	{
		// https://cms.paypal.com/cms_content/US/en_US/files/developer/PP_AdaptivePayments.pdf
		// https://www.x.com/developers/paypal/documentation-tools/going-live-with-your-application
		$token = new Cartthrob_token();
		
		$xml = '<?xml version="1.0" encoding="utf-8"?>';
		$xml .= '<PreapprovalRequest xmlns="http://svcs.paypal.com/types/ap">';
		
		$xml .= '<requestEnvelope xmlns="">';
		$xml .= '<detailLevel>ReturnAll</detailLevel>';
		$xml .= '<errorLanguage>en_US</errorLanguage>'; // @NOTE apparently this is required. US English only: sorry
		$xml .= '</requestEnvelope>';
		$xml .= '<cancelUrl xmlns="">' . htmlentities($this->get_notify_url(ucfirst(get_class($this)),'cancel_payment') .'&invoice='.$this->order('order_id') ).'</cancelUrl>'; 
 
		$xml .= '<clientDetails xmlns="">';
		$xml .= '<applicationId xmlns="">'.$this->application_id.'</applicationId>'; 
		$xml .= '<customerId xmlns="">' . $this->order('member_id') . '</customerId>'; 
		$xml .= '<ipAddress xmlns="">' . $this->order('ip_address') . '</ipAddress>'; 
		$xml .= '</clientDetails>';
		
		$xml .= '<currencyCode xmlns="">' . $this->order('currency_code') . '</currencyCode>';
		$xml .= '<dateOfMonth xmlns="">0</dateOfMonth>'; // 0-31 (0 being any day)
		$xml .= '<dayOfWeek xmlns="">NO_DAY_SPECIFIED</dayOfWeek>'; 
		// The startingDate and endingDate can be in eiter Zulu or GMT offset formats.  
		$xml .= '<endingDate xmlns="">' . date('Y-m-d', strtotime("next Year UTC")).'Z'. '</endingDate>' ;  // can't be longer than one year from today
		$xml .= '<ipnNotificationUrl xmlns="">' . htmlentities($this->get_notify_url(ucfirst(get_class($this)), 'ipn_token').'&invoice='.$this->order('order_id')) . '</ipnNotificationUrl>'; 
	#	$xml .= '<maxAmountPerPayment xmlns="">' . $this->order('total') . '</maxAmountPerPayment>'; 
	#	$xml .= '<maxNumberOfPayments xmlns="">365</maxNumberOfPayments>'; 
		$xml .= '<maxNumberOfPaymentsPerPeriod xmlns="">365</maxNumberOfPaymentsPerPeriod>'; 
		$xml .= '<maxTotalAmountOfAllPayments xmlns="">' . 2000 . '</maxTotalAmountOfAllPayments>';// can't exceed 2000 USD (or equivalent in other currencies)
		if ($this->plugin_settings('subscription_memo'))
		{
			$xml .= '<memo>'. $this->plugin_settings('subscription_memo').'</memo>'; 
		}
		$xml .= '<paymentPeriod xmlns="">NO_PERIOD_SPECIFIED</paymentPeriod>' ; 
		$xml .= '<pinType xmlns="">NOT_REQUIRED</pinType>'; 
		$xml .= '<feesPayer xmlns="">SENDER</feesPayer>'; 
		$xml .= '<displayMaxTotalAmount xmlns="">false</displayMaxTotalAmount>' ; 
		$xml .= '<returnUrl xmlns="">' . htmlentities($this->get_notify_url(ucfirst(get_class($this)) ,'confirm_token').'&invoice='.$this->order('order_id')) . '</returnUrl>' ; 
		$xml .= '<senderEmail xmlns="">' .$this->order('email_address') . '</senderEmail>'; 
		$xml .= '<startingDate xmlns="">' .date('Y-m-d', strtotime("today")).'Z' . '</startingDate>'; 
		$xml .= '</PreapprovalRequest>';
		
 		$headers = array(
						'X-PAYPAL-SECURITY-USERID: '   			.$this->API_UserName, 
						'X-PAYPAL-SECURITY-PASSWORD: '   		.$this->API_Password, 
						'X-PAYPAL-SECURITY-SIGNATURE: '   		.$this->API_Signature, 
						'X-PAYPAL-SECURITY-VERSION: 74.0'   		, 
						'X-PAYPAL-REQUEST-DATA-FORMAT: XML', 
						'X-PAYPAL-RESPONSE-DATA-FORMAT: XML', 
						'X-PAYPAL-APPLICATION-ID: '   			.$this->application_id, 
						'X-PAYPAL-DEVICE-IPADDRESS: ' 			.$this->order('ip_address')
						);
 					
		if ($this->plugin_settings('mode') == "test")
		{
			array_push($headers, 'X-PAYPAL-SANDBOX-EMAIL-ADDRESS: '.$this->plugin_settings('dev_email'));
		}

		$curl = curl_init();
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
				
				curl_setopt($curl, CURLOPT_VERBOSE, 1);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
				curl_setopt($curl, CURLOPT_TIMEOUT, 30);
				curl_setopt($curl, CURLOPT_URL, $this->endpoint . "/AdaptivePayments/Preapproval");
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
				curl_setopt($curl, CURLOPT_HTTPHEADER,$headers);
		
		$connect = curl_exec($curl);		
		curl_close($curl);
 		
		if (!$connect)
		{
			$token->set_error_message( $this->lang('curl_gateway_failure') ); 
			return $token;
		}
		
		$transaction = new SimpleXMLElement($connect); 

 		if (!empty($transaction->responseEnvelope->ack))
		{
			if ("SUCCESS" == strtoupper($transaction->responseEnvelope->ack) || "SUCCESSWITHWARNING" == strtoupper($transaction->responseEnvelope->ack)) 
			{
				$token->set_token( (string) $transaction->preapprovalKey); 

				$this->update_order( array('preapproval_key' => (string) $transaction->preapprovalKey));
			}
		} 
		else  
		{  
			if (!empty($transaction->error))
			{
				$token->set_error_message($transaction->error->errorId.": ". $transaction->error->message); 
			}
			
			}
			return $token;
			
		// at this point, we already have the preapproval key. need to now actually get it approved by going offsite. 

 		if($this->plugin_settings("mode") == "test")
		{
			$this->gateway_exit_offsite(NULL, "https://www.sandbox.paypal.com/webscr?cmd=_ap-preapproval&preapprovalkey=" . $token); exit;
		}

		$this->gateway_exit_offsite(NULL, "https://www.paypal.com/webscr?cmd=_ap-preapproval&preapprovalkey=" . $token); exit;
  		
	}
	public function confirm_token($post)
	{ 
 		$auth = array(
			'authorized' 	=> FALSE,
			'processing' 	=> TRUE,
			'error_message'	=> "",
			'failed'		=> FALSE,
			'declined'		=> FALSE,
				);
 
		$token = new Cartthrob_token();
				
 		$xml = '<?xml version="1.0" encoding="utf-8"?>';
		$xml .= '<PreapprovalDetailsRequest xmlns="http://svcs.paypal.com/types/ap">';
		$xml .= '<preapprovalKey  xmlns="">' . $this->order('preapproval_key') . '</preapprovalKey>'; 
		$xml .= '<requestEnvelope xmlns="">';
		$xml .= '<detailLevel>ReturnAll</detailLevel>';
		$xml .= '<errorLanguage>en_US</errorLanguage>'; // @TODO make dynamic
		$xml .= '</requestEnvelope>';
		$xml .= '</PreapprovalDetailsRequest>';
		
		$headers = array(
						'X-PAYPAL-SECURITY-USERID: '   			.$this->API_UserName, 
						'X-PAYPAL-SECURITY-PASSWORD: '   		.$this->API_Password, 
						'X-PAYPAL-SECURITY-SIGNATURE: '   		.$this->API_Signature, 
 						'X-PAYPAL-SECURITY-VERSION: 74.0'   		, 
						'X-PAYPAL-REQUEST-DATA-FORMAT: XML', 
						'X-PAYPAL-RESPONSE-DATA-FORMAT: XML', 
						'X-PAYPAL-APPLICATION-ID: '   			.$this->application_id, 
						'X-PAYPAL-DEVICE-IPADDRESS: ' 			.$this->order('ip_address')
						);
 					
		if ($this->plugin_settings('mode') == "test")
		{
			array_push($headers, 'X-PAYPAL-SANDBOX-EMAIL-ADDRESS: '.$this->plugin_settings('dev_email'));
		}

		$curl = curl_init();
				curl_setopt($curl, CURLOPT_VERBOSE, 1);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
				curl_setopt($curl, CURLOPT_TIMEOUT, 30);
				curl_setopt($curl, CURLOPT_URL, $this->endpoint . "/AdaptivePayments/PreapprovalDetails");
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
				curl_setopt($curl, CURLOPT_HTTPHEADER,$headers);
		
		$connect = curl_exec($curl);		
		curl_close($curl);
 
		if (!$connect)
		{
			//@TODO should probably just return the error
			exit( $this->lang('curl_gateway_failure'));
		}
		$transaction = new SimpleXMLElement($connect); 
		
		if (!empty($transaction->responseEnvelope->ack))
		{
			if ("SUCCESS" == strtoupper($transaction->responseEnvelope->ack) || "SUCCESSWITHWARNING" == strtoupper($transaction->responseEnvelope->ack)) 
			{
				$token->set_token( $this->order('preapproval_key') ); 
			}
		}
		else  
		{  
			if (!empty($transaction->error))
			{
				$token->set_error_message($transaction->error->errorId.": ". $transaction->error->message); 
			}
 		}
		return $token;
	}
	public function charge_token($token, $customer_id = NULL, $offsite = TRUE)
	{
		if ($offsite = TRUE)
		{
			if($this->plugin_settings("mode") == "test")
			{
				$this->gateway_exit_offsite(NULL, "https://www.sandbox.paypal.com/webscr?cmd=_ap-preapproval&preapprovalkey=" . $token); 
			}

			$this->gateway_exit_offsite(NULL, "https://www.paypal.com/webscr?cmd=_ap-preapproval&preapprovalkey=" . $token);
			exit; 
		}
		$auth['authorized']	 	= FALSE; 
		$auth['declined'] 		= FALSE; 
		$auth['transaction_id']	= NULL;
		$auth['failed']			= TRUE; 
		$auth['error_message']	= "";
		
		$xml = '<?xml version="1.0" encoding="utf-8"?>';
		$xml .= '<PayRequest xmlns="http://svcs.paypal.com/types/ap">';

		$xml .= '<requestEnvelope xmlns="">';
		$xml .= '<detailLevel>ReturnAll</detailLevel>';
		$xml .= '<errorLanguage>en_US</errorLanguage>'; // @TODO make dynamic
		$xml .= '</requestEnvelope>';

		$xml .= '<actionType xmlns="">PAY</actionType>'; 
		$xml .= '<cancelUrl xmlns="">' . htmlentities($this->get_notify_url(ucfirst(get_class($this)),'cancel_payment').'&invoice='.$this->order('order_id')) . '</cancelUrl>'; //not used but required
		$xml .= '<currencyCode xmlns="">' . $this->order('currency_code') . '</currencyCode>';
		$xml .= '<feesPayer xmlns="">SENDER</feesPayer>'; 
		// @NOTE max chars: 1024
		$xml .= '<ipnNotificationUrl xmlns="">' . htmlentities($this->get_notify_url(ucfirst(get_class($this)), 'ipn_token').'&invoice='.$this->order('order_id')) . '</ipnNotificationUrl>'; 
		$xml .= '<preapprovalKey xmlns="">'.$token.'</preapprovalKey>' ; 
		$xml .= '<returnUrl xmlns="">' . htmlentities($this->get_notify_url(ucfirst(get_class($this)), 'confirm_token').'&invoice='.$this->order('order_id')) . '</returnUrl>' ; 
		$xml .= '<reverseAllParallelPaymentsOnError xmlns="">FALSE</reverseAllParallelPaymentsOnError>'; 
		$xml .= '<senderEmail xmlns="">' .$this->order('email_address') . '</senderEmail>'; 
		$xml .= '<trackingId xmlns="">' .$this->order('order_id') . '</trackingId>'; 
		
		$xml .= '<clientDetails xmlns="">';
		$xml .= '<applicationId xmlns="">'.$this->application_id . '</applicationId>'; 
		$xml .= '<customerId xmlns="">' . $this->order('member_id') . '</customerId>'; 
		$xml .= '<ipAddress xmlns="">' . $this->order('ip_address') . '</ipAddress>'; 
		$xml .= '</clientDetails>';
		
		$xml .= '<fundingConstraint xmlns="">';
		$xml .= '<allowedFundingType xmlns="">';
		$xml .= '<fundingTypeInfo xmlns="">';
		$xml .= '<fundingType xmlns="">BALANCE</fundingType>';
		$xml .= '</fundingTypeInfo>';
		$xml .= '<fundingTypeInfo xmlns="">';
		$xml .= '<fundingType xmlns="">CREDITCARD</fundingType>';
		$xml .= '</fundingTypeInfo>';
		$xml .= '</allowedFundingType>';
		$xml .= '</fundingConstraint>';
		
		$xml .= '<receiverList xmlns="">';
			$xml .= '<receiver xmlns="">';
			$xml .= '<amount xmlns="">' . $this->order('total') . '</amount>'; 
			$xml .= '<email xmlns="">' . $this->order('dev_email') . '</email>'; 
			$xml .= '<invoiceId xmlns="">' . $this->order('order_id') . '</invoiceId>' ;
			$xml .= '<paymentType xmlns="">SERVICE</paymentType>';
			$xml .= '<primary xmlns="">TRUE</primary>' ; 
			$xml .= '</receiver>';
 		$xml .= '</receiverList>';
		
		$xml .= '<account xmlns="">';
		$xml .= '<email xmlns="">' .$this->order('email_address') . '</email>' ; 
		$xml .= '</account>';
		
		$xml .= '</PayRequest>'; 
		
 		$headers = array(
						'X-PAYPAL-SECURITY-USERID: '   			.$this->API_UserName, 
						'X-PAYPAL-SECURITY-PASSWORD: '   		.$this->API_Password, 
						'X-PAYPAL-SECURITY-SIGNATURE: '   		.$this->API_Signature, 
						'X-PAYPAL-SECURITY-VERSION: 74.0' , 
						'X-PAYPAL-REQUEST-DATA-FORMAT: XML', 
						'X-PAYPAL-RESPONSE-DATA-FORMAT: XML', 
						'X-PAYPAL-APPLICATION-ID: '   			.$this->application_id, 
						'X-PAYPAL-DEVICE-IPADDRESS: ' 			.$this->order('ip_address')
						);
 					
		if ($this->plugin_settings('mode') == "test")
		{
			array_push($headers, 'X-PAYPAL-SANDBOX-EMAIL-ADDRESS: '.$this->plugin_settings('dev_email'));
		}

		$curl = curl_init();
				curl_setopt($curl, CURLOPT_VERBOSE, 1);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
				curl_setopt($curl, CURLOPT_TIMEOUT, 30);
				curl_setopt($curl, CURLOPT_URL, $this->endpoint . "/AdaptivePayments/Pay");
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
				curl_setopt($curl, CURLOPT_HTTPHEADER,$headers);
		
		$connect = curl_exec($curl);		
		curl_close($curl);
 
		$token = new Cartthrob_token();
		
		if (!$connect)
		{
			$token->set_error_message($this->lang('curl_gateway_failure')); 
			return $token;
		}
		$transaction = new SimpleXMLElement($connect); 
		
		if (!empty($transaction->responseEnvelope->ack))
		{
			if ("SUCCESS" == strtoupper($transaction->responseEnvelope->ack) || "SUCCESSWITHWARNING" == strtoupper($transaction->responseEnvelope->ack)) 
			{
				return $token; 
 			}
		}
		else  
		{
			if (!empty($transaction->error))
			{
				$token->set_error_message($transaction->error->errorId.": ". $transaction->error->message); 
			}
			return $token; 
		}
		
		return $auth; 
	}
	
	public function ipn_token($post)
	{
		// https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_api_APIPN
		
		if (empty($post))
		{
			@header("HTTP/1.0 404 Not Found");
	        @header("HTTP/1.1 404 Not Found");
	        exit('No Data Sent');
		}
		$payment_status = strtolower(trim($post['payment_status'])) ; 
		list($order_id) = explode("-", $post['invoice']); 

		$auth = array(
			'authorized' 	=> FALSE,
			'error_message'	=> NULL,
			'failed'		=> TRUE,
			'declined'		=> FALSE,
			'transaction_id'=> NULL, 
			'processing'	=> FALSE
			); 
		
		// The return response to Paypal must contain all of the data of the original
		// with the addition of the notify-validate command

 
		$post['cmd'] = '_notify-validate';
		$data = $this->data_array_to_string($post);

		// Fix for multi-line data
		// Thanks to Dom.S for finding a cure for this multi-line issue.  
		// and to me for finally figuring out I needed to move this AFTER I urlencoded the data
		// ***** facepalm ******
		$data = preg_replace('/(.*[^%^0^D])(%0A)(.*)/i','${1}%0D%0A${3}',  $data);
			
		// RESULT will either contain VERIFIED or INVALID
		$result = $this->curl_transaction($this->_host,$data);
 		
 		$this->log("paypal result: {$result}");
		$this->log("order: ". $this->order('order_id')); 
 		
		if (stristr($result, 'VERIFIED'))
    	{
			$this->log("paypal: valid");
			
			// we don't want paypal info for ID's that we're not using, and we don't want information about unfinished transactions
			$paypal_id = ($this->plugin_settings('mode') == 'live') ? $this->plugin_settings('api_username') : $this->plugin_settings('test_username');
			if ($paypal_id != trim($post['receiver_email']))
    		{
				$payment_status = "failed";
				$post['reason_code'] = (isset($post['reason_code'])? $post['reason_code']. " ". $this->lang('paypal_incorrect_id'): $this->lang('paypal_incorrect_id')); 
    		}
 			
			switch( $payment_status)
			{
				case "created": 
				case "completed":
				case "processed": //masspay

					$auth = array(
						'authorized' 	=> TRUE,
						'error_message'	=> NULL,
						'failed'		=> FALSE,
						'declined'		=> FALSE,
						'transaction_id'=> $post['txn_id'], 
						);    
					$this->relaunch_cart(NULL, $order_id);
			 			  
					$this->set_status_authorized($auth, $order_id,  FALSE); 
 
  					break;    
					
				case "canceled_reversal": 
					$auth = array(
						'authorized' 	=> FALSE,
						'error_message'	=> "cancelled reversal ". $post['reason_code'],
						'failed'		=> TRUE,
						'declined'		=> FALSE,
						'transaction_id'=> NULL
						);  
					$this->set_status_pending($auth, $order_id,  FALSE); 	
 
 					break;
				case "denied": 
					$auth = array(
						'authorized' 	=> FALSE,
						'error_message'	=> "denied",
						'failed'		=> FALSE,
						'declined'		=> TRUE,
						'transaction_id'=> NULL
						);  
						$this->relaunch_cart(NULL, $order_id);
			 			
					$this->set_status_declined($auth, $order_id,  FALSE); 	
 
 					break;
				case "failed": 
					$auth = array(
						'authorized' 	=> FALSE,
						'error_message'	=> "failed",
						'failed'		=> TRUE,
						'declined'		=> FALSE,
						'transaction_id'=> NULL
						);  
						$this->relaunch_cart(NULL, $order_id);
			 			
					$this->set_status_failed($auth, $order_id,  FALSE); 	
 
 					break;
 				case "expired": 
					$auth = array(
						'authorized' 	=> FALSE,
						'error_message'	=> "expired",
						'failed'		=> TRUE,
						'declined'		=> FALSE,
						'transaction_id'=> NULL
						);  
					$this->set_status_expired($auth, $order_id,  FALSE); 	
 
					break;
				case "voided":
					$auth = array(
						'authorized' 	=> FALSE,
						'error_message'	=> "voided",
						'failed'		=> TRUE,
						'declined'		=> FALSE,
						'transaction_id'=> NULL,
						);  
					$this->set_status_voided($auth, $order_id,  FALSE); 	
 
					break;
				case "refunded": 
					$auth = array(
						'authorized' 	=> FALSE,
						'error_message'	=> "refunded". $post['reason_code'],
						'failed'		=> TRUE,
						'declined'		=> FALSE,
						'transaction_id'=> NULL,
						);
					$this->set_status_refunded($auth, $order_id,  FALSE); 	
 
					break;
				case "reversed": 
					$auth = array(
						'authorized' 	=> FALSE,
						'error_message'	=> "reversal " . $post['reason_code'],
						'failed'		=> TRUE,
						'declined'		=> FALSE,
						'transaction_id'=> NULL,
						);
					$this->set_status_reversed($auth, $order_id,  FALSE); 	
 
					break;
				case "pending": 
					$auth = array(
						'authorized' 	=> FALSE,
						'error_message'	=> "pending: ".$post['pending_reason'],
						'failed'		=> FALSE,
						'declined'		=> FALSE,
						'processing'	=> TRUE,
						'transaction_id'=> $post['txn_id'], 
						);
						$this->relaunch_cart(NULL, $order_id);
			 			
					$this->set_status_pending($auth, $order_id,  FALSE); 
 
					break;
				default: 
 					break;
			}   
 			
		}
		elseif (stristr($result, 'INVALID'))
		{	
			$this->log("paypal: invalid");
			
			if ($this->plugin_settings('log_errors')== "yes")
			{
				// LOGGING ///////////////////////////////////
				// this folder has to be writeable
				// the default is one folder above index.php
				if ($logfile = fopen("../paypal_problems.log", "a"))
				{
					fwrite($logfile, sprintf("\r%s:- %s",date("D M j G:i:s T Y"), $_SERVER["REQUEST_URI"] ));
					foreach ($_POST as $key=>$item)
					{
						$this->log("paypal post-{$key}: {$item}");
						
						fwrite($logfile, sprintf("\r%s:- %s",date("D M j G:i:s T Y"), "pp". $key ." : " . $item ));
					}
					fwrite($logfile, sprintf("\r%s:- %s",date("D M j G:i:s T Y"), $data ));
				}
			}
			
			$auth = array(
				'authorized' 	=> FALSE,
				'error_message'	=> $this->lang('paypal_not_verified'),
				'failed'		=> TRUE,
				'declined'		=> FALSE,
				'transaction_id'=> NULL, 
				);
				
				
			$this->set_status_failed($auth, $order_id,  FALSE); 	
 
		}
		else
		{
			$this->log("paypal: unknown response");
			
			$auth = array(
				'authorized' 	=> FALSE,
				'error_message'	=> $this->lang('paypal_unknown_response'),
				'failed'		=> TRUE,
				'declined'		=> FALSE,
				'transaction_id'=> NULL, 
				);
				
			$this->set_status_failed($auth, $order_id, FALSE); 	
 
		}
 		// we don't want to go through the normal checkout_complete function, so we exit here. 
		exit;
	}
	*/
}// END Class