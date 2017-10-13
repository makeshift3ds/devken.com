<?php

include ('admin_header.php');

$Core = Registry::getKey('Core');
$Admin = Registry::getKey('Admin');
$Smarty = Registry::getKey('Smarty');

switch($_REQUEST['mode']) {
	case 'insert_event':
		$_REQUEST['data']['start_date'] = $Core->formatSmartyDate('start');
		$_REQUEST['data']['end_date'] = $Core->formatSmartyDate('end');
		$_REQUEST['data']['removal_date'] = (!$_REQUEST['auto_remove']) ? null : $Core->formatSmartyDate('remove');
		$_REQUEST['data']['active'] = 1;
		$event_id = $Admin->insert('events',$_REQUEST['data']);
		// $Core->redirect('admin/event_list.php');
		$Core->redirect('admin/event_edit.php?id='.$event_id);
		break;
	case 'remove_event':
		$Admin->deactivate('events',$_REQUEST['id']);
		$Core->redirect('admin/event_list.php');
		break;
	case 'update_event':
		$_REQUEST['data']['start_date'] = $Core->formatSmartyDate('start');
		$_REQUEST['data']['end_date'] = $Core->formatSmartyDate('end');
		$_REQUEST['data']['removal_date'] = (!$_REQUEST['auto_remove']) ? null : $Core->formatSmartyDate('remove');
		$Admin->update('events',$_REQUEST['data']);
		$Core->redirect('admin/event_list.php');
		break;
	case 'order_events':
		$Admin->order('events',$_REQUEST['ids'],$_REQUEST['weights']);
		$Core->redirect('admin/event_list.php');
		break;
	case 'insert_event_category':
		$Admin->insert('event_categories',$_REQUEST['data']);
		$Core->redirect('admin/event_category_list.php');
		break;
	case 'remove_event_category':
		$Admin->deactivate('event_categories',$_REQUEST['id']);
		$Core->redirect('admin/event_category_list.php');
		break;
	case 'update_event_category':
		$Admin->update('event_categories',$_REQUEST['data']);
		$Core->redirect('admin/event_category_list.php');
		break;
	case 'order_event_categories':
		$Admin->order('event_categories',$_REQUEST['ids'],$_REQUEST['weights']);
		$Core->redirect('admin/event_category_list.php');
		break;
	case 'remove_event_to_product':
		$Admin->remove('events_to_products',null,'where event_id = '.Utilities::sanitize($_REQUEST['data']['event_id']).' and product_id = '.Utilities::sanitize($_REQUEST['data']['product_id']));
		$Core->redirect('admin/event_edit.php?id='.$_REQUEST['data']['event_id']);
		break;
	case 'insert_event_to_product':
		$Admin->insert('events_to_products',$_REQUEST['data']);
		$Core->redirect('admin/event_edit.php?id='.$_REQUEST['data']['event_id']);
		break;
	default :
		Error::programError('Invalid Mode','This is a program error. Information about this error has been emailed to the administrator. Please contact them if the error persists.');
		exit;
		break;
}
?>