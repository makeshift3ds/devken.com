<?php
/**
 * Registry API
 *
 * This class contains the common interface to variables and classes.
 *
 * @author      Kenneth Elliott <kenelliottmc@gmail.com>
 * @copyright   Copyright &copy; 2010 Kenneth Elliott <kenelliottmc@gmail.com>
 * @category    Manataria
 * @package     Registry
 * @since       Manataria 1.1.2
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
    	if(!isset(self::$_store[$key]) || !is_array(self::$_store[$key])) trigger_error('call to getParam failed because key['.$key.'] does not exist or is not an array');
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
?>
