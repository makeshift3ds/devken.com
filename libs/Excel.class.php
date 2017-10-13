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
class Excel {
	protected $alpha;
	protected $Core;

	// pass the core object
	public function __construct() {
		$this->alpha = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
		$this->Core = Registry::getKey('Core');
	}

	//*** EXCEL FUNCTIONS * xls_reader class paggard.com ***//

	// create a test multi-dimensional array
	public function testMda($rows=10,$cols=10) {
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

	// get range from two-dimensional array using A1 - B10 format
	public function getCellRange($arr=array(),$start=null,$end=null) {;
		if(count($arr) <= 1) return $arr;
		if(!$this->verifyRangeFormat($start)) $start = 'A1';
		if(!$this->verifyRangeFormat($end)) $end = $this->index2range(count($arr)-1);
		list($start_row,$start_col) = $this->range2index($start);
		list($end_row,$end_col) = $this->range2index($end);

		$data = array();

		if(is_null($start_row) || is_null($end_row)) return $data; // it is empty

		while($start_row<=$end_row) {
			$length = $end_col - $start_col;
			if(is_array($arr[$start_row])) $data[] = array_splice($arr[$start_row],$start_col,++$length);
			$start_row++;
		}

		return $data;
	}

	// change a range to index values
	public function range2index ($range) {
		$alphacol = substr($range,0,1);
		$rowid = substr($range,1);
		if(!is_string($alphacol) || !is_numeric($rowid)) return array(null,null);
		$colid = array_search(strtoupper($alphacol),$this->alpha);
		return array($rowid-1,$colid);
	}

	// change an index to a range
	public function index2range($col) {
		return $this->alpha[$col];
	}

	public function verifyRangeFormat($r) {
		return preg_match('/^\w*\d*$/',$r);
	}
}
?>
