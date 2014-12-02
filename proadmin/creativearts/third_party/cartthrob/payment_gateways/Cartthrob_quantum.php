<?php 
class Cartthrob_quantum extends Cartthrob_payment_gateway
{
	public $title = 'quantum_title';
 	public $overview = 'quantum_overview';
	public $language_file = TRUE;

 	public $settings = array(
		array(
			'name' =>  'quantum_gateway_login',
			'short_name' => 'gateway_login',
			'type' => 'text'
		),
		array(
			'name' =>  'quantum_restrict_key',
			'short_name' => 'restrict_key',
			'type' => 'text'
		),
		array(
			'name' =>  'quantum_email_customer',
			'short_name' => 'email_customer',
			'type' => 'radio'
		)
	);
	
	public $required_fields = array(
		'address',
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
		'shipping_first_name',
		'shipping_last_name',
		'shipping_address',
		'shipping_address2',
		'shipping_city',
		'shipping_state',
		'shipping_zip',
		'company',
		'phone',
		'email_address',
		'card_type',
		'credit_card_number',
		'CVV2',
		'expiration_month',
		'expiration_year',
	);
	
	public $hidden = array('description'); 
	
 
	
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
 		$this->_host			= "https://secure.quantumgateway.com/cgi/authnet_aim.php";
		
		$resp['authorized']	 	= FALSE; 
		$resp['declined'] 		= FALSE; 
		$resp['transaction_id']	= NULL;
		$resp['failed']			= TRUE; 
		$resp['error_message']	= "";
		
		$post_array = array(
			"x_login"         				=> $this->plugin_settings('gateway_login'),
			"x_tran_key"           			=> $this->plugin_settings('restrict_key'),
			"x_first_name"       	     	=> $this->order('first_name'),
			"x_last_name"       	     	=> $this->order('last_name'),
			"x_address"      		      	=> $this->order('address')." ". $this->order('address2'),
			"x_city"            	    	=> $this->order('city'),
			"x_state"              		  	=> $this->order('state'),
			"x_description"					=> $this->order('description'),
			"x_zip"            		    	=> $this->order('zip'),
			"x_country"            		   	=> $this->alpha2_country_code($this->order('country_code')),
			'x_ship_to_first_name'			=> ($this->order('shipping_first_name') ? $this->order('shipping_first_name') : $this->order('first_name')),
			'x_ship_to_last_name'			=> ($this->order('shipping_last_name') ? $this->order('shipping_last_name') : $this->order('last_name')),
			'x_ship_to_address'				=> ($this->order('shipping_address') ? $this->order('shipping_address')." ".$this->order('shipping_address2') : $this->order('address')." ".$this->order('address2')),
			'x_ship_to_city'				=> ($this->order('shipping_city') ? $this->order('shipping_city') : $this->order('city')),
			'x_ship_to_state'				=> ($this->order('shipping_state') ? $this->order('shipping_state') : $this->order('state')),
			'x_ship_to_zip'					=> ($this->order('shipping_zip') ? $this->order('shipping_zip') : $this->order('zip')),
			"x_phone"          		      	=> $this->order('phone'),
			"x_email"          		      	=> $this->order('email_address'),
			"x_cust_id"          		   	=> $this->order('member_id'),
			"x_invoice_num"					=> time().strtoupper(substr($this->order('last_name'), 0, 3)),
			"x_company"						=> $this->order('company'),
			"x_email_customer"    		 	=> ($this->plugin_settings('email_customer') ? "TRUE" : "FALSE"),
			"x_amount"               	 	=> $this->total(),
			"x_method"               	 	=> "CC",
			"x_type"                 		=> 'AUTH_CAPTURE',  // set to AUTH_CAPTURE for money capturing transactions
			"x_card_num"             		=> $credit_card_number,
			"x_card_code"             		=> $this->order('CVV2'),
			"x_exp_date"             		=> $this->order('expiration_month').'/'.$this->year_2($this->order('expiration_year')),
	     );
	
		reset($post_array);
		$data='';
		while (list ($key, $val) = each($post_array)) 
		{
			$data .= $key . "=" . urlencode($val) . "&";
		}
		
		$connect = $this->curl_transaction($this->_host, $data);

		if (!$connect)
		{
			
			$resp['failed']	 		= 	TRUE ; 
			$resp['authorized']		=	FALSE;
			$resp['declined']		=	FALSE;
			$resp['error_message']	=	$this->lang('curl_gateway_failure');
			
			return $resp; 
		}
		
		if (strpos($connect, "3Key Mismatch") !== FALSE)
		{
			$resp['failed']	 		= 	TRUE ; 
			$resp['authorized']		=	FALSE;
			$resp['declined']		=	FALSE;
			$resp['error_message']	=	$this->lang('quantum_three_key_error');
			
			return $resp;
		}
		
		$response = explode(",",$connect);
		$response = explode("|", $response[0]);
		
		// var_dump($response); exit(); 
		if ($connect{0} == 1) 
		{
			$resp['authorized']	= TRUE;
			$resp['transaction_id']	=	@$response[6];
			$resp['failed']			= FALSE; 
			
		}
		elseif($connect{0} == 2)
		{
			$resp['authorized'] 	= FALSE;
			$resp['declined']		= TRUE;
			$resp['return_error'] 	= $response;
			$resp['error_message'] 	= @$response[3];
			$resp['failed']			= FALSE; 
			
		}
		elseif($connect{0} == 3)
		{
			$resp['authorized'] 	= FALSE;
			$resp['declined']		= FALSE;
			$resp['return_error'] 	= $response;
			$resp['error_message']	= @$response[3];
			$resp['failed']			= TRUE; 
			
		}
		else
		{
			$resp['authorized']		= FALSE;
			$resp['declined']		= FALSE;
			$resp['error_message'] 	= $this->lang('quantum_problem_connecting');
			$resp['return_error']	= $response;
			$resp['failed']			= TRUE; 
			
		}
		
		return $resp;
	}
	// END
}
// END Class