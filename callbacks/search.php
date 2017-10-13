<?php

// page id
$controlid="search";

// get the classes from the registry
$Core = Registry::getKey('Core');
$Smarty = Registry::getKey('Smarty');

$searches['pages'][] = array('column_id'=>'meta_keywords','score'=>50);
$searches['pages'][] = array('column_id'=>'meta_description','score'=>25);
$searches['pages'][] = array('column_id'=>'title','score'=>100);
$searches['pages'][] = array('column_id'=>'content','score'=>10);
$searches['products2'][] = array('column_id'=>'title','score'=>100);
$searches['products2'][] = array('column_id'=>'content','score'=>50);
$searches['events'][] = array('column_id'=>'title','score'=>100);
$searches['events'][] = array('column_id'=>'content','score'=>50);

$Smarty->assign('searches',$searches);

// get the content
$content = $Core->getContent($controlid);
$Smarty->assign('page',$content);
?>