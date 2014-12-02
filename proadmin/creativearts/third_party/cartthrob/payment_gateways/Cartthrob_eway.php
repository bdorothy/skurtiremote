<?php 
class Cartthrob_eway extends Cartthrob_payment_gateway
{
	public $title = 'eway_title';
	public $eway_id = "87654321"; 
 	public $overview = 'eway_overview';
	public $language_file = TRUE;
 	public $settings = array(
		array(
			'name' =>  'eway_customer_id',
			'short_name' => 'customer_id', 
			'type' => 'text', 
			'default' => '87654321', 
		),
		array(
			'name' =>  'password',
			'short_name' => 'password', 
			'type' => 'text', 
			'default' => 'test123', 
		),
		array(
			'name' =>  'username',
			'short_name' => 'username', 
			'type' => 'text', 
			'default' => 'test@eway.com.au', 
		),
		
		
		array(
			'name' =>  'sandbox_eway_customer_id',
			'short_name' => 'sandbox_customer_id', 
			'type' => 'text', 
			'default' => '87654321', 
		),
		array(
			'name' =>  'sandbox_password',
			'short_name' => 'sandbox_password', 
			'type' => 'text', 
			'default' => 'test123', 
		),
		array(
			'name' =>  'sandbox_username',
			'short_name' => 'sandbox_username', 
			'type' => 'text', 
			'default' => 'test@eway.com.au', 
		),
		
		
		array(
			'name' => 'eway_payment_method',
			'short_name' => 'payment_method', 
			'type' => 'radio', 
			'default' => 'REAL-TIME', 
			'options' => array(
				'REAL-TIME'		    =>'REAL-TIME', 
				'REAL-TIME-CVN' 	=>'REAL-TIME-CVN', 
				'GEO-IP-ANTI-FRAUD'	=>'GEO-IP-ANTI-FRAUD'),
			
		),
		array(
			'name' => 'mode',
			'short_name' => 'test_mode', 
			'type' => 'radio', 
			'default' => 'test',
			'options' => array(
				'test' => 'test',
				'live' => 'live',
				'sandbox' => 'sandbox',
			)
		),
		array(
			'name'	=> 'test_response',
			'short_name'	=> 'test_response',
			'type' => 'select', 
			'default' => '00',
			'options' => array(
				'100' => 'approved',
				'114' => 'declined',
				'130' => 'failed',
				'use_total'=> 'use_total'
			)
		), 
		array(
			'name' =>  'sandbox_token_customer_id',
			'note'	=> 'sandbox_token_customer_id_note',
			'short_name' => 'token_customer_id', 
			'type' => 'text', 
			'default' => '', 
		),
		
	);
	
	public $required_fields = array(
		'first_name',
		'last_name',
		'address',
		'city',
		'zip',
		'credit_card_number',
		'expiration_year',
		'expiration_month',
		'CVV2'
	);
 
	public $fields = array(
		'first_name',
		'last_name',
		'address',
		'address2',
		'city',
		'zip',
		'country_code',
		'shipping_first_name',
		'shipping_last_name',
		'shipping_address',
		'shipping_address2',
		'shipping_city',
		'shipping_zip',
		'shipping_country_code',
		'phone',
		'email_address',
		'card_type',
		'credit_card_number',
		'expiration_month',
		'expiration_year',
		'CVV2'
 	);
		
 	public $hidden = array('description');

	public $card_types = NULL;
	private $username = "test@eway.com.au"; 
	private $password = "test123"; 
	
