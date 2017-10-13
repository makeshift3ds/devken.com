<?php

include ('admin_header.php');

Error::logError('Media Upload Error','testing');
$Core = Registry::getKey('Core');
$Admin = Registry::getKey('Admin');
$Smarty = Registry::getKey('Smarty');

switch($_REQUEST['mode']) {
	case 'insert_media':
		$Admin->insert('media',$_REQUEST['data']);
		$Core->redirect('admin/media_list.php');
		break;
	case 'remove_media':
		$Admin->deactivate('media',$_REQUEST['id']);
		$Core->redirect('admin/media_list.php');
		break;
	case 'update_media':
		if($_REQUEST['remove_flag']) $_REQUEST['data']['download'] = '';
		$download = isset($_FILES['download']) ? $_FILES['download'] : null;
		$Admin->update('media',$_REQUEST['data'],$download);
		$Core->redirect('admin/media_list.php');
		break;
	case 'order_media':
		$Admin->order('media',$_REQUEST['ids'],$_REQUEST['weights']);
		$Core->redirect('admin/media_list.php');
		break;
	case 'insert_media_category':
		$Admin->insert('media_categories',$_REQUEST['data']);
		$Core->redirect('admin/media_category_list.php');
		break;
	case 'remove_media_category':
		$Admin->deactivate('media_categories',$_REQUEST['id']);
		$Core->redirect('admin/media_category_list.php');
		break;
	case 'update_media_category':
		$Admin->update('media_categories',$_REQUEST['data']);
		$Core->redirect('admin/media_category_list.php');
		break;
	case 'order_media_categories':
		$Admin->order('media_categories',$_REQUEST['data']);
		$Core->redirect('admin/media_category_list.php');
		break;
	case 'upload_thumbnail':
	case 'upload_media':
			if(isset($_FILES['Filedata'])) {
					$file = $_FILES['Filedata'];
					$return = array();
					
					// sort the files
					$folder = '';
					if($_REQUEST['mode'] == 'upload_thumbnail') {
						$folder = 'thumbnails';
					} elseif($Core->restrictFileTypes(array('file'=>$file['name'],'types'=>array('jpg','gif','bmp','jpeg','png')))) {
						$folder = 'images';
					} elseif($Core->restrictFileTypes(array('file'=>$file['name'],'types'=>array('doc','docx','pdf','xls','txt','wpd','wps','csv','pps','ppt','pptx','wks','xlsx','indd','pct','qxd','qxp','zip','rar','sit','sitx','zipx','bin')))) {
						$folder = 'documents';
					} elseif ($Core->restrictFileTypes(array('file'=>$file['name'],'types'=>array('mp4','flv')))) {
						$folder = 'videos';
					} elseif ($Core->restrictFileTypes(array('file'=>$file['name'],'types'=>array('mp3','aiff','wav')))) {
						$folder = 'audio';
					} else {
						$return['status'] = 0;
						$return['error'] = "'".$ext."' files are not permitted. -- ".serialize($file);
					}
					
					$dbug = 'Filename : '. $file['name'].'\n';
					$dbug .= 'Filetype match : '. $filetype_match.'\n';
					$dbug .= 'Folder : '. $folder.'\n';
					
					if(isset($return['error'])) Error::logError('Media Upload Error',$dbug);

					if(!isset($return['error'])) {
						$doc = $Admin->processFancyUpload($file,'media/'.$folder);
					} else {
						echo json_encode($return);
						Error::logError('Media Upload Error',json_encode($return));
					}
					exit;
			} else {
				Error::programError('Media Upload Error','Upload attempted without a file. Using FancyUpload in media_handler.php.');
			}
		break;
	case 'delete_thumbnail':
	case 'delete_download':
		if(!isset($_REQUEST['id']) || $_REQUEST['id'] == '') {
			Error::websiteError('The request could not be completed because the media id was not present.');
			$Core->redirect('admin/media_edit.php');
		} else {
			if($_REQUEST['mode'] == 'delete_thumbnail') {
				$ref = $Core->getTable('media','id='.Utilities::sanitize($_REQUEST['id']),1);
				$download = Utilities::fileSearch('/'.$ref['thumbnail'].'/','..\media',2);
				unlink($download[0]);
				$Admin->update('media',array('id'=>Utilities::sanitize($_REQUEST['id']),'thumbnail'=>''));
				$_SESSION['success_msg'] = 'The thumbnail was removed successfully';
			} else {
				$Admin->update('media',array('id'=>Utilities::sanitize($_REQUEST['id']),'download'=>''));
				$_SESSION['success_msg'] = 'The download was removed successfully';
			}
			$Core->redirect('admin/media_edit.php?id='.$_REQUEST['id']);
		}
		break;
	default :
		Error::programError('Invalid Mode','This is a program error. Information about this error has been emailed to the administrator. Please contact them if the error persists.');
		//$Core->redirect('');
		echo '1';
		exit;
		break;
}
?>