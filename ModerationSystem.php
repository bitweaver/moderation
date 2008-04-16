<?php

/**
 * @version $Header: /cvsroot/bitweaver/_bit_moderation/ModerationSystem.php,v 1.16 2008/04/16 15:18:48 wjames5 Exp $
 *
 * +----------------------------------------------------------------------+
 * | Copyright ( c ) 2008, bitweaver.org
 * +----------------------------------------------------------------------+
 * | All Rights Reserved. See copyright.txt for details and a complete
 * | list of authors.
 * | Licensed under the GNU LESSER GENERAL PUBLIC LICENSE.
 * | See license.txt for details
 * |
 * | For comments, please use phpdocu.sourceforge.net standards!!!
 * | -> see http://phpdocu.sourceforge.net/
 * +----------------------------------------------------------------------+
 * | Authors: nick <nick@sluggardy.net>
 * +----------------------------------------------------------------------+
 *
 * ModerationSystem class
 *
 * This class represents an abstract moderation system which packages
 * can use to register things for moderation and
 *
 * @author   nick <nick@sluggardy.net>
 * @version  $Revision: 1.16 $
 * @package  moderation
 */

global $gModerationSystem;

/* The required moderation states. */
define( 'MODERATION_PENDING', "Pending" );
define( 'MODERATION_APPROVED', "Approved" );
define( 'MODERATION_REJECTED', "Rejected" );
define( 'MODERATION_DELETE', "Delete" );

/* The possible values for the responsible flag to indicate who must act */
define( 'MODERATION_NEEDED', 0);
define( 'MODERATION_GIVEN', 1);

/*
   A handy flag to turn on some validation of your transition table
   when you are developing moderation for a package.
*/
define( 'MODERATION_DEVELOPMENT', TRUE );

require_once( LIBERTY_PKG_PATH . 'LibertyContent.php' );

class ModerationSystem extends LibertyContent {

	/**
	 * The package registrations
	 */
	var $mPackages;

	/**
	 * Constructs a ModerationSystem. This shouldn't really be called.
	 * Use the $gModerationSystem instance instead which is created
	 * for you if you include this file.
	 */
	function ModerationSystem() {
		// Not much to do here
		$mPackages = array();
		LibertyContent::LibertyContent();
	}

    /**
	 * Request a moderation.
	 *
	 * Returns an ID for the moderation.
	 */
	function requestModeration( $pPackage, 
							  $pType,
							  $pModerationUser = NULL,
							  $pModerationGroup = NULL,
							  $pModerationPerm = NULL,
							  $pContentId = NULL,
							  $pRequest = NULL,
							  $pState = MODERATION_PENDING,
							  $pDataHash = NULL) {
		global $gBitSystem, $gBitUser;
		$moderationId = -1;
		// Validate package
		if ( ! empty( $this->mPackages[$pPackage] ) ) {
			// Validate type
			if ( in_array( $pType, $this->mPackages[$pPackage]['types'] ) ) {
				// Validate that we have a user or group
				if ( ! ( empty( $pModerationUser ) and
						empty( $pModerationGroup ) and 
						empty( $pModerationPerm  ) ) ) {
					// @TODO: Validate the $pState

					// Do the storage into the right table
					$table = BIT_DB_PREFIX."moderation";

					$store = array();
					$store['moderator_user_id'] = $pModerationUser;
					$store['moderator_group_id'] = $pModerationGroup;
					$store['moderator_perm_name'] = $pModerationPerm;
					$store['source_user_id'] = $gBitUser->mUserId;
					$store['content_id'] = $pContentId;
					$store['package'] = $pPackage;
					$store['type'] = $pType;
					$store['request'] = $pRequest;
					$store['status'] = $pState;
					if (!empty($pDataHash)) {
						$store['data'] = serialize($pDataHash);
					}

					// Keeping the transaction as short as possible
					$this->mDb->StartTrans();

					$store['moderation_id'] = $this->mDb->GenID( 'moderation_id_seq' );
					$this->mDb->associateInsert($table, $store);

					$this->mDb->CompleteTrans();

					// Setup the return value
					$moderationId = $store['moderation_id'];
				}
				else {
					$gBitSystem->fatalError(tra("Moderation user or moderation group or moderation perm name must be set."));
				}
			}
			else {
				$gBitSystem->fatalError(tra("Unknown moderation type for package:").$pPackage.tra(" type: ").$pType);
			}
		}
		else {
			$gBitSystem->fatalError(tra("Attempt to add moderation for unregistered package: ").$pPackage);
		}

		return $moderationId;
	}

