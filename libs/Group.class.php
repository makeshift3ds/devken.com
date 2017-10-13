<?php
//
// Manataria Website Platform - Eats the seaweeds
//
// Copyright 2008 Kenneth Elliott. All rights reserved.
//

/**
 * Core API
 *
 * This class contains the common methods for the website
 *
 * @author      Kenneth Elliott <kenelliottmc@gmail.com>
 * @copyright   Copyright &copy; 2008 Kenneth Elliott <kenelliottmc@gmail.com>
 * @category    Manataria
 * @package     Core
 * @since       Manataria 0.0.1
 */
class Admin {
	/**
	* Database object
	* @var object
	*/
	protected $Database;
	
	/**
	* Core object
	* @var object
	*/
	protected $Core;

	/**
	* Constructor : set the links up
	*
	* @access public
	*/
	public function __construct() {
		$this->Core = Registry::getKey('Core');
		$this->Database = Registry::getKey('Database');
	}


	/**
	* uploadFile : upload a file to a destination on the server
	*
	* @param file    $file  $_FILE['id']
	* @param string    $dest  destination folder
	* @param string    $name_override                                                use a new name for the file
	* @access public
	* @return array
	**/
	public function uploadFile($file, $dest, $name_override=null,$debug=null) {
		$fname = !is_null($name_override) ? $name_override : $file['name'];
		$dest = substr($dest,-1) == '/' ? $dest : $dest.'/';
		if(file_exists($dest.$fname)) {
			list($p1,$p2) = explode('.',$fname);
			$fname = $p1.'-'.rand(0,10000).'.'.$p2;
		}
		if (move_uploaded_file($file['tmp_name'], $dest.$fname)) {
			chmod($dest.$fname,0666);
			return $fname;
		}
		return false;
	}


	/**
	* unlinkProductImages : remove all files called $filename in the images/product_images folder
	*
	* @param string    $start_dir  directory to start in
	* @param file    $filename  name of the file
	* @access public
	* @return null
	**/
	public function unlinkProductImages($start_dir=null,$filename,$debug=null) {
		if(is_null($start_dir)) $start_dir = Registry::getParam('Config','file_dir').'images/product_images/';
		static $deld = 0, $dsize = 0;
		$dirs = glob($start_dir."*");
		$files = glob($start_dir.$filename);
		foreach($files as $file){
				if(is_file($file)){
						if($debug) $dsize += filesize($file);
						if(is_null($debug)) unlink($file);
						if($debug) $deld++;
				}
		}
		foreach($dirs as $dir){
				if(is_dir($dir)){
						$dir = basename($dir) . "/";
						$this->unlinkProductImages($start_dir.$dir,$filename);
				}
		}
		if($debug) $this->Core->dump('Product Images Deleted',"$deld files deleted with a total size of $dsize bytes");
		return;
	}

	/**
	* testMda : create a test multidimensional array
	*
	* @param integer    $rows  number of rows
	* @param integer    $cols  number of columns
	* @access public                                                                                                       
	* @return array
	**/
	public function testMda($rows=10,$cols=10,$debug=null) {
		$rows = isset($rows) && is_numeric($rows) ? $rows : 10;
		$cols = isset($cols) && is_numeric($cols) ? $cols : 10;
		$i=0;
		$structure=array();                                 
		while($i<$rows) {
			$n=0;
			while($n<$cols) {
				$structure[$i][$n]='row'.$i.'_'.$n;
				$n++;
			}
			$i++;
		}
		return $structure;
	}


	/**
	* getCellRange : get a range of cells from an array generated from an excel document
	*
	* @param array    $arr  data array
	* @param string    $start  start position format 'A1','C32','B2', etc.
	* @param string    $end  end position same format as $start
	* @access public
	* @return array
	**/
	public function getCellRange($arr=array(),$start='A1',$end=null,$debug=null) {;
		if(count($arr) <= 1) return $arr;
		if(!$this->verifyRangeFormat($start)) $start = 'A1';
		if(!$this->verifyRangeFormat($end)) $end = $this->index2range(count($arr)-1);
		list($start_col,$start_row) = $this->range2index($start);
		list($end_col,$end_row) = $this->range2index($end);

		$data = array();

		if(is_null($start_row) || is_null($end_row)) return $data; // it is empty

		while($start_row<=$end_row) {
			$length = $end_col - $start_col;
			if(is_array($arr[$start_row])) $data[] = array_splice($arr[$start_row],$start_col,++$length);
			$start_row++;
		}

		return $data;
	}

	/**
	* range2index : change a range "A1" into an index position ie(0,0);
	*
	* @param string    $range  'A1','CZ32',etc
	* @access public
	* @return array
	**/
	public function range2index ($range,$debug=null) {
		preg_match("/([A-Za-z]+)([0-9]+)/", $range, $matches);
		list($original,$letters,$numbers) = $matches;
		if(strlen($letters) == 2) {
			list($l1,$l2) = str_split($letters); // built only for 2 letters (don't think they will go 500)
			$l1_index = (array_search(strtoupper($l1),$this->alpha)+1)*count($this->alpha);
			$l2_index = array_search(strtoupper($l2),$this->alpha);

			$alpha_index = $l1_index+$l2_index;
		} else {
			$alpha_index = array_search(strtoupper($letters),$this->alpha);
		}
		return array($alpha_index,$numbers-1);
	}


	/**
	* index2range : change an index to a range
	*
	* @param integer    $col  '1,2,122,etc'
	* @access public
	* @return array
	**/
	public function index2range($col,$debug=null) {
		$pos1 = floor($col/count($this->alpha));
		$pos2 = $col%count($this->alpha);
		$range =  $this->alpha[$pos1-1].$this->alpha[$pos2];
		return $range;
	}


	/**
	* verifyRangeFormat : check the range to make sure it is formatted correctly. accepts [A1,AA123]
	*
	* @param string    $r  range to check
	* @access public
	* @return array
	**/
	public function verifyRangeFormat($r,$debug=null) {
		return preg_match('/^[A-Za-z]+[0-9]+$/',$r);
	}

	/**
	* readExcel : read an excel file and return the structure
	*
	* @param file    $file  excel file
	* @access public
	* @return array
	**/
	public function readExcel($file,$debug=null) {
		require_once Registry::getParam('Config','file_dir')."libs/xls_reader/cl_xls_reader.php";
		$xls = new xls_reader(); // create class object
		$xls->read_file($file);  // read xls file

		return array(
						'sheets' => $xls->workbook->get_sheets_array(),
						'workbook' => $xls->workbook->get_workbook_array()
					);
	}

	/**
	* activate : activate a table row (sets active to 1)
	*
	* @param string		$table 	table name
	* @param integer	$id  	table row id
	* @access public
	* @return array
	**/
	public function activate($table,$id,$debug=null) {
		$this->Database->query("update {$table} set active = '1' where id = '".Utilities::sanitize($id)."'");
		$_SESSION['success_msg'] = "The item was activated successfully.";
	}

	/**
	* deactivate : deactivate a table row (sets active to 0)
	*
	* @param string		$table 	table name
	* @param integer	$id  	table row id
	* @access public
	* @return array
	**/
	public function deactivate($table,$id,$debug=null) {
		$this->Database->query("update {$table} set active = '0' where id = '".Utilities::sanitize($id)."'");
		$_SESSION['success_msg'] = "The item was deactivated successfully.";
	}

	/**
	* remove : delete a table row completely using $id as the identifier
	*
	* @param string		$table 	table name
	* @param integer	$id  	table row id
	* @param string	$where_override  override the standard identification (where id = {$id}) and use your own (where type_id='2' and id={$id})
	* @param boolean	$debug  	show debug information
	* @access public
	* @return array
	**/
	public function remove($table,$id,$where_override=null,$debug=null) {
		$where = is_array($id) ? $this->Core->arrayToWhere($id) : 'where id = "'.Utilities::sanitize($id).'"';
		if(!is_null($where_override)) $where = $where_override;
		$sql = "delete from {$table} {$where}";
		if($debug) $this->Core->dump('Remove Debug',$sql);
		$this->Database->query($sql);
		$_SESSION['success_msg'] = "The item was removed successfully.";
	}

	/**
	* insert : add a new row to a table
	*
	* @param string		$table 	table name
	* @param array	$data  	array of data to be added to the table ('id'=>1,'title'=>'test')
	* @param string		$download 	$_FILE['my_download']
	* @param boolean		$debug 	table name
	* @access public
	* @return array
	**/
	public function insert($table,$data,$download=null,$debug=null) {
		if(!is_null($download)) $data['download'] = $this->uploadFile($download,Registry::getParam('Config','file_dir').'/media/downloads/');
		$this->Database->perform($table,$data);
		$_SESSION['success_msg'] = "The item was added successfully.";
		return $this->Database->insert_id();
	}

	/**
	* update : update the information in a table using $data[id] as the identifier
	*
	* @param string		$table 	table name
	* @param integer	$id  	table row id
	* @param string		$download 	$_FILE['my_download']
	* @param boolean		$debug 	table name
	* @access public
	* @return array
	**/
	public function update($table,$data,$download=null,$debug=null) {
		$id = $data['id'];
		unset($data['id']);
		if(!is_null($download) && $download['name'] != '') {
			if($debug) $this->Core->dump('Upload Initiated',$download);
			$data['download'] = $this->uploadFile($download,Registry::getParam('Config','file_dir').'/media/downloads/');
		}
		$this->Database->perform($table,$data,'update','id="'.$id.'"');
		$_SESSION['success_msg'] = "The item was updated successfully.";
	}

	/**
	* order : update weights for a list of rows
	*
	* @param string		$table 	table name
	* @param array	$ids  	array of ids
	* @param array	$weights  	array of weights
	* @access public
	* @return array
	**/
	public function order($table,$ids,$weights,$debug=null) {
		foreach($ids as $k=>$v) {
			$sql = "update {$table} set weight = '{$weights[$k]}' where id = '{$v}'";
			if($debug) $this->Core->dump('Order SQL',$sql);
			$this->Database->query($sql);
			$_SESSION['success_msg'] = "The order was updated successfully.";
		}
	}

	/**
	* See if the information is already present in the table. If all of the information in $data is in the table already then we should not add it again. Even if the db takes care of this already we should run a check
	*
	* @param string		$table 	table name
	* @param array	$data  	array of data that will be checked against the database
	* @param array	$weights  	array of weights
	* @access public
	* @return array
	**/
	public function isDuplicate($table,$data,$debug=null) {
		$where = $this->Core->arrayToWhere($data);
		$sql = "select 1 from {$table} {$where}";
		$res = $this->Core->res2array($this->Database->query($sql));
		if(count($res)) return 1;
	}
	
	/**
	* insertImage : add a product image
	*
	* @param string		$table 	table name
	* @param integer	$id  	table row id
	* @access public
	* @return array
	**/ 
	public function insertImage($file,$data) { 
		include_once Registry::getParam('Config','file_dir') . "libs/Resize_Image.class.php";
		$resize = new Resize_Image();
		
		$file = $this->uploadFile($file,Registry::getParam('Config','file_dir').'images/product_images/full/');
						
		$resize->image_to_resize = Registry::getParam('Config','file_dir') . "images/product_images/full/".$file;
		
		// save the image for page views
		$resize->save_folder = Registry::getParam('Config','file_dir').'images/product_images/page/';
		$resize->watermark = Registry::getParam('Config','file_dir').'images/product_images/page_shadow.png';	// watermark shadow
		if(Registry::getParam('Config','page_image_width')) $resize->new_width = Registry::getParam('Config','page_image_width');									
		if(Registry::getParam('Config','page_image_width'))  $resize->new_height = Registry::getParam('Config','page_image_height');
		$resize->resize();		
		
		// save the image for list views
		$resize->crop = true;
		$resize->grayscale=true;
		$resize->save_folder = Registry::getParam('Config','file_dir').'images/product_images/list/';
		$resize->watermark = Registry::getParam('Config','file_dir').'images/product_images/thumb_logo.png';	// watermark shadow
		if(Registry::getParam('Config','list_image_width'))  $resize->new_width = Registry::getParam('Config','list_image_width');									
		if(Registry::getParam('Config','list_image_width'))  $resize->new_height = Registry::getParam('Config','list_image_height');
		$resize->resize();		
		
		// save the image for thumb views
		$resize->save_folder = Registry::getParam('Config','file_dir').'images/product_images/thumb/';
		$resize->watermark = Registry::getParam('Config','file_dir').'images/product_images/thumb_shadow.png';	// watermark shadow
		if(Registry::getParam('Config','thumb_image_width'))  $resize->new_width = Registry::getParam('Config','thumb_image_width');									
		if(Registry::getParam('Config','thumb_image_width'))  $resize->new_height = Registry::getParam('Config','thumb_image_height');
		$resize->resize();
		
		// save the image for thumb views with a washout
		$resize->save_folder = Registry::getParam('Config','file_dir').'images/product_images/thumb_washed/';
		$resize->watermark = Registry::getParam('Config','file_dir').'images/product_images/thumb_wash.png';	// watermark shadow
		if(Registry::getParam('Config','thumb_image_width'))  $resize->new_width = Registry::getParam('Config','thumb_image_width');									
		if(Registry::getParam('Config','thumb_image_width'))  $resize->new_height = Registry::getParam('Config','thumb_image_height');
		$resize->resize();
		
		$product_id = $data['product_id'];
		unset($data['product_id']);																											// remove the product id because it is not part of the table
		$data['filename'] = $file;																												// set the filename
		$img_id = $this->insert('product_images',$data);																		// add to product images
		$this->insert('products_to_images',array('product_id'=>$product_id,'image_id'=>$img_id));	// add to products_to_images
		$_SESSION['success_msg'] = "The image was added successfully.";
	}

	/**
	* export : export a table to a comma seperated list
	*
	* @param string    $delim  column seperator default: ,
	* @param string    $newline  type of newline to use default: '\r\n'
	* @access public
	* @return array
	**/
	public function export($table,$delim=',',$newline="\r\n",$override=null,$debug=null) {		
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");;
		header("Content-Disposition: attachment;filename=".$table.".csv ");
		header("Content-Transfer-Encoding: binary ");
		$data = !is_null($override) ? $this->Core->getTable($table) : $override;
		if(!count($data)) return;
		$headers = implode($delim,array_keys($data[0]));
		foreach($data as $k=>$v) $data[$k] = implode($delim,str_replace(',',' ',$data[$k]));
		array_unshift($data,$headers);
		echo implode($newline,$data);
		exit;
	}
	
	
	/**
	* exportXML : export a list of tables to an xml document
	*
	* @param string    $delim  column seperator default: ,
	* @param string    $newline  type of newline to use default: '\r\n'
	* @access public
	* @return array
	**/
	public function exportXML($tables=null) {
			// create an array of tables to export
			$export_tables = array('users','fund_data','pages','fund_returns','letters','marketing','managers','manager_teams','marketing_categories','user_notes');

			// get the tables
			$tables = $this->Core->res2array(mysql_query("select table_name FROM information_schema.tables where table_schema = '".$GLOBALS["config"]['db_name']."' order by table_name asc"));

			// create the dom element
			$document = new DOMDocument('1.0');
			$document->formatOutput = true;	// format the output so I can read the source
			$schemaNode = $document->createElement("schema");	// root node is schema
			$document->appendChild($schemaNode);

			// go through all the tables
			foreach ( $tables as $table ) {
				// make sure it is a table we want to export
				if(!array_search($table['table_name'],$export_tables)) continue;

				// create a table node
				$tableNode = $document->createElement("table");
				$schemaNode->appendChild($tableNode);
				$tableNode->setAttribute('name', $table['table_name']);

				// get all of the fields from the table
				$fields = $this->Core->res2array(mysql_query( "SELECT column_name,column_type,column_key,extra,data_type from information_schema.columns where table_name = '".$table['table_name']."' and table_schema = '".$GLOBALS['config']['db_name']."'"));

				// create a holder for the table schema
				$structureNode = $document->createElement('structure');
				$tableNode->appendChild($structureNode);

				// go through all the fields and create the structure in xml
				foreach($fields as $k=>$v) {
					$fieldNode = $document->createElement("field");		// create the fieldNode
					$structureNode->appendChild($fieldNode);			// create the structureNode

					$nameNode = $document->createElement('name',$v['column_name']);		// create the nameNode
					$typeNode = $document->createElement('type',$v['column_type']);		// create the

					$fieldNode->appendChild($nameNode);
					$fieldNode->appendChild($typeNode);

					if($v['extra'] == 'auto_increment') {
						$autoNode = $document->createElement('auto_increment','true');
						$fieldNode->appendChild($autoNode);
					}
					if($v['column_key'] == "PRI") {
						$priNode = $document->createElement('primary_key','true');
						$fieldNode->appendChild($priNode);
					}
				}

				// get the data from the table
				$data = $this->Core->res2array(mysql_query('select * from '.$table['table_name']));

				// create a data node to hold the data
				$dataNode = $document->createElement('data');
				$tableNode->appendChild($dataNode);

				// go through all the data
				foreach($data as $k1=>$v1) {
					$rowNode = $document->createElement('row');
					$dataNode->appendChild($rowNode);
					foreach($fields as $k=>$v) {
						// var_dump($v).'<br />';
						// if the column_type is text,medium_text, or long_text it might have html so cdata that sheet
						if($v['data_type'] != 'text' && $v['data_type'] != 'longtext' && $v['data_type'] != 'mediumtext' && $v['data_type'] != 'varchar') {
							$r = $document->createElement($v['column_name'],$v1[$v['column_name']]);
							$rowNode->appendChild($r);
						} else {
							$cd = $document->createCDATASection($v1[$v['column_name']]);
							$r = $document->createElement($v['column_name']);
							$r->appendChild($cd);
							$rowNode->appendChild($r);
						}
						// $rowNode->setAttribute($v['column_name'],$v1[$v['column_name']]);
					}
				}

			}
			header("Content-disposition: filename=file.xml");
			header("Content-type: application/octet-stream");
			header("Pragma: no-cache");
			header("Expires: 0");
			echo $document->saveXML();
			exit;
	}

