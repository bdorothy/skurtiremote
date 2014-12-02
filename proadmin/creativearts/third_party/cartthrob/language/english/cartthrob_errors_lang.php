<?php   
$lang = array(  
	 //COUPON_ERROR_MESSAGES
	'coupon_valid_msg' =>  'Your code is valid and your cart total has been updated.',
	'coupon_invalid_msg'=>"The code you entered is invalid.",
	'coupon_inactive_msg'=>"The code you entered is not yet active.", //Inactive Coupon (used before coupon start date)
	'coupon_expired_msg'=>"The code you entered is expired.", //Expired Coupon (used after coupon end date)
	'coupon_global_limit_msg'=>"You are only allowed %d coupon code per order.", //Coupon Limit Reached
	'coupon_user_limit_msg'=>"You have already used this coupon code.", //If coupons have a limit per customer,
	'coupon_coupon_limit_msg'=>"The code you entered is no longer valid.", //If coupons have a limit per coupon,
	'coupon_default_error_msg'=>'The coupon codes channel has not been properly set up.',
	
	// VIEW: VALIDATION ERROR MESSAGES
	'validation_missing_fields' => 'The following required fields are missing',
	'validation_customer_name' => 'Customer Name',
	'validation_first_name' => 'First Name',
	'validation_last_name' => 'Last Name',
	'validation_address' => 'Address',
	'validation_address2' => 'Address (line 2)',
	'validation_city' => 'City',
	'validation_state' => 'State',
	'validation_zip' => 'Zip',
	'validation_country' => 'Country',
	'validation_phone' => 'Phone',
	'validation_email_address' => 'Email Address',
	'validation_company' => 'Company',
	'validation_shipping_first_name' => 'Shipping First Name',
	'validation_shipping_last_name' => 'Shipping Last Name',
	'validation_shipping_address'  => 'Shipping Address',
	'validation_shipping_address2'=> 'Shipping Address2',
	'validation_shipping_city' => 'Shipping City',
	'validation_shipping_state' => 'Shipping State',
	'validation_shipping_zip' => 'Shipping Zip',
	'validation_shipping_option' => 'Shipping Option',
	'validation_credit_card_number' => 'Credit Card Number',
	'validation_expiration_month' => 'Expiration Month',
	'validation_expiration_year' => 'Expiration Year',
	'validation_card_code' => 'Card Code (3 or 4 digit number on back)',
	'validation_card_modulus_10'	=> 'The credit card number appears to be invalid',
	
	//INVENTORY ERROR MESSAGES
	'item_not_in_stock' => '%s in your cart is out of stock, please remove it from your cart before checking out.',
	'item_quantity_greater_than_stock' => 'There are just %d remaining of item "%s" in your cart. Please remove "%s" or reduce its quantity before checking out.',
	'item_quantity_greater_than_stock_one' => 'There is just %d remaining of item "%s" in your cart. Please remove this item from your cart or reduce its quantity before checking out.',
	'item_quantity_greater_than_stock_zero' => 'Item "%s" is currently out of stuck. Please remove this item from your cart before checking out.',
	'item_not_in_stock_add_to_cart' => '%s is currently out of stock.',
	'item_quantity_greater_than_stock_add_to_cart' => 'There are just %d remaining of item "%s" in stock. Please reduce the quantity before adding to your cart.',
	'item_quantity_greater_than_stock_add_to_cart_one' => 'There is just %d remaining of item "%s" in stock. Please reduce the quantity before adding to your cart.',
	'item_quantity_greater_than_stock_add_to_cart_zero' => 'Item "%s" is currently out of stock.',
	
	//CHECKOUT ERRORS
	'empty_cart' => 'Your cart is empty.',

	//GENERAL PAYMENT GATEWAY ERRORS & CONTENT
	"gateway_function_does_not_exist" => "Payment gateway is not configured to process incoming payments from third parties.",
	"payment_action_missing" => "CartThrob is not correctly installed. 1. Go to the EE modules page and make sure that the CartThrob module says 'installed'. 2. Go to the EE extensions page, and make sure the CartThrob extension is listed as 'enabled'. 3. As a last resort you may need to go to the modules page and select 'remove' and then click to 'install' CartThrob. Resetting the CartThrob module in this way will not affect your templates or settings.",
	"unknown_error"	=> 'An unknown error occurred',
	'live'	=> 'Live',
	'test'	=> 'Test',
	'mode'	=> 'Mode',
	'sandbox'	=> 'Sandbox',
	'unauthorized_access'	=> 'The account you are attempting to access does not exist.', 
	
	//PAYMENT GATEWAY: DEV_TEMPLATE & CONTENT
	'dev_template_title' => 'Developer Payment Gateway Sample',
	'dev_template_affiliate' => 'insert affiliate link here',
	'dev_template_overview' => 'Overview content goes here ',
	'dev_template_settings_example' => 'Settings Example',
	'dev_template_error_1' => "CartThrob development payment gateway error: It's not you, it's me.", 
	'dev_template_error_2' => "CartThrob development payment gateway error: Let's see other people.", 
	'dev_template_error_3' => "CartThrob development payment gateway error: I just need some space.",
	'dev_template_error_4' => "CartThrob development payment gateway error: Let's just be friends.",
	
);