<?php

include ('admin_header.php');

$Core = Registry::getKey('Core');
$Admin = Registry::getKey('Admin');
$Smarty = Registry::getKey('Smarty');

switch($_REQUEST['mode']) {
	case 'insert_page':
		$_REQUEST['data']['id'] = $Core->slugCreate($_REQUEST['data']['title']); // create a unique id from the title
		if($Core->checkPage($_REQUEST['data']['id'])) {
				$Admin->update('pages',$_REQUEST['data']);
				$Core->redirect('admin/page_list.php');
		} else {
				$Admin->insert('pages',$_REQUEST['data']);
				$Core->redirect('admin/page_list.php');
		}
		break;
	case 'remove_page':
		$Admin->deactivate('pages',$_REQUEST['id']);
		$Core->redirect('admin/page_list.php');
		break;
	case 'update_page':
		$Admin->update('pages',$_REQUEST['data']);
		$Core->redirect('admin/page_list.php');
		break;
	case 'order_pages':
		$Admin->order('pages',$_REQUEST['ids'],$_REQUEST['weights']);
		$Core->redirect('admin/page_list.php');
		break;
	case 'insert_page_type':
		$Admin->insert('page_types',$_REQUEST['data']);
		$Core->redirect('admin/page_type_list.php');
		break;
	case 'remove_page_type':
		$Admin->remove('page_types',$_REQUEST['id']);
		$Core->redirect('admin/page_type_list.php');
		break;
	case 'update_page_type':
		$Admin->update('page_types',$_REQUEST['data']);
		$Core->redirect('admin/page_type_list.php');
		break;
	case 'order_page_types':
		$Admin->order('page_types',$_REQUEST['data']);
		$Core->redirect('admin/page_type_list.php');
		break;
	default :
		Error::programError('Invalid Mode','This is a program error. Information about this error has been emailed to the administrator. Please contact them if the error persists.');
		exit;
		break;
}
?>