<?php
/**
 * @package moderation
 */

global $gBitSystem, $gLibertySystem;

$registerHash = array(
	'package_name' => 'moderation',
	'package_path' => dirname( __FILE__ ).'/',
	'homeable' => TRUE,
);
$gBitSystem->registerPackage( $registerHash );

if( $gBitSystem->isPackageActive( 'moderation' ) ) {

	$menuHash = array(
		'package_name'       => MODERATION_PKG_NAME,
		'index_url'          => MODERATION_PKG_URL.'index.php',
		'menu_template'      => 'bitpackage:moderation/menu_moderation.tpl',
	);
	$gBitSystem->registerAppMenu( $menuHash );

	$gLibertySystem->registerService(
		MODERATION_PKG_NAME, MODERATION_PKG_NAME, array(
		'module_display_function'  => 'moderation_module_display',
	) );

//	$gBitSystem->registerNotifyEvent( array( "moderation_request" => tra("A moderation request is made.") ) );
//	$gBitSystem->registerNotifyEvent( array( "moderation_reply" => tra("A moderation reply is made.") ) );

	require_once( 'ModerationSystem.php' );
}
?>
