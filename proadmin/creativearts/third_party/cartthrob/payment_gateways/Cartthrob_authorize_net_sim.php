<?php 
class Cartthrob_authorize_net_sim extends Cartthrob_payment_gateway
{
	public $title = 'authorize_net_sim_title';
	public $affiliate = 'authorize_net_sim_affiliate'; 
	public $overview = 'authorize_net_sim_overview';
	public $language_file = TRUE;
	public $settings = array(
		array(
			'name' => 'authorize_net_sim_email_customer',
			'short_name' => 'email_customer',
			'type' => 'radio',
			'default' => "no",
			'options' => array(
				"no"	=> "no",
				"yes"	=> "yes"
			)
		),
		array(
			'name' => "mode",
			'short_name' => 'mode',
			'type' => 'radio',
			'default' => "test",
			'options' => array(
				"test"	=> "test",
				"live"	=> "live",
				"developer" => "developer"
			)
		),
		array(
			'name' => "authorize_net_sim_tax_inclusive",
			'short_name' => 'tax_inclusive',
			'type' => 'radio',
			'default' => "Y",
			'options' => array(
				"N"	=> "no",
				"Y"	=> "yes",
			)
		),
		array(
			'name' => 'authorize_net_sim_api_login',
			'short_name' => 'api_login',
			'type' => 'text'
		),
		array(
			'name' => 'authorize_net_sim_trans_key',
			'short_name' => 'transaction_key',
			'type' => 'text'
		),
		array(
			'name' => 'authorize_net_sim_dev_api_login',
			'short_name' => 'dev_api_login',
			'type' => 'text'
		),
		array(
			'name' => 'authorize_net_sim_dev_trans_key',
			'short_name' => 'dev_transaction_key',
			'type' => 'text'
		),
		array(
			'name' => "authorize_net_sim_hash_value",
			'short_name' => 'hash_value',
			'type' => 'text'
		),
		array(
			'name' => "authorize_net_sim_authcapture",
			'short_name' => 'transaction_settings',
			'default'	=> 'AUTH_CAPTURE',  // set to AUTH_CAPTURE for money capturing transactions
			'type' => 'radio',
			'options' => array(
				'AUTH_CAPTURE'	=> 'authorize_net_sim_auth_charge',
				'AUTH'	=> 'authorize_net_sim_auth_only',
				)
		),
 		array(
			'name' => 'authorize_net_sim_form_styles',
			'short_name' => 'form_styles_header',
			'type' => 'header',
		),
		array(
			'name'=>'authorize_net_sim_form_header',
			'short_name'=>'x_header_html_payment_form',
			'type'=>'select',
			'note'	=> 'authorize_net_sim_header_note',
			'attributes' => array(
				'class' 	=> 'templates',
				),
		),
		array(
			'name'=>'authorize_net_sim_form_footer',
			'short_name'=>'x_footer_html_payment_form',
			'type'=>'select',
			'note'	=> 'authorize_net_sim_footer_note',
			'attributes' => array(
				'class' 	=> 'templates',
				),
		),
		array(
			'name' => 'authorize_net_sim_form_background',
			'short_name' => 'x_color_background',
			'default'	=> '#FFFFFF',
			'type' => 'text',
		),
		array(
			'name' => 'authorize_net_sim_link_color',
			'short_name' => 'x_color_link',
			'default'	=> '#FF0000',
			'type' => 'text',
		),
		array(
			'name' => 'authorize_net_sim_text_color',
			'short_name' => 'x_color_text',
			'type' => 'text',
			'default'	=> '#000000',
		),
		array(
			'name' => 'authorize_net_sim_logo_url',
			'short_name' => 'x_logo_url',
			'type' => 'text',
		),
		array(
			'name' => 'authorize_net_sim_background_url',
			'short_name' => 'x_background_url',
			'type' => 'text',
		),
		
	);
	
	public $required_fields = array(
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
		'shipping_first_name',
		'shipping_last_name',
		'shipping_address',
		'shipping_address2',
		'shipping_city',
		'shipping_state',
		'shipping_zip',
	);
	
