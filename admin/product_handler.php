<?php

include ('admin_header.php');

$Core = Registry::getKey('Core');
$Admin = Registry::getKey('Admin');

switch($_REQUEST['mode']) {
	case 'insert_product':
		$id = $Admin->insert('products',$_REQUEST['data']);
		if(!Registry::getParam('Config','show_product_categories')) $Admin->insert('products_to_categories',array('product_id'=>$id,'category_id'=>'1'));
		$Core->redirect('admin/product_edit.php?id='.$id);
		break;
	case 'insert_product_to_category':
		if($Admin->isDuplicate('products_to_categories',$_REQUEST['data'])) $Core->redirect('admin/product_edit.php?id='.$_REQUEST['data']['product_id']);	// check to see if it is a duplicate first
		$Admin->insert('products_to_categories',$_REQUEST['data']);
		$Core->redirect('admin/product_edit.php?id='.$_REQUEST['data']['product_id']);
		break;
	case 'remove_product':
		$Admin->deactivate('products',$_REQUEST['id']);
		$Core->redirect('admin/product_list.php');
		break;
	case 'remove_product_to_category':
		$Admin->remove('products_to_categories',$_REQUEST['data']);
		$Core->redirect('admin/product_edit.php?id='.$_REQUEST['data']['product_id']);
		break;
	case 'remove_product_to_group':
		$Admin->remove('products_to_groups',$_REQUEST['data']);
		if($_REQUEST['redir'] == 'group') {
			$Core->redirect('admin/product_group_edit.php?id='.$_REQUEST['data']['group_id']);
		} else {
			$Core->redirect('admin/product_edit.php?id='.$_REQUEST['data']['product_id']);
		}
				
		break;
	case 'update_product':
		$Admin->update('products',$_REQUEST['data']);
		$Core->redirect('admin/product_edit.php?id='.$_REQUEST['data']['id']);
		break;
	case 'order_products':
		$Admin->order('products',$_REQUEST['ids'],$_REQUEST['weights']);
		$Core->redirect('admin/product_list.php?id='.$_REQUEST['id']);
		break;
	case 'export_products':
		$Admin->export('products');
		break;
	case 'insert_product_category':
		$Admin->insert('product_categories',$_REQUEST['data']);
		$Core->redirect('admin/product_category_list.php');
		break;
	case 'remove_product_category':
		$Admin->deactivate('product_categories',$_REQUEST['id']);
		$Core->redirect('admin/product_category_list.php');
		break;
	case 'update_product_category':
		$Admin->update('product_categories',$_REQUEST['data']);
		$Core->redirect('admin/product_category_list.php');
		break;
	case 'order_product_categories':
		$Admin->order('product_categories',$_REQUEST['ids'],$_REQUEST['weights']);
		$Core->redirect('admin/product_category_list.php');
		break;
	case 'export_product_categories':
		$Admin->export('product_categories');
		break;
	case 'insert_product_group':
		$Admin->insert('product_groups',$_REQUEST['data']);
		$Core->redirect('admin/product_group_list.php');
		break;
	case 'remove_product_group':
		$Admin->deactivate('product_groups',$_REQUEST['id']);
		$Core->redirect('admin/product_group_list.php');
		break;
	case 'update_product_group':
		$Admin->update('product_groups',$_REQUEST['data']);
		$Core->redirect('admin/product_group_list.php');
		break;
	case 'order_product_groups':
		$Admin->order('product_groups',$_REQUEST['ids'],$_REQUEST['weights']);
		$Core->redirect('admin/product_group_list.php');
		break;
	case 'export_product_groups':
		$Admin->export('product_groups');
		break;	
	case 'insert_product_to_group':
		if($Admin->isDuplicate('products_to_groups',$_REQUEST['data'])) $Core->redirect('admin/product_edit.php?id='.$_REQUEST['data']['product_id']);	// check to see if it is a duplicate first
		$Admin->insert('products_to_groups',$_REQUEST['data']);
		$Core->redirect('admin/product_edit.php?id='.$_REQUEST['data']['product_id']);
		break;
	case 'insert_image':
		$Admin->insertImage($_FILES['image'],$_REQUEST['data'],$_REQUEST['title'],$_REQUEST['description']);
		$Core->redirect('admin/product_edit.php?id='.$_REQUEST['data']['product_id']);
		break;	
	case 'remove_image':
		$Admin->unlinkProductImages(Registry::getParam('Config','file_dir').'images/product_images/',$_REQUEST['filename']);
		$Admin->remove('product_images',$_REQUEST['id']);
		$Core->redirect('admin/product_edit.php?id='.$_REQUEST['product_id']);
		break;	
	case 'add_product_description':
		$description_id = $Admin->insert('product_descriptions',$_REQUEST['data']);
		$Admin->insert('products_to_descriptions',array('product_id'=>$_REQUEST['product_id'],'description_id'=>$description_id));
		$Core->redirect('admin/product_edit.php?id='.$_REQUEST['product_id']);
		break;
	case 'remove_product_description':
		$Admin->remove('products_to_descriptions',null,'where product_id = '.$_REQUEST['product_id'].' and description_id='.$_REQUEST['description_id']);
		$Admin->remove('product_descriptions',$_REQUEST['description_id']);
		$Core->redirect('admin/product_edit.php?id='.$_REQUEST['product_id']);
		break;
	case 'update_product_description':
		$Admin->update('product_descriptions',$_REQUEST['data']);
		$Core->redirect('admin/product_edit.php?id='.$_REQUEST['product_id']);
		break;
	default :
		Error::programError('Invalid Mode','This is a program error. Information about this error has been emailed to the administrator. Please contact them if the error persists.');
		exit;
		break;
}
?>