<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['cartthrob_default_settings'] = array(
	'customer_info_defaults' => array(
		'first_name' => '',
		'last_name' => '',
		'address' => '',
		'address2' => '',
		'city' => '',
		'state' => '',
		'zip' => '',
		'country' => '',
		'country_code' => '',
		'company' => '',
	
		'phone' => '',
		'email_address' => '',
		'ip_address' => '',
		'description' => '',
		'use_billing_info' => '',
	
		'shipping_first_name' => '',
		'shipping_last_name' => '',
		'shipping_address' => '',
		'shipping_address2' => '',
		'shipping_city' => '',
		'shipping_state' => '',
		'shipping_zip' => '',
		'shipping_country' => '',
		'shipping_country_code' => '',
		'shipping_company' => '',
		'shipping_region' => '',
	
		'CVV2' => '',
		'card_type' => '',
		'expiration_month' => '',
		'expiration_year' => '',
		'begin_month' => '',
		'begin_year' => '',
		'bday_month' => '',
		'bday_day' => '',
		'bday_year' => '',
	
		'currency_code' => '',
		'language' => '',
	
		'shipping_option' => '',
		'weight_unit' => '',
	
		'region' => '',
	
		'success_return' => '',
		'cancel_return' => '',
	
		'gateway'		=> '', 
		
		'po_number' => '',
		'card_code' => '',
		'issue_number' => '',
		'transaction_type' => '',
		'bank_account_number' => '',
		'bank_account_name'  => '', 
		'bank_name'  => '', 
		'check_type' => '',
		'account_type' => '',
		'routing_number' => '',
	
		// MEMBER CREATION
		'username' => '', 
		'screen_name' => '',
		//'password' => '',
		//'password_confirm' => '', 
		//'create_member' => '',
		//'group_id' => '',
		
		// RECURRENT BILLING
		// these have been removed because they conflict with individual subscription variables
		#'subscription'	=> '', 
		#'subscription_name' => '',
		#'subscription_total_occurrences' => '',
		#'subscription_trial_price' => '',
		#'subscription_trial_occurrences' => '',
		#'subscription_start_date' => '',
		#'subscription_end_date' => '',
		#'subscription_interval_length' => '', // pay every X 
		#'subscription_interval_units' => '', // D, W, M, Y
		#'subscription_allow_modification' => '', // can subscribers change subscription
		#'subscription_type'	=> '',
	),
	'admin_email' => '',
	'log_email'	=> 'no',
	'low_stock_email' => '',
	'license_number' => '',
	'logged_in' => '0',
	'default_member_id' => '',
	'clear_cart_on_logout' => '1',
	'session_expire' => '18000',
	'allow_gateway_selection' => '1',
	'encode_gateway_selection' => 1,
	'encrypted_sessions' => 1,
	'session_use_fingerprint' => '1',
	'session_fingerprint_method' => '3',
	'allow_products_more_than_once' => '1',
	'allow_empty_cart_checkout' => '0',
	'show_debug' => '1',
	'product_split_items_by_quantity' => '0',
	'product_channels' => array(),
	'product_channel_fields' => array(),
	'save_orders' => '0',
	'orders_channel' => '',
	'orders_sequential_order_numbers' => '1',
	'orders_items_field' => '',
	'orders_subtotal_field' => '',
	'orders_subtotal_plus_tax_field' => '',
	'orders_tax_field' => '',
	'orders_shipping_field' => '',
	'orders_shipping_plus_tax_field' => '',
	'orders_discount_field' => '',
	'orders_total_field' => '',
	'orders_status_field' => '',
	'orders_status'	=> array(   
		'completed',    	// standard finished transaction
		'processing',		// currently being processed by third parties
		'declined',			// credit agency declined transaction
		'failed',			// failure when attempting to complete a transaction
		//'canceled',			// order cancelled by customer
		//'expired',			// credit agency reports that the transaction has expired
		//'voided',		
		//'refunded',
		//'reversed',			// reversed
		//'pending_payment'	// on hold
	),
	'orders_default_status' => '',
	'orders_processing_status' => '',
	'orders_declined_status' => '',
	'orders_failed_status' => '',
	'orders_status_reversed' => '',
	'orders_status_refunded' => '',
	'orders_status_voided' => '',
	'orders_status_canceled' => '',
	'orders_status_expired' => '',
	'orders_status_pending' => '',
	'orders_status_offsite' => '',
	'purchased_items_status_reversed' => '',
	'purchased_items_status_refunded' => '',
	'purchased_items_status_voided' => '',
	'purchased_items_status_canceled' => '',
	'purchased_items_status_expired' => '',
	'purchased_items_status_pending' => '',
	'purchased_items_status_offsite' => '',
	'orders_transaction_id' => '',
	'orders_last_four_digits' => '',
	'orders_convert_country_code' => '',
	'orders_coupon_codes' => '',
	'orders_customer_name' => '',
	'orders_customer_email' => '',
	'orders_customer_ip_address' => '',
	'orders_customer_phone' => '',
	'orders_full_billing_address' => '',
	'orders_billing_first_name' => '',
	'orders_billing_last_name' => '',
	'orders_billing_company' => '',
	'orders_billing_address' => '',
	'orders_billing_address2' => '',
	'orders_billing_city' => '',
	'orders_billing_state' => '',
	'orders_billing_zip' => '',
	'orders_billing_country' => '',
	'orders_country_code' => '',
	'orders_full_shipping_address' => '',
	'orders_shipping_first_name' => '',
	'orders_shipping_last_name' => '',
	'orders_shipping_company' => '',
	'orders_shipping_address' => '',
	'orders_shipping_address2' => '',
	'orders_shipping_city' => '',
	'orders_shipping_state' => '',
	'orders_shipping_zip' => '',
	'orders_shipping_country' => '',
	'orders_shipping_country_code' => '',
	'orders_shipping_option' => '',
	'orders_license_number_field' => '',
	'orders_license_number_type' => 'uuid',
	'orders_error_message_field' => '',
	'orders_payment_gateway' => '',
	'orders_language_field' => '',
	'orders_title_prefix' => 'Order #',
	'orders_title_suffix' => '',
	'orders_url_title_prefix' => 'order_',
	'orders_url_title_suffix' => '',
	'orders_subscription_id' => '',
	'orders_vault_id' => '',
	'orders_site_id'	=> '', 
	'save_purchased_items' => '0',
	'save_packages_too' => '0',
	'purchased_items_channel' => '',
	'purchased_items_default_status' => '',
	'purchased_items_processing_status' => '',
	'purchased_items_declined_status' => '',
	'purchased_items_failed_status' => '',
	'purchased_items_id_field' => '',
	'purchased_items_quantity_field' => '',
	'purchased_items_order_id_field' => '',
	'purchased_items_license_number_field' => '',
	'purchased_items_discount_field' => '',
	'purchased_items_package_id_field' => '',
	'purchased_items_price_field' => '',
	'purchased_items_title_prefix' => '',
	'purchased_items_license_number_type' => 'uuid',
	'approve_orders' => FALSE,
	'rounding_default' => 'standard',
	'global_item_limit' => '0',
	'global_coupon_limit' => '1',
	'tax_settings' => '',
	'tax_use_shipping_address' => '0',
	'coupon_code_channel' => '',
	'enable_logging' => '0',
	'cp_menu' => '1',
	'cp_menu_label' => '',
	'checkout_form_captcha' => '0',
	'coupon_code_field' => 'title',
	'coupon_code_type' => '',
	'discount_channel' => '',
	'discount_type' => '',
	'payment_gateway' => 'Cartthrob_ct_offline_payments',
	'low_stock_level' => '5',
	'send_email' => '1',
	'send_confirmation_email' => '1',
	'send_inventory_email' => '0',
	'send_customer_declined_email' => 0,
	'send_customer_processing_email' => 0,
	'send_customer_failed_email' => 0,
	'send_admin_declined_email' => 0,
	'send_admin_failed_email' => 0,
	'send_admin_processing_email' => 0,
	'store_checkout_page'    => 'checkout',
	'store_google_code'			=> '',
	'store_phone'			=> '',
	'store_about_us'         => '<p>Putamus parum claram anteposuerit litterarum formas humanitatis per seacula quarta decima et quinta decima eodem. Tempor cum soluta nobis eleifend, option congue nihil. At vero eros et accumsan et iusto odio dignissim qui blandit praesent.</p>
<p>Nisl ut aliquip ex ea commodo consequat duis autem vel eum. Ut laoreet dolore magna aliquam erat volutpat ut wisi enim ad minim veniam. Formas humanitatis per, seacula quarta decima et quinta.</p>
<p>Per seacula quarta decima et quinta decima eodem modo typi qui nunc nobis? Cum soluta nobis eleifend option congue nihil imperdiet doming id quod mazim placerat facer possim? Adipiscing elit sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat ut. Claritas est etiam processus dynamicus qui sequitur mutationem consuetudium lectorum mirum est notare quam. Ea commodo consequat duis autem vel eum iriure dolor in hendrerit in?</p>',
	'store_description'      => '<p>Dedicated to bringing you the best!</p>',
	'store_country'          => 'USA',
	'store_zip'              => '63102',
	'store_state'            => 'MO',
	'store_city'             => 'St. Louis',
	'store_address2'         => '',
	'store_address1'         => 'Walnut Street',
	'store_name'             => 'Store',
	'store_shipping_estimate'	=> 'Expected shipping time is 3-7 days',
	'email_order_confirmation' => '{preload_replace:headline="THANK YOU FOR YOUR ORDER"}
	{exp:cartthrob:submitted_order_info}
		<table width="600" cellpadding="5" cellspacing="0">
			<tr>
				<td valign="top" align="left" style="font-size:12px;color:#000000;font-family:arial, sans-serif;"><br>
					<p> <span style="font-size:16px;font-weight:bold;">{headline}</span> </p>
					<table cellspacing="0" cellpadding="2" bgcolor="#000000" width="100%">
						<tr>
							<td><span style="color:#ffffff;font-size:14px;">Order Data</span></td>
						</tr>
					</table>
					<table cellspacing="0" cellpadding="2" width="100%">
						<tr>
							<td valign="top">
								<span style="font-size:12px;font-weight:bold;">Order Date: </span> 
								<span style="font-size:12px;">{entry_date format="%M %D %Y"}</span>
							</td>
						</tr>
					</table>
					<table cellspacing="0" cellpadding="0" width="100%">
						<tr>
							<td valign="top">
								<span style="font-size:12px; font-weight:bold;">Order ID: </span> 
								<span style="font-size:12px;">{title}</span>
							</td>
						</tr>
					</table>
					<hr>
					<table cellspacing="0" cellpadding="0" width="100%">
						<tr>
							<td width="250" valign="top">
								<span style="font-size:14px; font-weight:bold; ">Billing</span><br>
								<span style="font-size:12px; ">	 
									{order_billing_first_name} {order_billing_last_name}<br>
									{order_billing_address}<br>
									{if order_billing_address2}{order_billing_address2}<br>{/if}
									{order_billing_city}, {order_billing_state} {order_billing_zip}<br>
									{if order_country_code}{order_country_code}<br>{/if}
									{order_customer_email}<br>
									{order_customer_phone}
								</span>
							</td>
							<td valign="top">
								<span style="font-size:14px; font-weight:bold;">Shipping</span><br>
								<span style="font-size:12px; ">	 
									{if order_shipping_address}
										{order_shipping_first_name} {order_shipping_last_name}<br>
										{order_shipping_address}<br>
										{if order_shipping_address2}{order_shipping_address2}<br>{/if}
										{order_shipping_city}, {order_shipping_state} {order_shipping_zip}
										{if order_shipping_country_code}{order_shipping_country_code}{/if}
									{if:else}
										{order_billing_first_name} {order_billing_last_name}<br>
										{order_billing_address}<br>
										{if order_billing_address2}{order_billing_address2}<br>{/if}
										{order_billing_city}, {order_billing_state} {order_billing_zip}<br>
										{if order_country_code}{order_country_code}<br>{/if}
										{order_customer_email}<br>
										{order_customer_phone}
									{/if}
								</span>
							</td>
						</tr>
					</table>
					<hr>

					Total number of purchased items: {order_items:total_results}.
					<table cellspacing="0" cellpadding="2" width="100%">
						<thead>
							<tr>
								<td><span style="font-size:12px;font-weight:bold;">ID</span></td>
								<td><span style="font-size:12px;font-weight:bold;">Description</span></td>
								<td align="right"><span style="font-size:12px;font-weight:bold;">Price</span></td>
								<td align="center">&nbsp;</td>
								<td align="right"><span style="font-size:12px;font-weight:bold;">Qty</span></td>
								<td align="right"><span style="font-size:12px;font-weight:bold;">Item Total</span></td>
								<td align="center">&nbsp;</td>
							</tr>
						</thead>
						<tbody>
							{order_items}
								<tr class="{item:switch="odd|even"}">
									<td><span style="font-size:12px;">{item:entry_id}</span></td>
									<td><span style="font-size:12px;">{item:title}</span></td>
									<td align="right"><span style="font-size:12px;">{item:quantity}</span></td>
									<td align="center">&nbsp;</td>
									<td align="right"><span style="font-size:12px;">{item:price}</span></td>
									<td align="right">
										<span style="font-size:12px;">
										{exp:cartthrob:arithmetic num1="{item:price_numeric}" num2="{item:quantity}" operator="*"}
										</span>
									</td>
									<td align="right">
									{if item:product_download_url}
										<a href="{exp:cartthrob:get_download_link field="product_download_url" entry_id="{item:entry_id}"}">Download</a>
									{/if}
									</td>
								</tr>
							{/order_items}
							<tr>
								<td><span style="font-size:12px;">&nbsp;</span></td>
								<td colspan="3">&nbsp;</td>
								<td><span style="font-size:12px;">&nbsp;</span></td>
								<td><span style="font-size:12px;">&nbsp;</span></td>
								<td align="center">&nbsp;</td>
							</tr>
						</tbody>
					</table>
					<hr>
					<table cellspacing="0" cellpadding="0" width="100%">
						<tr>
							<td align="right">
								<table cellspacing="0" cellpadding="2">
									<tr>
										<td valign="top" align="right"><span style="font-size:12px;">Shipping:</span></td>
										<td valign="top" align="right"></td>
										<td valign="top" align="right"><span style="font-size:12px;">{order_shipping}</span></td>
									</tr>
									<tr>
										<td valign="top" align="right"><span style="font-size:12px;">Tax:</span></td>
										<td valign="top" align="right"></td>
										<td valign="top" align="right"><span style="font-size:12px;">{order_tax}</span></td>
									</tr>
									<tr>
										<td valign="top" align="right">&nbsp;</td>
										<td valign="top" align="right"></td>
										<td valign="top" align="right"><span style="font-size:12px;"></span></td>
									</tr>
									<tr>
										<td valign="top" align="right"><span style="font-size:14px;font-weight:bold;">Total:</span></td>
										<td valign="top" align="right"></td>
										<td valign="top" align="right"><span style="font-size:14px;font-weight:bold;">{order_total}</span></td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	{/exp:cartthrob:submitted_order_info}',
	'email_admin_notification' => '{preload_replace:headline="ORDER INFORMATION"}
	{exp:cartthrob:submitted_order_info}
		<table width="600" cellpadding="5" cellspacing="0">
			<tr>
				<td valign="top" align="left" style="font-size:12px;color:#000000;font-family:arial, sans-serif;"><br>
					<p> <span style="font-size:16px;font-weight:bold;">{headline}</span> </p>
					<table cellspacing="0" cellpadding="2" bgcolor="#000000" width="100%">
						<tr>
							<td><span style="color:#ffffff;font-size:14px;">Order Data</span></td>
						</tr>
					</table>
					<table cellspacing="0" cellpadding="2" width="100%">
						<tr>
							<td valign="top">
								<span style="font-size:12px;font-weight:bold;">Order Date: </span> 
								<span style="font-size:12px;">{entry_date format="%M %D %Y"}</span>
							</td>
						</tr>
					</table>
					<table cellspacing="0" cellpadding="0" width="100%">
						<tr>
							<td valign="top">
								<span style="font-size:12px; font-weight:bold;">Order ID: </span> 
								<span style="font-size:12px;">{title}</span>
							</td>
						</tr>
					</table>
					<hr>
					<table cellspacing="0" cellpadding="0" width="100%">
						<tr>
							<td width="250" valign="top">
								<span style="font-size:14px; font-weight:bold; ">Billing</span><br>
								<span style="font-size:12px; ">	 
									{order_billing_first_name} {order_billing_last_name}<br>
									{order_billing_address}<br>
									{if order_billing_address2}{order_billing_address2}<br>{/if}
									{order_billing_city}, {order_billing_state} {order_billing_zip}<br>
									{if order_country_code}{order_country_code}<br>{/if}
									{order_customer_email}<br>
									{order_customer_phone}
								</span>
							</td>
							<td valign="top">
								<span style="font-size:14px; font-weight:bold;">Shipping</span><br>
								<span style="font-size:12px; ">	 
									{if order_shipping_address}
										{order_shipping_first_name} {order_shipping_last_name}<br>
										{order_shipping_address}<br>
										{if order_shipping_address2}{order_shipping_address2}<br>{/if}
										{order_shipping_city}, {order_shipping_state} {order_shipping_zip}
										{if order_shipping_country_code}{order_shipping_country_code}{/if}
									{if:else}
										{order_billing_first_name} {order_billing_last_name}<br>
										{order_billing_address}<br>
										{if order_billing_address2}{order_billing_address2}<br>{/if}
										{order_billing_city}, {order_billing_state} {order_billing_zip}<br>
										{if order_country_code}{order_country_code}<br>{/if}
										{order_customer_email}<br>
										{order_customer_phone}
									{/if}
								</span>
							</td>
						</tr>
					</table>
					<hr>

					Total number of purchased items: {order_items:total_results}.
					<table cellspacing="0" cellpadding="2" width="100%">
						<thead>
							<tr>
								<td><span style="font-size:12px;font-weight:bold;">ID</span></td>
								<td><span style="font-size:12px;font-weight:bold;">Description</span></td>
								<td align="right"><span style="font-size:12px;font-weight:bold;">Price</span></td>
								<td align="center">&nbsp;</td>
								<td align="right"><span style="font-size:12px;font-weight:bold;">Qty</span></td>
								<td align="right"><span style="font-size:12px;font-weight:bold;">Item Total</span></td>
							</tr>
						</thead>
						<tbody>
							{order_items}
								<tr class="{item:switch="odd|even"}">
									<td><span style="font-size:12px;">{item:entry_id}</span></td>
									<td><span style="font-size:12px;">{item:title}</span></td>
									<td align="right"><span style="font-size:12px;">{item:quantity}</span></td>
									<td align="center">&nbsp;</td>
									<td align="right"><span style="font-size:12px;">{item:price}</span></td>
									<td align="right">
										<span style="font-size:12px;">
										{exp:cartthrob:arithmetic num1="{item:price_numeric}" num2="{item:quantity}" operator="*"}
										</span>
									</td>
								</tr>
							{/order_items}
							<tr>
								<td><span style="font-size:12px;">&nbsp;</span></td>
								<td colspan="3">&nbsp;</td>
								<td><span style="font-size:12px;">&nbsp;</span></td>
								<td><span style="font-size:12px;">&nbsp;</span></td>
							</tr>
						</tbody>
					</table>
					<hr>
					<table cellspacing="0" cellpadding="0" width="100%">
						<tr>
							<td align="right">
								<table cellspacing="0" cellpadding="2">
									<tr>
										<td valign="top" align="right"><span style="font-size:12px;">Shipping:</span></td>
										<td valign="top" align="right"></td>
										<td valign="top" align="right"><span style="font-size:12px;">{order_shipping}</span></td>
									</tr>
									<tr>
										<td valign="top" align="right"><span style="font-size:12px;">Tax:</span></td>
										<td valign="top" align="right"></td>
										<td valign="top" align="right"><span style="font-size:12px;">{order_tax}</span></td>
									</tr>
									<tr>
										<td valign="top" align="right">&nbsp;</td>
										<td valign="top" align="right"></td>
										<td valign="top" align="right"><span style="font-size:12px;"></span></td>
									</tr>
									<tr>
										<td valign="top" align="right"><span style="font-size:14px;font-weight:bold;">Total:</span></td>
										<td valign="top" align="right"></td>
										<td valign="top" align="right"><span style="font-size:14px;font-weight:bold;">{order_total}</span></td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	{/exp:cartthrob:submitted_order_info}',
	'email_order_confirmation_subject' => 'Thank you for your order.',
	'email_admin_notification_subject' => 'An order has been placed',
	'email_order_confirmation_from' => '',
	'email_admin_notification_from' => '{customer_email}',
	'email_order_confirmation_from_name' => 'Web store',
	'email_admin_notification_from_name' => '{customer_name}',
	'notifications' => array(
		 array(
			'email_subject' => 'Thank you for your purchase',
			'email_from_name' => 'Store owner',
			'email_from' => 'you@example.com',
			'email_to' => '{customer_email}',
			'email_template' => 'cart/email_customer',
			'email_event' => 'completed',
			'status_start' => 'ANY',
			'status_end' => 'ANY',
			'email_type' => 'html',
		),
		 array(
			'email_subject' => 'A Purchase Has Been Made',
			'email_from_name' => '{customer_name}',
			'email_from' => '{customer_email}',
			'email_to' => 'you@example.com',
			'email_template' => 'cart/email_admin',
			'email_event' => 'completed',
			'status_start' => 'ANY',
			'status_end' => 'ANY',
			'email_type' => 'html',
		),
		 array(
			'email_subject' => 'Low Stock Alert',
			'email_from_name' => 'Store Owner',
			'email_from' => 'you@example.com',
			'email_to' => 'you@example.com',
			'email_template' => 'cart/email_low_stock',
			'email_event' => 'low_stock',
			'status_start' => 'ANY',
			'status_end' => 'ANY',
			'email_type' => 'html',
		),
		 array(
			'email_subject' => 'Order Status Has Changed',
			'email_from_name' => 'Store Owner',
			'email_from' => 'you@example.com',
			'email_to' => 'you@example.com',
			'email_template' => 'cart/email_status_change',
			'email_event' => '',
			'status_start' => 'ANY',
			'status_end' => 'ANY',
			'email_type' => 'html',
		),
	),
	'email_inventory_notification_from_name' => 'Inventory Alert',
	'email_inventory_notification_from' => '',
	'email_inventory_notification_subject' => 'Low stock inventory notification',
	'email_inventory_notification' => 'Low stock notification for item number ENTRY_ID. Stock level currently at STOCK_LEVEL',
	'email_admin_notification_plaintext' => '0',
	'email_low_stock_notification_plaintext' => '0',
	'email_order_confirmation_plaintext' => '0',
	'tax_plugin' => 'Cartthrob_tax_default',
	'shipping_plugin' => 'Cartthrob_shipping_by_location_quantity_threshold',
	'auto_force_https' => FALSE,
	'force_https_domain' => '',
	'number_format_defaults_decimals' => '2',
	'number_format_defaults_dec_point' => '.',
	'number_format_defaults_thousands_sep' => ',',
	'number_format_defaults_prefix' => '$',
	'number_format_defaults_prefix_position' => 'BEFORE',
	'number_format_defaults_space_after_prefix' => FALSE,
	'number_format_defaults_currency_code' => 'USD',
	'save_member_data' => '1',
	'modulus_10_checking' => '0',
	'tax_inclusive_price' => 0,
	'checkout_registration_options' => 'auto-login',
	'member_first_name_field' => '',
	'member_last_name_field' => '',
	'member_address_field' => '',
	'member_address2_field' => '',
	'member_city_field' => '',
	'member_state_field' => '',
	'member_zip_field' => '',
	'member_country_field' => '',
	'member_country_code_field' => '',
	'member_company_field' => '',
	'member_phone_field' => '',
	'member_email_address_field' => 'email',
	'member_use_billing_info_field' => '',
	'member_shipping_first_name_field' => '',
	'member_shipping_last_name_field' => '',
	'member_shipping_address_field' => '',
	'member_shipping_address2_field' => '',
	'member_shipping_city_field' => '',
	'member_shipping_state_field' => '',
	'member_shipping_zip_field' => '',
	'member_shipping_country_field' => '',
	'member_shipping_country_code_field' => '',
	'member_shipping_company_field' => '',
	'member_language_field' => '',
	'member_shipping_option_field' => '',
	'member_region_field' => '',
	'default_location' => array(
		'state' => 'NY',
		'zip' => '10020',
		'country_code' => 'USA',
		'region' => '',
		'shipping_state' => 'NY',
		'shipping_zip' => '10020',
		'shipping_country_code' => 'USA',
		'shipping_region' => '',
	),
	'garbage_collection_cron' => '0',
	'price_modifier_presets' => array(
		'Small/Medium/Large' => array(
			 array(
				'option_value' => 'S',
				'option_name' => 'Small',
				'price' => '',
			),
			 array(
				'option_value' => 'M',
				'option_name' => 'Medium',
				'price' => '',
			),
			 array(
				'option_value' => 'L',
				'option_name' => 'Large',
				'price' => '',
			),
		),
	),
	'clear_session_on_logout' => '0',
	'locales_countries' => '',
	'last_edited_gateway' => 'Cartthrob_ct_offline_payments',
	'gateways_format'	=> 'bootstrap',
	'available_gateways' => array(
		'Cartthrob_authorize_net' => '1',
		'Cartthrob_ct_offline_payments' => '1',
		'Cartthrob_paypal_express' => '1',
	),
	'reports' => array(
		 array(
			'name' => 'Orders by Logged in Admin',
			'template' => 'cart/sample_report',
		),
	),
	'Cartthrob_dev_template_settings' => array(
		'mode' => 'always_succeed',
		'gateway_fields_template' => '',
	),
	'Cartthrob_authorize_net_settings' => array(
		'api_login' => '',
		'transaction_key' => '',
		'email_customer' => 'no',
		'mode' => 'test',
		'dev_api_login' => '',
		'dev_transaction_key' => '',
		'hash_value' => '',
		'transaction_settings' => 'AUTH_CAPTURE',
		'gateway_fields_template' => '',
	),
	'Cartthrob_authorize_net_sim_settings' => array(
		'email_customer' => 'no',
		'mode' => 'test',
		'tax_inclusive' => 'Y',
		'api_login' => '',
		'transaction_key' => '',
		'dev_api_login' => '',
		'dev_transaction_key' => '',
		'hash_value' => '',
		'transaction_settings' => 'AUTH_CAPTURE',
		'x_header_html_payment_form' => 'cart/.footer_nav',
		'x_footer_html_payment_form' => 'cart/.footer_nav',
		'x_color_background' => '#FFFFFF',
		'x_color_link' => '#FF0000',
		'x_color_text' => '#000000',
		'x_logo_url' => '',
		'x_background_url' => '',
		'gateway_fields_template' => '',
	),
	'Cartthrob_anz_egate_settings' => array(
		'access_code' => '',
		'merchant_id' => '',
		'gateway_fields_template' => '',
	),
	'Cartthrob_beanstream_direct_settings' => array(
		'merchant_id' => '',
		'gateway_fields_template' => '',
	),
	'Cartthrob_echo_nvp_settings' => array(
		'merchant_echo_id' => '123>1234567',
		'merchant_echo_pin' => '12345678',
		'transaction_type' => 'ES',
		'mode' => 'test',
		'gateway_fields_template' => '',
	),
	'Cartthrob_thirdparty_nab_transact_settings' => array(
		'merchant_id' => 'XYZ0010',
		'password' => 'abcd1234',
		'test_mode' => 'test',
		'test_response' => '200',
		'gateway_fields_template' => '',
	),
	'Cartthrob_ogone_direct_settings' => array(
		'mode' => 'test',
		'pspid_live' => '',
		'pspid_test' => '',
		'api_userid' => '',
		'api_password' => '',
		'passphrase' => '',
		'gateway_fields_template' => '',
	),
	'Cartthrob_samurai_settings' => array(
		'mode' => 'sandbox',
		'merchant_key' => '',
		'merchant_password' => '',
		'processor_token' => '',
		'gateway_fields_template' => '',
	),
	'Cartthrob_stripe_settings' => array(
		'mode' => 'test',
		'api_key_test_publishable' => '',
		'api_key_test_secret' => '',
		'api_key_live_publishable' => '',
		'api_key_live_secret' => '',
		'gateway_fields_template' => '',
	),
	'Cartthrob_transaction_central_settings' => array(
		'merchant_id' => '',
		'reg_key' => '',
		'gateway_fields_template' => '',
	),
	'Cartthrob_ct_pay_by_account_settings' => array(
		'processing_status' => 'complete',
		'gateway_fields_template' => '',
	),
	'Cartthrob_cartthrob_direct_settings' => array(
		'username' => '',
		'password' => '',
		'dev_username' => '',
		'dev_password' => '',
		'mode' => 'no_account',
		'gateway_fields_template' => '',
	),
	'Cartthrob_quantum_settings' => array(
		'gateway_login' => '',
		'restrict_key' => '',
		'email_customer' => '0',
		'gateway_fields_template' => '',
	),
	'Cartthrob_eway_settings' => array(
		'customer_id' => '87654321',
		'payment_method' => 'REAL-TIME',
		'test_mode' => 'test',
		'test_response' => '100',
		'gateway_fields_template' => '',
	),
	'Cartthrob_linkpoint_settings' => array(
		'store_number' => '',
		'keyfile' => 'yourcert_file_name.pem',
		'test_store_number' => '',
		'test_keyfile' => 'yourcert_file_name.pem',
		'test_mode' => 'good',
		'gateway_fields_template' => '',
	),
	'Cartthrob_moneris_direct_settings' => array(
		'store_id' => '',
		'api_token' => '',
		'avs' => 'no',
		'mode' => 'test',
		'test_store_id' => 'store1',
		'test_total' => '10.00',
		'gateway_fields_template' => '',
	),
	'Cartthrob_ct_offline_payments_settings' => array(
		'processing_status' => 'complete',
		'gateway_fields_template' => '',
	),
	'Cartthrob_ct_pay_by_check_settings' => array(
		'processing_status' => 'complete',
		'gateway_fields_template' => '',
	),
	'Cartthrob_ct_pay_by_phone_settings' => array(
		'processing_status' => 'complete',
		'gateway_fields_template' => '',
	),
	'Cartthrob_paypal_express_settings' => array(
		'api_username' => '',
		'api_password' => '',
		'api_signature' => '',
		'test_username' => '',
		'test_password' => '',
		'test_signature' => '',
		'mode' => 'test',
		'allow_note' => 'no',
		'show_item_id' => 'yes',
		'show_item_options' => 'no',
		'solutiontype' => 'Mark',
		'shipping_settings' => 'editable_shipping',
		'payment_action' => 'Sale',
		'gateway_fields_template' => '',
	),
	'Cartthrob_paypal_pro_settings' => array(
		'api_username' => '',
		'api_password' => '',
		'api_signature' => '',
		'test_username' => '',
		'test_password' => '',
		'test_signature' => '',
		'payment_action' => 'Sale',
		'test_mode' => 'test',
		'country' => 'us',
		'api_version' => '60.0',
		'gateway_fields_template' => '',
	),
	'Cartthrob_thirdparty_psigate_settings' => array(
		'mode' => 'test',
		'test_mode_response' => 'R',
		'store_key' => '',
		'gateway_fields_template' => '',
	),
	'Cartthrob_worldpay_redirect_settings' => array(
		'installation_id' => '',
		'test_mode' => 'test',
		'order_complete_template' => 'cart/.footer_nav',
		'gateway_fields_template' => '',
	),
	'Cartthrob_realex_remote_settings' => array(
		'your_merchant_id' => '',
		'your_secret' => '',
		'gateway_fields_template' => '',
	),
	'currency_code' => 'GBP',
	'Cartthrob_sage_settings' => array(
		'mode' => 'test',
		'vendor_name' => '',
		'gateway_fields_template' => '',
	),
	'Cartthrob_sage_s_settings' => array(
		'profile' => 'NORMAL',
		'mode' => 'test',
		'vendor_name' => '',
		'gateway_fields_template' => '',
	),
	'Cartthrob_sage_us_settings' => array(
		'test_mode' => 'test',
		'm_id' => '',
		'm_key' => '',
		'm_test_id' => '',
		'm_test_key' => '',
		'gateway_fields_template' => '',
	),
	'Cartthrob_ct_save_order_settings' => array(
		'processing_status' => 'complete',
		'gateway_fields_template' => '',
	),
	'last_order_number' => '0',
	'Cartthrob_shipping_by_location_price_threshold_settings' => array(
		'mode' => 'rate',
		'location_field' => 'shipping_country_code',
		'backup_location_field' => 'country_code',
		'thresholds' => array(
			 array(
				'location' => '',
				'rate' => '',
				'threshold' => '',
			),
		),
	),
	'Cartthrob_shipping_by_location_quantity_threshold_settings' => array(
		'mode' => 'price',
		'location_field' => 'shipping_country_code',
		'backup_location_field' => 'country_code',
		'thresholds' => array(
			 array(
				'location' => 'USA',
				'rate' => '5',
				'threshold' => '1',
			),
			 array(
				'location' => 'USA',
				'rate' => '7.5',
				'threshold' => '10',
			),
			 array(
				'location' => 'USA',
				'rate' => '10',
				'threshold' => '50',
			),
			 array(
				'location' => 'GLOBAL',
				'rate' => '10',
				'threshold' => '1',
			),
			 array(
				'location' => 'GLOBAL',
				'rate' => '20',
				'threshold' => '10',
			),
			 array(
				'location' => 'GLOBAL',
				'rate' => '30',
				'threshold' => '50',
			),
		),
	),
	'Cartthrob_shipping_by_location_weight_threshold_settings' => array(
		'mode' => 'rate',
		'location_field' => 'country_code',
		'backup_location_field' => 'country_code',
		'thresholds' => array(
			 array(
				'location' => '',
				'rate' => '',
				'threshold' => '',
			),
		),
	),
	'Cartthrob_shipping_by_price_threshold_settings' => array(
		'mode' => 'price',
		'thresholds' => array(
			 array(
				'rate' => '',
				'threshold' => '',
			),
		),
	),
	'Cartthrob_shipping_by_quantity_threshold_settings' => array(
		'mode' => 'price',
		'thresholds' => array(
			 array(
				'rate' => '',
				'threshold' => '',
			),
		),
	),
	'Cartthrob_shipping_by_weight_global_rate_settings' => array(
		'rate' => '',
	),
	'Cartthrob_shipping_by_weight_threshold_settings' => array(
		'mode' => 'price',
		'thresholds' => array(
			 array(
				'rate' => '',
				'threshold' => '',
			),
		),
	),
	'Cartthrob_shipping_flat_rates_settings' => array(
		'rates' => array(
			 array(
				'short_name' => '',
				'title' => '',
				'rate' => '',
				'free_price' => '',
			),
		),
	),
	'Cartthrob_shipping_per_location_rates_settings' => array(
		'default_rate' => '',
		'default_type' => 'flat',
		'location_field' => 'billing',
		'rates' => array(
			 array(
				'rate' => '',
				'type' => 'flat',
				'zip' => '',
				'state' => '',
				'country' => '',
				'entry_ids' => '',
				'cat_ids' => '',
				'field_value' => '',
				'field_name' => '',
			),
		),
	),
	'Cartthrob_shipping_single_flat_rate_settings' => array(
		'rate' => '',
	),
	'Cartthrob_tax_default_plus_quebec_settings' => array(
		'tax_gst' => '5',
		'tax_qst' => '8.5',
		'tax_quebec_shipping' => 'no',
		'tax_quebec_name' => 'Consumption Tax (GST & QST)',
		'tax_quebec_effective_rate' => '13.925',
		'tax_settings' => array(
			 array(
				'name' => '',
				'rate' => '',
				'state' => '',
				'zip' => '',
			),
		),
	),
	'Cartthrob_tax_default_settings' => array(
		'use_tax_table' => 'no',
		'tax_settings' => array(
			 array(
				'name' => 'Missouri',
				'rate' => '7.7',
				'state' => 'MO',
				'zip' => '63303',
			),
			 array(
				'name' => 'GLOBAL',
				'rate' => '20',
				'state' => 'global',
				'zip' => '',
			),
		),
	),
	'Cartthrob_tax_standard_settings' => array(
		'default_tax' => '8',
	),
	'auth' => array(
		'processing' => FALSE,
		'authorized' => FALSE,
		'declined' => FALSE,
		'failed' => TRUE,
		'error_message' => '',
		'transaction_id' => '',
		'expired'	=> FALSE, 
		'canceled'	=> FALSE, 
		'voided'	=> FALSE,
		'pending'	=> FALSE,
		'refunded'	=> FALSE,
	),
	'use_profile_edit'	=> TRUE,
	'allow_fractional_quantities' => FALSE,
	'update_inventory_when_editing_order' => FALSE,
	'admin_checkout_groups' => array(1),
	'msm_show_all'	=> FALSE,
	'max_rebill_attempts'	=> 4,
	'subscription_rebill_tax' => TRUE,
	'subscription_rebill_shipping' => TRUE,
	'subscription_rebill_discount' => TRUE,
	'exempt_discount_from_tax'	=> FALSE, // if set to false, subtotal is taxed on subtotal - discount. If set to TRUE, then subtotal is taxed without the discount applied
	'round_tax_only_on_subtotal'	=> FALSE, // if set to TRUE, then tax is only rounded at the very end. This may cause line item total not to equal total of otehr totals, but tax calculation is more accurrate.. you won't lose tenths of cents due to early/often rounding
);
