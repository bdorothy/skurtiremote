<?php   
$lang = array(
	// SAGE ERRORS & CONTENT
	'sage_server_title'=> 'SagePay Europe Offsite (Server)',
	'sage_affiliate'=> '<p><a href="https://support.sagepay.com/apply/default.aspx?PartnerID=2A93C68A-1EB6-453E-BED0-2DB1429164B8"><img src="//livecart.com/images/affiliate_logos/sage_pay_partner_logo.gif" border="0" alt="Sage Pay Logo" ></a></p><p><a href="https://support.sagepay.com/apply/default.aspx?PartnerID=2A93C68A-1EB6-453E-BED0-2DB1429164B8">Click here to sign up now!</a></p>', 
	'sage_overview'=> "<p>Sage Pay is the UK and Ireland's leading independent payment service provider and makes accepting payments online, simple, fast, secure and profitable.</p>
	<h3>Testing</h3>
	<p>IP Address: You need to register any IP address you're using to test or process live transactions with your SagePay Account, or your tests and live transactions will fail. <br />
	<br />Credit Card Numbers: Sage requires specific credit card numbers to be used during testing. Without using these credit card numbers, your tests will always fail. 
	<br /> 
	CVV2: 123<br />
	Address Number: 88<br />
	Post Code: 412<br />
	<br />

	<strong>Visa Credit:</strong> 4929000000006<br />
	<br />
	
	<strong>MasterCard Credit:</strong> 5404000000000001<br />
	<br />
	
	<strong>Visa Debit / Delta:</strong> 4462000000000003<br />
	<br />
	
	<strong>Solo:</strong> 6334900000000005<br />
	Issue #: 1<br />
	<br />
	
	<strong>UK Maestro:</strong> 5641820000000005<br />
	Issue #:01<br />
	<br />
	
	<strong>American Express:</strong> 374200000000004<br />
	<br />
	
	<strong>Visa Electron:</strong> 4917300000000008<br />
	<br/>
	
	<strong>JCB:</strong> 3569990000000009: <br /> <br />
	
	<strong>Diner’s Club:</strong> 36000000000008<br />
	<br />
	
	<strong>Laser (LASER):</strong> 6304990000000000044<br />
	</p>
	",
	'sage_vps'=> 'VPS Protocol',
	'sage_vendor_name'=> 'Vendor Name',
	'sage_mode'=> 'Mode',
	
	'sage_failed' => "Can't connect using using fsockopen or cURL",
	'sage_malformed' => "The data sent to the payment processor was missing fields or badly formatted. This error typically only occurs during development, integration and testing.",
	'sage_invalid' => "The transaction was not registered because although the data sent to the payment gateway was formatted correctly, some information supplied was invalid. E.g. an incorrect vendor name or currency code was sent.",
	'sage_notauthed' => "The transaction was not authorized by the acquiring bank. No funds could be taken from the card.",
	'sage_rejected' => "The payment gateway rejected the transaction because of rules set on the vendor's account.",
	'sage_3dauth' => "3D-Authentication is required for this transaction.",
	'sage_ppredirect' => "Paypal payments have not been properly configured for this account.",
	'sage_authenticated' => "Security checks were performed successfully and the card details secured at Sage Pay, but no money has been taken from the card.",
	'sage_registered' => "3D-Secure checks failed or were not performed, but the card details are still secured at Sage Pay. No money has been taken from the card.",
	'sage_error' => "An error occurred at the payment gateway which means the transaction could not be completed successfully. No money has been taken from the card.",
	'sage_default' => "An unknown error occurred which means the transaction could not be completed successfully. No money has been taken from the card.",
	'sage_3dsecure' => "An unknown error occurred which means the transaction could not be completed successfully. No money has been taken from the card.",
	'sage_contact_admin' => "Could not connect to payment gateway. Please contact administrator",
	'sage_aborted'		=> "You chose to Cancel your order on the payment pages.",
	
	'sage_form_title'	=> 'Sage Form Payment Gateway',
	'sage_form_send_email'	=> 'Send Email?',
	'sage_form_encryption_password' => 'Encryption Password',
	'sage_minimal_formatting'	=> 'Minimal formatting for use in iFrames',
	'sage_payment_page_style'	=> 'Payment page style',
	'sage_normal'	=> 'Normal',
	'sage_signature_not_valid'	=> 'Security Signature is not valid',
	'sage_s_notification_url_too_long'	=> 'Notification URL is too long. Please contact ecommerce cart providers for more information.',
);