<?php

// This is an added class by leiavoia
// http://community.developer.authorize.net/t5/Integration-and-Testing/GetHostedProfilePage-API-call-How-To-Use-It/td-p/18022
class AuthorizeNetToken extends AuthorizeNetRequest {

	const LIVE_URL = "https://api.authorize.net/xml/v1/request.api";
	const SANDBOX_URL = "https://apitest.authorize.net/xml/v1/request.api";

	private $_xml;
	public function GetTokenResponse( $customer_profile_id, $comm_url = FALSE ) {
		$this->_constructXml("getHostedProfilePageRequest");

		$this->_xml->addChild("customerProfileId", $customer_profile_id);
		
		if ($comm_url)
		{
			$setting1 = $this->_xml->addChild("hostedProfileSettings");
	        $s1 = $setting1->addChild('setting');
	 		$s1->addChild('settingName','hostedProfileIFrameCommunicatorUrl'); 
			$s1->addChild('settingValue', $comm_url);

			$s2 = $setting1->addChild('setting');
	 		$s2->addChild('settingName','hostedProfilePageBorderVisible'); 
			$s2->addChild('settingValue', 'false');
		}

		return $this->_sendRequest();
		}

	protected function _setPostString() {
		$this->_post_string = $this->_xml->asXML();
		}

	protected function _getPostUrl() {
		return ($this->_sandbox ? self::SANDBOX_URL : self::LIVE_URL);
		}

	protected function _handleResponse($response) {
		return new AuthorizeNetToken_Response($response);
		}

	private function _constructXml($request_type) {
		$string = '<?xml version="1.0" encoding="utf-8"?><'.$request_type.' xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd"></'.$request_type.'>';
		$this->_xml = @new SimpleXMLElement($string);
		$merchant = $this->_xml->addChild('merchantAuthentication');
		$merchant->addChild('name',$this->_api_login);
		$merchant->addChild('transactionKey',$this->_transaction_key);
		}

	private function _addObject($destination, $object) {
		$array = (array)$object;
		foreach ($array as $key => $value) {
			if ($value && !is_object($value)) {
				if (is_array($value) && count($value)) {
					foreach ($value as $index => $item) {
						$items = $destination->addChild($key);
						$this->_addObject($items, $item);
						}
					} 
				else {
					$destination->addChild($key,$value);
					}
				} 
			elseif (is_object($value) && self::_notEmpty($value)) {
				$dest = $destination->addChild($key);
				$this->_addObject($dest, $value);
				}
			}
		}

	private static function _notEmpty($object) {
		$array = (array)$object;
		foreach ($array as $key => $value) {
			if ($value && !is_object($value)) {
				return true;
				} 
			elseif (is_object($value)) {
				if (self::_notEmpty($value)) {
					return true;
					}
				}
			}
		return false;
		}

	};

class AuthorizeNetToken_Response extends AuthorizeNetXMLResponse {
	public function GetToken() {
		return $this->_getElementContents("token");
		}
	};