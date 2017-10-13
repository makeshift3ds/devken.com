<?php
class Fedex {
	/**
	* Core object
	* @var object
	*/
	var $Core;    

	/**
	* request options required by FedEx API
	* @var array
	*/
	var $request;

	/**
	* Fedex WSDL File location
	* @var string
	*/
	var $wsdl;

	/**
	* Fedex Constructor : get shipping quotes from 
	*
	* @param array    $data  array of request options
	* @param string    $debug  debug flag
	* @access public
	* @return null
	**/
	public function Fedex($data=null,$debug=null) {
		if(isset($data)) $this->setRequest($data);
		$this->Core = Registry::getKey('Core');

		$this->wsdl = Registry::getParam('Config','file_dir').'libs/RateService_v8.wsdl';

		$this->request['Version'] = array('ServiceId' => 'crs', 'Major' => '8', 'Intermediate' => '0', 'Minor' => '0');
		$this->request['ReturnTransitAndCommit'] = true;
		$this->request['RequestedShipment']['DropoffType'] = 'REGULAR_PICKUP'; // valid values REGULAR_PICKUP, REQUEST_COURIER, ...
		$this->request['RequestedShipment']['ShipTimestamp'] = date('c');
		$this->request['ReturnTransitAndCommit'] = true;
		$this->request['RequestedShipment']['ShippingChargesPayment'] = array('PaymentType' => 'SENDER',
																													'Payor' => array('AccountNumber' => $data['AccountNumber'],
																													'CountryCode' => 'US'));
		$this->request['RequestedShipment']['RateRequestTypes'] = 'ACCOUNT'; 
		$this->request['RequestedShipment']['RateRequestTypes'] = 'LIST'; 
		$this->request['RequestedShipment']['PackageDetail'] = 'INDIVIDUAL_PACKAGES';
		if(!is_null($debug)) $this->Core->dump('WSDL',$this->wsdl);
	}

	/**
	* setRequest : set Fedex SOAP request options
	*
	* @param array    $data  array of request options
	* @param string    $debug  debug flag
	* @access public
	* @return null
	**/
	public function setRequest($data) {
		if(isset($data['DeveloperTestKey'])) $this->request['WebAuthenticationDetail']['UserCredential']['Key'] = $data['DeveloperTestKey'];
		if(isset($data['Password'])) $this->request['WebAuthenticationDetail']['UserCredential']['Password'] = $data['Password'];
		if(isset($data['AccountNumber'])) $this->request['ClientDetail']['AccountNumber'] = $data['AccountNumber'];
		if(isset($data['MeterNumber'])) $this->request['ClientDetail']['MeterNumber'] = $data['MeterNumber'];
		if(isset($data['CustomerTransactionId'])) $this->request['TransactionDetail']['CustomerTransactionId'] = $data['CustomerTransactionId'];
		if(isset($data['ShipperStreetLines'])) $this->request['RequestedShipment']['Shipper']['Address']['StreetLines'] = $data['ShipperStreetLines'];	// must be an array
		if(isset($data['ShipperCity'])) $this->request['RequestedShipment']['Shipper']['Address']['City'] = $data['ShipperCity'];
		if(isset($data['ShipperStateOrProvinceCode'])) $this->request['RequestedShipment']['Address']['Shipper']['StateOrProvinceCode'] = $data['ShipperStateOrProvinceCode'];
		if(isset($data['ShipperPostalCode'])) $this->request['RequestedShipment']['Shipper']['Address']['PostalCode'] = $data['ShipperPostalCode'];
		if(isset($data['ShipperCountryCode'])) $this->request['RequestedShipment']['Shipper']['Address']['CountryCode'] = $data['ShipperCountryCode'];
		if(isset($data['RecipientStreetLines'])) $this->request['RequestedShipment']['Recipient']['Address']['StreetLines'] = $data['RecipientStreetLines'];	// must be an array
    	if(isset($data['RecipientCity'])) $this->request['RequestedShipment']['Recipient']['Address']['City'] = $data['RecipientCity'];
    	if(isset($data['RecipientStateOrProvinceCode'])) $this->request['RequestedShipment']['Address']['Recipient']['StateOrProvinceCode'] = $data['RecipientStateOrProvinceCode'];
		if(isset($data['RecipientPostalCode'])) $this->request['RequestedShipment']['Recipient']['Address']['PostalCode'] = $data['RecipientPostalCode'];
		if(isset($data['RecipientCountryCode'])) $this->request['RequestedShipment']['Recipient']['Address']['CountryCode'] = $data['RecipientCountryCode'];
		if(isset($data['PackageWeight'])) $this->request['RequestedShipment']['RequestedPackageLineItems'] = array('0' => array('Weight' => array('Value' => $data['PackageWeight'],'Units' => 'LB')));

		// uncomment this line to test a 1lb shipment
		// $this->request['RequestedShipment']['RequestedPackageLineItems'] = array('0' => array('Weight' => array('Value' => 1.0,'Units' => 'LB')));
	}

