<?php
// page id
$controlid="registration";

// get the classes from the registry
$Core = Registry::getKey('Core');
$Smarty = Registry::getKey('Smarty');

if(isset($_REQUEST['mode'])) {
	switch($_REQUEST['mode']) {
		case 'register':
			$error = $Core->registerUser($_REQUEST['data']);
			if($error) {
				$Smarty->assign('user_data',$_REQUEST['data']);
				Error::websiteError($error);
				$Smarty->assign('template','forms/registration.html');
			} else {
				$_SESSION['success_msg'] = '';
				$_SESSION['user_data'] = $_REQUEST['data'];
				$Core->sendMail(array(
					'to'=>$_REQUEST['data']['email'],
					'subject'=>'Registration Received - Action Required to Activate',
					'content'=>'
					<div>
						Thank you for registering with our website. In order to complete your registration please click on the link below. If you are unable to click on it please copy 
						the url taking care to include text that may have been placed on subsequent lines.
					</div>
					<div>
						<a href="'.Registry::getParam('Config','site_url').'verify/'.$_SESSION['reg_id'].'">'.Registry::getParam('Config','site_url').'verify/'.$_SESSION['reg_id'].'</a>
					</div>										
					'
				));
				$Core->redirect('registration-success');
			}
			break;
		case 'activate':
			$Core->activateUser($_REQUEST['reg_id'],'account');
			break;
		default;
			$Smarty->assign('error_msg','An illegal mode was detected. Administrators have been notified and they will respond as soon as possible.');
			break;
	}
} else {
 		$Smarty->assign('template','forms/registration.html');
}

// get the content
$content = $Core->getContent($controlid);
$Smarty->assign('page',$content);
?>

