<?php
/*
	Plugin Name: Shadow Data View
	Plugin URI: n/a
	Description: Simple data viewer for the ShadowPress DB
	Version: 1.0
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
			
			function Horz_JMB_ShadowDataView(){
				$this->plugin_url = trailingslashit(WP_PLUGIN_URL.'/'.
						dirname(plugin_dir_path(__FILE__)));
				
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
				$settings = new Horz_JMB_Settings(); 
				$wpdb_shadow = new wpdb($settings->DB_USER, $settings->DB_PASSWORD, $settings->DB_NAME, $settings->DB_HOST);
				$reading='';

				$output='<div class="viewdata"><table><tbody>';
				$myheadings = $wpdb_shadow->get_results( "show columns from horz_sp_reading" );
				$output =$this->displayRowInHTMLTable($myheadings,$output,'th');
				$myrows = $wpdb_shadow->get_results( "SELECT * FROM horz_sp_reading LIMIT 10" );
				$output = $this->displayRowInHTMLTable($myrows,$output);
				$output=$output.'</tbody></table></div>';
				return $output;
			}	
				
			function add_new_xmlrpc_methods( $methods ) {
				$methods['shadowpress.display'] =  array(&$this, 'display');//'display';
				return $methods;
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