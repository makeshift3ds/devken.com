<?php

include ('admin_header.php');

$Core = Registry::getKey('Core');
$Admin = Registry::getKey('Admin');
$Smarty = Registry::getKey('Smarty');

switch($_REQUEST['mode']) {
	case 'insert_navigation':
		if($_REQUEST['href'] != '' && $_REQUEST['data']['url'] == 'url') $_REQUEST['data']['url'] = $_REQUEST['href'];
		if($_REQUEST['data']['parent_id'] == '0') unset($_REQUEST['data']['parent_id']);
		$Admin->insert('navigation',$_REQUEST['data']);
		$Core->redirect('admin/navigation_list.php');
		break;
	case 'remove_navigation':
		$Admin->remove('navigation',$_REQUEST['id']);
		$Core->redirect('admin/navigation_list.php');
		break;
	case 'update_navigation':
		if($_REQUEST[href] != '' && $_REQUEST['data']['url'] == 'url') $_REQUEST['data']['url'] = $_REQUEST['href'];
		$Admin->update('navigation',$_REQUEST['data']);
		$Core->redirect('admin/navigation_list.php');
		break;
	case 'order_navigation':
		$Admin->order('navigation',$_REQUEST['ids'],$_REQUEST['weights']);
		$Core->redirect('admin/navigation_list.php');
		break;
	case 'activate_navigation':
		$Admin->activate('navigation',$_REQUEST['id']);
		$Core->redirect('admin/navigation_list.php');
		break;
	case 'deactivate_navigation':
		$Admin->deactivate('navigation',$_REQUEST['id']);
		$Core->redirect('admin/navigation_list.php');
		break;
	default :
		Error::programError('Invalid Mode','This is a program error. Information about this error has been emailed to the administrator. Please contact them if the error persists.');
		exit;
		break;
}
?>