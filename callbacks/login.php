<?php

// page id
$controlid="login";

// get the classes from the registry
$Core = Registry::getKey('Core');
$Smarty = Registry::getKey('Smarty');

if(isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'logout') {
	$Core->logout();
}

if(isset($_SESSION['user']['table_users']['id'])) {
	$Core->redirect('account');
}

if(isset($_REQUEST['data']['username']) && isset($_REQUEST['password'])) {
	$r = isset($_REQUEST['return']) ? $_REQUEST['return'] : 'home';
	$Core->verifyLogin(Utilities::sanitize($_REQUEST['data']['username']),md5($_REQUEST['password']),$r);
}

// get the content
$content = $Core->getContent($controlid);
$Smarty->assign('page',$content);
?>