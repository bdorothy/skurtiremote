<?php 
class Cartthrob_cartthrob_direct extends Cartthrob_payment_gateway
{
	public $title = 'payleap_direct_title';
	public $affiliate = 'payleap_affiliate'; 
	public $overview = 'payleap_overview';
	public $settings = array(
		array(
			'name' => 'username',
			'short_name' => 'username',
			'type' => 'text'
		),
		array(
			'name' => 'password',
			'short_name' => 'password',
			'type' => 'text'
		),
		array(
			'name' => 'merchant_key',
			'short_name' => 'merchant_key',
			'type' => 'text'
		),
		array(
			'name' => 'payleap_dev_username',
			'short_name' => 'dev_username',
			'type' => 'text'
		),
		array(
			'name' => 'payleap_dev_password',
			'short_name' => 'dev_password',
			'type' => 'text'
		),
		array(
			'name' => 'payleap_dev_merchant_key',
			'short_name' => 'dev_merchant_key',
			'type' => 'text'
		),		
		array(
			'name' => "mode",
			'short_name' => 'mode',
			'type' => 'radio',
			'default' => "no_account",
			'options' => array(
				"no_account" => "payleap_no_account",
				"test"	=> "test",
				"live"	=> "live",
			)
		),
	);
	
	public $required_fields = array(
		'first_name',
		'last_name',
		'address',
		'city',
		'state',
		'zip',
		'phone',
		'email_address',
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
	
	public $hidden = array();
	public $card_types = array(
		"mc"	=> "MasterCard",
		"visa"	=> "Visa",
		"discover"	=> "Discover",
		"diners"	=> "Diners",
		"amex"		=> "AMEX"
		);
	
	public function initialize()
	{
		if ($this->plugin_settings('mode') == "test") 
		{
			$this->host	= "https://uat.payleap.com/TransactServices.svc/ProcessCreditCard";
			$this->tokenhost = "https://uat.payleap.com/MerchantServices.svc/"; 
			$this->username = $this->plugin_settings('dev_username'); 
			$this->password = $this->plugin_settings('dev_password'); 
			$this->merchant_key = $this->plugin_settings('dev_merchant_key'); 
 		}
		else
		{
			$this->host = "https://secure1.payleap.com/TransactServices.svc/ProcessCreditCard";
			$this->tokenhost = "https://secure1.payleap.com/MerchantServices.svc/"; 
 			$this->username = $this->plugin_settings('username'); 
			$this->password = $this->plugin_settings('password');
			$this->merchant_key = $this->plugin_settings('merchant_key'); 
			
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
		if($this->plugin_settings('mode') == "no_account")
		{
			$resp['authorized']	 	= FALSE; 
			$resp['declined'] 		= FALSE; 
			$resp['transaction_id']	= NULL;
			$resp['failed']			= TRUE; 
			$resp['error_message']	= "payleap_no_account_message";
			return $resp; 
		}
		
		$xml = new SimpleXMLElement("<TrainingMode>F</TrainingMode>");

		$invoice =  $xml->addChild("Invoice");
		$invoice->addChild("InvNum", $this->order('title')); 
		
			$billto = $invoice->addChild("BillTo"); 
			$billto->addChild("Name", $this->order('first_name') ." ". $this->order('last_name')); 
			$address = $billto->addChild("Address"); 
				$address->addChild("Street", $this->order('address') . ($this->order('address2') ? " ".$this->order('address2'):"") ); 
				$address->addChild("City", $this->order('city'));
				$address->addChild("State", $this->order('state')); 
				$address->addChild("Zip", $this->order('zip')); 
				$address->addChild("Country", $this->order('country_code')); 
			
			$billto->addChild("Email", $this->order('email_address')); 
			$billto->addChild("Phone", $this->order('phone')); 
		$invoice->addChild("Description", "Cart Payment"); 

		foreach ( $this->order("items") as $row_id => $item)
		{
			if (!isset($items))
			{
				$items = $invoice->addChild("Items"); 
			}
			$var = "item".$row_id; 
			$$var = $items->addChild("Item"); 
			$$var->addChild("Sku", $item['entry_id']); 
			$$var->addChild("TotalAmt", ( abs($item['quantity']) * number_format(abs($item['price']),2,'.','') ) ); 
		}

 		$invoice->addChild("ShippingAmt", $this->order('shipping')); 
		$invoice->addChild("TaxAmt", $this->order('tax')); 
		$invoice->addChild("DiscountAmt", $this->order('discount')); 
		$invoice->addChild("TotalAmt", $this->total()); 
		
		$xml->addChild("City", $this->order('city')); 
		$xml->addChild("BillToState", $this->order('state')); 
		$xml->addChild("BillToPostalCode", $this->order('zip')); 
		$xml->addChild("Email", $this->order('email_address')); 
		$xml->addChild("Phone", $this->order('phone')); 
		
		$extdata = (string) $xml->asXML(); 
		
 		$post_array= array(
			'Username'		=> $this->username,
			'Password'		=> $this->password,
			'TransType'		=> "Sale", //Auth
			'NameOnCard'	=> $this->order('first_name') ." ". $this->order('last_name'),
			'CardNum'    	=> $credit_card_number,      
			'ExpDate'		=> str_pad($this->order('expiration_month'), 2, '0', STR_PAD_LEFT).$this->year_2($this->order('expiration_year')),
			'CVNum'			=> $this->order('CVV2'),
			'Amount'		=> $this->total(),
			'PNRef'			=> "",
			'MagData'		=> "",
			"ExtData"		=> $extdata
  			); 
 
 		$transaction = new SimpleXMLElement($this->curl_transaction($this->host, $this->data_array_to_string($post_array) ));

		$resp['authorized']	 	= FALSE; 
		$resp['declined'] 		= FALSE; 
		$resp['transaction_id']	= NULL;
		$resp['failed']			= TRUE; 
		$resp['error_message']	= "";
		$resp['processing']		= FALSE; 
		
		if (!$transaction)
		{
 			$resp['error_message']	= $this->lang('curl_gateway_failure'); 
			return $resp; 
		}
 		switch($transaction->Result)
		{
			case "0": 
				$resp['authorized']	 	= TRUE; 
				$resp['declined'] 		= FALSE; 
				$resp['transaction_id']	= (string) $transaction->AuthCode;
				$resp['failed']			= FALSE; 
				$resp['error_message']	= "";
				break;
			case "12": 
				$resp['authorized']	 	= FALSE; 
				$resp['declined'] 		= TRUE; 
				$resp['transaction_id']	= NULL;
				$resp['failed']			= FALSE; 
				$resp['error_message']	= (string) $transaction->RespMSG;
				break;
			case "-100": 
				$resp['authorized']	 	= FALSE; 
				$resp['declined'] 		= FALSE; 
				$resp['transaction_id']	= NULL;
				$resp['failed']			= TRUE; 
				$resp['error_message']	= $this->lang('payleap_address_mismatch');
				break;
			default: 
				$resp['authorized']	 	= FALSE; 
				$resp['declined'] 		= FALSE; 
				$resp['transaction_id']	= NULL;
				$resp['failed']			= TRUE; 
				$resp['error_message']	= (string) $transaction->RespMSG;
		}
 
		return $resp; 
 
 	}
 	
 	/**
 	 * Add Customer
 	 * 
 	 * Used by create_token to create a custom profile on PayLeap
 	 * prior to token creation.
 	 * returns TRUE if successful and sets $this->customer_key
 	 * returns a string error message if failed
 	 */
	public function add_customer()
	{
		$post_array= array(
			'Username'		=> $this->username,
			'Password'		=> $this->password,
			'TransType'		=> 'ADD', 
			'Vendor'		=> $this->merchant_key, 
			'CustomerKey'	=> '', //@TODO if this is "add" is this required? 
			'CustomerID'	=> $this->order('member_id'),
			'CustomerName'	=> $this->order('first_name') . " ". $this->order('last_name'), 
			'FirstName'		=> $this->order('first_name'),
			'LastName'		=> $this->order('last_name'),
			'Street1'		=> $this->order('address'),
			'Street2'		=> $this->order('address2'), 
			'City'			=> $this->order('city'),
			'StateID'		=> $this->order('state'),
			'Zip'			=> $this->order('zip'),
			'CountryID'		=> $this->order('country_code'),
			'Email'			=> $this->order('email_address'),
  			);
		
		$payload = $this->curl_transaction($this->tokenhost."ManageCustomer", $this->data_array_to_string($post_array) );

		//$this->log("add_customer:<br><br>".htmlentities($payload));

		return new SimpleXMLElement($payload);
	}
	
	public function create_token($credit_card_number)
	{
		$token = new Cartthrob_token();
		
		$customer_transaction = $this->add_customer(); 

		if ( ! $customer_transaction)
		{
 			return $token->set_error_message( $this->lang('invalid_response') ); 
		}
 		
		if (strtoupper((string) $customer_transaction->Code) != "OK")
		{
			return $token->set_error_message( (string) $customer_transaction->Error ); 
		}
			
		$post_array= array(
			'Username'		=> $this->username,
			'Password'		=> $this->password,
			'TransType'		=> 'ADD', 
			'Vendor'		=> $this->merchant_key, 
			'CustomerKey'	=> (string) $customer_transaction->CustomerKey, 
			'CardInfoKey'	=> '', //@TODO check if a default is required
			'CcAccountNum'	=> $credit_card_number,
			'CcExpDate'		=> str_pad($this->order('expiration_month'), 2, '0', STR_PAD_LEFT).$this->year_2($this->order('expiration_year')),
			'CcNameonCard'	=> $this->order('first_name') . " ". $this->order('last_name'), 
			'CcStreet'		=> $this->order('address'),
			'CcZip'			=> $this->order('zip'),
  			);

		$payload = $this->curl_transaction($this->tokenhost."ManageCreditCardInfo", $this->data_array_to_string($post_array) );

		//$this->log("create_token:<br><br>".htmlentities($payload));

		$transaction = new SimpleXMLElement($payload);

		if (!$transaction)
		{
			return $token->set_error_message(  $this->lang('bad_token_response')  ); 
		}
 		
		if (strtoupper((string) $transaction->Code) != "OK")
		{
			return $token->set_error_message(  (string) $transaction->Error  ); 
		}
		
		return $token->set_token( (string) $transaction->CcInfoKey)
				->set_customer_id( (string) $customer_transaction->CustomerKey );
 
	}
	
	public function charge_token($token, $customer_id = NULL)
	{
		// ProcessCreditCard
		
		$post_array= array(
			'Username'		=> $this->username,
			'Password'		=> $this->password,
			'Vendor'		=> $this->merchant_key, 
			'CcInfoKey'		=> $token, 
			'Amount'		=> $this->order('total'),
			'InvNum'		=> $this->order('entry_id'),
  			);

		$payload = $this->curl_transaction($this->tokenhost."ProcessCreditCard", $this->data_array_to_string($post_array) );

		//$this->log("charge_token:<br><br>".htmlentities($payload));

		$transaction = new SimpleXMLElement($payload);

		if ( ! $transaction)
		{
			$resp['authorized']	 	= FALSE; 
			$resp['declined'] 		= FALSE; 
			$resp['transaction_id']	= NULL;
			$resp['failed']			= TRUE; 
			$resp['error_message']	= $this->lang('invalid_response');

			return $resp;
		}
 		

		switch(@$transaction->Result)
		{
			case "0": 
				$resp['authorized']	 	= TRUE; 
				$resp['declined'] 		= FALSE; 
				$resp['transaction_id']	= (string) $transaction->AuthCode;
				$resp['failed']			= FALSE; 
				$resp['error_message']	= "";
				break;
			case "12": 
				$resp['authorized']	 	= FALSE; 
				$resp['declined'] 		= TRUE; 
				$resp['transaction_id']	= NULL;
				$resp['failed']			= FALSE; 
				$resp['error_message']	= (string) $transaction->Message;
				break;
			case "-100": 
				$resp['authorized']	 	= FALSE; 
				$resp['declined'] 		= FALSE; 
				$resp['transaction_id']	= NULL;
				$resp['failed']			= TRUE; 
				$resp['error_message']	= $this->lang('payleap_address_mismatch');
				break;
			default: 
				$resp['authorized']	 	= FALSE; 
				$resp['declined'] 		= FALSE; 
				$resp['transaction_id']	= NULL;
				$resp['failed']			= TRUE; 
				$resp['error_message']	= (string) @$transaction->Error;
		}
 
		return $resp;
		
	}
 }
// END Class