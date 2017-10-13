<?php

// get the classes from the registry
$Core = Registry::getKey('Core');
$Smarty = Registry::getKey('Smarty');

// get the path
$path =  Registry::getParam('Config','path');

// get the content
$content = $Core->getContent($path);

// create the smarty variables
if((isset($content['title']) || isset($content['content'])) && $content['active']) {
		$Smarty->assign('page',$content);
		$Smarty->assign('content',$content['content']);	// text content
		$Smarty->assign('title',$content['title']);	// h2 title
}
?>
