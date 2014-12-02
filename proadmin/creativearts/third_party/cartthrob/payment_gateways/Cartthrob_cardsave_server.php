<?php 

class Cartthrob_cardsave_server extends Cartthrob_payment_gateway
{
	public $title = 'cardsave_server_title';
 	public $overview = "cardsave_server_overview"; 
	public $settings = array(
		array(
			'name' =>  'merchant_id',
			'short_name' => 'merchant_id', 
			'type' => 'text', 
			'default' => '', 
		),
		array(
			'name' =>  'password',
			'short_name' => 'password', 
			'type' => 'text', 
			'default' => '', 
		),
		array(
			'name' =>  'cardsave_server_pre_shared_key',
			'short_name' => 'pre_shared_key', 
			'type' => 'text', 
			'default' => '', 
		),
		array(
			'name' =>  'dev_merchant_id',
			'short_name' => 'dev_merchant_id', 
			'type' => 'text', 
			'default' => '', 
		),
		array(
			'name' =>  'dev_password',
			'short_name' => 'dev_password', 
			'type' => 'text', 
			'default' => '', 
		),
		array(
			'name' =>  'cardsave_server_dev_pre_shared_key',
			'short_name' => 'dev_pre_shared_key', 
			'type' => 'text', 
			'default' => '', 
		),

		array(
			'name' =>  'mode',
			'short_name' => 'mode', 
			'type' => 'radio',
			'options'	=> array(
				'test'=>'test',
				'live'=> 'live'
				),
			'default' => 'test', 
		),
	); 
	
	public $required_fields= array(
		'first_name'           ,
		'last_name'            ,
		'address'              ,
		'city'					,
		'country_code'			,
		'zip'					,
		); 
	
	public $fields = array(
		'first_name'           ,
		'last_name'            ,
		'address'              ,
		'address2'             ,
		'city'                 ,
 		'zip'                  ,
		'country_code'         ,
		'shipping_first_name'  ,
		'shipping_last_name'   ,
		'shipping_address'     ,
		'shipping_address2'    ,
		'shipping_city'        ,
 		'shipping_zip'         ,
		'shipping_country_code',
		'phone'                ,
		'email_address'        ,
		); 
	public $host = "https://mms.cardsaveonlinepayments.com/Pages/PublicPages/PaymentForm.aspx"; 	
 	
	public $merchant_id; 
	public $preshared_key; 
	public $password; 
	
	public function initialize()
	{
		if ( $this->plugin_settings('mode') == "live" )
		{
			$this->merchant_id=$this->plugin_settings('merchant_id'); 
			$this->password = $this->plugin_settings('password'); 
			$this->preshared_key = $this->plugin_settings('pre_shared_key');
		}
		else
		{
			$this->merchant_id=$this->plugin_settings('dev_merchant_id'); 
			$this->password = $this->plugin_settings('dev_password'); 
			$this->preshared_key = $this->plugin_settings('dev_pre_shared_key');
		}
	}
	
