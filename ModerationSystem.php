<?php

/**
 * @version $Header: /cvsroot/bitweaver/_bit_moderation/ModerationSystem.php,v 1.1 2008/01/22 21:13:44 nickpalmer Exp $
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
 * @version  $Revision: 1.1 $
 * @package  moderation
 */

global $gModerationSystem

/* The required moderation states. */
define( 'MODERATION_PENDING', "Pending" );
define( 'MODERATION_APPROVED', "Approved" );
define( 'MODERATION_REJECTED', "Rejected" );
define( 'MODERATION_DELETE', "Delete" );

/*
   A handy flag to turn on some validation of your transition table
   when you are developing moderation for a package.
*/
define( 'MODERATION_DEVELOPMENT', TRUE );

class ModerationSystem {

	/**
	 * The package registrations
	 */
	var $mPackages;

	/**
	 * Constructs a ModerationSystem. This shouldn't really be called.
	 * Use the $gModerationSystem instance instead which is created
	 * for you if you include this file.
	 */
	public ModerationSystem() {
		// Not much to do here
		$mPackages = array();
	}

    /**
	 * Request a moderation.
	 *
	 * Returns an ID for the moderation.
	 */
    public requestModeration( $pPackage, $pType,
							  $pModerationUser = NULL,
							  $pModerationGroup = NULL,
							  $pContentId = NULL
							  $pRequest = NULL ) {
		/* TODO: Validate and insert the request */
	}

    /**
	 * Register the callback function for a given package. When status
	 * on a packages queue changes this function will be called.
	 *
	 * The function should have the following API:
	 * handleModeration($pModeration) Where $pModeration is an array
	 * with all of the information in the moderation table.
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
	 * PENDING is the start state for all requests. MODERATION_DELETE
	 * is a special state which causes the moderation system to delete
	 * the moderation request from the system after making the callback
	 * to the package.
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
	 */
    public registerModerationListener( $pPackage, $pFunction, $pTransitions ) {
		global $gBitSystem;
		// Ensure that the transition table has the right structure
		// if we are in development mode. See above to turn this on.
		if ( ! MODERATION_DEVELOPMENT ||
			 $this->validateTransitions( $pTransitions) ) {
			// Save the registsration information for later.
			$mPackages[$pPackage]['name'] = $pPackage;
			$mPackages[$pPackage]['callback'] = $pFunction;
			$mPackages[$pPacakge]['types'] = array_keys($pStatuses);
			$mPackages[$pPackage]['transitions'] = $pTransitions;
		}
	}

	/**
	 * Validates that a transition map has the right structure.
	 * Note that this does not validate that the entire state tree
	 * has the required transisitons to lead to a delete state or that
	 * there are no dead end states in the map but it is a good
	 * check if you are developing moderation support for a package.
	 */
	private validateTransitions( $pTransitions ) {
		// Make sure we have an array of types
		if ( is_array( $pTransitions ) &&
			 count( array_keys( $pTransitions ) ) > 0 ){
			// Make sure that we have an array of states.
			foreach ( $pTransitions as $type => $states ) {
				$hasPending = $hasApproved = $hasRejected = $hasDelete = false;
				if ( ( ! is_array( $states ) ) or
					 count( array_keys( $states ) ) == 0 ) {
					$gBitSystem->fatalError("Invalid transition map given to the moderation system by the $pPackage package. $type does not lead to a state array.");
				}
				else {
					// Make sure that a state goes somewhere
					foreach ( $states as $state => $results ) {
						if ( $results == NULL ) {
							$gBitSystem->fatalError("Invalid transition map given to the moderation system by the $pPackage package. $state goes nowhere.");
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
	 * Sets the reply for a given request
	 */
	public setModerationReply( $pRequestId, $pReply )
	{
		/* TODO: Set the reply for a moderation */
	}

	/**
	 * Returns an array of pending moderations from the queue.
	 *
	 * List hash supports the following:
	 * moderator_user_id - The user_id or array of ids for the moderator.
	 * moderator_group_id - A group_id or array of ids for the moderators.
	 * package - The name of the package to restrict to.
	 * type - The type of moderation to restrict to.
	 * state - The state of the moderations to restrict to.
	 * source_user_id - The user_id of the creating user.
	 * content_id - The content_id to retrict to.
	 *
	 */
	public getList( $pListHash ) {

	}
}

// Initialize the moderation system global if we haven't already
if ( empty( $gModerationSystem ) ) {
	$gModerationSystem = new ModerationSystem();
}

?>