<?php
/*
	Plugin Name: ShadowPress
	Plugin URI: n/a
	Description: Sensor data logging for WordPress. Connect information from your community or school with your blog and the rest of the world.
	Version: 0.3
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

// Ensure that ABSPATH was defined
if ( !defined( 'ABSPATH' ) ) exit;

global $wpdb;

// Define the ShadowPress version
if ( !defined( 'HORZ_SP_VERSION' ) )
	define( 'HORZ_SP_VERSION', '0.3' );

// Define the database version
if ( !defined( 'HORZ_SP_DB_VERSION' ) )
	define( 'HORZ_SP_DB_VERSION', 0.1 );

// Define the plugin name
if ( !defined( 'HORZ_SP_NAME' ) )
	define( 'HORZ_SP_NAME', 'ShadowPress' );

// Define the ShadowPress blog id -- idea courtesy of Buddypress
if ( !defined( 'HORZ_SP_ROOT_BLOG' ) ) {
	if( !is_multisite() ) {
		$_id = 1;
	}else if ( !defined( 'HORZ_SP_ENABLE_MULTIBLOG' ) ) {
		$current_site = get_current_site();
		$_id = $current_site->blog_id;
	} else {
		$_id = get_current_blog_id();
	} 
	define( 'HORZ_SP_ROOT_BLOG', $_id );
}

// Path and URL
if ( !defined( 'HORZ_SP_PLUGIN_DIR' ) )
	define( 'HORZ_SP_PLUGIN_DIR', WP_PLUGIN_DIR . '/Shadowpress' );

if ( !defined( 'HORZ_SP_PLUGIN_URL' ) )
	define( 'HORZ_SP_PLUGIN_URL', plugins_url( 'Shadowpress' ) );

/**
 * Exits the plugin if the WP version is lower than $minver 
 * @param $minver is the minimum version of Wordpress supported
 */
function checkVersion($minver){
	global $wp_version;
	$exit_msg="HORZ_SP_NAME requires Wordpress version ".$wp_version.' or newer.';
	if(version_compare($wp_version, $minver,"<")){
		exit($exit_msg);
	}
}

checkVersion(3);

require( HORZ_SP_PLUGIN_DIR . '/utilities/utilitiesloader.php'     );
require( HORZ_SP_PLUGIN_DIR . '/interface/interfaceloader.php'     );	


	
?>