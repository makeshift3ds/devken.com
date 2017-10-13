<?php
/**
 * Paypal Wrapper
 *
 * Connect to paypal website payments pro
 *
 * @author      Kenneth Elliott <kenelliottmc@gmail.com>
 * @copyright   Copyright &copy; 2010 Kenneth Elliott <kenelliottmc@gmail.com>
 * @category    Manataria
 * @package     Paypal
 * @since       Manataria 1.1.2
 */
class Paypal {

	protected $payment_type = 'sale';
	protected $api_username = 'sdk-three_api1.sdk.com';
	protected $api_password = 'QFZCWN5HZM8VBG7Q';
	protected $api_signature = 'A.d9eRKfd1yVkRrtmMfCFLTqa6M9AyodL0SJkhYztxUi8W9pCXF6.4NI';
	protected $api_endpoint = 'https://api-3t.sandbox.paypal.com/nvp';
	protected $paypal_url = 'https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token=';
	protected $version = '54.0';

	protected $Core;

	// pass the core object
	public function __construct() {
		$this->Core = Registry::getKey('Core');
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

		if(curl_errno($ch)) echo 'paypal communication error : '.curl_error($ch);
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
				'&STREET='.urlencode($data['address1']).
				'&CITY='.urlencode($data['city']).
				'&STATE='.urlencode($data['state']).
				'&ZIP='.urlencode($data['zip']).
				'&COUNTRYCODE=US&CURRENCYCODE=USD';
		return $str;
	}


/**
 * Get required parameters from the web form for the request
$paymentType =urlencode( $_POST['paymentType']);
$firstName =urlencode( $_POST['firstName']);
$lastName =urlencode( $_POST['lastName']);
$creditCardType =urlencode( $_POST['creditCardType']);
$creditCardNumber = urlencode($_POST['creditCardNumber']);
$expDateMonth =urlencode( $_POST['expDateMonth']);

// Month must be padded with leading zero
$padDateMonth = str_pad($expDateMonth, 2, '0', STR_PAD_LEFT);

$expDateYear =urlencode( $_POST['expDateYear']);
$cvv2Number = urlencode($_POST['cvv2Number']);
$address1 = urlencode($_POST['address1']);
$address2 = urlencode($_POST['address2']);
$city = urlencode($_POST['city']);
$state =urlencode( $_POST['state']);
$zip = urlencode($_POST['zip']);
$amount = urlencode($_POST['amount']);
//$currencyCode=urlencode($_POST['currency']);
$currencyCode="USD";
$paymentType=urlencode($_POST['paymentType']);

/* Construct the request string that will be sent to PayPal.
   The variable $nvpstr contains all the variables and is a
   name value pair string with & as a delimiter
$nvpstr="&PAYMENTACTION=$paymentType&AMT=$amount&CREDITCARDTYPE=$creditCardType&ACCT=$creditCardNumber&EXPDATE=".         $padDateMonth.$expDateYear."&CVV2=$cvv2Number&FIRSTNAME=$firstName&LASTNAME=$lastName&STREET=$address1&CITY=$city&STATE=$state".
"&ZIP=$zip&COUNTRYCODE=US&CURRENCYCODE=$currencyCode";

/* Make the API call to PayPal, using API signature.
   The API response is stored in an associative array called $resArray
$resArray=hash_call("doDirectPayment",$nvpstr);

/* Display the API response back to the browser.
   If the response from PayPal was a success, display the response parameters'
   If the response was an error, display the errors received using APIError.php.

$ack = strtoupper($resArray["ACK"]);

if($ack!="SUCCESS")  {
    $_SESSION['reshash']=$resArray;
	$location = "APIError.php";
		 header("Location: $location");
   }

 */
}
?>
