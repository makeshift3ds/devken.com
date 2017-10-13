<?php

include ('admin_header.php');

$Core = Registry::getKey('Core');
$Admin = Registry::getKey('Admin');
$Smarty = Registry::getKey('Smarty');

switch($_REQUEST['mode']) {
	case 'insert_client':
		list($_REQUEST['data']['lat'],$_REQUEST['data']['lng']) = $Core->geocodeAddress($_REQUEST['data']['address']);
		$Admin->insert('clients',$_REQUEST['data']);
		$Core->redirect('admin/client_list.php');
		break;
	case 'remove_client':
		$Admin->deactivate('clients',$_REQUEST['id']);
		$Core->redirect('admin/client_list.php');
		break;
	case 'update_client':
		list($_REQUEST['data']['lat'],$_REQUEST['data']['lng']) = $Core->geocodeAddress($_REQUEST['data']['address']);
		$Admin->update('clients',$_REQUEST['data']);
		$Core->redirect('admin/client_list.php');
		break;
	case 'order_clients':
		$Admin->order('clients',$_REQUEST['ids'],$_REQUEST['weights']);
		$Core->redirect('admin/client_list.php');
		break;
	case 'export_clients':
		echo $Admin->export('clients');
		break;
	case 'insert_client_type':
		$_REQUEST['data']['id'] = $Core->slugCreate($_REQUEST['data']['title']); // create a unique id from the title
		$Admin->insert('client_types',$_REQUEST['data']);
		$Core->redirect('admin/client_type_list.php');
		break;
	case 'remove_client_type':
		$Admin->deactivate('client_types',$_REQUEST['id']);
		$Core->redirect('admin/client_type_list.php');
		break;
	case 'update_client_type':
		$Admin->update('client_types',$_REQUEST['data']);
		$Core->redirect('admin/client_type_list.php');
		break;
	case 'order_client_types':
		$Admin->order('client_types',$_REQUEST['ids'],$_REQUEST['weights']);
		$Core->redirect('admin/client_type_list.php');
		break;
	case 'export_client_types':
		echo $Admin->export('client_types');
		break;
	default :
		Error::programError('Invalid Mode','This is a program error. Information about this error has been emailed to the administrator. Please contact them if the error persists.');
		exit;
		break;
}
?>