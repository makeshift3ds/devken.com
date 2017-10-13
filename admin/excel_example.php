<?php

include ('admin_header.php');

$Core = Registry::getKey('Core');
$Admin = Registry::getKey('Admin');
$Smarty = Registry::getKey('Smarty');
	

$data = array(
				array('id','sku','category id','title','description','price','shipping','handling','stock','product weight','length','width','height','order weight'),
				array('1','00987xac8g43','1','Multi Tool','This is a great tool','12.00','1.22','0.33','42','1.2','12','2.2','0.25','0'),
				);

$Admin->export('example_excel',null,null,$data);



/*switch($_REQUEST['mode']) {
	case 'insert_address':
		$Admin->insert('addresses',$_REQUEST['data']);
		$Core->redirect('admin/user_list.php');
		break;
	case 'remove_address':
		$Admin->deactivate('addresses',$_REQUEST['id']);
		$Core->redirect('admin/user_list.php');
		break;
	case 'update_address':
		$Admin->update('addresses',$_REQUEST['data']);
		$Core->redirect('admin/user_list.php');
		break;
	case 'order_address':
		$Admin->order('addresses',$_REQUEST['ids'],$_REQUEST['weights']);
		$Core->redirect('admin/user_list.php');
		break;
	case 'export_addresses':
		break;
	default :
		echo 'invalid arguments';
		exit;
		break;
}
*/
?>