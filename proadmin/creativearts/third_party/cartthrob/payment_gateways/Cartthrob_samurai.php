<?php

class Cartthrob_samurai extends Cartthrob_payment_gateway
{
	public $title = 'samurai_title';
	public $affiliate = '';
	public $overview = 'samurai_overview';
		// THESE SETTINGS WILL GENERATE THE INPUT FIELDS ON THE PAYMENT CONFIGURE SCREEN
	public $settings = array(
		array(
			'name' => 'mode', 
			'short_name' => 'mode', 
			'type'	=> 'select',
			'default'	=> 'test',
			'options' => array(
				'sandbox' => 'samurai_mode_sandbox',
				'live' => 'samurai_mode_live',
			),
		),
		array(
			'name' => 'merchant_key',
			'short_name' => 'merchant_key',
			'type' => 'text',
		),
		array(
			'name' => 'merchant_password',
			'short_name' => 'merchant_password',
			'type' => 'text',
		),
		array(
			'name' => 'processor_token',
			'short_name' => 'processor_token',
			'type' => 'text',
		),
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
		'credit_card_number',
		'CVV2',
		'expiration_year',
		'expiration_month',
 	);
	
	public $nameless_fields = array(
		'credit_card_number',
		'CVV2',
		'expiration_year',
		'expiration_month',
	);
	
	public function initialize()
	{
		$sandbox = ($this->plugin_settings('mode') === 'live') ? 'false' : 'true';
		
		$this->form_extra = '
		<script type="text/javascript" src="https://samurai.feefighters.com/assets/api/samurai.js"></script>
		<script type="text/javascript">
		Samurai.init({
			merchant_key: "'.$this->plugin_settings('merchant_key').'",
			sandbox: '.$sandbox.',
			forceLoad: ["payments"]
		});
		</script>
		<script type="text/javascript" src="'.$this->theme_path().'third_party/cartthrob/scripts/ender.min.js"></script>
		<script type="text/javascript" src="'.$this->theme_path().'third_party/cartthrob/scripts/cartthrob-tokenizer.js"></script>
		<script type="text/javascript">
		CartthrobTokenizer.init(function(){
			Samurai.payment({
				credit_card: {
					first_name: CartthrobTokenizer.val(":input[name=first_name]"),
					last_name: CartthrobTokenizer.val(":input[name=last_name]"),
					address_1: CartthrobTokenizer.val(":input[name=address]"),
					address_2: CartthrobTokenizer.val(":input[name=address2]"),
					city: CartthrobTokenizer.val(":input[name=city]"),
					state: CartthrobTokenizer.val(":input[name=state]"),
					zip: CartthrobTokenizer.val(":input[name=zip]"),
					card_number: CartthrobTokenizer.val("#credit_card_number"),
					cvv: CartthrobTokenizer.val("#CVV2"),
					expiry_month: CartthrobTokenizer.val("#expiration_month"),
					expiry_year: CartthrobTokenizer.val("#expiration_year")
				}
			}, function(data) {
				CartthrobTokenizer.addHidden("payment_method_token", data.payment_method.payment_method_token)
						  .submitHandler();
			});
		});
		</script>
		';
	}
	
	public function charge($ignored)
	{
		//if there's no token it means that the end user doesn't have javascript enabled
		if (FALSE === ($token = $this->input_post('payment_method_token')))
		{
			return array(
				'authorized' => FALSE,
				'failed' => TRUE,
				'declined' => FALSE,
				'error_message' => $this->lang('samurai_javascript_required'),
				'transaction_id' => '',
			);
		}
		
		try
		{
			require_once $this->vendor_path().'/samurai/Samurai.php';
			
			Samurai::setup(array(
				'merchantKey' => $this->plugin_settings('merchant_key'),
				'merchantPassword' => $this->plugin_settings('merchant_password'),
				'processorToken' => $this->plugin_settings('processor_token'),
				'sandbox' => $this->plugin_settings('mode') === 'sandbox',
			));
			
			$payment_method = Samurai_PaymentMethod::find($token);
			
			if ( ! $payment_method->isSensitiveDataValid)
			{
				return array(
					'authorized' => FALSE,
					'failed' => FALSE,
					'declined' => TRUE,
					'error_message' => $this->compile_error_message($payment_method->errors, $this->lang('samurai_card_invalid')),
					'transaction_id' => '',
				);
			}
			
			$processor = Samurai_Processor::theProcessor();
			
			$purchase = $processor->purchase(
				$token,
				$this->total(),
				array(
					'billing_reference' => $this->order_id(),
					'customer_reference' => $this->customer_id(),
					//'custom' => 'custom data',
					//'descriptor' => 'descriptor',
				)
			);
			
			if ($purchase->isSuccess())
			{
				return array(
					'authorized' => TRUE,
					'failed' => FALSE,
					'declined' => FALSE,
					'transaction_id' => $purchase->referenceId,
					'error_message' => '',
				);
			}
			
			return array(
				'authorized' => FALSE,
				'failed' => FALSE,
				'declined' => TRUE,
				'error_message' => $this->compile_error_message($purchase->errors, $this->lang('samurai_card_declined')),
				'transaction_id' => '',
			);
		}
		catch(Exception $e)
		{
			return array(
				'authorized' => FALSE,
				'failed' => TRUE,
				'declined' => FALSE,
				'error_message' => $e->getMessage(),
				'transaction_id' => '',
			);
		}
	}
	
	private function compile_error_message($errors, $default_message)
	{
		$error_message = array();
		
		foreach ($errors as $key => $messages)
		{
			foreach ($messages as $message)
			{
				$error_message[] = $message->description;
			}
		}
		
		return ($error_message) ? implode(', ', $error_message) : $default_message;
	}
	// END 
}// END CLASS

/* End of file Cartthrob_samurai.php */
/* Location: ./system/expressionengine/third_party/cartthrob/payment_gateways/Cartthrob_samurai.php */