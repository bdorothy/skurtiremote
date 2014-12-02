<?php

#################### mpgGlobals ###########################################


class mpgGlobals
{

	var $Globals=array(
        	        'MONERIS_PROTOCOL' => 'https',
        	        'MONERIS_HOST' => 'esplusqa.moneris.com',
        	        'MONERIS_PORT' =>'443',
               	  	'MONERIS_FILE' => '/gateway_us/servlet/MpgRequest',
                  	'API_VERSION'  =>'US PHP Api v.3.2.0',
                  	'CLIENT_TIMEOUT' => '60'
                 	);

 	function mpgGlobals()
 	{
 		// default
 	}


 	function getGlobals()
 	{
  		return($this->Globals);
 	}

}//end class mpgGlobals



###################### mpgHttpsPost #########################################

class mpgHttpsPost
{

 	var $api_token;
 	var $store_id;
 	var $mpgRequest;
 	var $mpgResponse;

 	function mpgHttpsPost($storeid,$apitoken,$mpgRequestOBJ, $path_to_crt = NULL)
 	{

  		$this->store_id=$storeid;
  		$this->api_token= $apitoken;
  		$this->mpgRequest=$mpgRequestOBJ;
  		$dataToSend=$this->toXML();
  		
  		//do post

  		$g=new mpgGlobals();
  		$gArray=$g->getGlobals();
  		$transactionType=$mpgRequestOBJ->getTransactionType();

  		$url =  $gArray['MONERIS_PROTOCOL']."://".
       			$gArray['MONERIS_HOST'].":".
      			$gArray['MONERIS_PORT'].
       			$gArray['MONERIS_FILE'];

  		$ch = curl_init();
 		curl_setopt($ch, CURLOPT_URL,$url);
  		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
  		curl_setopt ($ch, CURLOPT_HEADER, 0);
  		curl_setopt($ch, CURLOPT_POST, 1);
  		curl_setopt($ch, CURLOPT_POSTFIELDS,$dataToSend);
  		curl_setopt($ch,CURLOPT_TIMEOUT,$gArray['CLIENT_TIMEOUT']);
  		curl_setopt($ch,CURLOPT_USERAGENT,$gArray['API_VERSION']);
  		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);

		if ($path_to_crt)
		{
			curl_setopt($ch, CURLOPT_CAINFO, $path_to_crt);
		}
		
  		$response=curl_exec ($ch);

   		curl_close ($ch);

  		if(!$response)
  		{

     			$response="<?xml version=\"1.0\"?><response><receipt>".
          			"<ReceiptId>Global Error Receipt</ReceiptId>".
          			"<ReferenceNum>null</ReferenceNum><ResponseCode>null</ResponseCode>".
          			"<AuthCode>null</AuthCode><TransTime>null</TransTime>".
          			"<TransDate>null</TransDate><TransType>null</TransType><Complete>false</Complete>".
          			"<Message>Global Error Receipt</Message><TransAmount>null</TransAmount>".
          			"<CardType>null</CardType>".
          			"<TransID>null</TransID><TimedOut>null</TimedOut>".
          			"<CorporateCard>false</CorporateCard><MessageId>null</MessageId>".
          			"</receipt></response>";
   		}

  		$this->mpgResponse=new mpgResponse($response);

 	}



 	function getMpgResponse()
 	{
  		return $this->mpgResponse;

 	}

 	function toXML( )
 	{

  		$req=$this->mpgRequest ;
  		$reqXMLString=$req->toXML();

  		$xmlString = "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>".
               			"<request>".
               			"<store_id>$this->store_id</store_id>".
               			"<api_token>$this->api_token</api_token>".
                		$reqXMLString.
                		"</request>";

  		return $xmlString;

 	}

}//end class mpgHttpsPost

###################### mpgHttpsPostStatus #########################################

class mpgHttpsPostStatus{

 var $api_token;
 var $store_id;
 var $status;
 var $mpgRequest;
 var $mpgResponse;

 function mpgHttpsPostStatus($store_id,$api_token,$status, $mpgRequestOBJ)
 {

  $this->store_id=$store_id;
  $this->api_token= $api_token;
  $this->status=$status;
  $this->mpgRequest=$mpgRequestOBJ;

  $dataToSend=$this->toXML();
  //do post

  $g=new mpgGlobals();
  $gArray=$g->getGlobals();

  $url=$gArray['MONERIS_PROTOCOL']."://".
       $gArray['MONERIS_HOST'].":".
       $gArray['MONERIS_PORT'].
       $gArray['MONERIS_FILE'];

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL,$url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt ($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS,$dataToSend);
  curl_setopt($ch,CURLOPT_TIMEOUT,$gArray['CLIENT_TIMEOUT']);
  curl_setopt($ch,CURLOPT_USERAGENT,$gArray['API_VERSION']);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);


