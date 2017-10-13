<?php
/**
 * MySQL Wrapper
 *
 * This class contains a uniform wrapper for communicating with a MySQL database.
 * Now build classes with the same interface for additional DBMS's ;)
 *
 * @author      Kenneth Elliott <kenelliottmc@gmail.com>
 * @copyright   Copyright &copy; 2010 Kenneth Elliott <kenelliottmc@gmail.com>
 * @category    Manataria
 * @package     MySQL_Adapter
 * @since       Manataria 1.1.2
 */
class MySQL_Adapter {
	var $db;
	var $link_id;
	var $error;
	var $queries;
	var $queryCount;
	
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
		$host = $this->not_null($host) ? $host : '';
		$user = $this->not_null($user) ? $user : '';
		$pass = $this->not_null($pass) ? $pass : '';
		$this->db = $this->not_null($db) ? $db : '';
		$this->connect_db($host, $user, $pass);
		$this->select_db();
		$this->error = Registry::getKey('Error');
		$this->queries = array();
		$this->queryCount = 0;
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
		$start = microtime();
		$resource = mysql_query($query, $this->link_id)
				or $this->error->mysqlError($query);
		$logdata = array('sql'=>$query,'time'=>(microtime()-$start)*1000);
		array_push($this->queries, $logdata);
		$this->queryCount++;
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

?>