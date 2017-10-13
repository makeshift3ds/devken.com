<?php
/*
 * Smarty plugin
 *
-------------------------------------------------------------
 * File:     modifier.money_format.php
 * Type:     modifier
 * Name:     money_format
 * Version:  1.0
 * Date:     Feb 8th, 2003
 * Purpose:  pass value to PHP money_format() and return result
 * Install:  Drop into the plugin directory.
 * Author:   Michael L. Fladischer <mfladischer@abis.co.at>
 *
-------------------------------------------------------------
 */
function smarty_modifier_money_format($string)
{
	return '$'.sprintf('%0.2f', $string);
}

/* vim: set expandtab: */

?>