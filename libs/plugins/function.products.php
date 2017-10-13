<?php

/**
 * Smarty {products} plugin
 *
 * Type:     function<br>
 * Name:     products<br>
 * Purpose:  smarty interface to Products.class.php
 * @param array
 * @param Smarty
 * @return null
 */
function smarty_function_products($params, &$smarty) {
	// get the products from the registry
	$Products = Registry::getKey('Products');

	// get the class methods (internal method) easier to search this way
	$methods = get_class_methods($Products);

	// set a default if the method is not defined
	if(!isset($params['method']) || $params['method'] == '') $params['method'] = 'NULL';

	// trigger_error if the method does not exist
	if(!array_search($params['method'],$methods)) trigger_error('Supplied method "<strong>'.$params['method'].'</strong>" does not exist in Products');

	// get the method
	$cmeth = new ReflectionMethod('Products', $params['method']);

	// get the arguments
	$args = $cmeth->getParameters();

	// final argument list
	$vars = array();

	// go through all the arguments
	foreach ($args as $i => $arg) {
		// see if the argument has been provided
		if(isset($params[$arg->getName()])) {
			// if so add it to the parameter list
			$vars[] = $params[$arg->getName()];
		} elseif(!$arg->isOptional()) {
			// if it hasn't been provided and is required issue an error
			trigger_error('Request to method <strong>'.$params['method'].'</strong> is missing required argument '.$arg->getName().'.');
		} else {
			// else just provide null
			$vars[] = null;
		}
	}

	// execute the method
	$val = call_user_func_array(array($Products, $params['method']), $vars);
		
	// assign the value(s)
	$smarty->assign($params['assign'],$val);
}

/* vim: set expandtab: */

?>
