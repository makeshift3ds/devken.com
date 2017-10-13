<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {core} plugin
 *
 * Type:     function<br>
 * Name:     iconize<br>
 * Purpose:  provide a filename and it will return an image associated with the file extension
 * @param array
 * @param Smarty
 * @return null
 */
function smarty_function_iconize($params, &$smarty) {
	//if(!isset($params['filename']) || !strpos($params['filename'],'.')) return null;

	$fparts = explode('.',$params['filename']);
	$ext = $fparts[count($fparts)-1];

	switch(strtolower($ext)) {
		case 'pdf':
			return "<img src='images/universal/icon_acrobat.gif' alt='' style='margin-bottom:-4px;' />";
			break;
		case 'xls':
			return "<img src='images/universal/icon_excel.gif' alt='' style='margin-bottom:-4px;' />";
			break;
		case 'doc':
			return "<img src='images/universal/icon_word.gif' alt='' style='margin-bottom:-4px;' />";
			break;
		case 'ppt':
			return "<img src='images/universal/icon_powerpoint.gif' alt='' style='margin-bottom:-4px;' />";
			break;
		case 'zip':
			return "<img src='images/universal/icon_winzip.gif' alt='' style='margin-bottom:-4px;' />";
			break;
		case 'wmv':
		case 'asf':
		case 'flv':
		case 'f4v':
		case 'mov':
		case 'mpg':
		case 'mpeg':
		case 'mp4':
		case 'mp3':
			return "<img src='images/universal/icon_video.gif' alt='' style='margin-bottom:-4px;' />";
		case 'gif':
		case 'jpg':
		case 'jpeg':
		case 'png':
		case 'bmp' :
			return "<img src='images/universal/icon_photo.gif' alt='' style='margin-bottom:-4px;' />";
			break;
		default :
			return null;
			break;
	}
}

/* vim: set expandtab: */

?>