    /**
	 * Register the callback function for a given package. When status
	 * on a packages queue changes this function will be called.
	 *
	 * The function should have the following API: boolean
	 * handleModeration(&$pModeration) Where $pModeration is an array
	 * with all of the information in the moderation table. The
	 * function should return TRUE if the transition should be stored
	 * and an error mesage to be displayed to the user otherwise. This
	 * gives packages the oportunity to error out their operations
	 * without causing the state to change and to have the moderation
	 * system display the error message.
	 *
	 * $pTransitions gives the state transition table (as an array) for
	 * the package so that the moderation UI knows how to display
	 * links to the transitions that a request can make.
	 * It is keyed first by moderation type and then by the states.
	 * Each state must have a transistion to at least one other state.
	 *
	 * All transition tables must have a PENDING, REJECTED and
	 * APPROVED state but packages are free to add additional states
	 * as required for their particular moderation processes. At least
	 * one state must lead to MODERATION_DELETE.
	 *
	 * PENDING is the default start state for all
	 * requests. MODERATION_DELETE is a special state which causes the
	 * moderation system to delete the moderation request from the
	 * system after making the callback to the package.
	 *
	 * The most simple example is:
	 * $pTransitions = array( "content" =>
	 * 						array (MODERATION_PENDING =>
	 * 									array(MODERATION_APPROVED,
	 * 										  MODERATION_REJECTED),
	 * 					   		   MODERATION_REJECTED => MODERATION_DELETE,
	 *							   MODERATION_APPROVED => MODERATION_DELETE,
	 *							   ) );
	 *
	 * This would show moderations in the packages "content" queue in
	 * the "Pending" state with links to change the state to
	 * "Approved" or "Rejected".  Moderations in the queue in state
	 * "Rejected" would show with a link to the "Delete" state. etc.
	 *
	 * More complex tables are possible by adding additional states.
	 * For example a system that allowed authors a chance to polish
	 * their work a bit if the moderator would accept with changes:
	 * $pTransitions = array( "content" =>
	 * 						array (MODERATION_PENDING =>
	 * 									array(MODERATION_APPROVED,
	 * 										  "Needs Work",
	 *										  MODERATION_REJECTED),
	 *							   "Needs Work" => array(MODERATION_PENDING,
	 *							                         MODERATION_DELETE),
	 * 					   		   MODERATION_REJECTED => MODERATION_DELETE,
	 *							   MODERATION_APPROVED => MODERATION_DELETE,
	 *							   ) );
	 *
	 * Note: If you define MODERATION_DEVELOPMENT in your code
	 * then a non-exhaustive attempt to verify your packages
	 * transition table will be made. See validateTransitions for
	 * more information on this check.
	 */
    function registerModerationListener( $pPackage, $pFunction, $pTransitions, $pModerationTemplate = 'bitpackage:moderation/moderate.tpl', $pRequestTemplate = 'bitpackage:moderation/request.tpl' ) {
		global $gBitSystem;
		// Ensure that the transition table has the right structure
		// if we are in development mode. See above to turn this on.
		if ( ! MODERATION_DEVELOPMENT ||
			 $this->validateTransitions( $pTransitions) ) {
			// Save the registsration information for later.
			$this->mPackages[$pPackage]['name'] = $pPackage;
			$this->mPackages[$pPackage]['callback'] = $pFunction;
			$this->mPackages[$pPackage]['types'] = array_keys($pTransitions);
			$this->mPackages[$pPackage]['transitions'] = $pTransitions;
			$this->mPackages[$pPackage]['moderate_tpl'] = $pModerationTemplate;
			$this->mPackages[$pPackage]['request_tpl'] = $pRequestTemplate;
		}
	}

