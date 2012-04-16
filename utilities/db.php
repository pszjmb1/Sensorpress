<?php

/* Provides Horz_JMB_ShadowDatabase which interacts with the ShadowPress mysql DB.
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

	include(HORZ_SP_UTILITES_DIR . '/settings.php');
	require_once(ABSPATH . WPINC . '/class-IXR.php');
	require_once(ABSPATH . WPINC . '/class-wp-xmlrpc-server.php');

	/**
	 * Uses the shortcode method to output weather data from the DB to a WP page
	 * @author pszjmb
	 *
	 */
	class Horz_JMB_ShadowDatabase{
		private $settings;
		private $wpdb_shadow;
		private $wpserver;
	
		function Horz_JMB_ShadowDatabase(){
			$this->settings = new Horz_JMB_Settings();
			$this->wpdb_shadow = new wpdb($this->settings->DB_USER,
					$this->settings->DB_PASSWORD, $this->settings->DB_NAME,
					$this->settings->DB_HOST);
			$this->wpserver = new wp_xmlrpc_server();
				
			$this->plugin_url = trailingslashit(WP_PLUGIN_URL.'/'.
					dirname(plugin_dir_path(__FILE__)));
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
	
		/**
		 * Allow client to compose its own queries
		 *
		 * @param array $args Method parameters -- 0 = usernam, 1 = password, 2 = query
		 * @return mixed Database query results
		 */
		function query($args) {
			$this->wpserver->escape(&$args);
			$username	= $args[0];
			$password	= $args[1];
			$user_query		= $args[2];
	
			if ( !$user = $this->wpserver->login($username, $password) ){
				$readings=array();
				array_push($readings,$this->wpserver->error);
				return $readings;
			}
				
			return $this->wpdb_shadow->get_results( $user_query );
				
		}
	}
?>