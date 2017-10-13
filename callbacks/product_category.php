<?php
// page id
$path="product_category";

// get the classes from the registry
$Core = Registry::getKey('Core');
$Smarty = Registry::getKey('Smarty');
$Commerce = Registry::getKey('Commerce');


// get the content
$content = $Core->getTable('product_categories','id='.Utilities::sanitize($_REQUEST['id']),1);
$content['content'] = $content['description'];
$Smarty->assign('page',$content);
?>

