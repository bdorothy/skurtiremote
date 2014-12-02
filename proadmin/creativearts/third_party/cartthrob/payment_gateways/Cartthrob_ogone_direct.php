<?php 
class Cartthrob_ogone_direct extends Cartthrob_payment_gateway
{
	public $title = 'ogone_direct_title';
	public $overview = 'ogone_direct_overview';
	public $language_file = TRUE;
	public $settings = array(
		array(
			'name' => "mode", 
			'short_name' => 'mode', 
			'type' => 'radio',
			'default' => 'test',
			'options' => array(
				'test' => "test",
				'live' => "live",
			),
		),
		array(
			'name' => 'ogone_pspid_live', 
			'short_name' => 'pspid_live', 
			'type' => 'text', 
		),
		array(
			'name' => 'ogone_pspid_test', 
			'short_name' => 'pspid_test', 
			'type' => 'text', 
		),
		array(
			'name' => 'ogone_api_user_id', 
			'short_name' => 'api_userid', 
			'type' => 'text', 
		),
		array(
			'name' => 'APIogone_api_password', 
			'short_name' => 'api_password', 
			'type' => 'text', 
		),
		array(
			'name' => 'ogone_sha_passphrase', 
			'short_name' => 'passphrase', 
			'type' => 'text', 
		)
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
		
		'credit_card_number',
		'card_type',
		'issue_number',
		'CVV2',
		
		'expiration_year',
		'expiration_month'
	);
	
	public $hidden = array(
		'currency_code'
	);

	public $card_types	= array(
		'mc',
		'visa',
		'amex',
		'maestro',
		'solo',
		'delta'
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
		
		switch ($this->plugin_settings('mode'))
		{
			case "test": 
				$this->_host = "https://secure.ogone.com/ncol/test/orderdirect.asp"; 
				$this->_pspid = $this->plugin_settings('pspid_test');
				break; 
			default: 
				$this->_host = "https://secure.ogone.com/ncol/prod/orderdirect.asp"; 
				$this->_pspid = $this->plugin_settings('pspid_live');
		}
		
		$total = $this->total() * 100; 
		
		$post_array = array(
			'PSPID'				=> $this->_pspid,
			'OrderID'			=> $this->order('entry_id'),
			'USERID'			=> $this->plugin_settings('api_userid'),
			'PSWD'				=> $this->plugin_settings('api_password'),
			'amount'			=> $total,
			'currency'			=> ( $this->order('currency_code') ? $this->order('currency_code') : "EUR"),  
			'CARDNO'			=> $credit_card_number,
			'ED'				=> $this->order('expiration_month'). $this->year_2($this->order('expiration_year')),
			'COM'				=> ($this->order('description') ? $this->order('description') : "Sale"),
			'CN'				=> $this->order('first_name'). " " . $this->order('last_name'),
			'EMAIL'				=> $this->order('email_address'),
			'SHASign'			=> '',
			'CVC'				=> $this->order('CVV2'),
			'Owneraddress'		=> $this->order('address'). " " . $this->order('address2'),
			'OwnerZip'			=> $this->order('zip'),
			'ownertown'			=> $this->order('city'),
			'ownercty'			=> ($this->order('country_code') ? $this->alpha2_country_code($this->order('country_code')) : "GB"),
			'ownertelno'		=> $this->order('phone'),
			'BRAND'				=> $card_type,
			'Operation'			=> 'SAL',
			'REMOTE_ADDR'		=> 'NONE', // 
			'RTIMEOUT'			=> 90,
			'ECI'				=> 7,
		);
		
		// SHA GENERATION //////////////////////////////
		uksort($post_array, "strnatcasecmp");
		$string_to_hash = "";
		foreach ($post_array as $key=>$value)
		{
			if ($value)
			{
				$revised_post_array[$key] = $value; 
				$string_to_hash.=strtoupper($key)."=".$value.$this->plugin_settings('passphrase');
			}
		}
		$sha1_hash = strtoupper(sha1($string_to_hash)); 
		// END SHA GENERATION ////////////////////////////////

		$revised_post_array['SHASign'] = $sha1_hash;
		$data		= $this->data_array_to_string($revised_post_array);
		$connect 	= $this->curl_transaction($this->_host,$data); 
		
		$resp['authorized'] 	= FALSE; 
		$resp['declined']		= FALSE; 
		$resp['processing']		= FALSE; 
		$resp['failed']			= TRUE; 
		$resp['error_message']	= NULL;
		$resp['transaction_id']	= NULL;
		
		if (!$connect)
		{
			$resp['error_message']	= $this->lang('curl_gateway_failure'); 
			return $resp; 
		}
		
		$result = xml_to_array($connect); 
		
		switch(  substr($result['ncresponse']['NCERROR'], 0, 1)  ) 
		{
			case '5':
				$resp['error_message']  = $this->lang('ogone_error_incomplete'); 
				$resp['declined']		= FALSE; 
				$resp['failed']			= TRUE;
				break; 
			case '3':	 
				$resp['error_message']  = $this->lang('ogone_error_refused'); 
				$resp['declined']		= TRUE; 
				$resp['failed']			= FALSE;
				break;
			case '0':	
				$resp['authorized'] 	= TRUE; 
				$resp['error_message']	= NULL; 
				$resp['transaction_id']	= $result['ncresponse']['PAYID'];
				
				break;
			default: 
				$resp['declined']		= FALSE; 
				$resp['failed']			= TRUE;
				$resp['error_message']  = $this->lang('ogone_error_unknown') . ' ' . $result['ncresponse']['NCERROR'];
		}
		
		return $resp;
	}
	// END 
}
// END Class