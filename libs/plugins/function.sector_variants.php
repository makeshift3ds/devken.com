<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {sector_variants} plugin
 *
 * Type:     function<br>
 * Name:     sector_variants<br>
 * Purpose:  smarty interface to HedgeCo Sectors and Holdings Programs variable styles for data cells. Will print out css for table data cells.
 * @param template,width,use_fieldset
 * @param Smarty
 * @return null
 */
function smarty_function_sector_variants($params, &$smarty) {
	$css = '';
	switch($params['variant']) {
		case 'bold' :
			$css = 'font-weight: bold;';
			break;
		case 'italic' :
			$css = 'font-style: italic;';
			break;
		case 'small-caps' :
			$css = 'font-variant: small-caps;';
			break;
		case 'highlight' :
			$css = 'font-weight: bold; color: #055e8b; background-color:#ddd;';
			break;
		case 'strike' :
			$css = 'text-decoration: line-through;';
			break;
		case 'small' :
			$css = 'font-size: 80%;';
			break;
		default :
			break;
	}

	switch($params['align']) {
		case 'right' :
			$css .= 'text-align: right;';
			break;
		case 'center' :
			$css .= 'text-align: center;';
			break;
		default :
			$css .= 'text-align: left;';
			break;
	}

	if($params['variant'] == 'highlight') {
		return "style='$css'";
	} else {
		return "style='background-color:#FFF;$css'";
	}
}

/* vim: set expandtab: */

?>
