<?php 
class Cartthrob_moneris_direct extends Cartthrob_payment_gateway
{
	public $title = 'moneris_title';
	//public $affiliate = 'moneris_affiliate'; 
	public $overview = 'moneris_overview';
	public $language_file = TRUE;
	public $settings = array(
		array(
			'name' => 'moneris_store_id',  
			'short_name' => 'store_id',  
			'type' => 'text',  
		),
		array(
			'name'	=> 'moneris_api_token',
			'short_name' => "api_token",
			'type'		=> 'text'
		),
		array(
			'name' => 'moneris_efraud',  
			'short_name' => 'avs',  
			'type' => 'radio',
			'default' => "no", 
			'options' => array(
				'no'	=> 'no',
				'yes' => 'yes'
				)  
		),	
		array(
			'name' => 'mode',  
			'short_name' => 'mode',  
			'type' => 'radio',
			'default' => "test", 
			'options' => array(
				'test'	=> 'test',
				'live' => 'live'
				)  
		),
		array(
			'name' => 'moneris_test_api_token',  
			'short_name' => 'test_store_id',  
			'default' => "store1",
			'type' => 'select',
			'options' => array(
				'store1'	=> 'moneris_test_1',
				'store2'	=> 'moneris_test_2',
				'store3'	=> 'moneris_test_3',
				'store5'	=> 'moneris_test_4',
				'moneris'	=> 'moneris_test_5',
 				'monusqa003'	=> 'monusqa003',
				'monusqa004'	=> 'monusqa004',
				'monusqa005'	=> 'monusqa005',
				'monusqa006'	=> 'monusqa006',
			
				)  
		),
		array(
			'name' => 'moneris_test_total',  
			'short_name' => 'test_total',  
			'type' => 'text',
			'default' => "10.00"
		)
	);
	
	public $required_fields = array(
		'first_name',
		'last_name'
	);
	
 	public $hidden = array('description'); 

