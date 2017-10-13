<?php
// page id
$controlid="blog";

// get the classes from the registry
$Core = Registry::getKey('Core');
$Smarty = Registry::getKey('Smarty');
$Cart = Registry::getKey('Cart');

$searches['blog_posts'][] = array('column_id'=>'meta_keywords','score'=>50);
$searches['blog_posts'][] = array('column_id'=>'meta_description','score'=>25);
$searches['blog_posts'][] = array('column_id'=>'title','score'=>100);
$searches['blog_posts'][] = array('column_id'=>'content','score'=>10);

$Smarty->assign('searches',$searches);


$Smarty->assign('no_header',1); // do not show h1 in main_template - blog will show it

$content = $Core->getContent($controlid);
if(isset($content)) {
	$Smarty->assign('page',$content);
	$Smarty->assign('content',$content['content']);	// text content
	$Smarty->assign('title',$content['title']);	// h2 title
}
?>

