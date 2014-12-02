<?php
// BASIC SAMPLE TEMPLATE FOR GENERATING PAYMENT PLUGINS.

/* AVAILABLE CUSTOMER FIELDS
 * If your required customer field is not in this list, it will not be processed. 
 * Please contact the CartThrob development team at http://livecart.com, to have additional fields added. 

'first_name',
'last_name',
'address',
'address2',
'city',
'state',
'zip',
'description',
'company',
'phone',
'email_address',
'shipping_first_name',
'shipping_last_name',
'shipping_address',
'shipping_address2',
'shipping_city',
'shipping_state',
'shipping_zip',
'expiration_month',
'expiration_year',
'begin_month',
'begin_year',
'bday_month',
'bday_day',
'bday_year',
'CVV2',
'card_code',
'issue_number',
'card_type',
'currency_code',
'country_code',
'shipping_option',
'credit_card_number'

*/

class Cartthrob_dev_template extends Cartthrob_payment_gateway
{
	public $title = 'dev_template_title';
	public $affiliate = '';
	public $overview = 'dev_template_overview';
		// THESE SETTINGS WILL GENERATE THE INPUT FIELDS ON THE PAYMENT CONFIGURE SCREEN
	public $settings = array(
		array(
			'name' => 'Mode', 
			'short_name' => 'mode', 
			'type'	=> 'select',
			'default'	=> 'random',
			'options' => array(
				'random'	=> 'dev_mode_random',
				'always_fail' => 'dev_mode_fail',
				'always_decline' => 'dev_mode_decline', 
				'always_succeed' => 'dev_mode_succeed'
				),  
		)
	);
	// These fields will be required in the checkout form when a payment is submitted to this gateway
	public $required_fields = array(
		'first_name',
		'last_name',
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
		'country_code',
 	);
	
	
	/**
	 * _process_payment function
	 *
 	 * @param string $credit_card_number purchaser's credit cart number
 	 * @access public
	 * @return array $resp an array containing the following keys: authorized, declined, failed, error_message, and transaction_id 
	 * the returned fields can be displayed in the templates using template tags. 
	 **/
	public function charge($credit_card_number)
	{
		// DO PAYMENT STUFF
		/* 
		helper functions
		// converts an array to a urlencoded string of name / value pairs.
		$data = 	$this->data_array_to_string($array);
		* 
		// connects to curl. pass in the curl server url, and a url encoded data string... whatever data is required by whoever you're sending it to
		$connect = 	$this->curl_transaction($curl_server,$data);
		* 
		// Splits a URL encoded string of name / value pairs into an array.
		$transaction =  $this->split_url_string($connect);
		* 
		// converts XML to an array. 
		$transaction = $this->convert_response_xml($connect);
		* 
		// for 3-d secure and other offsite payment gateways, you'll want to use this. 
		$redirect_url = $this->get_notify_url(__CLASS__, 'my_return_processing_method_name' );
		
		//retrive input
		$data = $this->input_post('something');
		$data = $this->input_get('something');
		
		//convert a language key
		$this->lang('key');
		*/
		
		return $this->random_response(); 
	}
	
	/**
	 * _random_response
	 *
	 * this generates random booleans, error_messages, and transaction ids for testing purposes. 
	 * do not use this function with a real payment gateway.
	 * 
	 * @return array
	 * @since 1.0.0
	 * @author Chris Newton
	 */
	private function random_response()
	{
		$random_responses=array();

		$bools_array = array(TRUE,FALSE);
		$errors_array= array(
			$this->lang('dev_template_error_1'),
			$this->lang('dev_template_error_2'),
			$this->lang('dev_template_error_3'),
			$this->lang('dev_template_error_4'),
		);
		
		$ids_array = array(rand(10000000,99999999),rand(10000000,99999999));
		
		$random_responses['bool1'] = $bools_array[array_rand($bools_array)];
		$random_responses['bool2'] = $bools_array[array_rand($bools_array)];
		
		if ($random_responses['bool1'])
		{
			$random_responses['bool3'] = FALSE;
		}
		else
		{
			$random_responses['bool3'] = TRUE;
			
		}
		
		switch($this->plugin_settings('mode'))
		{
			case "always_fail":
				$random_responses['transaction_id'] ="NULL"; 
				$random_responses['bool2'] = FALSE; 
				$random_responses['bool3'] = TRUE; 
				$random_responses['bool1'] = FALSE; 
				$random_responses['error_message'] = $errors_array[array_rand($errors_array)];
			break;
			case "always_succeed": 
				$random_responses['bool2'] = FALSE; 
				$random_responses['bool3'] = FALSE; 
				$random_responses['bool1'] = TRUE;
				$random_responses['transaction_id'] =$ids_array[array_rand($ids_array)];
				$random_responses['error_message'] = NULL; 
			break;
			
			case "always_decline":
				$random_responses['transaction_id'] =NULL; 
				$random_responses['bool2'] = TRUE; 
				$random_responses['bool3'] = FALSE; 
				$random_responses['bool1'] = FALSE;
				$random_responses['error_message'] = $errors_array[array_rand($errors_array)];
			break;
			default: 
				$random_responses['transaction_id'] = $ids_array[array_rand($ids_array)];
				$random_responses['error_message'] = $errors_array[array_rand($errors_array)];
			
		}
		
		// THESE ARRAY KEYS WILL USE DEFAULTS IF NOT SET. THEY ARE USED TO DISPLAY MESSAGING IN TEMPLATES & TO PASS DATA TO THE ORDERS BLOG
		// IF THIS IS NOT BLANK, TRANS IS GOOD; DECLINED & FAILED BELOW ARE IGNORED

		$resp['authorized'] = $random_responses['bool1'];

		// OTHERWISE THE PLUGIN WILL REDIRECT BASED ON THE FOLLOWING CONDITIONS (in this order)
		$resp['declined']		=	 $random_responses['bool2'];
		// A FAILED RESPONSE MEANS THAT THE REASON USUALLY HAS TO DO WITH THE GATEWAY EXPERIENCING A PROBLEM
		$resp['failed']			=	 $random_responses['bool3'];
		// THIS ERROR MESSAGE CAN BE DISPLAYED IN THE TEMPLATE AS NECESSARY
		$resp['error_message']	=	$random_responses['error_message'];
		
		// THE TRANS ID (if available) IF NO TRANSID IS RETURNED A TIME STAMP IS USED. 
		$resp['transaction_id']	=	$random_responses['transaction_id'];
		//var_dump($random_responses);var_dump($resp); exit();
		
		return $resp;
	}
	// END
	
	public function create_token($credit_card_number)
	{
		return new Cartthrob_token(array('token' => uniqid('', TRUE)));
	}
	
	public function charge_token($token)
	{
		return $this->random_response(); 
	}
}// END CLASS

/* End of file cartthrob.dev_template.php */
/* Location: ./system/modules/payment_gateways/cartthrob.dev_template.php */