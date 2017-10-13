<?php
/**
 * Commerce API
 *
 * This class contains the methods required for commerce applications.
 *
 * @author      Kenneth Elliott <kenelliottmc@gmail.com>
 * @copyright   Copyright &copy; 2010 Kenneth Elliott <kenelliottmc@gmail.com>
 * @category    Manataria
 * @package     Commerce
 * @since       Manataria 1.1.2
 */
class Commerce {
	/**
	* Core
	* @var integer
	*/
	protected $Core;
	
	/**
	* Cart
	* @var integer
	*/
	protected $Cart;
		
	/**
	* Database Class Object
	* @var integer
	*/
	protected $dbh;

	/**
	* Constructor.
	*
	* @access public
	*/
	public function Commerce() {
		$this->dbh = Registry::getKey('Database');
		$this->Core = Registry::getKey('Core');
		$this->Cart = Registry::getKey('Cart');
	}

	/**
	* getSubtotal.
	*
	* @access public
	*/
	public function getTotals($shipping_data=null,$debug=null) {
		$d_flag = 0;
		$cart = $this->Cart->getCart();
		
		// admins and type 3 users get the secondary rate
		if(isset($_SESSION['user']) && ($_SESSION['user']['table_users']['type_id'] == '3' || $_SESSION['user']['table_users']['type_id'] == '1')) {
			$d_flag = 1;	// discount flag
		}
		
		$subtotal = 0;
		$discount = 0;
		$weight = 0;
		$handling=0;
		foreach($cart as $key=>$item) {
			if(!isset($item['id'])) continue;
			$product = $this->Core->getTable('products','id='.$item['id'],1,null,null,null,null,null,$debug);
			$subtotal += $product['price']*$item['qty'];
			$weight += $product['product_weight']*$item['qty'];
			$handling += $product['handling']*$item['qty'];
			if($d_flag) $discount += ($product['price'] - $product['price2']) * $item['qty'];
		}
		
		if(isset($discount)) {
			$data['discount'] = $discount;
			$data['total'] = $subtotal - $discount;
		} else {
			$data['total'] = $subtotal;
		}
		$data['tax'] = $this->getTax($data['total'],$shipping_data);
	
		if(isset($shipping_data['postal_code']) && $shipping_data['postal_code'] != '' && $weight > 0) {
			$rates = $this->getShipping($weight,$shipping_data);
			if(!$rates) {
				Error::websiteError('Shipping Information could not be calculated.');
			} else {
				$data['shipping'] = $rates;
			}
		}
		
		$data['subtotal'] = $subtotal;
		$data['weight'] = $weight;
		$data['handling'] = $handling;
		$data['total'] += $data['tax'] + $rates[$_SESSION['order_data']['service_type']] + $data['handling'];
		return $data;
	}

	/**
	* getTax.
	*
	* @access public
	*/
	public function getTax($subtotal,$shipping_data=null,$debug=null) {
		$tax_data = $this->Core->geocodeAddress($shipping_data['postal_code'].' '.$shipping_data['country'],'location',null);
		if($debug) $this->Core->dump('Tax Data',$tax_data);
		if($tax_data['country_code'] == 'CA') {
				switch($tax_data['state']) {
					case 'AB':
						$rate = .05;
						break;
					case 'BC' :
						$rate = .12;
						break;
					case 'MB' :
						$rate = .12;
						break;
					case 'NB':
						$rate = .13;
						break;
					case 'NL':
						$rate = .13;
						break;
					case 'NS':
						$rate = .15;
						break;
					case 'ON':
						$rate = .13;
						break;
					case 'PE':
						$rate = .155;
						break;
					case 'QC':
						$rate = .12875;
						break;
					case 'SK':
						$rate = .1;
						break;
				}
				$_SESSION['order_data']['tax_rate'] = $rate;
				return sprintf($subtotal * $rate,'%3f');
		}
		return;
	}

	/**
	* getTax.
	*
	* @access public
	*/
	public function getShipping($weight,$shipping_data=null,$debug=null) {
		if(!$weight || !isset($shipping_data['postal_code'])) return;
		$Fedex = Registry::getKey('Fedex');		
		$rates = $Fedex->getRates(array(
			'PackageWeight'=>$weight,
			'RecipientPostalCode'=>$shipping_data['postal_code'],
			'RecipientCountryCode'=>$shipping_data['country']),null);
		return $rates;
	}

	/**
	* generate Credit Card Number for testing.
	*
	* @access public
	*/
	public function createCC($type=null) {
		if(is_null($type)) $type = 'visa';
		$prefixList = array();	// prefixes
		
		switch($type) {
			case 'visa':
				$prefixList[] =  "4539";
				$prefixList[] =  "4556";
				$prefixList[] =  "4916";
				$prefixList[] =  "4532";
				$prefixList[] =  "4929";
				$prefixList[] =  "4485";
				$prefixList[] =  "4716";
				$length = 16;
				break;
			case 'mastercard':
				$prefixList[] =  "51";
				$prefixList[] =  "52";
				$prefixList[] =  "53";
				$prefixList[] =  "54";
				$prefixList[] =  "55";
				$length = 16;
				break;
			case 'amex':
				$prefixList[] =  "34";
				$prefixList[] =  "37";
				$length = 15;
				break;
			case 'discover':
				$prefixList[] = "6011";
				$length = 16;
				break;
			case 'diners':
				$prefixList[] =  "300";
				$prefixList[] =  "301";
				$prefixList[] =  "302";
				$prefixList[] =  "303";
				$prefixList[] =  "36";
				$prefixList[] =  "38";
				$length = 14;
				break;
			case 'enRoute':
				$prefixList[] =  "2014";
				$prefixList[] =  "2149";
				$length = 15;
				break;
			case 'jcb':
				$prefixList[] =  "3088";
				$prefixList[] =  "3096";
				$prefixList[] =  "3112";
				$prefixList[] =  "3158";
				$prefixList[] =  "3337";
				$prefixList[] =  "3528";
				$prefixList[] = "2100";
				$prefixList[] = "1800";
				$length = 16;
				break;
			case 'voyager':				
				$prefixList[] = "8699";
				$length = 15;
				break;
		}
		
		$ccnumber = $prefixList[array_rand($prefixList)];
				
		while (strlen($ccnumber) < ($length - 1)) {
			$ccnumber .= rand(0,9);
		}

		$sum = 0;
		$pos = 0;
		$reversedCCnumber = strrev($ccnumber);

		while ( $pos < $length - 1 ) {
			$odd = $reversedCCnumber[$pos] * 2;
			if ($odd > 9) $odd -= 9;

			$sum += $odd;
			if ($pos != ($length - 2)) $sum += $reversedCCnumber[$pos +1];
			$pos += 2;
		}
		$checkdigit = (( floor($sum/10) + 1) * 10 - $sum) % 10;
		$ccnumber .= $checkdigit;
		return $ccnumber;
	}
	
}
?>
