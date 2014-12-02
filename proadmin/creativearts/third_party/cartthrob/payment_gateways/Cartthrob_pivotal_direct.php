<?php 

class Cartthrob_pivotal_direct extends Cartthrob_payment_gateway
{
	public $title = 'pivotal_direct_title'; 
	public $overview = 'pivotal_direct_overview'; 
	public $settings = array (
	  array (
	    'name' => 'mode',
	    'short_name' => 'mode',
	    'type' => 'select',
	    'default' => 'test',
	    'options' => 
	    array (
	      'test' => 'test',
	      'live' => 'live',
	    ),
	  ),
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
		/*
		array(
			'name' => 'pivotal_rpnum',
			'short_name' => 'rpnum',
			'type' => 'text'
		),*/
		array(
			'name' => 'pivotal_live_gateway_url',
			'short_name' => 'live_url',
			'default'	=> 'https://secure1.pivotalpayments.com/SmartPayments/transact.asmx',
			'type' => 'text'
		),
		array(
			'name' => 'pivotal_dev_username',
			'short_name' => 'dev_username',
			'type' => 'text'
		),
		array(
			'name' => 'pivotal_dev_password',
			'short_name' => 'dev_password',
			'type' => 'text'
		),
		/*
		array(
			'name' => 'pivotal_dev_rpnum',
			'short_name' => 'dev_rpnum',
			'type' => 'text'
		),
		*/
		array(
			'name' => 'pivotal_dev_gateway_url',
			'short_name' => 'dev_url',
			'default'	=> 'https://test.pivotalpayments.com/SmartPayments/transact.asmx',
			'type' => 'text'
		),
	); 

	
	public $required_fields = array(
		'first_name',
		'last_name',
		'address',
		'city',
		'state',
		'zip',
		'country_code',
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
		'country_code',
		'phone',
		'email_address',
		'shipping_first_name',
		'shipping_last_name',
		'shipping_address',
		'shipping_address2',
		'shipping_city',
		'shipping_state',
		'shipping_zip',
		'shipping_country_code',
		'card_type',
		'credit_card_number',
		'CVV2',
		'expiration_year',
		'expiration_month'
	);
	
	public $username = NULL; 
	public $password = NULL; 
	public $rpnum 	= NULL; 
 