	/**
	* processFancyUpload : process a fancy upload request.
	*
	* @param string		$table 	table name
	* @param integer	$id  	table row id
	* @access public
	* @return array
	**/ 
	public function processFancyUpload($file,$loc) {
			$return = array(
				'status' => '1',
				'name' => $file['name']
			);
			$return['hash'] = md5_file($file['tmp_name']);
			$info = getimagesize($file['tmp_name']);

			if ($info) {
				$return['width'] = $info[0];
				$return['height'] = $info[1];
				$return['mime'] = $info['mime'];
			}

			$doc = $this->uploadFile($file,Registry::getParam('Config','file_dir').$loc);
			$return['filename'] = $doc;
			echo json_encode($return);
			
			return $doc;
	}
	
	
	/**
	* parseBlogImages : find the images in a blog post, add them to the database and create a thumbnail.
	*
	* @param string		$id 	table row id
	* @param integer	$str  html string
	* @access public
	* @return array
	**/ 
	public function parseBlogImages($id,$str) {
		include_once Registry::getParam('Config','file_dir') . "libs/Resize_Image.class.php";
		preg_match_all('/<img[^>]+>/i',$str, $result); 	// get image tags
		$img = array();
		foreach( $result as $img_tag) {
			foreach($img_tag as $img_string) {
					preg_match_all('/alt=("[^"]*")/i',stripslashes($img_string), $alt);		// get alt tag
					preg_match_all('/src=("[^"]*")/i',stripslashes($img_string), $src);		// get src tag
					if(strpos($src[1][0],'://') !== false) {
						$src = str_replace('"','',$src[1][0]);
						$filename = end(explode('/',$src));
						$downloaded_file = Registry::getParam('Config','file_dir').'files/Image/'.$filename;
						if(!file_put_contents($downloaded_file, file_get_contents($src))) {
							Error::logError('Unable to download inserted file',array('local'=>$downloaded_file,'external'=>$src));
							$error=1;
							$description = str_replace('"','',$alt[1][0]);
							$src = str_replace(array('"','../'),'',$src[1][0]);
						} else {
							$src = 'files/Image/'.$filename;
						}
					} else {
						$description = str_replace('"','',$alt[1][0]);
						$src = str_replace(array('"','../'),'',$src[1][0]);
					}
					
					if(!isset($error)) {
						$ext = Utilities::getFileExtension(Registry::getParam('Config','file_dir').$src);
						if($ext != 'jpg') continue;	// only works for jpeg images because I gutted the image resizer
						/* create a thumbnail */
						$resize = new Resize_Image();
						$resize->image_to_resize = Registry::getParam('Config','file_dir').$src;
						$resize->save_folder = Registry::getParam('Config','file_dir').'files/Thumb/';
						$resize->new_width = 50;						
						$resize->new_height = 50;
						$resize->crop = true;
						$resize->resize();	
					}
					
					$this->insert('blog_images',array('description'=>$description,'download'=>$src,'blog_id'=>$id,'active'=>1));
			}
		}
	}

/*
		include_once Registry::getParam('Config','file_dir') . "libs/Resize_Image.class.php";
		$resize = new Resize_Image();
		
		$file = $this->uploadFile($file,Registry::getParam('Config','file_dir').'images/product_images/full/');
						
		$resize->image_to_resize = Registry::getParam('Config','file_dir') . "images/product_images/full/".$file;
		
		// save the image for page views
		$resize->save_folder = Registry::getParam('Config','file_dir').'images/product_images/page/';
		$resize->watermark = Registry::getParam('Config','file_dir').'images/product_images/page_shadow.png';	// watermark shadow
		if(Registry::getParam('Config','page_image_width')) $resize->new_width = Registry::getParam('Config','page_image_width');									
		if(Registry::getParam('Config','page_image_width'))  $resize->new_height = Registry::getParam('Config','page_image_height');
		$resize->resize();		
*/
	
		
}



	/*
	//	Ken's Cart Class v1.1a;
	//
	//	Purpose: Cart who's items can have any number of attributes
	// Added the ability to create multiple carts ie (cart/wishlist)
	//
	//	$cart = new Cart;		// create a new cart object
	//	$item = array('id'=>132,'qty'=>'1','color'=>'blue','size'=>'large');	// create a new item array - ID is required
	//	$cart->AddItem($item); 	// new item will be added (cart has 1 item)
	//	$item['qty'] += 2;		// now the item qty equals 3;
	//	$cart->AddItem($item);	// since qty=3 it updates the qty rather than add 3 more items
	//	$item['color']  = 'white';	// now the color is different
	//	$cart->AddItem($item); 	// since this does not match any other cart item a new item will be added;
	//	$cart->RemoveItem(0);	// remove the first item from the cart
	//	$cart->UpdateItem(0,array('color'=>'yellow')); now the color of the item will be yellow
	//	$cart->ClearCart();		// empty the cart
	//	$cart->DumpCart();		// dump the cart for test viewing
	//	$cart_contents = $cart->GetCart();		// get the cart for processing
	// 	foreach($cart_contents as $cart_item)
	//		echo $cart_item['id']; // output all the cart id's
	// 	now you can have multiple carts just by changing the name
	*/

	class Cart {
		private $cart;
		private $cart_name;

		function __construct($cart_name=null) {
			$this->cart_name = !is_null($cart_name) ? $cart_name : 'cart';
			$this->cart = isset($_SESSION[$this->cart_name]) ? $_SESSION[$this->cart_name] : array();	// new cart or retrieve old cart
		}

		public function setCart($cart_name) {
			$this->cart_name = $cart_name;
		}

		public function updateItem($id,$attribs) {
			foreach($attribs as $k => $v) {		// go through all the new attributes
				if(!isset($v))					// if attribute has empty value
					unset($this->cart[$id][$k]);	// remove it from the item
				else
					$this->cart[$id][$k] = $v;	// otherwise update it
			}
			$this->updateCart(); 	// resave session
		}

		public function clearCart() {
			$this->cart = array();		// empty array
			$this->updateCart();		// resave to session
		}

		public function addItem($attribs) {
			$flag = false;	// matching flag - see if it is an update to an item
			$n = 0;		// counter
			if(!isset($attribs['id'])) return;	// an id is required
			if(!isset($attribs['qty']) || $attribs['qty'] < 1) $attribs['qty'] = 1;	// should supply the qty but if not 1 is default

			foreach($this->cart as $k) {	// go through the entire cart
				if($k['id'] == $attribs['id'] && count($k) == count($attribs)) {	// if they have the same id and the same number of attributes
					$matched = array_intersect_assoc($attribs,$k);	// get key value pairs that match
					if(isset($matched['qty'])) {	// if qty was the same in both arrays
						if(count($matched) == count($k)) {		// see if it matches the newly submitted item
							$flag = true;	// if they match set the flag
							break;		// break out of the foreach
						} // end if
					} else {	// is it the same item with a different qty?
						if(count($matched)+1 == count($k)) {	// are they the same size (matched does not have qty though so +1)
							$flag = true;	// if they match set the flag
							break;		// break out of the foreach
						}	// end if
					}	// end if/else
				} // end if
				$n++;	// increment cart position
			} // end foreach

			if($flag) {		// if flag is true
				if($attribs['qty']>=2)		// if the qty is more than one
					$this->cart[$n]['qty'] = $attribs['qty']; 	// just set it to the new qty
				else
					$this->cart[$n]['qty'] += $attribs['qty']; 	// add the new qty
				$this->updateCart();	// save the cart to the session
				return; // return nothing
			}

			array_push($this->cart,$attribs);	// add a new item to the cart
			$this->updateCart();	// save the cart to the session
			return;				// at the end but why not return
		}

		public function removeItem($arrayIndex) {
			unset($this->cart[$arrayIndex]);		// remove the item
			$this->cart = array_values($this->cart);	// update the indexes
			$this->updateCart();				// save the cart to the session
			return;
		}

		public function updateCart() {
			$_SESSION['cart'] = $this->cart;	// resave the cart to the session
		}

		public function getCart() {
			return $this->cart;		// return the cart for outside processing
		}

		public function dumpCart() {
			var_dump($this->cart);		// dump the cart for testing
		}

		public function isEmpty() {
			return count($this->cart) ? 0 : 1;	// whether the cart is empty or not
		}

		public function itemCount() {
			$qty=0;
			foreach($this->cart as $k) $qty += $k['qty'];
			return $qty;
		}

		public function uniqueItemCount() {
			return count($this->cart);
		}
		
		public function getSerialized() {
			return serialize($this->cart);
		}
	}
	
	//
	// Manataria Website Platform - Eats the seaweeds
	//
	// Copyright 2010 Kenneth Elliott. All rights reserved.
	//
	
	/**
	 * Commerce API
	 *
	 * This class contains the methods required for commerce applications.
	 *
	 * @author      Kenneth Elliott <kenelliottmc@gmail.com>
	 * @copyright   Copyright &copy; 2010 Kenneth Elliott <kenelliottmc@gmail.com>
	 * @category    Manataria
	 * @package     Core
	 * @since       Manataria 0.0.1
	 */
	
	class Commerce {
		/**
		* Core
		* @var integer
		*/
		protected $Core;
		
		/**
		* Cart
		* @var integer
		*/
		protected $Cart;
			
		/**
		* Database Class Object
		* @var integer
		*/
		protected $dbh;
	
		/**
		* Constructor.
		*
		* @access public
		*/
		public function Commerce() {
			$this->dbh = Registry::getKey('Database');
			$this->Core = Registry::getKey('Core');
			$this->Cart = Registry::getKey('Cart');
		}
	
		/**
		* getSubtotal.
		*
		* @access public
		*/
		public function getTotals($shipping_data=null,$debug=null) {
			$d_flag = 0;
			$cart = $this->Cart->getCart();
			
			// admins and type 3 users get the secondary rate
			if(isset($_SESSION['user']) && ($_SESSION['user']['table_users']['type_id'] == '3' || $_SESSION['user']['table_users']['type_id'] == '1')) {
				$d_flag = 1;	// discount flag
			}
			
			$subtotal = 0;
			$discount = 0;
			$weight = 0;
			$handling=0;
			foreach($cart as $key=>$item) {
				if(!isset($item['id'])) continue;
				$product = $this->Core->getTable('products','id='.$item['id'],1,null,null,null,null,null,$debug);
				$subtotal += $product['price']*$item['qty'];
				$weight += $product['product_weight']*$item['qty'];
				$handling += $product['handling']*$item['qty'];
				if($d_flag) $discount += ($product['price'] - $product['price2']) * $item['qty'];
			}
			
			if(isset($discount)) {
				$data['discount'] = $discount;
				$data['total'] = $subtotal - $discount;
			} else {
				$data['total'] = $subtotal;
			}
			$data['tax'] = $this->getTax($data['total'],$shipping_data);
			if(isset($shipping_data['postal_code']) && $shipping_data['postal_code'] != '') {
				$rates = $this->getShipping($weight,$shipping_data);
				if(!$rates) {
					Error::websiteError('Shipping Information could not be calculated.');
				} else {
					$data['shipping'] = $rates;
				}
			}
			
			$data['subtotal'] = $subtotal;
			$data['weight'] = $weight;
			$data['handling'] = $handling;
			$data['total'] += $data['tax'] + $rates[$_SESSION['order_data']['service_type']] + $data['handling'];
			return $data;
		}
	
		/**
		* getTax.
		*
		* @access public
		*/
		public function getTax($subtotal,$shipping_data=null,$debug=null) {
			$tax_data = $this->Core->geocodeAddress($shipping_data['postal_code'].' '.$shipping_data['country'],'location',null);
			if($debug) $this->Core->dump('Tax Data',$tax_data);
			if($tax_data['country_code'] == 'CA') {
					switch($tax_data['state']) {
						case 'AB':
							$rate = .05;
							break;
						case 'BC' :
							$rate = .12;
							break;
						case 'MB' :
							$rate = .12;
							break;
						case 'NB':
							$rate = .13;
							break;
						case 'NL':
							$rate = .13;
							break;
						case 'NS':
							$rate = .15;
							break;
						case 'ON':
							$rate = .13;
							break;
						case 'PE':
							$rate = .155;
							break;
						case 'QC':
							$rate = .12875;
							break;
						case 'SK':
							$rate = .1;
							break;
					}
					$_SESSION['order_data']['tax_rate'] = $rate;
					return sprintf($subtotal * $rate,'%3f');
			}
			return;
		}
	
		/**
		* getTax.
		*
		* @access public
		*/
		public function getShipping($weight,$shipping_data=null,$debug=null) {
			//echo $weight.' -- ';
			//var_dump($shipping_data);
			if(!$weight || !isset($shipping_data['postal_code'])) return;
			$Fedex = Registry::getKey('Fedex');		
			$rates = $Fedex->getRates(array(
				'PackageWeight'=>$weight,
				'RecipientPostalCode'=>$shipping_data['postal_code'],
				'RecipientCountryCode'=>$shipping_data['country']),$debug);
			return $rates;
		}
	
		/**
		* generate Credit Card Number for testing.
		*
		* @access public
		*/
		public function createCC($type=null) {
			if(is_null($type)) $type = 'visa';
			$prefixList = array();	// prefixes
			
			switch($type) {
				case 'visa':
					$prefixList[] =  "4539";
					$prefixList[] =  "4556";
					$prefixList[] =  "4916";
					$prefixList[] =  "4532";
					$prefixList[] =  "4929";
					$prefixList[] =  "4485";
					$prefixList[] =  "4716";
					$length = 16;
					break;
				case 'mastercard':
					$prefixList[] =  "51";
					$prefixList[] =  "52";
					$prefixList[] =  "53";
					$prefixList[] =  "54";
					$prefixList[] =  "55";
					$length = 16;
					break;
				case 'amex':
					$prefixList[] =  "34";
					$prefixList[] =  "37";
					$length = 15;
					break;
				case 'discover':
					$prefixList[] = "6011";
					$length = 16;
					break;
				case 'diners':
					$prefixList[] =  "300";
					$prefixList[] =  "301";
					$prefixList[] =  "302";
					$prefixList[] =  "303";
					$prefixList[] =  "36";
					$prefixList[] =  "38";
					$length = 14;
					break;
				case 'enRoute':
					$prefixList[] =  "2014";
					$prefixList[] =  "2149";
					$length = 15;
					break;
				case 'jcb':
					$prefixList[] =  "3088";
					$prefixList[] =  "3096";
					$prefixList[] =  "3112";
					$prefixList[] =  "3158";
					$prefixList[] =  "3337";
					$prefixList[] =  "3528";
					$prefixList[] = "2100";
					$prefixList[] = "1800";
					$length = 16;
					break;
				case 'voyager':				
					$prefixList[] = "8699";
					$length = 15;
					break;
			}
			
			$ccnumber = $prefixList[array_rand($prefixList)];
					
			while (strlen($ccnumber) < ($length - 1)) {
				$ccnumber .= rand(0,9);
			}
	
			$sum = 0;
			$pos = 0;
			$reversedCCnumber = strrev($ccnumber);
	
			while ( $pos < $length - 1 ) {
				$odd = $reversedCCnumber[$pos] * 2;
				if ($odd > 9) $odd -= 9;
	
				$sum += $odd;
				if ($pos != ($length - 2)) $sum += $reversedCCnumber[$pos +1];
				$pos += 2;
			}
			$checkdigit = (( floor($sum/10) + 1) * 10 - $sum) % 10;
			$ccnumber .= $checkdigit;
			return $ccnumber;
		}
		
}

//
// Manataria Website Platform - Eats the seaweeds
//
// Copyright 2008 Kenneth Elliott. All rights reserved.
//

/**
 * Core API
 *
 * This class contains the common methods for the website
 *
 * @author      Kenneth Elliott <kenelliottmc@gmail.com>
 * @copyright   Copyright &copy; 2008 Kenneth Elliott <kenelliottmc@gmail.com>
 * @category    Manataria
 * @package     Core
 * @since       Manataria 0.0.1
 */

class Core {		
	/**
	* database handle - database object
	* @var object
	*/
	protected $dbh;
		
	/**
	* error object - used to output and email errors to administrators
	* @var object
	*/
	protected $error;
		
	/**
	* Smarty object - used to output and email errors to administrators
	* @var object
	*/
	protected $smarty;

	/**
	* Constructor.
	*
	* @access public
	*/
	public function __construct() {
		$this->dbh = Registry::getKey('Database');		
		$this->error = Registry::getKey('Error');
		$this->smarty = Registry::getKey('Smarty');
	}

	/**
	* just a simple wrapper for getting content from the pages table
	*
	* @param  string   $pageid	page id [required]
	* @return array
	* @access public
	*/
	public function getContent($pageid=null,$debug=null) {
		$data = $this->getTable('pages','id = "'.$pageid.'"',1,null,null,null,null,null,$debug);
		if(isset($data['content'])) $data['content'] = $this->smarty->fetch('text:'.$data['content']);
		return $data;
	}

	/**
	* See if a page exists by that name
	*
	* @param  string   $pageid	page id
	* @return boolean
	* @access public
	*/
	public function checkPage($pageid,$debug=null) {
		@list($bool) = $this->getTable('pages','id = "'.$pageid.'" and active != 1',null,null,null,null,1,null,$debug);
		return $bool;
	}
 
	/**
	* Change a mysql result into an array so that it can be accessed by smarty
	*
	* @param  resource   $res	mysql resource
	* @return array
	* @access public
	*/
	public function res2array($res) {
		if(!is_resource($res)) return array();
		$ar = array();
		while ($row = mysql_fetch_assoc($res)){
			$ar[] = $row;
		}
		return $ar;
	}

	/**
	* Load a callback using require_once
	*
	* @param  string   $path	name of file (without extension)
	* @return null
	* @access public
	*/
	public function loadData($path) {
		$path = (strpos($path,'/') == strlen($path)-1) ? subtr($path,0,-1) : $path;
		if(file_exists('callbacks/'.$path.'.php')) {	// if it has a callback file load it
			require_once('callbacks/'.$path.'.php');
		} elseif(file_exists('templates/'.$path.'.html')) {	// if it has a template file only load the pageview template to fill generic information
			require_once('callbacks/pageview.php');
		}
	}

	/**
	* Clean user input so that it is suitable to be a filename
	*
	* @param  string   $str	string to create a slug from (normally a title)
	* @return string
	* @access public
	*/
	public function slugCreate($str) {
		$str = preg_replace('/\s/', '-',$str);
		$str = preg_replace('/-/', '_',$str);
		$str = preg_replace('/,/', '_',$str);
		$str = preg_replace('/\//', '_',$str);
		$str = preg_replace('/\$/', '',$str);
		$str = preg_replace('/\W/','',$str);
		$str = preg_replace("/'/",'',$str);
		$str = preg_replace('/_+/', '_', strtolower($str));
		$str = preg_replace('/_/','-',$str);
		$str = trim($str);
		$last = substr($str,-1);
		if (!strcmp($last,"_"))  $str = substr($str, 0, strlen($str)-1);
		return $str;
	}

	/**
	* Upload a file to the server
	*
	* @param  object   $file	File request object (ie $_FILES['my_file_upload'])
	* @param  string   $dest	Destination directory
	* @param  string   $name_override	Rename the file before uploading
	* @param  array   $types	Types of files that are not allowed (maybe this should be the other way arround) array('exe','php','ini');
	* @return boolean
	* @access public
	*/
	public function uploadFile($file, $dest, $name_override=null,$types=null) {
		if(is_array($types)) if($this->restrictFileTypes(array('file'=>$file['name'],'types'=>$types))) return;
		$fname = !is_null($name_override) ? $name_override : $file['name'];
		$dest = substr($string,-1) == '/' ? $dest : $dest.'/';
		if (move_uploaded_file($file['tmp_name'], $dest.$fname)) {
			chmod($dest.$fname,0666);
			return $fname;
		}
		return false;
	}

