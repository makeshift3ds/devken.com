<?php

include ('admin_header.php');

$Core = Registry::getKey('Core');
$Admin = Registry::getKey('Admin');
$Smarty = Registry::getKey('Smarty');

switch($_REQUEST['mode']) {
	case 'insert_gift_certificate':
		$Admin->insert('gift_certificates',$_REQUEST['data']);
		$Core->redirect('admin/gift_certificate_list.php');
		break;
	case 'remove_gift_certificate':
		$Admin->deactivate('gift_certificates',$_REQUEST['id']);
		$Core->redirect('admin/gift_certificate_list.php');
		break;
	case 'update_gift_certificate':
		$Admin->update('gift_certificates',$_REQUEST['data']);
		$Core->redirect('admin/gift_certificate_list.php');
		break;
	case 'order_gift_certificates':
		$Admin->order('gift_certificates',$_REQUEST['ids'],$_REQUEST['weights']);
		$Core->redirect('admin/gift_certificate_list.php');
		break;
	case 'export_gift_certificates':
		$Admin->export('gift_certificates');
		break;
	default :
		Error::programError('Invalid Mode','This is a program error. Information about this error has been emailed to the administrator. Please contact them if the error persists.');
		exit;
		break;
}
?>