  $response=curl_exec ($ch);

  curl_close ($ch);

  //echo "RESPONSE = $response\n\n";

  if(!$response)
   {

     $response="<?xml version=\"1.0\"?><response><receipt>".
          "<ReceiptId>Global Error Receipt</ReceiptId>".
          "<ReferenceNum>null</ReferenceNum><ResponseCode>null</ResponseCode>".
          "<ISO>null</ISO> <AuthCode>null</AuthCode><TransTime>null</TransTime>".
          "<TransDate>null</TransDate><TransType>null</TransType><Complete>false</Complete>".
          "<Message>null</Message><TransAmount>null</TransAmount>".
          "<CardType>null</CardType>".
          "<TransID>null</TransID><TimedOut>null</TimedOut>".
          "</receipt></response>";
   }

  $this->mpgResponse=new mpgResponse($response);

 }



  function getMpgResponse()
 {
  return $this->mpgResponse;

 }

 function toXML( )
 {

  $req=$this->mpgRequest ;
  $reqXMLString=$req->toXML();

  $xmlString='';
  $xmlString .="<?xml version=\"1.0\"?>".
               "<request>".
               "<store_id>$this->store_id</store_id>".
               "<api_token>$this->api_token</api_token>".
               "<status_check>$this->status</status_check>".
                $reqXMLString.
                "</request>";

  return ($xmlString);

 }

}//end class mpgHttpsPostStatus


############# mpgResponse #####################################################


class mpgResponse
{

	var $responseData;

 	var $p; //parser

 	var $currentTag;
 	var $purchaseHash = array();
 	var $refundHash;
 	var $correctionHash = array();
 	var $isBatchTotals;
 	var $term_id;
 	var $receiptHash = array();
 	var $ecrHash = array();
 	var $CardType;
 	var $currentTxnType;
 	var $ecrs = array();
 	var $cards = array();
 	var $cardHash= array();

 	var $ACSUrl;

 	function mpgResponse($xmlString)
 	{

  		$this->p = xml_parser_create();
  		xml_parser_set_option($this->p,XML_OPTION_CASE_FOLDING,0);
  		xml_parser_set_option($this->p,XML_OPTION_TARGET_ENCODING,"UTF-8");
  		xml_set_object($this->p,$this);
  		xml_set_element_handler($this->p,"startHandler","endHandler");
  		xml_set_character_data_handler($this->p,"characterHandler");
  		xml_parse($this->p,$xmlString);
  		xml_parser_free($this->p);


 	}	//end of constructor


 	function getMpgResponseData()
	{
   		return($this->responseData);

 	}

	function getRecurSuccess()
	{
 		return ($this->responseData['RecurSuccess']);
	}

	function getCardLevelResult()
	{
		return ($this->responseData['CardLevelResult']);
	}

	function getAvsResultCode()
	{
		return ($this->responseData['AvsResultCode']);
	}

	function getCvdResultCode()
	{
		return ($this->responseData['CvdResultCode']);
	}

	function getCardType()
	{
 		return ($this->responseData['CardType']);
	}

	function getTransAmount()
	{
 		return ($this->responseData['TransAmount']);
	}

	function getTxnNumber()
	{
 		return ($this->responseData['TransID']);
	}

	function getReceiptId()
	{
 		return ($this->responseData['ReceiptId']);
	}

	function getTransType()
	{
 		return ($this->responseData['TransType']);
	}

	function getReferenceNum()
	{
 		return ($this->responseData['ReferenceNum']);
	}

	function getResponseCode()
	{
 		return ($this->responseData['ResponseCode']);
	}

	function getISO()
	{
 		return ($this->responseData['ISO']);
	}

	function getBankTotals()
	{
 		return ($this->responseData['BankTotals']);
	}

	function getMessage()
	{
 		return ($this->responseData['Message']);
	}

	function getAuthCode()
	{
 		return ($this->responseData['AuthCode']);
	}

	function getComplete()
	{
 		return ($this->responseData['Complete']);
	}

	function getTransDate()
	{
 		return ($this->responseData['TransDate']);
	}