	public $card_types = NULL; 
	
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
		'company',
		'phone',
		'email_address',
		'credit_card_number',
		'CVV2',
		'expiration_month',
		'expiration_year'
		); 

	
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
		$total = $this->total(); 
		
		$order_id = $this->order('entry_id')."-".date("dmy-G:i:s").rand(5, 15);
		
		if ($this->order('currency_code') == "USD")
		{
			$currency = "USD"; 
		} 
		else
		{
			$currency = "CAD"; 
		}
		
		if ($this->plugin_settings('mode') == "live")
		{
			$this->_host = "https://www3.moneris.com:443/gateway2/servlet/MpgRequest" ; 
			if ($currency != "CAD")
			{
				// us test host. 
				$this->_host = "https://esplus.moneris.com:443/gateway_us/servlet/MpgRequest" ; 
			}
			//$this->_host = "https://ipgate.moneris.com:443/gateway2/servlet/MpgRequest"; 
 			$store_id = $this->plugin_settings('store_id'); 
			$api_token = $this->plugin_settings('api_token');
		}
		else
		{
			$this->_host = "https://esqa.moneris.com:443/gateway2/servlet/MpgRequest" ; 
			if ($currency != "CAD")
			{
				// us test host. 
				$this->_host = "https://esplusqa.moneris.com:443/gateway_us/servlet/MpgRequest" ; 
			}
 			//$this->_host = "https://ssltest.moneris.com:443/gateway2/servlet/MpgRequest"; 
			
			if ($this->plugin_settings('test_total'))
			{
				$total = $this->plugin_settings('test_total'); 
			}
			
			
			if ($currency != "CAD")
			{
				switch($this->plugin_settings('test_store_id'))
				{
					case 'monusqa002': 
					case 'monusqa003': 
					case 'monusqa004': 
					case 'monusqa005': 
					case 'monusqa006': 
					case 'monusqa024': 
					case 'monusqa025': 
					default: 
						$store_id = $this->plugin_settings('test_store_id'); 
						$api_token = 'qatoken';
				}
				
				if (strpos($this->plugin_settings('test_store_id'), "monusqa") === FALSE)
				{
					$store_id = "monusqa003"; 
				}

			}
			else
			{
				switch($this->plugin_settings('test_store_id'))
				{
					case 'store1': 
					case 'store2': 
					case 'store3': 
					case 'store5':
						$store_id = $this->plugin_settings('test_store_id'); 
						$api_token = 'yesguy';
						break;
					case 'moneris': 
						$store_id = $this->plugin_settings('test_store_id'); 
						$api_token = 'hurgle';
						break;
					default: 
						$store_id = $this->plugin_settings('test_store_id'); 
						$api_token = 'yesguy';
				}
			}


		}
 		$expiry_date = $this->year_2($this->order('expiration_year')).$this->order('expiration_month'); 
		
		if ($currency == "CAD")
		{
			$type = "purchase"; 
			/*
			require_once $this->library_path().'moneris/ca/mpgClasses.php';

			$txnArray=array('type'=>$type,
			     		    'order_id'=>$order_id,
			     		    'cust_id'=>$this->order("member_id"),
			    		    'amount'=>$total,
			   			    'pan'=>$credit_card_number,
			   			    'expdate'=>$expiry_date,
			   			    'crypt_type'=>'7',
			   			    'dynamic_descriptor'=>$order_id
			   		       );
			*/
		}
		else
		{
			$type = "us_purchase"; 
			/*
			require_once $this->library_path().'moneris/us/mpgClasses.php';
			
			$txnArray=array('type'=>$type,
			     		    'order_id'=>$order_id,
			     		    'cust_id'=>$this->order("member_id"),
			    		    'amount'=>$total,
			   			    'pan'=>$credit_card_number,
			   			    'expdate'=>$expiry_date,
			   			    'crypt_type'=>'7',
 			   		       );
			*/ 
		}
		
		/*
 		$path_to_crt = $this->library_path().'moneris/curl-ca-bundle.crt';

		$mpgTxn = new mpgTransaction($txnArray);

		$mpgRequest = new mpgRequest($mpgTxn);

		$mpgHttpPost  =new mpgHttpsPost($store_id,$api_token,$mpgRequest, $path_to_crt);
 
		$mpgResponse=$mpgHttpPost->getMpgResponse();
		
		print("\nCardType = " . $mpgResponse->getCardType());
		print("\nTransAmount = " . $mpgResponse->getTransAmount());
		print("\nTxnNumber = " . $mpgResponse->getTxnNumber());
		print("\nReceiptId = " . $mpgResponse->getReceiptId());
		print("\nTransType = " . $mpgResponse->getTransType());
		print("\nReferenceNum = " . $mpgResponse->getReferenceNum());
		print("\nResponseCode = " . $mpgResponse->getResponseCode());
		print("\nISO = " . $mpgResponse->getISO());
		print("\nMessage = " . $mpgResponse->getMessage());
		print("\nAuthCode = " . $mpgResponse->getAuthCode());
		print("\nComplete = " . $mpgResponse->getComplete());
		print("\nTransDate = " . $mpgResponse->getTransDate());
		print("\nTransTime = " . $mpgResponse->getTransTime());
		print("\nTicket = " . $mpgResponse->getTicket());
		print("\nTimedOut = " . $mpgResponse->getTimedOut());
		print("\nStatusCode = " . $mpgResponse->getStatusCode());
		print("\nStatusMessage = " . $mpgResponse->getStatusMessage());
  		*/
		
 		$resp = array(
			 'authorized' 		=> FALSE, 
			 'declined' 		=> FALSE, 
			 'failed'			=> TRUE, 
			 'error_message'	=> NULL,
			 'transaction_id'	=> NULL
			); 
				 

		$xml = "<?xml version=\"1.0\" ".(($type=="us_purchase") ? 'encoding="iso-8859-1"' : "" ) ."?>"; 
	 	$xml .="<request>";
	 		$xml .="<store_id>".$store_id."</store_id>";
	 		$xml .="<api_token>".$api_token."</api_token>";
			$xml .="<".$type.">";
 				$xml .="<order_id>".$order_id."</order_id>"; 
				$xml .="<amount>".$total."</amount>"; 
				$xml .="<pan>".$credit_card_number."</pan>"; 
				$xml .="<expdate>".$this->year_2($this->order('expiration_year')).$this->order('expiration_month')."</expdate>"; 
				$xml .="<crypt_type>7</crypt_type>"; 
				if ($this->plugin_settings('avs') == "yes")
				{
					$xml .="<cvd_info>"; 
						$xml .="<cvd_indicator>1</cvd_indicator>"; 
						$xml .="<cvd_value>".$this->order('CVV2')."</cvd_value>"; 
					$xml .="</cvd_info>";					
				}
				$xml .="<cust_info>"; 
					$xml .="<email>".$this->order('email_address')."</email>"; 
					$xml .="<instructions></instructions>"; 
					$xml .="<billing>"; 
						$xml .="<first_name>".$this->order('first_name')."</first_name>"; 
						$xml .="<last_name>".$this->order('last_name')."</last_name>"; 
						$xml .="<company_name>".$this->order('company')."</company_name>"; 
						$xml .="<address>".$this->order('address')." ".$this->order('address2') ."</address>"; 
						$xml .="<city>".$this->order('city')."</city>"; 
						$xml .="<province>".$this->order('state')."</province>"; 
						$xml .="<postal_code>".$this->order('zip')."</postal_code>"; 
						$xml .="<country>".$this->alpha2_country_code($this->order('country_code'))."</country>"; 
						$xml .="<phone_number>".$this->order('phone')."</phone_number>"; 
					$xml .="</billing>"; 
					$xml .="<shipping>"; 
						$xml .="<first_name>".$this->order('shipping_first_name')."</first_name>"; 
						$xml .="<last_name>".$this->order('shipping_last_name')."</last_name>"; 
						$xml .="<company_name>".$this->order('company')."</company_name>"; 
						$xml .="<address>".$this->order('shipping_address')." ".$this->order('shipping_address2') ."</address>"; 
						$xml .="<city>".$this->order('shipping_city')."</city>"; 
						$xml .="<province>".$this->order('shipping_state')."</province>"; 
						$xml .="<postal_code>".$this->order('shipping_zip')."</postal_code>"; 
						$xml .="<country>".$this->order('shipping_country_code')."</country>"; 
						$xml .="<phone_number>".$this->order('phone')."</phone_number>";
					$xml .="</shipping>"; 
				$xml .="</cust_info>"; 
			$xml .="</".$type.">"; 
	 	$xml .="</request>";
		
		#var_dump($xml); 
		$connect = $this->curl_transaction($this->_host, $xml); 

		#var_dump($connect); 
		#exit; 
		$resp = array(
			 'authorized' 		=> FALSE, 
			 'declined' 		=> FALSE, 
			 'failed'			=> TRUE, 
			 'error_message'	=> NULL,
			 'transaction_id'	=> NULL
			);

		if (!$connect)
		{
			$resp['error_message']	= $this->lang('curl_gateway_failure'); 
			return $resp; 
		}
		$transaction = $this->xml_to_array($connect, $build_type="basic"); 
		
		if (isset($transaction['response']['receipt'][0]['ResponseCode']['data']))
		{
			if (strtolower($transaction['response']['receipt'][0]['ResponseCode']['data']) !="null")
			{
				$response_code = (int) $transaction['response']['receipt'][0]['ResponseCode']['data']; 
			}
			else
			{
				$response_code = NULL; 
			}
		}
		else
		{
			$response_code = NULL; 
		}
		
		
		if ($response_code === NULL )
		{
			$resp = array(
				'authorized'	=> FALSE, 
				'declined'	 	=> FALSE, 
				'failed'		=> TRUE, 
				'transaction_id'=> NULL
				);
				
			if (!empty($transaction['response']['receipt'][0]['Message']['data']))
			{
				$resp['error_message'] = $transaction['response']['receipt'][0]['Message']['data'];
			}
		}
		elseif ($response_code < 50)
		{
			
			$resp = array(
				 'authorized'		=> TRUE, 
				 'declined'			=> FALSE, 
				 'failed'			=> FALSE, 
				 'error_message'	=> NULL,
				 'transaction_id'	=> $transaction['response']['receipt'][0]['AuthCode']['data']." ". $transaction['response']['receipt'][0]['TransID']['data']
				);
				
		}
		else
		{
			$resp = array(
				'authorized'	=> FALSE, 
				'declined'	 	=> FALSE, 
				'failed'		=> TRUE, 
				'error_message'	=> "Reason Code: ". $response_code. " ". $transaction['response']['receipt'][0]['Message']['data'],
				'transaction_id'=> NULL
				);
		}
		return $resp;
 		
	}
	// END
}
// END Class