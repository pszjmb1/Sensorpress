<?php
/*
	Plugin Name: Shadow Data View
	Plugin URI: n/a
	Description: Simple data viewer for the ShadowPress DB
	Version: 0.2
	Author: JMB
	Author URI: n/a
	License: AGPLv3
*/

/* Shadow Data View displays data from the ShadowPress mysql DB.
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

	include('settings.php');
	include_once(ABSPATH . WPINC . '/class-IXR.php');
	include_once(ABSPATH . WPINC . '/class-wp-xmlrpc-server.php');
	/**
	 * Exits the plugin if the WP version is lower than $minver 
	 * @param $minver is the minimum version of Wordpress supported
	 */
	function checkVersion($minver){
		global $wp_version;
		$exit_msg='Shadow Data View requires Wordpress version '.$wp_version.' or newer.';
		if(version_compare($wp_version, $minver,"<")){
			exit($exit_msg);
		}
	}
	
	if(!class_exists('Horz_JMB_ShadowDataView')){
		/**
		 * Uses the shortcode method to output weather data from the DB to a WP page  
		 * @author pszjmb
		 *
		 */
		class Horz_JMB_ShadowDataView{
			var $plugin_url;
			private $settings;
			private $wpdb_shadow;
			private $wpserver;
			
			function Horz_JMB_ShadowDataView(){
				$this->settings = new Horz_JMB_Settings();
				$this->wpdb_shadow = new wpdb($this->settings->DB_USER, 
						$this->settings->DB_PASSWORD, $this->settings->DB_NAME, 
						$this->settings->DB_HOST);
				$this->wpserver = new wp_xmlrpc_server();
				
				$this->plugin_url = trailingslashit(WP_PLUGIN_URL.'/'.
						dirname(plugin_dir_path(__FILE__)));
				
				//$this->registerShortcodes();
				add_shortcode('view-data', array(&$this,'display'));
			}
			
			/**
				Called after plugin activation
			 */
			function install(){
				
			}
			
			/**
			 * Displays content from an array in an HTML table row as either a heading row or a main row
			 * @param $myrows is an array of elements to put into a table
			 * @param $coltype can be td or th
			 * @param $output is the text to append the results to
			 */
			function displayRowInHTMLTable($myrows,$output,$coltype='td'){
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
			
			function display(){
				//global $wpdb;
				$reading='';

				$output='<div class="viewdata"><table><tbody>';
				$myheadings = $this->wpdb_shadow->get_results( "show columns from horz_sp_reading" );
				$output =$this->displayRowInHTMLTable($myheadings,$output,'th');
				$myrows = $this->wpdb_shadow->get_results( "SELECT * FROM horz_sp_reading LIMIT 10" );
				$output = $this->displayRowInHTMLTable($myrows,$output);
				$output=$output.'</tbody></table></div>';
				//$a = new wp_xmlrpc_server();
				//return $a->sayHello('');
				return $output;
			}	
			
			/**
			 * Retrieve readings. Only allow returns of up to 10000 records
			 *
			 * @param array $args Method parameters.
			 * @return array
			 */
			function select($args) {		
				$readings = array();		
				$this->wpserver->escape(&$args);
				$username	= $args[0];
				$password	= $args[1];
				$table		= $args[2];
				$limit		= (int) $args[3];
			
				if ( !$user = $this->wpserver->login($username, $password) ){
					array_push($readings,$this->wpserver->error);
					return $readings;
				}
				
				if(!$limit || $limit <1 || $limit > 9999)
					$limit = 1;
				
				$query = "CALL selectrecent_$table( $limit )";
				
				//array_push($readings,$query);
				$results= $this->wpdb_shadow->get_results( $query );
				foreach($results as $result) {
					array_push($readings,$result);
				}
				
				return $readings;
				
			}
				
			function add_new_xmlrpc_methods( $methods ) {
				$methods['shadowpress.display'] =  array(&$this, 'display');
				$methods['shadowpress.select'] =  array(&$this, 'select');
				
				return $methods;
			}
			
			function registerShortcodes(){
				add_shortcode('view-data', array(&$this,'display'));
			}
		}
	}else{ 
			exit("Horz_JMB_ShadowDataView class already declared.");
	}
	
	checkVersion(3);
	$sdv = new Horz_JMB_ShadowDataView();
	if(isset($sdv)){
		// Register the activation function
		register_activation_hook(__FILE__, array($sdv,'install'));
		add_filter( 'xmlrpc_methods', array(&$sdv, 'add_new_xmlrpc_methods'));
	}
	
?>