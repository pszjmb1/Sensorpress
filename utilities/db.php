<?php

/* Provides Horz_JMB_SensorDatabase which interacts with the SensorPress mysql DB.
    Copyright (C) 2012  Jesse Blum (JMB)

    This program is free software: you can redistribute it and/or modify
    published by the Free Software Foundation, either version 3 of the
    License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

	include(HORZ_SP_UTILITES_DIR . '/settings.php');
	require_once(ABSPATH . WPINC . '/class-IXR.php');
	require_once(ABSPATH . WPINC . '/class-wp-xmlrpc-server.php');
	
	$db_logging = true;
	
	/**
	 * Log to file
	 *
	 * @param $msg That which to log.
	 */
	function dbLog($msg) {
		global $db_logging;
		if ($db_logging) {
			//echo HORZ_SP_UTILITES_DIR.'/dblog.txt';
			$fp = fopen(HORZ_SP_UTILITES_DIR.'/dblog.txt',"a+");
			$date = gmdate("Y-m-d H:i:s ");
			fwrite($fp, "\n\n".$date.$msg);
			fclose($fp);
		}
	}
	/**
	 * Uses the shortcode method to output weather data from the DB to a WP page
	 * @author pszjmb
	 *
	 */
	class Horz_JMB_SensorDatabase{
		private $settings;
		private $wpdb_sensor;
		private $wpserver;
		private $loggedin;
	
		function Horz_JMB_SensorDatabase(){
			$this->settings = new Horz_JMB_Settings();
			$this->wpdb_sensor = new wpdb($this->settings->DB_USER,
					$this->settings->DB_PASSWORD, $this->settings->DB_NAME,
					$this->settings->DB_HOST);
			$this->wpserver = new wp_xmlrpc_server();
			$this->loggedin = false;
		}
		
		/**
		 * Show Sensorpress tables.
		 *
		 * @param $args is a list of arguments for database acecss and use. 
		 * Presumes args[0] is username and args[1] is password
		 * @return array
		 */
		function tables($args) {			
			$this->wpserver->escape(&$args);
			array_push($args,"SHOW TABLES;");
			return $this->query($args);	
		}
		
		/**
		 * Show Sensorpress tables.
		 *
		 * @param $args is a list of arguments for database acecss and use. 
		 * Presumes args[0] is username, args[1] is password and 
		 * args[2] is the table to display the columns for
		 * @return array
		 */
		function columns($args) {
			$this->wpserver->escape(&$args);
			$args[2] = "SHOW COLUMNS FROM $args[2]";
			return $this->query($args);	
		}
	
		/**
		 * Retrieve items. Only allow returns of up to 10000 records
		 *
		 * @param array $args Method parameters.
		 * @return array
		 */
		function select($args) {
			$this->wpserver->escape(&$args);
			$output = array();
			
			$table		= $args[2];
			$limit		= (int) $args[3];
			
			if(!$limit || $limit <1 || $limit > 9999)
				$limit = 1;
				
			$query = "CALL selectrecent_$table( $limit )";
				
			//array_push($output,$query);
			$results= $this->wpdb_sensor->get_results( $query );
			foreach($results as $result) {
				array_push($output,$result);
			}
				
			return $output;
				
		}
	
		/**
		 * Retrieve latest readingset id for a given device
		 *
		 * @param array $args[2] contains the deviceId to select for
		 * @return array
		 */
		function selectLatestReadingsetIdForDevice($args) {
			$this->wpserver->escape(&$args);
			$output = array();
			
			$device		= $args[2];
				
			$query = "CALL selectLatestReadingsetIdForDevice( $device )";
				
			//array_push($output,$query);
			$results= $this->wpdb_sensor->get_results( $query );
			foreach($results as $result) {
				array_push($output,$result);
			}
				
			return $output;
				
		}
	
		/**
		 * Retrieve latest readingset id
		 * @return array
		 */
		function selectLatestReadingsetId($args) {
			$this->wpserver->escape(&$args);
			$output = array();
				
			$query = "CALL selectLatestReadingsetId()";
				
			$results= $this->wpdb_sensor->get_results( $query );
			foreach($results as $result) {
				array_push($output,$result);
			}
				
			return $output;
				
		}
	
		/**
		 * Retrieve latest readingset id for a given device
		 *
		 * @param array $args[2] contains the deviceId
		 * @param array $args[3] contains the reading_type_id
		 * @param array $args[4] contains the datatype
		 * @param array $args[5] contains the start datetime
		 * @param array $args[6] contains the stop datetime
		 * @return array
		 */
		function selectReadingsForDeviceInstanceByDateRange($args) {
			$this->wpserver->escape(&$args);
			$output = array();
			
			$device					= $args[2];
			$reading_type_id		= $args[3];
			$datatype				= $args[4];
			$startdatetime			= $args[5];
			$stopdatetime			= $args[6];
				
			$query = "CALL selectreadings_forDevInst_byDateRange( 
				$device,$reading_type_id,\"$datatype\",
				\"$startdatetime\",\"$stopdatetime\")";
			dbLog($query);
				
			//array_push($output,$query);
			$results= $this->wpdb_sensor->get_results( $query );
			foreach($results as $result) {
				array_push($output,$result);
			}
				
			return $output;
				
		}
	
	
		/**
		 * Insert a reading.
		 *
		 * @param array $args Method parameters.
		 * @return array
		 */
		function insert_reading($args) {
			$this->wpserver->escape(&$args);
			$output = array();
						
			$data_type			= $args[2];
			$value				= $args[3];
			$readingset_id		= $args[4];
			$reading_type		= $args[5];
							
			$query = 
				"CALL insert_reading_$data_type( $value,$readingset_id,$reading_type )";
				
			$out = array();
			$results= $this->wpdb_sensor->get_results($query);
			
			foreach($results as $result) {
				array_push($output,$result);
			}
			return $output;
						
		}
	
		/**
		 * Insert an import record.
		 *
		 * @param array $args Method parameters.
		 * @return array
		 */
		function insert_importRecord($args) {
			$this->wpserver->escape(&$args);
			$output = array();
						
			$filename			= $args[2];
			$deviceInstance		= $args[3];
			$timestamp			= $args[4];
							
			$query = 
				"CALL insert_import_record(\"$filename\",$deviceInstance,\"$timestamp\" )";
			//dbLog($query);
			$out = array();
			$results= $this->wpdb_sensor->get_results($query);
			
			foreach($results as $result) {
				array_push($output,$result);
			}
			return $output;
						
		}
	
		/**
		 * Select the last import record.
		 *
		 * @param array $args Method parameters.
		 * @return array
		 */
		function select_lastimportRecord($args) {
			$this->wpserver->escape(&$args);
			$output = array();
						
			$filename			= $args[2];
			$deviceInstance		= $args[3];
							
			$query = 
				"CALL selectLastImportRecord(\"$filename\",$deviceInstance)";
			dbLog($query);
			$out = array();
			$results= $this->wpdb_sensor->get_results($query);
			
			foreach($results as $result) {
				array_push($output,$result);
			}
			return $output;						
		}
	
		/**
		 * select the lowestReadingIdForReadingSetTimestamp
		 * Note that this is a "public" access routine and does not require username or password 
		 * @param array $args Method parameters.
		 * @return array
		 */
		function select_lowestReadingIdForReadingSetTimestamp($args) {
			$this->wpserver->escape(&$args);
			$output = array();
						
			$timestamp			= $args[0];
							
			$query = 
				"CALL select_lowestReadingIdForReadingSetTimestamp(\"$timestamp\")";
			dbLog($query);
			$out = array();
			$results= $this->wpdb_sensor->get_results($query);
			
			foreach($results as $result) {
				array_push($output,$result);
			}
			return $output;						
		}
		
		/**
		 * Allow client to compose its own queries
		 *
		 * @param array $args Method parameters -- 0 = usernam, 1 = password, 2 = query
		 * @return mixed Database query results
		 */
		function query($args) {
			//$this->wpserver->escape(&$args);
			$output = array();
						
			$user_query		= $args[2];
			dbLog($args[2]);
				
			return $this->wpdb_sensor->get_results( $user_query );
				
		}
	}
?>