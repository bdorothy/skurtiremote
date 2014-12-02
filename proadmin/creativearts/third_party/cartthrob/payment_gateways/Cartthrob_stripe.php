<?php

class Cartthrob_stripe extends Cartthrob_payment_gateway
{
	public $title = 'stripe_title';
	public $affiliate = '';
	public $overview = 'stripe_overview';
		// THESE SETTINGS WILL GENERATE THE INPUT FIELDS ON THE PAYMENT CONFIGURE SCREEN
	public $settings = array(
		array(
			'name' => 'mode', 
			'short_name' => 'mode', 
			'type'	=> 'select',
			'default'	=> 'test',
			'options' => array(
				'test'	=> 'stripe_mode_test',
				'live' => 'stripe_mode_live',
			),
		),
		array(
			'name' => 'stripe_private_key',
			'short_name' => 'api_key_test_secret',
			'type' => 'text',
		),
		array(
			'name' => 'stripe_api_key',
			'short_name' => 'api_key_test_publishable',
			'type' => 'text',
		),
		array(
			'name' => 'stripe_live_key_secret',
			'short_name' => 'api_key_live_secret',
			'type' => 'text',
		),
		array(
			'name' => 'stripe_live_key',
			'short_name' => 'api_key_live_publishable',
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
		$key = ($this->plugin_settings('mode') === 'live') ? $this->plugin_settings('api_key_live_publishable') : $this->plugin_settings('api_key_test_publishable');
		
		require_once $this->library_path().'stripe/Stripe.php';
		
		$api_key = ($this->plugin_settings('mode') === 'live') ? $this->plugin_settings('api_key_live_secret') : $this->plugin_settings('api_key_test_secret');
		
		Stripe::setApiKey($api_key);
		
		$this->form_extra = '
		<script type="text/javascript" src="https://js.stripe.com/v1/"></script>
		<script type="text/javascript">Stripe.setPublishableKey("'.$key.'");</script>
		<script type="text/javascript" src="'.$this->theme_path().'third_party/cartthrob/scripts/ender.min.js"></script>
		<script type="text/javascript" src="'.$this->theme_path().'third_party/cartthrob/scripts/cartthrob-tokenizer.js"></script>
		<script type="text/javascript">
		CartthrobTokenizer.init(function(){
			Stripe.createToken({
				name: CartthrobTokenizer.val("#first_name") + " " + CartthrobTokenizer.val("#last_name"), 
				number: CartthrobTokenizer.val("#credit_card_number"),
				cvc: CartthrobTokenizer.val("#CVV2"),
				exp_month: CartthrobTokenizer.val("#expiration_month"),
				exp_year: CartthrobTokenizer.val("#expiration_year"),
				address_line1: CartthrobTokenizer.val("#address"),
				address_line2: CartthrobTokenizer.val("#address2"),
				address_city:  CartthrobTokenizer.val("#city"),
				address_state: CartthrobTokenizer.val("#state"),
				address_zip: CartthrobTokenizer.val("#zip")
 			}, function(status, response){
				if (response.error) {
					CartthrobTokenizer.errorHandler(response.error.message);
					CartthrobTokenizer.submissionState = false;
				} else {
					CartthrobTokenizer.addHidden("stripeToken", response["id"])
							  .addHidden("credit_card_number", response.card["last4"])
							  .addHidden("card_type", response.card["type"])
							  .submitHandler();
				}
			})
		});
		</script>
		';
	}
	
	public function charge($ignored)
	{
		//if there's no token it means that the end user doesn't have javascript enabled
		if (FALSE === ($token = $this->input_post('stripeToken')))
		{
			return array(
				'authorized' => FALSE,
				'failed' => TRUE,
				'declined' => FALSE,
				'error_message' => $this->lang('stripe_javascript_required'),
				'transaction_id' => '',
			);
		}
		
		return $this->do_charge(array(
			'card' => $token,
		));
	}
	
	public function refund($transaction_id, $amount, $last_four)
	{
		$ch = Stripe_Charge::retrieve($transaction_id);
		$charge = $ch->refund();

		if (!empty($charge->refunded) && $charge->refunded === TRUE)
		{
			return array(
				'authorized' => TRUE,
				'failed' => FALSE,
				'declined' => FALSE,
				'transaction_id' => $charge->id,
			);
 
		}
		else
		{
			return array(
				'authorized' => FALSE,
				'failed' => TRUE,
				'declined' => FALSE,
				'error_message' => $this->lang("stripe_refund_could_not_be_completed")
			);
			
		}
		return $auth;
	}
	public function create_token($ignored)
	{
		$token = new Cartthrob_token;
		
		//if there's no token it means that the end user doesn't have javascript enabled
		if (FALSE === ($card_token = $this->input_post('stripeToken')))
		{
			return $token->set_error_message($this->lang('stripe_javascript_required'));
		}
		
		try
		{
			$customer = Stripe_Customer::create(array(
				'card' => $card_token,
				'email' => $this->order('email_address'),
				'description' => $this->customer_id(),
			));
			
			if ( ! empty($customer->id))
			{
				return $token->set_token($customer->id);
			}
			
			return $token->set_error_message($this->lang('stripe_unknown_error'));
		}
		catch(Exception $e)
		{
			return $token->set_error_message($e->getMessage());
		}
	}
	
	public function charge_token($token)
	{	
		//@TODO test just passing the raw $token as customer, we may have to use Stripe_Customer:retrieve
		return $this->do_charge(array(
			'customer' => $token,
		));
	}
	
	private function do_charge($params)
	{
		if ( ! isset($params['amount']))
		{
			$params['amount'] = $this->total() * 100;
		}
		
		if ( ! isset($params['currency']))
		{
			$currency = strtolower(($this->order('currency_code') ? $this->order('currency_code'): "USD")); 
			
			$params['currency'] = $currency; 
		}
		
		if ( ! isset($params['description']))
		{
			$params['description'] = $this->order('title') ." (".$this->order_id().")";
		}
		
		try
		{
			$charge = Stripe_Charge::create($params);
			
			//this is what's available
			//$charge->id
			//$charge->amount
			//$charge->created
			//$charge->currency
			//$charge->description
			//$charge->fee
			//$charge->livemode
			//$charge->object
			//$charge->paid
			//$charge->refunded
			//$charge->card->country
			//$charge->card->cvc_check
			//$charge->card->exp_month
			//$charge->card->exp_year
			//$charge->card->last4
			//$charge->card->object
			//$charge->card->type
			
			if ($charge->paid === FALSE)
			{
				return array(
					'authorized' => FALSE,
					'failed' => FALSE,
					'declined' => TRUE,
					'error_message' => $this->lang('stripe_card_declined'),
					'transaction_id' => $charge->id,
				);
			}
			else if ($charge->paid === TRUE)
			{
				return array(
					'authorized' => TRUE,
					'failed' => FALSE,
					'declined' => FALSE,
					'error_message' => '',
					'transaction_id' => $charge->id,
				);
			}
			
			return array(
				'authorized' => FALSE,
				'failed' => TRUE,
				'declined' => FALSE,
				'error_message' => $this->lang('stripe_unknown_error'),
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
}


/* End of file Cartthrob_stripe.php */
/* Location: ./system/expressionengine/third_party/cartthrob/payment_gateways/Cartthrob_stripe.php */
