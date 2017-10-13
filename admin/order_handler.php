<?php

include ('admin_header.php');

$Core = Registry::getKey('Core');
$Admin = Registry::getKey('Admin');
$Smarty = Registry::getKey('Smarty');

switch($_REQUEST['mode']) {
	case 'insert_order':
		$Admin->insert('orders',$_REQUEST['data']);
		$Core->redirect('admin/order_list.php');
		break;
	case 'remove_order':
		$Admin->deactivate('orders',$_REQUEST['id']);
		$Core->redirect('admin/order_list.php');
		break;
	case 'update_order':
		$Admin->update('orders',$_REQUEST['data']);
		$Core->redirect('admin/order_list.php');
		break;
	case 'order_orders':
		$Admin->order('orders',$_REQUEST['ids'],$_REQUEST['weights']);
		$Core->redirect('admin/order_list.php');
		break;
	case 'update_order_status':
		$Admin->update('orders',array('id'=>Utilities::sanitize($_REQUEST['data']['id']),'status'=>Utilities::sanitize($_REQUEST['data']['status'])));
		$Core->redirect('admin/order_list.php');
		break;
	case 'export_orders':
		$Admin->export('orders');
		break;
	default :
		Error::programError('Invalid Mode','This is a program error. Information about this error has been emailed to the administrator. Please contact them if the error persists.');
		exit;
		break;
}
?>