	/**
	 * Validates that a transition map has the right structure.
	 * Note that this does not validate that the entire state tree
	 * has the required transisitons to lead to a delete state or that
	 * there are no dead end states in the map but it is a good
	 * check if you are developing moderation support for a package.
	 *
	 * @access private
	 */
	function validateTransitions( $pTransitions ) {
		// Make sure we have an array of types
		if ( is_array( $pTransitions ) &&
			 count( array_keys( $pTransitions ) ) > 0 ){
			// Make sure that we have an array of states.
			foreach ( $pTransitions as $type => $states ) {
				$hasPending = $hasApproved = $hasRejected = $hasDelete = false;
				if ( ( ! is_array( $states ) ) or
					 count( array_keys( $states ) ) == 0 ) {
					$gBitSystem->fatalError(tra("Invalid transition map given to the moderation system by the ").$pPackage.tra(" package. Type: ").$type.tra(" does not lead to a state array.") );
				}
				else {
					// Make sure that a state goes somewhere
					foreach ( $states as $state => $results ) {
						if ( $results == NULL ) {
							$gBitSystem->fatalError( tra("Invalid transition map given to the moderation system by the ").$pPackage.tra(" package. Type: ").$type.tra(" state: ").$state.tra(" goes nowhere.") );
						}
						// Make sure we have the required origin states
						if ( $state == MODERATION_PENDING ) {
							$hasPending = true;
						}
						if ( $state == MODERATION_REJECTED ) {
							$hasRejected = true;
						}
						if ( $state == MODERATION_APPROVED ) {
							$hasApproved = true;
						}
						// Make sure some state leads to delete
						if ( $results == MODERATION_DELETE or
							 ( is_array( $results ) and
							   in_array( MODERATION_DELETE, $results ) ) ) {
							$hasDelete = true;
						}
					}
				}
				if ( ! ($hasPending and $hasRejected and $hasApproved and $hasDelete) ) {
					$gBitSystem->fatalError("Invalid transition map given to the moderation system by the $pPackage package. Required states are missing.");
				}
			}
		}
		else {
			$gBitSystem->fatalError("Invalid transition map given to the moderation system by the $pPackage package. No types.");
		}

		return true;
	}

	/**
	 * Sets the reply for a given request and triggers
	 * the callback to the package.
	 */
	function setModerationReply( $pRequestId, $pStatus, $pReply = NULL )
	{
		global $gBitSystem, $gBitUser;

		// Load the information
		$moderationInfo = $this->getModeration( $pRequestId );
		if ( ! empty( $moderationInfo ) ) {
			$isValidUser = FALSE;
			// Validate that the current user is a moderator
			if ( $gBitUser->isAdmin() ||
				 $gBitUser->mUserId == $moderationInfo['moderator_user_id'] or
				 $gBitUser->isInGroup( $moderationInfo['moderator_group_id'] ) ) {
				$isValidUser = TRUE;
			// if those checks fail then lets bother loading up the object and checking the perm if we have one  
			 }elseif( !empty(  $moderationInfo['moderator_perm_name'] ) && 
				 				$obj = LibertyBase::getLibertyObject( $_REQUEST['content_id'] ) && 
								$obj->hasUserPermission(  $moderationInfo['moderator_perm_name'] ) ){
				$isValidUser = TRUE;
			}

			if( $isValidUser ){
				// Some shorthands for current state
				$pkg = $moderationInfo['package'];
				$type = $moderationInfo['type'];
				$state = $moderationInfo['status'];

				// Validate that we are making a valid transition
				if ( ( is_array($moderationInfo['transitions'])&&
					   in_array($pStatus, $moderationInfo['transitions']) )
					 || $pStatus == $moderationInfo['transitions']) {
					$moderationInfo['last_status'] = $state;
					$moderationInfo['status'] = $pStatus;
					if ( ! empty($pReply) ) {
						$moderationInfo['reply'] = $pReply;
					}

					// We start the transaction now so that the update
					// to status is bundled with package updates
					$this->mDb->StartTrans();

					// TODO: Set the send_email flag based on user
					// preferences here. Should be able
					// to set preferences for both ones for which I
					// am the moderator as well as ones for which
					// I am the source_user_id
					$moderationInfo['send_email'] = TRUE;

					// Set who is responsible next before the callback.
					if ($moderationInfo['responsible'] == MODERATION_NEEDED) {
						$moderationInfo['next_responsible'] = MODERATION_GIVEN;
					}
					else {
						$moderationInfo['next_responsible'] = MODERATION_NEEDED;
					}

					// Make the callback and check the reply from the package.
					$result = $this->mPackages[$pkg]['callback']($moderationInfo);

					// Do we need to send a message about this event?
					if (!empty($moderationInfo['send_email'])) {
						// TODO: Make a call to switchboard here
					}

					if ( $result == TRUE ) {
						// Do the SQL dance
						$table = BIT_DB_PREFIX."moderation";
						$locId = array('moderation_id' => $moderationInfo['moderation_id']);
						unset($moderationInfo['moderation_id']);
						if ( $moderationInfo['status'] == MODERATION_DELETE ) {
							$this->mDb->query("DELETE FROM `".$table."` WHERE moderation_id = ? ", $locId);
						}
						else {
							$update['reply'] = $moderationInfo['reply'];
							$update['status'] = $moderationInfo['status'];
							$update['last_status'] = $moderationInfo['last_status'];
							$update['responsible'] = $moderationInfo['next_responsible'];
							$this->mDb->associateUpdate( $table, $update, $locId);
						}
						$this->mDb->CompleteTrans();
					}
					else {
						// Just in case rollback any changes.
						$this->mDb->RollbackTrans();
						$gBitSystem->fatalError(tra("Error with moderation:").$result);
					}
				}
				else {
					$gBitSystem->fatalError(tra("Attempt to change to an invalid state for moderation: ").$pRequestId.tra(" currently in: ").$state.tra(" going to: ").$pStatus);
				}
			}
			else {
				$gBitSystem->setHttpStatus(403);
				$gBitSystem->fatalError(tra("Unable to set moderation reply. You are not a moderator for this moderation request."));
			}
		}
		else {
			$gBitSystem->setHttpStatus(404);
			$gBitSystem->fatalError(tra("Unable to set moderation reply. Moderation with id: ").$pRequestId.tra(" could not be found."));
		}
	}

