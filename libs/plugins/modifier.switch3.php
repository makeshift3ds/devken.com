<?php
function smarty_modifier_switch3($string, $option1 = null, $option2 = null, $option3 = null)
{
    switch($string){
        case "0":
                return $option1;

        case "1":
                return $option2;

        default:
                return $option3;
    }
}

/* vim: set expandtab: */

?>