	function getTransTime()
	{
 		return ($this->responseData['TransTime']);
	}

	function getTicket()
	{
 		return ($this->responseData['Ticket']);
	}

	function getTimedOut()
	{
 		return ($this->responseData['TimedOut']);
	}
	
	function getMaskedPan()
	{
		return ($this->responseData['MaskedPan']);
	}

	function getRecurUpdateSuccess(){
		return ($this->responseData['RecurUpdateSuccess']);
	}

	function getNextRecurDate(){
		return ($this->responseData['NextRecurDate']);
	}

	function getRecurEndDate(){
		return ($this->responseData['RecurEndDate']);
	}
	
	function getStatusCode()	
	{
		return ($this->responseData['status_code']);
	}
	
	function getStatusMessage()	
	{
		return ($this->responseData['status_message']);
	}
	
	function getCavvResultCode()	
	{
		return ($this->responseData['CavvResultCode']);
	}
//-------------------------- Resolver response fields --------------------------------//

	function getDataKey()
	{
		return ($this->responseData['DataKey']);
	}

	function getResSuccess()
	{
		return ($this->responseData['ResSuccess']);
	}

	function getPaymentType()
	{
		return ($this->responseData['PaymentType']);
	}

	//------------------------------------------------------------------------------------//

	function getResolveData()
	{
		if($this->responseData['ResolveData']!='null'){
			return ($this->resolveData);
		}

		return $this->responseData['ResolveData'];
	}

	function setResolveData($data_key)
	{
		$this->resolveData=$this->resolveDataHash[$data_key];
	}

	function getResolveDataHash()
	{
		return ($this->resolveDataHash);
	}

	function getDataKeys()
	{
	 	return ($this->DataKeys);
 	}

 	function getResDataDataKey()
	{
		return ($this->resolveData['data_key']);
	}

	function getResDataPaymentType()
	{
		return ($this->resolveData['payment_type']);
	}

	function getResDataCustId()
	{
		return ($this->resolveData['cust_id']);
	}

	function getResDataPhone()
	{
		return ($this->resolveData['phone']);
	}

	function getResDataEmail()
	{
		return ($this->resolveData['email']);
	}

	function getResDataNote()
	{
		return ($this->resolveData['note']);
	}

	function getResDataPan()
	{
		return ($this->resolveData['pan']);
	}

	function getResDataMaskedPan()
	{
		return ($this->resolveData['masked_pan']);
	}

	function getResDataExpDate()
	{
		return ($this->resolveData['expdate']);
	}

	function getResDataAvsStreetNumber()
	{
		return ($this->resolveData['avs_street_number']);
	}

	function getResDataAvsStreetName()
	{
		return ($this->resolveData['avs_street_name']);
	}

	function getResDataAvsZipcode()
	{
		return ($this->resolveData['avs_zipcode']);
	}

	function getResDataSec()
	{
		return ($this->resolveData['sec']);
	}

	function getResDataCustFirstName()
	{
		return ($this->resolveData['cust_first_name']);
	}

	function getResDataCustLastName()
	{
		return ($this->resolveData['cust_last_name']);
	}

	function getResDataCustAddress1()
	{
		return ($this->resolveData['cust_address1']);
	}

	function getResDataCustAddress2()
	{
		return ($this->resolveData['cust_address2']);
	}

	function getResDataCustCity()
	{
		return ($this->resolveData['cust_city']);
	}

	function getResDataCustState()
	{
		return ($this->resolveData['cust_state']);
	}

	function getResDataCustZip()
	{
		return ($this->resolveData['cust_zip']);
	}

	function getResDataRoutingNum()
	{
		return ($this->resolveData['routing_num']);
	}

	function getResDataAccountNum()
	{
		return ($this->resolveData['account_num']);
	}

	function getResDataMaskedAccountNum()
	{
		return ($this->resolveData['masked_account_num']);
	}

	function getResDataCheckNum()
	{
		return ($this->resolveData['check_num']);
	}

	function getResDataAccountType()
	{
		return ($this->resolveData['account_type']);
	}

	function getResDataPresentationType()
	{
		return ($this->resolveData['presentation_type']);
	}

	function getResDataPAccountNumber()
	{
		return ($this->resolveData['p_account_number']);
	}

	function getResDataCryptType()
	{
		return ($this->resolveData['crypt_type']);
	}

	//---------------------------BatchClose response fields --------------------------------//

