<?php

/**
 * PSiGate Payment Gateway plugin for CartThrob 2 by Derek Hogue
 * Questions? Email derek@amphibian.info.
 * Include at least one genuinely-funny joke.
 * Included with cartthrob by permission http://livecart.com/forums/viewthread/2397/
**/

class Cartthrob_thirdparty_psigate extends Cartthrob_payment_gateway
{
	public $title = 'PSiGate';
	public $affiliate = '';
	public $overview = 'cartthrob_psigate_overview';
	public $language_file = TRUE;
	public $settings = array(
		array(
			'name' => 'cartthrob_psigate_mode', 
			'short_name' => 'mode', 
			'type'	=> 'select',
			'default'	=> 'test',
			'options' => array(
				'test'	=> 'cartthrob_psigate_test_mode',
				'production' => 'cartthrob_psigate_production_mode'
				)
		),
		array(
			'name' => 'cartthrob_psigate_test_mode_response', 
			'short_name' => 'test_mode_response', 
			'type'	=> 'select',
			'default'	=> 'R',
			'options' => array(
				'R'	=> 'cartthrob_psigate_random',
				'A' => 'cartthrob_psigate_approved',
				'D' => 'cartthrob_psigate_declined',
				'F' => 'cartthrob_psigate_fraud'
				)
		),
		array(
			'name' => 'cartthrob_psigate_store_key', 
			'short_name' => 'store_key', 
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
		'country',
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
		'country',
		'phone',
		'email_address',
		'shipping_first_name',
		'shipping_last_name',
		'shipping_address',
		'shipping_address2',
		'shipping_city',
		'shipping_state',
		'shipping_zip',
		'shipping_country',
		'credit_card_number',
		'CVV2',
		'expiration_year',
		'expiration_month'
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

		// Determine Store Key and URL based on mode
		$store_key = ($this->plugin_settings('mode') == 'test') ? 
			'merchantcardcapture200024' : 
			$this->plugin_settings('store_key');
			
		$url = ($this->plugin_settings('mode') == 'test') ? 
			'https://devcheckout.psigate.com/HTMLPost/HTMLMessenger' : 
			'https://checkout.psigate.com/HTMLPost/HTMLMessenger';
										
		$data = array(
			'MerchantID' => $store_key,
			'PaymentType' => 'CC',
			'CardAction' => 0,
			'ResponseFormat' => 'XML',
			'CustomerIP' => $this->order('ip_address'),
			'Bname' => $this->order('first_name') . ' ' . $this->order('last_name'),
			'Bcompany' => $this->order('company'),
			'Baddress1' => $this->order('address'),
			'Baddress2' => $this->order('address2'),
			'Bcity' => $this->order('city'),
			'Bprovince' => $this->order('state'),
			'Bpostalcode' => $this->order('zip'),
			'Bcountry' => $this->alpha2_country_code($this->order('country_code')),
			'Sname' => $this->order('shipping_first_name') . ' ' . $this->order('shipping_last_name'),
			'Scompany' => $this->order('shipping_company'),
			'Saddress1' => $this->order('shipping_address'),
			'Saddress2' => $this->order('shipping_address2'),
			'Scity' => $this->order('shipping_city'),
			'Sprovince' => $this->order('shipping_state'),
			'Spostalcode' => $this->order('shipping_zip'),
			'Scountry' => $this->alpha2_country_code($this->order('shipping_country')),
			'Phone' => $this->order('phone'),
			'Email' => $this->order('email_address'),
			'Tax1' => $this->order('tax'),
			'ShippingTotal' => $this->order('shipping'),
			'SubTotal' => $this->order('subtotal') - $this->order('discount'),
			'CardNumber' => $credit_card_number,
			'CardExpMonth' => $this->order('expiration_month'),
			'CardExpYear' => $this->order('expiration_year'),
			'CardIDNumber' => $this->order('CVV2') 
		);
		
		if($this->order('items'))
		{
			// Build the list of order items from the cart
			$items = count($this->order('items'));
			$i = 1;
			foreach ($this->order('items') as $row_id => $item)
			{
				$id = str_pad($i, 2, '0', STR_PAD_LEFT);
				$data['ItemID'.$id] = ($item['entry_id']) ? $item['entry_id'] : $id;
				$data['Description'.$id] = $item['title'];
				$data['Quantity'.$id] = $item['quantity'];
				$data['Price'.$id] = $item['price'];
				$i++;
			}
			
			// When passed cart items, PSiGate will ignore the cart subtotal value,
			// instead calculating the total from all items x quantities, plus shipping and tax.
			// So we have to add the cart discount as a negative item, as per their docs.
			if(intval($this->order('discount')) > 0)
			{
				$id = str_pad(($items + 1), 2, '0', STR_PAD_LEFT);
				$data['ItemID'.$id] = $this->lang('cartthrob_psigate_discount');
				$data['Description'.$id] = $this->lang('cartthrob_psigate_cart_discount');
				$data['Quantity'.$id] = 1;
				$data['Price'.$id] = '-' . $this->order('discount');
			}
		}
			
		if($this->plugin_settings('mode') == 'test')
		{
			$data['TestResult'] = $this->plugin_settings('test_mode_response');
		}
					
		$data = $this->data_array_to_string($data);
						
		$connect = $this->curl_transaction($url, $data);
		
		$resp = array(
			'authorized' => FALSE,
			'declined' => FALSE,
			'error_message' => FALSE,
			'failed' => FALSE,
			'transaction_id' => FALSE
		);
		
		if($connect)
		{
			$transaction = $this->convert_response_xml($connect);
			$result = preg_match("|<Approved>([A-Z:]*)</Approved>|", $transaction['Result'], $result_matches);
			$error = preg_match("|<ErrMsg>PSI-[0-9]*:(.*)</ErrMsg>|", $transaction['Result'], $error_matches);
			$order = preg_match("|<OrderID>([0-9]*)</OrderID>|", $transaction['Result'], $order_id);
			
			if($result_matches[1] == 'APPROVED')
			{
				$resp['authorized'] = TRUE;
			}
			elseif($result_matches[1] == 'DECLINED')
			{
				$resp['declined'] = TRUE;
			}
			else
			{
				$resp['failed'] = TRUE;
				$resp['error_message'] = $error_matches[1];
			}
			
			$resp['transaction_id'] = $order_id[1];
		}
		else
		{
			$resp['failed'] = TRUE;
			$resp['error_message'] = $this->lang('cartthrob_psigate_cannot_connect');
		}
		return $resp;
	}

}