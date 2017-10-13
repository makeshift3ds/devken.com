<?php

// page id
$controlid="checkout";

// get the classes from the registry
$Core = Registry::getKey('Core');
$Smarty = Registry::getKey('Smarty');
$Cart = Registry::getKey('Cart');
$Commerce = Registry::getKey('Commerce');
$Paypal = Registry::getKey('Paypal');

// switch that enables fake credit card numbers
$_REQUEST['debug'] = 'checkout';

if(isset($_SESSION['checkout_info'])) $Smarty->assign('checkout_info',$_SESSION['checkout_info']);

if(isset($_REQUEST['mode'])) {
	switch($_REQUEST['mode']) {
		case 'process_checkout' :
			$Core->processPaypalCheckout($_SESSION['checkout_info']);
			break;
		case 'preview_checkout' :
			// set cc exp so that it matches convention
			$_REQUEST['data']['expDateYear'] = $_REQUEST['expDateYear'];
			$_REQUEST['data']['expDateMonth'] = $_REQUEST['expDateMonth'];

			// verify the info - if there is an error it will forward back to the checkout page
			$Core->verifyCheckoutInfo($_REQUEST['data']);

			// set the postal code and shipping country so paypal understands it
			$_SESSION['order_data']['postal_code'] = $_REQUEST['data']['shipping_zip'];
			$_SESSION['order_data']['country'] = $_REQUEST['data']['shipping_country'];
			$Smarty->assign('totals',$Commerce->getTotals($_SESSION['order_data']));
			$Smarty->assign('checkout_preview',1);
			break;
		default :		
			break;
	}
}

// set a redirect point for when registration is complete
$Core->logUrl('checkout');
?>