	function getTerminalStatus($ecr_no)
	{
 		return ($this->ecrHash[$ecr_no]);
	}

	function getPurchaseAmount($ecr_no,$card_type)
	{
 		return ($this->purchaseHash[$ecr_no][$card_type]['Amount']=="" ? 0:$this->purchaseHash[$ecr_no][$card_type]['Amount']);
	}

	function getPurchaseCount($ecr_no,$card_type)
	{
 		return ($this->purchaseHash[$ecr_no][$card_type]['Count']=="" ? 0:$this->purchaseHash[$ecr_no][$card_type]['Count']);
	}

	function getRefundAmount($ecr_no,$card_type)
	{
 		return ($this->refundHash[$ecr_no][$card_type]['Amount']=="" ? 0:$this->refundHash[$ecr_no][$card_type]['Amount']);
	}

	function getRefundCount($ecr_no,$card_type)
	{
 		return ($this->refundHash[$ecr_no][$card_type]['Count']=="" ? 0:$this->refundHash[$ecr_no][$card_type]['Count']);
	}

	function getCorrectionAmount($ecr_no,$card_type)
	{
 		return ($this->correctionHash[$ecr_no][$card_type]['Amount']=="" ? 0:$this->correctionHash[$ecr_no][$card_type]['Amount']);
	}

	function getCorrectionCount($ecr_no,$card_type)
	{
 		return ($this->correctionHash[$ecr_no][$card_type]['Count']=="" ? 0:$this->correctionHash[$ecr_no][$card_type]['Count']);
	}

	function getTerminalIDs()
	{
 		return ($this->ecrs);
	}

	function getCreditCardsAll()
	{
 		return (array_keys($this->cards));
	}

	function getCreditCards($ecr)
	{
 		return ($this->cardHash[$ecr]);
	}



	function characterHandler($parser,$data)
	{

 		if($this->isBatchTotals)
 		{
   			switch($this->currentTag)
    			{
     			case "term_id"    :
			{
                       		$this->term_id=$data;
                          	array_push($this->ecrs,$this->term_id);
                          	$this->cardHash[$data]=array();
                          	break;
                        }

     			case "closed"     :
			{
                        	$ecrHash=$this->ecrHash;
                          	$ecrHash[$this->term_id]=$data;
                          	$this->ecrHash = $ecrHash;
                          	break;
                        }

     			case "CardType"   :
			{
                          	$this->CardType=$data;
                          	$this->cards[$data]=$data;
                          	array_push($this->cardHash[$this->term_id],$data) ;
                          	break;
                         }

     			case "Amount"     :
			{
                          	if($this->currentTxnType == "Purchase")
                           	{
                             		$this->purchaseHash[$this->term_id][$this->CardType]['Amount']=$data;
                            	}
                           	elseif( $this->currentTxnType == "Refund")
                            	{
                              		$this->refundHash[$this->term_id][$this->CardType]['Amount']=$data;
                            	}
                           	elseif( $this->currentTxnType == "Correction")
                            	{
                              		$this->correctionHash[$this->term_id][$this->CardType]['Amount']=$data;
                            	}
                           	break;
                         }

    			case "Count"     :
			{
                        	if($this->currentTxnType == "Purchase")
                            	{
                             		$this->purchaseHash[$this->term_id][$this->CardType]['Count']=$data;
                            	}
                           	elseif( $this->currentTxnType == "Refund")
                            	{
                              		$this->refundHash[$this->term_id][$this->CardType]['Count']=$data;

                            	}
                           	else if( $this->currentTxnType == "Correction")
                            	{
                              		$this->correctionHash[$this->term_id][$this->CardType]['Count']=$data;
                            	}
                          	break;
                        }
	    		}

 		}
		elseif($this->isResolveData && $this->currentTag != "ResolveData")
 		{
			if($this->currentTag == "data_key")
			{
				$this->data_key=$data;
				array_push($this->DataKeys,$this->data_key);
				$this->resolveData[$this->currentTag] .=$data;
			}
   			else
   			{
   				$this->resolveData[$this->currentTag] .=$data;
   			}
 		}
 		elseif($this->currentTag == 'DataKey')
 		{
			array_push($this->DataKeys,$data);
			$this->responseData[$this->currentTag] .=$data;
 		}
 		else
 		{
 			$this->responseData[$this->currentTag] .=$data;
 		}

	}//end characterHandler



