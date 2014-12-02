<?php 
class Cartthrob_ogone_alias extends Cartthrob_payment_gateway
{
	public $title = 'ogone_alias_title';
	public $overview = 'ogone_alias_overview';
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
			'name' => 'ogone_alias_pspid_live', 
			'short_name' => 'pspid_live', 
			'type' => 'text', 
		),
		array(
			'name' => 'ogone_alias_pspid_test', 
			'short_name' => 'pspid_test', 
			'type' => 'text', 
		),
		array(
			'name' => 'ogone_alias_api_user_id', 
			'short_name' => 'api_userid', 
			'type' => 'text', 
		),
		array(
			'name' => 'ogone_alias_api_password', 
			'short_name' => 'api_password', 
			'type' => 'text', 
		),
		array(
			'name' => 'ogone_alias_sha_passphrase', 
			'short_name' => 'passphrase', 
			'type' => 'text', 
		),
		array(
			'name'=>'ogone_alias_form_header',
			'short_name'=>'header_html_payment_form',
			'type'=>'select',
			'attributes' => array(
				'class' 	=> 'templates_blank',
				),
		),
		array(
			'name'=>'ogone_alias_form_footer',
			'short_name'=>'footer_html_payment_form',
			'type'=>'select',
			'attributes' => array(
				'class' 	=> 'templates_blank',
				),
		)
	);
	
	public $required_fields = array(
	);
	// we can't capture billing info, but we can at least get shipping fields
	public $fields = array(
		'shipping_first_name',
		'shipping_last_name',
		'shipping_address',
		'shipping_address2',
		'shipping_city',
		'shipping_state',
		'shipping_country',
		'shipping_zip',
		'email_address'
	);
	
	public $hidden = array(
	);

	public $card_types	= array(
		'mc',
		'visa',
		'amex',
		'maestro',
		'solo',
		'delta'
	); 
	public function initialize()
	{
		$this->EE =& get_instance(); 
		switch ($this->plugin_settings('mode'))
		{
			case "test": 
				$this->_alias_host = "https://secure.ogone.com/ncol/test/alias_gateway.asp";
				$this->_host = "https://secure.ogone.com/ncol/test/orderdirect.asp"; 
				$this->_pspid = $this->plugin_settings('pspid_test');
				break; 
			default: 
				$this->_alias_host = "https://secure.ogone.com/ncol/prod/alias_gateway.asp";
				$this->_host = "https://secure.ogone.com/ncol/prod/orderdirect.asp"; 
				$this->_pspid = $this->plugin_settings('pspid_live');
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
		
		$gateway = ucfirst(get_class($this));
		
		$post_array = array(
			'PSPID'				=> $this->_pspid,
			//'BRAND'				=> $card_type,
			//'CN'				=> $this->order('first_name'). " " . $this->order('last_name'),
			//'CARDNO'			=> $credit_card_number,
			//'CVC'				=> $this->order('CVV2'),
			//'ED'				=> $this->order('expiration_month'). $this->year_2($this->order('expiration_year')),
			'ACCEPTURL'			=> $this->response_script($gateway, array("create_token")),
			'EXCEPTIONURL'		=> $this->response_script($gateway, array("error")),
			/* these are not used by the alias gateway. Don't turn them back on or you're hosed 
			'CANCELURL'			=> $this->response_script($gateway, array("cancel")),
			'DECLINEURL'		=> $this->response_script($gateway, array("declined")),
			'BACKURL'			=> $this->response_script($gateway, array("return")),
			*/ 
			//'SHASIGN'			=> '',
			'ORDERID'			=> $this->order('entry_id')
		);
		
		// SHA GENERATION //////////////////////////////
		uksort($post_array, "strnatcasecmp");
		$string_to_hash = "";
		foreach ($post_array as $key=>$value)
		{
			if ($value)
			{
				//$revised_post_array[$key] = $value;
				$string_to_hash.=strtoupper($key)."=".$value.$this->plugin_settings('passphrase') ;
			}
		}
		#var_dump($this->plugin_settings('passphrase')); 
		#var_dump($string_to_hash);
		$sha1_hash = strtoupper(sha1($string_to_hash)); 

		#var_dump($sha1_hash); 
		
		// END SHA GENERATION ////////////////////////////////

	//	$revised_post_array['SHASign'] = $sha1_hash;
		
		// START THE JUMP FORM HERE ////////////////////////////
		$jump_html[] =  "<form name='jump' id='jump' method='post'  action='".$this->_alias_host."' >";
		
		$jump_html[] = "<input type='hidden' name='PSPID' value='".$post_array['PSPID']."' />";
		$jump_html[] = "<input type='hidden' name='ORDERID' value='".$post_array['ORDERID']."' />";
		$jump_html[] = "<input type='hidden' name='ACCEPTURL' value='".$post_array['ACCEPTURL']."' />";
		$jump_html[] = "<input type='hidden' name='EXCEPTIONURL' value='".$post_array['EXCEPTIONURL']."' />";
		/* these are not used by the alias gateway. don't turn them back on, or you're hosed 
		$jump_html[] = "<input type='hidden' name='DECLINEURL' value='".$post_array['DECLINEURL']."' />";
		$jump_html[] = "<input type='hidden' name='CANCELURL' value='".$post_array['CANCELURL']."' />";
		$jump_html[] = "<input type='hidden' name='BACKURL' value='".$post_array['BACKURL']."' />";
 		*/ 
		
		
		$jump_html[] = "<input type='hidden' name='SHASign' value='".$sha1_hash."' />";
		
		$jump_html[] = "<div class='ogone_field'><label for='card_name'>".$this->lang('ogone_alias_card_name')."</label><input type='text' name='CN' id='card_name' value='".$this->order('shipping_first_name')." ".$this->order('shipping_last_name')."' /></div>";
		$jump_html[] = "<div class='ogone_field'><label for='card_number'>".$this->lang('ogone_alias_card_number')."</label><input type='text' name='CARDNO' id='card_number' value='' /></div>";
		$jump_html[] = "<div class='ogone_field'><label for='card_cvv'>".$this->lang('ogone_alias_card_cvv')."</label><input type='text' name='CVC' id='card_cvv' value='' /></div>";
		$jump_html[] = "<div class='ogone_field'><label for='expiration_month'>".$this->lang('ogone_alias_expiration')."</label>".$this->month_select()." ".$this->year_select()."</div>";
		
		$jump_html[] =   "<input type='submit' class='submit' value='".$this->lang("ogone_alias_submit_button")."' />"; 
		$jump_html[] =   "</form>";
		
		$this->EE =& get_instance();
		$this->EE->load->model("vault_model");
		$this->EE->load->model("order_model");
		$gateway = __CLASS__;
		$member_id = $this->order('member_id');
		
		$vault = $this->EE->vault_model->get_member_vault($member_id, $gateway);
		
		if(!$vault ||  empty($vault['token']))
		{
			// this is called for maintenance functions. you'll note that we're telling it not to do any redirection or jump pages.
			$this->gateway_exit_offsite(NULL,  $url=FALSE, $jump_url= FALSE);
		
			if ($this->plugin_settings('header_html_payment_form'))
			{
				echo $this->parse_template($this->plugin_settings('header_html_payment_form')); 
	 		}

			foreach ($jump_html as $line)
			{
				echo $line; 
			}
		
			if ($this->plugin_settings('footer_html_payment_form'))
			{
				echo $this->parse_template($this->plugin_settings('footer_html_payment_form'));
			}
		
			exit;
		}
		else
		{
			return $this->charge_token($vault['token'], NULL);
			exit;
		}
	
	}
	/*
	http://wengcontemporary.dev/store/checkout;http://mightyliverobot.com/ee_272/delete/ogone_alias
	*/ 
	public function extload($post)
	{
		// relaunching full cart so that session is active and template content can be displayed. 
		$this->relaunch_cart($cart_id = NULL, $post['OrderID']);
		
		if($post['ct_action'] == "create_token")
		{
			// relaunching full cart so that session is active and template content can be displayed. 
			$this->relaunch_cart($cart_id = NULL, $post['OrderID']);

			if($post['status'] != "1")
			{
				$new_token = $this->create_token($credit_card_number = NULL, $post);
				$resp = $this->external_charge_token($post, $new_token, $customer_id = NULL);
				$this->gateway_order_update($resp, $this->order('order_id'), $this->order('return'));
				exit; 
			}
			else
			{
				$resp['authorized'] 	= FALSE; 
				$resp['declined']		= FALSE; 
				$resp['processing']		= FALSE; 
				$resp['failed']			= TRUE; 
				$resp['error_message']	= $post['NCError'];
				$resp['transaction_id']	= NULL;
				$this->gateway_order_update($resp, $this->order('order_id'), $this->order('return'));
				exit;
			}
		}
		elseif($post['ct_action'] == "return")
		{
			$resp['authorized'] 	= FALSE; 
			$resp['declined']		= FALSE; 
			$resp['processing']		= FALSE; 
			$resp['failed']			= TRUE; 
			$resp['error_message']	= $this->lang('ogone_alias_return'); 
			$resp['transaction_id']	= NULL;
		}
		elseif ($post['ct_action'] == "declined")
		{
			$resp['authorized'] 	= FALSE; 
			$resp['declined']		= TRUE; 
			$resp['processing']		= FALSE; 
			$resp['failed']			= FALSE; 
			$resp['error_message']	= $post['NCError'];
			$resp['transaction_id']	= NULL;
		}
		elseif ($post['ct_action'] =="cancel")
		{
			$resp['authorized'] 	= FALSE; 
			$resp['declined']		= FALSE; 
			$resp['processing']		= FALSE; 
			$resp['failed']			= TRUE; 
			$resp['error_message']	= $this->lang('ogone_alias_canceled'); 
			$resp['transaction_id']	= NULL;
		}
		else
		{
			$resp['authorized'] 	= FALSE; 
			$resp['declined']		= FALSE; 
			$resp['processing']		= FALSE; 
			$resp['failed']			= TRUE; 
			$resp['error_message']	= $post['NCError'];
			$resp['transaction_id']	= NULL;
		}
		$this->gateway_order_update($resp, $this->order('order_id'), $this->order('return'));
		exit; 
	}
	
	public function create_token($credit_card_number, $params = array())
	{
		$this->EE =& get_instance();
		$this->EE->load->model("vault_model");
		$this->EE->load->model("order_model");
		$gateway = __CLASS__; 
		$member_id = $this->order('member_id');
		
		$token = new Cartthrob_token;
		
		// check for a vault token
		$vault = $this->EE->vault_model->get_member_vault($member_id, $gateway);
		
		if(!$vault || empty($vault['token']))
		{
			if(! empty($params['Alias']))
			{
 				$token->set_token($params['Alias']);
				$token->set_customer_id($member_id);
				$new_vault = array(
					'customer_id' => $token->customer_id(),  
					'token' => $token->token(),
					'order_id' => $params['OrderID'],
					'member_id' => $member_id,
					'gateway' => $gateway,
					'last_four' => substr($params['CardNo'], -4)
				);
				$vault = $this->EE->vault_model->update($new_vault);
				
				$this->update_order(array(
					'vault_id' => $vault
				));
				return $token; // $new_vault['token'];
				
			}
		}
		
		return $token; 
		
	}
	
	public function external_charge_token($post, $token, $customer_id = NULL)
	{
		$resp = $this->charge_token($token, $customer_id); 
		return $resp; 
	}
	public function charge_token($token, $customer_id)
	{
		$alias = $token; 
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
		
		
		$total = $this->order('total') * 100; 
		$total = round($total, 0, PHP_ROUND_HALF_DOWN);
		
		$post_array = array(
			'PSPID'				=> $this->_pspid,
			'OrderID'			=> $this->order('entry_id'),
			'USERID'			=> $this->plugin_settings('api_userid'),
			'PSWD'				=> $this->plugin_settings('api_password'),
			'amount'			=> $total,
			'currency'			=> ( $this->order('currency_code') ? $this->order('currency_code') : "EUR"),  
			//'CARDNO'			=> $this->order('CardNo'),
			//'ED'				=> $this->order('ED'),
			//'COM'				=> ($this->order('description') ? $this->order('description') : "Sale"),
			//'CN'				=> $this->order('CN'),
			//'EMAIL'				=> $this->order('email_address'),
			//'SHASign'			=> '',
			//'CVC'				=> $this->order('CVC'),
			//'Owneraddress'		=> $this->order('address'). " " . $this->order('address2'),
			//'OwnerZip'			=> $this->order('zip'),
			//'ownertown'			=> $this->order('city'),
			//'ownercty'			=> ($this->order('country_code') ? $this->alpha2_country_code($this->order('country_code')) : "GB"),
			//'ownertelno'		=> $this->order('phone'),
			//'BRAND'				=> $this->order('BRAND'),
			'Operation'			=> 'SAL',
			'REMOTE_ADDR'		=> 'NONE', // 
			'RTIMEOUT'			=> 90,
			'ECI'				=> 7,
			'Alias'				=> $alias
		);
		
		// SHA GENERATION //////////////////////////////
		uksort($post_array, "strnatcasecmp");
		$string_to_hash = "";
		$revised_post_array = Array();
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
		// for some reason xml_to_array isn't working for the response string, doing something else
		//$result = $this->xml_to_array($connect);
		$result_object = simplexml_load_string($connect);
		
		$attributes_object = $result_object->attributes();
		$attributes_array = (array) $attributes_object;

		$result = $attributes_array['@attributes'];

		
		switch(  substr($result['NCERROR'], 0, 1)  ) 
		{
			case '5':
				$resp['error_message']  = $this->lang('ogone_alias_error_incomplete'). ' ' . $result['NCERROR']. ' ' . $result['NCERRORPLUS']; 
				$resp['declined']		= FALSE; 
				$resp['failed']			= TRUE;
				break; 
			case '3':	 
				$resp['error_message']  = $this->lang('ogone_alias_error_refused'). ' ' . $result['NCERROR']. ' ' . $result['NCERRORPLUS']; 
				$resp['declined']		= TRUE; 
				$resp['failed']			= FALSE;
				break;
			case '0':	
				$resp['authorized'] 	= TRUE; 
				$resp['error_message']	= NULL; 
				$resp['transaction_id']	= $result['PAYID'];
				
				break;
			default: 
				$resp['declined']		= FALSE; 
				$resp['failed']			= TRUE;
				$resp['error_message']  = $this->lang('ogone_alias_error_unknown') . ' ' . $result['NCERROR']. ' ' . $result['NCERRORPLUS'];
		}
		return $resp; 
	}
	public function year_select($selected=NULL)
	{
		$this->EE->load->helper('form');
		$years = 10; 
		$start_year =  date('Y');
		$final_year = $start_year + $years;
		
		$data = array();
		
		for ($year = $start_year; $year < $final_year; $year++)
		{
			$data[$year] = $year;
		}
		
		$extra = "id='expiration_year' class='expiration_year'"; 

		$name =   "ECOM_CARDINFO_EXPDATE_YEAR"; 
 		
		return form_dropdown(
			$name,
			$data,
			(($selected) ? $selected : $start_year),
			$extra
		);
	}
	public function month_select($selected=NULL)
	{
		$attrs = array(); 
		$this->EE->load->helper('form');
 		$data = array(
			"01"	=> $this->EE->lang->line('january'),
			"02"	=> $this->EE->lang->line('february'),
			"03"	=> $this->EE->lang->line('march'),
			"04"	=> $this->EE->lang->line('april'),
			"05"	=> $this->EE->lang->line('may'),
			"06"	=> $this->EE->lang->line('june'),
			"07"	=> $this->EE->lang->line('july'),
			"08"	=> $this->EE->lang->line('august'),
			"09"	=> $this->EE->lang->line('september'),
			"10"	=> $this->EE->lang->line('october'),
			"11"	=> $this->EE->lang->line('november'),
			"12"	=> $this->EE->lang->line('december'),
			);
			$extra = "id='expiration_month' class='expiration_month'"; 
		
		$name =  "ECOM_CARDINFO_EXPDATE_MONTH"; 
		
		return form_dropdown(
			$name, 
			$data,
			(($selected) ? $selected : "01"),
			$extra
		);
	}
	// END 
}
// END Class