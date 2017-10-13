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
function smarty_modifier_2point($number, $format='%i')
{
    if (is_numeric($number)){
	    $number = sprintf('%0.1f', $number);
    }
    if($number=="-"){
      return $number;
    }else if($number==""){
      return "-";
    }else if($number=="0.0"){
      return "0%";
    }else{  
    return $number."%";
    }

}

/* vim: set expandtab: */
?>