	function startHandler($parser,$name,$attrs)
	{

		$this->currentTag=$name;

		if($this->currentTag == "ResolveData")
		{
			$this->isResolveData=1;
  	 	}
  	 	elseif($this->isResolveData)
  	 	{
  	 		$this->resolveData[$this->currentTag]="";
  	 	}

  		if($this->currentTag == "BankTotals")
  	 	{
  	  		$this->isBatchTotals=1;
  	 	}
  		elseif($this->currentTag == "Purchase")
   		{
   	 		$this->purchaseHash[$this->term_id][$this->CardType]=array();
   	 		$this->currentTxnType="Purchase";
   		}
  		elseif($this->currentTag == "Refund")
  	 	{
  	  		$this->refundHash[$this->term_id][$this->CardType]=array();
  	  		$this->currentTxnType="Refund";
  	 	}
  		elseif($this->currentTag == "Correction")
   		{
   	 		$this->correctionHash[$this->term_id][$this->CardType]=array();
   	 		$this->currentTxnType="Correction";
   		}
	}

	function endHandler($parser,$name)
	{
	 	$this->currentTag=$name;
		if($this->currentTag == "ResolveData")
		{
			$this->isResolveData=0;
			if($this->data_key!="")
			{
				$this->resolveDataHash[$this->data_key]=$this->resolveData;
				$this->resolveData=array();
			}
	 	}
	 	if($name == "BankTotals")
	  	{
	    		$this->isBatchTotals=0;
	   	}

 		$this->currentTag="/dev/null";
	}

}//end class mpgResponse


################## mpgRequest ###########################################################

class mpgRequest
{

