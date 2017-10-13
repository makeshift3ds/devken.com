<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Smarty numbers_us modifier plugin
 *
 * Type:     modifier<br>
 * Name:     numbers_us<br>
 * Purpose:  Replaces ,'s with .'s. Thats it. Nothing else to see here.
 * @link http://www.php.net/money_format
 * @param float
 * @param string format (default %n)
 * @return string
 */
function smarty_modifier_numbers_us($number, $filter=',') {
    $num = str_replace($filter,'.',$number);
    return $num;

}

/* vim: set expandtab: */
?>
