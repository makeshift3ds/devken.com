<?php

// page id
$controlid="lost-password";

// get the classes from the registry
$Core = Registry::getKey('Core');
$Smarty = Registry::getKey('Smarty');

if(isset($_REQUEST['data'])) {
	if (isset($_REQUEST['data']['email']) && $_REQUEST['data']['email'] != '') {
		$where = 'email = "'.Utilities::sanitize($_REQUEST['data']['email']).'"';
	} else {
		$where = 'username = "'.Utilities::sanitize($_REQUEST['data']['email']).'"';
	}
	$user = $Core->getTable('users',$where,1,null,null,1,null,null,null);
	if(isset($user['email'])) {
		$Core->sendMail(array(
								'to' => $user['email'],
								'subject' => 'Reset your email - Action Required',
								'content' => 'Please click on the link below to reset the password to your account.'
								));
	}
	$Core->redirect('lost-password-sent');
}

// get the content
$content = $Core->getContent($controlid);
$Smarty->assign('page',$content);
?>