 	var $txnTypes =array(
			'us_preauth' => array('order_id','cust_id', 'amount', 'pan', 'expdate', 'crypt_type', 'dynamic_descriptor'),
			'us_completion' => array('order_id', 'comp_amount','txn_number', 'crypt_type',
                                            'commcard_invoice','commcard_tax_amount'),
			'us_purchase'=> array('order_id','cust_id', 'amount', 'pan', 'expdate', 'crypt_type',
                                            'commcard_invoice','commcard_tax_amount','dynamic_descriptor'),
            		'us_forcepost'=> array('order_id','cust_id','amount','pan','expdate','auth_code','crypt_type','dynamic_descriptor'),
			'us_purchasecorrection' => array('order_id', 'txn_number', 'crypt_type'),
			'us_refund' => array('order_id', 'amount', 'txn_number', 'crypt_type'),
			'us_ind_refund' => array('order_id','cust_id', 'amount','pan','expdate', 'crypt_type','dynamic_descriptor'),
			'us_cavv_preauth' => array('order_id','cust_id', 'amount', 'pan','expdate', 'cavv','dynamic_descriptor'),
			'us_cavv_purchase'=> array('order_id','cust_id','amount','pan','expdate', 'cavv',
                                                 'commcard_invoice','commcard_tax_amount','dynamic_descriptor'),
			'us_track2_preauth' => array('order_id','cust_id','amount','track2','pan','expdate','pos_code','dynamic_descriptor'),
			'us_track2_completion' => array('order_id', 'comp_amount','txn_number','pos_code',
                                            'commcard_invoice','commcard_tax_amount'),
			'us_track2_forcepost'=>array('order_id','cust_id', 'amount', 'track2','pan','expdate','pos_code','auth_code','dynamic_descriptor'),
			'us_track2_purchase' =>array('order_id','cust_id','amount','track2','pan','expdate',
                                                 'commcard_invoice','commcard_tax_amount','pos_code','dynamic_descriptor'),
			'us_track2_purchasecorrection' => array('order_id', 'txn_number'),
			'us_track2_refund' => array('order_id', 'amount', 'txn_number'),
			'us_track2_ind_refund' => array('order_id','amount','track2','pan','expdate','cust_id','pos_code','dynamic_descriptor'),
			'us_ach_debit' => array('order_id','cust_id','amount'),
			'us_ach_credit' => array('order_id','cust_id','amount'),
			'us_ach_reversal' => array('order_id','txn_number'),
            		'us_ach_fi_enquiry' => array('routing_num'),
		        'us_pinless_debit_purchase' => array('order_id','amount','pan','expdate','cust_id','presentation_type','intended_use','p_account_number'),
			'us_pinless_debit_refund' => array('order_id', 'amount', 'txn_number'),
			'us_opentotals' => array('ecr_number'),
			'us_batchclose' => array('ecr_number'),
			'us_card_verification' => array('order_id','cust_id','pan','expdate'),
			'us_contactless_purchase' => array('order_id','cust_id','amount','track2','pan','expdate','commcard_invoice','commcard_tax_amount','pos_code','dynamic_descriptor'),
			'us_contactless_refund' => array('order_id','amount','track2','pos_code','txn_number'),
			'us_contactless_purchasecorrection' => array('order_id','txn_number'),
			'us_reauth' => array('order_id','cust_id','orig_order_id','txn_number','amount','crypt_type'),
			'us_recur_update' => array('order_id', 'cust_id', 'pan', 'expdate', 'recur_amount','add_num_recurs', 'total_num_recurs', 'hold', 'terminate',
                      					'avs_street_number', 'avs_street_name', 'avs_zipcode'),
			'us_res_add_cc' => array('cust_id','phone','email','note','pan','expdate','crypt_type'),
			'us_res_add_ach' => array('cust_id','phone','email','note'),
			'us_res_add_pinless' => array('cust_id','phone','email','note','pan','expdate','presentation_type','p_account_number'),
			'us_res_update_cc' => array('data_key','cust_id','phone','email','note','pan','expdate','crypt_type'),
			'us_res_update_ach' => array('data_key','cust_id','phone','email','note'),
			'us_res_update_pinless' => array('data_key','cust_id','phone','email','note','pan','expdate','presentation_type','p_account_number'),
			'us_res_delete' => array('data_key'),
			'us_res_lookup_full' => array('data_key'),
			'us_res_lookup_masked' => array('data_key'),
			'us_res_get_expiring' => array(),
			'us_res_purchase_cc' => array('data_key','order_id','cust_id','amount','crypt_type','commcard_invoice','commcard_tax_amount','dynamic_descriptor'),
			'us_res_purchase_ach' => array('data_key','order_id','cust_id','amount'),
			'us_res_purchase_pinless' => array('data_key','order_id','cust_id','amount','intended_use','p_account_number'),
			'us_res_preauth_cc' => array('data_key','order_id','cust_id','amount','crypt_type','dynamic_descriptor'),
			'us_res_ind_refund_cc' => array('data_key','order_id','cust_id','amount','crypt_type','dynamic_descriptor'),
			'us_res_ind_refund_ach' => array('data_key','order_id','cust_id','amount'),
			'us_res_tokenize_cc' => array('order_id','txn_number','cust_id','phone','email','note'),
			'us_enc_track2_preauth' => array('order_id','cust_id','amount','enc_track2','pos_code','device_type', 'dynamic_descriptor'),
			'us_enc_track2_purchase' => array('order_id','cust_id','amount','enc_track2','pos_code','device_type', 'dynamic_descriptor'),
			'us_enc_track2_ind_refund' => array('order_id','cust_id','amount','enc_track2','pos_code','device_type', 'dynamic_descriptor'),
			'us_enc_track2_forcepost' => array('order_id', 'cust_id', 'amount', 'enc_track2', 'device_type', 'pos_code', 'auth_code', 'dynamic_descriptor'),
			'us_enc_purchase' => array('order_id', 'cust_id', 'amount', 'enc_track2', 'device_type', 'crypt_type', 'dynamic_descriptor'),
			'us_enc_ind_refund' => array('order_id', 'cust_id', 'amount', 'enc_track2', 'device_type', 'crypt_type', 'dynamic_descriptor'),
			'us_enc_preauth' => array('order_id', 'cust_id', 'amount', 'enc_track2', 'device_type', 'crypt_type', 'dynamic_descriptor'),
			'us_enc_forcepost' => array('order_id', 'cust_id', 'amount', 'enc_track2', 'device_type', 'auth_code', 'crypt_type', 'dynamic_descriptor'),
			'us_enc_card_verification' => array('order_id', 'cust_id', 'enc_track2', 'device_type'),
			'us_enc_res_add_cc' => array('enc_track2', 'device_type', 'crypt_type'),
			'us_enc_res_update_cc' => array('enc_track2', 'device_type', 'crypt_type')

			          );

	var $txnArray;

	function mpgRequest($txn)
	{

 		if(is_array($txn))
   		{
    			$this->txnArray = $txn;
   		}
 		else
   		{
    			$temp[0]=$txn;
    			$this->txnArray=$temp;
   		}
	}

	function getTransactionType()
	{
  		$jtmp=$this->txnArray;
  		$jtmp1=$jtmp[0]->getTransaction();
  		$jtmp2=array_shift($jtmp1);
  		return $jtmp2;
	}

