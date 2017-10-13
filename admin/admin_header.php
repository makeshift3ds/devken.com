<?php
include ('../conf/initiate.php');

$Core = Registry::getKey('Core');
$Smarty = Registry::getKey('Smarty');
Registry::setKey('Profiler',$profiler);

if(!isset($_FILES['Filedata']))  $Core->adminRequired();

$Smarty->assign('user',$_SESSION['user']['table_users']);

// error and success messages
if(isset($_SESSION['success_msg'])) {
	$Smarty->assign('success_msg',$_SESSION['success_msg']);
	unset($_SESSION['success_msg']);
}


if(isset($_SESSION['error_msg'])) {
		$Smarty->assign('error_msg',$_SESSION['error_msg']);
		unset($_SESSION['error_msg']);
}

// include extra classes
include_once Registry::getParam('Config','file_dir')."/libs/Admin.class.php";

// create some objects
$Admin = new Admin();

// add classes to the registry
Registry::setKey('Admin',$Admin);
?>