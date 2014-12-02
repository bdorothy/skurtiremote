<?php 
class Cartthrob_gocardless extends Cartthrob_payment_gateway
{
	public $title = 'gocardless_title';
	public $overview = 'gocardless_overview';
	public $settings = array(
		array(
			'name' => 'gocardless_app_id',
			'short_name' => 'app_id',
			'type' => 'text'
		),
		array(
			'name' => 'gocardless_app_secret',
			'short_name' => 'app_secret',
			'type' => 'text'
		),
		array(
			'name' => 'gocardless_access_token',
			'short_name' => 'access_token',
			'type' => 'text'
		),
		array(
			'name' => 'gocardless_merchant_id',
			'short_name' => 'merchant_id',
			'type' => 'text'
		),
		array(
			'name' => 'gocardless_redirect_url',
			'short_name' => 'redirect_url',
			'type' => 'note',
			'note' => 'Enter this url in your GoCardless Developer Dashboard as your Redirect URI',
			//'default' => '',
		),	
		array(
			'name' => 'mode',
			'short_name' => 'mode',
			'type' => 'radio',
			'default' => 'sandbox',
			'options' => array(
				'sandbox' => 'sandbox',
				'live' => 'live',
			)
		),
	);
	
	public $required_fields = array();
	
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
	
	public function initialize()
	{
		foreach ($this->settings as &$setting)
		{
			if ($setting['short_name'] === 'redirect_url')
			{
				$setting['default'] = $this->response_script(__CLASS__);
				
				break;
			}
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
		$this->setup();
		
		$url = GoCardless::new_bill_url(array(
			'amount' => $this->total(),
			'name' => $this->order('title'),
			'state' => $this->order('order_id'),//state does not mean location, it's just a value they'll pass back in their redirect
			'user' => array(
				'first_name' => $this->order('first_name'),
				'last_name' => $this->order('last_name'),
				'email' => $this->order('email_address'),
				'billing_address1' => $this->order('address'),
				'billing_address2' => $this->order('address2'),
				'billing_town' => $this->order('city'),
				'billing_county' => $this->order('state'),
				'billing_postcode' => $this->order('zip'),
			),
		));
		
		$this->gateway_exit_offsite(array(), $url);
		
		exit;
 	}
	
	private function setup()
	{
		require_once $this->vendor_path().'/gocardless/GoCardless.php';
		
		GoCardless::$environment = $this->plugin_settings('mode');
		
		GoCardless::set_account_details(array(
			'app_id' => $this->plugin_settings('app_id'),
			'app_secret' => $this->plugin_settings('app_secret'),
			'merchant_id' => $this->plugin_settings('merchant_id'),
			'access_token' => $this->plugin_settings('access_token'),
		));
	}
	
	public function extload($data) 
	{
		$this->setup();
		
		$auth = array(
			'authorized' => FALSE,
			'error_message' => '',
			'failed' => TRUE,
			'processing' => FALSE,
			'declined' => FALSE,
			'transaction_id' => '',
		);
		
		$resource_keys = array(
			'resource_uri',
			'resource_id',
			'resource_type',
			'signature',
			'state',
		);
		
		$missing_key = NULL;
		
		foreach ($resource_keys as $key)
		{
			if (empty($data[$key]))
			{
				$auth['error_message'] = $this->lang('gocardless_missing_resource_'.$key);
				
				$missing_key = $key;
				
				break;
			}
		}

		if ( ! is_null($missing_key))
		{
			if ( ! empty($data['state']))
			{
				$this->relaunch_cart(NULL, $data['state']);
			}
			
			$this->gateway_order_update($auth, $this->order('order_id'), $this->order('return'));
			
			exit;
		}
		
		$this->relaunch_cart(NULL, $data['state']);
		
		try
		{
			$resource_data = array();
			
			foreach ($resource_keys as $key)
			{
				$resource_data[$key] = $data[$key];
			}
			
			$result = GoCardless::confirm_resource($resource_data);
			
			//example result
			/*
			{
				"amount": "44.0",
				"gocardless_fees": "0.44",
				"partner_fees": "0",
				"currency": "GBP",
				"created_at": "2011-11-04T21: 41: 25Z",
				"description": "Month 2 payment",
				"id": "VZUG2SC3PRT5EM",
				"name": "Bill 2 for Subscription description",
				"paid_at":  "2011-11-07T15: 00: 00Z",
				"status": "paid",
				"merchant_id": "01HY02EAAE",
				"user_id": "FIVWCCVEST6S4D",
				"source_type": "subscription",
				"source_id": "YH1VEVQHYVB1UT",
				"uri": "https://sandbox.gocardless.com/api/v1/bills/VZUG2SC3PRT5EM"
			}
			*/
			
			//possible statuses
			/*
			Pending - waiting for the money to clear from the customer's account.
			
			Paid - Bill has been succesfully been debited from the customer's account. It is being held by GoCardless pending a withdrawal to the merchant.
			
			Failed - Bill could not be debited from a customers's account. This usually means that there were insufficient funds.
			
			Withdrawn - the Bill has been paid out to the merchant. Takes up to one business day to reach the merchant's bank account.
			
			Refunded - the bill has been refunded to the customer.
			*/
			
			$auth['transaction_id'] = $result['id'];
			
			if ($result['status'] === 'paid' || $result['status'] === 'withdrawn')
			{
				$auth['authorized'] = TRUE;
				
				$auth['failed'] = FALSE;
			}
			else if ($result['status'] === 'pending')
			{
				$auth['processing'] = TRUE;
				
				$auth['failed'] = FALSE;
			}
			else
			{
				$auth['declined'] = TRUE;
				
				$auth['failed'] = FALSE;
				
				$auth['error_message'] = $this->lang('gocardless_declined');
			}
		}
		catch(Exception $e)
		{
			$auth['error_message'] = $e->getMessage();
		}
		
		$this->gateway_order_update($auth, $this->order('order_id'), $this->order('return'));
		
		exit;
	}//END
 }
// END Class