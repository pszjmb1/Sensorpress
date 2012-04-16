<?php
	// Exit if accessed directly
	if ( !defined( 'ABSPATH' ) ) exit;
	
	// Utilites directory
	if ( !defined( 'HORZ_SP_UTILITES_DIR' ) )
		define( 'HORZ_SP_UTILITES_DIR', HORZ_SP_PLUGIN_DIR . '/utilities' );
	
	// Require utility files
	require_once( HORZ_SP_UTILITES_DIR . '/db.php'     );
	require_once( HORZ_SP_UTILITES_DIR . '/xml_rpc.php'     );
	
		
	$spdbxml = new Horz_JMB_ShadowDB_XML_RPC();
	if(isset($spdbxml)){
		// Pass the Wordpress xmlrpc methods filter data to the ShadowDataView add_new_xmlrpc_methods function
		add_filter( 'xmlrpc_methods', array(&$spdbxml, 'add_new_xmlrpc_methods'));
	}
?>