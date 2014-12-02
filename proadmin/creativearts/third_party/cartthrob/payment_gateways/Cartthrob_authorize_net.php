<?php 
class Cartthrob_authorize_net extends Cartthrob_payment_gateway
{
	public $title = 'authorize_net_title';
	public $affiliate = 'authorize_net_affiliate'; 
	public $overview = 'authorize_net_overview';
	public $language_file = TRUE;
	public $settings = array(
		array(
			'name' => 'authorize_net_settings_api_login',
			'short_name' => 'api_login',
			'type' => 'text'
		),
		array(
			'name' => 'authorize_net_settings_trans_key',
			'short_name' => 'transaction_key',
			'type' => 'text'
		),
		array(
			'name' => 'authorize_net_settings_email_customer',
			'short_name' => 'email_customer',
			'type' => 'radio',
			'default' => "no",
			'options' => array(
				"no"	=> "no",
				"yes"	=> "yes"
			)
		),
		array(
			'name' => "mode",
			'short_name' => 'mode',
			'type' => 'radio',
			'default' => "test",
			'options' => array(
				"test"	=> "test",
				"live"	=> "live",
				"developer" => "developer"
			)
		),
		array(
			'name' => 'authorize_net_settings_dev_api_login',
			'short_name' => 'dev_api_login',
			'type' => 'text'
		),
		array(
			'name' => 'authorize_net_settings_dev_trans_key',
			'short_name' => 'dev_transaction_key',
			'type' => 'text'
		), 
		array(
			'name' => 'authorize_net_advanced_settings_header',
			'short_name' => 'advanced_settings_header',
			'type' => 'header',
		),
		array(
			'name' => "authorize_net_hash_value",
			'short_name' => 'hash_value',
			'type' => 'text'
		),
		array(
			'name' => "authorize_net_authcapture",
			'short_name' => 'transaction_settings',
			'default'	=> 'AUTH_CAPTURE',  // set to AUTH_CAPTURE for money capturing transactions
			'type' => 'radio',
			'options' => array(
				'AUTH_CAPTURE'	=> 'authorize_net_auth_charge',
				'AUTH_ONLY'	=> 'authorize_net_auth_only',
				)
		),
		
		array(
			'name' => "authorize_net_perform_additional_validation_when_creating_tokens",
			'short_name' => 'perform_additional_validation_when_creating_tokens',
			'note'	=> 'authorize_net_perform_additional_validation_when_creating_tokens_note',
			'default'	=> 'no',  // set to AUTH_CAPTURE for money capturing transactions
			'type' => 'radio',
			'options' => array(
				'no'	=> 'no',
				'yes'	=> 'yes',
				)
		),
		
		
	);
	
	public $required_fields = array(
		'credit_card_number',
		'expiration_year',
		'expiration_month'
	);
	
	public $fields = array(
		'first_name',
		'last_name',
		'address',
		'address2',
		'city',
		'state',
		'zip',
		'phone',
		'email_address',
		'shipping_first_name',
		'shipping_last_name',
		'shipping_address',
		'shipping_address2',
		'shipping_city',
		'shipping_state',
		'shipping_zip',
		'card_type',
		'credit_card_number',
		'CVV2',
		'expiration_year',
		'expiration_month'
	);
	public $recurrent_billing_delete = array(
		
	); 
	public $recurrent_billing_update = array(
		'credit_card_number',
		'expiration_year',
		'expiration_month'
	); 
	public $hidden = array();
	public $card_types = NULL;
	
	private $tax = NULL; 
	private $shippng = NULL;
	private $amount = NULL; 
	
