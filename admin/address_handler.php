<?php

include ('admin_header.php');

$Core = Registry::getKey('Core');
$Admin = Registry::getKey('Admin');
$Smarty = Registry::getKey('Smarty');

switch($_REQUEST['mode']) {
	case 'insert_address':
		$Admin->insert('addresses',$_REQUEST['data']);
		$Core->redirect('admin/user_list.php');
		break;
	case 'remove_address':
		$Admin->deactivate('addresses',$_REQUEST['id']);
		$Core->redirect('admin/user_list.php');
		break;
	case 'update_address':
		$Admin->update('addresses',$_REQUEST['data']);
		$Core->redirect('admin/user_list.php');
		break;
	case 'order_address':
		$Admin->order('addresses',$_REQUEST['ids'],$_REQUEST['weights']);
		$Core->redirect('admin/user_list.php');
		break;
	case 'export_addresses':
		$Admin->export('address');
		break;
	default :
		Error::programError('Invalid Mode','This is a program error. Information about this error has been emailed to the administrator. Please contact them if the error persists.');
		exit;
		break;
}
?>