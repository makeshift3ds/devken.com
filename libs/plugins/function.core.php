<?php

/**
 * Smarty {core} plugin
 *
 * Type:     function<br>
 * Name:     core<br>
 * Purpose:  smarty interface to Core.class.php
 * @param array
 * @param Smarty
 * @return null
 */
 
function smarty_function_core($params, &$smarty) {
	// get the core from the registry
	$Core = Registry::getKey('Core');
	
	//$Core->dump($params);

	if(!isset($params['method']) || $params['method'] == '') $Core->dump('smartCore error',$params);	// no methods by those names ;)
	
	Console::logSpeed('{Core} : '.$params['method'].' - assign:'.$params['assign'].' :: Begin');
	// remove method info, leave function arguments
	$assign = isset($params['assign']) ? $params['assign'] : '';
	$method = isset($params['method']) ? $params['method'] : '';
	unset($params['assign']);
	unset($params['method']);
	
	// pull the xml structure of the class from the registry
	$xml = Registry::getParam('ClassStructure','Core');
	
	$xml = simplexml_load_string($xml);
	
	// verify the info before running it
	if($xml->$method) {		// method is available in the class
		// final argument list
		$vars = array();

		// go through all the arguments
		foreach ($xml->$method->children() as $arg => $optional) {
			// see if the argument has been provided
			if(isset($params[$arg])) {
				// if so add it to the parameter list
				$vars[] = $params[$arg];
			} elseif($optional != 1) {
				// if it hasn't been provided and is required issue an error
				echo 'Request to method : <strong>'.$method.'</strong> is missing required argument : <strong>'.$arg.' - '.$optional.'</strong>.';
			} else {
				// else just provide null
				$vars[] = null;
			}
		}
	} else {	// it is not available
		echo '<strong>'.$method.'</strong> is not available';
	}
	$val = call_user_func_array(array($Core, $method), $vars);
	$smarty->assign($assign,$val);
	Console::logSpeed('{Core} : '.$method.' - assign:'.$assign.' :: End');
}

/* vim: set expandtab: */

?>
