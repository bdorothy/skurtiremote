<?php 
class Cartthrob_linkpoint extends Cartthrob_payment_gateway
{
	public $title = 'linkpoint_title';
 	public $overview = 'linkpoint_overview';
	public $language_file = TRUE;
	public $settings = array(
		array(
			'name' => 'linkpoint_store_number',
			'short_name' => 'store_number',
			'type' => 'text'
		),
		array(
			'name' => 'linkpoint_keyfile',
			'short_name' => 'keyfile',
			'default'	=> 'yourcert_file_name.pem',
			'type' => 'text'
		),
		array(
			'name' => 'linkpoint_test_store_number',
			'short_name' => 'test_store_number',
			'type' => 'text'
		),
		array(
			'name' => 'linkpoint_test_keyfile',
			'short_name' => 'test_keyfile',
			'default'	=> 'yourcert_file_name.pem',
			'type' => 'text'
		),
		array(
			'name' => 'mode',
			'short_name' => 'test_mode',
			'default'	=> 'good',
			'type' => 'select',
			'options' => array(
				'live' => "live",
				'good' => 'linkpoint_test_good',
				'decline' => 'linkpoint_test_decline',
				'duplicate' => 'linkpoint_test_duplicate'
				)
		)
	);
	
	public $required_fields = array(
		'credit_card_number',
		'expiration_month',
		'expiration_year',
		'first_name',
		'last_name',
		'address',
		'city',
		'state',
		'zip',
		'country_code',
		'phone',
		'email_address'
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
		'phone',
		'email_address',
		'card_type',
		'credit_card_number',
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
		if ($this->plugin_settings('test_mode') == "live")
		{
			$this->_host = "secure.linkpt.net"; 				
		}
		else
		{
			$this->_host = "staging.linkpt.net"; 
			
		}
		$this->_port = "1129"; 
		$this->_path = "/LSGSXML";
		
		
		if ($this->plugin_settings('test_mode') == "live") 
		{
			$live="live";
			$keyfile = $this->plugin_settings('keyfile'); 
			$store_number = $this->plugin_settings('store_number'); 
		}
		else
		{
			$live=$this->plugin_settings('test_mode');
			$keyfile = $this->plugin_settings('test_keyfile'); 
			$store_number = $this->plugin_settings('test_store_number'); 
 		}
		$xml="<order>"; 

		$xml.="<merchantinfo>";
			$xml.="<configfile>".$store_number."</configfile>";
			$xml.="<keyfile>".$keyfile."</keyfile>";
			$xml.="<host>".$this->_host."</host><port>".$this->_port."</port>";
		$xml.="</merchantinfo>";

		$xml.="<orderoptions>";
			$xml.="<ordertype>Sale</ordertype>";
			$xml.="<result>".$live."</result>";
		$xml.="</orderoptions>";

		$xml.="<payment>";
			$xml.="<chargetotal>".$this->total()."</chargetotal>";
		$xml.="</payment>";

		$xml.="<creditcard>";
			$xml.="<cardnumber>".$credit_card_number."</cardnumber>";
			$xml.="<cardexpmonth>".str_pad($this->order('expiration_month'), 2, '0', STR_PAD_LEFT)."</cardexpmonth>";
			$xml.="<cardexpyear>".$this->year_2($this->order('expiration_year'))."</cardexpyear>";
		if ( $this->order('CVV2'))
		{
			$xml.="<cvmvalue>".$this->order('CVV2')."</cvmvalue>";
			$xml.="<cvmindicator>provided</cvmindicator>";
		}
		else
		{
			$xml.="<cvmindicator>not_provided</cvmindicator>";
		}

		$xml.="</creditcard>";

		$xml.="<billing>";
			$xml.="<name>".$this->order('first_name')." ".$this->order('last_name')."</name>";
			$xml.="<address1>".$this->order('address')." ".$this->order('address2')."</address1>";
			$xml.="<company>".$this->order('company')."</company>";
			$xml.="<address2>".$this->order('address2')."</address2>";
			$xml.="<city>".$this->order('city')."</city>";
			$xml.="<state>".$this->order('state')."</state>";
			$xml.="<zip>".$this->order('zip')."</zip>";
			$xml.="<country>".$this->alpha2_country_code($this->order('country_code'))."</country>";
			$xml.="<phone>".$this->order('phone')."</phone>";
			$xml.="<email>".$this->order('email_address')."</email>";
		$xml.="</billing>";

		$xml.="<shipping>";
			$xml.="<name>".$this->order('shipping_first_name')." ".$this->order('shipping_last_name')."</name>";
			$xml.="<address1>".$this->order('shipping_address')." ".$this->order('shipping_address2')."</address1>";
			$xml.="<city>".$this->order('shipping_city')."</city>";
			$xml.="<state>".$this->order('shipping_state')."</state>";
			$xml.="<zip>".$this->order('shipping_zip')."</zip>";
			$xml.="<country>".$this->alpha2_country_code($this->order('shipping_country_code'))."</country>";
		$xml.="</shipping>";

		$xml.="<transactiondetails>";
			$xml.="<oid>".$this->order('entry_id')."</oid>";
		$xml.="</transactiondetails>";

		$xml .="</order>";
		
		$curl_array = array(
			"https://". $this->_host.":".$this->_port.$this->_path,
			$xml,
			array(
				CURLOPT_POST => 1,
				CURLOPT_SSLCERT => $keyfile,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_SSL_VERIFYPEER => 0
				)
			); 
		
		$connect = $this->curl_post($curl_array); 
 
		if (!$connect || strlen($connect)<2)
		{
			$resp['error_message'] = $this->lang('curl_gateway_failure');
			return $resp; 
		}

		// Not using CT's standard xml_to_array because the data that's returned isn't properly formatted XML
		preg_match_all ("/<(.*?)>(.*?)\</", $connect, $outarr, PREG_SET_ORDER);
		$count = 0;
		while (isset($outarr[$count]))
		{
			$transaction[$outarr[$count][1]] = strip_tags($outarr[$count][0]);
			$count++; 
		}
		

		$resp = array(
			'error_message'		=> NULL,
			'authorized'		=> FALSE,
			'failed'			=> TRUE,
			'declined'			=> FALSE,
			'transaction_id'	=> NULL
			);
			
		if (!$transaction['r_error'] && $transaction["r_approved"] == "APPROVED") 
		{
			$resp = array(
				'error_message'		=> NULL,
				'authorized'		=> TRUE,
				'failed'			=> FALSE,
				'declined'			=> FALSE,
				'transaction_id'	=> @$transaction["r_ref"]
				);
		}
		else
		{
			$resp = array(
				'error_message'		=> @$transaction["r_error"]  ,
				'authorized'		=> FALSE,
				'failed'			=> FALSE,
				'declined'			=> TRUE,
				'transaction_id'	=> NULL
				);
		}

		return $resp;
	}
 	// END
}
// END Class