	public function process_payment($credit_card_number)
	{
		// $TransactionDateTime = date('Y-m-d H:i:s O') ; 
		$TransactionDateTime = date('Y-m-d H:i:s P') ; 
		$total = round($this->order('total')*100);
		$gateway = ucfirst(get_class($this)); 
		
		$post_array = array(
				'PreSharedKey'							=> $this->preshared_key,
				'MerchantID'							=> $this->merchant_id,
				'Password'								=> $this->password,
				'Amount'								=> $total,
				'CurrencyCode'							=> $this->iso_currency_convert($this->order('currency_code')),  
				'OrderID'								=> $this->order('entry_id'),
				'TransactionType'						=> "SALE",
				'TransactionDateTime'					=> $TransactionDateTime,
				'CallbackURL'							=> $this->response_script($gateway,array("callback")), 
				'OrderDescription'						=> "Cart Purchase", 
				'CustomerName'							=> $this->order('first_name')." ". $this->order('last_name'),
				'Address1'								=> $this->order('address'),
				'Address2'								=> $this->order('address2'),
				'Address3'								=> '',
				'Address4'								=> '',
				'City'									=> $this->order('city'),
				'State'									=> $this->order('state'),
				'PostCode'								=> $this->order('zip'),
				'CountryCode'							=> $this->iso_country_convert($this->order('country_code')),
				'CV2Mandatory'							=> "false",
				'Address1Mandatory'						=> 'true',
				'CityMandatory'							=> 'true',
				'PostCodeMandatory'						=> 'true',
				'StateMandatory'						=> 'false',
				'CountryMandatory'						=> 'true',
				'ResultDeliveryMethod'					=> 'SERVER',
				'ServerResultURL'						=> $this->response_script($gateway, array("result")),  
				'PaymentFormDisplaysResult'				=> 'false',
				'ServerResultURLCookieVariables'		=> '',
				'ServerResultURLFormVariables'			=> '',
				'ServerResultURLQueryStringVariables'	=> '',
			);
		
		$string_to_hash = "";
		$revised_post_array = array(); 
		$bad_keys = array("PreSharedKey", "Password"); 
		
		while (list($key, $val) = each($post_array)) 
		{
			//$val = urlencode(stripslashes(str_replace("\n", "\r\n", $val))); 

			if ($key != ('TransactionDateTime' || 'ServerResultURL')) 
			{
				$val = urlencode(stripslashes(str_replace("\n", "\r\n", $val)));
			} 
			else 
			{
				$val = stripslashes(str_replace("\n", "\r\n", $val)); 
			};
			$string_to_hash .= $key."=".$val.'&';

			// removing certain items from post data. 
			if (!in_array($key, $bad_keys))
			{
				$revised_post_array[$key] = $val; 
			}
		}
		// pop the last ampersand
		$string_to_hash = substr($string_to_hash, 0, -1);
		
		$revised_post_array['HashDigest'] = sha1($string_to_hash); 
		
 		$this->gateway_exit_offsite($revised_post_array, $this->host); 
		exit; 
	}
	public function extload($post)
	{
		$resp['authorized']	 	= FALSE; 
		$resp['declined'] 		= FALSE; 
		$resp['transaction_id']	= NULL;
		$resp['failed']			= TRUE; 
		$resp['error_message']	= "";

		if (!isset($post['ct_action']) || !isset($post['OrderID']))
		{
			echo "StatusCode=30&Message=". $this->lang('cardsave_server_action_not_specified');  
		}
		
		$this->relaunch_cart_snapshot($post['OrderID']);
		
		if ($post['ct_action'] == "result")
		{
			return $this->result($post); 
		}
		elseif($post['ct_action'] == "callback")
		{
			return $this->callback($post);
		}

	}
	public function result($post)
	{
		$resp['authorized']	 	= FALSE; 
		$resp['declined'] 		= FALSE; 
		$resp['transaction_id']	= NULL;
		$resp['failed']			= TRUE; 
		$resp['error_message']	= "";

		$transauthorised = FALSE; 
		switch (intval($post["StatusCode"]))
		{
			// transaction authorised
			case 0:
				$transauthorised = TRUE;
				break;
			// card referred (treat as decline)
			case 4:
				$transauthorised = FALSE;
				break;
			// transaction declined
			case 5:
				$transauthorised = FALSE;
				break;
			// duplicate transaction
			case 20:
				// need to look at the previous status code to see if the
				// transaction was successful
				if (intval($post["PreviousStatusCode"]) == 0)
				{
					// transaction authorised
					$transauthorised = TRUE;
				}
				else
				{
					// transaction not authorised
					$transauthorised = FALSE;
				}
				break;
			// error occurred
			case 30:
				$transauthorised = FALSE;
				break;
			default:
				$transauthorised = FALSE;
				break;
		}
	
		if ($transauthorised == TRUE) {
			
			$transaction_id = str_replace("AuthCode: ", "", $post['Message']);
			$resp['authorized']	 	= TRUE; 
			$resp['declined'] 		= FALSE; 
			$resp['transaction_id']	= $transaction_id;
			$resp['failed']			= TRUE; 
			$resp['error_message']	= "";
			
			$this->gateway_order_update($resp, $this->order('order_id'), NULL);
			echo "StatusCode=0&Message="; 
			exit;
		} 
		else 
		{
			$resp['authorized']	 	= FALSE; 
			$resp['declined'] 		= FALSE; 
			$resp['transaction_id']	= NULL;
			$resp['failed']			= TRUE; 
			$resp['error_message']	= $this->lang("cardsave_server_no_message"); 
		
			$this->gateway_order_update($resp, $this->order('order_id'), NULL);
			echo "StatusCode=30&Message=". $this->lang("cardsave_server_transaction_not_authorized"). " ". $post['Message']; 
			exit;
		}
 		exit;
		
	}
	
