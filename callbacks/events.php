<?php
// page id
$controlid="events";

// get the classes from the registry
$Core = Registry::getKey('Core');
$Smarty = Registry::getKey('Smarty');
$Cart = Registry::getKey('Cart');

$content = $Core->getContent($controlid);
if(isset($content)) {
		$Smarty->assign('page',$content);
		$Smarty->assign('content',$content['content']);	// text content
		$Smarty->assign('title',$content['title']);	// h2 title
}
?>

