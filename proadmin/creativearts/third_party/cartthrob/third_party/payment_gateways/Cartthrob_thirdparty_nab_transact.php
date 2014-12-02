<?php 
/**
 * Cartthrob Thirdparty NAB Transact
 * 
 * this software is distributed under GNU GPL.   
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * @package default
 * @author Andrew Gunstone <http://ThirstStudios.com>
 */
class Cartthrob_thirdparty_nab_transact extends Cartthrob_payment_gateway
{
	public $title = 'nab_transact_title';
 	public $overview = 'nab_transact_overview';
	public $language_file = TRUE;
 	public $settings = array(
		array(
			'name' =>  'nab_transact_merchant_id',
			'short_name' => 'merchant_id', 
			'type' => 'text', 
			'default' => 'XYZ0010', 
		),
		array(
			'name' =>  'nab_transact_password',
			'short_name' => 'password', 
			'type' => 'text', 
			'default' => 'abcd1234', 
		),
		array(
			'name' => 'nab_transact_test_mode',
			'short_name' => 'test_mode', 
			'type' => 'radio', 
			'default' => 'test',
			'options' => array(
				'test' => 'test',
				'live' => 'live'
			)
		),
		array(
			'name'	=> 'nab_transact_test_response',
			'short_name'	=> 'test_response',
			'type' => 'select', 
			'default' => '00',
			'options' => array(
				'200' => 'approved',
				'251' => 'declined',
				'use_total'=> 'use_total'
			)
		)
	);
	
	public $required_fields = array(
		'first_name',
		'last_name',
		'address',
		'city',
		'zip',
		'credit_card_number',
		'expiration_year',
		'expiration_month',
	);
 
	public $fields = array(
		'first_name',
		'last_name',
		'address',
		'address2',
		'city',
		'zip',
		'shipping_first_name',
		'shipping_last_name',
		'shipping_address',
		'shipping_address2',
		'shipping_city',
		'shipping_zip',
		'phone',
		'email_address',
		'card_type',
		'credit_card_number',
		'expiration_month',
		'expiration_year',
 	);
		
 	public $hidden = array('description');

	public $card_types = NULL;

	/**
	 * process_payment
	 *
 	 * @param string $credit_card_number 
 	 * @return mixed | array | bool An array of error / success messages  is returned, or FALSE if all fails.
	 * @author Chris Newton
	 * @access public
	 * @since 1.0.0
	 */
	public function charge($credit_card_number)
	{
		// NAB Transact processes with no decimal values. 
		$total = round($this->total()*100);
		
		if ($this->plugin_settings('test_mode')== "test")
		{
			if ($this->plugin_settings('test_response')!= "use_total")
			{
				$total = $this->plugin_settings('test_response');
			}
			$credit_card_number = "4444333322221111";
 			$this->_merchant_id = "XYZ0010";
 			$this->_password = "abcd1234";

			$this->_host='https://transact.nab.com.au/test/xmlapi/payment';

		}
 		else
		{
			$this->_merchant_id = $this->plugin_settings('merchant_id'); 
 			$this->_password = $this->plugin_settings('password');
			$this->_host='https://transact.nab.com.au/live/xmlapi/payment';
		}
		
		global $LANG;

		if (strlen($this->order('expiration_year')) == 4)
		{
			$expiration_year = substr($this->order('expiration_year'), -2);
		}
		else
		{
			$expiration_year = str_pad($this->order('expiration_year'), 2, '0', STR_PAD_LEFT); 
		}
		
		$data = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
		$data .= "<NABTransactMessage>";
		
		$data .= "<MessageInfo>";
		
			$data .= "<messageID>". md5(time().rand(1000,1000000).time()) ."</messageID>";
			$data .= "<messageTimestamp>". date("YdmGis000000O") ."</messageTimestamp>";
			$data .= "<timeoutValue>60</timeoutValue>";
			$data .= "<apiVersion>xml-4.2</apiVersion>";
		
		$data .= "</MessageInfo>";
		$data .= "<MerchantInfo>";
			
			$data .= "<merchantID>".$this->_merchant_id."</merchantID>";
			$data .= "<password>".$this->_password."</password>";
		
		$data .= "</MerchantInfo>";
		
		$data .= "<RequestType>Payment</RequestType>";
		
		$data .= "<Payment>";
			$data .= '<TxnList count="1">';
			$data .= '<Txn ID="1">';
		
				$data .= '<txnType>0</txnType>';
				$data .= '<txnSource>23</txnSource>';
				$data .= '<amount>'. $total .'</amount>';
				$data .= '<currency>AUD</currency>';
				$data .= '<purchaseOrderNo>test</purchaseOrderNo>';
		
				$data .= "<CreditCardInfo>";
					$data .= "<cardNumber>".$credit_card_number."</cardNumber>";
					$data .= "<expiryDate>". str_pad($this->order('expiration_month'), 2, '0', STR_PAD_LEFT) .'/'. $expiration_year ."</expiryDate>";
				$data .= "</CreditCardInfo>";

			$data .= "</Txn>";
			$data .= "</TxnList>";
		$data .= "</Payment>";
		$data .= "</NABTransactMessage>";
		
		$connect = 	$this->curl_transaction($this->_host,$data); 
		
		$resp['authorized']					=	FALSE;
		$resp['error_message']				=	NULL;
		$resp['failed']						=	TRUE;
		$resp['declined']					=	FALSE;
		$resp['transaction_id'] 			=	NULL;
		
		if (!$connect)
		{
			$resp['failed']	 				= 	TRUE; 
			$resp['authorized']				=	FALSE;
			$resp['declined']				=	FALSE;
			$resp['error_message']			=	$this->lang('curl_gateway_failure');
			return $resp; 
		}
		$transaction = $this->xml_to_array($connect,'basic'); 
		
		$error = NULL; 
		if (!empty($transaction['NABTransactMessage']['Payment'][0]['TxnList'][0]['Txn'][0]['responseCode']['data']))
		{
			if(strtolower($transaction['NABTransactMessage']['Payment'][0]['TxnList'][0]['Txn'][0]['responseCode']['data'])!="00")
		  	{
				if (!empty($transaction['NABTransactMessage']['Payment'][0]['TxnList'][0]['Txn'][0]['responseCode']['data']))
				{
					$error = $transaction['NABTransactMessage']['Payment'][0]['TxnList'][0]['Txn'][0]['responseText']['data'];
				}
				$resp['declined'] 				= TRUE;
				$resp['failed']					= FALSE;
				$resp['error_message'] 			= $this->lang('nab_transact_transaction_error'). " ". $error;

			}
			elseif(strtolower($transaction['NABTransactMessage']['Payment'][0]['TxnList'][0]['Txn'][0]['responseCode']['data'])=="00")
			{
				$resp['declined']		   		 = FALSE;
				$resp['failed']			   		 = FALSE; 
				$resp['authorized']		   		 = TRUE;
				$resp['error_message']	   		 = NULL;
				$resp['transaction_id']    		 = (!empty($transaction['NABTransactMessage']['Payment'][0]['TxnList'][0]['Txn'][0]['txnID']['data']) ? $transaction['NABTransactMessage']['Payment'][0]['TxnList'][0]['Txn'][0]['txnID']['data'] : NULL);
			}
			else
			{
				$resp['authorized']				= FALSE;
				$resp['declined']				= FALSE;
				$resp['failed']					= TRUE;
				$resp['error_message'] 			= $this->lang('nab_transact_invalid_response');
			}
		}			

		return $resp;
	}
	// END
}
// END Class