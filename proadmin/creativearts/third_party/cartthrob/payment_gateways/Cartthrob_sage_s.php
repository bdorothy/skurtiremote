<?php 
class Cartthrob_sage_s extends Cartthrob_payment_gateway
{
	public $title = 'sage_server_title';
	public $affiliate = 'sage_affiliate'; 
	public $overview = 'sage_overview';
	public $language_file = TRUE;
 	public $settings = array(
		array(
			'name' => 'sage_payment_page_style', 
			'short_name' => 'profile', 
			'type' => 'radio',  
			'default' => 'NORMAL', 
			'options' => array(
				'NORMAL' => 'sage_normal',
				'LOW' => 'sage_minimal_formatting'
				),
		),
		array(
			'name' =>  'mode',
			'short_name' => 'mode', 
			'type' => 'radio',  
			'default' => 'test', 
			'options' => array(
				'simulator' => 'simulator',
				'test' => 'test',
				'live' => 'live'
				),
		),
		array(
			'name' => 'sage_vendor_name',
			'short_name' => 'vendor_name', 
			'type' => 'text',
		),
	);
	
	public $required_fields = array(
		'first_name',
		'last_name',
		'address',
		'city',
		'zip',
		'country_code'
	);
	
	
	public $fields = array(
		'first_name'           ,
		'last_name'            ,
		'address'              ,
		'address2'             ,
		'city'                 ,
		'state'                ,
		'zip'                  ,
		'country_code'         ,
		'shipping_first_name'  ,
		'shipping_last_name'   ,
		'shipping_address'     ,
		'shipping_address2'    ,
		'shipping_city'        ,
		'shipping_state'       ,
		'shipping_zip'         ,
		'shipping_country_code',
		'phone'                ,
		'email_address'        ,
 		); 
		
	public $hidden = array('description','currency_code');
	
	private $_host = "https://test.sagepay.com/gateway/service/vspserver-register.vsp";
	private $tokenrebillhost = "https://test.sagepay.com/gateway/service/vspdirect-register.vsp"; 
	private $tokenhost = "https://test.sagepay.com/gateway/service/token.vsp";
	
	
	public function initialize()
	{
	
 		if ($this->plugin_settings('mode') == "test")
		{
 			$this->_host = "https://test.sagepay.com/gateway/service/vspserver-register.vsp";
			$this->tokenrebillhost = "https://test.sagepay.com/gateway/service/vspdirect-register.vsp"; 
			$this->tokenhost = "https://test.sagepay.com/gateway/service/token.vsp"; 
			
		}
		elseif ($this->plugin_settings('mode') == "simulator")
		{
			$this->_host = "https://test.sagepay.com/Simulator/VSPServerGateway.asp?Service=VendorRegisterTx";
		}
		else
		{
			$this->tokenrebillhost = "https://live.sagepay.com/gateway/service/vspdirect-register.vsp"; 
			$this->tokenhost = "https://live.sagepay.com/gateway/service/token.vsp"; 
			
			$this->_host = "https://live.sagepay.com/gateway/service/vspserver-register.vsp";
		}	
	}
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
		$auth['authorized'] 	=	FALSE; 
		$auth['declined']		=	FALSE; 
		$auth['failed']			=	TRUE; 
		$auth['error_message']	= 	NULL; 
		$auth['transaction_id']	=	NULL;
		
		$basket=""; 
			
		if ($this->order('items'))
		{
			$basket = (count($this->order('items'))+2).":"; 
 
			foreach ($this->order('items') as $row_id => $item)
			{
 				$basket .= str_replace(":","",$item['title']) .":";
				$basket .= $item['quantity'] .":";
				$basket .= number_format($item['price'],2,'.','').":";
				$basket .= ":";
				$basket .= number_format($item['price'],2,'.','').":";
				$basket .= number_format(($item['price']*$item['quantity']),2,'.','').":";
 
			}
			$basket .= 'Shipping:----:----:----:----:';
			$basket .= number_format($this->order('shipping'), 2, '.', '').":";
			$basket .= 'VAT/Tax:----:----:----:----:';
			$basket .= number_format($this->order('tax'), 2, '.', '');
		}

