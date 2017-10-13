<?php

include ('admin_header.php');

$Core = Registry::getKey('Core');
$Admin = Registry::getKey('Admin');
$Smarty = Registry::getKey('Smarty');
$Database = Registry::getKey('Database');

switch($_REQUEST['mode']) {
	case 'insert_post':
		$_REQUEST['data']['slug'] = ($_REQUEST['data']['url'] != '') ? $Core->slugCreate($_REQUEST['data']['url']) : $Core->slugCreate($_REQUEST['data']['title']);
		$id = $Admin->insert('blog_posts',$_REQUEST['data']);
		$Admin->parseBlogImages($id,$_REQUEST['data']['content']);
		$Core->redirect('admin/blog_edit.php?id='.$id);
		break;
	case 'remove_post':
		$Database->query('delete from blog_images where blog_id = '.Utilities::sanitize($_REQUEST['id']));	// remove blog images
		$Admin->deactivate('blog_posts',$_REQUEST['id']);
		$Core->redirect('admin/blog_list.php');
		break;
	case 'update_post':
		$Database->query('delete from blog_images where blog_id = '.Utilities::sanitize($_REQUEST['data']['id']));
		$_REQUEST['data']['slug'] = ($_REQUEST['data']['url'] != '') ? $Core->slugCreate($_REQUEST['data']['url']) : $Core->slugCreate($_REQUEST['data']['title']);
		$Admin->update('blog_posts',$_REQUEST['data']);
		$Admin->parseBlogImages($_REQUEST['data']['id'],$_REQUEST['data']['content']);
		$Core->redirect('admin/blog_list.php');
		break;
	case 'order_posts':
		$Admin->order('blog_posts',$_REQUEST['ids'],$_REQUEST['weights']);
		$Core->redirect('admin/blog_list.php');
		break;
	case 'insert_blog_category':
		$Admin->insert('blog_categories',$_REQUEST['data']);
		$Core->redirect('admin/blog_category_list.php');
		break;
	case 'remove_blog_category':
		$Admin->remove('blog_categories',$_REQUEST['id']);
		$Core->redirect('admin/blog_category_list.php');
		break;
	case 'update_blog_category':
		$Admin->update('blog_categories',$_REQUEST['data']);
		$Core->redirect('admin/blog_category_list.php');
		break;
	case 'order_blog_categories':
		$Admin->order('blog_categories',$_REQUEST['data']);
		$Core->redirect('admin/blog_category_list.php');
		break;
	case 'remove_blog_post_to_category':
		$Admin->remove('blog_posts_to_categories',$_REQUEST['data']);
		$Core->redirect('admin/blog_edit.php?id='.$_REQUEST['data']['blog_id']);
		break;
	case 'insert_blog_post_to_category':
		if($Admin->isDuplicate('blog_posts_to_categories',$_REQUEST['data'])) $Core->redirect('admin/blog_edit.php?id='.$_REQUEST['data']['blog_id']);	// check to see if it is a duplicate first
		$Admin->insert('blog_posts_to_categories',$_REQUEST['data']);
		$Core->redirect('admin/blog_edit.php?id='.$_REQUEST['data']['blog_id']);
		break;
	default :
		Error::programError('Invalid Mode','This is a program error. Information about this error has been emailed to the administrator. Please contact them if the error persists.');
		exit;
		break;
}
?>