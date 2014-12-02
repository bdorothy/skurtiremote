<?php

class Cartthrob_sage extends Cartthrob_payment_gateway
{
	public $title = 'sage_title';
	public $affiliate = 'sage_affiliate'; 
	// @TODO add notes about extload when using subs
	public $overview = 'sage_overview';
	public $settings = array(
		array(
			'name' => 'mode',
			'short_name' => 'mode', 
			'type' => 'radio',
			'default' => "test",
			'options' => array(
				"simulator" => 'simulator', 
				"test" =>'test', 
				"live" => 'live'
				)
		),
		array(
			'name' => 'sage_vendor_name',
			'short_name' => 'vendor_name', 
			'type' => 'text',
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
		'card_type'            ,
		'issue_number'         ,
		'credit_card_number'   ,
		'CVV2'                 ,
		'expiration_month'     ,
		'expiration_year'      ,
		'begin_month'          ,
		'begin_year'           ,
		); 
	
	// description and currency_code are also used by this gateway
 
	public function initialize()
	{
		if ($this->plugin_settings('mode') == "simulator")
		{
			$this->_host = 'https://test.sagepay.com/Simulator/VSPDirectGateway.asp'; 
			$this->_3dhost = "https://test.sagepay.com/Simulator/VSPDirectCallback.asp"; 
			// pretty sure we can't do token requests in simulator
			#$this->tokenhost = "https://test.sagepay.com/Simulator/DirectToken.asp"; 
		}
		elseif ($this->plugin_settings('mode') == "live")
		{
			$this->_host = 'https://live.sagepay.com/gateway/service/vspdirect-register.vsp'; 
			$this->_3dhost = "https://live.sagepay.com/gateway/service/direct3dcallback.vsp"; 
			$this->tokenhost = "https://live.sagepay.com/gateway/service/directtoken.vsp"; 
			$this->tokenrebillhost = "https://live.sagepay.com/gateway/service/vspdirect-register.vsp"; 
		}
		else
		{
			$this->_host = 'https://test.sagepay.com/gateway/service/vspdirect-register.vsp'; 
			$this->_3dhost = "https://test.sagepay.com/gateway/service/direct3dcallback.vsp"; 
			$this->tokenhost = "https://test.sagepay.com/gateway/service/directtoken.vsp"; 
			$this->tokenrebillhost = "https://test.sagepay.com/gateway/service/vspdirect-register.vsp"; 
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
			'Currency'					=> ( $this->order('currency_code')?  $this->order('currency_code') : "GBP"),  
			'Description'				=>  ($this->order('description') ? $this->order('description') : "Purchase from ".$this->order('site_name')),
			'CardHolder'				=> $this->order('first_name') . " ". $this->order('last_name'), 
			'CardNumber'				=> $credit_card_number,
			'StartDate'					=>  ($this->order('begin_month') ? str_pad($this->order('begin_month'), 2, '0', STR_PAD_LEFT) . $this->year_2($this->order('begin_year')): ""),
			'ExpiryDate'				=> str_pad($this->order('expiration_month'), 2, '0', STR_PAD_LEFT) . $this->year_2($this->order('expiration_year')),
			'IssueNumber'				=> $this->order('issue_number'),
			'CV2'						=> $this->order('CVV2'),
			'CardType'					=> $card_type,
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

		); 
		
		// We don't want to pass the state data to Sage unless it has 2 characters and is a us state. They don't accept any non-us state values
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
		
		$data = 	$this->data_array_to_string($post_array);
		$connect = 	$this->curl_transaction($this->_host,$data); 
		
		if (!$connect)
		{
			$resp['failed']			= TRUE;
			$resp['authorized']		= FALSE;
			$resp['declined']		= FALSE;
			$resp['error_message']	= $this->lang('curl_gateway_failure'); 
			
			return $resp; 
		}
 
		return $this->handle_response( $this->sage_string_split($connect) ); 
		
	}
	public function create_token($credit_card_number)
	{
		$token = new Cartthrob_token();
		
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
		
		$post_array = array(
			'VPSProtocol' 				=> "2.23",
			'TxType'					=> 'TOKEN',
			'Vendor'					=> $this->plugin_settings('vendor_name'),
 			'Currency'					=> ( $this->order('currency_code')?  $this->order('currency_code') : "GBP"),  
			'CardHolder'				=> $this->order('first_name') . " ". $this->order('last_name'), 
			'CardNumber'				=> $credit_card_number,
			'StartDate'					=>  ($this->order('begin_month') ? str_pad($this->order('begin_month'), 2, '0', STR_PAD_LEFT) . $this->year_2($this->order('begin_year')): ""),
			'ExpiryDate'				=> str_pad($this->order('expiration_month'), 2, '0', STR_PAD_LEFT) . $this->year_2($this->order('expiration_year')),
			'IssueNumber'				=> $this->order('issue_number'),
			'CV2'						=> $this->order('CVV2'),
			'CardType'					=> $card_type,
			
 		);	
		
		$data = 	$this->data_array_to_string($post_array);
		$connect = 	$this->curl_transaction($this->tokenhost,$data); 
		
		if (!$connect)
		{
			return $token->set_error_message(  $this->lang('bad_token_response')  ); 
		}
 	
		$transaction = $this->sage_string_split($connect); 
 	
		if (!empty($transaction['Status']))
		{
			if ("OK" == strtoupper($transaction['Status']))
			{
				return $token->set_token( $transaction['Token'] ); 
				
			}
		}
		return $token->set_error_message(  $this->lang('bad_token_response')  ); 

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
		
		$transaction = $this->sage_string_split($connect); 

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
	function sage_string_split($connect)
	{
		$transaction = array(); 
		$array = explode("\r\n", $connect);
		$i = 0;
		while ($i < count($array)) {
			$b = explode("=", $array[$i], 2); 

			if ( ! isset($b[1]))
			{
				$b[1] = '';
			}
			$no_space_key=rtrim(htmlspecialchars(urldecode($b[0])));
			$transaction[$no_space_key] =  $b[1] ;
			$i++;
		}
		return $transaction; 
	}	
	function handle_response($transaction)
	{
		$resp = array(
			 'authorized'		=>	FALSE,
			 'error_message'	=>	NULL,
			 'failed'			=>	TRUE,
			 'declined'			=>	FALSE,
			 'transaction_id' 	=>	NULL,
			); 

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
			elseif ("3DAUTH" == strtoupper($transaction['Status']))
			{
				
				$auth = array(
					'authorized' 	=> FALSE,
					'error_message'	=> NULL,
					'failed'		=> FALSE,
					'declined'		=> FALSE,
					'processing'	=> TRUE,
					);
				if (!empty($transaction['VPSTxId']))
				{
					$auth['transaction_id'] = trim($transaction['VPSTxId'], "{}"); 
				}
				$this->gateway_order_update($auth, $this->order('entry_id')); 	
				
				// JUMP PAGE!
	 			echo "<html>
					<head>
						<script type='text/javascript'>
							window.onload = function(){ document.forms[0].submit(); };
						</script>
				</head><body>";
				// hiding contents from JS users.
				echo "<script type='text/javascript'>document.write('<div style=\'display:none\'>');</script>";
				echo "<form name='jump' id='jump' method='POST' action='".$transaction['ACSURL']."' >"; 
				echo "<input type='hidden' name='MD' value='{$transaction['MD']}' />";
				echo "<input type='hidden' name='TermUrl' value='".$this->get_notify_url(ucfirst(get_class($this)),'payment_notification')."' />";
// Might need to be TermURL (not sure)
				echo "<input type='hidden' name='PaReq' value='{$transaction['PAReq']}' />";
				echo "<h1>".$this->lang('jump_header')."</h1>";
				echo "<p>".$this->lang('jump_alert')."</p>";  
				echo "<input type='submit' value='".$this->lang('jump_finish')."' />"; 
				echo "</form>"; 
				echo "</body></html>"; 
				exit; 

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
				if (array_key_exists('3DSecureStatus',$transaction) && "NOTCHECKED" !=  strtoupper($transaction['3DSecureStatus']))
				{
					$error_message .= $this->lang('sage_3dsecure'); 
				}

				$resp = array(
					 'authorized'		=>	FALSE,
					 'error_message'	=>	$error_message,
					 'failed'			=>	TRUE,
					 'declined'			=>	FALSE,
					 'transaction_id' 	=> 	NULL
					);
			}
			return $resp;
			
		}
		$resp = array(
			 'authorized'		=>	FALSE,
			 'error_message'	=>	$this->lang('sage_contact_admin'),
			 'failed'			=>	FALSE,
			 'declined'			=>	FALSE,
			 'transaction_id' 	=> 	NULL
			);

		return $resp;
	}
 	function payment_notification($post)
	{
		$sage_3d_post_array = array(
			'PARes'  	=> $post['PaRes'],
			'MD'		=> $post['MD']
 		);
		$data 		= $this->data_array_to_string($sage_3d_post_array);
	
		$connect 	= $this->curl_transaction($this->_3dhost, $data);
		 
		if (!$connect)
		{
			$resp['failed']			= TRUE;
			$resp['authorized']		= FALSE;
			$resp['declined']		= FALSE;
			$resp['error_message']	= $this->lang('sage_failed');

			return $resp;	
		} 
		$authentication_results =  $this->sage_string_split($connect, "\r\n");
		//$authentication_results =  $this->split_url_string($connect, "\r\n");
		$resp = $this->handle_response($authentication_results); 
		
		// $this->gateway_order_update($resp,$this->order('entry_id'), $this->order('return'));
		$this->checkout_complete_offsite($resp,$this->order('entry_id'));
	}
	
 
}
// END Class