<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Smarty money_format modifier plugin
 *
 * Type:     modifier<br>
 * Name:     money_format<br>
 * Purpose:  Formats a number as a currency string
 * @link http://www.php.net/money_format
 * @param float
 * @param string format (default %n)
 * @return string
 */
 
function smarty_modifier_negative($number = null){
  if($number == null){
	return "<div align='center'>n/a</a>";
  }
  if($number<0.00){
    $number=str_replace("-","",$number);
    $number="<font color='#ff0000'>(".$number.")</font>";
  }
  return $number;
}
?>