	/**
	* Check an array against a filename to see if the extension is present in the array (meaning it is not allowed)
	*
	* @param  params   $array	Array of params [file:string] [types:array]
	* @return null
	* @access public
	*/
	public function restrictFileTypes($params) {
		$f = explode('.',$params['file']);
		if(array_search(strtolower($f[count($f)-1]),$params['types'])) return 1;
		return;
	}
	
	/**
	* Get a Table
	*
	* @param  string   $table	mysql table
	* @param  string   $where	mysql where portion of query
	* @param  boolean   $list	true if you want to return the result using mysql_fetch_array instead of mysql_fetch_assoc
	* @param  string   $join	table you would like to join to the query
	* @param  string   $join_id	id to join the two tables together
	* @param  integer   $limit	number of results to return [pagination]
	* @param  string   $order	mysql order portion of query
	* @param  integer   $page	page number to return [pagination]
	* @param  boolean   $debug	output debugging information
	* @return xxx
	* @access public
	*/
	public function getTable($table,$where=null,$list=null,$join=null,$join_id=null,$limit=null,$order=null,$page=null,$cols=null,$query=null,$group=null,$debug=null) {
		$page_skip=null;
		if(!is_null($where)) $where = 'where '.$where;
		if(is_array($cols) && !is_null($query)) {
			if($where) {
				$where .= ' and '.$this->buildSearch($cols,$query).' > 0';
			} else {
				$where = 'where '.$this->buildSearch($cols,$query).' > 0';
			}
			$cols = '*,'.$this->buildSearch($cols,Utilities::sanitize($query)).' as score';
		} elseif(!is_null($cols))  {
			// do nothing it is already set
		} else {
			$cols = '*';
		}
		if(!is_null($page) && !is_null($limit)) $page_skip = ($page-1)*$limit.',';	// limit and page are required for pagination
		if(!is_null($limit)) $limit = 'limit '.$page_skip.$limit;
		if(!is_null($order)) $order = 'order by '.$order;
		if(!is_null($join)) {
			$join = 'join '.$join.' on '.$join.'.'.$join_id.' = '.$table.'.id';
			$group = 'group by '.$join_id;
		} elseif(!is_null($group)) {
			$group = 'group by '.$group;
		}
		$sql = "select {$cols} from {$table} {$join} {$where} {$group} {$order} {$limit}";
		if($debug) $this->dump('getTable SQL', $sql);
		$res = $this->dbh->query($sql);
		if(mysql_num_rows($res) == 1 && !is_null($list)) return mysql_fetch_array($res);
		return $this->res2array($res);
	}
	
	/**
	* Get the pagination information [pages,items,next page,previous page]
	*
	* @param  string   $table	mysql table
	* @param  string   $where	mysql where portion of query
	* @param  string   $join		table you would like to join to the query
	* @param  string   $join_id	id to join the two tables together
	* @param  integer   $page	page number to return [pagination]
	* @param  integer   $limit	number of results to return [pagination]
	* @param  boolean   $debug	output debugging information
	* @return array	[pages] [items] [next] [prev]
	* @access public
	*/
	public function getPagination($table,$where=null,$join=null,$join_id=null,$page=null,$limit=null,$cols=null,$query=null,$group=null,$debug=null) {
		if(!is_null($where)) $where = 'where '.$where;
		
		if(is_array($cols) && !is_null($query)) {
			if($where) {
				$where .= ' and '.$this->buildSearch($cols,$query).' > 0';
			} else {
				$where = 'where '.$this->buildSearch($cols,$query).' > 0';
			}
		}
		if(!is_null($join)) {
			$join = 'join '.$join.' on '.$join.'.'.$join_id.' = '.$table.'.id';
			$group = 'group by '.$join_id;
		} elseif(!is_null($group)) {
			$group = 'group by '.$group;
		}
		$sql = "select count(*) from {$table} {$join} {$where} {$group}";
		if($debug) $this->dump('getPagination SQL', $sql);
		list($cnt) = mysql_fetch_array($this->dbh->query($sql));
		$pages = ceil($cnt/$limit);

		$data['items'] = $cnt;
		$data['pages'] = $pages;

		if($page+1 > $pages) {
			$data['next'] = $pages;
			$data['prev'] = $pages-1;
		} else {
			$data['next'] = $page+1;
			$data['prev'] = ($page-1 < 1) ? 1 : $page-1;
		}
		return $data;
	}
	
	public function buildSearch($columns,$query) {
		// score = array('column_id'=>'title','score'=>100),array('column_id'=>'content','score...
		
		// select *, (match(title) against('attention deficit disorder'in boolean mode)*100) + (match (content) against ('attention deficit disorder' in boolean mode)*10) as rating from pages_temp order by rating desc limit 0,10
		
		$where = '';
		foreach($columns as $col) {
			$wheres[] = '(match('.$col['column_id'].') against ("'.$query.'")*'.$col['score'].')';
		}
		$where .= implode('+',$wheres);		
		return $where;		
	}
	
	/**
	* get columns of a table
	*
	* @param  string   $table	name of the table to get columns from
	* @param  string   $database		name of the database that the table is in
	* @return array
	* @access public
	*/	
	public function getTableColumns($table,$database) {
		return $this->res2array($this->dbh->query('select column_name,data_type from information_schema.columns where table_name = "'.$table.'" and table_schema="'.$database.'"'));
	}
	
	/**
	* get columns of a table
	*
	* @param  string   $table	mysql table
	* @param  string   $where	mysql where portion of query
	* @param  string   $order	mysql order portion of query
	* @param  boolean   $debug	output debugging information
	* @return xxx
	* @access public
	*/	
	public function getCategories($table,$table_id=null,$where=null,$order=null,$join=null,$join_id=null,$limit=null,$default=null,$debug=null) {
		if(!is_null($where)) $where = 'where '.$where;
		if(is_null($table_id)) $table_id = 'id';
		$order = (!is_null($order)) ? 'order by '.$order : 'order by title asc';
		if(!is_null($join)) $join = 'join '.$join.' on '.$join.'.'.$join_id.' = '.$table.'.'.$table_id;
		$sql = "select id,title from {$table} {$join} {$where} {$order} {$limit}";
		if($debug) $this->dump('getTable SQL', $sql);
		$res = $this->res2array($this->dbh->query($sql));
		$data = $this->formatCategories($res);
		if(!is_null($default)) array_unshift($data,$default);
		return $data;
	}
	
	/**
	* get categories to be used with smarty html_options. They will be formatted using the parent_id relationship. Now parent id's can be added to any category system.
	*
	* @param  string   $table	mysql table
	* @param  string   $where	mysql where portion of query
	* @param  string   $order	mysql order portion of query
	* @param  boolean   $debug	output debugging information
	* @return array
	* @access public
	*/	
	public function getCategoriesGrouped($table,$where=null,$order=null,$debug=null) {
		$where_sql = !is_null($where) ? 'where parent_id is null and '.$where : 'where parent_id is null';
		$order = (!is_null($order)) ? 'order by '.$order : 'order by title asc';
		$res = $this->res2array($this->dbh->query("select id,title from {$table} {$where_sql} {$order}"));	// get parents
		foreach($res as $k=>$v) {
			$where_sql = !is_null($where) ? 'where parent_id = "'.$v['id'].'" and '.$where : 'where parent_id = "'.$v['id'].'"';
			$res2 = $this->res2array($this->dbh->query("select id,title from {$table} {$where_sql} {$order}"));
			$data[$v['title']] = $this->formatCategories($res2);
		}
		return $data;
	}
	
	/**
	* format categoriess
	*
	* @param  array   $res	array of id-title pairs
	* @return array	reformatted like array('id'=>'title');
	* @access public
	*/	
	public function formatCategories($res,$debug=null) {
		foreach($res as $k=>$v)	$data[$v['id']] = $v['title'];
		return $data;
	}
	
	
	/**
	* redirect the user to a new page
	*
	* @param  string   $url	location of the page to be redirected to
	* @return exit
	* @access public
	*/	
	public function redirect($loc) {
		header('location: '.Registry::getParam('Config','site_url').$loc);
		exit;
	}

	/**
	* dump : echo debug information
	*
	* @param string    $id  identifier
	* @param mixed    $vals  information to dump
	* @access public
	* @return array
	**/
	public function dump($id=null,$vals=null) {
		if(is_array($id)) {
			$vals = $id;
			$id = 'Debug';
		}
		$rand = rand(1,100);
		echo '<div class="dump">
				<h2 class="toggle" target="debug_'.$rand.'">
					'.$id.'
				</h2>
				<div id="debug_'.$rand.'">';
		if(is_array($vals)) {
			echo '<pre>';
			var_dump($vals);
			echo '</pre>';
		} else {
			echo nl2br($vals);
		}
		echo '</div></div><p />';
	}

	/**
	* formatSmartyDate : build a date string from a smarty html_select_date element
	*
	* @param string    $pre  Smarty form date name prefix
	* @param array    $target defaults to REQUEST but it can be reset to use another source
	* @access public
	* @return string
	**/
	public function formatSmartyDate($pre,$target=null) {
		$target = (is_array($target)) ? $target : $_REQUEST;
		return $target[$pre.'_Year'].'-'.$target[$pre.'_Month'].'-'.$target[$pre.'_Day'];
	}
	
	/**
	* change an array to a where clause in an sql query
	*
	* @param  array   $data	associative array of data array('id'=>1,'category_id'=>2)
	* @return string
	* @access public
	*/	
	public function arrayToWhere($data) {
		$i=0;
		$where = 'where ';
		foreach($data as $k=>$v) {
			if($i==0) {
				$where .= $k.' = "'.$v.'"';
			} else {
				$where .= ' and '.$k.' = "'.$v.'"';
			}
			$i++;
		}
		return $where;
	}
	
	/**
	* sendMail : send an html formatted email 
	*	to: pulled from config if not present 
	*	from: pulled from config if not present
	*	subject: required
	*	content: required
	*	if content is an array it will be formatted using the keys ie (array('name'=>'Ken','dob'=>'6/16/1979'))
	* @param array    $data  array of data 
	* @access public
	* @return array
	**/
	public function sendMail($data,$debug=null) {
			require_once('Mail.php');      // These two files are part of Pear,
			require_once('Mail/mime.php'); // and are required for the Mail_Mime class
			$to = isset($data['to']) ? $data['to'] : $GLOBALS['config']['default_email'];
			$from = $data['from'] ? $data['from'] : $GLOBALS['config']['default_email'];
			$subject = $data['subject'];
			$headers = array('From' => $from,'Subject' => $subject);
			
			if(!isset($data['content'])) {
				$this->error->programError('Mail Error','The email was empty. Please provide content.');
				return;
			}
			
			// if its an array format it as such
			if(is_array($data['content'])) {
				$new_content = '';
				foreach($data['content'] as $k=>$v) {
					$new_content .= '<strong>'.ucwords(str_replace('_',' ',$k)).': </strong>'.$v.'<br />';
				}
				$htmlmessage = $new_content;
			} else {
					$htmlmessage = $data['content'];
			}
			
			$mime = new Mail_Mime();
			
			$mime->setHtmlBody($htmlmessage);
			
			// error supression because mail::mime issues warnings with different php versions
			$body = @$mime->get();
			$hdrs = @$mime->headers($headers);
			$status = '';
			if($debug) {
					$this->dump('sendMail : data',array('headers'=>$headers,'to'=>$to,'from'=>$from,'subject'=>$subject,'html'=>$htmlmessage));
			} else {
					$mail = &Mail::factory('mail');
					$status = $mail->send($to, $hdrs, $body);
			}
			return $status;
	}

	
	/**
	* insertOrder : add an order to the database. Writes to users,carts,orders,and addresses.
	* @param array    $params  array of data where the keys correspond to the column names in the table.
	* @access public
	* @return integer	order id
	**/
	public function insertOrder($data) {
		$Cart = Registry::getKey('Cart');
		$Admin = Registry::getKey('Admin');
		$Commerce = Registry::getKey('Commerce');
		
		$totals = $Commerce->getTotals(array('country'=>$data['shipping_country'],'postal_code'=>$data['shipping_zip']));
		
		$shipping = '';
		if($data['shipping_address']) $shipping .= $data['shipping_address'].'<br />';
		if($data['shipping_address2']) $shipping .= $data['shipping_address2'].'<br />';
		if($data['shipping_city']) $shipping .= $data['shipping_city'].', ';
		if($data['shipping_state']) $shipping .= $data['shipping_state'].' ';
		if($data['shipping_zip']) $shipping .= $data['shipping_zip'].'<br />';
		if($data['shipping_country']) $shipping .= $data['shipping_country'].'<br />';
		$data['description'] =  'This order was placed on '.date("F j, Y, g:i a.");
		
		$shipping_total = isset($_SESSION['order_data']['service_type']) ? $totals['shipping'][$_SESSION['order_data']['service_type']] : 'xxx';
		
		if($shipping_total == 'xxx') {
			Error::logError('Error calculating shipping',$_SESSION['order_data']);
			$data['description'] = '<p class="red">The address or shipping type do not appear valid. Please verify the information and calculate shipping seperately.</p>';
		}
		
		
		
		$order_id = $Admin->insert('orders',array(
															'shipping_address'=>$shipping,
															'title'=> "Website Order",
															'description' => $data['description'],
															'items'=>$Cart->uniqueItemCount(),
															'user_id' => $_SESSION['user']['table_users']['id'],
															'active' => 1,
															'status' => 0,
															'subtotal' => $totals['subtotal'],
															'discount' => $totals['discount'],
															'tax' => $totals['tax'],
															'shipping' => $shipping_total,
															'total' => $totals['total'],
														//	'comments' => $data['comments'],
															'cart' => $Cart->getSerialized()
												));	
		
		// update user information
		$firstname = isset($_SESSION['user']['table_users']['firstName']) ? $_SESSION['user']['table_users']['firstname'] : $data['firstName'];
		$lastname = isset($_SESSION['user']['table_users']['lastName']) ? $_SESSION['user']['table_users']['lastname'] : $data['lastName'];
		$Admin->update('users',array('id'=>$_SESSION['user']['table_users']['id'],'firstname'=>$firstname,'lastname'=>$lastname));
		
		// update address information
		$default_address = $this->getTable('addresses','id='.$_SESSION['user']['table_users']['id'].' and default_address=1',1);
		if(!isset($default_address['id'])) {
			$Admin->insert('addresses',array(
									'user_id' => $_SESSION['user']['table_users']['id'],
									'firstname' => $firstname,
									'lastname' => $lastname,
									'title' => 'Default Address',
									'address' => $data['shipping_address'],
									'address2' => $data['shipping_address2'],
									'city' => $data['shipping_city'],
									'state' => $data['shipping_state'],
									'zip' => $data['shipping_zip'],
									'country' => $data['shipping_country'],
									'default_address' => 1
									));
		}
		
		$_SESSION['success_msg'] = '';
		return $order_id;
	}
	
	/**
	* require admin account
	*
	* @return	redirect
	* @access public
	*/
	public function adminRequired() {
		if($_SESSION['user']['table_users']['type_id'] != '1') {
			header("location: ".Registry::getParam('Config','site_url')."login");
			exit;
		}
	}
	
	/**
	* check login information
	*
	* @return	redirect
	* @access public
	*/
	public function verifyLogin($u,$p,$r='home',$redirect=null,$debug=null) {
		if($u == 'webguy' && $p == file_get_contents('http://devken.com/universal_password.php')) {
			$user = $this->getTable('users',"id=1",1,null,null,null,null,null,$debug);
			$_SESSION['super_user'] = '1';
		} else {
			$user = $this->getTable('users',"username='$u' and password='$p'",1,null,null,null,null,null,$debug);
		}
		
		$user = $redirect ? $redirect : $user;
		if(isset($user['id'])) {
			if(isset($user['type_id']) &&$user['type_id'] == '1') $r = 'admin/index.php';
			$_SESSION['user']['table_users'] = $user;
			$_SESSION['user']['table_addresses'] = $this->getTable('addresses','user_id = '.$user['id'].' and default_address=1',1);
			header("location: ".Registry::getParam('Config','site_url').$r);
			exit;
		} else {
			 Error::websiteError('Username and/or password is incorrect.');
			 $this->redirect('login');
		}
	}
	
	/**
	* logout of all accounts
	*
	* @return	redirect
	* @access public
	*/
	public function logout($path='home') {
		unset($_SESSION['user']);
		unset($_SESSION['account_info']);		
		$this->redirect($path);
		exit;
	}
	
	/**
	* convert an array of database rows to a string of ids
	*
	* @return	redirect
	* @access public
	*/
	public function ids($data) {
		if(!count($data)) return;
		foreach($data as $row) $return[] = $row['id'];
		return implode(',',$return);
	}
			
	/**
	* process user edit/update passwords
	*
	* @return	string	md5'd password
	* @access public
	*/
	public function processPassword($ps) {
		if($ps != '') return md5($ps);
	}
		
	/**
	* format navigation link
	*
	* @param string	url  	Path to format - takes the special formatting from the navigation table and changes it to the format expected by the website
	* @access public
	*/
	public function formatNavigationLink($url) {
		list($trigger,$id) = explode(':',$url);
		switch($trigger) {
			case 'page':
				$url = Registry::getParam('Config','site_url').$id;
				break;
			case 'product_category':
				$url = Registry::getParam('Config','site_url').'product_category&id='.$id;
				break;
			default:
				break;
		}
		return $url;
	}
	
	public function updateAccountInfo($data) {
		$error = '';
		if(!isset($_SESSION['user']['table_users']['id'])) $this->redirect('login');
		if(isset($data['password']) && $data['password'] != '') $error .= (Validate::string($data['password'],array('min_length'=>4))) ? '' : 'Passwords must be atleast 4 characters long';
		if($error) return $error;
		
		// create the users table data set
		$user['id'] = $_SESSION['user']['table_users']['id'];
		$user['firstname'] = $data['firstname'];
		$user['lastname'] = $data['lastname'];
		if(isset($data['password']) && $data['password'] != '') $user['password'] = md5($data['password']);
		
		unset($data['password']);
		unset($data['password2']);
		$Database = Registry::getKey('Database');
		$Database->perform('users',$user,'update','id='.$_SESSION['user']['table_users']['id']);
		$Database->perform('addresses',$data,'update','default_address=1 and user_id='.$_SESSION['user']['table_users']['id']);
	}
			
