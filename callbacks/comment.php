<?php
// page id
$controlid="comment";

// get the classes from the registry
$Core = Registry::getKey('Core');
$Smarty = Registry::getKey('Smarty');
$Cart = Registry::getKey('Cart');

$content = $Core->getContent($controlid);

if(isset($_REQUEST['data'])) {
		$content = '';
		foreach($_REQUEST['data'] as $k=>$v) {
			$content .= "<strong>{$k}</strong>: {$v}<br />";
		}
		$email = isset($_REQUEST['data']['email']) && Validate::email($_REQUEST['data']['email']) ? $_REQUEST['data']['email'] : 'invalid@provided.com';
		$Core->sendMail(array(
								'to' => Registry::getParam('Config','default_email'),
								'from' => $email,
								'subject' => 'A Request was submitted through your website',
								'content' => $content
								));
		$Core->redirect('comment');
}

if(isset($content)) {
		$Smarty->assign('page',$content);
		if(isset($content['content'])) $Smarty->assign('content',$content['content']);	// text content
		if(isset($content['title'])) $Smarty->assign('title',$content['title']);	// h2 title
}
?>