		if (strlen($basket) > 7499)
		{
			// the basket can't be over 7500, and has to be formatted a specific way. We'll remove it if it's too long.
			$basket = ""; 
		}
		
		$country_code = $this->order('country_code') ? $this->alpha2_country_code($this->order('country_code')) : "GB"; 
		$shipping_country_code = $this->order('shipping_country_code') ? $this->alpha2_country_code($this->order('shipping_country_code')) : $country_code; 
		
		$post_array = array(
			'VPSProtocol' 				=> "2.23",
			'TxType'					=> 'PAYMENT',
			'Vendor'					=> $this->plugin_settings('vendor_name'),
			'VendorTXCode'				=> $this->order('entry_id')."_".time(), // needs a unique ID for this transaction. 
			'Amount'					=> number_format($this->total(),2,'.',''),
			'Currency'					=> ($this->order('currency_code') ? $this->order('currency_code') : "GBP"),
			'Description'				=> substr(($this->order('description') ? $this->order('description') : "Purchase from ".$this->order('site_name')), 0, 40),
			'BillingSurname'			=> substr($this->order('last_name'),0,20),
			'BillingFirstnames'			=> substr($this->order('first_name'), 0, 20),
			'BillingAddress1'			=> substr($this->order('address'), 0, 100),
			'BillingAddress2'			=> substr($this->order('address2'), 0, 100),
			'BillingCity'				=> substr($this->order('city'), 0, 40),
			'BillingPostCode'			=> substr($this->order('zip'), 0, 10),
			'BillingCountry'			=> $country_code,
			'BillingPhone'				=> preg_replace('/[^0-9-]/', '', $this->order('phone')),
			'DeliverySurname'			=> substr(($this->order('shipping_last_name') ? $this->order('shipping_last_name') : $this->order('last_name')),0,20),
			'DeliveryFirstnames'		=> substr(($this->order('shipping_first_name') ? $this->order('shipping_first_name') : $this->order('first_name')), 0, 20),
			'DeliveryAddress1'			=> substr(($this->order('shipping_address') ? $this->order('shipping_address') : $this->order('address')), 0, 100),
			'DeliveryAddress2'			=> substr(($this->order('shipping_address2') ? $this->order('shipping_address2') : $this->order('address2')), 0, 100),
			'DeliveryCity'				=> substr(($this->order('shipping_city') ? $this->order('shipping_city') : $this->order('city')), 0, 40),
			'DeliveryPostCode'			=> substr(($this->order('shipping_zip') ? $this->order('shipping_zip') : $this->order('zip')), 0, 10),
			'DeliveryCountry'			=> $shipping_country_code,
			'CustomerEMail'				=> $this->order('email_address'),
			'Basket'					=> $basket,
			'NotificationURL'			=> $this->response_script(ucfirst(get_class($this))),  
		); 

		if (strlen($post_array['NotificationURL']) > 250)
		{
			$resp = array(
				 'authorized'		=>	FALSE,
				 'error_message'	=>	$this->lang('sage_s_notification_url_too_long'),
				 'failed'			=>	TRUE,
				 'declined'			=>	FALSE,
				 'transaction_id' 	=>	NULL,
				);
			return $resp; 
		}

		// We don't want to pass the state data to eWay unless it has 2 characters and is a us state. They don't accept any non-us state values
		if ("US" != $post_array['DeliveryCountry'])
		{
		    $post_array['DeliveryState']  = "";
		}
		else
		{
			$post_array['DeliveryState'] = strtoupper($this->order('shipping_state') ? $this->order('shipping_state') : $this->order('state'));
		}
		if ("US" != $post_array['BillingCountry'])
		{
		    $post_array['BillingState']  = ""; 
		}
		else
		{
		    $post_array['BillingState'] = strtoupper($this->order('state')); 
		}
		
		$post_array['Profile']  = $this->plugin_settings('profile'); 

		$data = 	$this->data_array_to_string($post_array);
		
		$connect = 	$this->curl_transaction($this->_host,$data); 
		

		if (!$connect)
		{
			$auth['failed']			= TRUE;
			$auth['authorized']		= FALSE;
			$auth['declined']		= FALSE;
			$auth['error_message']	= $this->lang('curl_gateway_failure'); 
			return $auth; 
		}
		
		$transaction =  $this->split_url_string($connect, "\r\n");
		$next_url = explode("NextURL=", $connect);

