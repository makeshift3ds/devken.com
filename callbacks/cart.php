<?php
// page id
$controlid="cart";

// get the classes from the registry
$Core = Registry::getKey('Core');
$Smarty = Registry::getKey('Smarty');
$Cart = Registry::getKey('Cart');
$Database = Registry::getKey('Database');
$Fedex = Registry::getKey('Fedex');

if(!isset($_SESSION['order_data']['postal_code'])) $_REQUEST['mode'] = 'shipping';

if(isset($_REQUEST['mode'])) {
		switch($_REQUEST['mode']) {
			case 'shipping' :
				// checking for undefined variables causes some ruckus
				@$_SESSION['order_data']['service_type'] = Utilities::idReturner(array($_REQUEST['data']['service_type'],$_SESSION['order_data']['service_type']),'FEDEX_GROUND');
				@$_SESSION['order_data']['postal_code'] = Utilities::idReturner(array($_REQUEST['data']['postal_code'],$_SESSION['order_data']['postal_code'],$_SESSION['user']['table_addresses']['zip']),'');
				@$_SESSION['order_data']['country'] = Utilities::idReturner(array($_REQUEST['data']['country'],$_SESSION['order_data']['country'],$_SESSION['user']['table_addresses']['country']),'US');
				// $_SESSION['success_msg'] = 'Your shipping has been calculated successfully';
				//$Core->redirect('cart');
				//break;
			case 'add' :
				$product = mysql_fetch_assoc($Database->query('select id,price,price2,shipping,handling,stock,product_weight,sku,title from products where id = "'.Utilities::sanitize($_REQUEST['id']).'" limit 1'));
				if(!isset($product['id'])) {
					// Error::websiteError('Product does not exist');
					$Core->redirect('cart');
				}
				if(isset($_REQUEST['event_id'])) $product['event_id'] = $_REQUEST['event_id'];
				$product['qty'] = $_REQUEST['qty'];
				$Cart->addItem($product);
				$_SESSION['success_msg'] = 'A new item has been added to your cart';
				$Core->redirect('cart');
				break;
			case 'remove' :
				$Cart->removeItem(is_null($_REQUEST['index']) ? 0 : $_REQUEST['index']);
				$_SESSION['success_msg'] = 'An item has been removed from your cart';
				$Core->redirect('cart');
				break;
			case 'clear' :
				$Cart->clearCart();
				$_SESSION['success_msg'] = 'Your shopping cart has been emptied';
				$Core->redirect('cart');
				break;
			default:
				break;
		}
}

$content = $Core->getContent($controlid);
if($content) {
		$Smarty->assign('page',$content);
		$Smarty->assign('content',$content['content']);	// text content
		$Smarty->assign('title',$content['title']);	// h2 title
}
?>