 	public function initialize()
	{
		if ($this->plugin_settings('test_mode') == "live")
		{
 			$this->token_host = "https://www.eway.com.au/gateway/ManagedPaymentService/managedCreditCardPayment.asmx?WSDL"; 
		}
		else
		{
			$this->token_host = "https://www.eway.com.au/gateway/ManagedPaymentService/test/managedcreditcardpayment.asmx?WSDL";
		}
		switch ($this->plugin_settings('payment_method'))
		{
			case "REAL-TIME":
				(($this->plugin_settings('test_mode') != 'live')? 
					$this->_host='https://www.eway.com.au/gateway/xmltest/testpage.asp': 
					$this->_host='https://www.eway.com.au/gateway/xmlpayment.asp');
					break;
			case "REAL-TIME-CVN":
				(($this->plugin_settings('test_mode') != 'live')? 
					$this->_host='https://www.eway.com.au/gateway_cvn/xmltest/testpage.asp': 
					$this->_host='https://www.eway.com.au/gateway_cvn/xmlpayment.asp');
					break;
			case "GEO-IP-ANTI-FRAUD":
				$this->required_fields[] = 'country_code'; 
				
				(($this->plugin_settings('test_mode') != 'live')? 
					$this->_host='https://www.eway.com.au/gateway_cvn/test/xmlbeagle_test.asp':
					$this->_host='https://www.eway.com.au/gateway_cvn/xmlbeagle.asp');
					break;
			default: 
				$this->_host = 'https://www.eway.com.au/gateway/xmltest/testpage.asp';
				break;
		}
		if ($this->plugin_settings('test_mode')== "test")
		{
 			$this->eway_id = "87654321";
		}
		elseif ($this->plugin_settings('test_mode')== "sandbox")
		{
			$this->eway_id = $this->plugin_settings('sandbox_customer_id'); 
			$this->username = $this->plugin_settings('sandbox_username'); 
			$this->password = $this->plugin_settings('sandbox_password'); 
		}
 		else
		{
			$this->eway_id = $this->plugin_settings('customer_id'); 
			$this->username = $this->plugin_settings('username'); 
			$this->password = $this->plugin_settings('password'); 
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
		// eWay processes with no decimal values. 
		$total = round($this->total()*100);
		
		if ($this->plugin_settings('test_mode')== "test")
		{
			if ($this->plugin_settings('test_response')!= "use_total")
			{
				$total = $this->plugin_settings('test_response');
			}
			$credit_card_number = "4444333322221111";
		}
		
		if (strlen($this->order('expiration_year') == 4))
		{
			$expiration_year = substr($this->order('expiration_year'), -2);
		}
		else
		{
			$expiration_year = str_pad($this->order('expiration_year'), 2, '0', STR_PAD_LEFT); 
		}
		
		$post_array = array(
			'ewayTotalAmount'					=> $total,
			'ewayCustomerLastName'				=> $this->order('last_name'),	
			'ewayCustomerFirstName'				=> $this->order('first_name'),
			'ewayCustomerEmail'					=> $this->order('email_address'),
			'ewayCustomerAddress'				=> $this->order('address')." ".$this->order('address2') ." ". $this->order('city'),	
			'ewayCustomerPostcode'				=> $this->order('zip'),	
			'ewayCustomerInvoiceDescription'	=> $this->order('description'),				
			'ewayCustomerInvoiceRef'			=> $this->order('entry_id'), 		
			'ewayCardHoldersName'				=> $this->order('first_name')." ".$this->order('last_name'),	
			'ewayCardNumber'					=> $credit_card_number,
			'ewayCardExpiryMonth'				=> str_pad($this->order('expiration_month'), 2, '0', STR_PAD_LEFT), 
			'ewayCardExpiryYear'				=> $expiration_year, 	
			'ewayTrxnNumber'					=> "",
			'ewayOption1'					    => "",
			'ewayOption2'					    => "",
			'ewayOption3'					    => "",
			'ewayCVN'                           => $this->order("CVV2")
		);
		if ($this->plugin_settings('payment_method')== "GEO-IP-ANTI-FRAUD")
		{
			$post_array['ewayCustomerBillingCountry'] = $this->alpha2_country_code($this->order('country_code'));
			$post_array['ewayCustomerIPAddress']	= $this->order('ip_address'); 
		}
		$data = "<ewaygateway><ewayCustomerID>" . $this->eway_id  . "</ewayCustomerID>";
		foreach($post_array as $key=>$value)
		{
			$value = str_replace("&", "and", $value); 
			$data .= "<{$key}>{$value}</{$key}>";
		}
		$data .= "</ewaygateway>";

		$connect = 	$this->curl_transaction($this->_host,$data); 
		
		$resp['authorized']					=	FALSE;
		$resp['error_message']				=	NULL;
		$resp['failed']						=	TRUE;
		$resp['declined']					=	FALSE;
		$resp['transaction_id'] 			=	NULL;
		
		if (!$connect)
		{
			$resp['failed']	 				= 	TRUE; 
			$resp['authorized']				=	FALSE;
			$resp['declined']				=	FALSE;
			$resp['error_message']			=	$this->lang('curl_gateway_failure');
			return $resp; 
		}
		$transaction = $this->xml_to_array($connect,'basic'); 
		
		$error = NULL; 
		if (!empty($transaction['ewayResponse']['ewayTrxnStatus']['data']))
		{
			if(strtolower($transaction['ewayResponse']['ewayTrxnStatus']['data'])=="false")
		  	{
				if (!empty($transaction['ewayResponse']['ewayTrxnStatus']['data']))
				{
					$error = $transaction['ewayResponse']['ewayTrxnError']['data'];
				}
				$resp['declined'] 				= TRUE;
				$resp['failed']					= FALSE;
				$resp['error_message'] 			= $this->lang('eway_transaction_error'). " ". $error;

			}
			elseif(strtolower($transaction['ewayResponse']['ewayTrxnStatus']['data'])=="true")
			{
				if (!empty($transaction['ewayAuthCode']))
				{
					$this->update_order(array('authorize_id' => $transaction['ewayAuthCode']));
				}
				
				$resp['declined']		   		 = FALSE;
				$resp['failed']			   		 = FALSE; 
				$resp['authorized']		   		 = TRUE;
				$resp['error_message']	   		 = NULL;
				$resp['transaction_id']    		 = (!empty($transaction['ewayResponse']['ewayTrxnNumber']['data']) ? $transaction['ewayResponse']['ewayTrxnNumber']['data'] : NULL);
			}
			else
			{
				$resp['authorized']				= FALSE;
				$resp['declined']				= FALSE;
				$resp['failed']					= TRUE;
				$resp['error_message'] 			= $this->lang('eway_invalid_response');
			}
		}			

		return $resp;
	}
	// END
 
	public function create_token($credit_card_number)
	{
		$token = new Cartthrob_token();
		
 		if ($this->plugin_settings('test_mode')== "test")
		{
			$credit_card_number = "4444333322221111";
		}
		///////////// COMMON ////////////////////
		require_once PATH_THIRD.'cartthrob/third_party/lib/nusoap/nusoap.php';
		
		if(is_callable('ini_set'))
		{
			ini_set("soap.wsdl_cache_enabled", "0");
		}	
 		// for future reference, here's a PHP method that might work without using nusoap
		// http://stackoverflow.com/questions/11394612/soapfault-exception-http-bad-request-in-eway
		$headers = '<eWAYHeader xmlns="https://www.eway.com.au/gateway/managedpayment" >'; 
		$headers .= "<eWAYCustomerID>" . (string) $this->eway_id . "</eWAYCustomerID>"; 
		$headers .= "<Username>" .$this->username . "</Username>"; 
		$headers .= "<Password>" .$this->password . "</Password>";
		$headers .= "</eWAYHeader>";
		
 		$client = new nusoap_client($this->token_host, 'wsdl');
		$err = $client->getError();
		if ( $err ) 
		{
 			$auth['authorized']	 	= FALSE; 
			$auth['declined'] 		= FALSE; 
			$auth['transaction_id']	= "";
			$auth['failed']			= TRUE; 
			$auth['error_message']	= $err;
			return $auth;
		}
			
		$client->setHeaders($headers);
 		///////////// END COMMON ////////////////////

		if (strlen($this->order('expiration_year')) == 4)
		{
			$expiration_year = substr($this->order('expiration_year'), -2);
		}
		else
		{
			$expiration_year = str_pad($this->order('expiration_year'), 2, '0', STR_PAD_LEFT); 
		}
		
		// Eway is ridiculous. They *require* the Title... and it can only be in certain formats. So here we go with a lot of needless stuff
		$custom_data = $this->order('custom_data'); 
		$title = 'Mr.'; 
		if (!empty($custom_data['customer_title']))
		{
			switch (trim(str_replace(".", "", strtolower($custom_data['customer_title']))))
			{
				case "ms":
					$title = "Ms.";
					break;
				case "mrs":
					$title = "Mrs"; 
					break;
				case "miss":
					$title = "Miss"; 
					break;
				case "dr": 
					$title = "Dr.";
					break;
				case "sir":
					$title = "Sir.";
					break;
				case "prof": 
					$title = "Prof.";
					break;
				default: $title="Mr."; 
			}
		}
	
		$request['Title']			= (string) $title; 
 		$request['FirstName'] 		= (string) $this->order('first_name') ; 
		$request['LastName'] 		= (string) $this->order('last_name'); 
		$request['Address'] 		= (string) $this->order('address')." ". $this->order('address2') ; 
		$request['Suburb']			= NULL; 
		$request['State'] 		    = (string) $this->order('state') ; 
		$request['Company']			= (string) $this->order('company'); 
		$request['PostCode'] 		= (string) $this->order('zip'); 
		$request['Country'] 		= (string) strtolower($this->alpha2_country_code($this->order('country_code')));
		$request['Email'] 		    = (string) $this->order('email_address') ; 
 		$request['Fax']				= NULL; 
		$request['Phone'] 		    = (string) $this->order('phone') ; 
 		$request['Mobile']			= NULL; 
		$request['CustomerRef'] 	= (string) $this->order('member_id'); 
		$request['JobDesc']			= NULL; 
		$request['Comments']		= NULL; 
		$request['URL']				= NULL;
		
		$request['CCNumber']		= (string) $credit_card_number;
		$request['CCNameOnCard']	= $this->order('first_name'). " ". $this->order('last_name');
		$request['CCExpiryMonth']	= (int) str_pad($this->order('expiration_month'), 2, '0', STR_PAD_LEFT); // wsdl says this should be int... but not possible if format is 2 chars with a leading zero
		$request['CCExpiryYear']	= (int) $expiration_year;  // wsdl says this should be int... but not possible if format is 2 chars with a leading zero
		
		// might need to just dump the args, and send the $request in the call. worked for ProcessPayment

	    $result = $client->call('CreateCustomer', $request, 
					$namespace='https://www.eway.com.au/gateway/managedpayment', 			
					$soapAction='https://www.eway.com.au/gateway/managedpayment/CreateCustomer'
					);
 
		//// debug
  		# echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
		# echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
		# echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
		# exit;
					
 		if ($client->fault) 
		{
			$error_message = (string) $client->faultstring;
 			/*
			// @TODO convert text to lang file
			@ob_start();
			echo '<h2>Error: The request contains an invalid SOAP body</h2><pre>'; print_r($result); echo '</pre>';
			$error_message = @ob_get_contents();
			@ob_end_clean();
			*/
			
			$token->set_error_message( $error_message ); 
			return $token;
		} 
		else 
		{
 			$err = $client->getError();
			
			if ($err && empty($result['CreateCustomerResult'])) 
			{
				$token->set_error_message( (string) $err ); 
				return $token;
 			}
			else 
			{
				$token->set_token( (string) $result['CreateCustomerResult']); 
				$token->set_customer_id( (string) $result['CreateCustomerResult']); 
				return $token; 
  			}
		}


  		return $token;
 		
	}
	public function charge_token($token, $customer_id)
	{
		if ($this->plugin_settings('test_mode')== "test")
		{
			$customer_id = "9876543211000"; 
		}
		if ($this->plugin_settings('test_mode') =="sandbox")
		{
			if ( $this->plugin_settings('token_customer_id') )
			{
				// token test customer id
				$customer_id = $this->plugin_settings('token_customer_id');
			}
		}
		///////////// COMMON ////////////////////
		$auth['authorized']	 	= FALSE; 
		$auth['declined'] 		= FALSE; 
		$auth['transaction_id']	= "";
		$auth['failed']			= TRUE; 
		$auth['error_message']	= "";
		
		require_once PATH_THIRD.'cartthrob/third_party/lib/nusoap/nusoap.php';
		
		if(is_callable('ini_set'))
		{
			ini_set("soap.wsdl_cache_enabled", "0");
		}	
 
		$headers = '<eWAYHeader xmlns="https://www.eway.com.au/gateway/managedpayment" >'; 
		$headers .= "<eWAYCustomerID>" .  (string) $this->eway_id . "</eWAYCustomerID>"; 
		$headers .= "<Username>" .$this->username . "</Username>"; 
		$headers .= "<Password>" .$this->password . "</Password>";
		$headers .= "</eWAYHeader>";
		
 		$client = new nusoap_client($this->token_host, 'wsdl');
		$err = $client->getError();
		if ( $err ) 
		{
 			$auth['authorized']	 	= FALSE; 
			$auth['declined'] 		= FALSE; 
			$auth['transaction_id']	= "";
			$auth['failed']			= TRUE; 
			$auth['error_message']	= $err;
			return $auth;
		}
			
		$client->setHeaders($headers);
		
		///////////// END COMMON ////////////////////
 		
		$request['managedCustomerID']	= (string) $customer_id;
		$request['amount']				= (int) round($this->order('total')*100); 
		$request['invoiceReference']	= $this->order('entry_id'); 
		$request['invoiceDescription']	= NULL; 
		
 
	    $result = $client->call('ProcessPayment', $request, 
					$namespace='https://www.eway.com.au/gateway/managedpayment', 			
					$soapAction='https://www.eway.com.au/gateway/managedpayment/ProcessPayment'
					);
		 
		$response = (!empty($result['ewayResponse']) ? $result['ewayResponse'] : NULL); 
		if (empty($response) ||  $client->fault) 
		{
 			// @TODO lang
			@ob_start();
			echo '<h2>Error: The request contains an invalid SOAP body</h2><pre>'; print_r($result); echo '</pre>';
			$buffer = @ob_get_contents();
			@ob_end_clean();
 				
			$auth['authorized']	 	= FALSE; 
			$auth['declined'] 		= FALSE; 
			$auth['transaction_id']	= "";
			$auth['failed']			= TRUE; 
			$auth['error_message']	= $buffer;
		} 
		else 
		{
			
 			$err = $client->getError();
			
 			if (($err && empty($response['ewayTrxnNumber'])) || (!empty($response['ewayTrxnError']) && strpos($response['ewayTrxnError'], "00") === FALSE) )
			{
 				$auth['authorized']	 	= FALSE; 
				$auth['declined'] 		= FALSE; 
				$auth['transaction_id']	= "";
				$auth['failed']			= TRUE; 
				$auth['error_message']	= (string) $err. (string) $response['ewayTrxnError'];
 			}
			else 
			{
				$auth['authorized']	 	= TRUE; 
				$auth['declined'] 		= FALSE; 
				$auth['transaction_id']	= (string) $response['ewayTrxnNumber']; 
				$auth['failed']			= FALSE; 
				$auth['error_message']	= "";
  			}
 
		}
 		#echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
	#	echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
	#	echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
	#	exit; 

  		return $auth;
	}
}
// END Class