	public $hidden = array();
	public $card_types = NULL;
	
	
	public function initialize()
	{
		//  changing the overview dynamically to include the notification link
		$this->overview = $this->lang('authorize_net_sim_overview'). " <a href='". $this->response_script(ucfirst(get_class($this)))."'>".$this->response_script(ucfirst(get_class($this)))."</a>";

		$this->_x_type 			=	$this->plugin_settings('transaction_settings');
 		$this->_x_test_request         	= "TRUE";

		$this->api_login 			= $this->plugin_settings('api_login');
		$this->transaction_key 	= $this->plugin_settings('transaction_key');
		
		if ($this->plugin_settings('mode') == 'developer') 
		{
			$this->_host					= "https://test.authorize.net/gateway/transact.dll";
			$this->api_login 						= $this->plugin_settings('dev_api_login');
			$this->transaction_key 				= $this->plugin_settings('dev_transaction_key');
		}
		elseif ($this->plugin_settings('mode') == "test") 
		{
			$this->_host					= "https://secure.authorize.net/gateway/transact.dll";
 		}
		else
		{
			$this->_host					= "https://secure.authorize.net/gateway/transact.dll";
			$this->_x_test_request         	= "FALSE";
			
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
		
		$order_id 			= $this->order('entry_id'); 
		$total 				= $this->total(); 
 		
		$random_number	= rand(1, 1000);
		$time_stamp	= time();

		// The following lines generate the SIM fingerprint.  PHP versions 5.1.2 and
		// newer have the necessary hmac function built in.  For older versions, it
		// will try to use the mhash library.
		if( phpversion() >= '5.1.2' )
		{ 
			$fingerprint = hash_hmac("md5", 
							$this->api_login . 
							"^" . $order_id . 
							"^" . $time_stamp . 
							"^" . $total . 
							"^", $this->transaction_key); 
		}
		else 
		{ 
			$fingerprint = bin2hex(mhash(MHASH_MD5, 
							$this->api_login . 
							"^" . $order_id . 
							"^" . $time_stamp . 
							"^" . $total . 
							"^", $this->transaction_key)); 
		}
		
		
 
 		$post_array = array(
			"x_login"         				=> $this->api_login,
			"x_type"                 		=> $this->_x_type,  // set to AUTH_CAPTURE for money capturing transactions
			"x_amount"               	 	=> number_format($this->total(),2,'.',''),
			"x_relay_response"				=> "TRUE",
			"x_relay_URL"					=> $this->response_script(ucfirst(get_class($this))), 
			"x_version"           	 		=> "3.1",
			"x_test_request"    		   	=> $this->_x_test_request,
			"x_method"               	 	=> "CC",
			"x_duplicate_window"			=> "0",
			"x_email"          		      	=> $this->order('email_address'),
			"x_email_customer"    		 	=> ($this->plugin_settings('email_customer') == "yes") ? "TRUE" : "FALSE",
			"x_address"      		      	=> $this->order('address')." ".$this->order('address2'),

			"x_first_name"       	     	=> $this->order('first_name'),
			"x_last_name"       	     	=> $this->order('last_name'),
			"x_city"            	    	=> $this->order('city'),
			"x_state"              		  	=> $this->order('state'),
			"x_description"					=> $this->order('description'),
			"x_zip"            		    	=> $this->order('zip'),
			"x_country"            		   	=> $this->alpha2_country_code(($this->order('country_code') ? $this->order('country_code') : 'USA')),
			'x_ship_to_first_name'			=> ($this->order('shipping_first_name')) ? $this->order('shipping_first_name') : $this->order('first_name'),
			'x_ship_to_last_name'			=> ($this->order('shipping_last_name')) ? $this->order('shipping_last_name') : $this->order('last_name'),
			'x_ship_to_address'				=> ($this->order('shipping_address')) ? $this->order('shipping_address').' '.$this->order('shipping_address2') : $this->order('address').' '.$this->order('address2'),
			'x_ship_to_city'				=> ($this->order('shipping_city')) ? $this->order('shipping_city') : $this->order('city'),
			'x_ship_to_state'				=> ($this->order('shipping_state')) ? $this->order('shipping_state') : $this->order('state'),
			'x_ship_to_zip'					=> ($this->order('shipping_zip')) ? $this->order('shipping_zip') : $this->order('zip'),
			'x_ship_to_company'				=> ($this->order('shipping_company'))? $this->order('shipping_company') : $this->order('company'),
			
			"x_phone"          		      	=> $this->order('phone'),
			"x_cust_id"          		   	=> $this->order('member_id'),
			"x_invoice_num"					=> time().strtoupper(substr($this->order('last_name'), 0, 3)),
			"x_company"						=> $this->order('company'),
			"x_card_num"             		=> $credit_card_number,
			"x_card_code"             		=> $this->order('CVV2'),
			"x_exp_date"             		=> str_pad($this->order('expiration_month'), 2, '0', STR_PAD_LEFT).'/'.$this->year_2($this->order('expiration_year')),
			"x_tax"							=> $this->order('tax'),
			"x_freight"						=> $this->order('shipping'),
			"x_show_form"					=> "PAYMENT_FORM",
			"x_fp_hash"						=> $fingerprint,
			"x_fp_sequence"					=> $this->order('entry_id'),
			"x_fp_timestamp"				=> $time_stamp,
			"x_invoice_num"					=> $this->order('entry_id'),
			"x_delim_data"    	    	 	=> "TRUE",
			"x_background_url"				=> $this->plugin_settings('x_background_url'),
			"x_logo_url"                    => $this->plugin_settings('x_logo_url'),
			"x_color_text"                  => $this->plugin_settings('x_color_text'),
			"x_color_link"                  => $this->plugin_settings('x_color_link'),
			"x_color_background"            => $this->plugin_settings('x_color_background'),
		);
 		$post_array['x_cust_id']  = $this->order('member_id');
		$post_array['x_po_num'] =  $this->order('entry_id') ;
		
		
		if (!$this->order('shipping_country_code'))
		{
			$post_array['x_ship_to_country'] = $post_array['x_country']; 
		}
		else
		{
			$post_array['x_ship_to_country'] = $this->alpha2_country_code($this->order('shipping_country_code')); 
		}
 		
		$header = ""; 
		$footer=""; 
		if ($this->plugin_settings('x_header_html_payment_form'))
		{
			$header = $this->parse_template($this->plugin_settings('x_header_html_payment_form')); 
 			$post_array["x_header_html_payment_form"]    = substr($header, 0, 254);
		}
		if ($this->plugin_settings('x_footer_html_payment_form'))
		{
			$footer = $this->parse_template( $this->plugin_settings('x_footer_html_payment_form')); 
 			$post_array["x_footer_html_payment_form"]    = substr($footer, 0, 254); 
		}
		
		reset($post_array);
		$data='';
		
		while (list ($key, $val) = each($post_array)) 
		{
			$data .= $key . "=" . urlencode($val) . "&";
		}
		
 		// SENDING ORDER DATA TO AUTHORIZE.NET
		$line_item = array();
		
		if ($this->order('items'))
		{
			foreach ($this->order('items') as $row_id => $item)
			{
				$basket = ""; 
				
				if (!isset($count))
				{
					$count=1;
				}
				$count++;
				// authorize.net does not allow more than 30 items to be shown as line items. 
				if ($count > 30)
				{
					continue; 
				}
	
				$title = $this->strip_punctuation( $item['title'] ); 
	
				$title = substr($title, 0, 30); 
				
				while (strlen(urlencode(htmlspecialchars($title))) > 30)
				{
					$title = substr($title, 0, -1); 
				}
				if (empty($item['entry_id']))
				{
					$item['entry_id'] = "000";
				}
				$basket .= $item['entry_id']."<|>"; 
				$basket .= urlencode(htmlspecialchars($title))."<|>";
				$basket .= $item['entry_id']."<|>";
				$basket .= abs($item['quantity'])."<|>";
				$basket .= number_format(abs($item['price']),2,'.','')."<|>"; 
				$basket .= $this->plugin_settings('tax_inclusive');
				
				if (! empty($title))
				{
					$line_item[] = $basket; 
				}
			}
		}
		
		// ADDING TO EXISTING DATA STRING. 
		while (list($key, $val) = each($line_item)) 
		{
			$post_array['x_line_item'][] = $val; 
			$data .= 'x_line_item=' .$val.'&';
		}
		$data .= 'x_duty=0'; 
		

		$post_array['x_duty'] = 0; 
		
		$this->gateway_exit_offsite($post_array, $url = FALSE, $jump_url = $this->_host); 
		exit; 
	}
 	function extload($post)
	{
		if (isset($post['ACT']))
		{
			unset($post['ACT']); 
		}
		if (isset($post['G']))
		{
			unset($post['G']); 
		}
		if (isset($post['M']))
		{
			unset($post['M']); 
		}
		if (empty($post))
		{
			die($this->lang('authorize_net_sim_no_post')); 
		}

		
		$auth = array(
			'authorized' 	=> FALSE,
			'error_message'	=> NULL,
			'failed'		=> TRUE,
			'declined'		=> FALSE,
			'transaction_id'=> NULL, 
			'processing'	=> FALSE
			);
			
		
		if ($this->plugin_settings('hash_value'))
		{
			$hash = strtoupper(md5($this->plugin_settings('hash_value'). $this->api_login . $post['x_trans_id'].$post['x_amount'])); 
			
			if ($hash != $post['x_MD5_Hash'])
			{
				$auth = array(
					'authorized' 	=> FALSE,
					'error_message'	=> $this->lang('authorize_net_sim_non_matching_sha'),
					'failed'		=> TRUE,
					'declined'		=> FALSE,
					'transaction_id'=> NULL 
					);
				
				$this->checkout_complete_offsite($auth, $this->order('entry_id'), 'template'); 
			}
		}
		
		switch ($post["x_response_code"])
		{
 			case "1":
				$auth['authorized']	 	= TRUE; 
				$auth['declined'] 		= FALSE; 
				$auth['transaction_id']	= $post['x_trans_id'];
				$auth['failed']			= FALSE; 
				$auth['error_message']	= '';
				
				$order_data['orders_billing_first_name']	= $post['x_first_name']; 
				$order_data['orders_billing_last_name']		= $post['x_last_name']; 
				$order_data['orders_billing_company']		= $post['x_company']; 
				$order_data['orders_billing_address']		= $post['x_address']; 
				$order_data['orders_billing_address2']		= $post['x_city']; 
				$order_data['orders_billing_state']			= $post['x_state']; 
				$order_data['orders_billing_city']			= $post['x_zip']; 
				$order_data['orders_country_code']			= $post['x_country']; 
				$order_data['orders_customer_phone']		= $post['x_phone']; 
				$order_data['orders_shipping_first_name']	= $post['x_ship_to_first_name']; 
				$order_data['orders_shipping_last_name']	= $post['x_ship_to_last_name']; 
				$order_data['orders_shipping_company']		= $post['x_ship_to_company']; 
				$order_data['orders_shipping_address']		= $post['x_ship_to_address']; 
				$order_data['orders_shipping_city']			= $post['x_ship_to_city']; 
				$order_data['orders_shipping_state']		= $post['x_ship_to_state']; 
				$order_data['orders_shipping_zip']			= $post['x_ship_to_zip']; 
				$order_data['orders_shipping_country_code']	= $post['x_ship_to_country'];
				
				$this->update_order_by_id($this->order('entry_id'), $order_data); 
				break;
			case "2":
				$auth['authorized']	 	= FALSE; 
				$auth['declined'] 		= TRUE; 
				$auth['transaction_id']	= NULL;
				$auth['failed']			= FALSE; 
				$auth['error_message']	= $post["x_response_reason_text"];
				break; 
			default:
				$auth['authorized']	 	= FALSE; 
				$auth['declined'] 		= FALSE; 
				$auth['transaction_id']	= NULL;
				$auth['failed']			= TRUE; 
				$auth['error_message']	= $post["x_response_reason_text"];
		}
		
		$this->checkout_complete_offsite($auth, $this->order('entry_id'), 'template');
		
	}
}
// END Class