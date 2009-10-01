<?php

/**
 * @version $Header: /cvsroot/bitweaver/_bit_moderation/admin/schema_inc.php,v 1.8 2009/10/01 13:45:44 wjames5 Exp $
 *
 * +----------------------------------------------------------------------+
 * | Copyright ( c ) 2008, bitweaver.org
 * +----------------------------------------------------------------------+
 * | All Rights Reserved. See copyright.txt for details and a complete
 * | list of authors.
 * | Licensed under the GNU LESSER GENERAL PUBLIC LICENSE.
 * | See http://www.gnu.org/copyleft/lesser.html for details
 * |
 * | For comments, please use phpdocu.sourceforge.net standards!!!
 * | -> see http://phpdocu.sourceforge.net/
 * +----------------------------------------------------------------------+
 * | Authors: nick <nick@sluggardy.net>
 * +----------------------------------------------------------------------+
 *
 * Moderation Schema
 *
 * This file contains the schema for the moderation package.
 *
 * @author   nick <nick@sluggardy.net>
 * @version  $Revision: 1.8 $
 * @package  moderation
 */

$tables = array(
	'moderation' => "
    	moderation_id I4 PRIMARY,
    	moderator_user_id I4,
    	moderator_group_id I4,
		moderator_perm_name C(30),
    	source_user_id I4 NOTNULL,
    	content_id I4,
		responsible I1 NOTNULL DEFAULT 0,
    	package C(128) NOTNULL,
    	type C(64) NOTNULL,
    	status C(64) NOTNULL,
    	last_status C(64),
		data X,
    	request X,
    	reply X
    	CONSTRAINT '
        	, CONSTRAINT `moderation_queue_mod_user_id` FOREIGN KEY (`moderator_user_id`) REFERENCES `".BIT_DB_PREFIX."users_users` (`user_id`)
        	, CONSTRAINT `moderation_queue_mod_group_id` FOREIGN KEY (`moderator_group_id`) REFERENCES `".BIT_DB_PREFIX."users_groups` (`group_id`)
			, CONSTRAINT `moderation_queue_mod_perm_name` FOREIGN KEY (`moderator_perm_name`) REFERENCES `".BIT_DB_PREFIX."users_permissions` (`perm_name`)
        	, CONSTRAINT `moderation_queue_content_id` FOREIGN KEY (`content_id`) REFERENCES `".BIT_DB_PREFIX."liberty_content` (`content_id`)
        	, CONSTRAINT `moderation_queue_source_user_id` FOREIGN KEY (`source_user_id`) REFERENCES `".BIT_DB_PREFIX."users_users` (`user_id`)
		'
	",
	);

global $gBitInstaller;

foreach( array_keys( $tables ) AS $tableName ) {
	$gBitInstaller->registerSchemaTable( MODERATION_PKG_NAME, $tableName, $tables[$tableName] );
}

$gBitInstaller->registerPackageInfo( MODERATION_PKG_NAME, array(
	'description' => "A Moderation service system that makes it easy for packages to provide moderation features.",
	'license' => '<a href="http://www.gnu.org/licenses/licenses.html#LGPL">LGPL</a>',
) );

// ### Sequences
$sequences = array (
	'moderation_id_seq' => array( 'start' => 1 )
);

$gBitInstaller->registerSchemaSequences( MODERATION_PKG_NAME, $sequences );

// ### Default Preferences
$gBitInstaller->registerPreferences( MODERATION_PKG_NAME, array(
 //	array( MODERATION_PKG_NAME, 'moderation_display_request','y' ),
 //	array( MODERATION_PKG_NAME, 'moderation_display_reply','y' ),
) );

/*
$gBitInstaller->registerUserPermissions( MODERATION_PKG_NAME, array(
	array( 'p_moderation_admin', 'Can administer all aspects of moderation', 'editors', MODERATION_PKG_NAME ),
) );
*/

?>