	public function callback($post)
	{
		$resp['authorized']	 	= FALSE; 
		$resp['declined'] 		= FALSE; 
		$resp['transaction_id']	= NULL;
		$resp['failed']			= TRUE; 
		$resp['error_message']	= "";
		
		$str="PreSharedKey=" . $this->preshared_key;
		$str=$str . '&MerchantID=' . $post["MerchantID"];
		$str=$str . '&Password=' . $this->password;
		$str=$str . '&CrossReference=' . $post["CrossReference"];
		$str=$str . '&OrderID=' . $post["OrderID"];
		$sha =  sha1($str);
	
		$hash = $post["HashDigest"];
		if ($sha == $hash) 
		{ 
			$this->final_redirect($this->order('return')); 
		} 
		else 
		{ 
			$resp['authorized']	 	= FALSE; 
			$resp['declined'] 		= FALSE; 
			$resp['transaction_id']	= NULL;
			$resp['failed']			= TRUE; 
			$resp['error_message']	= $this->lang('cardsave_server_hashes_did_not_match');
			
			$this->gateway_order_update($resp, $this->order('order_id'), $this->order('return'));
		}
		
 		exit; 
	}
	
	function createhash( $post) { 
		
		$PreSharedKey = $this->preshared_key; 
		$Password	= $this->password; 
		
		$str="PreSharedKey=" . $PreSharedKey;
		$str=$str . '&MerchantID=' . $post["MerchantID"];
		$str=$str . '&Password=' . $Password;
		$str=$str . '&StatusCode=' . $post["StatusCode"];
		$str=$str . '&Message=' . $post["Message"];
		$str=$str . '&PreviousStatusCode=' . $post["PreviousStatusCode"];
		$str=$str . '&PreviousMessage=' . $post["PreviousMessage"];
		$str=$str . '&CrossReference=' . $post["CrossReference"];
		$str=$str . '&Amount=' . $post["Amount"];
		$str=$str . '&CurrencyCode=' . $post["CurrencyCode"];
		$str=$str . '&OrderID=' . $post["OrderID"];
		$str=$str . '&TransactionType=' . $post["TransactionType"];
		$str=$str . '&TransactionDateTime=' . $post["TransactionDateTime"];
		$str=$str . '&OrderDescription=' . $post["OrderDescription"];
		$str=$str . '&CustomerName=' . $post["CustomerName"];
		$str=$str . '&Address1=' . $post["Address1"];
		$str=$str . '&Address2=' . $post["Address2"];
		$str=$str . '&Address3=' . $post["Address3"];
		$str=$str . '&Address4=' . $post["Address4"];
		$str=$str . '&City=' . $post["City"];
		$str=$str . '&State=' . $post["State"];
		$str=$str . '&PostCode=' . $post["PostCode"];
		$str=$str . '&CountryCode=' . $post["CountryCode"];
		return sha1($str);
	}
	
