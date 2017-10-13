<?php

include ('admin_header.php');

$Core = Registry::getKey('Core');
$Admin = Registry::getKey('Admin');
$Smarty = Registry::getKey('Smarty');

switch($_REQUEST['mode']) {
	case 'insert_cart':
		$Admin->insert('carts',$_REQUEST['data']);
		$Core->redirect('admin/cart_list.php');
		break;
	case 'remove_cart':
		$Admin->deactivate('carts',$_REQUEST['id']);
		$Core->redirect('admin/cart_list.php');
		break;
	case 'update_cart':
		$Admin->update('carts',$_REQUEST['data']);
		$Core->redirect('admin/cart_list.php');
		break;
	case 'order_carts':
		$Admin->order('carts',$_REQUEST['ids'],$_REQUEST['weights']);
		$Core->redirect('admin/cart_list.php');
		break;
	case 'export_carts':
		$Admin->export('carts');
		break;
	default :
		Error::programError('Invalid Mode','This is a program error. Information about this error has been emailed to the administrator. Please contact them if the error persists.');
		exit;
		break;
}
?>