<?php

/* Dataview displays data from the Sensorpress mysql DB.
 Copyright (C) 2012  Jesse Blum (JMB)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as
published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

require_once(ABSPATH . WPINC . '/class-IXR.php');
require_once(ABSPATH . WPINC . '/class-wp-xmlrpc-server.php');
require_once(HORZ_SP_UTILITES_DIR . '/settings.php');
require_once( HORZ_SP_UTILITES_DIR . '/db.php'     );

/**
 * Uses the shortcode method to output weather data from the DB to a WP page
 * @author pszjmb
 *
 */
class Horz_JMB_SensorDataView{
	private $settings;
	private $wpdb_sensor;
	private $wpserver;
	private $spdb;

	function Horz_JMB_SensorDataView(){
		$this->settings = new Horz_JMB_Settings();
		$this->wpdb_sensor = new wpdb($this->settings->DB_USER,
				$this->settings->DB_PASSWORD, $this->settings->DB_NAME,
				$this->settings->DB_HOST);
		$this->wpserver = new wp_xmlrpc_server();
		$this->spdb = new Horz_JMB_SensorDatabase();
	}

	/**
	 * Displays content from an array in an HTML table row as either a heading row or a main row
	 * @param $myrows is an array of elements to put into a table
	 * @param $coltype can be td or th
	 * @param $output is the text to append the results to
	 */
	function prepareOutput_HTMLTable($myrows,$output,$coltype='td'){
		if($myrows){
			if($coltype=='td'){
				foreach ( $myrows as $arow ){
					$output=$output.'<tr>';
					//foreach ( $arow as $colitem)
					//	$output = $output.'<td class="data_row">'.$colitem.'</'td'>';
					$output = $output.'<td class="data_row">'.$arow->value_int.'</td>';
				}
				$output=$output.'</tr>';
			}else{
				$output=$output.'<tr>';
				//foreach ( $myrows as $arow ){
				//$colitem = $arow->Field;
				$output = $output.'<'.$coltype.' class="data_row">'.$myrows[6]->Field.'</'.$coltype.'>';
				//}
				$output=$output.'</tr>';
			}
		}else{
			$output=$output.'<tr><'.$coltype.' class="data_row">None found.</'.$coltype.'></tr>';
		}
		return $output;
	}
	
	
	
	function doQuery($queryIn){
			
		$args = array();
		array_push($args,$this->settings->DB_USER);
		array_push($args,$this->settings->DB_PASSWORD);
		array_push($args,$queryIn);
			
		return $spdb->query($args);
	}

	/**
	 * Displays content on new lines
	 * @param $myrows is an array of elements to output
	 */
	function prepareOutput_Simple($myrows){
		if($myrows){
			$output = "<br/>";
			foreach ( $myrows as $arow ){
				if($arow){
					foreach ( $arow as $item ){
						$output=$output.$item.'<br/>';
					}
				}
			}
		}
		return $output;
	}

	/**
	 * Shows direct access to DB -- discouraged...
	 */
	function display_old(){
		//global $wpdb;
		$reading='';

		$output='<div class="viewdata"><table><tbody>';
		$myheadings = $this->wpdb_sensor->get_results( "show columns from horz_sp_reading" );
		$output =$this->prepareOutput_HTMLTable($myheadings,$output,'th');
		$myrows = $this->wpdb_sensor->get_results( "SELECT * FROM horz_sp_reading LIMIT 10" );
		$output = $this->prepareOutput_HTMLTable($myrows,$output);
		$output=$output.'</tbody></table></div>';
		//$a = new wp_xmlrpc_server();
		//return $a->sayHello('');
		return $output;
	}

	/**
	 * Shows prefered DB access through Horz_JMB_SensorDatabase object
	 */
	function display(){				
		return $this->prepareOutput_Simple(
				$this->spdb->select( array('','','reading',10) )
				);
	}


	function registerShortcodes(){
		add_shortcode('view-data', array(&$this,'display'));
	}
}

?>