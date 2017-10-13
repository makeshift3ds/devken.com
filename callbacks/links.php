<?php

// get the classes from the registry
$Core = Registry::getKey('Core');
$Smarty = Registry::getKey('Smarty');

// get the path
$path =  Registry::getParam('Config','path');

if(isset($_REQUEST['mode'])) {
	switch($_REQUEST['mode'])	{
		case 'submit_link' :
			$Core->sendMail(array(
						'from' => $_REQUEST['data']['email'],
						'subject'=>'A link request has been submitted',
						'content'=>$_REQUEST['data']),1);
			break;
		default :
			break;
	}
}

// get the content
$content = $Core->getContent($path);

// create the smarty variables
$Smarty->assign('page',$content);
$Smarty->assign('content',$content['content']);	// text content
$Smarty->assign('title',$content['title']);	// h2 title
?>