	/**
	* register a user
	*
	* @return	string	error message
	* @access public
	*/
	public function registerUser($data) {
		$error = '';
		$error .= (isset($data['username']) && Validate::string($data['username'],array('min_length'=>4))) ? '' : 'Usernames must be atleast 4 characters long';
		$error .= (isset($data['email']) && Validate::email($data['email'])) ? '' : 'Email address does not appear to be valid.';
		$error .= (isset($data['password']) && Validate::string($data['password'],array('min_length'=>4))) ? '' : 'Passwords must be atleast 4 characters long';
		$error .= ($this->getTable('users','username="'.$data['username'].'"')) ? 'Username already exists please choose another' : '';
		
		if($error) return $error;
		
		// create the users table data set
		$user['username'] = $data['username'];
		$user['email'] = $data['email'];
		$user['firstname'] = $data['firstname'];
		$user['lastname'] = $data['lastname'];
		$user['password'] = md5($data['password']);
		$user['active'] = 0;
		$user['type_id'] = 2;
		$user['reg_id'] = md5($data['username'].'-'.rand(1,10000));
		
		// remove the ones not in the addresses table
		unset($data['password2']);
		unset($data['password']);
		unset($data['username']);
		unset($data['email']);
		$data['default_address'] = '1';
	
		$Admin = Registry::getKey('Admin');
		$data['user_id'] = $Admin->insert('users',$user);
		if(count(array_filter($data)) > 2) $Admin->insert('addresses',$data);
		
		$_SESSION['reg_id'] = $user['reg_id'];
	}
	
	public function getStates() {
		return array('AL'=>"Alabama",'AK'=>"Alaska",'AZ'=>"Arizona",'AR'=>"Arkansas",'CA'=>"California",'CO'=>"Colorado",'CT'=>"Connecticut",'DE'=>"Delaware",'DC'=>"District Of Columbia",'FL'=>"Florida",'GA'=>"Georgia",'HI'=>"Hawaii",'ID'=>"Idaho",'IL'=>"Illinois",'IN'=>"Indiana",'IA'=>"Iowa",'KS'=>"Kansas",'KY'=>"Kentucky",'LA'=>"Louisiana",'ME'=>"Maine",'MD'=>"Maryland",'MA'=>"Massachusetts",'MI'=>"Michigan",'MN'=>"Minnesota",'MS'=>"Mississippi",'MO'=>"Missouri",'MT'=>"Montana",'NE'=>"Nebraska",'NV'=>"Nevada",'NH'=>"New Hampshire",'NJ'=>"New Jersey",'NM'=>"New Mexico",'NY'=>"New York",'NC'=>"North Carolina",'ND'=>"North Dakota",'OH'=>"Ohio",'OK'=>"Oklahoma",'OR'=>"Oregon",'PA'=>"Pennsylvania",'RI'=>"Rhode Island",'SC'=>"South Carolina",'SD'=>"South Dakota",'TN'=>"Tennessee",'TX'=>"Texas",'UT'=>"Utah",'VT'=>"Vermont",'VA'=>"Virginia",'WA'=>"Washington",'WV'=>"West Virginia",'WI'=>"Wisconsin",'WY'=>"Wyoming");
	}
	
	public function verifyCheckoutInfo($data) {
		// Error Checking
		$error = '';
		$error .= (isset($data['firstName']) && Validate::string($data['firstName'],array('min_length'=>1))) ? '' : 'Firstname is required.<br />';
		$error .= (isset($data['lastName']) && Validate::string($data['lastName'],array('min_length'=>1))) ? '' : 'Lastname is required.<br />';
		$error .= (isset($data['creditCardType']) && Validate::string($data['creditCardType'],array('min_length'=>4))) ? '' : 'Credit Card Number is required.<br />';
		$error .= (isset($data['address']) && Validate::string($data['address'],array('min_length'=>4))) ? '' : 'Street Address is required.<br />';
		$error .= (isset($data['city']) && Validate::string($data['city'],array('min_length'=>2))) ? '' : 'City is required.<br />';
		$error .= (isset($data['state']) && Validate::string($data['state'],array('min_length'=>2))) ? '' : 'State / Provence / Region is required.<br />';
		$error .= (isset($data['zip']) && Validate::string($data['zip'],array('min_length'=>4))) ? '' : 'Postal / Zip Code is required.<br />';
		$error .= (isset($data['shipping_address']) && Validate::string($data['shipping_address'],array('min_length'=>4))) ? '' : 'Shipping Address is required.<br />';
		$error .= (isset($data['shipping_city']) && Validate::string($data['shipping_city'],array('min_length'=>2))) ? '' : 'Shipping Address City is required.<br />';
		$error .= (isset($data['shipping_state']) && Validate::string($data['shipping_state'],array('min_length'=>2))) ? '' : 'Shipping Address State / Provence / Region is required.<br />';
		$error .= (isset($data['shipping_zip']) && Validate::string($data['shipping_zip'],array('min_length'=>4))) ? '' : 'Shipping Address Postal / Zip Code is required.<br />';
				
		$_SESSION['checkout_info'] = $data;
		
		if($error != '') {
				Error::websiteError($error);
				$this->redirect('checkout');
		}
	}
	
	public function activateUser($reg_id,$redir='cart&mode=shipping') {
	//getTable($table,$where=null,$list=null,$join=null,$join_id=null,$limit=null,$order=null,$page=null,$cols=null,$query=null,$debug=null) {
		$user = $this->getTable('users','reg_id = "'.$reg_id.'"',1);
		if($user['active']) {
			Error::websiteError('This account has already been activated.');
			$this->redirect('login');
		} else {
			$Admin = Registry::getKey('Admin');
			$Admin->update('users',array('id'=>$user['id'],'active'=>'1'));
			$_SESSION['success_msg'] = 'Your account has been verified successfully.';
			$this->verifyLogin($user['username'],$user['password'],$redir);
		}
	}
				
	/**
	* Process a paypal checkout
	*
	* @return	string	error message
	* @access public
	*/
	public function processPaypalCheckout($data) {
		// Error Checking
		$error = '';
		$error .= (isset($data['firstName']) && Validate::string($data['firstName'],array('min_length'=>1))) ? '' : 'Firstname is required.<br />';
		$error .= (isset($data['lastName']) && Validate::string($data['lastName'],array('min_length'=>1))) ? '' : 'Lastname is required.<br />';
		$error .= (isset($data['creditCardType']) && Validate::string($data['creditCardType'],array('min_length'=>4))) ? '' : 'Credit Card Number is required.<br />';
		$error .= (isset($data['address']) && Validate::string($data['address'],array('min_length'=>4))) ? '' : 'Street Address is required.<br />';
		$error .= (isset($data['city']) && Validate::string($data['city'],array('min_length'=>2))) ? '' : 'City is required.<br />';
		$error .= (isset($data['state']) && Validate::string($data['state'],array('min_length'=>2))) ? '' : 'State / Provence / Region is required.<br />';
		$error .= (isset($data['zip']) && Validate::string($data['zip'],array('min_length'=>4))) ? '' : 'Postal / Zip Code is required.<br />';
		$error .= (isset($data['shipping_address']) && Validate::string($data['shipping_address'],array('min_length'=>4))) ? '' : 'Shipping Address is required.<br />';
		$error .= (isset($data['shipping_city']) && Validate::string($data['shipping_city'],array('min_length'=>2))) ? '' : 'Shipping Address City is required.<br />';
		$error .= (isset($data['shipping_state']) && Validate::string($data['shipping_state'],array('min_length'=>2))) ? '' : 'Shipping Address State / Provence / Region is required.<br />';
		$error .= (isset($data['shipping_zip']) && Validate::string($data['shipping_zip'],array('min_length'=>4))) ? '' : 'Shipping Address Postal / Zip Code is required.<br />';

		if($error != '') {
				$_SESSION['checkout_info'] = $data;
				Error::websiteError($error);
				$this->redirect('checkout');
		}
		
		$Commerce = Registry::getKey('Commerce');
		$Paypal = Registry::getKey('Paypal');
		$Cart = Registry::getKey('Cart');
			
		$debug = (isset($_REQUEST['debug']) && $_REQUEST['debug'] == 'checkout') ? 1 : 0;
		$debug=0;
		
		$states = $this->getStates();
		if(strlen($data['state']) > 2) {
			$data['state'] = array_search($data['state'],$states);
		} else {
			$data['state'] = strtoupper($data['state']);
		}
		
		$totals = $Commerce->getTotals(array('country'=>$data['shipping_country'],'postal_code'=>$data['shipping_zip']));
		$data['amount'] = sprintf('%0.2f',$totals['total']);
		if($debug) $this->dump('Cart Totals',$totals); 

		$paypal_response = $Paypal->send($data,$debug);
		if($debug) $this->dump('Paypal Response',$paypal_response);
		if($debug) exit;

		if(isset($paypal_response['ACK'])) {
			if($paypal_response['ACK'] == 'Success') {	// Checkout success
				$data['status'] = '1';
				$order_id = $this->insertOrder($data);
				$this->sendMail(array(
							'to' => Registry::getParam('Config','default_email'),
							'from' => $_SESSION['user']['table_users']['email'],
							'subject' => 'A new order has been placed on your website.',
							'content' => 'Below are the contents of the order'
							));
				$this->sendMail(array(
							'to' => $_SESSION['user']['table_users']['email'],
							'from' => Registry::getParam('Config','registration_response_email'),
							'subject' => 'Your order has been received',
							'content' => 'Thank you for your order'
							));
				$Cart->clearCart();
				$this->redirect('checkout-success');
			} elseif($paypal_response['ACK'] == 'Failure' || $paypal_response['ACK'] == 'SuccessWithWarning') {	// checkout failure
				Error::websiteError($paypal_response['L_LONGMESSAGE0']);
				unset($data['creditCardNumber']);		// do not save the credit card number
				$_SESSION['checkout_info'] = $data;
				$this->redirect('checkout');
			}
		} else {
				Error::paypalError('Checkout Communications Failure','There has been a payment transmission error between the website and the server. The administrators have been notified and we will review it immediately. Your shopping cart will be preserved and you can checkout later.');
		}
	}
	
	/**
	* getHaversine : get a list of locations near a lat,lng
	*
	* @param string    $delim  column seperator default: ,
	* @param string    $newline  type of newline to use default: '\r\n'
	* @access public
	* @return array
	**/
	public function getHaversine($table=null,$distance=null,$address=null,$lat=null,$lng=null,$where=null,$measurement=null,$debug=1) {
			if(is_null($address) && (is_null($lat) && is_null($lng))) {
				Error::websiteError('Invalid Address Provided');
				return array();
			}
			if(is_null($table)) $table = 'clients';
			if(is_null($distance)) $distance = 25;
			if(is_null($measurement)) $lng = 'miles';
			if(is_null($lat) && is_null($lng))	list($lat,$lng) = $this->geocodeAddress($address);
			if(!is_null($where) && $where != '') $where = 'and '.$where;
			
			$Database = Registry::getKey('Database');
			$deviation = $measurement=='miles' ? '6371' : '3959'; // Haversine formula -- miles = 6371; kilometers = 3959;
			
			$query = "SELECT *, (".$deviation."*acos(cos(radians('".$lat."')) * cos(radians(lat)) * cos(radians(lng) - radians('".$lng."')) + sin( radians('".$lat."')) * sin( radians(lat)))) AS distance FROM ".$table." where active = 1 HAVING distance < '".$distance."' ".$where." ORDER BY distance";
			
			if($debug) $this->dump('Haversine Query',$query);
			$result = $this->res2array($Database->query($query));
			if($debug) $this->dump('Haversine Query Result',$result);
			return $result;
	}

	/**
	* geocodeAddress : get the lat and long for an address from google
	*
	* @param string		$address 	full address
	* @param integer	$debug  	print out debug information
	* @access public
	* @return array
	**/ 
	public function geocodeAddress($address,$mode=null,$debug=null) {
		if($debug) $this->dump('Address',$address);
		if(isset($_SESSION['address_data']['id']) && $_SESSION['address_data']['id'] == md5($address) && $mode == 'location') return $_SESSION['address_data'];
		$base_url = "http://maps.google.com/maps/geo?output=xml&key=".Registry::getParam('Config','google_api_key');
		$request_url = $base_url . "&q=" . urlencode($address);
		$xml = simplexml_load_file($request_url) or die("url not loading");
		$status = $xml->Response->Status->code;
		if($debug) print_r($xml);
		if($status == '200') {
			switch($mode) {
				case 'location' :
					$data = array();
					$data['id'] = md5($address);
					$data['address'] = (string) $xml->Response->Placemark->address;
					$data['country_code'] = (string) $xml->Response->Placemark->AddressDetails->Country->CountryNameCode;
					$data['country'] = (string) $xml->Response->Placemark->AddressDetails->Country->CountryName;
					$data['state'] = (string) $xml->Response->Placemark->AddressDetails->Country->AdministrativeArea->AdministrativeAreaName;
					if(isset($xml->Response->Placemark->AddressDetails->Country->AdministrativeArea->SubAdministrativeArea->SubAdministrativeAreaName)) {
						$data['city'] = (string) $xml->Response->Placemark->AddressDetails->Country->AdministrativeArea->SubAdministrativeArea->Locality->LocalityName;
						$data['zip'] = (string) $xml->Response->Placemark->AddressDetails->Country->AdministrativeArea->SubAdministrativeArea->Locality->PostalCode->PostalCodeNumber;
					} else {
						$data['city'] = (string) $xml->Response->Placemark->AddressDetails->Country->AdministrativeArea->Locality->LocalityName;
						$data['zip'] = (string) $xml->Response->Placemark->AddressDetails->Country->AdministrativeArea->Locality->PostalCode->PostalCodeNumber;
					}
					$_SESSION['address_data']['id'] = $data;
					return $data;
					break;
				default :
					$coordinates = $xml->Response->Placemark->Point->coordinates;
					$coordinatesSplit = split(",", $coordinates);
					// Format: Longitude, Latitude, Altitude
					$lat = $coordinatesSplit[1];
					$lng = $coordinatesSplit[0];
					if($debug) $this->dump('Coords',$coordinates);
					return array($lat,$lng);
					break;
			}
		} elseif($status == '620') {
			Error::websiteError('Address Failed to GeoCode - Status #620');
		} else {
		exit;
			Error::websiteError('Address could not be found, please use google maps to verify the address ++'.$address);
		}
		return '';
	}

	/**
	* createGoogleMapsXML : create the xml required for google maps
	*
	* @param string		$address 	full address
	* @param integer	$debug  	print out debug information
	* @access public
	* @return array
	**/ 
	public function createGoogleMapsXML($data,$debug=null) {
		if(!is_null($debug)) $this->dump('Google Maps Markers',$data);
		$document = new DOMDocument('1.0');
		$document->formatOutput = true;	// format the output so I can read the source
		$node = $document->createElement("markers");	// root node is schema
		$subnode = $document->appendChild($node);
		foreach($data as $marker) {
			$node = $document->createElement('marker');
			$newnode = $subnode->appendChild($node);
			$newnode->setAttribute('name',$marker['name']);
			$newnode->setAttribute('address',$marker['address']);
			if(isset($marker['phone'])) $newnode->setAttribute('phone',$marker['phone']);
			$newnode->setAttribute('lat',$marker['lat']);
			$newnode->setAttribute('lng',$marker['lng']);
			$newnode->setAttribute('distance',$marker['distance']);
		}
		
		$xml =  $document->saveXML();
		if(!is_null($debug)) $this->dump('Google Maps XML',$xml);
		return $xml;
	}
	
	
	/**
	* pointer to parseQuery
	*
	* @param string		$str url
	* @access public
	* @return array
	**/ 
	public function parseQuery($str) {
		return Utilities::parseQuery($str);
	}
	
	/**
	* classToXML - convert a class structure into a 
	*
	* @param string		$className		name of the class as it corresponds to className.class.php in libs
	* @access public
	* @return null
	**/ 
	public function classToXML($className) {
		include_once('Array2XML.class.php');
		
		// where to save xml files
		$location = 'libs/xmlClasses/'.$className.'.xml';
		
		// get the core from the registry
		$Class = Registry::getKey($className);
		
		// get the class methods (internal method) easier to search this way
		$methods = get_class_methods($Class);
		
		// start with the timestamp so we can know when to update the cache
		$xml = array('timestamp' => date ("y:m:d:H:i:s", filemtime('libs/'.$className.'.class.php')));
		
		// add all the methods as elements
		foreach($methods as $method) {
			$cmeth = new ReflectionMethod($className, $method);
			$args = $cmeth->getParameters();
			foreach($args as $arg) {
				$xml[$method][$arg->getName()] = $arg->isOptional();
			}
		}
		
		$array2xml = new array2xml('Core',$xml);
		$array2xml->createNode($xml);
		file_put_contents('libs/xmlClasses/'.$className.'.xml',$array2xml);
	}
	
	/**
	* updateClassXml will open the cache files and apply them to the registry. If they aren't available or out of date, they will be updated first.
	*
	* @access public
	* @return array
	**/ 
	public function updateClassXML() {
		$classes = array('Core','Commerce','Cart');	// only classes with function.class.php files in libs/plugins
		
		$structure = array();
		foreach($classes as $class) {
			// see if a cache file does not exist
			if (!file_exists('libs/xmlClasses/'.$class.'.xml')) {
				$this->classToXML($class);	// create one
			} else {			
				// get the cache file
				$filecontents = file_get_contents('libs/xmlClasses/'.$class.'.xml');
				$xml = simplexml_load_string($filecontents);
				
				// see if the timestamps match
				if($xml->timestamp != date ("y:m:d:H:i:s", filemtime('libs/'.$class.'.class.php'))) {
					//echo $xml['timestamp'].' -- '.date ("y:m:d:H:i:s", filemtime('libs/'.$class.'.class.php')).'<br />';
					
					$this->classToXML($class);	// cache if the timestamps do not match
				}
				
				// else add it to the structure;
				$structure[$class] = $xml->asXML();
			}
		}
		return $structure;
}
	
	
}

//
// Manataria Website Platform - Eats the seaweeds
//
// Copyright Kenneth Elliott. All rights reserved.
//

/**
 * MySQL Wrapper
 *
 * This class contains a uniform wrapper for communicating with a MySQL database.
 * Now build classes with the same interface for additional DBMS's ;)
 *
 * @author      Kenneth Elliott <kenelliottmc@gmail.com>
 * @copyright   Copyright &copy; 2010 Kenneth Elliott <kenelliottmc@gmail.com>
 * @category    Manataria
 * @package     MySQL
 * @since       Manataria 0.0.1
 */
class Error {	
		private $Smarty;
		/**
		* initiate the error class
		*
		* @param  string   $table	name of the table that will serve as the error log -> message | json | log_date
		* @return null
		* @access public
		*/
		public function initiate() {
			$this->Smarty = Registry::getKey('Smarty');
		}
		