	public function initialize()
	{
		//  changing the overview dynamically to include the notification link
		$this->overview = $this->lang('authorize_net_silent_post'). " <a href='". $this->response_script(ucfirst(get_class($this)))."'>".$this->response_script(ucfirst(get_class($this)))."</a>";
		
		$this->_x_type 					= ($this->plugin_settings('transaction_settings') ? $this->plugin_settings('transaction_settings') : 'AUTH_CAPTURE' );
 		$this->_x_test_request         	= "TRUE";

		$this->_host					= "https://secure.authorize.net/gateway/transact.dll";
		$this->_arb_host				= "https://api.authorize.net/xml/v1/request.api";
		$this->xmlns 					= "https://api.authorize.net/"; 
		
		$this->api_login = $this->plugin_settings('api_login');
		$this->transaction_key = $this->plugin_settings('transaction_key');
		
		if ( ! defined('AUTHORIZENET_SANDBOX'))
		{
			define('AUTHORIZENET_SANDBOX', $this->plugin_settings('mode') === 'developer');
		}
		
		if ($this->plugin_settings('mode') == 'developer') 
		{
			$this->_host								= "https://test.authorize.net/gateway/transact.dll";
			$this->_arb_host							= "https://apitest.authorize.net/xml/v1/request.api";
 			
			$this->api_login = $this->plugin_settings('dev_api_login');
			$this->transaction_key = $this->plugin_settings('dev_transaction_key');
			$this->_x_test_request         	= "FALSE";
			
		}
		elseif ($this->plugin_settings('mode') == "test") 
		{
			$this->_x_test_request         	= "TRUE";
 		}
		else
		{
			$this->_host					= "https://secure.authorize.net/gateway/transact.dll";
			$this->_x_test_request         	= "FALSE";
		}
		
		if ( ! defined('AUTHORIZENET_API_LOGIN_ID'))
		{
			define("AUTHORIZENET_API_LOGIN_ID", $this->api_login);
		}
		if ( ! defined('AUTHORIZENET_TRANSACTION_KEY'))
		{
			define("AUTHORIZENET_TRANSACTION_KEY", $this->transaction_key);
		}
		if ( ! defined('AUTHORIZENET_SANDBOX'))
		{
			if (empty($this->_x_test_request)  || $this->_x_test_request == "FALSE" )
			{
		 		define("AUTHORIZENET_SANDBOX", false);
			}
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
		$tax = $this->order('tax'); 
		$shipping = $this->order('shipping'); 
		$amount = number_format($this->total(),2,'.',''); 
	
		// setting override values if this charge is being used as an AUTH by another function.
		if (isset($this->tax))
		{
			$tax = number_format($this->tax,2,'.','');
		}
		if (isset($this->shipping))
		{
			$shipping = number_format($this->shipping,2,'.','');
		}
		if (isset($this->amount))
		{
			$amount = number_format($this->amount,2,'.','');
		}
		
 		$post_array = array(
			"x_login"         				=> $this->api_login,
			"x_tran_key"           			=> $this->transaction_key,
			"x_version"           	 		=> "3.1",
			"x_test_request"    		   	=> $this->_x_test_request,
			"x_delim_data"    	    	 	=> "TRUE",
			"x_delim_char"                => ",",
		    "x_encap_char"                => "|", 
			"x_relay_response"				=> "FALSE",
			"x_first_name"       	     	=> $this->order('first_name'),
			"x_last_name"       	     	=> $this->order('last_name'),
			"x_address"      		      	=> $this->order('address')." ".$this->order('address2'),
			"x_city"            	    	=> $this->order('city'),
			"x_state"              		  	=> $this->order('state'),
			"x_description"					=> $this->order('description'),
			"x_zip"            		    	=> $this->order('zip'),
			"x_country"            		   	=> $this->alpha2_country_code(($this->order('country_code') ? $this->order('country_code') : 'USA')),
			'x_ship_to_first_name'			=> ($this->order('shipping_first_name')) ? $this->order('shipping_first_name') : $this->order('first_name'),
			'x_ship_to_last_name'			=> ($this->order('shipping_last_name')) ? $this->order('shipping_last_name') : $this->order('last_name'),
			'x_ship_to_address'				=> ($this->order('shipping_address')) ? $this->order('shipping_address').' '.$this->order('shipping_address2') : $this->order('address').' '.$this->order('address2'),
			'x_ship_to_city'				=> ($this->order('shipping_city')) ? $this->order('shipping_city') : $this->order('city'),
			'x_ship_to_state'				=> ($this->order('shipping_state')) ? $this->order('shipping_state') : $this->order('state'),
			'x_ship_to_zip'					=> ($this->order('shipping_zip')) ? $this->order('shipping_zip') : $this->order('zip'),
			"x_phone"          		      	=> $this->order('phone'),
			"x_email"          		      	=> $this->order('email_address'),
			"x_cust_id"          		   	=> $this->order('member_id'),
			"x_invoice_num"					=> time().strtoupper(substr($this->order('last_name'), 0, 3)),
			"x_company"						=> $this->order('company'),
			"x_email_customer"    		 	=> ($this->plugin_settings('email_customer') == "yes") ? "TRUE" : "FALSE",
			"x_amount"               	 	=> number_format($this->total(),2,'.',''),
			"x_method"               	 	=> "CC",
			"x_type"                 		=> $this->_x_type,  // set to AUTH_CAPTURE for money capturing transactions
			"x_card_num"             		=> $credit_card_number,
			"x_card_code"             		=> $this->order('CVV2'),
			"x_exp_date"             		=> str_pad($this->order('expiration_month'), 2, '0', STR_PAD_LEFT).'/'.$this->year_2($this->order('expiration_year')),
			"x_tax"							=> $tax,
			"x_freight"						=> $shipping,
		);
	
		reset($post_array);
		$data='';
		while (list ($key, $val) = each($post_array)) 
		{
			$data .= $key . "=" . urlencode($val) . "&";
		}
		
		if (!$this->amount)
		{
			// SENDING ORDER DATA TO AUTHORIZE.NET
			$line_item = array();

	 		if ($this->order('items'))
			{
				foreach ($this->order('items') as $row_id => $item)
				{
					$basket = ""; 

					if (!isset($count))
					{
						$count=1;
					}
					$count++;
					if ($count > 30)
					{
						continue; 
					}

					$title = $this->strip_punctuation( $item['title'] ); 

					$title = substr($title, 0, 30); 

					while (strlen(urlencode(htmlspecialchars($title))) > 30)
					{
						$title = substr($title, 0, -1); 
					}
					if (empty($item['entry_id']))
					{
						$item['entry_id'] = "000";
					}
					$basket .= $item['entry_id']."<|>"; 
					$basket .= urlencode(htmlspecialchars($title))."<|>";
					$basket .= $item['entry_id']."<|>";
					$basket .= abs($item['quantity'])."<|>";
					$basket .= number_format(abs($item['price']),2,'.','')."<|>"; 
					$basket .="Y";

					if (! empty($title))
					{
						$line_item[] = $basket; 
					}
				}
			}


			// ADDING TO EXISTING DATA STRING. 
			while (list($key, $val) = each($line_item)) 
			{
				$data .= 'x_line_item=' .$val.'&';
			}
		}

		
		$data .= 'x_duty=0';
		
		$auth['authorized']	 	= FALSE; 
		$auth['declined'] 		= FALSE; 
		$auth['transaction_id']	= NULL;
		$auth['failed']			= TRUE; 
		$auth['error_message']	= "";
		
 		$ch = curl_init($this->_host);
		curl_setopt($ch, CURLOPT_HEADER, 0); 		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // you can choose to delete these lines if you'd like this isn't needed on some servers
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		$connect = curl_exec($ch); 
 		
  		if (!$connect)
		{
 			$auth['error_message'] = $this->lang('curl_gateway_failure')." ".  curl_error($ch);
 
			return $auth; 
		}
		
		curl_close ($ch);
		$response = $this->csv2array($connect, ",", "|"); 
		
		switch($response[0])
		{
			case 1: 
				$auth['authorized'] 	= TRUE; 
				$auth['failed']			= FALSE; 
				$auth['transaction_id'] = @$response[6];
 	
				if (!empty($response[4]))
				{
					$this->update_order(array('authorization_id' => $response[4]));
				}
			break;
			case 2:
				$auth['authorized']	 	= FALSE; 
				$auth['declined'] 		= TRUE; 
				$auth['transaction_id']	= NULL;
				$auth['failed']			= FALSE; 
				$auth['error_message']	= @$response[3];
			break;
			case 3: 
				$auth['authorized']	 	= FALSE; 
				$auth['declined'] 		= FALSE; 
				$auth['transaction_id']	= NULL;
				$auth['failed']			= TRUE; 
				$auth['error_message']	= @$response[3];
				break;
			case 4: 
				if ($response[2] == "252" || $response[2]== "253")
				{
					$auth['authorized'] 	= FALSE;
					$auth['processing']		= TRUE;  
					$auth['failed']			= FALSE; 
					$auth['error_message']	= @$response[3];
					$auth['transaction_id'] = @$response[6];	
					if (!empty($response[4]))
					{
						$this->update_order(array('authorization_id' => $response[4]));
					}
				}
				else
				{
					$auth['authorized']	 	= FALSE; 
					$auth['declined'] 		= FALSE; 
					$auth['transaction_id']	= NULL;
					$auth['failed']			= TRUE; 
					$auth['error_message']	= @$response[3];
				}
			break;
			default:
				$auth['failed']			= TRUE; 
				$auth['error_message']	= $this->lang('authorize_net_error_1') . $response[3];
		}
		return $auth;
	}
	// END Auth
	
	function csv2array($input,$delimiter=',',$enclosure='"',$escape='\\')
	{ 
	    $fields = explode($enclosure.$delimiter.$enclosure,substr($input,1,-1)); 
	
	    foreach ($fields as $key=>$value) 
		{
	        $fields[$key]=str_replace($escape.$enclosure,$enclosure,$value); 
		}
	    return($fields); 
	}
	
	public function refund($transaction_id, $amount, $card_num, $order_id=NULL)
	{
		if (!$card_num)
		{
			return array(
				'authorized' => FALSE,
				'failed' => TRUE,
				'declined' => FALSE,
				// @TODO lang
				'error_message' => "The refund can't be processed at this time. Because this transaction is not a credit card transaction refunds must be handled manually.",
				'transaction_id' => $transaction_id
			);
		}
		if (!$amount)
		{
			return array(
				'authorized' => FALSE,
				'failed' => TRUE,
				'declined' => FALSE,
				// @TODO lang
				'error_message' => "You must submit a refund amount greater than zero.",
				'transaction_id' => $transaction_id
			);
		}
		/*
		• Transaction must be successfully processed. If not processed yet, use a VOID instead.
		• The amount being requested for refund must be less than or equal to the original settled amount.
		• The sum of multiple  Refund transactions must be less than or equal to the original settled amount.
		• The transaction is submitted within 120 days of the settlement date of the original transaction.

		$response->response_code;
		$response->response_subcode;
		$response->response_reason_code;
		$response->transaction_id;
		*/

		require_once $this->library_path().'/authorize_net/AuthorizeNet.php';
		
		$amount = number_format($amount,2,'.','');
		
		$request = new AuthorizeNetAIM($this->api_login, $this->transaction_key);
		
		$request->setField('first_name', $this->order('first_name')); 
		$request->setField('last_name', $this->order('last_name')); 
		$request->setField('city', $this->order('city')); 
		$request->setField('state', $this->order('state')); 
		$request->setField('zip', $this->order('first_name')); 
		$request->setField('country', $this->order('country')); 
		$request->setField('address', $this->order('address')." ".$this->order('address2')); 
		$request->setField('phone', $this->order('phone')); 
		
		$response = $request->credit($transaction_id, $amount, $card_num); 
		
		if ($response->approved) 
		{
			return array(
				'authorized' => TRUE,
				'failed' => FALSE,
				'declined' => FALSE,
				'error_message' => NULL,
				'transaction_id' => $response->transaction_id,
			);
		}
 		else
		{
			/*
			// @NOTE void would be great... but these are partial transactions (possibly) so we can't do a void. Authorize.net suggests to try a void first, but we're assuming a partial. 
				$void = new AuthorizeNetAIM;
		        $void_response = $void->void($transaction_id);
		*/

			if (isset($response->response_reason_code))
			{
				return array(
					'authorized' => FALSE,
					'failed' => TRUE,
					'declined' => FALSE,
					'error_message' => $response->response_reason_text. ": ". $response->response_reason_text,
					'transaction_id' => NULL
				);
			}
			else
			{
			 	return array(
					'authorized' => TRUE,
					'failed' => TRUE,
					'declined' => FALSE,
					'error_message' => "unknown error",
					'transaction_id' => NULL
				);
			}
		}
	}
	
	public function create_token($credit_card_number)
	{
		$token = new Cartthrob_token();
		
		if ($this->plugin_settings('perform_additional_validation_when_creating_tokens') =="yes")
		{
			// to perform an additional authorization on a credit card of 1 cent 
		// when creating a customer profile, the validation mode set to LiveMode should generate an "auth" on the card
		// however, this may not be sufficient for a full CV checking authorization (based on the way some banks respond to validation requests)
			// the following will run a full authorization on a card, and the customer will see this authorization transaction on their account statement until it is released. (typically a few days)
		$this->_x_type = "AUTH_ONLY"; 
			$this->amount = "0.10"; // ten cents to account for international transactions where it might be possible that 1 cent would be a fractional value of the local currency and fail a bank authorization based on that alone.  
		$this->tax = "0.00"; 
		$this->shipping = "0.00";
		$authorization = (array) $this->charge($credit_card_number); 
		if (!empty($authorization['failed']) || !empty($authorization['declined']))
		{
			if (!empty($authorization['error_message']))
			{
				$token->set_error_message(  (string) $authorization['error_message']  ); 
			}
			else
			{
				$token->set_error_message(  'transaction failed'  ); 
			}
			
			return $token; 
		}
		
			$this->_x_type 					= ($this->plugin_settings('transaction_settings') ? $this->plugin_settings('transaction_settings') : 'AUTH_CAPTURE' );
		}
		require_once $this->library_path().'/authorize_net/AuthorizeNet.php';
 		
		$request = new AuthorizeNetCIM;
		// Create new customer profile
		
		// NOTE authorize.net's SDK uses camelCasing. It's annoying, and less readable (imho), but I'm using it to be expedient.
		$customerProfile  = new AuthorizeNetCustomer;
		$customerProfile->description       	= $this->order('first_name'). " ". $this->order('last_name');
		$customerProfile->merchantCustomerId	= $this->order('member_id');
		$customerProfile->email             	= $this->order('email_address');
		
		// Add address.
		$address = new AuthorizeNetAddress;
		$address->firstName = $this->order('shipping_first_name'); 
		$address->lastName = $this->order('shipping_last_name');
		$address->company = $this->order('shipping_company');
		$address->address = $this->order('shipping_address');
		$address->city = $this->order('shipping_city'); 
		$address->state = $this->order('shipping_state'); 
		$address->zip = $this->order('shipping_zip'); 
		$address->country = $this->order('shipping_country_code'); 
		$address->phoneNumber = $this->order('phone'); 
		$customerProfile->shipToList[] = $address;
		
		// Next, create an AuthorizeNetCIM object:
		$request = new AuthorizeNetCIM;
		// Finally, call the createCustomerProfile method and pass in your customer object:
		
		$response = $request->createCustomerProfile($customerProfile, $validationMode = "none");// set to "none" because it will error out if there is not payment validation also being performed

		$customerProfileId = NULL; 
		if ($response->isOk()) 
		{
			$customerProfileId = $response->getCustomerProfileId();
			
			/*
				The response object also stores the XML response as a SimpleXml element
				which you can access like so:

				$new_customer_id = $response->xml->customerProfileId
				
				You can also run xpath queries against the result:

				$array = $response->xpath('customerProfileId');
				$new_customer_id = $array[0];
			
			*/
			
		}
 		else
		{
			// Authorize.net errors out if this customer has created a profile in the past. I won't create a duplicate
			if (
				isset($response->xml->messages->message->text) && 
				strpos((string) $response->xml->messages->message->text, "A duplicate record with ID ")!==FALSE )
			{
				$msg = (string) $response->xml->messages->message->text; 
				$msg = str_replace("A duplicate record with ID ", "", $msg); 
				$customerProfileId  = str_replace(" already exists.", "", $msg); 
			}
		}
		
		if (!$customerProfileId)
		{
			if (isset($response->xml->messages->message->text))
			{
				$token->set_error_message(  (string) $response->xml->messages->message->text  ); 
			}
			else
			{
				$token->set_error_message(  $this->lang('authorize_net_bad_token_response')   ); 
			}
			return $token;
		}
		
		$paymentProfile = new AuthorizeNetPaymentProfile;
		$paymentProfile->customerType = "individual";
		$paymentProfile->payment->creditCard->cardNumber = $credit_card_number;
		$paymentProfile->payment->creditCard->expirationDate = $this->year_4($this->order('expiration_year')). "-" . $this->order('expiration_month');
		$paymentProfile->billTo->firstName = $this->order('first_name'); 
		$paymentProfile->billTo->lastName = $this->order('last_name');
		$paymentProfile->billTo->company = $this->order('company');
		$paymentProfile->billTo->address = $this->order('address');
		$paymentProfile->billTo->city = $this->order('city'); 
		$paymentProfile->billTo->state = $this->order('state'); 
		$paymentProfile->billTo->zip = $this->order('zip'); 
		$paymentProfile->billTo->country = $this->order('country_code'); 
		$paymentProfile->billTo->phoneNumber = $this->order('phone'); 

		$request = new AuthorizeNetCIM;
		$response = $request->createCustomerPaymentProfile($customerProfileId, $paymentProfile, $validation_mode = "liveMode");

		if ($response->isOk()) 
		{
			//$paymentProfileId = $response->getCustomerPaymentProfileIds();
			$paymentProfileId = array((string) $response->xml->customerPaymentProfileId);
			
			$token->set_token($paymentProfileId[0]); 
			$token->set_customer_id($customerProfileId); 
		}
 		else
		{
			if (isset($response->xml->messages->message->text))
			{
				if (strpos((string) $response->xml->messages->message->text, "A duplicate customer payment profile already exists")!== FALSE)
				{
					$request = new AuthorizeNetCIM;
					$response = $request->getCustomerProfile($customerProfileId);
					
					if (isset($response->xml->profile->paymentProfiles))
					{
						foreach ($response->xml->profile->paymentProfiles as $profile)
						{
							$last_four = str_replace("X", '', (string) $profile->creditCard->cardNumber); 

							// what happens when they don't match for some reason?'
							if ($last_four == $this->order("last_four"))
							{
								$token->set_token((string) $profile->customerPaymentProfileId ); 
								$token->set_customer_id($customerProfileId); 
							}
						}
					}
 
				}
				else
				{
					$token->set_error_message(  (string) $response->xml->messages->message->text  ); 
				}
			}
			else
			{
				$token->set_error_message(  $this->lang('authorize_net_bad_token_response')   ); 
			}
		}
			
		return $token; 
	}
	public function charge_token($token, $customer_id)
	{
		require_once $this->library_path().'/authorize_net/AuthorizeNet.php';
 
		// Create Auth & Capture Transaction
		$transaction = new AuthorizeNetTransaction;
		$transaction->amount = $this->total();
		$transaction->customerProfileId = $customer_id;
		$transaction->customerPaymentProfileId = $token;
 
		$description = $this->order('description'); 
		
		if (!$description)
		{
			// if there's no description, we'll set a description if there's only one item in the cart
			if ($this->order('items') && count($this->order('items')) == 1)
			{
				foreach ($this->order('items') as $row_id => $item)
				{
					$description = $this->strip_punctuation( $item['title'] ); 
					
					break;
				}
			}
		}
		if ($this->order())
		{
			$transaction->order->invoiceNumber = $this->order('entry_id'); 
		}
		else
		{
			$transaction->order->description = "Direct Charge";
		}
		
 		// SENDING ORDER DATA TO AUTHORIZE.NET
		
 		if ($this->order('items'))
		{
			$line_item = array();
			foreach ($this->order('items') as $row_id => $item)
			{
				if (!isset($count))
				{
					$count=1;
				}
				$count++;
				if ($count > 30)
				{
					continue; 
				}
				
				$lineItem              = new AuthorizeNetLineItem;
				
				if (empty($item['entry_id']))
				{
					$lineItem->itemId = "000";
				}
				else
				{
					$lineItem->itemId = $item['entry_id']; 
				}

				$title = $this->strip_punctuation( $item['title'] ); 
	
				$title = substr($title, 0, 30); 
				
				while (strlen(urlencode(htmlspecialchars($title))) > 30)
				{
					$title = substr($title, 0, -1); 
				}
				
				$lineItem->name = urlencode(htmlspecialchars($title)); 
				$lineItem->quantity    = abs($item['quantity']);
				$lineItem->unitPrice   = number_format(abs($item['price']),2,'.','');

				$transaction->lineItems[] = $lineItem;
			}
		}

		$request = new AuthorizeNetCIM;
		
		if ($this->order('entry_id'))
		{
			$request->setRefId($this->order('entry_id')); 
		}

		$response = $request->createCustomerProfileTransaction("AuthCapture", $transaction);
		
		$resp['authorized']	 	= FALSE; 
		$resp['declined'] 		= FALSE; 
		$resp['transaction_id']	= NULL;
		$resp['failed']			= TRUE; 
		$resp['error_message']	= "unknown error"; // @TODO lang
		
 		if ($response->isOk()) 
		{
			$transactionResponse = $response->getTransactionResponse();
			
			if ($transactionResponse->approved)
			{
				$resp['authorized']	 	= TRUE; 
				$resp['declined'] 		= FALSE; 
				$resp['transaction_id']	= (string) $transactionResponse->transaction_id;
				$resp['failed']			= FALSE; 
				$resp['error_message']	= "";
 			}
			elseif($transactionResponse->error)
			{
				$resp['authorized']	 	= FALSE; 
				$resp['declined'] 		= FALSE; 
				$resp['transaction_id']	= NULL;
				$resp['failed']			= TRUE; 
				$resp['error_message']	= (string) $transactionResponse->error_message; 
			}
 		}
		else
		{
			if (isset($response->xml->messages->message->text))
			{
				$resp['authorized']	 	= FALSE; 
				$resp['declined'] 		= FALSE; 
				$resp['transaction_id']	= NULL;
				$resp['failed']			= TRUE; 
				$resp['error_message']	= (string) $response->xml->messages->message->text;
 			}
			else
			{
				$resp['authorized']	 	= FALSE; 
				$resp['declined'] 		= FALSE; 
				$resp['transaction_id']	= NULL;
				$resp['failed']			= TRUE; 
				$resp['error_message']	= $this->lang('authorize_net_bad_token_charge_response');
 			}			
		}

		return $resp; 
	}
	function create_recurrent_billing($subscription_amount, $credit_card_number, $sub_data)
	{
		$auth['authorized']	 	= FALSE; 
		$auth['declined'] 		= FALSE; 
		$auth['transaction_id']	= NULL;
		$auth['failed']			= TRUE; 
		$auth['error_message']	= "";
		
		if (!empty($sub_data['subscription_interval_units']))
		{
 			if ($sub_data['subscription_interval_units'] !="months" && $sub_data['subscription_interval_units'] !="days")
			{
				$sub_data['subscription_interval_units'] = "months"; 
			}
		}
 		// authorize.net does not allow intervals longer than 12 for month based subs
		if ($sub_data['subscription_interval_units'] == "months" && $sub_data['subscription_interval'] > 12)
		{
			$sub_data['subscription_interval'] = 12;
		}
		// authorize.net does not allow intervals longer than 365, or shorter than 7 days
		elseif ($sub_data['subscription_interval_units'] == "days" )
		{
			if ($sub_data['subscription_interval'] > 365)
			{
				$sub_data['subscription_interval'] = 365; 
			}
			elseif ( $sub_data['subscription_interval'] < 7)
			{
				$sub_data['subscription_interval'] = 7; 	
			}
		}
		
		$xml = new SimpleXMLElement("<ARBCreateSubscriptionRequest  xmlns=\"".$this->xmlns."xml/v1/schema/AnetApiSchema.xsd\"></ARBCreateSubscriptionRequest>");
		
			$merchantAuthentication = $xml->addChild('merchantAuthentication'); 
			$merchantAuthentication->addChild('name', $this->api_login); 
			$merchantAuthentication->addChild('transactionKey', $this->transaction_key); 
			
			// order id plus time. Since we might be sending multiple instances at the same time. 
			$xml->addChild('refId', $this->order('entry_id'). "_". time()); 
		
			$subscription = $xml->addChild('subscription'); 
				$subscription->addChild('name', $this->subscription_info($sub_data, 'subscription_name', 'default')); 
				
				$paymentSchedule = $subscription->addChild("paymentSchedule"); 
							$interval = $paymentSchedule->addChild('interval'); 
							$interval->addChild('length', $this->subscription_info($sub_data, 'subscription_interval', '1')); 
						$interval->addChild('unit', $this->subscription_info($sub_data, 'subscription_interval_units', 'months')); 
				
					$paymentSchedule->addChild('startDate', $this->subscription_info($sub_data, 'subscription_start_date', date ('Y-m-d')));
					// I think 0 is the default for unlimited @TODO check! 	
					$paymentSchedule->addChild('totalOccurrences', $this->subscription_info($sub_data, 'subscription_total_occurrences', 9999)); 	
					if ($this->subscription_info($sub_data, 'subscription_trial_occurrences'));
					{
						$paymentSchedule->addChild('trialOccurrences', $this->subscription_info($sub_data, 'subscription_trial_occurrences', 0)); 	
					}
				$subscription->addChild('amount', $subscription_amount); 
				if ($this->subscription_info($sub_data, 'subscription_trial_occurrences'));
				{
					$subscription->addChild('trialAmount', $this->subscription_info($sub_data, 'subscription_trial_price', $subscription_amount)); 	
				}
				
				$payment = $subscription->addChild('payment'); 
		
					$creditCard = $payment->addChild('creditCard'); 
					$creditCard->addChild('cardNumber', $credit_card_number); 
					$creditCard->addChild('expirationDate', str_pad($this->order('expiration_year'), 4, '201', STR_PAD_LEFT). "-".
						str_pad($this->order('expiration_month'), 2, '0', STR_PAD_LEFT)); 
					if ($this->order('CVV2'))
					{
						$creditCard->addChild('cardCode', $this->order('CVV2'));
					}		
				$billTo = $subscription->addChild('billTo'); 
				$billTo->addChild('firstName', $this->order('first_name')); 
				$billTo->addChild('lastName', $this->order('last_name')); 
				
		$data_to_send = str_replace($this->xmlns, "AnetApi/", $xml->asXML()); 
		
 		$connect = 	$this->arb_curl($data_to_send);
		
		if (!$connect)
		{
			$auth['error_message'] = $this->lang('curl_gateway_failure');
 
			return $auth; 
		}

		list ($refId, $resultCode, $code, $text, $subscriptionId) = $this->parse_return($connect);

		if ($resultCode == "Ok")
		{
			$auth['authorized']	 	= TRUE; 
			$auth['declined'] 		= FALSE; 
			$auth['transaction_id']	= $subscriptionId;
			$auth['failed']			= FALSE; 
			$auth['error_message']	= "";
		}
		elseif ($resultCode == "Error")
		{
			$auth['authorized']	 	= FALSE; 
			$auth['declined'] 		= FALSE; 
			$auth['transaction_id']	= "";
			$auth['failed']			= TRUE; 
			$auth['error_message']	= $code . ": ". $text;
		}
		return $auth;
	}
	// do not use the transaction_id / subscription_id. Use the internal trans entry id. 
	function update_recurrent_billing($subscription_id, $credit_card_number = FALSE)
	{
		$auth['authorized']	 	= FALSE; 
		$auth['declined'] 		= FALSE; 
		$auth['transaction_id']	= NULL;
		$auth['failed']			= TRUE; 
		$auth['error_message']	= "";
		
		$xml = new SimpleXMLElement("<ARBUpdateSubscriptionRequest  xmlns=\"".$this->xmlns."xml/v1/schema/AnetApiSchema.xsd\"></ARBUpdateSubscriptionRequest>");
		
			$merchantAuthentication = $xml->addChild('merchantAuthentication'); 
			$merchantAuthentication->addChild('name', $this->api_login); 
			$merchantAuthentication->addChild('transactionKey', $this->transaction_key); 

			$xml->addChild('subscriptionId', $subscription_id); 
		
			$subscription = $xml->addChild('subscription'); 
		
				$payment = $subscription->addChild('payment'); 
		
					$creditCard = $payment->addChild('creditCard'); 
					$creditCard->addChild('cardNumber', $credit_card_number); 
					$creditCard->addChild('expirationDate', str_pad($this->order('expiration_year'), 4, '201', STR_PAD_LEFT). "-".
						str_pad($this->order('expiration_month'), 2, '0', STR_PAD_LEFT)); 
						

		$data_to_send = str_replace($this->xmlns, "AnetApi/", $xml->asXML()); 
 		$connect = 	$this->arb_curl($data_to_send);
		
		if (!$connect)
		{
			$auth['error_message'] = $this->lang('curl_gateway_failure');
 
			return $auth; 
		}

		list ($resultCode, $code, $text, $subscriptionId) = $this->parse_return($connect);

		if ($resultCode == "Ok")
		{
			$auth['authorized']	 	= TRUE; 
			$auth['declined'] 		= FALSE; 
			$auth['transaction_id']	= $subscriptionId;
			$auth['failed']			= FALSE; 
			$auth['error_message']	= "";
			
		}
		elseif ($resultCode == "Error")
		{
			$auth['authorized']	 	= FALSE; 
			$auth['declined'] 		= FALSE; 
			$auth['transaction_id']	= "";
			$auth['failed']			= TRUE; 
			$auth['error_message']	= $code . ": ". $text;
		}
		return $auth;
	}
	
	function delete_recurrent_billing($subscription_id)
	{
		$xml = new SimpleXMLElement("<ARBCancelSubscriptionRequest  xmlns=\"".$this->xmlns."xml/v1/schema/AnetApiSchema.xsd\"></ARBCancelSubscriptionRequest>");
		
			$merchantAuthentication = $xml->addChild('merchantAuthentication'); 
			$merchantAuthentication->addChild('name', $this->api_login); 
			$merchantAuthentication->addChild('transactionKey', $this->transaction_key); 

			$xml->addChild('subscriptionId', $subscription_id); 
		
		$data_to_send = str_replace($this->xmlns, "AnetApi/", $xml->asXML()); 
 		$connect = 	$this->arb_curl($data_to_send);
		
		if (!$connect)
		{
			$auth['error_message'] = $this->lang('curl_gateway_failure');
 
			return $auth; 
		}

		list ($resultCode, $code, $text, $subscriptionId) = $this->parse_return($connect);

		if ($resultCode == "Ok")
		{
			$auth['authorized']	 	= TRUE; 
			$auth['declined'] 		= FALSE; 
			$auth['transaction_id']	= $subscriptionId;
			$auth['failed']			= FALSE; 
			$auth['error_message']	= "";
			
		}
		elseif ($resultCode == "Error")
		{
			$auth['authorized']	 	= FALSE; 
			$auth['declined'] 		= FALSE; 
			$auth['transaction_id']	= "";
			$auth['failed']			= TRUE; 
			$auth['error_message']	= $code . ": ". $text;
		}
		return $auth;
	}
	
	function extload($post)
	{
		if (isset($post['ACT']))
		{
			unset($post['ACT']); 
		}
		if (isset($post['G']))
		{
			unset($post['G']); 
		}
		if (isset($post['M']))
		{
			unset($post['M']); 
		}
		if (empty($post))
		{
			die($this->lang('authorize_net_no_post')); 
		}
		
		
		$auth = array(
			'authorized' 	=> FALSE,
			'error_message'	=> NULL,
			'failed'		=> TRUE,
			'declined'		=> FALSE,
			'transaction_id'=> NULL, 
			'processing'	=> FALSE
			);
			
		if ($this->plugin_settings('hash_value'))
		{
			// slightly different from AIM/SIM hash
			$hash = strtoupper(md5($this->plugin_settings('hash_value'). $post['x_trans_id'].$post['x_amount'])); 
			
			if ($hash != $post['x_MD5_Hash'])
			{
				$auth = array(
					'authorized' 	=> FALSE,
					'error_message'	=> $this->lang('authorize_net_non_matching_sha'),
					'failed'		=> TRUE,
					'declined'		=> FALSE,
					'transaction_id'=> NULL 
					);
 				die($this->lang('authorize_net_non_matching_sha')); 
			}
		}
	
		// the presence of x_subscription_paynum and x_subscription_id indicate ARB
		if (array_key_exists("x_subscription_paynum", $post) &&  array_key_exists("x_subscription_id", $post))
		{
			$subscription_id = $post['x_subscription_id']; 
			$save_data['timestamp']		= time(); 

			switch ($post["x_response_code"])
			{
	 			case "1":
 					$save_data['status']		= "open"; 
					$this->update_subscriptions($save_data, $subscription_id); 
					break;
				case "2": // card was declined
					$save_data['status']		= "failed"; 
					$this->update_subscriptions($save_data, $subscription_id); 
					break;
				case "3": // card is expired
					if ($post['x_response_reason_code'] =="8")
					{
						$save_data['status']		= "expired"; 
						$this->update_subscriptions($save_data, $subscription_id); 
						break;
					}
				case "4": // transaction is held for review
					$save_data['status']		= "pending"; 
					$this->update_subscriptions($save_data, $subscription_id); 
					break;
				default:
					// 'x_response_ reason_code'
					$save_data['status']		= "failed"; 
					$this->update_subscriptions($save_data, $subscription_id); 
			}
		}

		exit; 
		
	}
	function parse_return($content)
	{
		
		$refId = $this->substring_between($content,'<refId>','</refId>');
		$resultCode = $this->substring_between($content,'<resultCode>','</resultCode>');
		$code = $this->substring_between($content,'<code>','</code>');
		$text = $this->substring_between($content,'<text>','</text>');
		$subscriptionId = $this->substring_between($content,'<subscriptionId>','</subscriptionId>');
		
		
		return array ($refId, $resultCode, $code, $text, $subscriptionId);
	}
	
	//helper function for parsing response
	function substring_between($haystack,$start,$end) 
	{
		if (strpos($haystack,$start) === false || strpos($haystack,$end) === false) 
		{
			return false;
		} 
		else 
		{
			$start_position = strpos($haystack,$start)+strlen($start);
			$end_position = strpos($haystack,$end);
			return substr($haystack,$start_position,$end_position-$start_position);
		}
	}
	
	function arb_curl($data)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->_arb_host);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$connect = curl_exec($ch);
		return $connect;
	}
	/*
	sample silent post
		[x_response_code] => 2
		[x_response_subcode] => 1
		[x_response_reason_code] => 2
		[x_response_reason_text] => This transaction has been declined.
		[x_auth_code] =>
		[x_avs_code] => P
		[x_trans_id] => 2692521494
		[x_invoice_num] =>
		[x_description] =>
		[x_amount] => 5.99
		[x_method] => CC
		[x_type] => auth_capture
		[x_cust_id] => 17234
		[x_first_name] => Johnny
		[x_last_name] => Fakeuser
		[x_company] =>
		[x_address] =>
		[x_city] =>
		[x_state] =>
		[x_zip] =>
		[x_country] =>
		[x_phone] =>
		[x_fax] =>
		[x_email] =>
		[x_ship_to_first_name] =>
		[x_ship_to_last_name] =>
		[x_ship_to_company] =>
		[x_ship_to_address] =>
		[x_ship_to_city] =>
		[x_ship_to_state] =>
		[x_ship_to_zip] =>
		[x_ship_to_country] =>
		[x_tax] => 0.0000
		[x_duty] => 0.0000
		[x_freight] => 0.0000
		[x_tax_exempt] => FALSE
		[x_po_num] =>
		[x_MD5_Hash] => 35BB06A9F9349854922A13EE67AE5115
		[x_cavv_response] =>
		[x_test_request] => false
		[x_subscription_id] => 4991817
		[x_subscription_paynum] => 2
		[x_cim_profile_id] => 12354
		x_subscription_paynum starts at 1 for the first payment
		*/ 
		
	/*
	silent post tester code
	
		<form action="UPDATE WITH SILENT POST URL HERE!" method="post">
		    <input type="hidden" name="x_response_code" value="1"/>
		    <input type="hidden" name="x_response_subcode" value="1"/>
		    <input type="hidden" name="x_response_reason_code" value="1"/>
		    <input type="hidden" name="x_response_reason_text" value="This transaction has been approved."/>
		    <input type="hidden" name="x_auth_code" value=""/>
		    <input type="hidden" name="x_avs_code" value="P"/>
		    <input type="hidden" name="x_trans_id" value="1821199455"/>
		    <input type="hidden" name="x_invoice_num" value=""/>
		    <input type="hidden" name="x_description" value=""/>
		    <input type="hidden" name="x_amount" value="9.95"/>
		    <input type="hidden" name="x_method" value="CC"/>
		    <input type="hidden" name="x_type" value="auth_capture"/>
		    <input type="hidden" name="x_cust_id" value="1"/>
		    <input type="hidden" name="x_first_name" value="John"/>
		    <input type="hidden" name="x_last_name" value="Smith"/>
		    <input type="hidden" name="x_company" value=""/>
		    <input type="hidden" name="x_address" value=""/>
		    <input type="hidden" name="x_city" value=""/>
		    <input type="hidden" name="x_state" value=""/>
		    <input type="hidden" name="x_zip" value=""/>
		    <input type="hidden" name="x_country" value=""/>
		    <input type="hidden" name="x_phone" value=""/>
		    <input type="hidden" name="x_fax" value=""/>
		    <input type="hidden" name="x_email" value=""/>
		    <input type="hidden" name="x_ship_to_first_name" value=""/>
		    <input type="hidden" name="x_ship_to_last_name" value=""/>
		    <input type="hidden" name="x_ship_to_company" value=""/>
		    <input type="hidden" name="x_ship_to_address" value=""/>
		    <input type="hidden" name="x_ship_to_city" value=""/>
		    <input type="hidden" name="x_ship_to_state" value=""/>
		    <input type="hidden" name="x_ship_to_zip" value=""/>
		    <input type="hidden" name="x_ship_to_country" value=""/>
		    <input type="hidden" name="x_tax" value="0.0000"/>
		    <input type="hidden" name="x_duty" value="0.0000"/>
		    <input type="hidden" name="x_freight" value="0.0000"/>
		    <input type="hidden" name="x_tax_exempt" value="FALSE"/>
		    <input type="hidden" name="x_po_num" value=""/>
		    <input type="hidden" name="x_MD5_Hash" value="A375D35004547A91EE3B7AFA40B1E727"/>
		    <input type="hidden" name="x_cavv_response" value=""/>
		    <input type="hidden" name="x_test_request" value="false"/>
		    <input type="hidden" name="x_subscription_id" value="365314"/>
		    <input type="hidden" name="x_subscription_paynum" value="1"/>
		    <input type="submit"/>
		</form>
	*/ 
	
}
// END Class