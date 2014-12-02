<?php   


$lang = array(
	'cardsave_server_title'		=> "CardSave.net Server",
	'cardsave_server_overview'		=> '
		<p>This gateway makes use of a file called extload.php which is contained in the CartThrob themes/lib folder. If you have renamed your system folder, or you have moved it relative to your site\'s index.php page, you will need to update this location in the extload.php file. </p>
	<p>You will need a pre-shared key from Cardsave.net. Login to the Merchant Management System and select merchant information from the left menu. At the bottom of that page you will see the security information and pre-shared key.</p>
	<h3>Testing</h3>
	<p><a href="https://mms.cardsaveonlinepayments.com/SiteFiles/VirtualFiles/TEST_CARD_DETAILS/TestCardDetails.zip">You can download some test cards to try here, they have varying responses as noted in the accompanying PDF.</a> You need to use the card billing address as specified in the PDF file alongside each card - If you use the incorrect billing address, as default, the payment will fail (AVS/Postcode Check will fail).</p>
	
	',
 	'cardsave_server_pre_shared_key'	=> 'Pre-shared key', 
	'cardsave_server_dev_pre_shared_key'	=> 'Dev Pre-shared key',
	'cardave_server_data_not_specified'	=> 'This transaction can not be processed',
	'cardsave_server_action_not_specified' => 'This transaction can not br processed, due to a communication failure with the gateway. Action or order id is missing',
	'cardsave_server_no_message'			=> 'No message was specified by the server',
	'cardsave_server_hashes_did_not_match'	=> 'Hashes did not match',
	'cardsave_server_transaction_not_authorized'	=> 'The transaction was not authorized',
	);