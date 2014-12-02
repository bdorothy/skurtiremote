<?php 
$lang = array(
	// ***********************************************************
	// ***********************************************************
	// CUSTOMER FACING
	// ***********************************************************
	// ***********************************************************

	//----------------------------------------
	 //DOWNLOADS ERROR MESSAGES
	"download_url_not_specified" =>	"No download URL was specified.",
	"download_file_read_error" => 	"File download could not be found.", 
	"download_file_not_authorized" =>"You are not authorized to download this file.",
	"download_file_not_authorized_for_member" =>"Your member ID is not authorized to download this file.",
	"download_file_not_authorized_for_group" => "Your member group is not authorized to download this file.",
	"download_remote_file"	=> "URL points to offsite file.",
	"download_local_data_exists"	=> "File exists locally.",
	"download_mime"	=> 'Mime Type', 
	"download_extension"	=> 'File Extension',
	"download_filename"	=> 'Filename',
	'download_file_instructions'	=> 'Either <strong>group_id</strong> or <strong>member_id</strong> parameter must be specified when downloading a protected file.',
	'gzip_settings_cant_be_adjusted'	=> 'Download system attempted to edit Apache SetEnv to disable GZip compression of files. It was unable to do so, which may lead to corruption of downloaded files.',
	'output_compression_settings_cant_be_adjusted'	=> 'Download system attempted to edit PHP Ini settings to disable output compression of files. It was unable to do so, which may lead to corruption of downloaded files.',
	'upload_url_not_specified'		=> 'No upload directory was specified.',
	'upload_urls_not_set'			=> 'Upload URLs have not been designated.',
	
	//----------------------------------------
	 //COUPON_ERROR_MESSAGES
	'coupon_valid_msg' =>  'Your code is valid and your cart total has been updated.',
	'coupon_invalid_msg'=>"The code you entered is invalid.",
	'coupon_inactive_msg'=>"The code you entered is not yet active.", //Inactive Coupon (used before coupon start date)
	'coupon_expired_msg'=>"The code you entered is expired.", //Expired Coupon (used after coupon end date)
	'coupon_global_limit_msg'=>"You are only allowed %d coupon code per order.", //Coupon Limit Reached
	'coupon_user_limit_msg'=>"You have already used this coupon code.", //If coupons have a limit per customer,
	'coupon_coupon_limit_msg'=>"The code you entered is no longer valid.", //If coupons have a limit per coupon,
	'coupon_no_access_msg' => 'Your account is not authorized to use this code', //Coupon is only available to a specific user group

	//----------------------------------------
	// VALIDATION ERROR MESSAGES
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
	'validation_country_code' => 'Country Code',
	'validation_phone' => 'Phone',
	'validation_email_address' => 'Email Address',
	'validation_group_id_is_too_low' => 'When registering new members, the group the member is assigned to must not be set to Super Administrator, Banned, Guest, or Pending membership types.',
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
	'validation_custom_data' => 'Custom Data (%s)',
	'validation_item_options' => 'Item Option (%s)',
	'validation_header' => 'Error Messages: Customer Data Error Messages',
	'validation_description' => 'Data validation & warning error messages used with customer data validation.',
	'validation_form_header' => 'Validation Error Message',	
	'validation_form_field_name' => 'Form Field Name',
	'validation_form_field_description' => 'Fields used as parameters for the payment "process" function.',
	'validation_error_display_text' => 'Error Display Text',
	'validation_error_display_description' => 'When errors occur this text will be used in a list of the problem fields.',
	'validation_customer_billing_address' => 'Customer Billing Address',
	'validation_customer_contact_info' => 'Customer Contact Info',
 	'validation_customer_shipping_address' => 'Customer Shipping Address',
 	'validation_credit_card_information' => 'Credit Card Information',
 	'validation_username' => 'Username',
 	'validation_screen_name' => 'Screen Name',
 	'validation_email' => 'Email',
 	'validation_password' => 'Password',
 	'validation_password_confirm' => 'Password Confirm',

	//----------------------------------------
	 //SUBSCRIPTION ERRORS
	'invalid_subscription_id' 				=> 'Could not update: an invalid subscription ID was supplied.',
	'modification_not_allowed' 				=> 'Could not update: modification of this subscription is not allowed.',
	'subscription_invalid_status' 				=> 'Could not update: an invalid status was supplied',
	'subscription_invalid_interval_units' 				=> 'Could not update: an invalid interval unit was supplied.',
	'subscription_invalid_interval_length' 				=> 'Could not update: an invalid interval length was supplied.',
	//----------------------------------------
	// GATEWAY FIELDS
	'billing'	=> 'Billing Info',
	'shipping'	=> 'Shipping Info',
	'member'	=> 'Member Signup',
	'additional_info'	=> 'Additional Info',
	'payment'	=> 'Payment Info',
	'checking_payment'	=> 'Checking Info',
	'payment_expiration' => 'Card Expiration',
	'payment_begin'	=> 'Card Valid From',
	'first_name' => 'First Name',
	'last_name' => 'Last Name',
	'address' => 'Address',
	'address2' => 'Address 2',
	'city' => 'City',
	'state' => 'State',
	'zip' => 'Zip',
	'country' => 'Country',
	'country_code' => 'Country',
	'settings_country_code'	=> 'Country Code',
	'company' => 'Company',
	'region' => 'Region',
	'shipping_first_name'	=> 'Shipping First Name',
	'shipping_last_name'	=> 'Shipping Last Name',
	'shipping_address'	=> 'Shipping Address',
	'shipping_address2'	=> 'Shipping Address (line 2)',
	'shipping_city'	=> 'Shipping City',
	'shipping_state'	=> 'Shipping State',
	'shipping_zip'	=> 'Shipping Zip',
	'shipping_country'	=> 'Shipping Country',
	'shipping_country_code'	=> 'Shipping Country',
	'settings_shipping_country_code'	=> 'Shipping Country Code',
	'shipping_company'	=> 'Shipping Company',
	'shipping_region'	=> 'Shipping Region',
	'phone'		=> 'Phone',
	'email_address'		=> 'Email Address',
	'ip_address'		=> 'IP Address',
	'description'		=> 'Description',
	'language'		=> 'language',
	'username '	=> 'Username',
	'screen_name'	=> 'Screen Name',
	'password'	=> 'Password',
	'password_confirm '	=> 'Password Confirm',
	'card_type'	=> 'Payment Type',
	'credit_card_number'	=> 'Credit Card Number',
	'po_number'	=> 'PO Number',
	'card_code'	=> 'Card Code',
	'issue_number'	=> 'Issue Number',
	'transaction_type'	=> 'Transaction Type',
	'bank_account_number'	=> 'Bank Account Number',
	'check_type'	=> 'Check Type',
	'account_type'	=> 'Account Type',
	'routing_number'	=> 'Routing Number',
	'CVV2'	=> 'Card Security Code',
	'bank_name'	=> 'Bank Name',
	'bank_account_name'	=> 'Bank Account Name',
	'bday_month'	=> 'Birthday Month',
	'bday_day'	=> 'Birthday Day',
	'bday_year'	=> 'Birthday Year',
	'expiration_month'	=> 'Expiration Month',
	'expiration_year'	=> 'Expiration Year',
	'begin_month'	=> 'Begin Month',
	'begin_year'	=> 'Begin Year',

	//----------------------------------------
	//SUBSCRIPTION GATEWAY FIELDS
	'subscription_name' 	=> 'Subscription Name',
	'subscription_total_occurrences' 	=> 'Total Occurrences',
	'subscription_trial_price' 	=> 'Trial Price',
	'subscription_trial_occurrences' 	=> 'Trial Occurrences',
	'subscription_start_date' 	=> 'Start Date',
	'subscription_end_date' 	=> 'End Date',
	'subscription_interval_length' 	=> 'Interval Length',
	'subscription_interval_units'  	=> 'Interval Units',
	'subscription_allow_modification' 	=> 'Allow Modification?',

	'subscription_invalid_total_occurrences' 	=> 'Invalid Total Occurrences',
	'subscription_invalid_trial_price' 	=> 'Invalid Trial Price',
	'subscription_invalid_trial_occurrences' 	=> 'Invalid Trial Occurrences',
	'subscription_invalid_start_date' 	=> 'Invalid Start Date',
	'subscription_invalid_end_date' 	=> 'Invalid End Date',
	'subscription_invalid_interval_length' 	=> 'Invalid Interval Length',
	'subscription_invalid_interval_units'  	=> 'Invalid Interval Units',
	
	//----------------------------------------
	//CREDIT CARDS DEFAULT
	'visa'		=> 'Visa',
	'mc'		=> 'Mastercard',
	'amex'		=> 'American Express',
	'discover'	=> 'Discover',
	'jcb'		=> 'JCB',
	'diners'	=> 'Diners',
	'maestro'	=> 'Maestro',
	'solo'		=> 'Solo',
	'sears'		=> 'Sears',
	
	//----------------------------------------
	//BANK ACCOUNT TYPES DEFAULT
	'savings'		=> 'Savings',
	'business_checking'	=>'Business Checking',
	'checking'		=> 'Checking',
	
	//----------------------------------------
	//MONTHS
	'january'		=> 'January',
	'february'      => 'February'  ,
	'march'         => 'March'     ,
	'april'         => 'April'     ,
	'may'           => 'May'       ,
	'june'          => 'June'      ,
	'july'          => 'July'      ,
	'august'        => 'August'    ,
	'september'     => 'September' ,
	'october'       => 'October'   ,
	'november'      => 'November'  ,
	'december'      => 'December'  ,
	
	'month_01'	=> 'January',
	'month_02'	=> 'February',
	'month_03'	=> 'March',
	'month_04'	=> 'April',
	'month_05'	=> 'May',
	'month_06'	=> 'June',
	'month_07'	=> 'July',
	'month_08'	=> 'August',
	'month_09'	=> 'September',
	'month_10'	=> 'October',
	'month_11'	=> 'November',
	'month_12'	=> 'December',
	
	//----------------------------------------
	//ADD TO CART ERRORS
	'add_to_cart_no_entry_id' => 'You must submit an entry_id when using this tag.',

	//----------------------------------------
	//INVENTORY ERROR MESSAGES
	'configuration_not_in_stock'	=> "The selected configuration of %s is not in stock. Please make another selection",
	'item_not_in_stock' => '%s in your cart is out of stock, please remove it from your cart before checking out.',
	'item_quantity_greater_than_stock' => 'There are just %d remaining of item "%s" in your cart. Please remove "%s" or reduce its quantity before checking out.',
	'item_quantity_greater_than_stock_one' => 'There is just %d remaining of item "%s" in your cart. Please remove this item from your cart or reduce its quantity before checking out.',
	'item_quantity_greater_than_stock_zero' => 'Item "%s" is currently out of stock. Please remove this item from your cart before checking out.',
	'item_not_in_stock_add_to_cart' => '%s is currently out of stock.',
	'item_quantity_greater_than_stock_add_to_cart' => 'There are just %d remaining of item "%s" in stock. Please reduce the quantity before adding to your cart.',
	'item_quantity_greater_than_stock_add_to_cart_one' => 'There is just %d remaining of item "%s" in stock. Please reduce the quantity before adding to your cart.',
	'item_quantity_greater_than_stock_add_to_cart_zero' => 'Item "%s" is currently out of stock.',
	'item_title_placeholder'	=> 'item',

	//----------------------------------------
	//CHECKOUT ERRORS
	'empty_cart' => 'Your cart is empty.',
	'must_be_logged_in' => 'You must be logged in to checkout.',
	'must_be_logged_in_for_subscriptions' => 'You must be logged in to complete your transaction', 
	'you_do_not_have_sufficient_permissions_to_update_this_order' => 'You do not have sufficient permissions to update this order',

	//----------------------------------------
	//GENERAL PAYMENT GATEWAY ERRORS & CONTENT
	"gateway_function_does_not_exist" => "Payment gateway is not configured to process incoming payments from third parties.",
	"payment_action_missing" => "CartThrob is not correctly installed. 1. Go to the EE modules page and make sure that the CartThrob module says 'installed'. 2. Go to the EE extensions page, and make sure the CartThrob extension is listed as 'enabled'. 3. As a last resort you may need to go to the modules page and select 'remove' and then click 'install' CartThrob. Resetting the CartThrob module in this way will not affect your templates or settings.",
	'invalid_payment_gateway'	=> 'The payment gateway currently selected can not be used. Please contact the site administrator',
	'validation_card_modulus_10'	=> 'The credit card number you submitted is invalid',
	'jump_alert'	=> "You are about to be taken offsite to finish this transaction. You will be directed to your bank's website, and will complete your transaction there. Once the transaction is complete, you will be returned to this website. ",
	'jump_finish'	=> 'Click here to finish transaction',
	'jump_header'	=> 'Almost complete',
	'ct_offline_error_message'	=> 'Your order will be processed offline.',
	'ct_pay_by_account_error_message'	=> 'The order total will be applied to your credit account',
	'ct_pay_by_check_error_message'	=> 'To complete this transaction, please submit a check for full purchase price',
	'ct_pay_by_phone_error_message'	=> 'Call us to complete this order',
	'gateway_requires_ssl'			=> 'This gateway requires the use of a secure (SSL/http) connection. Please install a secure certificate on your server before attempting to use',
 	'transaction_cancelled' => 'Transaction cancelled',
 	'transaction_failed' => 'Transaction failed',
	'email_debugger'		=> 'Email Debugger',
	'email_debugger_warning'	=> 'Turn off email debugging to complete transaction successfully. You will see PHP errors below due to email debugging being enabled.',
	'template_not_found'		=> 'Template or return location could not be found. Please select a return location or default template (if available in settings).',
	
	//----------------------------------------
	 //GENERAL ERROR MESSAGES
	"curl_not_installed" =>  "The cURL library for PHP Must be installed to process monetary transactions with the selected payment gateway.", 
	'curl_gateway_failure'	=> 'Could not connect to the specified payment gateway',
	'cant_connect' => "Can't connect using cURL",
	'invalid_response' => "An invalid response was recieved from the payment gateway",

	//----------------------------------------
	 //SHIPPING ERROR MESSAGES
	'shipping_update_required'	=> 'Before continuing to checkout, you must update your shipping location and/or select a shipping method',
	
	//----------------------------------------
	 //MISC
	"powered_by" => 	"Powered By ",
	"powered_by_title" =>
	"Powered By CartThrob Shopping Cart System for ExpressionEngine",
	'shipping_settings_not_configured'	=> 'Shipping settings have not been configured for this shipping method. Please contact the site administrator.',
	'cartthrob_profiler_data'	=> 'CartThrob Debuging Profiler Data',

	
	// ***********************************************************
	// ***********************************************************
	// BACKEND
	// ***********************************************************
	// ***********************************************************

	// MODULES page
	//----------------------------------------
	'cartthrob_module_name'		=> 'CartThrob Pro',
	'cartthrob_module_description'	=> 'The most powerful and versatile ecommerce system available for ExpressionEngine. Please visit <a href="http://livecart.com">livecart.com</a> for information and our awesome customer support!',
	'settings_saved' => 'Settings Saved.',
	'set_encryption_key' => 'You must set an encryption key before you can proceed.',
	'encryption_key' => 'Encryption Key',
	'encryption_key_instructions' => 'From the CodeIgniter User Guide: <blockquote><i>A key is a piece of information that controls the cryptographic process and permits an encrypted string to be decoded. In fact, the key you chose will provide the only means to decode data that was encrypted with that key, so not only must you choose the key carefully, you must never change it if you intend use it for persistent data...To take maximum advantage of the encryption algorithm, your key should be 32 characters in length (128 bits). The key should be as random a string as you can concoct, with numbers and uppercase and lowercase letters. Your key should not be a simple text string. In order to be cryptographically secure it needs to be as random as possible.</i></blockquote> We recommend using something like 1Password or <a href="https://www.grc.com/passwords.htm">https://www.grc.com/passwords.htm</a> to generate a strong key. You should probably avoid using quotation marks, apostrophes and Dollar signs however.',
	'update_required' => 'Update Required',
	'cartthrob_run_module_updates' => 'Please go to to Addons > Modules and click the Run Module Updates button before proceeding.',
	
	// FIELDTYPES
	//----------------------------------------
	'save_preset' => 'Save',
	'load_preset' => 'Load',
	'delete_preset' => 'Delete',
	'select_preset' => 'Select A Preset',
	'delete_preset_confirm' => 'Are you sure you want to delete this preset?',
	'load_preset_confirm' => 'Are you sure you want to load this preset?',
	'order_items_field_must_not_be_named_items'	=> 'The short_name of this field may not be "items". Please chooes a different name.',
	'remove_row_confirm' => 'Are you sure you want to delete this row?',
	'remove_column_confirm' => 'Are you sure you want to delete this column?',
	'name_column_prompt' => 'Please name your column.',
	'name_preset_prompt' => 'Please name your preset.',
	'no_product_channels' => 'You do not have any product channels.',
	'no_products_in_search' => 'No products matched your search.',
	
	// CARTTHROB MATRIX FIELDTYPE COLUMNS
	'cartthrob_matrix_entry_id' => 'Entry ID',
	'cartthrob_matrix_price' => 'Price',
    'cartthrob_matrix_price_plus_tax' => 'Price Plus Tax',
	'cartthrob_matrix_option_value' => 'Option Short Name',
	'cartthrob_matrix_option_name' => 'Option Label',
	'cartthrob_matrix_inventory' => 'Inventory ',
	'cartthrob_matrix_title' => 'Title',
	'cartthrob_matrix_quantity' => 'Quantity',
	'cartthrob_price_quantity_thresholds_from_quantity' => 'From This Quantity',
	'cartthrob_price_quantity_thresholds_up_to_quantity' => 'Up To This Quantity',
	'cartthrob_package_description' => 'Description',
	'cartthrob_package_price'	=> 'Package Price',
	'base_price'	=> 'Base Price',
	'cartthrob_package_option_presets' => 'Option Presets',
	'cartthrob_package_allow_selection' => 'Allow Selection?',
    'cartthrob_order_items_wish_list_entry_id' => 'Wish List ID',
	'show_package_details' => 'Show Package Details',
	'hide_package_details' => 'Hide Package Details',
	'add_row' => 'Add Row',
	'add_column' => 'Add Column',
	'cartthrob_price_by_member_group_global' => 'GLOBAL',
	'cartthrob_price_by_member_group_member_group' => 'Member Group',

	// GENERAL 
	//----------------------------------------
	'choose_a_channel' => 'Choose a channel above before using this section.', 
	'title' => 'Title',
	'channel'=> 'Channel',
	'show_debug' => 'Show Debug Info (when using debug_info tag)', 
	'super_admins_only'	=> 'Show to SuperAdmins only',
	'yes' => "Yes", 
	'no' => "No",
	'and' => 'and',  
	'variables' => 'variables', 
	'installed' => 'Installed',
	'errors' => 'Errors',
	'templates' => 'Templates',
	'section' => 'channel',
	'settings' => 'Settings',
	'add_another_row' => 'Add Another Row',
	'delete_this_row' => 'Delete This Row',
	'or' => 'OR',
	'name' => 'Name',
	'weight' => 'Weight',
	'price_plus_tax'	=> 'Price plus Tax',
	'cost' => 'Cost',
	'type' => 'Type',
	'rate'	=> 'Rate',
	'flat' => 'Flat',
	'mode'	=> 'Mode',
	'live'	=> 'Live',
	'test'	=> 'Test',
	'test_mode'	=> 'Test Mode', 
	'tax_type'	=> 'Tax Type',
	'non_taxable'	=> 'Non-taxable',
	'taxable'		=> 'Taxable', 
	'tax_exempt'	=> 'Tax Exempt',
	"unknown_error"	=> 'An unknown error occurred',
	'sandbox'	=> 'Sandbox',
	'merchant_id'	=> 'Merchant Id',
	'registration_key'	=> 'Registration Key',
	'dev_merchant_id'	=> 'Dev Merchant Id',
	'dev_password'		=> 'Dev Password',
	
	'choose_a_channel' => 'Choose a channel above before using this section.',
	'subtotal' => 'Subtotal',
	'discount' => 'Discount',
	'shipping' => 'Shipping',
	'tax' => 'Tax',
	'total' => 'Total',
	'today_sales' => 'Today\'s Sales',
	'month_sales' => 'This Month\'s Sales',
	'year_sales' => 'This Year\'s Sales',
	'narrow_by_month' => 'Click a plot point to narrow your report by month',
	'narrow_by_day' => 'Click a plot point to narrow your report by day',
	'narrow_by_order' => 'Click a plot point to view the specific order',
	'order_totals' => 'Order Totals',
	'amount' => 'Amount',
	'refresh' => 'Refresh',
	'reports_settings_title' => 'Custom Reports Template',
	'reports_settings_overview' => 'Additional custom reports can be generated using standard EE templates and tags. Additional generated templates will be displayed in the "Reports" dropdown above.',
	'report_name' => 'Report Name',
	'report_template' => 'Report Template',
	
	//NAV
	//----------------------------------------
	'nav_add_tax'	=> 'Add tax',
	'nav_edit_tax'	=> 'Edit tax',
	'nav_delete_tax'=> 'Delete tax',
	'nav_global_item_options'	=> 'Global Options',
	'nav_cartthrob' => 'CartThrob',
	'nav_settings' => 'Settings',
	'nav_products' => 'Products',
	'nav_orders' => 'Orders',
	'nav_discounts' => 'Discounts',
	'nav_purchased_items' => 'Purchased Items',
	'nav_coupon_codes' => 'Coupon Codes',
	'nav_get_started' => 'Get Started',
	'nav_license_number' => 'License Number',
	'nav_import_export_settings' => 'Import/Export Settings',
	'nav_general_settings' => 'General Global Settings',
	'nav_number_format_defaults' => 'Number Format Defaults',
	'nav_product_options' => 'Product Options',
	'nav_shipping' => 'Shipping',
	'nav_tax' => 'Tax',
	'nav_default_location' => 'Default Customer Location',
	'nav_coupon_options' => 'Coupon Options',
	'nav_email_admin' => 'Admin',
	'nav_email_customer' => 'Customer',
	'nav_email_low_stock'	=> 'Low Stock',
	'nav_notifications'		=> 'Notifications',
	'nav_order_admin'	=> 'Order Admin',
	'nav_payment_gateways' => 'Payment Gateways',
	'nav_global_settings' => 'Global',
	'nav_product_settings' => 'Products',
	'nav_order_settings' => 'Orders',
	'nav_om_sales_dashboard'	=> 'Order Management',
	'nav_shipping' => 'Shipping',
	'nav_taxes' => 'Taxes',
	'nav_template_variables'	=> 'Template Variables', 
	'nav_coupons_discounts' => 'Discounts',
	'nav_email_notifications' => 'Emails',
	'nav_payment_gateways' => 'Payments',
	'nav_support' => 'Support',
	'nav_reports' => 'Reports',
	'nav_more_settings' => 'More Settings',
	'nav_installation' => 'Installation',
	'nav_import_export' => 'Settings Files',
	'nav_members' => 'Members',
	'nav_set_license_number' => 'License Number',
	'nav_install_channels' => 'Auto-Install',
	'nav_product_channels' => 'Product Channels',
	'nav_order_channel_configuration' => 'Orders Channel',
	'nav_discount_options' => 'Discount Options',
	'nav_database_options' => 'Database Options',
	'nav_subscriptions_settings' => 'Subscriptions',
	'subnav_subscriptions_settings' => 'Settings',
	'subnav_subscriptions_list' => 'Subscriptions',
	'subnav_subscriptions_vaults' => 'Vaults',
	'subnav_subscriptions_permissions' => 'Permissions',
	
	// ACCESSORY
	'cartthrob_support_notifications'	=> 'Support Notifications', 
	'cartthrob_accessory_name'			=> 'CartThrob', 
	'cartthrob_support_new_notifications'	=> 'CartThrob support notifications have been posted in the last 24 hours. Click here to review open support tickets.',
	
	// VIEW: NOTIFICATIONS
	//----------------------------------------
	'application_events' => 'Application Events',
	'cartthrob_initiated_event'	=> 'Event',
	'Cartthrob_subscriptions' => 'CartThrob Subscriptions',
	'ct_canceled'	=> 'Payment Canceled',
	'ct_completed'	=> 'Successful Transaction',
	'ct_declined'		=> 'Declined Transaction',
	'ct_expired'	=> 'Payment Expired',
	'ct_failed'		=> 'Failed Transaction',
	'ct_low_stock'	=> 'Low stock warning',
	'ct_offsite'	=> 'Customer completing transaction offsite',
	'ct_pending'	=> 'Payment Pending',
	'ct_processing'	=> 'Incomplete Transaction (processing)',
	'ct_refunded'	=> 'Refund alert',
	'email_event'		=> 'Send when',
	'email_from' => 'From email',
	'email_from_name' => 'From Name',
	'email_reply_to_name'	=> 'Reply-to Name',
	'email_reply_to_note'	=> 'Some service providers will bounce email that does not contain a specified reply-to address (including Gmail)',
	'email_reply_to'	=> 'Reply-to email address',
	'email_template'	=> 'Email template',
	'email_to'	=> 'To email',
	'ending_status'	=> 'Status (new)',
	'log_email'	=> 'Log email?',
	'log_email_note'	=> 'if this is set to yes, all email attempts will be logged to exp_cartthrob_email_log database table',
	'log_and_send'		=> 'Log and send',
	'log_only'			=>	'Log without sending',
	'notifications' => 'Notifications',
	'notifications_description'	=> '',
	'other_events'		=> 'Other Events',
	'payment_triggers'	=> 'Payment Triggers',
	'rebill_failure' => 'Rebill Failure',
	'rebill_final_failure' => 'Rebill Final Failure',
	'rebill_success' => 'Rebill Success',
	'starting_status'	=> 'Status (orig)',
	'status_change'	=> 'Order status has been changed',

	
	// VIEW: DATABASE
	//----------------------------------------
	'database_options_header' => 'Database Options',
	'database_options_description' => '',
	'database_options_tax_settings' => 'Save tax settings in a separate database table?',
	
	// VIEW: REPORT
	//----------------------------------------
	'reports_header'	=> 'Sales Reports',
	'reports_order_totals_to_date'	=> 'Order Totals To Date',
		
	// VIEW: IMPORT SETTINGS 
	//----------------------------------------
	'import_settings_description' => 'Import your CartThrob settings for backup or use in other installations.',
	'import_import_settings' => 'Import Settings',
	'import_overwrite_settings' => 'WARNING: This will overwrite your existing settings.',
	'import_settings_header' => 'Import Settings',
	
	// VIEW: EXPORT SETTINGS
	//----------------------------------------
	'export_settings_description' => 'Export your CartThrob settings for backup or use in other installations.',
	'export_to_file' => 'Export to file',
	'export_settings_header' => 'Export Settings',

	 //VIEW: COUPON_ERROR_MESSAGES
	//----------------------------------------
	'coupon_not_valid_for_items'	=> 'The discount code you entered is not valid for any of the items in your cart.',
	'coupon_minimum_not_reached'	=> 'You must add more items to use this discount.',
	'coupon_error_msgs_heading' => 'Error Messages: Coupon Error Messages',
	'coupon_error_msgs_description' => 'Warning & error messages for coupon validation.',
	'coupon_error_msgs_form_header' => 'COUPON ERROR MESSAGES',       
	'coupon_limit_msg'=>"Coupon Limit Reached (variables: <span class='red'>{limit}</span>)",
	
	
	// VIEW: COUPON_OPTIONS
	//----------------------------------------
	'coupon_options_header' => 'Coupons',
	'coupon_options_heading' => 'Coupons',
	'coupon_code_channel' => 'Coupon Channel',
	'coupon_options_description' => 'Controls coupon creation & data storage',
	'coupon_code_field_header'=>'COUPON DATA FIELDS',
	'coupon_code_field' => 'Coupon Code Field',
	'coupon_code_type'=>'Coupon Settings Field',
	'coupon_users_field_instructions' => "CartThrob will store a pipe delimited list of member ids that have used this coupon code if a field is selected. Leave blank if you don't need to track usage. The field that you select for this option should be capable of storing a large amount of data. This option is not recommended for high-volume usage. ",  
	
	// VIEW: EMAIL_ADMIN
	//----------------------------------------
	'email_admin_header' => 'Admin Email',
	'email_admin_description' => 'Controls sending of notices to administrative users after a transaction is complete.',
	'email_form_header' => 'Admin Email Notifications', 
	'email_form_variables' => 'The following variables are available in the subject and body', 
	'send_email'=>'Send Email to Admin upon transaction completion?',
	'admin_email'=>'Admin Email (separate multiple addresses with a comma)',
	'email_admin_notification_from'=>'From Email Address',
	'email_admin_notification_from_name'=>'From Name',
	'email_admin_notification_subject'=>'Subject',
	'email_admin_notification'=>'Email Body',
	'email_admin_body_note' => 'All content is rendered through the template parser, so feel free to use EE template tags. If you store order data in a channel, the constant ORDER_ID can be used in a channel entries tag to access that data. Emails are sent as text/html. You can also use template embeds by passing the entry_id/ORDER_ID as an embed variable. Embedding a standard template will make it easier to preview and troubleshoot your email content',  
	'email_admin_notification_type' => 'Email Type',
	'email_admin_notification_html' => 'HTML',
	'email_admin_notification_plaintext' => 'Plain Text',
	 
	// VIEW: EMAIL_CUSTOMER     
	//----------------------------------------
	'email_customer_header' => 'Customer Email',
	'email_customer_description' => 'Controls sending of notices to administrative users after a transaction is complete.',
	'email_customer_form_header' => 'Customer Email Notifications', 
	'email_customer_form_variables' => 'The following variables are available in the subject and body', 
	'send_customer_email'=>'Send Email to Customer upon transaction completion?',
	'email_customer_notification_from'=>'From Email Address',
	'email_customer_notification_from_name'=>'From Name',
	'email_customer_notification_subject'=>'Subject',
	'email_customer_notification'=>'Email Body',
	'email_customer_body_note' => 'All content is rendered through the template parser, so feel free to use EE template tags. If you store order data in a channel, the constant ORDER_ID can be used in a channel entries tag to access that data. Emails are sent as text/html.',
	'email_customer_notification_type' => 'Email Type',
	'email_customer_notification_html' => 'HTML',
	'email_customer_notification_plaintext' => 'Plain Text',
	
	// VIEW: Order notifications
	//----------------------------------------
	'email_type'	=> 'Email Type',
	'send_html_email'	=> 'HTML',
	'send_text_email'	=> 'Plain Text',
	'email_subject'		=> 'Subject',
	'from_email'		=> 'From Email',
	'from_name'		=> 'From Name',
	'to_email'		=> 'To Email',
	'to_name'		=> 'To Name', 
	'email_message'	=> 'Message',
	
	// VIEW: EMAIL_LOW_STOCK     
	//----------------------------------------
	'email_low_stock_header' => 'Low Stock Levels',
	'email_low_stock_description' => 'Controls sending of notices to administrative users when stock for an item is low.',
	'email_low_stock_form_header' => 'Low Stock Notifications',
 	'email_low_stock_send_warning'=>'Send Email to Admin upon when stock levels are low?',
	'email_low_stock_body_note' => 'All content is rendered through the template parser, so feel free to use EE template tags. Use the constant ENTRY_ID in a channel entries tag to access product specific data. Emails are sent as text/html. You can also use template embeds by passing the ENTRY_ID as an embed variable. Embedding a standard template will make it easier to preview and troubleshoot your email content',  
	'low_stock_value'	=> 'Low stock warning number. When stock levels are below this number, low stock notifications will be sent.',
		
	// VIEW: MEMBER FIELDS
	//----------------------------------------
	'profile_edit_saving_instructions'	=> 'If "no" is selected, standard member fields will be used to capture customer data.',
	'member_configuration_header' => 'Member Fields', 
	'members_description' => 'This section controls saving of member data',
	'members_use_profile_edit'	=> 'Use Profile:Edit to manage member data',
	'members_form_header' => 'Member Field Mapping', 
	'members_save_data' => 'Save Member Data',
	'members_saving_instructions' => 'Saving collected data to member fields is optional. If "no" is selected, no member data will be saved.',
	'member_data_fields_instructions'	=> 'This section controls customer data field mapping.',
	'members_built_in_fields' => 'Built-in Member Fields',
	'members_custom_fields' => 'Custom Member Fields',
	'member_data_fields_header' => 'Member Data Fields',
	'member_data_custom_fields' => 'Member Custom Fields',
	'member_data_template_fields' => 'Customer Data Input',
	'member_creation_options'	=> 'Member creation options',
	'member_login_options'	=> 'Member Creation',
	'member_creation_options_description' => 'These settings apply only when registering customers during checkout. ',
	'member_login_options_header'	=> 'Member Login',
	'member_login_options_description'	=>'When registering users during the checkout process, you must select auto-login if you wish to save the customer\'s address and billing data to EE member fields. If you do not choose auto-login, standard EE member activation settings will be honored. When using an offsite gateway like PayPal the customer may not be automatically be logged in.',
	'member_auto_login'	=> 'Auto-login (data will be saved)',
	'member_use_ee_settings'	=> 'Require member account activation (data will not be saved)',
 	
	// VIEW: NUMBER_FORMAT_DEFAULTS
	//----------------------------------------
	'number_format_defaults_heading' => 'Number Format Defaults',
	'number_format_defaults_description' => 'Sets the default number formatting options. Please note, all numbers must be entered using "English notation" (1200.00 rather than 1200,00). Numbers can be output in another format using the settings below, but all numbers must be input in English notation.',
	'number_format_defaults_header' => 'Number Format Defaults',
	'number_format_defaults_decimals' => 'Decimal Precision',
	'number_format_defaults_dec_point' => 'Decimal Point',
	'number_format_defaults_thousands_sep' => 'Thousands Separator',
	'number_format_defaults_prefix' => 'Number Prefix, e.g. $ or &pound;',
	'number_format_defaults_prefix_position' => 'Prefix Position (display prefix before or after number)',
	'number_format_defaults_currency_code' => 'Currency Code, e.g. USD or EUR',
	'before'	=> "Before price",
	'after'		=> 'After price',
	
	// VIEW: SUPPORT
	//----------------------------------------
	'mark_as_urgent'	=> "Mark as urgent",
	'update_support_request'	=> 'Update',
	'helpspot_update_submission_successful' => 'Update was successful',
	'helpspot_update_submission_failed'	=> 'Update failed',
	'grant_access'	=> 'Grant EE control panel access to support team and request direct review?',
	'level_2_support'	=> 'Additional Level 2 Support Options',
	'level_1_support'	=> 'Level 1 Support Request',
	'helpspot_support_description'	=> 'Submit support, new business & quote requests.',
	'helpspot_form_description'	=> '<strong>Guaranteed access to support may be obtained by subscribing to a support & account maintenance agreement.</strong> All other level-1 support requests are handled on a first-come first-served basis at no additional cost for the first 60 days after purchase. CartThrob support is active from <strong>9 am to 5pm Monday-Friday Central US timezone</strong>. Only license holders with accounts in good standing may access the support portal.',
	'helpspot_level2_note'	=> 'A minimum assessment fee of $50 USD will be charged for each level-2 service request. Upon assessment, an estimate of additional fees (if any) will be provided and must be approved before service can commence. ', 
	'helpspot_grant_login_access' => 'For faster service, administrative login credentials will be generated & sent directly to the support team.',
	'helpspot_overview_header'	=> 'Support Portal',
	'helpspot_submission_successful'	=> 'Support submission successful. You will be notified by email when this support ticket is updated', 
	'helpspot_submission_failed'	=> 'Support submission failed. Please fill in all fields and try submitting your request again shortly.', 
	'helpspot_requires_license_number'	=> 'Support requires a valid license number',
	'support_documentation' => 'Documentation &raquo;',
	'nav_http://livecart.com/bug_tracker' => 'Bug Tracker &raquo;',
	'nav_http://livecart.com/forums' => 'Get Help &raquo;',
	'forum_support'	=> 'Forums &raquo;',
	'contact_us' => 'Sales & Customization &raquo;',
	'nav_http://livecart.com/docs/sub_pages/shipping/' => 'Shipping plugins &raquo;',
	'nav_http://livecart.com/docs/sub_pages/payments/' => 'Payment plugins &raquo;',
	'support_discounts_overview' => 'Discounts & Promotions &raquo;',
	'support_overview_header'	=> 'Overview',
	'support_taxes_overview' => 'Taxes &raquo;',
	'support_shipping_overview'	=> 'Shipping &raquo;',
	'support_checkout'	=> 'Payments & Checkout &raquo;',
	'support_troubleshooting'	=> 'Troubleshooting &raquo;',
	'support_system_test'	=> 'System Test', 
	'support_system_test_overview'	=> 'The following lists functions used by CartThrob that may be disabled by some web servers, but are required by specific features of CartThrob. If a function is disabled on your server, it will be listed below, along with the consequences of its failure. You may be able to use CartThrob with some of these functions disabled, depending on your required feature needs.',
	'support_curl_failed'		=> 'cURL is not installed, or is not configured correctly. Most payment gateways require the use of cURL, live rates shipping plugins will not work without it, and any other system that communicates with third-party services will fail. You are strongly encouraged to contact your host and ask them to add cURL to your hosting configuration. It is included at no cost with most hosting systems, so if it is disabled, request its addition or find a better host. ',
	'support_curl_success'	=> 'cURL is active',
	
	'support_ini_set_failed'	=> '"ini_set" is not callable by CartThrob. If zlib compression is turned on in your hosting configuration a problem with downloads may occur. When using CartThrob tags to dowload files that are not already zipped files, there is a strong chance that they will be corrupted.',
	'support_ini_set_success'	=> 'ini_set is active',
	'support_apache_setenv_failed'	=> '"apache_setenv" is not callable by CartThrob. If you use CartThrob tags to download files that are not already zipped files, there is a strong chance that they will be corrupted. By default many servers have "gzip compression" turned on, which may corrupt certain files (like epub and movie files) when you attempt to download them.', 
	'support_apache_setenv_success'	=> 'apache_setenv is active',
	
	// VIEW: LOCALES
	//----------------------------------------
	'locales_header' => 'Locales',
	'locales_countries' => 'Countries',
	'locales_countries_description' => 'Select the countries you wish to appear in cartthrob country select menus. Select all or none to enable all countries.',
	'locales_countries_all' => 'Select All',
				
	// VIEW: GENERAL SETTINGS
	//----------------------------------------
	'global_settings_header'	=> 'Miscellaneous global settings',
	'general_settings_header' => 'General',
	'logged_in'=>'User Must Be Logged In?',
	'customer_login_options' => 'Customer Login Options',
	'global_settings_description' => 'Cartwide settings',
	'global_settings_login_description' => 'If set to "yes" customer will be not be able to checkout. Any attempt to complete checkout will result in an error. If set to "no"  membership will not be required for checkout.',
	'global_settings_encrypted_sessions' 	=> 'Enable Encrypted Sessions?',
	'global_settings_encrypted_sessions_description' => 'CartThrob encrypts session data for security where possible. If you are experiencing problems with Internet Explorer losing cart data disable this setting, and standard PHP sessions will be used for temporary cart storage.',
	'global_settings_logging_enabled' => 'CP Logging enabled?',
	'global_settings_logging_description' => 'Controls whether or not certain actions are noted in the EE CP log.',
	'global_settings_cp_menu' => 'Add CartThrob Tab to CP Main Menu?',
	'global_settings_cp_menu_description'=> 'Adds a navigation tab to the CP for easy access to CartThrob settings and channels. Requires EE 2.1.5 or higher.',
	'global_settings_cp_menu_label' => 'CartThrob Tab Label',
	'global_settings_cp_menu_label_description' => 'If using the above setting, use this to rename the button. Leave blank to use the default label, CartThrob.',
	'global_settings_session_options' => 'Session Options',
	'global_settings_session_expire' => 'Session Expire (in seconds)',
	'global_settings_session_description' => 'Normally, a PHP Session will expire when the browser is shut down, or at a default time of about 24 minutes (1440 seconds). Change this value to extend or shorten the session length.',
	'global_settings_cart_history' => 'Cart History',
	'global_settings_clear_cart' => 'Clear Cart on Logout?',
	'global_settings_clear_cart_description' => 'If this is set to "no" cart items will remain after a user has logged out. If set to "yes" after logout all cart items stored in session will be deleted.',
	'global_settings_clear_session' => 'Clear Session on Logout?',
	'global_settings_ee_sessions_start'	=> 'Utilize EE session_start hook',
	'global_settings_ee_sessions_start_description'	=> 'If your site is having difficulty with Internet Explorer losing cart data, but you would like to contiue using CartThrob\'s Fingerprinted Sessions, try enabling this. In some instances where CartThrob can not load temporary customer data correctly by itself, ExpressionEngine may be able to give it a boost. Because the session_end hook is called so often by ExpressionEngine this setting is disabled by default to ensure optimal performance. Only enable it if you are experiencing intermittent loss of cart data. ',
	'global_settings_clear_session_description' => 'If this is set to "no" session data (cart items, customer info) will remain after a user has logged out. If set to "yes" after logout all information stored in session will be deleted.',
	'global_settings_quantity_options' => 'Global Quantity Options',
	'global_settings_rounding_options' => 'Global Rounding Options',
	'global_settings_quantity_limit' => 'Global Per Item Quantity Limit (set to 0 for unlimited)',
	'global_settings_quantity_description' => 'This sets a per-item quantity limit on products that can be added to the cart & ordered.',
	'global_settings_default_member_id' => 'Logged Out Member ID<br />(leave blank to default to oldest superadmin)',
	'global_settings_default_member_id_description' => 'If you allow logged out users to place orders, this sets the author ID for saved orders to the specified member ID. You may want to create a user called "Anonymous" for this purpose.',
	'global_settings_checkout_options' => 'Checkout Options',
	'global_settings_session_use_fingerprint' => 'Use Fingerprinting For Sessions?',
	'global_settings_session_use_fingerprint_description' => 'Choose Yes for best security. If you are experiencing problems with Internet Explorer losing cart data disable this setting, and standard PHP sessions will be used for temporary cart storage.',
	'global_settings_session_fingerprint_method' => 'Fingerprinting Method',
	'global_settings_session_fingerprint_method_description' => 'Choose the method that works best for your server and user base (ie the lowest percentage of session loss).',
	'global_settings_session_fingerprint_method_warning' => 'CAUTION: Changing your fingerprinting method will wipe out existing cart sessions. Please do not this while in production.',
	'are_you_sure' => 'Are you sure you wish to proceed?',
	'global_settings_session_fingerprint_method_0' => 'HTTP_ACCEPT_LANGUAGE + HTTP_ACCEPT_CHARSET + HTTP_ACCEPT_ENCODING',
	'global_settings_session_fingerprint_method_1' => 'IP Address',
	'global_settings_session_fingerprint_method_2' => 'User Agent',
	'global_settings_session_fingerprint_method_3' => 'IP Address + User Agent',
	'global_settings_session_fingerprint_method_4' => 'IP Address (Rackspace Cloud)',
	'global_settings_session_fingerprint_method_5' => 'IP Address (Rackspace Cloud) + User Agent',
	'global_settings_garbage_collection_cron' => 'Do session garbage collection in a cron job?',
	'global_settings_garbage_collection_cron_description' => 'On high-traffic sites, the built-in session garbage collection (removing old sessions) can intermittently hinder front-end performance. You can work around this by setting this option to yes, which will disable the front-end from triggering garbage collection. You will then be resonsible for setting up a cron job to manage the garbage collection. This is your cron job url:<br><br><code>%s</code><br><br>For instance, using curl you could set up your cron job like this: <br><br><code>curl --silent "%s" 2>&1 /dev/null</code>',
	'global_settings_checkout_form_captcha' => 'Require Captcha on Checkout Form for Guest Checkouts?',
	'global_settings_checkout_form_captcha_description' => 'See the <a href="http://livecart.com/docs/tags_detail/checkout_form/index.html#if-captcha">docs</a> for more information.',
	'global_settings_last_order_number' => 'Last Order Number',
	'global_settings_last_order_number_description' => 'If using sequential order/invoice numbers, this setting stores the number of the last order. The next order will be this value plus one. Reset this value to 0 to start order numbering at 1.',
	'global_settings_admin_checkout_groups' => 'Admin Checkout Groups',
	'global_settings_admin_checkout_groups_description' => 'These member groups will be able to checkout on behalf of other users using the member_id, order_id or create_user parameters on the checkout_form. Superadmins are automatically allowed to do so.',
	'allow_empty_cart_checkout' => 'Allow Empty Cart Checkout?',
	'allow_empty_cart_checkout_description' => 'If set to no, checkout_form will display an error on submit of an empty cart.',
	'round_to'	=> 'Round Decimals to Nearest',
	'rounding_standard' => '.01 (standard)',
	'rounding_swedish'	=> '.05 (Swedish)',
	'rounding_new_zealand' => '.10 (New Zealand)',
	'round_up_extra_precision'	=> '.001 (always round up) All calculations are made with extra precision, and final rounding for output uses standard precision. May not be compatible with PayPal.',
	'round_up'				=> '.01 (always round up)',
	'round_down'				=> '.01 (always round down)',
	'rounding_description'	=> 'Where rounding to two decimal points is required, rounding calculations will use this setting to determine how numbers are rounded. This includes standard calculations, as well as taxes, shipping, and coupons.',
	'global_settings_allow_fractional_quantities' => 'Allow fractional quantities?',
	'msm_show_all'			=> 'Multi-Site Manager: Show all Order/Product Channels from MSM sites (BETA FEATURE)', 
	'msm_show_all_description'	=> 'Setting to "yes" will show all channels from All MSM sites will in this site\'s configuration settings. You will be able to configure product channels from other MSM sites to work on this site, as well as save orders and purchased items to another MSM site. Order manager reports will have to be run on the site where the orders are saved.',

	// VIEW: LICENSE NUMBER
	//----------------------------------------
	'set_license_number_header' => 'License Number',
	'license_number_uuid' => 'UUID/GUID',
	'license_number_label' => 'License Number',
	'license_number_description' => 'Your CartThrob License Number',
	'license_not_installed' => 'You have not entered your license number.', 
	'license_please_enter' => 'Please enter before proceeding.',
	'extension_not_installed' => 'You have not enabled the CartThrob extension.',
	'please' => 'Please',
	'enable' => 'enable',
	'before_proceeding' => 'before proceeding.',
	'module_not_installed' => 'You have not installed the CartThrob module.',
	'install' => 'install',
	
	// VIEW: GET STARTED
	//----------------------------------------
	'support_header' => 'Support Links',
	'get_started_header'	=> 'Get Started',
	'get_started_description' => 'CartThrob works the way you do. Make the most of it by viewing the online tutorials!',
	'get_started_form_header' => 'Get Started',
	'get_started_overview' => 'CartThrob Overview &raquo;',
	'get_started_backend_settings' => 'Backend Settings &raquo;',

	'get_started_view_user_guide' => 'View the User Guide &raquo;',
	'get_started_view_tags_list'	=> 'CartThrob template tags &raquo;',
	
	// VIEW: INSTALL_CHANNELS
	//----------------------------------------
	'install_channels_header' => 'Auto-Install Templates and Channels',
	'install_channels_description' => 'This will automatically install and configure channels & templates to help you get started.',
	'install_channels_form_header' => 'Install Templates & Channels',
	'template_variable_settings'	=> 'Template Variables',
	'template_variable_description' => 'For your convenience, the following data can be set, and output directly in your templates using the <a href="http://livecart.com/docs/tags_detail/view_setting/">view_setting tag</a>',
	'template_var_store_checkout_page_note'     => 'Choose "shipping" if you are using the default templates and you would like to show a shipping page before the checkout page',
	'template_var_store_checkout_page'          => 'Checkout page',
	'template_var_store_about_us'               => 'About us',
	'template_var_store_description'            => 'Description',
	'template_var_store_country'                => 'Country',
	'template_var_store_zip'                    => 'Zip',
	'template_var_store_state'                  => 'State',
	'template_var_store_city'                   => 'City',
	'template_var_store_address2'               => 'Address2',
	'template_var_store_address1'               => 'Address',
	'template_var_store_name'                   => 'Store name',
	'template_var_store_shipping_estimate'		=> 'Shipping estimate',
	'template_var_store_google_code'			=> 'Google Analytics ID',
	'template_var_store_phone'					=> 'Phone Number',
	'install_channel_data'						=> 'Install sample data?',
	 
	// VIEW: ITEM OPTIONS
	//----------------------------------------
	'item_options_header' => 'Product Settings',
	'item_options_description' => 'This section controls the way product data is stored, and the way orders are processed using that stored data',
	'item_options_form_header' => 'Item Options', 
	'item_options_allow_duplicate_items' => 'Allow items to appear more than once in cart',
	'item_options_duplicate_items_instructions'  => 'If your prodcts can be configured with unique options (like color, shape, size, personalization) set this to "yes." Normally, if your products do not have configuration options, setting this to "no" add quantiy if "add to cart" is used multiple times on the same product. If this is set to "yes" each time "add to cart" is used on one product a completely new product will be added to the cart.',
	'site_id'	=> 'Site ID',
	
	
	// VIEW: ORDER CHANNEL 
	//---------------------------------------- 
	'orders_header' => 'Orders Channel',
	'order_channel_configuration_header' => 'Orders Channel',
	'orders_billing_country_code' => 'Billing Country Code',
	'orders_channel' => 'Orders Channel Field',
	'order_fields_in_channel' => 'Fields found in your orders channel. The names of these fields do not need to strictly match up with the type of data being captured (but it is probably a good idea.)',
	'orders_shipping_country_code' => 'Shipping Country Code',
	'orders_options_description' => 'The section controls the saving of order data',
	'orders_form_header' => 'Order Save Options',
	'save_orders'=>'Save completed orders to a channel',
	'orders_saving_instructions' => 'Saving orders to a channel is optional. If "no" is selected, no order data is stored.',
	'order_numbers' => 'Order/Invoice Numbers',
	'order_numbers_instructions' => 'Choose your order numbering format. Order/invoice numbers are saved in the title of the order entry. If you choose sequential ordering, the system advances the invoice number by 1 based on the number saved in the previous order\'s title. If you need to reset invoice number, change the number in the title of the most recent order',
	'order_numbers_entry_id' => 'Use entry_id as order/invoice number',
	'order_numbers_sequential' => 'Create sequential order/invoice numbers',
	'orders_status_field'=>'Order Status',
	'orders_country_code' => 'Country Code',
	'orders_billing_country' => 'Billing Country',
	'orders_convert_country_code'=>'Convert Country Codes',
	'orders_convert_country_code_instructions'=>'If your user submits a three-letter country code as the country/shipping_country, this option will convert that code to the full country name before saving.',
	'update_inventory_when_editing_order' => 'Update Product Inventory When Editing Order',
	'update_inventory_when_editing_order_description' => 'This will update the inventory of the corresponding products when you manually edit the order items\' quantity, delete an order item, or delete an order entry.',
	'orders_set_processing_status' => 'Set the status of an in-process order. This is only used for payment gateways that do not immediately process an order, such as PayPal Standard or Offline Payments',
	'orders_set_status' => 'Set the status of a completed order. This is only used for channels that have custom entry statuses. For instance you could use statuses of paid, packaged, shipped. "Open" is the default status for channels that do not use a custom entry status.',
	'order_data_fields' => 'Order Data Fields',
	'order_data_fields_instructions' => 'This section controls where general order information is stored.',
	'choose_a_webog' => 'Choose a channel above before using this section.',
    'order_info_is_not_required'  => 'None of the order information is required to be stored. The fields below correspond to parameters of the <strong>process</strong> function. ',
     'order_data_type' => 'Order Data Type',
     'order_data_type_instructions' => 'This is a general description of the type of data that will be stored. Collecting and storing this data is completely optional.',
	'orders_channel'=>'Orders channel Field', 
	'order_fields_in_channel' => 'Fields found in your orders channel. The names of these fields do not need to strictly match up with the type of data being captured (but it is probably a good idea.)',
	'orders_items_field'=>'Items',
    'orders_items_field_instructions' => "Items purchased are stored as a serialized string of data, containing entry_id, quantity, and title. Please review the documentation regarding this field online.",
	'orders_subtotal_field'=>'Subtotal',  
	'orders_subtotal_plus_tax_field'	=> 'Subtotal plus tax', 
	'orders_tax_field'=>'Tax',
	'orders_tax_instructions' => 'The tax cost of the order',
	'orders_shipping_field'=>'Shipping',
	'orders_shipping_plus_tax_field'	=> 'Shipping plus tax',
	'orders_shipping_field_instructions' => 'The total shipping cost for the order.',
	'orders_discount_field'=>'Discount',
	'orders_discount_field_instructions' => 'The total discount for the order.',
	'orders_total_field'=>'Total',
	'orders_transaction_id'=>'Transaction ID',
    'orders_transaction_id_instructions' => 'The transaction ID is required for most refund operations. If you plan to process refunds through CartThrob, you will need to capture the transaction ID. If your payment gateway provides a transactiond id, you can save that information here. ',
	'orders_last_four_digits'=>'Last Four Digits',
	'orders_last_four_digits_instructions' => 'The last 4 digits are required for most Credit Card refunds. If you take Credit Cards on your site, and do not store the last 4 digits you may not be able to process refunds through CartThrob. Storing entire credit card numbers is not a safe idea. Storing the last 4 digits of a credit card for future reference is common practice however. See <a href="https://www.pcisecuritystandards.org/">PCI Standards</a> for more details.',
    'orders_coupon_codes'=>'Coupon Codes',
    'orders_coupon_codes_instructions'=>'Coupon Codes',
    'orders_shipping_method' => 'Shipping Method',
	'orders_shipping_method_instructions'  => "If you provide multiple shipping options (UPS, USPS, FEDEX), store the customer's selected shipping method here.",
	'orders_customer_info_fields_header' => 'Customer Info Fields',
	'orders_customer_info_fields_instructions' => 'Tip: These fields are vital for customer service issues',
 	'orders_customer_name'=>'Customer Name',
	'orders_customer_name_instructions' => 'If you are capturing billing first name and last name, you can probably skip saving the customer full name.',
	'orders_customer_email'=>'Customer Email',
	'orders_customer_ip_address' => 'Customer IP Address',
	'orders_customer_email_instructions' => 'If you do not require a membership to place orders, you should probably save the customer email address.',
	'orders_customer_phone'=>'Customer Phone',
	'orders_billing_info_fields' => 'Billing Info Fields',
	'orders_full_billing_address'=>'Full Billing Address',
	'orders_full_billing_address_instructions'=>"Sometimes it's handy to have the entire billing address available in one field.",
	'orders_billing_first_name'=>'Billing First Name',
	'orders_billing_last_name'=>'Billing Last Name',
	'orders_billing_address'=>'Billing Address',
	'orders_billing_address2'=>'Billing Address 2',
	'orders_billing_company' => 'Billing Company',
	'orders_billing_city'=>'Billing City ',
	'orders_billing_state'=>'Billing State',
	'orders_billing_zip'=>'Billing Zip',
	'orders_full_shipping_address'=>'Full Shipping Address',
	'orders_shipping_first_name'=>'Shipping First Name',
	'orders_shipping_last_name'=>'Shipping Last Name',
	'orders_shipping_address'=>'Shipping Address',
	'orders_shipping_address2'=>'Shipping Address 2',
	'orders_shipping_company' => 'Shipping Company',
	'orders_shipping_city'=>'Shipping City',
	'orders_shipping_state'=>'Shipping State',
	'orders_shipping_zip'=>'Shipping Zip',
	'orders_shipping_country'=>'Shipping Country',
	'orders_shipping_info_fields' => 'Shipping Info Fields',
	'orders_shipping_info_fields_instructions' => "Sometimes it's handy to have the entire shipping address available in one field.",
	'orders_license_number_field'=>'License Number',
	'orders_license_number_type'=>'License Number Type',
	'orders_error_message_field'=>'Error Message Field',
	'orders_error_message_field_instructions'=>'If the transaction is declined or fails, the error message can be stored here.',
	'orders_payment_gateway'=>'Payment Gateway Field',
	'orders_language_field'=>'Language Field',
	'orders_site_id'	=> 'Site ID',
	'orders_vault_id'	=> 'Vault ID', 
	'orders_sub_id'		=> 'Subscription ID',
	'orders_subscription_id'	=> 'Subscription ID',
	'orders_language_field_instructions'=>'If your customer has specified a language, it\'ll be stored here.',
	'orders_title_prefix'=>'Title Prefix',
	'orders_title_suffix'=>'Title Suffix',
	'orders_url_title_prefix'=>'URL Title Prefix',
	'orders_url_title_suffix'=>'URL Title Suffix',
	
	// VIEW: PAYMENT GATEWAYS
	//----------------------------------------
	'payment_gateways_header' => 'Payment Gateways',
	'gateway_settings_affiliate_title'	=> 'Affiliate',
	'gateways_header' => 'Payment Gateway Settings',
	'payment_security_header' => 'Payment Data & Security Settings',
	'gateways_required_fields_description' => 'These fields must be filled during checkout to successfully process payment with this payment processor.',
	'gateway_settings_overview_title' => 'Overview',
	'gateway_settings_note_title' => 'Note',
	'gateways_description' => "Payment gateway settings are stored here. Multiple gateways can be configured here, though only one can be selected as the primary, default payment gateway. See documentation for details on allowing customers to choose from a list of payment gateways. <br /><br /> You may need to refer to your payment gateway provider's documentation to properly configure these settings. PLEASE NOTE: Many merchant account systems require an <a href='http://tldp.org/HOWTO/SSL-Certificates-HOWTO/x64.html'>SSL certificate</a> to take transactions online. <a href='http://www.dreamhoster.com/r.cgi?25217'>Dreamhost currently offers them for $15 / yearly.</a>",
	'gateways_form_header' => 'Item Options',
	'gateways_choose' => 'Choose your primary payment gateway',
	'gateways_edit' => 'Select a gateway to edit its settings',
	'gateways_format'	=> 'Gateway fields format',
	'gateways_format_description'	=> 'When the default gateway fields are output in the checkout form using the {gateway_fields} variable, they are wrapped either in <a href="http://twitter.github.com/bootstrap/base-css.html#forms">twitter bootstrap horizontal style form field formatting</a> or using basic label & paragraph formatting',
	'gateways_format_bootstrap'		=> 'Twitter Bootstrap Style',
	'gateways_format_default'		=> 'Plain',
	'gateways_required_fields' => 'The following form fields are required by the payment gateway.',
	'gateways_sample_html' => 'Sample HTML of all fields accepted for this payment processor',
	'plugin_select' => 'Use this value when setting this plugin with tag parameter',
	'gateways_form_input' => 'Use this value when setting gateway with form field',
	'gateways_form_input_urlencoded' => 'Use this value when sending gateway selection in a url',
	'template_settings_name'	=> 'HTML Template', 
	'template_settings_note' => 'Each plugin has its own unique set of HTML required for use. The {gateway_fields} variable outputs this HTML. You may choose to replace the default html with your own by selecting your own existing template, using the HTML below as a guide. Your template should include at least the minimum required fields, or errors will occur',
	'choose_a_template'	=> "Choose a template",
	'gateways_default_template'	=> "DEFAULT",
	'create_member'	=> 'Create Member?',
	'group_id'	=> 'Group ID',
	'sale'		=> 'Sale',
	'authorization'	=> 'Authorization',
	'sandbox'	=> 'Sandbox',
	'live'	=> 'Live',
	'simulator'	=> 'Simulator',
	
	// VIEW: COUPON/VOUCHERS
	'discount_redeemed_by'						=> 'Redeemed by',
	'discount_redeemed_by_note'					=> 'A pipe delimited list of member_id\'s of users who have used this coupon code.',
	'discount_per_user_limit'					=> 'Per User Limit',
	'discount_per_user_limit_note'				=> 'How many times can this be used per customer? Leave blank for no limit.',
	'discount_limit'							=> 'Global Limit',
	'discount_limit_note'						=> 'How many times can this be used overall? Leave blank for no limit.',
	'discount_limit_by_member_group'			=> 'Limit by member group', 
	'discount_limit_by_member_group_note'		=> 'Which member groups can use this? Enter a list of list of group_id\'s, separated by comma. Leave blank for no limit.',
	'discount_limit_by_member_id'				=> 'Limit by member id', 
	'discount_limit_by_member_id_note'			=> 'Which members can use this? Enter a list of member_id\'s, separated by comma. Leave blank for no limit.',
	'discount_reason_eligible_product' => 'Eligible for %s discount',

	// VIEW: PLUGIN SETTINGS
	//----------------------------------------
	'plugins_in_addition_to' => 'In addition to the ',
	'plugins_field_notes' => 'standard payment gateway fields</a>, the following fields should be added to the Checkout Form.',
	'plugins_fields' => 'Payment Gateway Fields',

	// VIEW: PRODUCT OPTIONS
	//----------------------------------------
	'product_options_header' => 'Product Options',
	'product_description' => 'This section controls the way product data is stored, and the way orders are processed using that stored data',
	'product_form_header' => 'Duplicate Item Options',
	'product_form_description' => 'Controls whether one item can appear in the cart more than once.',
	'product_allow_duplicate_items' => 'Allow items to appear more than once in cart',
//	'product_allow_duplicate_instructions' => 'Normally, if "add to cart" is used multiple times on the same product, the quantity of that product in the cart will be increased. If your product can be configured with unique options (like color, shape, size, personalization) set this to "yes" so that multiple different versions will appear in the cart. If this is set to "yes" each time "add to cart" is used on an individual product a completely new product will be added to the cart.',
	'product_allow_duplicate_instructions' => 'Normally, if "add to cart" is used multiple times on the same product, the quantity of that product in the cart will be increased. If this is set to "yes" each time "add to cart" is used on an individual product a completely new product will be added to the cart, rather than attempt to update the quantity of the existing item in the cart.',
	'product_split_items_by_quantity' => 'Split multiple quantities into single items',
	'product_split_items_by_quantity_instructions' => 'This option will split your items by quantity. For example, if you add an item to cart and specify a quantity of 3, the cart will get 3 items added to it, with a quantity of 1.',
	'product_non_channel_options' => 'Non-channel product options',
	'product_non_channel_directions' => 'Please see documentation for limitations of creating non-channel based products.',
	'product_non_channel_tooltip' => "During a standard transaction, when an order is placed, CartTrob checks product prices, and the final price against the amounts stored in the channel data. If product information is stored directly in a template, this security check is not performed, and there is a chance, though remote, that prices in the cart's session could be manipulated, leading to an incorrect total amount. In any case, as a final precaution, CartThrob does not allow less than zero amounts during order processing. ",
	'product_allow_non_channel_products' => 'Allow Non-channel Products (via POST)',
	'product_non_channel_tooltip' => 'By default products that are not stored in a channel can not be added to the cart for security purposes. If you would like to hard code products in your template (this is handy if you only have a few products) set the preference to "Yes" here. ',	
	
	// VIEW: PRODUCT CHANNELS 
	//---------------------------------------- 
 	'product_non_channel_options' => 'Non-channel product options',
	'product_non_channel_directions' => 'Please see documentation for limitations of creating non-channel based products.',
	'product_non_channel_tooltip' => 'By default products that are not stored in a channel can not be added to the cart for security purposes. If you would like to hard code products in your template (this is handy if you only have a few products) set the preference to "Yes" here. ',
	'product_allow_non_channel_products' => 'Allow Non-Channel Products (via POST)',
	'product_channels_header' => 'Product Channels',
	'product_channel_description' => 'This section controls the way product data is stored, and the way orders are processed using that stored data',
	'product_channel_form_header' => 'Manage Product Channels',
	'product_channel_form_description' => 'Settings for channels that contain product data.',
	'product_channel_manage_tooltip' => '<p>Products can be hard-coded in your templates, or stored in a channel for easier maintenance. </p> 
	<p>You can add and remove channels from this list at any time, it will not affect stored order data, and will not affect the channels themselves. </p>',
	'product_channel' => 'Product Channel',
	'product_channel_choose_a_channel' => 'Choose a channel that stores product information. This item is required.',
	'product_channel_price_field' => 'Price Field Name',
	'product_channel_price_field_description' => 'Prices can be stored as part of your product data. If you set prices per product, choose the field here.',
	'product_channel_shipping_field' => 'Shipping Cost Field Name',
	'product_channel_shipping_field_description' => 'Shipping Cost Field Name',
	'product_channel_weight_field' => 'Product Weight Field Name',
	'product_channel_weight_field_description' => 'If you use weight to figure shipping for each product, choose the product weight field here.',
	'product_channel_inventory_field' => 'Product Inventory Field Name',
	'product_channel_inventory_field_description' => 'Track your product\'s inventory with this field.',
	'product_channel_price_modifier_field' => 'Price Modifier Field Name',
	'product_channel_price_modifier_field_description' => 'Price modifiers can be added to your product. If you use price modifiers, choose the price modifier field here.',
	'product_channel_global_price' => 'Global Price',
	'product_channel_global_price_description' => 'If all of your products are priced the same, you can add that price here.',
	'product_channel_add_another_channel' => 'Add Another Channel',
	
	// VIEW: PURCHASED ITEMS
	//----------------------------------------
	'purchased_items_sub_id_field' => 'Purchased Items Sub ID Field Name', 
	'purchased_items_discount_field'	=> 'Purchased Items Discount Field',
	'purchased_items_headers' => 'Order Settings: Purchased Items channel',
	'purchased_items_description' => 'This section controls the way product data is stored, and the way orders are processed using that stored data',
	'purchased_items_form_header' => 'Purchased Items channel',	
	'save_purchased_items'=>'Save each purchased item to its own channel entry?',
	'save_packages_too'=>'Save each package to its own channel entry?',
	'save_packages_too_note'=>'Each package sub item is automatically saved as a purchased items channel entry. Setting this to "yes" will also save the package itself as a purcahsed item entry',
	'purchased_items_channel'=>'Purchased Items channel',
	'purchased_items_data_fields' => 'Purchased Items Data Fields',
	'purchased_items_data_fields_description' => 'This section controls where product data is stored.',
	'purchased_items_data_fields_tooltip' => '<p><strong>Choose a channel above before using this section.</strong></p> 
	<p>None of the order information is required to be stored. The fields below correspond to paramters of the <strong>process</strong> function. </p>',
	'purchased_items_data_type' => 'Purchased Items Data Type',
	'purchased_items_data_type_description' => 'This is a general description of the type of data that will be stored. Collecting and storing this data is completely optional.',
	'purchased_items_channel_field' => 'Purchased Items channel Field',
	'purchased_items_channel_field_description' => 'Fields found in your purchased items channel. The names of these fields do not need to strictly match up with the type of data being captured (but it is probably a good idea.)',
	'purchased_items_id_field'=>'Purchased Items Entry ID Field Name',
	'purchased_items_quantity_field'=>'Purchased Items Quantity Field Name',
	'purchased_items_order_id_field'=>'Purchased Items Order ID Field Name',
	'purchased_items_license_number_field'=>'Purchased Items License Number Field Name',
	'purchased_items_package_id_field'=>'Purchased Items Package ID Field Name',
	'purchased_items_license_number_type'=>'Purchased Items License Number Type',
	'purchased_items_header' => 'Purchased Items Channel',
	'purchased_items_channel' => 'Purchased Items Channel',
	'purchased_items_channel_field' => 'Purchased Items Channel Field',
	'purchased_items_channel_field_description' => 'Fields found in your purchased items channel. The names of these fields do not need to strictly match up with the type of data being captured (but it is probably a good idea.)',
	'purchased_items_set_status' => 'Set the status of a product that is part of a completed successful order. This is only used for channels that have custom entry statuses. For instance you could use statuses of paid, packaged, shipped. "Open" is the default status for channels that do not use a custom entry status.',
	'purchased_items_title_prefix'	=> 'Title prefix',
	'purchased_items_price_field'	=> 'Price field',


	// VIEW: SETTINGS FORM
	//----------------------------------------
	'there_are_no_updates' => 'There are no updates at this time',
	'ct_news_and_updates' => 'CartThrob News & Updates',
	
	// VIEW: SHIPPING
	//----------------------------------------
	'shipping_header' => 'Shipping & Taxes: Shipping',
	'shipping_description' => 'Shipping settings are configured on a site-wide basis. Choose "Defined Per Product" to control shipping on an item-by-item basis.',
	'shipping_form_header' => 'SHIPPING PLUGIN SETTINGS',
	'shipping_form_tooltip' => 'Shipping plugins are stored in the system> modules> cartthrob> shipping_plugins folder. Details on developing additional shipping plugins can be found in the CartThrob documentation.',
	'shipping_choose_a_plugin' => 'Choose a shipping plugin',
	'shipping_defined_per_product' => 'Defined Per Product',

	// VIEW: TAXES
	//----------------------------------------
	'tax' => 'Tax',
	'tax_header' => 'Shipping & Taxes: Taxes',
	'tax_description' => 'Tax settings are configured on a site-wide basis.',
	'tax_form_header' => 'TAX SETTINGS',
	'tax_form_header_description' => 'Tax settings can be configured for multiple countries, U.S. states and region codes.',
	'tax_form_legend' => 'If the zip/region code field is blank, the state/country shown in the dropdown will be used to compute taxes.',
	'tax_percent'	=> 'Tax%',
	'tax_name' => 'Name',
	'tax_state' => 'State',
	'tax_region' => 'Region',
	'tax_country'	=> 'Country',
	'tax_city'	=> 'City',
	'tax_zip'	=> 'Zip',
	'tax_special'	=> 'Special',
	'tax_add_another_setting' => 'Create new location in tax database',
	'tax_by_location'	=> 'Tax by location',
	'tax_by_location_legacy'	=> 'Tax by location - Percentage',
	'tax_by_location'			=> 'Tax By Location - Percentage (with standalone tax database)',
	'tax_settings' => 'Tax Settings',
	'tax_global_options'	=> 'Global Tax options',
	'tax_by_location_settings' => 'Tax By Location Settings',
	'tax_shipping' => 'Tax Shipping?',
	'thresholds' => 'Thresholds',
	'tax_use_shipping_address' => 'Calculate taxes using shipping address? (billing address is default)',
	'tax_choose_a_plugin'	=> 'Choose a tax plugin',
	'edit_tax'	=> 'Edit Tax',
	'tax_delete'	=> "Delete this tax?",
	'delete_if_checked' => 'Check this box to set this tax for deletion',
	'use_tax_table'	=> 'Use database tables to handle taxes? ',
	'use_tax_table_note'	=> 'This plugin has its own settings to manage tax locations. You can choose to override this plugin\'s tax location settings with information  stored in the tax tables in the database.',
	'default_tax' => 'Default tax percent', 
	'delete_tax'	=> 'Delete Tax Confirmation',
	'delete_tax_description'	=> 'If you choose to delete this tax, it can not be undone',
	'default_tax_note' => 'If no location data is found, this rate will be charged by default', 
	'quebec_gst'=> 'Quebec GST',
	'quebec_qst' => 'Quebec QST', 
	'quebec_tax_shipping' => 'Tax Shipping for purchases made in Quebec', 
	'quebec_tax_descriptive_name' => 'Quebec Tax Descriptive Name (Descriptive name of this tax shown to customer for Quebec)', 
	'quebec_tax_effective_rate' => 'Quebec Tax Effective Rate (Effective tax rate shown to customer for Quebec)', 
	'tax_by_location_with_quebec' => 'Tax by location - Percentage. With special handling for Quebec Taxes',
	'tax_quebec_overview'	=> 'To calculate taxes for Quebec, you will need to know that your customer resides in Quebec. To do this, make sure you can capture "QC" in the states dropdown. To do this simply, edit the locales config file to include Canadian provinces (located in the cartthrob folder: cartthrob > config > locales.php)', 
	'tax_rounding_options'	=> 'Tax Rounding Options',
	'tax_rounding_options_note'	=> 'By default CartThrob calculates the tax on each item, rounds it, then multiplies that tax amount by quantity of each item. This keeps the line item tax totals (per item) in line with the tax total displayed to the customer. If you set this option to "yes", the total tax will only be rounded on the subtotal of all items * quantities of those items. This system works better in the US, and countries where tax is calculated on the subtotal of items rather than for each individual item in the cart.',
	// VIEW: PAYMENT SECURITY	
	//----------------------------------------	
	'security_settings_allow_gateway_selection' => 'Allow Gateway Selection in Checkout Form?',
	'security_settings_allow_gateway_selection_description' => 'If set to No, checkout form will ignore the gateway parameter and/or form input and will default to your selected payment gateway.',
	'security_settings_selectable_gateways'	=> 'When gateway selection is allowed, selection is limited to checked gateways', 
	'security_settings_encode_gateway_selection' => 'Encode Gateway Parameter in Checkout Form?',
	'security_settings_encode_gateway_selection_description' => 'For security purposes, this should be set to yes. This option is here for legacy compatability.',
	'security_settings_cc_validation'	=> "Credit Card Validation Options",
	'security_settings_cc_modulus_checking'	=> 'Validate Credit Card Number',
	'security_settings_cc_modulus_description'	=> 'Enabling this will perform a Modulus 10 check on any credit card number before sending to your selected payment gateway. This can potentially reduce fraudulent purchase attempts.',
	'security_settings_header' => 'Payment Data & Security Settings:',
	'security_settings_description' => "Global payment gateway and credit card security settings are stored here",
	'security_settings_header' => 'Data & Security Options',

	// VIEW: DISCOUNT
	//----------------------------------------
	'discount_options_header' => 'Discounts',
	'discount_channel' => 'Discount Channel',
	'discount_type' => 'Discount Settings Field',
	'discount_options_description' => 'Controls discount creation & data storage',
	
	// VIEW: DEFAULT CUSTOMER INFO
	//----------------------------------------
	'default_location_form_field_name' => 'Location',
	'default_location_header'	=> 'Default Location',
	'default_location_form_field_description' => 'Name of fields that are used to store customer location information.',
	'default_location_default_display_text' => 'Default Customer Location',
	'default_location_default_display_description' => 'The location you set below will be used as a default for tax and shipping calculations, until the customer sets their specific location.',
	'default_location_state' => 'State (for US Locations)',
	'default_location_zip' => 'Zip',
	'default_location_country_code' => 'Country Code',
	'default_location_region' => 'Region',
	'default_location_shipping_state' => 'Shipping State (for US Locations)',
	'default_location_shipping_zip' => 'Shipping Zip',
	'default_location_shipping_country_code' => 'Shipping Country Code',
	'default_location_shipping_region' => 'Shipping Region',
	
	// VIEW: SUBSCRIPTIONS
	//----------------------------------------
	'subscriptions_settings_header'	=> "Subscriptions",
	'subscriptions_settings_header_detail'	=> 'Settings & basic documentation for CartThrob subscriptions',
	
	'max_rebill_attempts' => 'Maximum Rebill Attempts',
	'process_subscriptions' => 'Process Subscriptions',
	'max_rebill_attempts_description' => 'How many times should CartThrob attempt to rebill a failed subscription payment before setting the subscription to "closed" status. (leave blank for unlimited)',
	'subscription_rebill_tax' => 'Charge tax on subscription rebills?',
	'subscription_rebill_shipping' => 'Charge shipping on subscription rebills?',
	'subscription_rebill_discount' => 'Add discounts to subscription rebills?',

 
	
	'subscriptions_cron_information'	=> '
	<p>Rebilling requires that your system be told to periodically check for expired and expiring subscriptions. You can either manually process your subscriptions, or ideally you should automate the process by using <a target="_blank" href="http://en.wikipedia.org/wiki/Cron">CRON,</a> a time based scheduling application already available to most webhosts. Listed are several methods of using CRON. Choose the method that works best with your hosting platform.</p>
	
	<h3>CRON Configuration</h3>
	<p>Select the method that best fits your server. If you are unsure, ask your host administrator which method would work best for you.</p> 
	<h4>Method 1. Can you execute Shell scripts on your server?</h4>
	<p>Set this command in CRON</p>
	<p class="terminal">%sthird_party/cartthrob/lib/cron_subscriptions.sh %sthird_party/cartthrob/lib/extload.php</p>
	
	<h4>Method 2. Can you run perl scripts on your server?</h4>
	<p>Set this command in CRON</p>
	<p class="terminal">%sthird_party/cartthrob/lib/cron_subscriptions.pl %sthird_party/cartthrob/lib/extload.php</p>
	
	<h4>Method 3. Does your server allow the use of PHP\'s "passthru" function?</h4>
	<p>'.((@is_callable('passthru'))? 'In testing, it looks like your server is capable of running "passthru" You can use the following command' :'In testing, it does not look like you can use the following command in your cron script').'</p>
	<p class="terminal">%sthird_party/cartthrob/lib/extload.php cron process_subscriptions</p>
	
	<h4>Method 4. If none of the above methods work...</h4>
	<p>It is possible (though slow and not preferred) that you can manually call your script to update via a URL. You can use an online service like <a href="http://www.webbasedcron.com/">WebBasedCron</a> to call this URL as often as once a minute. This method only processes one subscription per URL call, so depending on your subscription volume, you may need to call it very often to process all of your subscriptions.</p>
	<p class="terminal">%s</p>
	
	<p>or via CURL</p>
	
	<p class="terminal">curl --silent "%s" 2&gt;&amp;1 /dev/null</p>
	
	<h4>Method 5. Finally, you can choose to run subscriptions manually, using the button below.</h4>',
	
 	'valid_gateways' => 'Valid Gateways',
	'valid_gateways_description' => 'The following gateways are compatible with CartThrob Subscriptions',
	'no_valid_subscription_gateways' => 'You have no valid subscription gateways. Please update your CartThrob installation.',
	'manually_process_subscriptions' => 'Process subscriptions now',
	
	//template installer
	//----------------------------------------
	'error_no_simplexml' => 'You must have SimpleXML enabled/installed',
	'error_blank_xml' => 'You submitted blank XML',
	'error_xml_error' => 'You submitted invalid XML',
	'error_no_data' => 'You submitted XML with no data',
	'error_category_exists' => 'The category <em>%s</em> already exists and could not be installed',
	'error_field_exists' => 'The field <em>%s</em> already exists and could not be installed',
	'error_field_group_exists' => 'The field group <em>%s</em> already exists and could not be installed',
	'error_member_group_exists' => 'The member group <em>%s</em> already exists and could not be installed',
	'error_template_exists' => 'The template <em>%s</em> already exists and could not be installed',
	'error_template_group_exists' => 'The template group <em>%s</em> already exists and could not be installed',
	'error_channel_exists' => 'The channel <em>%s</em> already exists and could not be installed',
	
	'installed_field' => 'Successfully installed the field <em>%s</em>',
	'installed_field_group' => 'Successfully installed the field group <em>%s</em>',
	'installed_member_group' => 'Successfully installed the member group <em>%s</em>',
	'installed_template' => 'Successfully installed the template <em>%s</em>',
	'installed_template_group' => 'Successfully installed the template group <em>%s</em>',
	'installed_channel' => 'Successfully installed the channel <em>%s</em>',
	
	// STATUS
	//----------------------------------------
	'ct_processing_status'	 => 'When order is taken, set processing status to:',  
	
	'orders_default_status'=>'Payment authorized/complete',
	'orders_processing_status'=>'Payment being processed',
	'orders_failed_status'=>'Payment failed',
	'orders_declined_status'=>'Payment declined',    
	
	'purchased_items_default_status' => 'Payment authorized/complete',
	'purchased_items_declined_status' => 'Payment declined',
	'purchased_items_processing_status' => 'Payment being processed',
	'purchased_items_failed_status' => 'Payment failed',
	  
	'status_reversed'		=> 'Payment reversed',
	'status_refunded'		=> 'Payment refunded',    
	'status_voided'			=> 'Payment voided',  
	'status_expired'		=> 'Payment expired',
	'status_canceled'		=> 'Payment canceled',
	'status_pending'		=> 'Payment pending/on hold',
	'status_offsite'		=> 'Transaction in process offsite ',
	
	// SHIPPING PLUGINS 
	//---------------------------------------- 
	'title_by_location_quantity_threshold' => 'By Location - Quantity Threshold',
	'title_by_location_price_threshold' => 'By Location - Price Threshold',
	'title_by_location_weight_threshold' => 'By Location - Weight Threshold',
	'title_price_threshold' => 'By Price - Threshold',
	'title_by_quantity_threshold' => 'By Quantity - Threshold',
	'title_by_weight_global_rate' => 'By Weight - Global Rate',
	'title_by_weight_threshold' => 'By Weight - Threshold',
	'title_flat_rates' => 'Customer Selectable Flat Rates',
	'title_per_location_rates' => 'Per Location Rates',
	
	// A
	'amount_off' => 'Amount Off',
	'amount_off_note' => 'Enter the amount to subtract from the subtotal. NUMERIC VALUES ONLY.',
	'amount_off_over_x_title' => 'Amount Off For Orders Over X',
	'amount_off_product_title' => 'Amount Off Product',
	'add_tax'	=> 'Add a tax',
	'all_values'	=> 'Attributes',
	
	// B
	'backup_location_field' => 'Backup Location Field',
	'buy_x_get_x'	=> 'Buy X Get X',
	'by_location_price_threshold_note' => 'Costs are set at price intervals for each location.',
	'by_weight' => 'By Weight',
	'by_item'	=> 'By Item',
	'by_weight_global_rate_note' => 'Costs are charged by weight, so products must be assigned a weight.',
	'by_weight_threshold_note' => 'Costs are set at weight intervals, so products must be assigned a weight',

	// C
	'complete'	=> 'Complete',
	'calculate_costs' => 'Calculate costs',
	'charge_default_by' => 'Charge Default By',
	'cost_per_transaction' => 'Cost Per Transaction',
	'costs_are_set_at' => 'Costs are set at quantity intervals of',
	'charge_by_location'	=> 'Charge by location',
	
	'ct_offline_transaction_id'			 => 'OFFLINE PAYMENT',
	'ct_pay_by_check_transaction_id'	=> 'CHECK PAYMENT',
	'ct_pay_by_phone_transaction_id'	=> 'PHONE PAYMENT',
	'ct_pay_by_account_transaction_id'	=> 'CREDIT ACCOUNT PAYMENT',

	'ct_offline_title' => 'Offline Payments',
	'ct_pay_by_check_title' => 'Pay By Check',
	'ct_pay_by_phone_title' => 'Pay By Phone',
	'ct_pay_by_account_title' => 'Bill to Credit Account',

	
	'ct_pay_by_check_overview'	=> 'This payment method is intended for use when you will be collecting a check from the customer at a later date.', 
	'ct_offline_overview' => 'This is intended to be used on transactions where you will be taking payments offline',
	'ct_pay_by_account_overview'	=> 'This payment method is intended for use when you will be applying a charge to the customer\'s existing credit account.', 
	'ct_pay_by_phone_overview'	=> 'This payment method is intended for use when you will be collecting payment from the customer via the phone.', 
	
	// D
	'default_cost_per_item' => 'Default Cost per Item',
	'default_setting' => 'Default Setting?',
	'declined'	=> 'Declined',
	'discount_quantity'	=> 'Discount Quantity',
	
	// E
	'enter_required_minimum' => 'Enter the required order minimum. NUMERIC VALUES ONLY.',
	'enter_the_purchase_quantity'	=> 'Enter the quantity required to activate this discount',
	'enter_the_number_of_items'	=>"Enter the number of items to be discounted",
	'enter_the_percentage_discount'	=> 'Enter the percentage discount (if any.) If left blank, Amount Off will be used instead)',
	'enter_the_discount_amount'	=> 'Enter the amount discount (if any.) If left blank, Percentage Off will be applied instead)',
	'entry_id'			=> 'entry id',
	
	// F
	'flat_rate' => 'Flat Rate',
	'flat_rates_note' => 'One option is selected per transaction by customer',
	'free_order' => 'Free Order',
	'free_shipping' => 'Free Shipping',
	'free_shipping_over_x_title'	=> 'Free Shipping Over X',
	'failed'	=> 'Failed',
	'from_quantity' => 'From Quantity',
	'field_maxl'	=> 'Max Length',
	'field_max_length'	=> 'Max Length',
	// G
	
	'group_by'		=> 'Group discount by',
	'grou_by_note'	=> 'Items may be listed with a quantity as one line item or may appear as multiple line items with different options. If you choose to group by entry id, all items with the matching entry id, regardless of cost or options  will be considered to qualify for this discount. If grouping by line item, each line item will be checked individually to see it required quantities are met',
	
	// I
	'if_order_over' => 'If order over',
	'in_channel_field' => 'In channel field',
	
	// L
	'limit_by_quantity_title' => 'Item specific discount limited by quantity',
	'location_countries' => 'Location: Countries',
	'location_states' => 'Location: States',
	'location_threshold' => 'Location (separate multiple locations with a comma)<br />Use GLOBAL to set defaults',
	'location_threshold_overview' => 'Costs are set at weight intervals for each location.',
	'location_zip_regions' => 'Location: Zip/Regions',
	'log_errors_to_file'	=> 'Log errors to file?',
	'line_item'			=> 'line items',

	// M
	'months_off'		=> 'Number of free months (including current month)', 
	'multiply_rate_and_weight' => 'Multiply rate and weight',
	'multiply_rate_by_quantity' => 'Multiply rate by quantity',
	'minimum_quantity_required_for_discount'	=> 'Minimum quantity required for discount',

	// N
	'name_column_prompt'		=> 'Name of new column',
	'no_shipping_returned'		=> 'No shipping data was returned',
	'numeric'				=> 'Numeric',
	
	// P
	'per_item_limit' => 'Per Item Limit',
	'per_item_limit_note' => 'If there is a limit to the number of items that can be discounted, add that limit here.',
	'per_location_rates_overview' => '
	<p>Per location rates allows you to set rates on an item by item, category, or custom field basis. </p>
	<p>At least one value must be set for COST, LOCATION and PRODUCT.</p>
	<p>If customer location is set and matches any location found in the Zip, State, and Country_code, or "GLOBAL" is
		set in any of these fields, products in the customers cart will be compared to entries set in the Entry_id, Category_id or Custom field.  You can separate multiple codes with a comma. For instance, "1,2,3" could be set in the entry_ids field, or "cd,vinyl" could be set to match channel content found in a "Package Type" channel custom field. You must have a value set in one of the product fields for rates to be calculated. Use "GLOBAL" to make the setting apply to all entries</p>
	<p>If the "Default Cost Per Item" is set, even products not found in one of these settings will have this base shipping price applied.</p>',
	'per_location_rates_note' => 'Costs are set at price intervals for each location.',
	'percentage_off' => 'Percentage Off',
	'percentage_off_over_x' => 'Percentage Off For Orders Over X',
	'percentage_off_over_x_packages' => 'Percentage Off For Packages Over X',
	'percentage_off_over_x_quantity_packages' => 'Percentage Off For Packages Quantities Over X',
	'percentage_off_note' => 'Enter the percentage to subtract from the subtotal. NUMERIC VALUES ONLY.',
	'percentage_off_single_product_title' => 'Percentage Off A Single Product',
	'price_threshold' => 'Price Threshold',
	'price_threshold_example' => '(ex: $0-$10 would be entered as: <span class="red">10</span>)',
	'price_threshold_overview' => 'Costs are set at price intervals',
	'primary_location_field' => 'Primary Location Field',
	'product_cat_ids' => 'Product: Cat IDs',
	'product_channel_content' => 'Product: Channel Content',
	'product_entry_id' => 'Product entry_id',
	'product_entry_ids' => 'Product: Entry IDs',
	'purchase_quantity'	=> 'Purchased Quantity',
	'percentage_off_categories' => 'Percentage Off Categories',
	
	// Q
	'qualifying_entry_ids' => 'Qualifying entry_ids',
	'qualifying_entry_ids_note' => 'If this applies only to certain items add entry IDs here. Separate multiple entry_ids by comma',
	'quantity_threshold' => 'Quantity Threshold',
	'quantity_threshold_example' => '(ex: 0-10 items would be entered as: <span class="red">10</span>)',
	'quantity_threshold_overview' => 'Costs are set at quantity intervals for each location.',

	// P
	'processing'	=> 'Processing',
	
	// R
	'rate_amount' => 'Rate amount',
	'rate_amount_times_cart_total' => 'Rate amount * cart total',
	'rate_example' => '(ex: $10.95 would be entered as: <span class="red">10.95</span>)',

	// S
	'separate_multiple_entry_ids_by_comma' => 'Separate multiple entry_ids by comma',
	'separate_multiple_entry_ids'	=> 'Separate multiple entry_ids by comma',
	'set_shipping_cost_by' => 'Set shipping cost by',
	'setting_short_name' => 'Short Name (shipping code, etc.)',
	'setting_title' => 'Title (descriptive name)',
	'shipping_is_free_at' => 'Shipping is free at (optional; ex. 99)',
	'single_flat_rate'	=> 'One single flat rate for the entire purchase',
	'state_country' => 'State/Country',
	'subs_free_months'	=> 'Free Trial Subscription', 

	// U
	'use_rate_as_shipping_cost' => 'Use rate as shipping cost',
	'use_shipping_address' => 'Use shipping address?',
	'up_to_quantity' => 'Up to Quantity',

	// W
	'weight_threshold' => 'Weight Threshold',
	'weight_threshold_example' => '(ex: 10 lbs would be entered as: <span class="red">10</span>)',

	// Z
	'zip_region' => 'Zip/Region',
	
	// THE END. 
	'thats_all_there_is'=>'And they lived happily ever after. The End.'

);