	function iso_country_convert($country_code)
	{
		$codes = array(
		'AFG' => '004',
		'ALB' => '008',
		'DZA' => '012',
		'ASM' => '016',
		'AND' => '020',
		'AGO' => '024',
		'AIA' => '660',
		'ATA' => '010',
		'ATG' => '028',
		'ARG' => '032',
		'ARM' => '051',
		'ABW' => '533',
		'AUS' => '036',
		'AUT' => '040',
		'AZE' => '031',
		'BHS' => '044',
		'BHR' => '048',
		'BGD' => '050',
		'BRB' => '052',
		'BLR' => '112',
		'BEL' => '056',
		'BLZ' => '084',
		'BEN' => '204',
		'BMU' => '060',
		'BTN' => '064',
		'BOL' => '068',
		'BIH' => '070',
		'BWA' => '072',
		'BVT' => '074',
		'BRA' => '076',
		'IOT' => '086',
		'VGB' => '092',
		'BRN' => '096',
		'BGR' => '100',
		'BFA' => '854',
		'BDI' => '108',
		'KHM' => '116',
		'CMR' => '120',
		'CAN' => '124',
		'CPV' => '132',
		'CYM' => '136',
		'CAF' => '140',
		'TCD' => '148',
		'CHL' => '152',
		'CHN' => '156',
		'CXR' => '162',
		'CCK' => '166',
		'COL' => '170',
		'COM' => '174',
		'COD' => '180',
		'COG' => '178',
		'COK' => '184',
		'CRI' => '188',
		'CIV' => '384',
		'CUB' => '192',
		'CYP' => '196',
		'CZE' => '203',
		'DNK' => '208',
		'DJI' => '262',
		'DMA' => '212',
		'DOM' => '214',
		'ECU' => '218',
		'EGY' => '818',
		'SLV' => '222',
		'GNQ' => '226',
		'ERI' => '232',
		'EST' => '233',
		'ETH' => '231',
		'FRO' => '234',
		'FLK' => '238',
		'FJI' => '242',
		'FIN' => '246',
		'FRA' => '250',
		'GUF' => '254',
		'PYF' => '258',
		'ATF' => '260',
		'GAB' => '266',
		'GMB' => '270',
		'GEO' => '268',
		'DEU' => '276',
		'GHA' => '288',
		'GIB' => '292',
		'GRC' => '300',
		'GRL' => '304',
		'GRD' => '308',
		'GLP' => '312',
		'GUM' => '316',
		'GTM' => '320',
		'GIN' => '324',
		'GNB' => '624',
		'GUY' => '328',
		'HTI' => '332',
		'HMD' => '334',
		'VAT' => '336',
		'HND' => '340',
		'HKG' => '344',
		'HRV' => '191',
		'HUN' => '348',
		'ISL' => '352',
		'IND' => '356',
		'IDN' => '360',
		'IRN' => '364',
		'IRQ' => '368',
		'IRL' => '372',
		'ISR' => '376',
		'ITA' => '380',
		'JAM' => '388',
		'JPN' => '392',
		'JOR' => '400',
		'KAZ' => '398',
		'KEN' => '404',
		'KIR' => '296',
		'PRK' => '408',
		'KOR' => '410',
		'KWT' => '414',
		'KGZ' => '417',
		'LAO' => '418',
		'LVA' => '428',
		'LBN' => '422',
		'LSO' => '426',
		'LBR' => '430',
		'LBY' => '434',
		'LIE' => '438',
		'LTU' => '440',
		'LUX' => '442',
		'MAC' => '446',
		'MKD' => '807',
		'MDG' => '450',
		'MWI' => '454',
		'MYS' => '458',
		'MDV' => '462',
		'MLI' => '466',
		'MLT' => '470',
		'MHL' => '584',
		'MTQ' => '474',
		'MRT' => '478',
		'MUS' => '480',
		'MYT' => '175',
		'MEX' => '484',
		'FSM' => '583',
		'MDA' => '498',
		'MCO' => '492',
		'MNG' => '496',
		'MSR' => '500',
		'MAR' => '504',
		'MOZ' => '508',
		'MMR' => '104',
		'NAM' => '516',
		'NRU' => '520',
		'NPL' => '524',
		'ANT' => '530',
		'NLD' => '528',
		'NCL' => '540',
		'NZL' => '554',
		'NIC' => '558',
		'NER' => '562',
		'NGA' => '566',
		'NIU' => '570',
		'NFK' => '574',
		'MNP' => '580',
		'NOR' => '578',
		'OMN' => '512',
		'PAK' => '586',
		'PLW' => '585',
		'PSE' => '275',
		'PAN' => '591',
		'PNG' => '598',
		'PRY' => '600',
		'PER' => '604',
		'PHL' => '608',
		'PCN' => '612',
		'POL' => '616',
		'PRT' => '620',
		'PRI' => '630',
		'QAT' => '634',
		'REU' => '638',
		'ROU' => '642',
		'RUS' => '643',
		'RWA' => '646',
		'SHN' => '654',
		'KNA' => '659',
		'LCA' => '662',
		'SPM' => '666',
		'VCT' => '670',
		'WSM' => '882',
		'SMR' => '674',
		'STP' => '678',
		'SAU' => '682',
		'SEN' => '686',
		'SCG' => '891',
		'SYC' => '690',
		'SLE' => '694',
		'SGP' => '702',
		'SVK' => '703',
		'SVN' => '705',
		'SLB' => '090',
		'SOM' => '706',
		'ZAF' => '710',
		'SGS' => '239',
		'ESP' => '724',
		'LKA' => '144',
		'SDN' => '736',
		'SUR' => '740',
		'SJM' => '744',
		'SWZ' => '748',
		'SWE' => '752',
		'CHE' => '756',
		'SYR' => '760',
		'TWN' => '158',
		'TJK' => '762',
		'TZA' => '834',
		'THA' => '764',
		'TLS' => '626',
		'TGO' => '768',
		'TKL' => '772',
		'TON' => '776',
		'TTO' => '780',
		'TUN' => '788',
		'TUR' => '792',
		'TKM' => '795',
		'TCA' => '796',
		'TUV' => '798',
		'VIR' => '850',
		'UGA' => '800',
		'UKR' => '804',
		'ARE' => '784',
		'GBR' => '826',
		'UMI' => '581',
		'USA' => '840',
		'URY' => '858',
		'UZB' => '860',
		'VUT' => '548',
		'VEN' => '862',
		'VNM' => '704',
		'WLF' => '876',
		'ESH' => '732',
		'YEM' => '887',
		'ZMB' => '894',
		'ZWE' => '716');
		
		if (array_key_exists($country_code, $codes))
		{
			return $codes[$country_code]; 
		}
		return $country_code; 
	}
	
