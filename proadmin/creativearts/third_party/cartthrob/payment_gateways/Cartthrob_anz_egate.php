<?php
class Cartthrob_anz_egate extends Cartthrob_payment_gateway
{
	public $title = 'anz_egate_title';
	public $affiliate = ''; 
	public $overview = 'anz_egate_overview'; 
	public $language_file = TRUE;
	public $settings = array(
		array(
			'name' => 'anz_egate_access_code',
			'short_name' => 'access_code',
			'type' => 'text'
		),
		array(
			'name' => 'anz_egate_merchant_id',
			'short_name' => 'merchant_id',
			'type' => 'text'
		)
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
		'country_code',
		'phone',
		'email_address',
		'company',
		'description',
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
	
	public $hidden = array('description');
	public $card_types = array(
		'diners',
		'amex',
		'mc',
		'visa'
		);
 
	/**
	 * process_payment function
	 *
	 * @param string $credit_card_number purchaser's credit cart number
 	 * @access public
	 * @return array $resp an array containing the following keys: authorized, declined, failed, error_message, and transaction_id 
	 * the returned fields can be displayed in the templates using template tags. 
	 **/
	function charge($credit_card_number)
	{
		$total = round($this->total()*100);
		$post_array = array(
			'vpc_Version'				=> '1',
			'vpc_Command'				=> 'pay',
			'vpc_MerchTxnRef'			=> $this->order('entry_id')."-". strftime("%Y%m%d%H%M%S"),
			'vpc_AccessCode'			=> $this->plugin_settings('access_code'),
			'vpc_Merchant'				=> $this->plugin_settings('merchant_id'),
			'vpc_OrderInfo'				=> $this->order('entry_id'),
			'vpc_Amount'				=> $total,
			'vpc_CardNum'				=> $credit_card_number,
			'vpc_CardExp'				=> $this->year_2($this->order('expiration_year')).str_pad($this->order('expiration_month'), 2, '0', STR_PAD_LEFT),
			'vpc_CardSecurityCode'		=> $this->order('CVV2'),
	     );
		
		$data = 	$this->data_array_to_string($post_array);
 
		$connect = 	$this->curl_transaction("https://migs.mastercard.com.au/vpcdps", $data); 

		$resp['authorized']	 	= FALSE; 
		$resp['declined'] 		= FALSE; 
		$resp['transaction_id']	= NULL;
		$resp['failed']			= TRUE; 
		$resp['error_message']	= $this->lang('anz_egate_error_7'). " ". $this->lang('anz_egate_cant_connect');
		
		if (!$connect)
		{
			return $resp; 
		}
		elseif(strchr($connect,"<html>"))
		{
			$resp['error_message'] = $connect;
			return $resp;
		}
		$transaction =  $this->split_url_string($connect);
		
		if ($transaction['vpc_TxnResponseCode'] !="0")
		{
			$resp['error_message'] = $this->getResponseDescription($transaction['vpc_TxnResponseCode']) . " " . @$transaction['vpc_Message'];
			return $resp; 
		}
		else
		{
			$this->update_order(array('authorize_id' => $transaction['vpc_AuthorizeId']));
	  		
			$resp['authorized']	 	= TRUE; 
			$resp['declined'] 		= FALSE; 
			$resp['transaction_id']	= $transaction['vpc_TransactionNo'];
			$resp['failed']			= FALSE; 
			$resp['error_message']	= NULL;
			
			return $resp; 
		}
	}
	// END _process_payment
	//  ----------------------------------------------------------------------------

	// This function uses the Transaction Response code retrieved from the Digital
	// Receipt and returns an appropriate description for the vpc_TxnResponseCode

	// @param $responseCode String containing the vpc_TxnResponseCode

	// @return String containing the appropriate description

	function getResponseDescription($responseCode) {
		
	    switch ($responseCode) {
	        case "0" : $result = $this->lang('anz_egate_error_0'); break;
	        case "?" : $result = $this->lang('anz_egate_error_question'); break;
	        case "1" : $result = $this->lang('anz_egate_error_1'); break;
	        case "2" : $result = $this->lang('anz_egate_error_2'); break;
	        case "3" : $result = $this->lang('anz_egate_error_3'); break;
	        case "4" : $result = $this->lang('anz_egate_error_4'); break;
	        case "5" : $result = $this->lang('anz_egate_error_5'); break;
	        case "6" : $result = $this->lang('anz_egate_error_6'); break;
	        case "7" : $result = $this->lang('anz_egate_error_7'); break;
	        case "8" : $result = $this->lang('anz_egate_error_8'); break;
	        case "9" : $result = $this->lang('anz_egate_error_9'); break;
	        case "A" : $result = $this->lang('anz_egate_error_A'); break;
	        case "C" : $result = $this->lang('anz_egate_error_C'); break;
	        case "D" : $result = $this->lang('anz_egate_error_D'); break;
	        case "F" : $result = $this->lang('anz_egate_error_F'); break;
	        case "I" : $result = $this->lang('anz_egate_error_I'); break;
	        case "L" : $result = $this->lang('anz_egate_error_L'); break;
	        case "N" : $result = $this->lang('anz_egate_error_N'); break;
	        case "P" : $result = $this->lang('anz_egate_error_P'); break;
	        case "R" : $result = $this->lang('anz_egate_error_R'); break;
	        case "S" : $result = $this->lang('anz_egate_error_S'); break;
	        case "T" : $result = $this->lang('anz_egate_error_T'); break;
	        case "U" : $result = $this->lang('anz_egate_error_U'); break;
	        case "V" : $result = $this->lang('anz_egate_error_V'); break;
	        default  : $result = $this->lang('anz_egate_response_code'). ": ". @$responseCode.". ";
	    }
	    return $result;
	}
}
// END Class