	function toXML()
	{

 		$tmpTxnArray=$this->txnArray;
 		$txnArrayLen=count($tmpTxnArray); //total number of transactions
		$xmlString = NULL; 
		
 		for($x=0;$x < $txnArrayLen;$x++)
 		{
    			$txnObj=$tmpTxnArray[$x];
    			$txn=$txnObj->getTransaction();

    			$txnType=array_shift($txn);
    			$tmpTxnTypes=$this->txnTypes;
    			$txnTypeArray=$tmpTxnTypes[$txnType];
    			$txnTypeArrayLen=count($txnTypeArray); //length of a specific txn type

    			$txnXMLString="";

			for($i=0;$i < $txnTypeArrayLen ;$i++)
    			{
					if (array_key_exists($i, $txnTypeArray) && array_key_exists($txnTypeArray[$i], $txn))
					{
     				 $txnXMLString  .="<$txnTypeArray[$i]>"   //begin tag
                       				.$txn[$txnTypeArray[$i]] // data
                       				. "</$txnTypeArray[$i]>"; //end tag
						
					}
    			}

   			$txnXMLString = "<$txnType>$txnXMLString";


   			$recur  = $txnObj->getRecur();
  			if($recur != null)
   			{
         			$txnXMLString .= $recur->toXML();
   			}

			$avs  = $txnObj->getAvsInfo();
			if($avs != null)
			{
				$txnXMLString .= $avs->toXML();
			}

			$cvd  = $txnObj->getCvdInfo();
			if($cvd != null)
			{
				$txnXMLString .= $cvd->toXML();
			}

   			$custInfo = $txnObj->getCustInfo();
   			if($custInfo != null)
   			{
        		$txnXMLString .= $custInfo->toXML();
   			}

   			$ach = $txnObj->getAchInfo();
			if($ach != null)
			{
				$txnXMLString .= $ach->toXML();
   			}

   			$txnXMLString .="</$txnType>";
   			$xmlString .=$txnXMLString;

 		}

 		return $xmlString;

	}//end toXML



}//end class


##################### mpgCustInfo #######################################################

class mpgCustInfo
{


 	var $level3template = array(	cust_info=>array('email','instructions',
                 			billing => array('first_name', 'last_name', 'company_name', 'address',
                                    			 'city', 'province', 'postal_code', 'country',
                                    			 'phone_number', 'fax','tax1', 'tax2','tax3',
                                    			 'shipping_cost'),
                 			shipping => array('first_name', 'last_name', 'company_name', 'address',
                                   			  'city', 'province', 'postal_code', 'country',
                                   			  'phone_number', 'fax','tax1', 'tax2', 'tax3',
                                   			  'shipping_cost'),
                 			item   => array ('name', 'quantity', 'product_code', 'extended_amount')
                		)
           		);

 	var $level3data;
 	var $email;
 	var $instructions;

 	function mpgCustInfo($custinfo=0,$billing=0,$shipping=0,$items=0)
 	{
 		if($custinfo)
   		{
    			$this->setCustInfo($custinfo);
   		}
 	}




 	function setCustInfo($custinfo)
 	{
 		$this->level3data['cust_info']=array($custinfo);
 	}


 	function setEmail($email)
	{
   		$this->email=$email;
   		$this->setCustInfo(array(email=>$email,instructions=>$this->instructions));
 	}

 	function setInstructions($instructions)
	{
 		$this->instructions=$instructions;
   		$this->setCustinfo(array(email=>$this->email,instructions=>$instructions));
 	}

 	function setShipping($shipping)
 	{
  		$this->level3data['shipping']=array($shipping);
 	}

 	function setBilling($billing)
 	{
  		$this->level3data['billing']=array($billing);
 	}

 	function setItems($items)
 	{
   		if(! $this->level3data['item'])
    		{
     			$this->level3data['item']=array($items);
   	 	}
   		else
    		{
     			$index=count($this->level3data['item']);
     			$this->level3data['item'][$index]=$items;
    		}
 	}

 	function toXML()
 	{
  		$xmlString=$this->toXML_low($this->level3template,"cust_info");
  		return $xmlString;
 	}