		if (!empty($next_url[1]))
		{
			$next_url = str_replace("\r\n","",$next_url[1]);
		}

		if (!is_array($transaction))
		{
			$auth['failed']			= TRUE;
			$auth['authorized']		= FALSE;
			$auth['declined']		= FALSE;
			$auth['error_message']	= $this->lang('sage_failed');
			
			return $auth;
		}
		
		if ("OK" != strtoupper($transaction['Status']))
		{
			switch(strtoupper($transaction['Status']))
			{
				case "MALFORMED":
					$auth['error_message'] = $this->lang('sage_malformed'); 
					$auth['error_message'] .= $transaction['StatusDetail']; 
					break; 
				case "INVALID":
					$auth['error_message'] = $this->lang('sage_invalid');
					$auth['error_message'] .= $transaction['StatusDetail']; 
					break;
				case "ERROR":
					$auth['error_message'] = $this->lang('sage_error');
					break;
				default:
					$auth['error_message'] = $this->lang('sage_default');
			}
			
			$auth['failed']			= TRUE;
			$auth['authorized']		= FALSE;
			$auth['declined']		= FALSE;
			$auth['transaction_id']	= NULL; 
			return $auth; 
		}
 
  		$this->update_order(array('sage_key' => $transaction['SecurityKey']));
		$this->gateway_exit_offsite(NULL, $next_url); exit;
	}
 	public function charge_token($token, $customer_id)
	{
		$resp = array(
			 'authorized'		=>	FALSE,
			 'error_message'	=>	NULL,
			 'failed'			=>	TRUE,
			 'declined'			=>	FALSE,
			 'transaction_id' 	=>	NULL,
			);
			
		switch ($this->order('card_type'))
		{
			case "mc": 
				$card_type="MC"; 
				break;
			case 'visa': 
				$card_type="VISA"; 
				break;
			case 'amex': 
				$card_type="AMEX"; 
				break;
			case 'maestro': 
				$card_type="MAESTRO"; 
				break;
			case 'solo': 
				$card_type="SOLO"; 
				break;
			case 'delta': 
				$card_type="DELTA"; 
				break;
			default: $card_type="VISA";  
		}
		$basket=""; 
		
		if ($this->order('items'))
		{
			$basket = (count($this->order('items'))+2).":"; 
 
			foreach ($this->order('items') as $row_id => $item)
			{
 	
				$basket .= $item['title'] .":";
				$basket .= $item['quantity'] .":";
				$basket .= number_format($item['price'],2,'.','').":";
				$basket .= ":";
				$basket .= number_format($item['price'],2,'.','').":";
				$basket .= number_format(($item['price']*$item['quantity']),2,'.','').":";
 
			}
			$basket .= 'Shipping:----:----:----:----:';
			$basket .= number_format($this->order('shipping'), 2, '.', '').":";
			$basket .= 'VAT/Tax:----:----:----:----:';
			$basket .= number_format($this->order('tax'), 2, '.', '');
		}
		
		$post_array = array(
			'VPSProtocol' 				=> "2.23",
			'TxType'					=> 'PAYMENT',
			'Vendor'					=> $this->plugin_settings('vendor_name'),
			'VendorTxCode'				=> $this->order('entry_id')."_".time(),
			'Amount'					=> number_format($this->order('total'),2,'.',''),
			'Currency'					=> ( $this->order('currency_code')?  $this->order('currency_code') : "GBP"),  
			'Description'				=>  ($this->order('description') ? $this->order('description') : "Purchase from ".$this->order('site_name')),
			'Token'						=> $token,
			'StoreToken'				=> 1,
			// 'CV2'			=> '', // NB: If AVS/CV2 is ON for your account this field becomes compulsory.
			'BillingSurname'			=> $this->order('last_name'),
			'BillingFirstnames'			=> $this->order('first_name'),
			'BillingAddress1'			=> $this->order('address'),
			'BillingAddress2'			=> $this->order('address2'),
			'BillingCity'				=> $this->order('city'),
			'BillingPostCode'			=> $this->order('zip'),
			'BillingCountry'			=> $this->alpha2_country_code($this->order('country_code')),
			'BillingPhone'				=> preg_replace('/[^0-9-]/', '', $this->order('phone')),
			'DeliverySurname'			=> ($this->order('shipping_last_name') ? $this->order('shipping_last_name') : $this->order('last_name')),
			'DeliveryFirstnames'		=> ($this->order('shipping_first_name') ? $this->order('shipping_first_name') : $this->order('first_name')),
			'DeliveryAddress1'			=> ($this->order('shipping_address') ? $this->order('shipping_address') : $this->order('address')),
			'DeliveryAddress2'			=> ($this->order('shipping_address2') ? $this->order('shipping_address2') : $this->order('address2')),
			'DeliveryCity'				=> ($this->order('shipping_city') ? $this->order('shipping_city') : $this->order('city')),
			'DeliveryPostCode'			=> ($this->order('shipping_zip') ? $this->order('shipping_zip') : $this->order('zip')),
			'DeliveryCountry'			=> ($this->order('shipping_country_code') ? $this->alpha2_country_code($this->order('shipping_country_code')) : $this->alpha2_country_code($this->order('country_code'))),
			'CustomerEMail'				=> $this->order('email_address'),
			'Basket'					=> $basket,
			'Apply3DSecure'				=> 0,
			'AccountType'				=> 'C', 
		    'Apply3DSecure'				 => 2,
		    'ApplyAVSCV2' 				 => 2
 		);	
		// We don't want to pass the state data to Sage unless it has 2 characters and is a us state. They don't accept any non-us state values
		if ("US" != $post_array['DeliveryCountry'])
		{
		    $post_array['DeliveryState']  = "";
		}
		else
		{
		    $post_array['DeliveryState'] = strtoupper($this->order('shipping_state'));
		}
		if ("US" != $post_array['BillingCountry'])
		{
		    $post_array['BillingState']  = ""; 
		}
		else
		{
		    $post_array['BillingState'] = strtoupper($this->order('state')); 
		}
		
		$data = 	$this->data_array_to_string($post_array);
		$connect = 	$this->curl_transaction($this->tokenrebillhost,$data); 
		
		if (!$connect)
		{
			$resp['failed']			= TRUE;
			$resp['authorized']		= FALSE;
			$resp['declined']		= FALSE;
			$resp['error_message']	= $this->lang('curl_gateway_failure'); 
			
			return $resp; 
		} 	
		
 		$transaction =  $this->split_url_string($connect, "\r\n");

		if (!empty($transaction['Status']))
		{
			if ("OK" == strtoupper($transaction['Status']))
			{
				$resp = array(
					 'authorized'		=>	TRUE,
					 'error_message'	=>	NULL,
					 'failed'			=>	FALSE,
					 'declined'			=>	FALSE,
					 'transaction_id' 	=> 	trim($transaction['VPSTxId'], "{}")
					);
			}
			else
			{
				switch(strtoupper($transaction['Status']))
				{
					case "MALFORMED":
					$error_message = $this->lang('sage_malformed'); 
					$error_message .= $transaction['StatusDetail']; 
					break; 
					case "INVALID":
					$error_message = $this->lang('sage_invalid');
					$error_message .= $transaction['StatusDetail']; 
					break;
					case "ABORT":
					$error_message = $this->lang('transaction_cancelled');
					break;
					case "NOTAUTHED":
					$error_message = $this->lang('sage_notauthed');
					break; 
					case "REJECTED": 
					$error_message = $this->lang('sage_rejected'); 
					break; 
					case "PPREDIRECT":
					$error_message = $this->lang('sage_ppredirect');
					break;
					case "AUTHENTICATED":
					$error_message = $this->lang('sage_authenticated');
					break;
					case "REGISTERED":
					$error_message = $this->lang('sage_registered');
					break;
					case "ERROR":
					$error_message = $this->lang('sage_error');
					break;
					case "ATTEMPTED":
					$error_message =  $this->lang('sage_error')."; ATTEMPTED"; //$this->lang('sage_ATTEMPTED');
					break;
					case "NOTAVAILABLE":
					$error_message = $this->lang('sage_error')."; NOTAVAILABLE";  //$this->lang('sage_NOTAVAILABLE');
					break;
					case "INCOMPLETE":
					$error_message = $this->lang('sage_error')."; INCOMPLETE"; //$this->lang('sage_INCOMPLETE');
					break;
					default:
					$error_message = $this->lang('sage_default');
				}

				$resp = array(
					 'authorized'		=>	FALSE,
					 'error_message'	=>	$error_message,
					 'failed'			=>	TRUE,
					 'declined'			=>	FALSE,
					 'transaction_id' 	=> 	NULL
					);
			}
		}
		return $resp;
	}
	
	public function create_token($credit_card_number)
	{
		$post_array = array(
			'VPSProtocol' 				=> "2.23",
			'VendorTXCode'				=> $this->order('entry_id')."_".time(), // needs a unique ID for this transaction. 
			'TxType'					=> 'TOKEN',
			'Vendor'					=> $this->plugin_settings('vendor_name'),
 			'Currency'					=> ( $this->order('currency_code')?  $this->order('currency_code') : "GBP"),  
			'NotificationURL'			=> $this->response_script(ucfirst(get_class($this)), array('create_token')),  
			'Profile'					  => $this->plugin_settings('profile'),
			'AccountType'				=> 'C'
 		);	
		
 		$data = 	$this->data_array_to_string($post_array);
		
		$connect = 	$this->curl_transaction($this->tokenhost,$data); 
		
		if (!$connect)
		{
			$auth['failed']			= TRUE;
			$auth['authorized']		= FALSE;
			$auth['declined']		= FALSE;
			$auth['error_message']	= $this->lang('curl_gateway_failure'); 
			return $auth; 
		}
		
		$transaction =  $this->split_url_string($connect, "\r\n");
		$next_url = explode("NextURL=", $connect);

		if (!empty($next_url[1]))
		{
			$next_url = str_replace("\r\n","",$next_url[1]);
		}

		if (!is_array($transaction))
		{
			$auth['failed']			= TRUE;
			$auth['authorized']		= FALSE;
			$auth['declined']		= FALSE;
			$auth['error_message']	= $this->lang('sage_failed');
			
			return $auth;
		}
		
		if ("OK" != strtoupper($transaction['Status']))
		{
			switch(strtoupper($transaction['Status']))
			{
				case "MALFORMED":
					$auth['error_message'] = $this->lang('sage_malformed'); 
					$auth['error_message'] .= $transaction['StatusDetail']; 
					break; 
				case "INVALID":
					$auth['error_message'] = $this->lang('sage_invalid');
					$auth['error_message'] .= $transaction['StatusDetail']; 
					break;
				case "ERROR":
					$auth['error_message'] = $this->lang('sage_error');
					break;
				default:
					$auth['error_message'] = $this->lang('sage_default');
			}
			
			$auth['failed']			= TRUE;
			$auth['authorized']		= FALSE;
			$auth['declined']		= FALSE;
			$auth['transaction_id']	= NULL; 
			return $auth; 
		}
 
  		$this->update_order(array('sage_key' => $transaction['SecurityKey']));
		$this->gateway_exit_offsite(NULL, $next_url); exit;
	}
 
	/**
	 * payment_notification
	 *
	 * @return void
	 * @author Chris Newton
	 * @since 1.0
	 **/
	function extload($post)
	{
		$auth['authorized'] 	=	FALSE; 
		$auth['declined']		=	FALSE; 
		$auth['failed']			=	TRUE; 
		$auth['error_message']	= 	NULL; 
		$auth['transaction_id']	=	NULL;
		
		if (!empty($post['ct_action']) && $post['ct_action'] == "charge_token")
		{
			// cart should still be active, since it's basically a reload
 			$transaction = $this->charge_token($this->order('token'), NULL);
			$this->checkout_complete_offsite($transaction, $this->order('order_id'), $this->order('return'));
			exit; 
		}
 		if (!empty($post['VendorTxCode']))
		{
			list($order_id) = explode("_", $post['VendorTxCode']); 
			
			$this->relaunch_cart(NULL, $order_id); 	
		}
		else
		{
			die($this->lang('sage_default')); 
		}
		
		if (strpos( $this->order('return'), 'http') === 0)
		{
			$return_url =  $this->order('return'); 
		}
		else
		{
			$return_url = $this->create_url($this->order('return'));
		}
		
 		
		if ("OK" == strtoupper($post['Status']))
		{
			$tmp = array(
				'VPSTxId'			=>	$post['VPSTxId'],
				'VendorTxCode'		=>	$post['VendorTxCode'],
				'Status'  			=>	$post['Status'],
				'TxAuthNo'			=>	$post['TxAuthNo'],
				'VendorName' 		=>	strtolower($this->plugin_settings('vendor_name')),
				'AVSCV2'			=>	$post['AVSCV2'],
				'SecurityKey'		=>	$this->order('sage_key'),
				'AddressResult'		=>	$post['AddressResult'],
				'PostCodeResult'	=>	$post['PostCodeResult'],
				'CV2Result'			=>	$post['CV2Result'],
				'GiftAid'			=>	$post['GiftAid'],
				'3DSecureStatus'	=>	$post['3DSecureStatus'],
				'CAVV'				=>	(!empty($post['CAVV'])?$post['CAVV']: ""),
				'AddressStatus'		=>	(!empty($post['AddressStatus'])?$post['AddressStatus']: ""),
				'PayerStatus'		=>	(!empty($post['PayerStatus'])?$post['PayerStatus']: ""),
				'CardType'			=>	$post['CardType'],
				'Last4Digits'		=>	$post['Last4Digits']
				); 
			
			if (empty($tmp['AddressStatus']))
			{
				unset ($tmp['AddressStatus']);
				unset ($tmp['PayerStatus']); 
			}
			
			$md5 = implode("",$tmp);
			
			$md5hash = strtoupper(md5($md5));
			
			if (!empty($post['ct_action']) && $post['ct_action'] == "create_token")
			{	
				$tmp = array(
					'VPSTxId'			=>	$post['VPSTxId'],
					'VendorTxCode'		=>	$post['VendorTxCode'],
					'Status'  			=>	$post['Status'],
					'VendorName' 		=>	strtolower($this->plugin_settings('vendor_name')),
					'Token'				=> 	$post['Token'],
					'SecurityKey'		=>	$this->order('sage_key'),
					); 

				$md5 = implode("",$tmp);

				$md5hash = strtoupper(md5($md5));
			}
			
			/////////////////////////////////////////////////////////////
			$this->write_to_log("order id = ".$order_id."\n");
			$this->write_to_log("\n\nTMP Array"."\n");
			if (isset($tmp))
			{
				foreach($tmp as $k => $v)
				{
					$this->write_to_log("$k - ".$v."\n");
				}
			}
			$this->write_to_log("\n\nPOST Array"."\n");
			foreach($post as $key => $value)
			{
				$this->write_to_log("$key - ".$value."\n");
			}
			$this->write_to_log("md5 - ".$md5."\n");
			$this->write_to_log("md5hash - ".$md5hash."\n");
			$this->write_to_log("md5hash-lower - ".$md5hash2."\n");
			$this->write_to_log("return - ".$return_url."\n");
			/////////////////////////////////////////////////////////////
			
			
			if ($md5hash != strtoupper($post['VPSSignature']))
			{
				$auth['authorized'] 	=	FALSE; 
				$auth['declined']		=	FALSE; 
				$auth['failed']			=	TRUE; 
				$auth['error_message']	= 	$this->lang('sage_signature_not_valid'); 
				$auth['transaction_id']	=	NULL;
				
				$this->write_to_log("vps signature failed \n\n");
 				
				$this->checkout_complete_offsite($auth, $order_id, 'stop_processing'); 
				// SAGE requires that we output this stuff. 
				@ob_clean();
				header("Content-type: text/plain");
				echo "Status=INVALID\r\n";
				echo "RedirectURL=".$return_url."\r\n";
				@ob_flush();
				exit; 
			}
			$auth['authorized'] 	=	TRUE; 
			$auth['declined']		=	FALSE; 
			$auth['failed']			=	FALSE; 
			$auth['error_message']	= 	NULL; 
			$auth['transaction_id']	=	trim($post['VPSTxId'], "{}"); 
			// "Auth:".$post['TxAuthNo']."_Tx:".$post['VPSTxId']."_Vnd:".$post['VendorTxCode']."_Sec:".$_SESSION['cartthrob']['sage_key'];
			
			if (!empty($post['ct_action']) && $post['ct_action'] == "create_token")
			{
				$token = new Cartthrob_token();
				$token->set_token( $post['Token'] ); 
				
				$new_vault = array(
					'customer_id' => $token->customer_id(),  
					'token' => $token->token(),
					'order_id' => $order_id,
					'member_id' => $this->order('member_id'),
					'gateway' => ucfirst(get_class($this)),
					'last_four' => $post['Last4Digits'],
				);
 				foreach ($new_vault as $key => $value)
				{
					$this->write_to_log("vault: $key - ".$value."\n");
				}
 				
				$vault['id'] = $this->update_vault_data($new_vault);
				$this->update_order(array('vault_id' => $vault['id']));
				$this->update_order(array('token' => $token->token() ));
				$this->save_cart();
				
		  		$return_url = $this->response_script(ucfirst(get_class($this)), array('charge_token'));
 				$this->write_to_log("new vault id - ".$vault['id'] ."\n");
				$this->write_to_log("return url - ".$return_url ."\n");
 				
 			}

			$this->write_to_log('', $close = TRUE); 
			
 			$this->checkout_complete_offsite($auth, $order_id, 'stop_processing'); 
			// SAGE requires that we output this stuff. 
			@ob_clean();
			header("Content-type: text/plain");
			echo "Status=OK\r\n";
			echo "RedirectURL=".$return_url."\r\n";
			@ob_flush();
			exit;
		}
		else
		{
			$status = NULL; 
			$redirect_url = NULL; 
			switch(strtoupper($post['Status']))
			{
				case "NOTAUTHED":
					$auth['error_message'] = $this->lang('sage_notauthed');
					$status = "Status=OK\r\n";
					break; 
				case "ABORT":
					$auth['error_message'] =  $this->lang('transaction_cancelled');  
 					$status = "Status=OK\r\n";
					$redirect_url = "RedirectURL=".$return_url."\r\n";
				
					$this->set_status_canceled($auth, $this->order('entry_id'),  FALSE); 	
					$this->save_cart();
					
					// but need to handle canceled as well.
					$auth['canceled'] = TRUE; 
					break;
				case "REJECTED": 
					$auth['error_message'] =  $this->lang('sage_rejected'); 
					$auth['declined']		=	TRUE; 
					$status =  "Status=INVALID\r\n";
					break; 
				case "AUTHENTICATED":
					$auth['error_message'] = $this->lang('sage_authenticated');
					$status =  "Status=OK\r\n";
					break;
				case "REGISTERED":
					$auth['error_message'] =  $this->lang('sage_registered');
					$status = "Status=OK\r\n";
					break;
				case "ERROR":
					$auth['error_message'] = $this->lang('sage_error');
					$status = "Status=INVALID\r\n";
					break;
				default:
					$auth['error_message'] =  $this->lang('sage_default');
			$auth['authorized'] 	=	FALSE; 
			$auth['transaction_id']	=	NULL;
			}

			/*
			if (!empty($post['ct_action']) && $post['ct_action'] == "create_token")
			{
				$token = new Cartthrob_token();
				$token->set_error_message( $auth['error_message']  );
			}
			*/ 
			
			$this->checkout_complete_offsite($auth, $order_id, 'stop_processing'); 
			@ob_clean();
			header("Content-type: text/plain");
			if ($status)
			{
				echo $status; 
			}
			else
			{
				echo "Status=INVALID\r\n";
			}
			if ($redirect_url)
			{
				echo $redirect_url; 
			}
			else
			{
			 	echo "RedirectURL=".$return_url."\r\n";
 			}
			// SAGE requires that we output this stuff. 
			@ob_flush();
			

			exit; 
			
		}
	}// END
	
	public function write_to_log($string, $close = FALSE)
	{
		return; 
		//$log_dir="/path/to/system/expressionengine/third_party/cartthrob/payment_gateways/sage_log";
		if (!isset($log_dir))
		{
			return; 
		}
		if (!isset($this->filep))
		{
			// write to a file for testing
			
			$timestamp=date("d-m-y--H-i-s-". rand(5, 100));
			$this->filep=fopen("$log_dir/$timestamp.post.txt","a");
			
		}
 		
		fwrite($this->filep, $string); 
		
		if ($close)
		{
			fclose($this->filep);
		}
	}
}
// END Class
