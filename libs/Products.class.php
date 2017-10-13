<?php
//
// Manataria Website Platform - Eats the seaweeds
//
// Copyright 2008 Kenneth Elliott. All rights reserved.
//

/**
 * Products API
 *
 * This class contains the product methods for the website
 *
 * @author      Kenneth Elliott <kenelliottmc@gmail.com>
 * @copyright   Copyright &copy; 2008 Kenneth Elliott <kenelliottmc@gmail.com>
 * @category    Manataria
 * @package     Core
 * @since       Manataria 0.0.1
 */

class Products {
	/**
	* page id : current page
	* @var integer
	*/
	protected $Core;

	/**
	* control id - this is used by page_views to know which content is being viewed (not the same as page id)
	* @var integer
	*/
	protected $Admin;
		
	/**
	* control id - this is used by page_views to know which content is being viewed (not the same as page id)
	* @var integer
	*/
	protected $dbh;

	/**
	* Constructor.
	*
	* @access public
	*/
	public function Products() {
		$this->dbh = Registry::getKey('Database');
		$this->Core = Registry::getKey('Core');
		$this->Admin = Registry::getKey('Admin');
	}
}
?>
