<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty default modifier plugin
 *
 * Type:     modifier<br>
 * Name:     default<br>
 * Purpose:  designate default value for empty variables
 * @link http://smarty.php.net/manual/en/language.modifier.default.php
 *          default (Smarty online manual)
 * @param string
 * @param string
 * @return string
 */
function smarty_modifier_default($string, $default = '')
{
    if (!isset($string) || $string === '' || $string == '0000-00-00')
        return $default;
    else
        return $string;
}

/* vim: set expandtab: */

?>
