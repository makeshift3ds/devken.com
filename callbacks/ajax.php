<?php

// page id
$controlid="ajax";
$no_header = 1;

// get the classes from the registry
$Core = Registry::getKey('Core');
$Smarty = Registry::getKey('Smarty');
$Error = Registry::getKey('Error');

//	public function getHaversine($table=null,$distance=null,$address=null,$lat=null,$lng=null,$where=null,$measurement=null,$debug=null) {
switch ($_REQUEST['mode']) {
	case 'googleXML' :	// output for google maps api
		if(isset($_REQUEST['lat']) && isset($_REQUEST['lng']) && isset($_REQUEST['radius'])) {
				$type_id = isset($_REQUEST['type_id']) ? 'type_id='.Utilities::sanitize($_REQUEST['type_id']) : null;
				$clients = $Core->getHaversine('clients',Utilities::sanitize($_REQUEST['radius']),null,Utilities::sanitize($_REQUEST['lat']),Utilities::sanitize($_REQUEST['lng']),$type_id,'kilometers',null);
				
				$sponsored = $Core->getHaversine('clients','20000',null,Utilities::sanitize($_REQUEST['lat']),Utilities::sanitize($_REQUEST['lng']),'type_id = 3 and id not in ('.$Core->ids($clients).')','kilometers',null);
				$clients = array_merge($clients,$sponsored);
				$clientXML = $Core->createGoogleMapsXML($clients);
				header("Content-type: text/xml");
				header("Pragma: no-cache");
				header("Expires: 0");
				echo $clientXML;
				exit;
		}
		break;
	case 'show_page':
		if (file_exists(Registry::getParam('Config','file_dir')."templates/forms/".Utilities::sanitize($_REQUEST['page']).".html")) {
			$Smarty->display('forms/'.Utilities::sanitize($_REQUEST['page']).'.html');
		} else {
				$info = $Core->getContent(Utilities::sanitize($_REQUEST['page']));
				if(isset($info['title'])) echo '<h1>'.$info['title'].'</h1>';
				if(isset($info['content'])) echo '<p>'.$info['content'].'</p>';
		}
		break;
	default;
		$Error->programError('False Request','This request has been logged because it does not match our guidelines. An administrator has been notified and will be reviewing it shortly. Our apologies for any inconvenience this may have caused.');
		exit;
		break;
}
?>