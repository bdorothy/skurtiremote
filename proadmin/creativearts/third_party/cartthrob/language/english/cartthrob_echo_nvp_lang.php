<?php   
$lang = array(
	
	'echo_nvp_title' 				=> 'Echo NVP Payments',
	'echo_nvp_overview'				=> '<p>Echo NVP requires specific credit card numbers when used in test mode to test for specific responses<br />
		For a "good" response, use card number 4005550000000019.</p>',
	'echo_nvp_id'					=> 'Merchant Echo ID',
	'echo_nvp_pin'					=> 'Merchant Echo Pin',
	'echo_nvp_tt_avs' 				=> 'Address Verification',
	'echo_nvp_tt_auth' 				=> 'Authorization',
	'echo_nvp_tt_auth_avs' 			=> 'Authorization with Address Verification',
	'echo_nvp_tt_credit' 			=> 'Credit',
	'echo_nvp_tt_deposit' 			=> 'Deposit',
	'echo_nvp_tt_auth_and_deposit' 	=> 'Authorization and Deposit',
	'echo_nvp_tt_auth_and_deposit_avs' 		=> 'Authorization and Deposit with Address Verification',
	'echo_nvp_tt_system_check' 		=> 'System check',
	
	'echo_nvp_error_declined'				=> 'The transaction was declined without explanation by the card issuer.',
	'echo_nvp_error_amount_exceeded'		=> 'The amount exceeds the limits established by the issuer for this type of transaction.',
	'echo_nvp_error_not_valid'				=> 'The issuer indicates that this card is not valid.',
	'echo_nvp_error_number_not_valid'		=> 'The card issuer number is not valid.',
	'echo_nvp_error_resubmit'				=> 'Customer should resubmit transaction.',
	'echo_nvp_error_expired'				=> 'The card is expired.',
	'echo_nvp_error_invalid_expiration'		=> 'The credit card has expired or the expiration date was invalid.',
	'echo_nvp_error_low_amount'				=> 'The dollar amount was less than $1.00 or greater than the maximum allowed.',
	'echo_nvp_error_duplicate_transaction'	=> 'Duplicate transaction attempt.',
	'echo_nvp_error_error_occurred'			=> 'An error occurred during payment processing. Your card has not been charged',
	'echo_nvp_error_error_code'				=> 'Error Code: ',
);