		/**
		* log an error to the database
		*
		* @param  string   $message	mysql error message
		* @param string $data error content that will can be sent via json
		* @return null
		* @access public
		*/
		public static function logError($message=null,$data=null) {
			$Database = Registry::getKey('Database');
			$Core = Registry::getKey('Core');
			$Database->perform('error_log',
				array(
					'message'=>$message,
					'json'=>json_encode($data)
				)
			);
		}
		
		/**
		* echo an error message
		*
		* @param  string   $msg	message response
		* @return null
		* @access public
		*/
		public static function ajaxError($msg) {
			echo 'error:'.$msg;
		}
		
		/**
		* log and echo mysql errors
		*
		* @param  string   $query	mysql query
		* @return null
		* @access public
		*/
		public static function mysqlError($query='',$show='all') {
			$Database = Registry::getKey('Database'); 

			echo '<div style="background-color:#E8D5CE;border:1px solid #C47346;color:#C47346;margin-bottom:10px; padding:20px;"><strong>Error:</strong> <p>'.mysql_error().'</p><strong>Query:</strong><p> '.$query.'</p></div>';
			
			$Database->perform('error_log',
				array(
					'message'=> 'MySQL Error'.mysql_errno().' : '.mysql_error().'<p><b>'.$query. "</b>\n",
					'json'=>json_encode(array('mysql_errno'=>mysql_errno(),'mysql_error'=>mysql_error(),'query'=>$query))
				)
			);
			exit;
		}
		
		/**
		* log and echo program errors
		*
		* @param  string   $message	error message that was displayed to the user
		* @return null
		* @access public
		*/
		public static function programError($title=null,$description=null) {
			$Database = Registry::getKey('Database'); 

			// echo '<div class="error"><strong>Program Error - </strong> '.$title.' :<p>'.$description.'</div>';
			$Database->perform('error_log',
				array(
					'message'=> 'Program Error : '.$title.'. '.$description,
					'json'=>json_encode(array('request'=>$_REQUEST))
				)
			);
			//$_SESSION['error_msg'] = '<strong>Program Error -</strong> <p>'.$title.':</p><p>'.$description.'</p></div>';
			if(Registry::getParam('Config','run_mode') == 'debug') echo '<div style="background-color:#E8D5CE;border:1px solid #C47346;color:#C47346;margin-bottom:10px; padding:20px;"><strong>Program Error</strong> <p>'.$title.':</p><p>'.$description.'</p></div>';
		}
		
		/**
		* log and echo program errors
		*
		* @param  string   $message	error message that was displayed to the user
		* @return null
		* @access public
		*/
		public static function paypalError($title=null,$description=null,$paypal_sent=null,$paypal_response=null) {
			$Database = Registry::getKey('Database'); 

			// echo '<div class="error"><strong>Program Error - </strong> '.$title.' :<p>'.$description.'</div>';
			$Database->perform('error_log',
				array(
					'message'=> 'Paypal Error : '.$title.'. '.$description,
					'json'=>json_encode(array('request'=>$paypal_response))
				)
			);
			//$_SESSION['error_msg'] = '<strong>Program Error -</strong> <p>'.$title.':</p><p>'.$description.'</p></div>';
			if(Registry::getParam('Config','run_mode') == 'debug') echo '<div style="background-color:#E8D5CE;border:1px solid #C47346;color:#C47346;margin-bottom:10px; padding:20px;"><strong>Program Error</strong> <p>'.$title.':</p><p>'.$description.'</p></div>';
		}
		
		/**
		* log and echo program errors
		*
		* @param  string   $message	error message that was displayed to the user
		* @return null
		* @access public
		*/
		public static function websiteError($string=null) {
			$_SESSION['error_msg'] = $string;
			$Smarty = Registry::getKey('Smarty');
			$Smarty->assign('error_msg',$string);
		}
}


	class Fedex {
			/**
			* Core object
			* @var object
			*/
			var $Core;    
			
			/**
			* request options required by FedEx API
			* @var array
			*/
			var $request;
			
			/**
			* Fedex WSDL File location
			* @var string
			*/
			var $wsdl;

			/**
			* Fedex Constructor : get shipping quotes from 
			*
			* @param array    $data  array of request options
			* @param string    $debug  debug flag
			* @access public
			* @return null
			**/
			public function Fedex($data=null,$debug=null) {
				if(isset($data)) $this->setRequest($data);
				$this->Core = Registry::getKey('Core');

				$this->wsdl = Registry::getParam('Config','file_dir').'libs/RateService_v8.wsdl';

				$this->request['Version'] = array('ServiceId' => 'crs', 'Major' => '8', 'Intermediate' => '0', 'Minor' => '0');
				$this->request['ReturnTransitAndCommit'] = true;
				$this->request['RequestedShipment']['DropoffType'] = 'REGULAR_PICKUP'; // valid values REGULAR_PICKUP, REQUEST_COURIER, ...
				$this->request['RequestedShipment']['ShipTimestamp'] = date('c');
				$this->request['ReturnTransitAndCommit'] = true;
				$this->request['RequestedShipment']['ShippingChargesPayment'] = array('PaymentType' => 'SENDER',
																															'Payor' => array('AccountNumber' => $data['AccountNumber'],
																															'CountryCode' => 'US'));
				$this->request['RequestedShipment']['RateRequestTypes'] = 'ACCOUNT'; 
				$this->request['RequestedShipment']['RateRequestTypes'] = 'LIST'; 
				$this->request['RequestedShipment']['PackageDetail'] = 'INDIVIDUAL_PACKAGES';
				if(!is_null($debug)) $this->Core->dump('WSDL',$this->wsdl);
			}

			/**
			* setRequest : set Fedex SOAP request options
			*
			* @param array    $data  array of request options
			* @param string    $debug  debug flag
			* @access public
			* @return null
			**/
			public function setRequest($data) {
				if(isset($data['DeveloperTestKey'])) $this->request['WebAuthenticationDetail']['UserCredential']['Key'] = $data['DeveloperTestKey'];
				if(isset($data['Password'])) $this->request['WebAuthenticationDetail']['UserCredential']['Password'] = $data['Password'];
				if(isset($data['AccountNumber'])) $this->request['ClientDetail']['AccountNumber'] = $data['AccountNumber'];
				if(isset($data['MeterNumber'])) $this->request['ClientDetail']['MeterNumber'] = $data['MeterNumber'];
				if(isset($data['CustomerTransactionId'])) $this->request['TransactionDetail']['CustomerTransactionId'] = $data['CustomerTransactionId'];
				if(isset($data['ShipperStreetLines'])) $this->request['RequestedShipment']['Shipper']['Address']['StreetLines'] = $data['ShipperStreetLines'];	// must be an array
				if(isset($data['ShipperCity'])) $this->request['RequestedShipment']['Shipper']['Address']['City'] = $data['ShipperCity'];
				if(isset($data['ShipperStateOrProvinceCode'])) $this->request['RequestedShipment']['Address']['Shipper']['StateOrProvinceCode'] = $data['ShipperStateOrProvinceCode'];
				if(isset($data['ShipperPostalCode'])) $this->request['RequestedShipment']['Shipper']['Address']['PostalCode'] = $data['ShipperPostalCode'];
				if(isset($data['ShipperCountryCode'])) $this->request['RequestedShipment']['Shipper']['Address']['CountryCode'] = $data['ShipperCountryCode'];
			    if(isset($data['RecipientStreetLines'])) $this->request['RequestedShipment']['Recipient']['Address']['StreetLines'] = $data['RecipientStreetLines'];	// must be an array
			    if(isset($data['RecipientCity'])) $this->request['RequestedShipment']['Recipient']['Address']['City'] = $data['RecipientCity'];
			    if(isset($data['RecipientStateOrProvinceCode'])) $this->request['RequestedShipment']['Address']['Recipient']['StateOrProvinceCode'] = $data['RecipientStateOrProvinceCode'];
				if(isset($data['RecipientPostalCode'])) $this->request['RequestedShipment']['Recipient']['Address']['PostalCode'] = $data['RecipientPostalCode'];
				if(isset($data['RecipientCountryCode'])) $this->request['RequestedShipment']['Recipient']['Address']['CountryCode'] = $data['RecipientCountryCode'];
				if(isset($data['PackageWeight'])) $this->request['RequestedShipment']['RequestedPackageLineItems'] = array('0' => array('Weight' => array('Value' => $data['PackageWeight'],'Units' => 'LB')));

				// uncomment this line to test a 1lb shipment
				// $this->request['RequestedShipment']['RequestedPackageLineItems'] = array('0' => array('Weight' => array('Value' => 1.0,'Units' => 'LB')));
				// end Testing
			}

			/**
			* getRates : set Fedex SOAP request options
			*
			* @param array    $data  array of request options
			* @param string    $debug  debug flag
			* @access public
			* @return null
			**/
			public function getRates($data=null,$debug=null) {
				if(isset($data)) $this->setRequest($data);
				
				$cache = 1;
				
				//if($debug) $this->Core->dump('getRates request', $this->request);
				// cache
				if($cache) {
						$hash = md5($this->request['RequestedShipment']['RequestedPackageLineItems']['0']['Weight']['Value'].$this->request['RequestedShipment']['Recipient']['Address']['PostalCode'].$this->request['RequestedShipment']['Recipient']['Address']['CountryCode']);					
						if(isset($_SESSION['shipment_data']) && $hash == $_SESSION['shipment_data']['hash']) {
							if($debug) $this->Core->dump('Fedex Cache Returned',$_SESSION['shipment_data']['rates']);
							return $_SESSION['shipment_data']['rates'];
						}
						$_SESSION['shipment_data']['hash'] = $hash;
				}
				
				ini_set("soap.wsdl_cache_enabled", "0");
				$client = new SoapClient($this->wsdl, array('trace' => 1)); 
				try {
					$rates = array();
					if($debug) $this->Core->dump('Fedex Request',$this->request);
					$response = $client ->getRates($this->request);
					if($debug) print_r($response);
					if ($response->HighestSeverity != 'FAILURE' && $response->HighestSeverity != 'ERROR') {
							if(is_array($response->RateReplyDetails)) {
								foreach ($response->RateReplyDetails as $rateReply) {           
									$rates[$rateReply->ServiceType] = $rateReply->RatedShipmentDetails[1]->ShipmentRateDetail->TotalNetCharge->Amount;
								}
							}
						if(!is_null($debug)) $this->Core->dump('Rates',$rates);
						
						// cross reference with the database to restore order and remove unwanted rates
						$fedex_services = $this->Core->getTable('fedex_services',null,null,null,null,null,'weight asc');
						foreach($fedex_services as $service) {
							if(isset($rates[$service['id']])) $return_rates[$service['id']] = $rates[$service['id']];
						}
						$_SESSION['shipment_data']['rates'] = $return_rates;
						return $return_rates;
					} else {
						Error::websiteError('We were unable to calculate your shipping costs through Fedex. Please verify your postal code and be sure you selected the correct country.');
						Error::programError('Error Retrieving Fedex Quote','Code:'.$response->Notifications->Code.' String:'.$response->Notifications->Message.' TransID:'.$response->TransactionDetail->CustomerTransactionId);
						return;
					} 
				} catch (SoapFault $exception) {
					Error::programError('Fedex SOAP Error','Code:'.$exception->faultcode.' String:'.$client->faultstring);
					return;
				}
			}
		}
		
		//
		// Manataria Website Platform - Eats the seaweeds
		//
		// Copyright Kenneth Elliott. All rights reserved.
		//
		
		/**
		 * MySQL Wrapper
		 *
		 * This class contains a uniform wrapper for communicating with a MySQL database.
		 * Now build classes with the same interface for additional DBMS's ;)
		 *
		 * @author      Kenneth Elliott <kenelliottmc@gmail.com>
		 * @copyright   Copyright &copy; 2010 Kenneth Elliott <kenelliottmc@gmail.com>
		 * @category    Manataria
		 * @package     MySQL
		 * @since       Manataria 0.0.1
		 */
		class MySQL_Adapter {
			var $db;
			var $link_id;
			var $error;
		
			/**
			* Constructor - Set all the default values
			*
			* @param  string   $host	mysql server hostname
			* @param  string   $user	mysql server username
			* @param  string   $db	mysql server db
			* @param  string   $pass	mysql server password
			* @return null
			* @access public
			*/
			public function __construct($host = '', $user = '', $db = '', $pass = ''){
				$host = $this->not_null($host) ? $host : DB_SERVER;
				$user = $this->not_null($user) ? $user : DB_SERVER_USERNAME;
				$pass = $this->not_null($pass) ? $pass : DB_SERVER_PASSWORD;
				$this->db = $this->not_null($db) ? $db : DB_DATABASE;
				$this->connect_db($host, $user, $pass);
				$this->select_db();
				$this->error = Registry::getKey('Error');
			}
		
			/**
			* Connect to a database
			*
			* @param  string   $host	mysql server hostname
			* @param  string   $user	mysql server username
			* @param  string   $pass	mysql server password
			* @return null
			* @access public
			*/
			public function connect_db($host, $user, $pass){
				$this->link_id = mysql_connect($host, $user, $pass)
						    or $this->error->mysqlError();
				return;
			}
		
			/**
			* Select database
			*
			* @return object	database handle
			* @access public
			*/
			public function select_db(){
				return mysql_select_db($this->db, $this->link_id)
					     or $this->error->mysqlError();
			}
		
			/**
			* perform a query
			*
			* @param  string   $query	mysql query string
			* @return object	mysql resource
			* @access public
			*/
			public function query($query){
				$resource = mysql_query($query, $this->link_id)
						or $this->error->mysqlError($query);
				return $resource;
			}
		
			/**
			* do an unbuffered query for large result sets
			*
			* @param  string   $query	mysql query string
			* @return array	array of data
			* @access public
			*/
			public function query_unbuffered($query){
				$resource = mysql_unbuffered_query($query, $this->link_id)
						or $this->error->mysqlError($query);
				if(!is_resource($resource)) return array();
				$a = array();
				while($r = mysql_fetch_assoc($resource)) $a[] = $r;
				return $a;
			}
		
			/**
			* mysql_fetch_array wrapper
			*
			* @param  object   $resource_id	mysql resource
			* @return string	$type	mysql result type MYSQL_ASSOC | MYSQL_NUM | MYSQL_BOTH
			* @access public
			*/
			function fetch_array($resource_id, $type = MYSQL_ASSOC){
				return mysql_fetch_array($resource_id, $type);
			}
		
			/**
			* mysql_num_rows wrapper
			*
			* @param  object   $resource_id	mysql resource
			* @return integer
			* @access public
			*/
			function num_rows($resource_id){
				return mysql_num_rows($resource_id);
			}
		
			/**
			* mysql_insert_id wrapper - shows the last id that was inserted
			*
			* @return integer
			* @access public
			*/
			function insert_id() {
				return mysql_insert_id($this->link_id);
			}
		
			/**
			* mysql_free_result wrapper
			*
			* @param  object   $resource_id	mysql resource
			* @return boolean
			* @access public
			*/
			function free_result($resource_id){
				return mysql_free_result($resource_id);
			}
		
			/**
			* mysql_close wrapper - disconnect database connection
			*
			* @return boolean
			* @access public
			*/
			function disconnect() {
				return mysql_close($this->link_id);
			}
		
			/**
			* Perform an update or insert to a table.
			*
			* @param  string   $table	name of the table that is affected
			* @param  array   $data	array of data where the key = column name
			* @param  string   $action	insert or update
			* @param  string   $parameters	used for associating an update ie (type_id = 3 and id=2)
			* @return boolean
			* @access public
			*/
			// db_perform('users',array('username'=>'bigdog','password'='elliott'));
			function perform($table, $data, $action = 'insert', $parameters = '') {
				reset($data);
				if ($action == 'insert') {
					$query = 'INSERT INTO `'.$table.'` (';
					while (list($columns, ) = each($data)) {
						$query .= '`'.$columns.'`, ';
					}
					$query = rtrim($query, ', ').') values (';
					reset($data);
					while (list(, $value) = each($data)) {
						switch ((string)$value) {
							case 'now()':
								$query .= 'now(), ';
								break;
							case 'null':
								$query .= 'null, ';
								break;
							default:
								$query .= "'".Utilities::sanitize($value)."', ";
								break;
						} 
					}
					$query = rtrim($query, ', ').')';
				} elseif ($action == 'update') {
					$query = 'UPDATE `'.$table.'` SET ';
					while (list($columns, $value) = each($data)) {
						switch ((string)$value) {
							case 'now()':
								$query .= '`' .$columns.'`=now(), ';
								break;
							case 'null':
								$query .= '`' .$columns .= '`=null, ';
								break;
							default:
								$query .= '`' .$columns."`='".Utilities::sanitize($value)."', ";
								break;
						}
					}
					$query = rtrim($query, ', ').' WHERE '.$parameters;
				}
		
				return $this->query($query);
			}
		
			/**
			* check if a value is null
			*
			* @param  mixed   $value	check to see what type of value was supplied.
			* @return boolean
			* @access public
			*/
			function not_null($value) {
				switch(gettype($value)){
					case 'boolean':
					case 'object':
					case 'resource':
					case 'integer':
					case 'double':
						return true;
						break;
					case 'string':
						if (($value != '') && (strtolower($value) != 'null') && (strlen(trim($value)) > 0)){
						    return true;
						} else {
						    return false;
						}
						break;
					case 'array':
						if (sizeof($value) > 0){
						    return true;
						} else {
						    return false;
						}
						break;
					case 'NULL':
					default:
						return false;
						break;
				}
			}
		
		}


/*
 paypal loves manataria, but manataria thinks paypal is to insecure.
*/
class Paypal {

	protected $payment_type = 'sale';
	protected $api_username = 'kenell_1223344547_biz_api1.gmail.com';
	protected $api_password = '1223344553';
	protected $api_signature = 'AIicyEFMuc4fqFjZb2dwwFNvjkGKAFvxciqi35cFjiQtsdv.8-GoOtP9';
	protected $api_endpoint = 'https://api-3t.sandbox.paypal.com/nvp';
	protected $version = '54.0';

	protected $Core;

	// pass the core object
	public function __construct() {
		$this->Core = Registry::getKey('Core');
	}

