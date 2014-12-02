<?php 
class Cartthrob_sage_us extends Cartthrob_payment_gateway
{
	public $title = 'sage_us_title';
	public $language_file = TRUE;
	
 	public $settings = array(
		array(
			'name' =>  'mode',
			'short_name' => 'test_mode', 
			'type' => 'radio', 
			'default' => 'test',
			'options' => array(
				'test' => 'test',
				'live' => 'live'
			)
		),
		
		array(
			'name' =>  'sage_us_merchant_id',
			'short_name' => 'm_id',
			'type' => 'text'
		),
		array(
			'name' => 'sage_us_merchant_key',
			'short_name' => 'm_key',
			'type' => 'text'
		),
		
		
		array(
			'name' => 'sage_us_test_id',
			'short_name' => 'm_test_id',
			'type' => 'text'
		),
		array(
			'name' => 'sage_us_test_key',
			'short_name' => 'm_test_key',
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
		'credit_card_number'   ,
		'CVV2'                 ,
		'expiration_month'     ,
		'expiration_year'      ,
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
 		$this->_host = "https://www.sagepayments.net/cgi-bin/eftBankcard.dll?transaction";
		
		$post_array = array(
			'M_id'					=> (($this->plugin_settings('test_mode') == "test") ? $this->plugin_settings('m_test_id') : $this->plugin_settings('m_id')),
			'M_key'					=> (($this->plugin_settings('test_mode') == "test") ? $this->plugin_settings('m_test_key') : $this->plugin_settings('m_key')),
			'C_name'				=> $this->order('first_name') ." ". $this->order('last_name'),
			'C_address'				=> $this->order('address')." ". $this->order('address2'),
			'C_city'				=> $this->order('city'),
			'C_state'				=> $this->order('state'),
			'C_zip'					=> $this->order('zip'),
			'C_country'				=> $this->order('country_code'),
			'C_email'				=> $this->order('email_address'),
			'C_cardnumber'			=> $credit_card_number,
			'C_exp'					=> $this->order('expiration_month').$this->order('expiration_year'),
			'T_amt'					=> $this->total(),
			'T_code'				=> '01', // Sale
			'T_ordernum'			=> $this->order('entry_id')."_" .time(),
			'C_cvv'					=> $this->order('CVV2'),
			'T_tax'					=> $this->order('tax'),
			'T_shipping'			=> $this->order('shipping'),
			'C_ship_name'			=> $this->order('shipping_first_name') ." ". $this->order('shipping_last_name'),
			'C_ship_address'		=> $this->order('shipping_address')." ". $this->order('shipping_address2'),
			'C_ship_city'			=> $this->order('shipping_city'),
			'C_ship_state'			=> $this->order('shipping_state'),
			'C_ship_zip'			=> $this->order('shipping_zip'),
			'C_ship_country'		=> $this->order('shipping_country_code'),
			'C_telephone'			=> $this->order('telephone'),
			//'C_fax'
		);
		
 		$data = 	$this->data_array_to_string($post_array);
		
		$connect = 	$this->curl_transaction($this->_host,$data); 

		$resp['authorized']	 	= FALSE; 
		$resp['declined'] 		= FALSE; 
		$resp['transaction_id']	= NULL;
		$resp['failed']			= TRUE; 
		$resp['error_message']	= "";
		
		if (!$connect)
		{
			$resp['error_message'] = $this->lang('curl_gateway_failure'); 
			return $resp; 
		}
		
		$transaction['Approval Indicator'] = $connect[1];
		$transaction['Message'] = substr($connect, 8, 32);
		$transaction['Reference'] = substr($connect, 46, 10);
		
		if ($transaction['Approval Indicator'] == 'A')
		{
			$resp['authorized']	 	= TRUE; 
			$resp['declined'] 		= FALSE; 
			$resp['transaction_id']	= $transaction['Reference'];
			$resp['failed']			= FALSE; 
			$resp['error_message']	= "";
		} 
		elseif ($transaction['Approval Indicator'] == 'E')
		{
			$resp['authorized']	 	= FALSE; 
			$resp['declined'] 		= TRUE; 
			$resp['transaction_id']	= NULL;
			$resp['failed']			= TRUE; 
			$resp['error_message']	= $transaction['Message'] ;
		}
		elseif ($transaction['Approval Indicator'] == 'X')
		{
			$resp['authorized']	 	= FALSE; 
			$resp['declined'] 		= TRUE; 
			$resp['transaction_id']	= NULL;
			$resp['failed']			= TRUE; 
			$resp['error_message']	= $transaction['Message'] ;
		}
		else
		{
			$resp['authorized']	 	= FALSE; 
			$resp['declined'] 		= FALSE; 
			$resp['transaction_id']	= NULL;
			$resp['failed']			= TRUE; 
			$resp['error_message']	= $this->lang('sage_us_general_error');
		}
		return $resp;
		
	}
	// END
}
// END Class