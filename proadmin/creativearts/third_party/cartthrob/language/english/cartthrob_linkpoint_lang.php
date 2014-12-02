<?php   
$lang = array(
	
		// LINKPOINT 
		'linkpoint_title'	=> 'First Data Global Gateway (LinkPoint)',
		'linkpoint_execute_curl' => "<r_error>Could not execute curl.</r_error>",
		'linkpoint_connect_problem' => "There Was A Problem Connecting To Linkpoint Payment Gateway. Please contact site administrator and tell them about this problem",
		'linkpoint_no_response' => " no response",
		'linkpoint_store_number'	=> 'Store Number',
		'linkpoint_overview'	=> '<p>You must have port 1129 available for both sending and receiving data on your server to use First Data Global Gateway. You must also generate a keyfile using the First Data Virtual Terminal system and upload it to the root of your website. Once uploaded, add the name and relative url location of the keyfile to the settings. Any time you update your merchant data with First Data, you will need to upload a new keyfile, and add the name and location here. If your keyfile location is incorrect, you will most likely see the SGS-020006 error from FirstData.</p>',
		'linkpoint_keyfile'	=> 'Name/path to your certificate file. Should be stored at publicly accessible location (like web root).',
		'linkpoint_test_store_number' => 'Test Store Number',
		'linkpoint_test_keyfile'	=> 'Name/path to your test certificate file.',
		'linkpoint_test_good'	=> 'Test successful transaction',
		'linkpoint_test_decline'=> 'Test declined transaction',
		'linkpoint_test_duplicate'	=> 'Test duplicate transaction'
);