<?php

include ('admin_header.php');

$Core = Registry::getKey('Core');
$Admin = Registry::getKey('Admin');
$Smarty = Registry::getKey('Smarty');

switch($_REQUEST['mode']) {
	case 'insert_user':
		$_REQUEST['data']['id'] = $Core->slugCreate($_REQUEST['data']['title']); // create a unique id from the title
		$Admin->insert('users',$_REQUEST['data']);
		$Core->redirect('admin/user_list.php');
		break;
	case 'remove_user':
		$Admin->deactivate('users',$_REQUEST['id']);
		$Core->redirect('admin/user_list.php');
		break;
	case 'update_user':
		// password check
		if(strlen($_REQUEST['data']['password']) > 4) {
			$_REQUEST['data']['password'] = $Core->processPassword($_REQUEST['data']['password']);
		} else {
			unset($_REQUEST['data']['password']);
		}
		$Admin->update('users',$_REQUEST['data']);
		$Core->redirect('admin/user_list.php');
		break;
	case 'order_users':
		$Admin->order('users',$_REQUEST['ids'],$_REQUEST['weights']);
		$Core->redirect('admin/user_list.php');
		break;
	case 'export_users':
		echo $Admin->export('users');
		break;
	case 'insert_user_type':
		$_REQUEST['data']['id'] = $Core->slugCreate($_REQUEST['data']['title']); // create a unique id from the title
		$Admin->insert('user_types',$_REQUEST['data']);
		$Core->redirect('admin/user_type_list.php');
		break;
	case 'remove_user_type':
		$Admin->deactivate('user_types',$_REQUEST['id']);
		$Core->redirect('admin/user_type_list.php');
		break;
	case 'update_user_type':
		$Admin->update('user_types',$_REQUEST['data']);
		$Core->redirect('admin/user_type_list.php');
		break;
	case 'order_user_types':
		$Admin->order('user_types',$_REQUEST['ids'],$_REQUEST['weights']);
		$Core->redirect('admin/user_type_list.php');
		break;
	case 'export_user_types':
		echo $Admin->export('user_types');
		break;
	case 'update_address':
		$Admin->update('addresses',$_REQUEST['data']);
		$Core->redirect('admin/address_edit.php');
		break;
	default :
		Error::programError('Invalid Mode','This is a program error. Information about this error has been emailed to the administrator. Please contact them if the error persists.');
		exit;
		break;
}
?>