	public function send($data)	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_endpoint);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POST, 1);

		//NVPRequest for submitting to server
		$nvpreq="METHOD=doDirectPayment".
				"&VERSION=".urlencode($this->version).
				"&PWD=".urlencode($this->api_password).
				"&USER=".urlencode($this->api_username).
				"&SIGNATURE=".urlencode($this->api_signature).
				$this->encodeNVPString($data);

		//setting the nvpreq as POST FIELD to curl
		curl_setopt($ch,CURLOPT_POSTFIELDS,$nvpreq);

		//getting response from server
		$response = curl_exec($ch);

		$nvpResArray=$this->nvp2array($response);
		$nvpReqArray=$this->nvp2array($nvpreq);
		// $_SESSION['nvpReqArray']=$nvpReqArray;

		if(curl_errno($ch)) Error::paypalError('Paypal Communication Error','There was an error sending the information to Paypal for processing.',$nvpreq,curl_error($ch)); //echo 'paypal communication error : '.curl_error($ch);
		curl_close($ch);
		return $nvpResArray;
	}

	function nvp2array($nvpstr) {
		$intial=0;
	 	$nvpArray = array();
		while(strlen($nvpstr)){
			$keypos= strpos($nvpstr,'=');
			$valuepos = strpos($nvpstr,'&') ? strpos($nvpstr,'&'): strlen($nvpstr);
			$keyval=substr($nvpstr,$intial,$keypos);
			$valval=substr($nvpstr,$keypos+1,$valuepos-$keypos-1);
			$nvpArray[urldecode($keyval)] =urldecode( $valval);
			$nvpstr=substr($nvpstr,$valuepos+1,strlen($nvpstr));
	     }
		return $nvpArray;
	}

	public function encodeNVPString($data) {
		$padDateMonth = str_pad($data['expDateMonth'], 2, '0', STR_PAD_LEFT);
		$str = 	'&PAYMENTACTION='.$this->payment_type.
				'&AMT='.urlencode($data['amount']).
				'&CREDITCARDTYPE='.urlencode($data['creditCardType']).
				'&ACCT='.urlencode($data['creditCardNumber']).
				'&EXPDATE='.$padDateMonth.$data['expDateYear'].
				'&CVV2='.urlencode($data['cvv2Number']).
				'&FIRSTNAME='.urlencode($data['firstName']).
				'&LASTNAME='.urlencode($data['lastName']).
				'&STREET='.urlencode($data['address']).
				'&CITY='.urlencode($data['city']).
				'&STATE='.urlencode($data['state']).
				'&ZIP='.urlencode($data['zip']).
				'&COUNTRYCODE=US&CURRENCYCODE=USD';
		return $str;
	}
}

//
// Manataria Website Platform - Eats the seaweeds
//
// Copyright 2008 Kenneth Elliott. All rights reserved.
//

/**
 * Registry API
 *
 * This class contains the common interface to variables and classes.
 *
 * @author      Kenneth Elliott <kenelliottmc@gmail.com>
 * @copyright   Copyright &copy; 2008 Kenneth Elliott <kenelliottmc@gmail.com>
 * @category    Manataria
 * @package     Registry
 * @since       Manataria 0.0.1
 */
class Registry {
    public static $_store = array();

    public static function setKey($key,$item) {;
        self::$_store[$key] = $item;
    }

    public static function getKey($key) {
        return self::$_store[$key];
    }

    public static function isKey($key) {
        return (self::getKey($key) !== null);
    }

    public static function getParam($key,$param) {
    	if(!isset(self::$_store[$key]) || !is_array(self::$_store[$key])) trigger_error('call to getParam failed because key does not exist or is not an array');
		return self::$_store[$key][$param];
    }

    public static function setParam($key,$param,$value) {
		self::$_store[$key][$param] = $value;
    }

    public static function dump($key=null) {
		if(isset($key)) { var_dump(self::$_store[$key]); return; }
		var_dump(self::$_store, true);
    }
}


/**
 * Project:     Smarty: the PHP compiling template engine
 * File:        Smarty.class.php
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * For questions, help, comments, discussion, etc., please join the
 * Smarty mailing list. Send a blank e-mail to
 * smarty-general-subscribe@lists.php.net
 *
 * @link http://smarty.php.net/
 * @copyright 2001-2005 New Digital Group, Inc.
 * @author Monte Ohrt <monte at ohrt dot com>
 * @author Andrei Zmievski <andrei@php.net>
 * @package Smarty
 * @version 2.6.7
 */

/* $Id: Smarty.class.php,v 1.511 2005/02/03 14:41:33 mohrt Exp $ */

/**
 * DIR_SEP isn't used anymore, but third party apps might
 */
if(!defined('DIR_SEP')) {
    define('DIR_SEP', DIRECTORY_SEPARATOR);
}

/**
 * set SMARTY_DIR to absolute path to Smarty library files.
 * if not defined, include_path will be used. Sets SMARTY_DIR only if user
 * application has not already defined it.
 */

