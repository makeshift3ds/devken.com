<?php
/**
 * Admin API
 *
 * Administration controls. This class should be kept restricted because it contains upload functions and stuff.
 *
 * @author      Kenneth Elliott <kenelliottmc@gmail.com>
 * @copyright   Copyright &copy; 2010 Kenneth Elliott <kenelliottmc@gmail.com>
 * @category    Manataria
 * @package     Admin
 * @since       Manataria 1.1.2
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
?>