	/**
	 * Loads the data for a given moderation id.
	 */
	function getModeration( $pRequestId ) {
		$query = "SELECT m.*, lc.title from `".BIT_DB_PREFIX."moderation` m LEFT JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (m.`content_id` = lc.`content_id`) WHERE `moderation_id` = ?";
		$result = $this->mDb->getArray($query, array($pRequestId));
		if (!empty($result)) {
			$result = $result[0];
			$result['transitions'] = $this->getTransitions( $result );
			if (!empty($result['data'])) {
				$result['data'] = unserialize($result['data']);
			}
		}

		return $result;
	}

	/**
	 * Returns the next transitions this moderation can move to.
	 *
	 * Assumes that the passed in array contains the package
	 * type and status.
	 */
	function getTransitions( $pModeration ) {
		global $gBitSystem;

		$package = $pModeration['package'];
		$type = $pModeration['type'];
		$status = $pModeration['status'];

		if ( ! empty( $this->mPackages[$package]['transitions'][$type][$status] ) ) {
			return $this->mPackages[$package]['transitions'][$type][$status];
		}
		else {
			$gBitSystem->fatalError(tra("Moderation in state with no next transitions: ") . $pModeration['moderation_id']);
		}
	}

	/**
	 * Returns an array of pending moderations from the queue.
	 *
	 * List hash supports the following:
	 * moderator_user_id - The user_id or array of ids for the moderator.
	 * moderator_group_id - A group_id or array of ids for the moderators.
	 * package - The name of the package to restrict to.
	 * type - The type of moderation to restrict to.
	 * status - The status of the moderations to restrict to.
	 * source_user_id - The user_id of the creating user.
	 * content_id - The content_id to retrict to.
	 *
	 */
	function getList( $pListHash ) {
		$this->prepGetList($pListHash);

		$selectSql = ''; $joinSql = ''; $whereSql = '';
		$bindVars = array();

		// Because this links to liberty_content via content_id we
		// use services sql to be able to protect the content_id and such.
		// We add a flag to the $pListHash to tell packages this is a
		// moderations list so they can add joins if using custom templates
		$pListHash['moderations_list'] = true;
		$this->getServicesSql( 'content_list_sql_function', $selectSql, $joinSql, $whereSql, $bindVars, NULL, $pListHash );

		// What do we stick all our things together with?
		if (isset($pListHash['where_join']) &&
			(strtoupper($pListHash['where_join']) == 'OR' ||
			 strtoupper($pListHash['where_join']) == 'AND') ) {
			$joiner = ' '.$pListHash['where_join'].' ';
		}
		else {
			$joiner = ' AND ';
		}

		// Now figure out our part of WHERE
		$first = true;
		$args = array('moderator_user_id',
					  'moderator_group_id',
					  'moderator_perm_name',
					  'package',
					  'type',
					  'status',
					  'source_user_id',
					  'content_id');

		$emptyWhere = empty($whereSql);
		$subclause = ($joiner == ' OR ' && !$emptyWhere);
		foreach ($args as $arg) {
			if ( ! empty( $pListHash[$arg] ) ) {
				// Do we need to open the ORed clause?
				if ($first && $subclause) {
					$whereSql .= ' AND ( ';
				}
				if (is_array($pListHash[$arg])) {
					if ((!$emptyWhere &&!$subclause) || !$first) {
						$whereSql .= $joiner;
					}
					$whereSql .= 'm.`'.$arg."` IN (". implode( ',',array_fill( 0,count( $pListHash[$arg] ),'?' ) ). ")";
					$bindVars = array_merge($bindVars, $pListHash[$arg]);
				} else {
					if ((!$emptyWhere && !$subclause) || !$first) {
						$whereSql .= $joiner;
					}
					$whereSql .= 'm.`'.$arg."` = ?";
					$bindVars[] = $pListHash[$arg];
				}
				$first = false;
			}
		}

		// Do we need to close the ORed clause
		if (!$first && $subclause) {
			$whereSql .= ' ) ';
		}

		// Fix up the start of the SQL in case it starts with AND
		$whereSql = trim($whereSql);
		if (!empty($whereSql)) {
			if (strtoupper(substr($whereSql, 0, 3)) == 'AND') {
				$whereSql = substr($whereSql, 3);
			}
			$whereSql = " WHERE " . $whereSql;
		}

        global $gBitUser;
        
        $joinSql .= "LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content_permissions` lcperm ON (m.`content_id`=lcperm.`content_id`)                                     
					  LEFT OUTER JOIN `".BIT_DB_PREFIX."users_groups_map` ugsm ON (ugsm.`group_id`=lcperm.`group_id`) ";

        $whereSql .= " AND ( lcperm.perm_name IS NULL OR ( lcperm.perm_name = m.moderator_perm_name AND ugsm.user_id = ? AND ( (lcperm.is_revoked != ? OR lcperm.is_revoked IS NULL) OR lc.`user_id`=? ) ) )";
        
        $bindVars[] = $gBitUser->mUserId;
        $bindVars[] = "y";
        $bindVars[] = $gBitUser->mUserId;

		// Extra moderation_id for association
		$query = "SELECT m.`moderation_id` as `hash_key`, m.*, lc.title ".$selectSql." from `".BIT_DB_PREFIX."moderation` m LEFT JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (m.`content_id` = lc.`content_id`) ".$joinSql." ".$whereSql." ORDER BY `package`, `type`, `status` ";

		$results = $this->mDb->getAssoc($query, $bindVars);
		foreach ($results as $id => $data) {
			$results[$id]['transitions'] = $this->getTransitions($data);
			if (!empty($data['data'])) {
				$results[$id]['data'] = unserialize($data['data']);
			}
		}

		$query = "SELECT count(*) from `".BIT_DB_PREFIX."moderation` m LEFT JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (m.`content_id` = lc.`content_id`)".$joinSql." ".$whereSql;
		$pListHash['cant'] = $this->mDb->getOne($query, $bindVars);
		$this->postGetList($pListHash);

		return $results;
	}

	function display( $pMsg = NULL, $pTitle = NULL ){
		global $gBitSmarty, $gBitSystem;
		$gBitSmarty->assign_by_ref( 'modMsg', $pMsg );
		$title = $pTitle != NULL ? $pTitle : tra('Moderation');
		$gBitSystem->display( 'bitpackage:moderation/display_msg.tpl', $title );
		die;
	}	
}

// Initialize the moderation system global if we haven't already
if ( empty( $gModerationSystem ) ) {
	$gModerationSystem = new ModerationSystem();
	// Store it in the context.
	$gBitSmarty->assign_by_ref('gModerationSystem', $gModerationSystem);
}

function moderation_content_expunge( &$pObject, &$pParamHash ) {
	global $gBitSystem;
	$query = "DELETE FROM `".BIT_DB_PREFIX."moderation` WHERE `content_id` = ?";
	$gBitSystem->mDb->query($query, array($pObject->mContentId));
}

?>
