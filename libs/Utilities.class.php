<?php
/**
 * Utilities API
 *
 * Helper functions that are used throughout the site to filter,access and organize data.
 *
 * @author      Kenneth Elliott <kenelliottmc@gmail.com>
 * @copyright   Copyright &copy; 2010 Kenneth Elliott <kenelliottmc@gmail.com>
 * @category    Manataria
 * @package     Core
 * @since       Manataria 1.1.2
 */
class Utilities {

	/**
	* santize data of sql injection attacks
	*
	* @param  mixed   $input	array or string to be sanitized
	* @param  mixed   $input	array or string to be returned if sanitization returns an empty string
	* @return mixed
	* @access public
	*/
	public static function sanitize($input,$def=null){
		if(is_array($input)){
			foreach($input as $k=>$i) $output[$k]=self::sanitize($i);
		} else{
			if(get_magic_quotes_gpc()) $input=stripslashes($input);
			$output=mysql_real_escape_string($input);
		}
		return $output ? $output : $def;
	}


	/**
	* provide an array of values in the order of precedence. idReturner will check them all and return the value with the highest precedence or the default supplied, or null.
	*
	* @param  mixed   $arr	array of values to search
	* @param  mixed   $def	default value if array contains no elements
	* @example idReturner(array($_GET['id'],$_SESSION['id']),999);
	* @return string
	* @access public
	*/
	public static function idReturner($arr,$def=null) {
		if(!is_array($arr) || count($arr)<=1) return $arr;
		$arr = array_reverse($arr);
		$id=null;
		foreach($arr as $k=>$v) if(!is_null($v) && $v != "") $id = $v;
		return !is_null($id) ? $id : $def;
	}

	/**
	* get the contents of a single directory (does not include filepath in return value). Can also provide an array of extensions ie array('xls','txt','csv') and the rest of the files will be filtered out.
	*
	* @param  mixed   $input	array or string to be sanitized
	* @param  mixed   $input	array or string to be returned if sanitization returns an empty string
	* @example getDir('/images',array('jpg','gif','png'));
	* @return array
	* @access public
	*/
	public static function getDir($start_dir='.',$filter=array()) {
		$files = array();
		if (is_dir($start_dir)) {
			$fh = opendir($start_dir);
			while (($file = readdir($fh)) !== false) {
				if(strcmp($file,'.')==0 || strcmp($file,'..')==0) continue;
				if(!is_dir($start_dir.'/'.$file) && (!count($filter) || array_search(self::getFileExtension($start_dir.'/'.$file),$filter)!==false)) array_push($files, $file);
			}
			closedir($fh);
		} else {
			return null;
		}
		return $files;
	}

	/**
	* get the contents of a directory and its child directories. returns filepath (can be used to return files with paths from a single directory. 
	* can also provide an array of extensions ie array('xls','txt','csv') and the rest of the files will be filtered out.
	*
	* @param  mixed   $input	array or string to be sanitized
	* @param  mixed   $input	array or string to be returned if sanitization returns an empty string
	* @example getDir('/images',array('jpg','gif','png'));
	* @return array
	* @access public
	*/
	public static function getDirRecursive($start_dir='.',$filter=array()) {
		$files = array();
		if (is_dir($start_dir)) {
			$fh = opendir($start_dir);
			while (($file = readdir($fh)) !== false) {
				if(strcmp($file,'.')==0 || strcmp($file,'..')==0) continue;
				$filepath = $start_dir . '/' . $file;
				if(is_dir($filepath)) {
					$files = array_merge($files, self::getDirRecursive($filepath));
				} else {
					if(!is_dir($start_dir.'/'.$file) && (!count($filter) || array_search(self::getFileExtension($start_dir.'/'.$file),$filter)!==false)) array_push($files, $filepath);
				}
			}
			closedir($fh);
		} else {
			$files = false;
		}
		return $files;
	}

	/**
	* just return the extension
	*
	* @param  string   $filename	name of the file with extension
	* @example getDir('myfile.png');	// returns 'png'
	* @return string
	* @access public
	*/
	public static function getFileExtension($filename) {
		$path_info = pathinfo($filename);
		return $path_info['extension'];
	}


	/**
	* easy benchmarking
	* start with $start = Utilities::benchmark();
	* end with echo Utilities::benchmark($start);
	* it will print out the seconds
	*
	* @param  timestamp   $time	time stamp that was generated from benchmark
	* @example $start_time = self::benchmark(); $execution_time = self::benchmark($start_time);
	* @return microseconds
	* @access public
	*/
	public static function benchmark($time=null) {
		$now = microtime();
		if(!$time)	return $now;
		return round($now - $time, 3);
	}

	/**
	* change an array of key_values into two seperate arrays for use in inserting into a single table
	* key1,key2,key3 - "value1","value2","value3"
	*
	* @param  array  $data	array of key => values to be split up
	* @example sqlKeyValues(array('firstname'=>'Ken',lastname=>'Elliott'));
	* @return array
	* @access public
	*/
	public static function sqlKeyValues($data) {
		function quote($v) {
			return '"'.$v.'"';
		}
		$keys = join(',',array_keys($data));
		$values = join(',',array_map('quote',array_values($data)));
		return array('keys'=>$keys,'values'=>$values);
	}
	
	/**
	* search for a file and return its location
	*
	* @param  string   $mask	regular expression 
	* @example fileSearch('/'.$_REQUEST['file'].'/','media',1);
	* @return array
	* @access public
	*/
	public static function fileSearch($mask,$dir,$level) {
		if($level > 3) return;	// 3 levels max
		$return_me = array();
		if (is_dir($dir)) {
			if ($dh = opendir($dir)) {
				while (($file = readdir($dh)) !== false) {
						if(is_dir($dir."\\".$file) && $file != '.' && $file != '..'){
								$test_return = Utilities::fileSearch($mask,$dir."\\".$file,$level+1);
								if(is_array($test_return)){
										$temp = array_merge($test_return,$return_me);
										$return_me = $temp;
								}
								if(is_string($test_return)) array_push($return_me,$test_return);
						} elseif (preg_match($mask,$file)) {
							array_push($return_me,$dir."\\".$file);
						}
				}
				closedir($dh);
			}
		}
    	return $return_me;
	}
	
	/**
	* Adaptation of parse_url
	*
	* @param  string   $var	url string 
	* @example parseQuery('http://www.youtube.com/watch?v=oHg5SJYRHA0');
	* @return array
	* @access public
	*/
	public static function parseQuery($str) {
		$parsed_var  = parse_url($str, PHP_URL_QUERY);
		$parsed_var  = html_entity_decode($parsed_var);
		$parsed_var  = explode('&', $parsed_var);
		$arr  = array();
		foreach($parsed_var as $val) {
			$x = explode('=', $val);
			$arr[$x[0]] = $x[1];
		}
		unset($val, $x, $parsed_var);
		return array('host'=>parse_url($str, PHP_URL_HOST),
							'values' => $arr);
	 }
	
}

?>