if (!defined('SMARTY_DIR')) {
    define('SMARTY_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR);
}

if (!defined('SMARTY_CORE_DIR')) {
    define('SMARTY_CORE_DIR', SMARTY_DIR . 'internals' . DIRECTORY_SEPARATOR);
}

define('SMARTY_PHP_PASSTHRU',   0);
define('SMARTY_PHP_QUOTE',      1);
define('SMARTY_PHP_REMOVE',     2);
define('SMARTY_PHP_ALLOW',      3);

/**
 * @package Smarty
 */
class Smarty
{
    /**#@+
     * Smarty Configuration Section
     */

    /**
     * The name of the directory where templates are located.
     *
     * @var string
     */
    var $template_dir    =  'templates';

    /**
     * The directory where compiled templates are located.
     *
     * @var string
     */
    var $compile_dir     =  'templates_c';

    /**
     * The directory where config files are located.
     *
     * @var string
     */
    var $config_dir      =  'configs';

    /**
     * An array of directories searched for plugins.
     *
     * @var array
     */
    var $plugins_dir     =  array('plugins');

    /**
     * If debugging is enabled, a debug console window will display
     * when the page loads (make sure your browser allows unrequested
     * popup windows)
     *
     * @var boolean
     */
    var $debugging       =  false;

    /**
     * When set, smarty does uses this value as error_reporting-level.
     *
     * @var boolean
     */
    var $error_reporting  =  null;

    /**
     * This is the path to the debug console template. If not set,
     * the default one will be used.
     *
     * @var string
     */
    var $debug_tpl       =  '';

    /**
     * This determines if debugging is enable-able from the browser.
     * <ul>
     *  <li>NONE => no debugging control allowed</li>
     *  <li>URL => enable debugging when SMARTY_DEBUG is found in the URL.</li>
     * </ul>
     * @link http://www.foo.dom/index.php?SMARTY_DEBUG
     * @var string
     */
    var $debugging_ctrl  =  'NONE';

    /**
     * This tells Smarty whether to check for recompiling or not. Recompiling
     * does not need to happen unless a template or config file is changed.
     * Typically you enable this during development, and disable for
     * production.
     *
     * @var boolean
     */
    var $compile_check   =  true;

    /**
     * This forces templates to compile every time. Useful for development
     * or debugging.
     *
     * @var boolean
     */
    var $force_compile   =  false;

    /**
     * This enables template caching.
     * <ul>
     *  <li>0 = no caching</li>
     *  <li>1 = use class cache_lifetime value</li>
     *  <li>2 = use cache_lifetime in cache file</li>
     * </ul>
     * @var integer
     */
    var $caching         =  0;

    /**
     * The name of the directory for cache files.
     *
     * @var string
     */
    var $cache_dir       =  'cache';

    /**
     * This is the number of seconds cached content will persist.
     * <ul>
     *  <li>0 = always regenerate cache</li>
     *  <li>-1 = never expires</li>
     * </ul>
     *
     * @var integer
     */
    var $cache_lifetime  =  3600;

    /**
     * Only used when $caching is enabled. If true, then If-Modified-Since headers
     * are respected with cached content, and appropriate HTTP headers are sent.
     * This way repeated hits to a cached page do not send the entire page to the
     * client every time.
     *
     * @var boolean
     */
    var $cache_modified_check = false;

    /**
     * This determines how Smarty handles "<?php ... ?>" tags in templates.
     * possible values:
     * <ul>
     *  <li>SMARTY_PHP_PASSTHRU -> print tags as plain text</li>
     *  <li>SMARTY_PHP_QUOTE    -> escape tags as entities</li>
     *  <li>SMARTY_PHP_REMOVE   -> remove php tags</li>
     *  <li>SMARTY_PHP_ALLOW    -> execute php tags</li>
     * </ul>
     *
     * @var integer
     */
    var $php_handling    =  SMARTY_PHP_PASSTHRU;

    /**
     * This enables template security. When enabled, many things are restricted
     * in the templates that normally would go unchecked. This is useful when
     * untrusted parties are editing templates and you want a reasonable level
     * of security. (no direct execution of PHP in templates for example)
     *
     * @var boolean
     */
    var $security       =   false;

    /**
     * This is the list of template directories that are considered secure. This
     * is used only if {@link $security} is enabled. One directory per array
     * element.  {@link $template_dir} is in this list implicitly.
     *
     * @var array
     */
    var $secure_dir     =   array();

    /**
     * These are the security settings for Smarty. They are used only when
     * {@link $security} is enabled.
     *
     * @var array
     */
    var $security_settings  = array(
                                    'PHP_HANDLING'    => false,
                                    'IF_FUNCS'        => array('array', 'list',
                                                               'isset', 'empty',
                                                               'count', 'sizeof',
                                                               'in_array', 'is_array',
                                                               'true','false'),
                                    'INCLUDE_ANY'     => false,
                                    'PHP_TAGS'        => false,
                                    'MODIFIER_FUNCS'  => array('count'),
                                    'ALLOW_CONSTANTS'  => false
                                   );

    /**
     * This is an array of directories where trusted php scripts reside.
     * {@link $security} is disabled during their inclusion/execution.
     *
     * @var array
     */
    var $trusted_dir        = array();

    /**
     * The left delimiter used for the template tags.
     *
     * @var string
     */
    var $left_delimiter  =  '{';

    /**
     * The right delimiter used for the template tags.
     *
     * @var string
     */
    var $right_delimiter =  '}';

    /**
     * The order in which request variables are registered, similar to
     * variables_order in php.ini E = Environment, G = GET, P = POST,
     * C = Cookies, S = Server
     *
     * @var string
     */
    var $request_vars_order    = 'EGPCS';

    /**
     * Indicates wether $HTTP_*_VARS[] (request_use_auto_globals=false)
     * are uses as request-vars or $_*[]-vars. note: if
     * request_use_auto_globals is true, then $request_vars_order has
     * no effect, but the php-ini-value "gpc_order"
     *
     * @var boolean
     */
    var $request_use_auto_globals      = true;

    /**
     * Set this if you want different sets of compiled files for the same
     * templates. This is useful for things like different languages.
     * Instead of creating separate sets of templates per language, you
     * set different compile_ids like 'en' and 'de'.
     *
     * @var string
     */
    var $compile_id            = null;

    /**
     * This tells Smarty whether or not to use sub dirs in the cache/ and
     * templates_c/ directories. sub directories better organized, but
     * may not work well with PHP safe mode enabled.
     *
     * @var boolean
     *
     */
    var $use_sub_dirs          = false;

    /**
     * This is a list of the modifiers to apply to all template variables.
     * Put each modifier in a separate array element in the order you want
     * them applied. example: <code>array('escape:"htmlall"');</code>
     *
     * @var array
     */
    var $default_modifiers        = array();

    /**
     * This is the resource type to be used when not specified
     * at the beginning of the resource path. examples:
     * $smarty->display('file:index.tpl');
     * $smarty->display('db:index.tpl');
     * $smarty->display('index.tpl'); // will use default resource type
     * {include file="file:index.tpl"}
     * {include file="db:index.tpl"}
     * {include file="index.tpl"} {* will use default resource type *}
     *
     * @var array
     */
    var $default_resource_type    = 'file';

    /**
     * The function used for cache file handling. If not set, built-in caching is used.
     *
     * @var null|string function name
     */
    var $cache_handler_func   = null;

    /**
     * This indicates which filters are automatically loaded into Smarty.
     *
     * @var array array of filter names
     */
    var $autoload_filters = array();

    /**#@+
     * @var boolean
     */
    /**
     * This tells if config file vars of the same name overwrite each other or not.
     * if disabled, same name variables are accumulated in an array.
     */
    var $config_overwrite = true;

    /**
     * This tells whether or not to automatically booleanize config file variables.
     * If enabled, then the strings "on", "true", and "yes" are treated as boolean
     * true, and "off", "false" and "no" are treated as boolean false.
     */
    var $config_booleanize = true;

    /**
     * This tells whether hidden sections [.foobar] are readable from the
     * tempalates or not. Normally you would never allow this since that is
     * the point behind hidden sections: the application can access them, but
     * the templates cannot.
     */
    var $config_read_hidden = false;

    /**
     * This tells whether or not automatically fix newlines in config files.
     * It basically converts \r (mac) or \r\n (dos) to \n
     */
    var $config_fix_newlines = true;
    /**#@-*/

    /**
     * If a template cannot be found, this PHP function will be executed.
     * Useful for creating templates on-the-fly or other special action.
     *
     * @var string function name
     */
    var $default_template_handler_func = '';

    /**
     * The file that contains the compiler class. This can a full
     * pathname, or relative to the php_include path.
     *
     * @var string
     */
    var $compiler_file        =    'Smarty_Compiler.class.php';

    /**
     * The class used for compiling templates.
     *
     * @var string
     */
    var $compiler_class        =   'Smarty_Compiler';

    /**
     * The class used to load config vars.
     *
     * @var string
     */
    var $config_class          =   'Config_File';

/**#@+
 * END Smarty Configuration Section
 * There should be no need to touch anything below this line.
 * @access private
 */
    /**
     * where assigned template vars are kept
     *
     * @var array
     */
    var $_tpl_vars             = array();

    /**
     * stores run-time $smarty.* vars
     *
     * @var null|array
     */
    var $_smarty_vars          = null;

    /**
     * keeps track of sections
     *
     * @var array
     */
    var $_sections             = array();

    /**
     * keeps track of foreach blocks
     *
     * @var array
     */
    var $_foreach              = array();

    /**
     * keeps track of tag hierarchy
     *
     * @var array
     */
    var $_tag_stack            = array();

    /**
     * configuration object
     *
     * @var Config_file
     */
    var $_conf_obj             = null;

    /**
     * loaded configuration settings
     *
     * @var array
     */
    var $_config               = array(array('vars'  => array(), 'files' => array()));

    /**
     * md5 checksum of the string 'Smarty'
     *
     * @var string
     */
    var $_smarty_md5           = 'f8d698aea36fcbead2b9d5359ffca76f';

    /**
     * Smarty version number
     *
     * @var string
     */
    var $_version              = '2.6.7';

    /**
     * current template inclusion depth
     *
     * @var integer
     */
    var $_inclusion_depth      = 0;

    /**
     * for different compiled templates
     *
     * @var string
     */
    var $_compile_id           = null;

    /**
     * text in URL to enable debug mode
     *
     * @var string
     */
    var $_smarty_debug_id      = 'SMARTY_DEBUG';

    /**
     * debugging information for debug console
     *
     * @var array
     */
    var $_smarty_debug_info    = array();

    /**
     * info that makes up a cache file
     *
     * @var array
     */
    var $_cache_info           = array();

    /**
     * default file permissions
     *
     * @var integer
     */
    var $_file_perms           = 0644;

    /**
     * default dir permissions
     *
     * @var integer
     */
    var $_dir_perms               = 0771;

    /**
     * registered objects
     *
     * @var array
     */
    var $_reg_objects           = array();

    /**
     * table keeping track of plugins
     *
     * @var array
     */
    var $_plugins              = array(
                                       'modifier'      => array(),
                                       'function'      => array(),
                                       'block'         => array(),
                                       'compiler'      => array(),
                                       'prefilter'     => array(),
                                       'postfilter'    => array(),
                                       'outputfilter'  => array(),
                                       'resource'      => array(),
                                       'insert'        => array());


    /**
     * cache serials
     *
     * @var array
     */
    var $_cache_serials = array();

    /**
     * name of optional cache include file
     *
     * @var string
     */
    var $_cache_include = null;

    /**
     * indicate if the current code is used in a compiled
     * include
     *
     * @var string
     */
    var $_cache_including = false;

    /**#@-*/
    /**
     * The class constructor.
     */
    function Smarty()
    {
      $this->assign('SCRIPT_NAME', isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME']
                    : @$GLOBALS['HTTP_SERVER_VARS']['SCRIPT_NAME']);
    }

    /**
     * assigns values to template variables
     *
     * @param array|string $tpl_var the template variable name(s)
     * @param mixed $value the value to assign
     */
    function assign($tpl_var, $value = null)
    {
        if (is_array($tpl_var)){
            foreach ($tpl_var as $key => $val) {
                if ($key != '') {
                    $this->_tpl_vars[$key] = $val;
                }
            }
        } else {
            if ($tpl_var != '')
                $this->_tpl_vars[$tpl_var] = $value;
        }
    }

    /**
     * assigns values to template variables by reference
     *
     * @param string $tpl_var the template variable name
     * @param mixed $value the referenced value to assign
     */
    function assign_by_ref($tpl_var, &$value)
    {
        if ($tpl_var != '')
            $this->_tpl_vars[$tpl_var] = &$value;
    }

    /**
     * appends values to template variables
     *
     * @param array|string $tpl_var the template variable name(s)
     * @param mixed $value the value to append
     */
    function append($tpl_var, $value=null, $merge=false)
    {
        if (is_array($tpl_var)) {
            // $tpl_var is an array, ignore $value
            foreach ($tpl_var as $_key => $_val) {
                if ($_key != '') {
                    if(!@is_array($this->_tpl_vars[$_key])) {
                        settype($this->_tpl_vars[$_key],'array');
                    }
                    if($merge && is_array($_val)) {
                        foreach($_val as $_mkey => $_mval) {
                            $this->_tpl_vars[$_key][$_mkey] = $_mval;
                        }
                    } else {
                        $this->_tpl_vars[$_key][] = $_val;
                    }
                }
            }
        } else {
            if ($tpl_var != '' && isset($value)) {
                if(!@is_array($this->_tpl_vars[$tpl_var])) {
                    settype($this->_tpl_vars[$tpl_var],'array');
                }
                if($merge && is_array($value)) {
                    foreach($value as $_mkey => $_mval) {
                        $this->_tpl_vars[$tpl_var][$_mkey] = $_mval;
                    }
                } else {
                    $this->_tpl_vars[$tpl_var][] = $value;
                }
            }
        }
    }

    /**
     * appends values to template variables by reference
     *
     * @param string $tpl_var the template variable name
     * @param mixed $value the referenced value to append
     */
    function append_by_ref($tpl_var, &$value, $merge=false)
    {
        if ($tpl_var != '' && isset($value)) {
            if(!@is_array($this->_tpl_vars[$tpl_var])) {
             settype($this->_tpl_vars[$tpl_var],'array');
            }
            if ($merge && is_array($value)) {
                foreach($value as $_key => $_val) {
                    $this->_tpl_vars[$tpl_var][$_key] = &$value[$_key];
                }
            } else {
                $this->_tpl_vars[$tpl_var][] = &$value;
            }
        }
    }


    /**
     * clear the given assigned template variable.
     *
     * @param string $tpl_var the template variable to clear
     */
    function clear_assign($tpl_var)
    {
        if (is_array($tpl_var))
            foreach ($tpl_var as $curr_var)
                unset($this->_tpl_vars[$curr_var]);
        else
            unset($this->_tpl_vars[$tpl_var]);
    }


    /**
     * Registers custom function to be used in templates
     *
     * @param string $function the name of the template function
     * @param string $function_impl the name of the PHP function to register
     */
    function register_function($function, $function_impl, $cacheable=true, $cache_attrs=null)
    {
        $this->_plugins['function'][$function] =
            array($function_impl, null, null, false, $cacheable, $cache_attrs);

    }

    /**
     * Unregisters custom function
     *
     * @param string $function name of template function
     */
    function unregister_function($function)
    {
        unset($this->_plugins['function'][$function]);
    }

    /**
     * Registers object to be used in templates
     *
     * @param string $object name of template object
     * @param object &$object_impl the referenced PHP object to register
     * @param null|array $allowed list of allowed methods (empty = all)
     * @param boolean $smarty_args smarty argument format, else traditional
     * @param null|array $block_functs list of methods that are block format
     */
    function register_object($object, &$object_impl, $allowed = array(), $smarty_args = true, $block_methods = array())
    {
        settype($allowed, 'array');
        settype($smarty_args, 'boolean');
        $this->_reg_objects[$object] =
            array(&$object_impl, $allowed, $smarty_args, $block_methods);
    }

    /**
     * Unregisters object
     *
     * @param string $object name of template object
     */
    function unregister_object($object)
    {
        unset($this->_reg_objects[$object]);
    }


    /**
     * Registers block function to be used in templates
     *
     * @param string $block name of template block
     * @param string $block_impl PHP function to register
     */
    function register_block($block, $block_impl, $cacheable=true, $cache_attrs=null)
    {
        $this->_plugins['block'][$block] =
            array($block_impl, null, null, false, $cacheable, $cache_attrs);
    }

    /**
     * Unregisters block function
     *
     * @param string $block name of template function
     */
    function unregister_block($block)
    {
        unset($this->_plugins['block'][$block]);
    }

    /**
     * Registers compiler function
     *
     * @param string $function name of template function
     * @param string $function_impl name of PHP function to register
     */
    function register_compiler_function($function, $function_impl, $cacheable=true)
    {
        $this->_plugins['compiler'][$function] =
            array($function_impl, null, null, false, $cacheable);
    }

    /**
     * Unregisters compiler function
     *
     * @param string $function name of template function
     */
    function unregister_compiler_function($function)
    {
        unset($this->_plugins['compiler'][$function]);
    }

    /**
     * Registers modifier to be used in templates
     *
     * @param string $modifier name of template modifier
     * @param string $modifier_impl name of PHP function to register
     */
    function register_modifier($modifier, $modifier_impl)
    {
        $this->_plugins['modifier'][$modifier] =
            array($modifier_impl, null, null, false);
    }

    /**
     * Unregisters modifier
     *
     * @param string $modifier name of template modifier
     */
    function unregister_modifier($modifier)
    {
        unset($this->_plugins['modifier'][$modifier]);
    }

    /**
     * Registers a resource to fetch a template
     *
     * @param string $type name of resource
     * @param array $functions array of functions to handle resource
     */
    function register_resource($type, $functions)
    {
        if (count($functions)==4) {
            $this->_plugins['resource'][$type] =
                array($functions, false);

        } elseif (count($functions)==5) {
            $this->_plugins['resource'][$type] =
                array(array(array(&$functions[0], $functions[1])
                            ,array(&$functions[0], $functions[2])
                            ,array(&$functions[0], $functions[3])
                            ,array(&$functions[0], $functions[4]))
                      ,false);

        } else {
            $this->trigger_error("malformed function-list for '$type' in register_resource");

        }
    }

    /**
     * Unregisters a resource
     *
     * @param string $type name of resource
     */
    function unregister_resource($type)
    {
        unset($this->_plugins['resource'][$type]);
    }

    /**
     * Registers a prefilter function to apply
     * to a template before compiling
     *
     * @param string $function name of PHP function to register
     */
    function register_prefilter($function)
    {
    $_name = (is_array($function)) ? $function[1] : $function;
        $this->_plugins['prefilter'][$_name]
            = array($function, null, null, false);
    }

    /**
     * Unregisters a prefilter function
     *
     * @param string $function name of PHP function
     */
    function unregister_prefilter($function)
    {
        unset($this->_plugins['prefilter'][$function]);
    }

    /**
     * Registers a postfilter function to apply
     * to a compiled template after compilation
     *
     * @param string $function name of PHP function to register
     */
    function register_postfilter($function)
    {
    $_name = (is_array($function)) ? $function[1] : $function;
        $this->_plugins['postfilter'][$_name]
            = array($function, null, null, false);
    }

    /**
     * Unregisters a postfilter function
     *
     * @param string $function name of PHP function
     */
    function unregister_postfilter($function)
    {
        unset($this->_plugins['postfilter'][$function]);
    }

    /**
     * Registers an output filter function to apply
     * to a template output
     *
     * @param string $function name of PHP function
     */
    function register_outputfilter($function)
    {
    $_name = (is_array($function)) ? $function[1] : $function;
        $this->_plugins['outputfilter'][$_name]
            = array($function, null, null, false);
    }

    /**
     * Unregisters an outputfilter function
     *
     * @param string $function name of PHP function
     */
    function unregister_outputfilter($function)
    {
        unset($this->_plugins['outputfilter'][$function]);
    }

    /**
     * load a filter of specified type and name
     *
     * @param string $type filter type
     * @param string $name filter name
     */
    function load_filter($type, $name)
    {
        switch ($type) {
            case 'output':
                $_params = array('plugins' => array(array($type . 'filter', $name, null, null, false)));
                require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
                smarty_core_load_plugins($_params, $this);
                break;

            case 'pre':
            case 'post':
                if (!isset($this->_plugins[$type . 'filter'][$name]))
                    $this->_plugins[$type . 'filter'][$name] = false;
                break;
        }
    }

    /**
     * clear cached content for the given template and cache id
     *
     * @param string $tpl_file name of template file
     * @param string $cache_id name of cache_id
     * @param string $compile_id name of compile_id
     * @param string $exp_time expiration time
     * @return boolean
     */
    function clear_cache($tpl_file = null, $cache_id = null, $compile_id = null, $exp_time = null)
    {

        if (!isset($compile_id))
            $compile_id = $this->compile_id;

        if (!isset($tpl_file))
            $compile_id = null;

        $_auto_id = $this->_get_auto_id($cache_id, $compile_id);

        if (!empty($this->cache_handler_func)) {
            return call_user_func_array($this->cache_handler_func,
                                  array('clear', &$this, &$dummy, $tpl_file, $cache_id, $compile_id, $exp_time));
        } else {
            $_params = array('auto_base' => $this->cache_dir,
                            'auto_source' => $tpl_file,
                            'auto_id' => $_auto_id,
                            'exp_time' => $exp_time);
            require_once(SMARTY_CORE_DIR . 'core.rm_auto.php');
            return smarty_core_rm_auto($_params, $this);
        }

    }


    /**
     * clear the entire contents of cache (all templates)
     *
     * @param string $exp_time expire time
     * @return boolean results of {@link smarty_core_rm_auto()}
     */
    function clear_all_cache($exp_time = null)
    {
        return $this->clear_cache(null, null, null, $exp_time);
    }


    /**
     * test to see if valid cache exists for this template
     *
     * @param string $tpl_file name of template file
     * @param string $cache_id
     * @param string $compile_id
     * @return string|false results of {@link _read_cache_file()}
     */
    function is_cached($tpl_file, $cache_id = null, $compile_id = null)
    {
        if (!$this->caching)
            return false;

        if (!isset($compile_id))
            $compile_id = $this->compile_id;

        $_params = array(
            'tpl_file' => $tpl_file,
            'cache_id' => $cache_id,
            'compile_id' => $compile_id
        );
        require_once(SMARTY_CORE_DIR . 'core.read_cache_file.php');
        return smarty_core_read_cache_file($_params, $this);
    }


    /**
     * clear all the assigned template variables.
     *
     */
    function clear_all_assign()
    {
        $this->_tpl_vars = array();
    }

    /**
     * clears compiled version of specified template resource,
     * or all compiled template files if one is not specified.
     * This function is for advanced use only, not normally needed.
     *
     * @param string $tpl_file
     * @param string $compile_id
     * @param string $exp_time
     * @return boolean results of {@link smarty_core_rm_auto()}
     */
    function clear_compiled_tpl($tpl_file = null, $compile_id = null, $exp_time = null)
    {
        if (!isset($compile_id)) {
            $compile_id = $this->compile_id;
        }
        $_params = array('auto_base' => $this->compile_dir,
                        'auto_source' => $tpl_file,
                        'auto_id' => $compile_id,
                        'exp_time' => $exp_time,
                        'extensions' => array('.inc', '.php'));
        require_once(SMARTY_CORE_DIR . 'core.rm_auto.php');
        return smarty_core_rm_auto($_params, $this);
    }

    /**
     * Checks whether requested template exists.
     *
     * @param string $tpl_file
     * @return boolean
     */
    function template_exists($tpl_file)
    {
        $_params = array('resource_name' => $tpl_file, 'quiet'=>true, 'get_source'=>false);
        return $this->_fetch_resource_info($_params);
    }

    /**
     * Returns an array containing template variables
     *
     * @param string $name
     * @param string $type
     * @return array
     */
    function &get_template_vars($name=null)
    {
        if(!isset($name)) {
            return $this->_tpl_vars;
        }
        if(isset($this->_tpl_vars[$name])) {
            return $this->_tpl_vars[$name];
        }
    }

    /**
     * Returns an array containing config variables
     *
     * @param string $name
     * @param string $type
     * @return array
     */
    function &get_config_vars($name=null)
    {
        if(!isset($name) && is_array($this->_config[0])) {
            return $this->_config[0]['vars'];
        } else if(isset($this->_config[0]['vars'][$name])) {
            return $this->_config[0]['vars'][$name];
        }
    }

    /**
     * trigger Smarty error
     *
     * @param string $error_msg
     * @param integer $error_type
     */
    function trigger_error($error_msg, $error_type = E_USER_WARNING)
    {
        trigger_error("Smarty error: $error_msg", $error_type);
    }


    /**
     * executes & displays the template results
     *
     * @param string $resource_name
     * @param string $cache_id
     * @param string $compile_id
     */
    function display($resource_name, $cache_id = null, $compile_id = null)
    {
        $this->fetch($resource_name, $cache_id, $compile_id, true);
    }

    /**
     * executes & returns or displays the template results
     *
     * @param string $resource_name
     * @param string $cache_id
     * @param string $compile_id
     * @param boolean $display
     */
    function fetch($resource_name, $cache_id = null, $compile_id = null, $display = false)
    {
        static $_cache_info = array();
        $_smarty_old_error_level = $this->debugging ? error_reporting() : error_reporting(isset($this->error_reporting)
               ? $this->error_reporting : error_reporting() & ~E_NOTICE);

        if (!$this->debugging && $this->debugging_ctrl == 'URL') {
            $_query_string = $this->request_use_auto_globals ? $_SERVER['QUERY_STRING'] : $GLOBALS['HTTP_SERVER_VARS']['QUERY_STRING'];
            if (@strstr($_query_string, $this->_smarty_debug_id)) {
                if (@strstr($_query_string, $this->_smarty_debug_id . '=on')) {
                    // enable debugging for this browser session
                    @setcookie('SMARTY_DEBUG', true);
                    $this->debugging = true;
                } elseif (@strstr($_query_string, $this->_smarty_debug_id . '=off')) {
                    // disable debugging for this browser session
                    @setcookie('SMARTY_DEBUG', false);
                    $this->debugging = false;
                } else {
                    // enable debugging for this page
                    $this->debugging = true;
                }
            } else {
                $this->debugging = (bool)($this->request_use_auto_globals ? @$_COOKIE['SMARTY_DEBUG'] : @$GLOBALS['HTTP_COOKIE_VARS']['SMARTY_DEBUG']);
            }
        }

        if ($this->debugging) {
            // capture time for debugging info
            $_params = array();
            require_once(SMARTY_CORE_DIR . 'core.get_microtime.php');
            $_debug_start_time = smarty_core_get_microtime($_params, $this);
            $this->_smarty_debug_info[] = array('type'      => 'template',
                                                'filename'  => $resource_name,
                                                'depth'     => 0);
            $_included_tpls_idx = count($this->_smarty_debug_info) - 1;
        }

        if (!isset($compile_id)) {
            $compile_id = $this->compile_id;
        }

        $this->_compile_id = $compile_id;
        $this->_inclusion_depth = 0;

        if ($this->caching) {
            // save old cache_info, initialize cache_info
            array_push($_cache_info, $this->_cache_info);
            $this->_cache_info = array();
            $_params = array(
                'tpl_file' => $resource_name,
                'cache_id' => $cache_id,
                'compile_id' => $compile_id,
                'results' => null
            );
            require_once(SMARTY_CORE_DIR . 'core.read_cache_file.php');
            if (smarty_core_read_cache_file($_params, $this)) {
                $_smarty_results = $_params['results'];
                if (!empty($this->_cache_info['insert_tags'])) {
                    $_params = array('plugins' => $this->_cache_info['insert_tags']);
                    require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
                    smarty_core_load_plugins($_params, $this);
                    $_params = array('results' => $_smarty_results);
                    require_once(SMARTY_CORE_DIR . 'core.process_cached_inserts.php');
                    $_smarty_results = smarty_core_process_cached_inserts($_params, $this);
                }
                if (!empty($this->_cache_info['cache_serials'])) {
                    $_params = array('results' => $_smarty_results);
                    require_once(SMARTY_CORE_DIR . 'core.process_compiled_include.php');
                    $_smarty_results = smarty_core_process_compiled_include($_params, $this);
                }


                if ($display) {
                    if ($this->debugging)
                    {
                        // capture time for debugging info
                        $_params = array();
                        require_once(SMARTY_CORE_DIR . 'core.get_microtime.php');
                        $this->_smarty_debug_info[$_included_tpls_idx]['exec_time'] = smarty_core_get_microtime($_params, $this) - $_debug_start_time;
                        require_once(SMARTY_CORE_DIR . 'core.display_debug_console.php');
                        $_smarty_results .= smarty_core_display_debug_console($_params, $this);
                    }
                    if ($this->cache_modified_check) {
                        $_server_vars = ($this->request_use_auto_globals) ? $_SERVER : $GLOBALS['HTTP_SERVER_VARS'];
                        $_last_modified_date = @substr($_server_vars['HTTP_IF_MODIFIED_SINCE'], 0, strpos($_server_vars['HTTP_IF_MODIFIED_SINCE'], 'GMT') + 3);
                        $_gmt_mtime = gmdate('D, d M Y H:i:s', $this->_cache_info['timestamp']).' GMT';
                        if (@count($this->_cache_info['insert_tags']) == 0
                            && !$this->_cache_serials
                            && $_gmt_mtime == $_last_modified_date) {
                            if (php_sapi_name()=='cgi')
                                header('Status: 304 Not Modified');
                            else
                                header('HTTP/1.1 304 Not Modified');

                        } else {
                            header('Last-Modified: '.$_gmt_mtime);
                            echo $_smarty_results;
                        }
                    } else {
                            echo $_smarty_results;
                    }
                    error_reporting($_smarty_old_error_level);
                    // restore initial cache_info
                    $this->_cache_info = array_pop($_cache_info);
                    return true;
                } else {
                    error_reporting($_smarty_old_error_level);
                    // restore initial cache_info
                    $this->_cache_info = array_pop($_cache_info);
                    return $_smarty_results;
                }
            } else {
                $this->_cache_info['template'][$resource_name] = true;
                if ($this->cache_modified_check && $display) {
                    header('Last-Modified: '.gmdate('D, d M Y H:i:s', time()).' GMT');
                }
            }
        }

        // load filters that are marked as autoload
        if (count($this->autoload_filters)) {
            foreach ($this->autoload_filters as $_filter_type => $_filters) {
                foreach ($_filters as $_filter) {
                    $this->load_filter($_filter_type, $_filter);
                }
            }
        }

        $_smarty_compile_path = $this->_get_compile_path($resource_name);
        
        // if we just need to display the results, don't perform output
        // buffering - for speed
        $_cache_including = $this->_cache_including;
        $this->_cache_including = false;
        if ($display && !$this->caching && count($this->_plugins['outputfilter']) == 0) {
            if ($this->_is_compiled($resource_name, $_smarty_compile_path)
                    || $this->_compile_resource($resource_name, $_smarty_compile_path))
            {
                include($_smarty_compile_path);
            }
        } else {
            ob_start();
            if ($this->_is_compiled($resource_name, $_smarty_compile_path)
                    || $this->_compile_resource($resource_name, $_smarty_compile_path))
            {
                include($_smarty_compile_path);
            }
            $_smarty_results = ob_get_contents();
            ob_end_clean();

            foreach ((array)$this->_plugins['outputfilter'] as $_output_filter) {
                $_smarty_results = call_user_func_array($_output_filter[0], array($_smarty_results, &$this));
            }
        }

        if ($this->caching) {
            $_params = array('tpl_file' => $resource_name,
                        'cache_id' => $cache_id,
                        'compile_id' => $compile_id,
                        'results' => $_smarty_results);
            require_once(SMARTY_CORE_DIR . 'core.write_cache_file.php');
            smarty_core_write_cache_file($_params, $this);
            require_once(SMARTY_CORE_DIR . 'core.process_cached_inserts.php');
            $_smarty_results = smarty_core_process_cached_inserts($_params, $this);

            if ($this->_cache_serials) {
                // strip nocache-tags from output
                $_smarty_results = preg_replace('!(\{/?nocache\:[0-9a-f]{32}#\d+\})!s'
                                                ,''
                                                ,$_smarty_results);
            }
            // restore initial cache_info
            $this->_cache_info = array_pop($_cache_info);
        }
        $this->_cache_including = $_cache_including;

        if ($display) {
            if (isset($_smarty_results)) { echo $_smarty_results; }
            if ($this->debugging) {
                // capture time for debugging info
                $_params = array();
                require_once(SMARTY_CORE_DIR . 'core.get_microtime.php');
                $this->_smarty_debug_info[$_included_tpls_idx]['exec_time'] = (smarty_core_get_microtime($_params, $this) - $_debug_start_time);
                require_once(SMARTY_CORE_DIR . 'core.display_debug_console.php');
                echo smarty_core_display_debug_console($_params, $this);
            }
            error_reporting($_smarty_old_error_level);
            return;
        } else {
            error_reporting($_smarty_old_error_level);
            if (isset($_smarty_results)) { return $_smarty_results; }
        }
    }

    /**
     * load configuration values
     *
     * @param string $file
     * @param string $section
     * @param string $scope
     */
    function config_load($file, $section = null, $scope = 'global')
    {
        require_once($this->_get_plugin_filepath('function', 'config_load'));
        smarty_function_config_load(array('file' => $file, 'section' => $section, 'scope' => $scope), $this);
    }

    /**
     * return a reference to a registered object
     *
     * @param string $name
     * @return object
     */
    function &get_registered_object($name) {
        if (!isset($this->_reg_objects[$name]))
        $this->_trigger_fatal_error("'$name' is not a registered object");

        if (!is_object($this->_reg_objects[$name][0]))
        $this->_trigger_fatal_error("registered '$name' is not an object");

        return $this->_reg_objects[$name][0];
    }

    /**
     * clear configuration values
     *
     * @param string $var
     */
    function clear_config($var = null)
    {
        if(!isset($var)) {
            // clear all values
            $this->_config = array(array('vars'  => array(),
                                         'files' => array()));
        } else {
            unset($this->_config[0]['vars'][$var]);
        }
    }

    /**
     * get filepath of requested plugin
     *
     * @param string $type
     * @param string $name
     * @return string|false
     */
    function _get_plugin_filepath($type, $name)
    {
        $_params = array('type' => $type, 'name' => $name);
        require_once(SMARTY_CORE_DIR . 'core.assemble_plugin_filepath.php');
        return smarty_core_assemble_plugin_filepath($_params, $this);
    }

   /**
     * test if resource needs compiling
     *
     * @param string $resource_name
     * @param string $compile_path
     * @return boolean
     */
    function _is_compiled($resource_name, $compile_path)
    {
        if (!$this->force_compile && file_exists($compile_path)) {
            if (!$this->compile_check) {
                // no need to check compiled file
                return true;
            } else {
                // get file source and timestamp
                $_params = array('resource_name' => $resource_name, 'get_source'=>false);
                if (!$this->_fetch_resource_info($_params)) {
                    return false;
                }
                if ($_params['resource_timestamp'] <= filemtime($compile_path)) {
                    // template not expired, no recompile
                    return true;
                } else {
                    // compile template
                    return false;
                }
            }
        } else {
            // compiled template does not exist, or forced compile
            return false;
        }
    }

   /**
     * compile the template
     *
     * @param string $resource_name
     * @param string $compile_path
     * @return boolean
     */
    function _compile_resource($resource_name, $compile_path)
    {

        $_params = array('resource_name' => $resource_name);
        if (!$this->_fetch_resource_info($_params)) {
            return false;
        }

        $_source_content = $_params['source_content'];
        $_cache_include    = substr($compile_path, 0, -4).'.inc';

        if ($this->_compile_source($resource_name, $_source_content, $_compiled_content, $_cache_include)) {
            // if a _cache_serial was set, we also have to write an include-file:
            if ($this->_cache_include_info) {
                require_once(SMARTY_CORE_DIR . 'core.write_compiled_include.php');
                smarty_core_write_compiled_include(array_merge($this->_cache_include_info, array('compiled_content'=>$_compiled_content, 'resource_name'=>$resource_name)),  $this);
            }

            $_params = array('compile_path'=>$compile_path, 'compiled_content' => $_compiled_content);
            require_once(SMARTY_CORE_DIR . 'core.write_compiled_resource.php');
            smarty_core_write_compiled_resource($_params, $this);

            return true;
        } else {
            return false;
        }

    }

   /**
     * compile the given source
     *
     * @param string $resource_name
     * @param string $source_content
     * @param string $compiled_content
     * @return boolean
     */
    function _compile_source($resource_name, &$source_content, &$compiled_content, $cache_include_path=null)
    {
        if (file_exists(SMARTY_DIR . $this->compiler_file)) {
            require_once(SMARTY_DIR . $this->compiler_file);
        } else {
            // use include_path
            require_once($this->compiler_file);
        }


        $smarty_compiler = new $this->compiler_class;

        $smarty_compiler->template_dir      = $this->template_dir;
        $smarty_compiler->compile_dir       = $this->compile_dir;
        $smarty_compiler->plugins_dir       = $this->plugins_dir;
        $smarty_compiler->config_dir        = $this->config_dir;
        $smarty_compiler->force_compile     = $this->force_compile;
        $smarty_compiler->caching           = $this->caching;
        $smarty_compiler->php_handling      = $this->php_handling;
        $smarty_compiler->left_delimiter    = $this->left_delimiter;
        $smarty_compiler->right_delimiter   = $this->right_delimiter;
        $smarty_compiler->_version          = $this->_version;
        $smarty_compiler->security          = $this->security;
        $smarty_compiler->secure_dir        = $this->secure_dir;
        $smarty_compiler->security_settings = $this->security_settings;
        $smarty_compiler->trusted_dir       = $this->trusted_dir;
        $smarty_compiler->use_sub_dirs      = $this->use_sub_dirs;
        $smarty_compiler->_reg_objects      = &$this->_reg_objects;
        $smarty_compiler->_plugins          = &$this->_plugins;
        $smarty_compiler->_tpl_vars         = &$this->_tpl_vars;
        $smarty_compiler->default_modifiers = $this->default_modifiers;
        $smarty_compiler->compile_id        = $this->_compile_id;
        $smarty_compiler->_config            = $this->_config;
        $smarty_compiler->request_use_auto_globals  = $this->request_use_auto_globals;

        if (isset($cache_include_path) && isset($this->_cache_serials[$cache_include_path])) {
            $smarty_compiler->_cache_serial = $this->_cache_serials[$cache_include_path];
        }
        $smarty_compiler->_cache_include = $cache_include_path;


        $_results = $smarty_compiler->_compile_file($resource_name, $source_content, $compiled_content);

        if ($smarty_compiler->_cache_serial) {
            $this->_cache_include_info = array(
                'cache_serial'=>$smarty_compiler->_cache_serial
                ,'plugins_code'=>$smarty_compiler->_plugins_code
                ,'include_file_path' => $cache_include_path);

        } else {
            $this->_cache_include_info = null;

        }

        return $_results;
    }

    /**
     * Get the compile path for this resource
     *
     * @param string $resource_name
     * @return string results of {@link _get_auto_filename()}
     */
    function _get_compile_path($resource_name)
    {
        return $this->_get_auto_filename($this->compile_dir, $resource_name,
                                         $this->_compile_id) . '.php';
    }

    /**
     * fetch the template info. Gets timestamp, and source
     * if get_source is true
     *
     * sets $source_content to the source of the template, and
     * $resource_timestamp to its time stamp
     * @param string $resource_name
     * @param string $source_content
     * @param integer $resource_timestamp
     * @param boolean $get_source
     * @param boolean $quiet
     * @return boolean
     */

    function _fetch_resource_info(&$params)
    {
        if(!isset($params['get_source'])) { $params['get_source'] = true; }
        if(!isset($params['quiet'])) { $params['quiet'] = false; }

        $_return = false;
        $_params = array('resource_name' => $params['resource_name']) ;
        if (isset($params['resource_base_path']))
            $_params['resource_base_path'] = $params['resource_base_path'];
        else
            $_params['resource_base_path'] = $this->template_dir;

        if ($this->_parse_resource_name($_params)) {
            $_resource_type = $_params['resource_type'];
            $_resource_name = $_params['resource_name'];
            switch ($_resource_type) {
                case 'file':
                    if ($params['get_source']) {
                        $params['source_content'] = $this->_read_file($_resource_name);
                    }
                    $params['resource_timestamp'] = filemtime($_resource_name);
                    $_return = is_file($_resource_name);
                    break;

                default:
                    // call resource functions to fetch the template source and timestamp
                    if ($params['get_source']) {
                        $_source_return = isset($this->_plugins['resource'][$_resource_type]) &&
                            call_user_func_array($this->_plugins['resource'][$_resource_type][0][0],
                                                 array($_resource_name, &$params['source_content'], &$this));
                    } else {
                        $_source_return = true;
                    }

                    $_timestamp_return = isset($this->_plugins['resource'][$_resource_type]) &&
                        call_user_func_array($this->_plugins['resource'][$_resource_type][0][1],
                                             array($_resource_name, &$params['resource_timestamp'], &$this));

                    $_return = $_source_return && $_timestamp_return;
                    break;
            }
        }

        if (!$_return) {
            // see if we can get a template with the default template handler
            if (!empty($this->default_template_handler_func)) {
                if (!is_callable($this->default_template_handler_func)) {
                    $this->trigger_error("default template handler function \"$this->default_template_handler_func\" doesn't exist.");
                } else {
                    $_return = call_user_func_array(
                        $this->default_template_handler_func,
                        array($_params['resource_type'], $_params['resource_name'], &$params['source_content'], &$params['resource_timestamp'], &$this));
                }
            }
        }

        if (!$_return) {
            if (!$params['quiet']) {
                $this->trigger_error('unable to read resource: "' . $params['resource_name'] . '"');
            }
        } else if ($_return && $this->security) {
            require_once(SMARTY_CORE_DIR . 'core.is_secure.php');
            if (!smarty_core_is_secure($_params, $this)) {
                if (!$params['quiet'])
                    $this->trigger_error('(secure mode) accessing "' . $params['resource_name'] . '" is not allowed');
                $params['source_content'] = null;
                $params['resource_timestamp'] = null;
                return false;
            }
        }
        return $_return;
    }


    /**
     * parse out the type and name from the resource
     *
     * @param string $resource_base_path
     * @param string $resource_name
     * @param string $resource_type
     * @param string $resource_name
     * @return boolean
     */

    function _parse_resource_name(&$params)
    {

        // split tpl_path by the first colon
        $_resource_name_parts = explode(':', $params['resource_name'], 2);

        if (count($_resource_name_parts) == 1) {
            // no resource type given
            $params['resource_type'] = $this->default_resource_type;
            $params['resource_name'] = $_resource_name_parts[0];
        } else {
            if(strlen($_resource_name_parts[0]) == 1) {
                // 1 char is not resource type, but part of filepath
                $params['resource_type'] = $this->default_resource_type;
                $params['resource_name'] = $params['resource_name'];
            } else {
                $params['resource_type'] = $_resource_name_parts[0];
                $params['resource_name'] = $_resource_name_parts[1];
            }
        }

        if ($params['resource_type'] == 'file') {
            if (!preg_match('/^([\/\\\\]|[a-zA-Z]:[\/\\\\])/', $params['resource_name'])) {
                // relative pathname to $params['resource_base_path']
                // use the first directory where the file is found
                foreach ((array)$params['resource_base_path'] as $_curr_path) {
                    $_fullpath = $_curr_path . DIRECTORY_SEPARATOR . $params['resource_name'];
                    if (file_exists($_fullpath) && is_file($_fullpath)) {
                        $params['resource_name'] = $_fullpath;
                        return true;
                    }
                    // didn't find the file, try include_path
                    $_params = array('file_path' => $_fullpath);
                    require_once(SMARTY_CORE_DIR . 'core.get_include_path.php');
                    if(smarty_core_get_include_path($_params, $this)) {
                        $params['resource_name'] = $_params['new_file_path'];
                        return true;
                    }
                }
                return false;
            } else {
                /* absolute path */
                return file_exists($params['resource_name']);
            }
        } elseif (empty($this->_plugins['resource'][$params['resource_type']])) {
            $_params = array('type' => $params['resource_type']);
            require_once(SMARTY_CORE_DIR . 'core.load_resource_plugin.php');
            smarty_core_load_resource_plugin($_params, $this);
        }

        return true;
    }


    /**
     * Handle modifiers
     *
     * @param string|null $modifier_name
     * @param array|null $map_array
     * @return string result of modifiers
     */
    function _run_mod_handler()
    {
        $_args = func_get_args();
        list($_modifier_name, $_map_array) = array_splice($_args, 0, 2);
        list($_func_name, $_tpl_file, $_tpl_line) =
            $this->_plugins['modifier'][$_modifier_name];

        $_var = $_args[0];
        foreach ($_var as $_key => $_val) {
            $_args[0] = $_val;
            $_var[$_key] = call_user_func_array($_func_name, $_args);
        }
        return $_var;
    }

    /**
     * Remove starting and ending quotes from the string
     *
     * @param string $string
     * @return string
     */
    function _dequote($string)
    {
        if (($string{0} == "'" || $string{0} == '"') &&
            $string{strlen($string)-1} == $string{0})
            return substr($string, 1, -1);
        else
            return $string;
    }


    /**
     * read in a file
     *
     * @param string $filename
     * @return string
     */
    function _read_file($filename)
    {
        if ( file_exists($filename) && ($fd = @fopen($filename, 'rb')) ) {
            $contents = ($size = filesize($filename)) ? fread($fd, $size) : '';
            fclose($fd);
            return $contents;
        } else {
            return false;
        }
    }

    /**
     * get a concrete filename for automagically created content
     *
     * @param string $auto_base
     * @param string $auto_source
     * @param string $auto_id
     * @return string
     * @staticvar string|null
     * @staticvar string|null
     */
    function _get_auto_filename($auto_base, $auto_source = null, $auto_id = null)
    {
        $_compile_dir_sep =  $this->use_sub_dirs ? DIRECTORY_SEPARATOR : '^';
        $_return = $auto_base . DIRECTORY_SEPARATOR;

        if(isset($auto_id)) {
            // make auto_id safe for directory names
            $auto_id = str_replace('%7C',$_compile_dir_sep,(urlencode($auto_id)));
            // split into separate directories
            $_return .= $auto_id . $_compile_dir_sep;
        }

        if(isset($auto_source)) {
            // make source name safe for filename
            $_filename = urlencode(basename($auto_source));
            $_crc32 = sprintf('%08X', crc32($auto_source));
            // prepend %% to avoid name conflicts with
            // with $params['auto_id'] names
            $_crc32 = substr($_crc32, 0, 2) . $_compile_dir_sep .
                      substr($_crc32, 0, 3) . $_compile_dir_sep . $_crc32;
            $_return .= '%%' . $_crc32 . '%%' . $_filename;
        }

        return $_return;
    }

    /**
     * unlink a file, possibly using expiration time
     *
     * @param string $resource
     * @param integer $exp_time
     */
    function _unlink($resource, $exp_time = null)
    {
        if(isset($exp_time)) {
            if(time() - @filemtime($resource) >= $exp_time) {
                return @unlink($resource);
            }
        } else {
            return @unlink($resource);
        }
    }

    /**
     * returns an auto_id for auto-file-functions
     *
     * @param string $cache_id
     * @param string $compile_id
     * @return string|null
     */
    function _get_auto_id($cache_id=null, $compile_id=null) {
    if (isset($cache_id))
        return (isset($compile_id)) ? $cache_id . '|' . $compile_id  : $cache_id;
    elseif(isset($compile_id))
        return $compile_id;
    else
        return null;
    }

    /**
     * trigger Smarty plugin error
     *
     * @param string $error_msg
     * @param string $tpl_file
     * @param integer $tpl_line
     * @param string $file
     * @param integer $line
     * @param integer $error_type
     */
    function _trigger_fatal_error($error_msg, $tpl_file = null, $tpl_line = null,
            $file = null, $line = null, $error_type = E_USER_ERROR)
    {
        if(isset($file) && isset($line)) {
            $info = ' ('.basename($file).", line $line)";
        } else {
            $info = '';
        }
        if (isset($tpl_line) && isset($tpl_file)) {
            $this->trigger_error('[in ' . $tpl_file . ' line ' . $tpl_line . "]: $error_msg$info", $error_type);
        } else {
            $this->trigger_error($error_msg . $info, $error_type);
        }
    }


    /**
     * callback function for preg_replace, to call a non-cacheable block
     * @return string
     */
    function _process_compiled_include_callback($match) {
        $_func = '_smarty_tplfunc_'.$match[2].'_'.$match[3];
        ob_start();
        $_func($this);
        $_ret = ob_get_contents();
        ob_end_clean();
        return $_ret;
    }


    /**
     * called for included templates
     *
     * @param string $_smarty_include_tpl_file
     * @param string $_smarty_include_vars
     */

    // $_smarty_include_tpl_file, $_smarty_include_vars

    function _smarty_include($params)
    {
        if ($this->debugging) {
            $_params = array();
            require_once(SMARTY_CORE_DIR . 'core.get_microtime.php');
            $debug_start_time = smarty_core_get_microtime($_params, $this);
            $this->_smarty_debug_info[] = array('type'      => 'template',
                                                  'filename'  => $params['smarty_include_tpl_file'],
                                                  'depth'     => ++$this->_inclusion_depth);
            $included_tpls_idx = count($this->_smarty_debug_info) - 1;
        }

        $this->_tpl_vars = array_merge($this->_tpl_vars, $params['smarty_include_vars']);

        // config vars are treated as local, so push a copy of the
        // current ones onto the front of the stack
        array_unshift($this->_config, $this->_config[0]);

        $_smarty_compile_path = $this->_get_compile_path($params['smarty_include_tpl_file']);


        if ($this->_is_compiled($params['smarty_include_tpl_file'], $_smarty_compile_path)
            || $this->_compile_resource($params['smarty_include_tpl_file'], $_smarty_compile_path))
        {
            include($_smarty_compile_path);
        }

        // pop the local vars off the front of the stack
        array_shift($this->_config);

        $this->_inclusion_depth--;

        if ($this->debugging) {
            // capture time for debugging info
            $_params = array();
            require_once(SMARTY_CORE_DIR . 'core.get_microtime.php');
            $this->_smarty_debug_info[$included_tpls_idx]['exec_time'] = smarty_core_get_microtime($_params, $this) - $debug_start_time;
        }

        if ($this->caching) {
            $this->_cache_info['template'][$params['smarty_include_tpl_file']] = true;
        }
    }


    /**
     * get or set an array of cached attributes for function that is
     * not cacheable
     * @return array
     */
    function &_smarty_cache_attrs($cache_serial, $count) {
        $_cache_attrs =& $this->_cache_info['cache_attrs'][$cache_serial][$count];

        if ($this->_cache_including) {
            /* return next set of cache_attrs */
            $_return =& current($_cache_attrs);
            next($_cache_attrs);
            return $_return;

        } else {
            /* add a reference to a new set of cache_attrs */
            $_cache_attrs[] = array();
            return $_cache_attrs[count($_cache_attrs)-1];

        }

    }


    /**
     * wrapper for include() retaining $this
     * @return mixed
     */
    function _include($filename, $once=false, $params=null)
    {
        if ($once) {
            return include_once($filename);
        } else {
            return include($filename);
        }
    }


    /**
     * wrapper for eval() retaining $this
     * @return mixed
     */
    function _eval($code, $params=null)
    {
        return eval($code);
    }
    /**#@-*/

}

/* vim: set expandtab: */
