<?php
	// Exit if accessed directly
	if ( !defined( 'ABSPATH' ) ) exit;
	
	// Interface directory
	if ( !defined( 'HORZ_SP_INTERFACE_DIR' ) )
		define( 'HORZ_SP_INTERFACE_DIR', HORZ_SP_PLUGIN_DIR . '/interface' );
	
	// Require interface files
	require( HORZ_SP_INTERFACE_DIR . '/dataview.php'     );
	
	$sdv = new Horz_JMB_ShadowDataView();
	if(isset($sdv)){
		//Register the short codes
		$sdv->registerShortcodes();
	}
?>