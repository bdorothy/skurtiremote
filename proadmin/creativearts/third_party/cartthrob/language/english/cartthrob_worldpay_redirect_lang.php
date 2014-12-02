<?php   
$lang = array(
	
	// RBS WORLDPAY ERRORS & CONTENT
	'worldpay_redirect_title'			=> 'RBS Worldpay Redirect Payment Gateway',
	'worldpay_redirect_overview'			=> "<p>This gateway makes use of a file called extload.php which is contained in the CartThrob themes/lib folder. If you have renamed your system folder, or you have moved it relative to your site's index.php page, you will need to update this location in the extload.php file. </p>
	
	<p>To set up, login to WorldPay, and go here: WorldPay admin : Installations : Installation Administration. You will need to set your Payment Response URL to the following url: <pre>&lt;WPDISPLAY ITEM=MC_callback&gt;</pre> You must also check 'Payment Response Enabled' and 'Enable Shopper Response'.</p>
	
	<p>The page you set in the 'return' parameter of the checkout_form will be used as the payment response page. </p>
	
	<p>Creating your Shopper Response Page: Please reference: <a href='http://www.worldpay.com/support/kb/bg/pdf/payment_response.pdf'>WorldPay's documentation</a>, specifically the 'Shopper Response' section on page 32.</p> 
	
	<p>Troubleshooting: If you see an error like this: <pre>CartThrob ecommerce could not find necessary files </pre> Try changing the Payment Response URL to either  <pre>http://&lt;WPDISPLAY ITEM=MC_callback&gt;</pre> or <pre>https://&lt;WPDISPLAY ITEM=MC_callback&gt;</pre></p>
	
	<p>Troubleshooting: If upgrading from WorldPay Select Junior, remove the MD5 Secret Key from your WorldPay settings at the following location: 
	WorldPay admin : Installations : Installation Administration : MD5 secret for transactions (also Confirm field) [REMOVE PASSWORD HERE]</p>
	
	<p>Troubleshooting: try starting with a very simple 'success' template with a few words of text.</p>
	
	<p>Troubleshooting: Don't use anything that could cause a redirect on your success page. WorldPay will not display any pages that use redirects.</p> 
	
	<p>Troubleshooting: To get full details about of any shopper response failure, in WorldPay's settings enable the failure email and select that you wish to attach the Payment Message and error logs to the email. The errors logs will indicate to you what they tried to retrieve from your server and therefore, what has caused the failure.
	</p>
	
		<p>Please note: WorldPay does not redirect customers back to your site when they're done processing the transaction. WorldPay pulls in content from your order complete template, so you'll have to use full URL paths to to include any images or navigation. You'll also need to make sure the image URLs use https:// links. Since Worldpay's page is secured, it will balk if your images are not also secured. Javascript and Flash or other web objects will not be shown by WorldPay on their success page. It's best to keep your template simple.</p>

		",
	'worldpay_redirect_installation_id'	=> 'Installation ID',
	'worldpay_transaction_failure'		=> 'Worldpay data was not successfully transmitted. Please contact site owner to make sure your transaction went through successfully.',
 
);