 	function toXML_low($template,$txnType)
 	{

  	for($x=0;$x<count($this->level3data[$txnType]);$x++)
   	{
     	if($x>0)
     	{
      		$xmlString .="</$txnType><$txnType>";
     	}
     	$keys=array_keys($template);
     	for($i=0; $i < count($keys);$i++)
     	{
        	$tag=$keys[$i];

        	if(is_array($template[$keys[$i]]))
        	{
          		$data=$template[$tag];

          		if(! count($this->level3data[$tag]))
           		{
            		continue;
           		}
          		$beginTag="<$tag>";
          		$endTag="</$tag>";

          		$xmlString .=$beginTag;

          		#if(is_array($data))
           		{
            		$returnString=$this->toXML_low($data,$tag);
            		$xmlString .= $returnString;
           		}
          		$xmlString .=$endTag;
        	}
        	else
        	{
         		$tag=$template[$keys[$i]];
         		$beginTag="<$tag>";
         		$endTag="</$tag>";
         		$data=$this->level3data[$txnType][$x][$tag];
         		$xmlString .=$beginTag.$data .$endTag;
        	}

     	}//end inner for

    }//end outer for

    return $xmlString;
	}//end toXML_low

}//end class

##################### mpgRecur #####################################################

class mpgRecur{

	var $params;
	var $recurTemplate = array('recur_unit','start_now','start_date','num_recurs','period','recur_amount');

	function mpgRecur($params)
	{
		$this->params = $params;
		if( (! $this->params['period']) )
		{
			$this->params['period'] = 1;
		}
	}

	function toXML()
	{
		$xmlString = "";

		foreach($this->recurTemplate as $tag)
		{
			$xmlString .= "<$tag>". $this->params[$tag] ."</$tag>";
		}

		return "<recur>$xmlString</recur>";
	}

}//end class

##################### mpgAvsInfo #######################################################

class mpgAvsInfo
{

    var $params;
    var $avsTemplate = array('avs_street_number','avs_street_name','avs_zipcode');

    function mpgAvsInfo($params)
    {
        $this->params = $params;
    }

    function toXML()
    {
        $xmlString = "";

        foreach($this->avsTemplate as $tag)
        {
            $xmlString .= "<$tag>". $this->params[$tag] ."</$tag>";
        }

        return "<avs_info>$xmlString</avs_info>";
    }

}//end class

##################### mpgCvdInfo #######################################################

class mpgCvdInfo
{

    var $params;
    var $cvdTemplate = array('cvd_indicator','cvd_value');

    function mpgCvdInfo($params)
    {
        $this->params = $params;
    }

    function toXML()
    {
        $xmlString = "";

        foreach($this->cvdTemplate as $tag)
        {
            $xmlString .= "<$tag>". $this->params[$tag] ."</$tag>";
        }

        return "<cvd_info>$xmlString</cvd_info>";
    }

}//end class


##################### mpgAchInfo #######################################################

class mpgAchInfo
{

    var $params;
    var $achTemplate = array('sec','cust_first_name','cust_last_name',
                             'cust_address1','cust_address2','cust_city',
                             'cust_state','cust_zip','routing_num','account_num',
                             'check_num','account_type','micr');

    function mpgAchInfo($params)
    {
        $this->params = $params;
    }

    function toXML()
    {
        $xmlString = "";

        foreach($this->achTemplate as $tag)
        {
            $xmlString .= "<$tag>". $this->params[$tag] ."</$tag>";
        }

        return "<ach_info>$xmlString</ach_info>";
    }

}//end class

##################### mpgTransaction ################################################

class mpgTransaction
{

	var $txn;
	var $custInfo = null;
	var $recur = null;
	var $avs; 
	var $cvd; 
	var $ach; 
	
	
	function mpgTransaction($txn)
	{
		$this->txn=$txn;
	}

	function getCustInfo()
	{
		return $this->custInfo;
	}

	function setCustInfo($custInfo)
	{
		$this->custInfo = $custInfo;
		array_push($this->txn,$custInfo);
	}

	function getRecur()
	{
		return $this->recur;
	}

	function setRecur($recur)
	{
		$this->recur = $recur;
	}

	function getTransaction()
	{
		return $this->txn;
	}

	function getCvdInfo()
	{
		return $this->cvd;
	}

	function setCvdInfo($cvd)
	{
		$this->cvd = $cvd;
	}

	function getAvsInfo()
	{
		return $this->avs;
	}

	function setAvsInfo($avs)
	{
		$this->avs = $avs;
	}

	function getAchInfo()
	{
		return $this->ach;
	}

	function setAchInfo($ach)
	{
		$this->ach = $ach;
	}


}//end class


?>
