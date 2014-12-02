<?php   
$lang = array(
	'samurai_title' => 'Samurai by FeeFighters',
	'samurai_affiliate' => '',
	'samurai_overview' => '
	<p>Samurai requires PHP 5.3 or greater to run. </p>
	<p>Samurai relies on JavaScript to submit a payment. This is what
allows it to process a credit card without the number ever having to touch your server, since
the card number is posted directly to Samurai via JavaScript. In order for CartThrob to work
properly, you must not set a custom id for the checkout_form; it must have the default id, which is checkout_form.</p>

<p>The following fields must have blank name attributes, and instead use id attributes for naming:
<code>credit_card_number</code>, <code>CVV2</code>, <code>expiration_month</code>, <code>expiration_year</code>
(ex. <code>&lt;input type="text" name="" id="credit_card_number" /&gt;</code>).
This happens by default when using the {gateway_fields} variable, and is only a concern if you plan to use custom checkout fields.
</p>',
	'samurai_mode_sandbox'   => 'Sandbox',
	'samurai_mode_live'     => 'Live',
	'samurai_javascript_required' => 'You must have JavaScript turned on to check out.',
	'samurai_unknown_error' => 'An unknown error has occurred.',
	'samurai_card_invalid' => 'The card you submitted is invalid.',
	'samurai_card_declined' => 'The card you submitted was declined.',
	'samurai_merchant_key'	=> 'Merchant Key',
	'samurai_merchant_password'=> 'Merchant Password',
	'samurai_processor_token'	=> 'Processor Token',
);