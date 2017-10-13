<?php
// page id
$controlid="mediaplayer";

// get the classes from the registry
$Core = Registry::getKey('Core');
$Smarty = Registry::getKey('Smarty');
$Cart = Registry::getKey('Cart');

$content = $Core->getContent($controlid);

if(isset($content)) {
		$Smarty->assign('page',$content);
		if(isset($content['content'])) $Smarty->assign('content',$content['content']);	// text content
		if(isset($content['title'])) $Smarty->assign('title',$content['title']);	// h2 title
}
?>

