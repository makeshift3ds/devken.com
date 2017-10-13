<?php
/**
 * Paypal Interface Wrapper
 *
 * Connect to a paypal account
 *
 * @author      Kenneth Elliott <kenelliottmc@gmail.com>
 * @copyright   Copyright &copy; 2010 Kenneth Elliott <kenelliottmc@gmail.com>
 * @category    Manataria
 * @package     MySQL_Adapter
 * @since       Manataria 1.1.2
 */
class Paypal {
	protected $payment_type; // = 'sale';
	protected $api_username; // = 'kenell_1223344547_biz_api1.gmail.com';
	protected $api_password; // = '1223344553';
	protected $api_signature; // = 'AIicyEFMuc4fqFjZb2dwwFNvjkGKAFvxciqi35cFjiQtsdv.8-GoOtP9';
	protected $api_endpoint; // = 'https://api-3t.sandbox.paypal.com/nvp';
	protected $version; // = '54.0';

	public function __construct($params) {
		$this->Core = Registry::getKey('Core');
		if(isset($params['payment_type'])) $this->payment_type = $params['payment_type']; 
		if(isset($params['api_username'])) $this->api_username = $params['api_username']; 
		if(isset($params['api_password'])) $this->api_password = $params['api_password']; 
		if(isset($params['api_signature'])) $this->api_signature = $params['api_signature']; 
		if(isset($params['api_endpoint'])) $this->api_endpoint = $params['api_endpoint']; 
		if(isset($params['version'])) $this->version = $params['version'];
	}

	public function send($data)	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_endpoint);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POST, 1);

		//NVPRequest for submitting to server
		$nvpreq="METHOD=doDirectPayment".
				"&VERSION=".urlencode($this->version).
				"&PWD=".urlencode($this->api_password).
				"&USER=".urlencode($this->api_username).
				"&SIGNATURE=".urlencode($this->api_signature).
				$this->encodeNVPString($data);

		//setting the nvpreq as POST FIELD to curl
		curl_setopt($ch,CURLOPT_POSTFIELDS,$nvpreq);

		//getting response from server
		$response = curl_exec($ch);

		$nvpResArray=$this->nvp2array($response);
		$nvpReqArray=$this->nvp2array($nvpreq);
		// $_SESSION['nvpReqArray']=$nvpReqArray;

		if(curl_errno($ch)) Error::paypalError('Paypal Communication Error','There was an error sending the information to Paypal for processing.',$nvpreq,curl_error($ch)); 
		//echo 'paypal communication error : '.curl_error($ch);
		curl_close($ch);
		return $nvpResArray;
	}

	function nvp2array($nvpstr) {
		$intial=0;
	 	$nvpArray = array();
		while(strlen($nvpstr)){
			$keypos= strpos($nvpstr,'=');
			$valuepos = strpos($nvpstr,'&') ? strpos($nvpstr,'&'): strlen($nvpstr);
			$keyval=substr($nvpstr,$intial,$keypos);
			$valval=substr($nvpstr,$keypos+1,$valuepos-$keypos-1);
			$nvpArray[urldecode($keyval)] =urldecode( $valval);
			$nvpstr=substr($nvpstr,$valuepos+1,strlen($nvpstr));
	     }
		return $nvpArray;
	}

	public function encodeNVPString($data) {
		$padDateMonth = str_pad($data['expDateMonth'], 2, '0', STR_PAD_LEFT);
		$str = 	'&PAYMENTACTION='.$this->payment_type.
				'&AMT='.urlencode($data['amount']).
				'&CREDITCARDTYPE='.urlencode($data['creditCardType']).
				'&ACCT='.urlencode($data['creditCardNumber']).
				'&EXPDATE='.$padDateMonth.$data['expDateYear'].
				'&CVV2='.urlencode($data['cvv2Number']).
				'&FIRSTNAME='.urlencode($data['firstName']).
				'&LASTNAME='.urlencode($data['lastName']).
				'&STREET='.urlencode($data['address']).
				'&CITY='.urlencode($data['city']).
				'&STATE='.urlencode($data['state']).
				'&ZIP='.urlencode($data['zip']).
				'&COUNTRYCODE=US&CURRENCYCODE=USD';
		return $str;
	}
}
?>
