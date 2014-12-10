<?php

class Cartthrob_ccavenue extends Cartthrob_payment_gateway
{

	public function __construct(){
	$this->EE =& get_instance();
	}

	// SOME DEFINITIONS
	public $title = 'CCAvenue';
	public $affiliate = '';
	public $overview = 'CC Avenue Payment Gateway';
	
	
	
	// CONFIGURATIONS
	public $settings = array(
		array(
			'name' => 'merchant_id', 
			'short_name' => 'merchant_id', 
			'type'	=> 'text',
			'default'	=> ''
		),
		array(
			'name' => 'working_key', 
			'short_name' => 'working_key', 
			'type'	=> 'text',
			'default'	=> ''
		)
	);
	
	
	// FIELDS ON THE CHECKOUT PAGE
	public $fields = array(
		'first_name',
		'last_name',
		'address',
		'address2',
		'city',
		'state',
		'country_code',
		'zip',
		'phone',
		'email_address',
		'shipping_first_name',
		'shipping_last_name',
		'shipping_address',
		'shipping_address2',
		'shipping_city',
		'shipping_state',
		'shipping_zip',
		'shipping_country_code',

 	);
	
	
	// MANDATORY FIELDS OF CHECKOUT PAGE
	public $required_fields = array(
		'first_name',
		'last_name',
		'address',
		'city',
		'state',
		'country_code',
		'zip',
		'phone',
		'email_address',
		
 	);
	
	
	
	// THE PROECESSOR ON SUBMIT
	function charge($credit_card_number){ // $this->order('variable_name');
	
	// REQUIRED FIVE VARIABLES FOR AVENUES CHECKSUM
	
	// Loading the merchant id and working key from site configs
	//$this->EE->load->library('siteconfig');
	
	
	
	$data['Merchant_Id']  	 = 'LASEASDFASDFJKER898'; //$this->EE->siteconfig->item('avenues_mid');
	$data['Order_Id']  		 = $this->order('entry_id');
	$data['Redirect_Url'] 	 = $this->EE->config->item('site_url').'response';
	$data['Amount'] 		 = $this->order('total');
	
	
	
	$WorkingKey				 = 'skljsadfjksfksjdf';//$this->EE->siteconfig->item('avenues_wkey');
	
	// OTHER REQUIRED FIELDS
	$data['billing_cust_name'] = $this->order('first_name').' '.$this->order('last_name');
	$data['billing_cust_address'] = $this->order('address');
	$data['billing_cust_country'] = $this->order('country_code');
	$data['billing_cust_tel'] = $this->order('phone');
	$data['billing_cust_email'] = $this->order('email_address');
	$data['billing_cust_state'] = $this->order('state');
	$data['billing_cust_city'] = $this->order('city');
	$data['billing_zip_code'] = $this->order('zip');
	
	
	$Checksum = $this->getChecksum($data['Merchant_Id'], $data['Amount'], $data['Order_Id'],  $data['Redirect_Url'], $WorkingKey);
	$data['Checksum'] = $Checksum;
	

	$this->_host = "https://www.ccavenue.com/shopzone/cc_details.jsp";
	$this->gateway_exit_offsite($data, NULL, $this->_host); exit; 
	}
	
	
	// LIBRARY FUNCTIONS
	function getchecksum($MerchantId,$Amount,$OrderId ,$URL,$WorkingKey)
	{
		$str ="$MerchantId|$OrderId|$Amount|$URL|$WorkingKey";
		$adler = 1;
		$adler = $this->adler32($adler,$str);
		return $adler;
	}
	
	
	function verifyChecksum($MerchantId , $OrderId, $Amount, $AuthDesc, $WorkingKey,  $CheckSum)
	{	
		$str = "";
		$str = "$MerchantId|$OrderId|$Amount|$AuthDesc|$WorkingKey";
		$adler = 1;
		$adler = $this->adler32($adler,$str);
		if($adler==$CheckSum) return true;
		else return false;		
	}
	
	function adler32($adler , $str)	{
		$BASE =  65521 ;
		$s1 = $adler & 0xffff ;
		$s2 = ($adler >> 16) & 0xffff;
		for($i = 0 ; $i < strlen($str) ; $i++){
			$s1 = ($s1 + Ord($str[$i])) % $BASE ;
			$s2 = ($s2 + $s1) % $BASE ;
	}
		return $this->leftshift($s2 , 16) + $s1;
	}



	function leftshift($str , $num){
	$str = DecBin($str);
	for( $i = 0 ; $i < (64 - strlen($str)) ; $i++)
	$str = "0".$str ;
	for($i = 0 ; $i < $num ; $i++) 	{
    $str = $str."0";
	$str = substr($str , 1 ) ;
	//echo "str : $str <BR>";
	}
	return $this->cdec($str) ;
	}
	
	
	

	function cdec($num)	{
	$dec=0;
	for ($n = 0 ; $n < strlen($num) ; $n++)	{
	$temp = $num[$n] ;
	$dec =  $dec + $temp*pow(2 , strlen($num) - $n - 1);
	}
	return $dec;
	}
	
	
	
	

}// END CLASS

/* End of file cartthrob.dev_template.php */
/* Location: ./system/modules/payment_gateways/cartthrob.dev_template.php */