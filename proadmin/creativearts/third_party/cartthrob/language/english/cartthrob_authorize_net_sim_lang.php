<?php   
$lang = array(
	
	// AUTHORIZE NET ERRORS & CONTENT
	'authorize_net_sim_title'	=> 'Authorize.net SIM Offsite payment gateway',
	'authorize_net_sim_affiliate'	=> '<p><a href="http://reseller.authorize.net/application/?id=5553676"><img src="//www.authorize.net/images/authorizenet_logo.gif" border="0" alt="AUTHORIZE.NET and the Authorize.Net logo are trademarks of CyberSource Corporation." width="150" height="42"></a></p><p><a href="http://reseller.authorize.net/application/?id=5553676">Click here to sign up now!</a></p>',
	'authorize_net_sim_overview'	=> '<p><strong>EE 2.2.2 or greater is required to use this gateway.</strong></p>
	<p>This gateway makes use of a file called extload.php which is contained in the CartThrob themes/lib folder. If you have renamed your system folder, or you have moved it relative to your site\'s index.php page, you will need to update this location in the extload.php file. </p>
	
	<p>To use Authorize.net SIM, you must set the following settings at Authorize.net. You must add the following URL to authorize.net\'s Response/Receipt URLs. This setting will display ONE default URL, which you can replace with the URL noted below, or you can choose to add additional response URLs. If the URL below has not been added to Authorize.net\'s settings, your transactions WILL fail. </p>
	
	<p>The Authorize.net SIM method takes customer payments offsite on the Authorize.net site. When the transaction at Authorize.net is completed, the customer is not automatically redirected back to your website, but content from your website can be shown on the Authorize.net payment completion page. The page you specify as the return parameter (return="template_group/template") in your checkout form will be shown on Authorize.net\'s site when the customer\'s payment has been completed. You should specify a template that does not have any embedded images, css, javascript or other external content, due to limitations of what Authorize.net will display on its site. Also, please only specify the template group and template rather than a full URL (return="group/template") or your template will not be rendered correctly.</p>',
	'authorize_net_sim_api_login' => 'API Login',
	'authorize_net_sim_trans_key' => 'Transaction Key',
	'authorize_net_sim_email_customer' => 'Email Customer?',
	'authorize_net_sim_test_mode' => 'Test Mode?',
	'authorize_net_sim_dev_mode' => 'Developer Mode?',
	'authorize_net_sim_dev_api_login' => 'Developer API Login',
	'authorize_net_sim_dev_trans_key' => 'Developer Transaction Key',

	'authorize_net_sim_hash_value'	=> 'API Hash Value',
	'authorize_net_sim_authcapture'	=> 'Authorization Mode',
	'authorize_net_sim_auth_charge'	=> 'Authorize and charge',
	'authorize_net_sim_auth_only'	=> 'Authorize only',
	'authorize_net_sim_form_styles'	=> 'Payment form HTML/CSS Styles',
	'authorize_net_sim_form_footer'	=> 'Footer Template',
	'authorize_net_sim_footer_note'	=> 'Avoid using double quotes in template. All embeds (including JavaScript, Images and CSS will be ignored). All CSS must be hardcoded rather than embedded as linked stylesheets', 
	'authorize_net_sim_form_header'	=> 'Header Template', 
	'authorize_net_sim_header_note'	=> 'Avoid using double quotes in template. All embeds (including JavaScript, Images and CSS will be ignored). All CSS must be hardcoded rather than embedded as linked stylesheets',
	'authorize_net_sim_form_background'	=> 'Background color (Hex Value)', 
	'authorize_net_sim_link_color'	=> 'Text Link Color (Hex Value)',
	'authorize_net_sim_text_color'	=> 'Text Color (Hex Value)',
	'authorize_net_sim_logo_url'	=> 'Full logo URL  (ex. http://authorize.net/your_logo.jpg. Logo images must be uploaded to the payment gateway server. Contact authorize.net for more details. )', 
	'authorize_net_sim_background_url' => 'Full background image URL (ex. http://authorize.net/your_background_img.jpg Background images must be uploaded to the payment gateway server. Contact Authorize.net for more details)',
	
	
	'authorize_net_sim_cant_connect' => "Can not connect using using fsockopen or cURL",
	'authorize_net_sim_error_1' => "There Was A Problem Connecting To Authorize.net. Error ",
	'authorize_net_sim_error_2' => "There Was A Problem Connecting To Authorize.net. Error 2",
	'authorize_net_sim_no_response' => " no response",
	'authorize_net_sim_tax_inclusive' => 'Do items in the customer\'s cart contain tax?',
	'authorize_net_sim_non_matching_sha'	=> 'Non-matching SHA values suggest that URL was tampered with',
	'authorize_net_sim_submit'	=> 'Continue to payment page',
	
	'authorize_net_sim_notification_link'=> 'Provide this link to Authorize.net',
	'authorize_net_sim_no_post'	=> 'No post data was sent, and the transaction can not be processed', 
);