	public function initialize()
	{
		if ($this->plugin_settings('mode') == "test") 
		{
			$this->host	=  $this->plugin_settings('dev_url'); //"https://gatewaystage.itstgate.com/SmartPayments/transact.asmx/";
			$this->username = $this->plugin_settings('dev_username'); 
			$this->password = $this->plugin_settings('dev_password'); 
			$this->rpnum = $this->plugin_settings('dev_rpnum'); 
			
 		}
		else
		{
			$this->host	=  $this->plugin_settings('live_url'); //"https://gatewaystage.itstgate.com/SmartPayments/transact.asmx/";
			$this->username = $this->plugin_settings('username'); 
			$this->password = $this->plugin_settings('password'); 
			$this->rpnum = $this->plugin_settings('rpnum'); 
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
	public function process_payment($credit_card_number)
	{
  
		$xml = new SimpleXMLElement("<removeme></removeme>" );
		
		$xml->addChild("TrainingMode", "F"); 
		$xml->addChild("City", $this->order('city')); 
		$xml->addChild("BillToState", $this->order('state')); 
		if ($this->order('member_id'))
		{
			$xml->addChild("CustomerID", $this->order('member_id')); 
		}
		if ($this->order('CVV2'))
		{
			$xml->addChild("CVPresence", "3"); 
		}
		$invoice =  $xml->addChild("Invoice");
		$invoice->addChild("InvNum", $this->order('order_id')); 

			$billto = $invoice->addChild("BillTo"); 
			$billto->addChild("Name", $this->order('first_name') ." ". $this->order('last_name')); 
			if ($this->order('member_id'))
			{
				$billto->addChild("CustomerID", $this->order('member_id')); 
			}
			
			$address = $billto->addChild("Address"); 
				$address->addChild("Street", $this->order('address') . ($this->order('address2') ? " ".$this->order('address2'):"") ); 
				$address->addChild("City", $this->order('city'));
				$address->addChild("State", $this->order('state')); 
				$address->addChild("Zip", $this->order('zip')); 
				$address->addChild("Country", $this->order('country_code')); 
			
			$billto->addChild("Email", $this->order('email_address')); 
			$billto->addChild("Phone", $this->order('phone')); 
		$invoice->addChild("Description", $this->lang('pivotal_cart_payment')); 
		
		foreach ( $this->order("items") as $row_id => $item)
		{
			if (!isset($items))
			{
				$items = $invoice->addChild("Items"); 
			}
			$var = "item".$row_id; 
			$$var = $items->addChild("Item"); 
			$$var->addChild("SKU", $item['entry_id']); 
			$$var->addChild("Description", $item['title'] ); 
			$$var->addChild("Quantity", $item['quantity'] ); 
			$$var->addChild("TotalAmt", ( abs($item['quantity']) * number_format(abs($item['price']),2,'.','') ) ); 
		}
		$invoice->addChild("DiscountAmt", $this->order('discount')); 
 		$invoice->addChild("ShippingAmt", $this->order('shipping')); 
		$invoice->addChild("TaxAmt", $this->order('tax')); 
		$invoice->addChild("TotalAmt", $this->order('total')); 
 
		$extdata = (string) $xml->asXML(); 
		
		// stupid hack to get around simplexml's insestence that it be valid xml ;)
		$extdata = str_replace("<removeme>", "", $extdata); 
		$extdata = str_replace("</removeme>", "", $extdata); 
		
  		$post_array= array(
			'UserName'		=> $this->username, 
			'Password'		=> $this->password, 
			#'RPNum'			=> $this->rpnum, 
			'TransType'		=> "SALE",  
			'CardNum'    	=> $credit_card_number,      
			'ExpDate'		=> str_pad($this->order('expiration_month'), 2, '0', STR_PAD_LEFT).$this->year_2($this->order('expiration_year')),
			'MagData'		=> "",
			'NameOnCard'	=> $this->order('first_name') ." ". $this->order('last_name'),
			'Amount'		=> number_format(abs($this->order('total')),2,'.',''), 
			'InvNum'	 	=> $this->order('order_id'),
			'PNRef'			=> "",
			'Zip'			=> $this->order('zip'),
			'Street'		=> $this->order('address'),
			'CVNum'			=> $this->order('CVV2'),
			"ExtData"		=> $extdata
  			); 

 		$transaction = new SimpleXMLElement($this->curl_transaction($this->host. "ProcessCreditCard", $this->data_array_to_string($post_array) ));

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
				
				$avs_message = NULL; 
				if (!empty($transaction->GetAVSResultTXT))
				{
					$avs_message = (string) $transaction->GetAVSResultTXT; 
				}
				$resp['authorized']	 	= FALSE; 
				$resp['declined'] 		= TRUE; 
				$resp['transaction_id']	= NULL;
				$resp['failed']			= FALSE; 
				$resp['error_message']	= (string) $transaction->RespMSG. " ". $avs_message;
				break;
			case "-100": 
				$avs_message = NULL; 
				if (!empty($transaction->GetAVSResultTXT))
				{
					$avs_message = (string) $transaction->GetAVSResultTXT; 
				}
				$resp['authorized']	 	= FALSE; 
				$resp['declined'] 		= FALSE; 
				$resp['transaction_id']	= NULL;
				$resp['failed']			= TRUE; 
				$resp['error_message']	= $this->lang('pivotal_address_mismatch'). " ".$avs_message;
				break;
			default: 
				$avs_message = NULL; 
				if (!empty($transaction->GetAVSResultTXT))
				{
					$avs_message = (string) $transaction->GetAVSResultTXT; 
				}
				$resp['authorized']	 	= FALSE; 
				$resp['declined'] 		= FALSE; 
				$resp['transaction_id']	= NULL;
				$resp['failed']			= TRUE; 
				$resp['error_message']	= (string) $transaction->RespMSG. " ". $avs_message;
		}
 
		return $resp; 
 
 	}

}