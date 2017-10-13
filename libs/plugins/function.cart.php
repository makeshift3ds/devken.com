<?php

/**
 * Smarty {cart} plugin
 *
 * Type:     function<br>
 * Name:     cart<br>
 * Purpose:  smarty interface to Cart.class.php
 * @param array
 * @param Smarty
 * @return null
 */
function smarty_function_cart($params, &$smarty) {
	// get the cart from the registry
	$Cart = Registry::getKey('Cart');

	// get the class methods (internal method) easier to search this way
	$methods = get_class_methods($Cart);

	// set a default if the method is not defined
	if(!isset($params['method']) || $params['method'] == '') $params['method'] = 'NULL';

	// trigger_error if the method does not exist
	if(!array_search($params['method'],$methods)) trigger_error('Supplied method "<strong>'.$params['method'].'</strong>" does not exist in Cart');

	// get the method
	$cmeth = new ReflectionMethod('Cart', $params['method']);

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
	$val = call_user_func_array(array($Cart, $params['method']), $vars);

	// if getContent parse smarty tags in the data
	if($params['method'] == 'getContent') {
		$val['data'] = $smarty->fetch('text:'.$params['pageid']);
	}

	// assign the value(s)
	$smarty->assign($params['assign'],$val);
}

/* vim: set expandtab: */

?>
