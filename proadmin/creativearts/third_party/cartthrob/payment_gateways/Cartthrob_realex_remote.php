<?php 

class Cartthrob_realex_remote extends Cartthrob_payment_gateway
{
	public $title = 'realex_remote_title';
	public $overview = 'realex_remote_overview';
	public $affiliate = 'realex_remote_affiliate';
	public $language_file = TRUE;
	public $settings = array(
		array(
			'name' => "realex_merchant_id",
			'short_name' => 'your_merchant_id',
			'type' => 'text'
		),
		array(
			'name' => "realex_your_secret",
			'short_name' => 'your_secret',
			'type' => 'text'
		)
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
		'card_type',
		'credit_card_number',
		'CVV2',
		'issue_number',
		'expiration_month',
		'expiration_year'
		); 
	public $hidden = array('currency_code'); 
	
	public $card_types = array(
		'mc',
		'visa',
		'amex',
		'switch',
		'laser',
		'diners'
		); 
	
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
		$currency_code = ($this->order('currency_code')) ? $this->order('currency_code') : "EUR";
		
		switch ($this->order('card_type'))
		{
			case "mc": 
				$card_type="MC"; 
				break;
			case 'diners': 
				$card_type="DINERS"; 
				break;
			case 'visa': 
				$card_type="VISA"; 
				break;
			case 'amex': 
				$card_type="AMEX"; 
				break;
			case 'switch': 
				$card_type="SWITCH"; 
				break;
			case 'laser': 
				$card_type="LASER"; 
				break;
			default: $card_type="VISA";  
		}
		
		$this->_host = "https://epage.payandshop.com/epage-remote.cgi"; 
		
		$timestamp = strftime("%Y%m%d%H%M%S");
		mt_srand((double)microtime()*1000000);

		$orderid = $this->order('entry_id')."-".$timestamp;

		$rounded_total = round($this->total()*100);
		
		$tmp = $timestamp.".".$this->plugin_settings('your_merchant_id').".".$orderid.".".$rounded_total.".".$currency_code.".".$credit_card_number;

		$md5hash = md5($tmp);

		$tmp = $md5hash.".".$this->plugin_settings('your_secret');

		$md5hash = md5($tmp);

		$xml = "<request type='auth' timestamp='$timestamp'>
			<merchantid>".$this->plugin_settings('your_merchant_id')."</merchantid>
			<orderid>".$orderid."</orderid>
			<amount currency='".$currency_code."'>".$rounded_total."</amount>
			<card> 
				<number>".$credit_card_number."</number>
				<expdate>".$this->order('expiration_month').$this->year_2($this->order('expiration_year'))."</expdate>
				<type>".$card_type."</type>";

				if ($this->order('member_id'))
				{
					$xml .="	<issueno>".$this->order('issue_number')."</issueno>";	
				}	

				$xml.="		<chname>".$this->order('first_name')." ".$this->order('last_name')."</chname> 
				<cvn>
					<number>".$this->order('CVV2')."</number>
					<presind>1</presind>
				</cvn>
			</card> 
			<autosettle flag='1'/>
			<md5hash>".$md5hash."</md5hash>
			<tssinfo>";
			if ($this->order('member_id'))
			{
				$xml .="		<custnum>".$this->order('member_id')."</custnum>";	
			}

			$xml .="		<address type='billing'>
					<code>".$this->order('zip')."</code>
					<country>".($this->order('country_code') ? $this->alpha2_country_code($this->order('country_code')) : "IE")."</country>
				</address>
			</tssinfo>
		</request>";


		$connect = 	$this->curl_transaction($this->_host,$xml); 
		if (!$connect)
		{
			$resp['failed']	  		= 	TRUE; 
			$resp['authorized']		=	FALSE;
			$resp['declined']		=	FALSE;
			$resp['error_message']	=	$this->lang('curl_gateway_failure'); 

			return $resp; 
		}

		$data_array = $this->xml_to_array($connect); 

		if ($data_array['response']['result']['data'] == "00")
		{
			$resp['authorized']	 	= TRUE; 
			$resp['declined'] 		= FALSE; 
			$resp['transaction_id']	= $data_array['response']['authcode']['data']; 
			$resp['failed']			= FALSE; 
			$resp['error_message']	= '';
		}
		elseif($data_array['response']['result']['data'] == "101" || $data_array['response']['result']['data'] == "102" || $data_array['response']['result']['data'] == "103")
		{
			$resp['authorized']	 	= FALSE; 
			$resp['declined'] 		= TRUE; 
			$resp['transaction_id']	= "";
			$resp['failed']			= FALSE; 
			$resp['error_message']	= $data_array['response']['message']['data'];
		}
		elseif($data_array['response']['result']['data'] == "108")
		{
			$resp['authorized']	 	= FALSE; 
			$resp['declined'] 		= FALSE; 
			$resp['transaction_id']	= "";
			$resp['failed']			= TRUE; 
			$resp['error_message']	= $data_array['response']['message']['data'];
		}
		else
		{
			$resp['authorized']	 	= FALSE; 
			$resp['declined'] 		= FALSE; 
			$resp['transaction_id']	= NULL;
			$resp['failed']			= TRUE; 
			$resp['error_message']	= $data_array['response']['message']['data'];
		}

		return $resp;
	}
	// END
}
// END Class