	/**
	* getRates : set Fedex SOAP request options
	*
	* @param array    $data  array of request options
	* @param string    $debug  debug flag
	* @access public
	* @return null
	**/
	public function getRates($data=null,$debug=null) {
		if(isset($data)) $this->setRequest($data);	// add all the data to this->request so we can send it from any method

		$cache = 1;

		if($debug) $this->Core->dump('getRates request', $this->request);
		
		// cache
		if($cache) {
				$hash = md5($this->request['RequestedShipment']['RequestedPackageLineItems']['0']['Weight']['Value'].$this->request['RequestedShipment']['Recipient']['Address']['PostalCode'].$this->request['RequestedShipment']['Recipient']['Address']['CountryCode']);					
				if(isset($_SESSION['shipment_data']) && $hash == $_SESSION['shipment_data']['hash'] && count($_SESSION['shipment_data']['rates']) > 0) {
					if($debug) $this->Core->dump('Fedex Cache Returned',$_SESSION['shipment_data']['rates']);
					return $_SESSION['shipment_data']['rates'];
				}
				$_SESSION['shipment_data']['hash'] = $hash;
		}

		ini_set("soap.wsdl_cache_enabled", "0");
		$client = new SoapClient($this->wsdl, array('trace' => 1)); 
		try {
			$rates = array();
			if($debug) $this->Core->dump('Fedex Request',$this->request);
			$response = $client ->getRates($this->request);
			if($debug) print_r($response);
			if ($response->HighestSeverity != 'FAILURE' && $response->HighestSeverity != 'ERROR') {
				if(is_array($response->RateReplyDetails)) {
					foreach ($response->RateReplyDetails as $rateReply) {           
						$rates[$rateReply->ServiceType] = $rateReply->RatedShipmentDetails[1]->ShipmentRateDetail->TotalNetCharge->Amount;
					}
				}
				if(!is_null($debug)) $this->Core->dump('Rates',$rates);

				// cross reference with the database to restore order and remove unwanted rates
				$fedex_services = $this->Core->getTable('fedex_services',null,null,null,null,null,'weight asc');
				foreach($fedex_services as $service) {
					if(isset($rates[$service['id']])) $return_rates[$service['id']] = $rates[$service['id']];
				}
				$_SESSION['shipment_data']['rates'] = $return_rates;
				return $return_rates;
			} else {
				Error::websiteError('We were unable to calculate your shipping costs through Fedex. Please verify your postal code and be sure you selected the correct country.');
				Error::logError('Error Retrieving Fedex Quote','Code:'.$response->Notifications->Code.' String:'.$response->Notifications->Message.' TransID:'.$response->TransactionDetail->CustomerTransactionId);
				return;
			}
		} catch (SoapFault $exception) {
			Error::logError('Fedex SOAP Error','Code:'.$exception->faultcode.' String:'.$client->faultstring);
			return;
		}
	}
}
?>