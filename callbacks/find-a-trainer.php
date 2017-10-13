<?php
// page id
$controlid="clients";

// get the classes from the registry
$Core = Registry::getKey('Core');
$Smarty = Registry::getKey('Smarty');
$Cart = Registry::getKey('Cart');

/*
if(isset($_REQUEST['zip']) && isset($_REQUEST['distance'])) {
		$clients = $Core->getHaversine('clients',Utilities::sanitize($_REQUEST['distance']),Utilities::sanitize($_REQUEST['zip']),'kilometers',null);
		$clientXML = $Core->createGoogleMapsXML($clients);
		$Smarty->assign('clients',$clients);
		$Smarty->assign('clientXML',$clientXML);
}
*/
$content = $Core->getContent($controlid);
if(isset($content)) {
		$Smarty->assign('page',$content);
		$Smarty->assign('content',$content['content']);	// text content
		$Smarty->assign('title',$content['title']);	// h2 title
}
?>

