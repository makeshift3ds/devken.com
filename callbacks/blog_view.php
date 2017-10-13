<?php
// page id
$controlid="blog_view";

// get the classes from the registry
$Core = Registry::getKey('Core');
$Smarty = Registry::getKey('Smarty');
$Cart = Registry::getKey('Cart');

$Smarty->assign('no_header',1);
$page = $Core->getTable('blog_posts',"slug = '".Utilities::sanitize($_REQUEST['slug'])."' and active=1",1);
$content = $Core->getContent($controlid);
if(isset($content)) {
	$Smarty->assign('page',$page);
	$Smarty->assign('content','');	// text content
	$Smarty->assign('title','');	// h2 title
}
?>

