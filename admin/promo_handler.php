<?php

include ('admin_header.php');

$Core = Registry::getKey('Core');
$Admin = Registry::getKey('Admin');
$Smarty = Registry::getKey('Smarty');

switch($_REQUEST['mode']) {
	case 'insert_promo':
		$Admin->insert('promos',$_REQUEST['data']);
		$Core->redirect('admin/promo_list.php');
		break;
	case 'remove_promo':
		$Admin->deactivate('promos',$_REQUEST['id']);
		$Core->redirect('admin/promo_list.php');
		break;
	case 'update_promo':
		$Admin->update('promos',$_REQUEST['data']);
		$Core->redirect('admin/promo_list.php');
		break;
	case 'order_promos':
		$Admin->order('promos',$_REQUEST['ids'],$_REQUEST['weights']);
		$Core->redirect('admin/promo_list.php');
		break;
	case 'export_promos':
		$Admin->export('promos');
		break;
	default :
		Error::programError('Invalid Mode','This is a program error. Information about this error has been emailed to the administrator. Please contact them if the error persists.');
		exit;
		break;
}
?>