<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {sectors} plugin
 *
 * Type:     function<br>
 * Name:     sectors<br>
 * Purpose:  smarty interface to HedgeCo Sectors and Holdings Program. Will print out sector and holdings data using different templates.
 * @param template,width,use_fieldset
 * @param Smarty
 * @return null
 */
function smarty_function_sectors($params, &$smarty) {
	// get the core from the registry
	$Core = Registry::getKey('Core');

	// check to see if a group was provided also check to make sure it exists in the database.
	// perhaps we should not display an error
	if(!isset($params['groupid'])) {
		trigger_error('The groupid provided is invalid. Your sector data can not be displayed.');
		return;
	}

	if(strrpos($params['groupid'],',')) {
		$ids = explode(',',$params['groupid']);
		foreach($ids as $k=>$v) {
			$pr = array();
			$pr['groupid'] = $v;
			$pr['template'] = 'grouper';
			$pr['index'] = $k;
			$pr['group_count'] = count($ids);
			if($k+1 == count($ids)) $pr['show_js'] = 1;
			smarty_function_sectors($pr,$smarty);
		}
		return;
	}

	$template = isset($params['template']) ? $params['template'] : '';	// none provided it will use default switch
	$group = $Core->getSectorGroup($params['groupid']);
	$headers = $Core->getSectorFields($group['sid']);
	$data = $Core->getGroupSectorValues($group['sid'],$params['groupid']);

	$smarty->assign('group',$group);
	$smarty->assign('headers',$headers);
	$smarty->assign('data',$data);
	$smarty->assign('params',$params);

	switch($template) {
		case 'vertical' :
			$smarty->display('tables/vertical.html');
			break;
		case 'grouper' :
			$smarty->display('tables/grouper.html');
			if($params['show_js']) $smarty->display('tables/grouper_js.html');
			break;
		case 'custom' :
			$smarty->display('tables/custom.html');
			break;
		case 'horizontal' :
		default :
			$smarty->display('tables/horizontal.html');
			break;
	}

}

/* vim: set expandtab: */

?>
