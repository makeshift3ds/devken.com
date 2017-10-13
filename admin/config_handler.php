<?php

include ('admin_header.php');

$Core = Registry::getKey('Core');
$Admin = Registry::getKey('Admin');
$Smarty = Registry::getKey('Smarty');
$Database = Registry::getKey('Database');

switch($_REQUEST['mode']) {
	case 'update_options':
		foreach($_REQUEST['data'] as $k=>$v) {
			$Admin->update('config',array('id'=>$k,'value'=>$v));
		}
		$_SESSION['success_msg'] = 'Configuration Options updated successfully';
		$Core->redirect('admin/config_list.php');
		break;
	default :
		Error::programError('Invalid Mode','This is a program error. Information about this error has been emailed to the administrator. Please contact them if the error persists.');
		exit;
		break;
}
?>