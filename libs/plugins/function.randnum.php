<?php
/**
* Smarty plugin
* @package Smarty
* @subpackage plugins
*/

/**
* Smarty function plugin
* -------------------------------------------------------------
* Type:     function
* Name:     randnum
* Author:   Ken Elliott (HedgeCo Networks)
* Purpose:  returns a random whole number
* -------------------------------------------------------------
* @param int min optional lower range limit (defaults to 0)
* @param int max optional upper range limit (defaults to 1000)
* @return int
*/
function smarty_function_randnum($params, &$smarty) {
	// bring the params into the local scope
	extract($params);

	// set some defaults
	if(!is_integer($min)) $min = 0;
	if(!is_integer($max)) $max = 1000;
	if(!is_integer($decimals)) $decimals = 2;

	// get the random number
	$random_number = number_format(($min+lcg_value()*(abs($max-$min))), $decimals, '.', '');

	// if it wants to be returned assign it to a smarty value
	if (isset($assign)) {
		$smarty->assign($assign, $random_number);
	} else {
		// otherwise just print it out
		return $random_number;
	}
}