	function iso_currency_convert($currency_code)
	{
 		$codes['AFA'] = array('Afghan Afghani', '971');
		$codes['AWG'] = array('Aruban Florin', '533');
		$codes['AUD'] = array('Australian Dollars', '036');
		$codes['ARS'] = array('Argentine Pes', '    03');
		$codes['AZN'] = array('Azerbaijanian Manat', '944');
		$codes['BSD'] = array('Bahamian Dollar', '044');
		$codes['BDT'] = array('Bangladeshi Taka', '050');
		$codes['BBD'] = array('Barbados Dollar', '052');
		$codes['BYR'] = array('Belarussian Rouble', '974');
		$codes['BOB'] = array('Bolivian Boliviano', '068');
		$codes['BRL'] = array('Brazilian Real', '986');
		$codes['GBP'] = array('British Pounds Sterling', '826');
		$codes['BGN'] = array('Bulgarian Lev', '975');
		$codes['KHR'] = array('Cambodia Riel', '116');
		$codes['CAD'] = array('Canadian Dollars', '124');
		$codes['KYD'] = array('Cayman Islands Dollar', '136');
		$codes['CLP'] = array('Chilean Peso', '152');
		$codes['CNY'] = array('Chinese Renminbi Yuan', '156');
		$codes['COP'] = array('Colombian Peso', '170');
		$codes['CRC'] = array('Costa Rican Colon', '188');
		$codes['HRK'] = array('Croatia Kuna', '191');
		$codes['CPY'] = array('Cypriot Pounds', '196');
		$codes['CZK'] = array('Czech Koruna', '203');
		$codes['DKK'] = array('Danish Krone', '208');
		$codes['DOP'] = array('Dominican Republic Peso', '214');
		$codes['XCD'] = array('East Caribbean Dollar', '951');
		$codes['EGP'] = array('Egyptian Pound', '818');
		$codes['ERN'] = array('Eritrean Nakfa', '232');
		$codes['EEK'] = array('Estonia Kroon', '233');
		$codes['EUR'] = array('Euro', '978');
		$codes['GEL'] = array('Georgian Lari', '981');
		$codes['GHC'] = array('Ghana Cedi', '288');
		$codes['GIP'] = array('Gibraltar Pound', '292');
		$codes['GTQ'] = array('Guatemala Quetzal', '320');
		$codes['HNL'] = array('Honduras Lempira', '340');
		$codes['HKD'] = array('Hong Kong Dollars', '344');
		$codes['HUF'] = array('Hungary Forint', '348');
		$codes['ISK'] = array('Icelandic Krona', '352');
		$codes['INR'] = array('Indian Rupee', '356');
		$codes['IDR'] = array('Indonesia Rupiah', '360');
		$codes['ILS'] = array('Israel Shekel', '376');
		$codes['JMD'] = array('Jamaican Dollar', '388');
		$codes['JPY'] = array('Japanese yen', '392');
		$codes['KZT'] = array('Kazakhstan Tenge', '368');
		$codes['KES'] = array('Kenyan Shilling', '404');
		$codes['KWD'] = array('Kuwaiti Dinar', '414');
		$codes['LVL'] = array('Latvia Lat', '428');
		$codes['LBP'] = array('Lebanese Pound', '422');
		$codes['LTL'] = array('Lithuania Litas', '440');
		$codes['MOP'] = array('Macau Pataca', '446');
		$codes['MKD'] = array('Macedonian Denar', '807');
		$codes['MGA'] = array('Malagascy Ariary', '969');
		$codes['MYR'] = array('Malaysian Ringgit', '458');
		$codes['MTL'] = array('Maltese Lira', '470');
		$codes['BAM'] = array('Marka', '977');
		$codes['MUR'] = array('Mauritius Rupee', '480');
		$codes['MXN'] = array('Mexican Pesos', '484');
		$codes['MZM'] = array('Mozambique Metical', '508');
		$codes['NPR'] = array('Nepalese Rupee', '524');
		$codes['ANG'] = array('Netherlands Antilles Guilder', '532');
		$codes['TWD'] = array('New Taiwanese Dollars', '901');
		$codes['NZD'] = array('New Zealand Dollars', '554');
		$codes['NIO'] = array('Nicaragua Cordoba', '558');
		$codes['NGN'] = array('Nigeria Naira', '566');
		$codes['KPW'] = array('North Korean Won', '408');
		$codes['NOK'] = array('Norwegian Krone', '578');
		$codes['OMR'] = array('Omani Riyal', '512');
		$codes['PKR'] = array('Pakistani Rupee', '586');
		$codes['PYG'] = array('Paraguay Guarani', '600');
		$codes['PEN'] = array('Peru New Sol', '604');
		$codes['PHP'] = array('Philippine Pesos', '608');
		$codes['QAR'] = array('Qatari Riyal', '634');
		$codes['RON'] = array('Romanian New Leu', '946');
		$codes['RUB'] = array('Russian Federation Ruble', '643');
		$codes['SAR'] = array('Saudi Riyal', '682');
		$codes['CSD'] = array('Serbian Dinar', '891');
		$codes['SCR'] = array('Seychelles Rupee', '690');
		$codes['SGD'] = array('Singapore Dollars', '702');
		$codes['SKK'] = array('Slovak Koruna', '703');
		$codes['SIT'] = array('Slovenia Tolar', '705');
		$codes['ZAR'] = array('South African Rand', '710');
		$codes['KRW'] = array('South Korean Won', '410');
		$codes['LKR'] = array('Sri Lankan Rupee', '144');
		$codes['SRD'] = array('Surinam Dollar', '968');
		$codes['SEK'] = array('Swedish Krona', '752');
		$codes['CHF'] = array('Swiss Francs', '756');
		$codes['TZS'] = array('Tanzanian Shilling', '834');
		$codes['THB'] = array('Thai Baht', '764');
		$codes['TTD'] = array('Trinidad and Tobago Dollar', '780');
		$codes['TRY'] = array('Turkish New Lira', '949');
		$codes['AED'] = array('UAE Dirham', '784');
		$codes['USD'] = array('US Dollars', '840');
		$codes['UGX'] = array('Ugandian Shilling', '800');
		$codes['UAH'] = array('Ukraine Hryvna', '980');
		$codes['UYU'] = array('Uruguayan Peso', '858');
		$codes['UZS'] = array('Uzbekistani Som', '860');
		$codes['VEB'] = array('Venezuela Bolivar', '862');
		$codes['VND'] = array('Vietnam Dong', '704');
		$codes['AMK'] = array('Zambian Kwacha', '894');
		$codes['ZWD'] = array('Zimbabwe Dollar', '716');

		
		if (array_key_exists($currency_code, $codes))
		{
			return $codes[$currency_code][1]; 
		}
		return $currency_code;
	}
}
// END CLASS