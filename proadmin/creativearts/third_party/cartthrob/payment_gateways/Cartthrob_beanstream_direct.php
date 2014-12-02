<?php 
class Cartthrob_beanstream_direct extends Cartthrob_payment_gateway
{
	public $title = 'beanstream_title';
	public $language_file = TRUE;
	
	public $settings = array(
		array(
			'name' => 'merchant_id', 
			'short_name' => 'merchant_id', 
			'type' => 'text',  
		)
	);
	
	public $required_fields = array(
		'first_name',
		'last_name',
		'expiration_month',
		'expiration_year',
		'email_address',
		'address',
		'city',
		'state',
		'zip',
		'country_code', 
		'credit_card_number',
		'phone'
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
		'company',
		'phone',
		'email_address',
		'card_type',
		'credit_card_number',
		'CVV2',
		'expiration_month',
		'expiration_year'
	);
	
 	public $card_types = array(
		'visa',
		'mc',
		'amex',
		'discover',
		'diners',
		'jcb',
		'sears'
		);
 
	public function initialize()
	{
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
		
		$post_array = array(
			'requestType'			=> 'BACKEND', 
			'merchant_id'			=> $this->plugin_settings('merchant_id'),
			'trnOrderNumber'		=> $this->order('entry_id'),
			'trnAmount'				=> $this->total(),
			'trnCardOwner'			=> $this->order('first_name'). " " . $this->order('last_name'),
			'trnCardNumber'			=> $credit_card_number,
			'trnExpMonth'			=> $this->order('expiration_month'),
			'trnExpYear'			=> $this->order('expiration_year'),
			'trnCardCvd'			=> $this->order('CVV2'),
			'ordName'				=> $this->order('first_name'). " " . $this->order('last_name'),
			'ordEmailAddress'		=> $this->order('email_address'),
			'ordPhoneNumber'		=> $this->order('phone'),
			'ordAddress1'			=> $this->order('address'),
			'ordAddress2'			=> $this->order('address2'),
			'ordCity'				=> $this->order('city'),
			'ordProvince' 			=> ($this->order('state')) ? $this->order('state') : '--', 
			'ordPostalCode'			=> $this->order('zip'),
			//'termURL'				=> "",
			'ordCountry'			=> $this->alpha2_country_code($this->order('country_code')),
			);
						
		$data = $this->data_array_to_string($post_array);
		$connect = $this->curl_transaction('https://www.beanstream.com/scripts/process_transaction.asp', $data);
		
		if (!$connect)
		{
			$auth['failed'] 		= TRUE;
			$auth['error_message']	= $this->lang('curl_gateway_failure');	
			return $auth; 
		}
		
		$response = $this->split_url_string($connect);
		
		$error_text = str_replace(array("&lt;LI&gt;","&lt;br&gt;"), " ", $response['messageText']);
			
		switch ($response['messageId'])
		{
			case "16": 
				// duplicate transaction
				$auth['authorized'] 	= 	FALSE; 
				$auth['declined']		=	FALSE; 
				$auth['failed']			=	TRUE; 
				$auth['error_message']	= 	$error_text; 
				$auth['transaction_id']	=	NULL;
				return $auth;
				break;
			case "1": 
				$auth['authorized'] 	= 	TRUE; 
				$auth['declined']		=	FALSE; 
				$auth['failed']			=	FALSE; 
				$auth['error_message']	= 	""; 
				$auth['transaction_id']	=	$response['trnId'];
				return $auth;
				break;
		}
		switch($response['errorType']){
			case "U": 
				// validation errors
				$auth['authorized'] 	= 	FALSE; 
				$auth['declined']		=	FALSE; 
				$auth['failed']			=	TRUE; 
				$auth['error_message']	= 	$error_text;
				$auth['transaction_id']	=	NULL;
				return $auth; 
				break;
			case "S":
				// system errors (missing merchant id, etc)
				$auth['authorized'] 	= 	FALSE; 
				$auth['declined']		=	FALSE; 
				$auth['failed']			=	TRUE; 
				$auth['error_message']	= 	$error_text;
				$auth['transaction_id']	=	NULL;
				return $auth; 
				break;
			
			default: 
				$auth['authorized'] 	= 	FALSE; 
				$auth['declined']		=	FALSE; 
				$auth['failed']			=	TRUE; 
				$auth['error_message']	= 	$error_text; 
				$auth['transaction_id']	=	NULL;
				return $auth; 
		}
		return $auth;
	}
	// END
}
// END Class