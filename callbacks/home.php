<?php
// page id
$controlid="home";

// get the classes from the registry
$Core = Registry::getKey('Core');
$Smarty = Registry::getKey('Smarty');

// get the content
$content = $Core->getContent($controlid);
$content['title'] = 'Home';
$Smarty->assign('page',$content);
?>

