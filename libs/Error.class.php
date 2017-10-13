<?php 
/**
 * Error API
 *
 * Common interface to Errors. Will log to database,email and alert.
 *
 * @author      Kenneth Elliott <kenelliottmc@gmail.com>
 * @copyright   Copyright &copy; 2010 Kenneth Elliott <kenelliottmc@gmail.com>
 * @category    Manataria
 * @package     Error
 * @since       Manataria 1.1.2
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



?>