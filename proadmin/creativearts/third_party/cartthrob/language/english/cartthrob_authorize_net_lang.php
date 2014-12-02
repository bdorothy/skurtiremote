<?php   
$lang = array(
	
	// AUTHORIZE NET ERRORS & CONTENT
	'authorize_net_title'	=> 'Authorize.net',
	'authorize_net_affiliate'	=> '<p><a href="http://reseller.authorize.net/application/?id=5553676"><img src="//www.authorize.net/images/authorizenet_logo.gif" border="0" alt="AUTHORIZE.NET and the Authorize.Net logo are trademarks of CyberSource Corporation." width="150" height="42"></a></p><p><a href="http://reseller.authorize.net/application/?id=5553676">Click here to sign up now!</a></p>',
	'authorize_net_overview'	=> '<p>Authorize.Net manages the submission of payment transactions to the processing networks on behalf of its 284,000 merchant customers. Since 1996, Authorize.Net has been a leading provider of Internet Protocol (IP) based payment gateway services, enabling merchants to accept credit card and electronic check payments from Web sites, retail stores, mail order/telephone order (MOTO) centers or mobile devices. Authorize.net handles international sales, but the merchant bank account must be based in the United States.  </p> 
	<h3>Testing</h3>
	<p>Whether in LIVE or in TEST mode, you must submit all transactions securely when using Authorize.net. Make sure you use set the "secure_action" parameter of the checkout_form to "yes".</p>
	<h3>Testing when using with Subscriptions</h3>
	<p>When used in conjunction with Subscriptions, Authorize.net can not be in test mode. It must either be in LIVE or being used with a DEVELOPER account in live mode. Your account (whether LIVE or DEVELOPER) must have CIM (customer information manager) enabled for your account, and the Authorize.net account must be in "live" mode itself. </p>
	',
	'authorize_net_settings_api_login' => 'API Login',
	'authorize_net_settings_trans_key' => 'Transaction Key',
	'authorize_net_settings_email_customer' => 'Email Customer?',
	'authorize_net_settings_test_mode' => 'Test Mode?',
	'authorize_net_settings_dev_mode' => 'Developer Mode?',
	'authorize_net_settings_dev_api_login' => 'Developer API Login',
	'authorize_net_settings_dev_trans_key' => 'Developer Transaction Key',
	
	'authorize_net_cant_connect' => "Can not connect using using fsockopen or cURL",
	'authorize_net_error_1' => "There Was A Problem Connecting To Authorize.net. Error ",
	'authorize_net_error_2' => "There Was A Problem Connecting To Authorize.net. Error 2",
	'authorize_net_no_response' => " no response",
	'authorize_net_silent_post'		=> '<p>If using Authorize.net ARB, you should enable the Silent Post feature in the Authorize.net Merchant Interface. Specify the following URL in the Settings > Silent Post field of the Merchant Interface:</p>',
	'authorize_net_no_post'		=> 'No post data was sent, and the transaction can not be processed',
	'authorize_net_advanced_settings_header'	=> "Advanced Settings",
	'authorize_net_non_matching_sha'	=> 'Non-matching SHA values suggest that URL was tampered with',

	'authorize_net_hash_value'	=> 'API Hash Value',
	'authorize_net_authcapture'	=> 'Authorization Mode',
	'authorize_net_auth_charge'	=> 'Authorize and charge',
	'authorize_net_auth_only'	=> 'Authorize only',
	'authorize_net_perform_additional_validation_when_creating_tokens' =>'Perform additional validation when creating tokens?',
	'authorize_net_perform_additional_validation_when_creating_tokens_note' => 'When set to "yes" this will perform an additional authorization on a credit card of 10 cents 
			when creating a customer profile. By default Authoriz.net should generate an "authorization" on the card, however this may not be sufficient for a full CVV checking authorization (it will depend on the way some banks respond to validation requests)
			When set, this will run a full authorization on a card, and the customer will see this authorization transaction on their account statement until it is released. (typically a few days)
	', 
	'authorize_net_bad_token_charge_response'	=> 'While attempting to charge a customer token a bad response was received',
);