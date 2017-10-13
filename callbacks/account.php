<?php
// page id
$controlid="account";

// get the classes from the registry
$Core = Registry::getKey('Core');
$Smarty = Registry::getKey('Smarty');
$Cart = Registry::getKey('Cart');
$Database = Registry::getKey('Database');

if(!isset($_SESSION['user']['table_users']['id'])) $Core->redirect('login');

if(isset($_REQUEST['debug']) && $_REQUEST['debug'] == 'cart') $Core->dump('Cart',$Cart->getCart());

if(isset($_REQUEST['mode'])) {
		switch($_REQUEST['mode']) {
			case 'update' :
				$error_msg = $Core->updateAccountInfo($_REQUEST['data']);
				if($error_msg) {
					Error::websiteError($error);
				} else {
					$_SESSION['success_msg'] = 'Your Account Information has been updated successfully';
				}
				$Core->redirect('account');
				break;
			default:
				break;
		}
}

$content = $Core->getContent($controlid);
if($content) {
		$Smarty->assign('page',$content);
		$Smarty->assign('content',$content['content']);	// text content
		$Smarty->assign('title',$content['title']);	// h2 title
}
?>

