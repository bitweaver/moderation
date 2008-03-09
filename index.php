<?php

require_once('../bit_setup_inc.php');
require_once('ModerationSystem.php');

// Are we trying to look at a single moderation?
if (isset($_REQUEST['moderation_id'])) {
	if( !empty($_REQUEST['transition']) ) {
		$gModerationSystem->setModerationReply($_REQUEST['moderation_id'],
											   $_REQUEST['transition'],
											   (empty($_REQUEST['reply']) ?
												NULL : $_REQUEST['reply']) );
		if ($_REQUEST['transition'] == MODERATION_DELETE) {
			bit_redirect(MODERATION_PKG_URL.'index.php');
		}
	}

	$moderation = $gModerationSystem->getModeration($_REQUEST['moderation_id']);
	// Do we have a valid moderation
	if ( ! empty( $moderation ) ) {
		// Verify that the user can see this moderation
		if ( $gBitUser->isAdmin() ||
			 $moderation['source_user_id'] == $gBitUser->mUserId ||
			 ( ! empty($moderation['moderator_user_id']) &&
			   $moderation['moderator_user_id'] == $gBitUser->mUserId ) ||
			 ( ! empty($moderation['moderation_group_id']) &&
			   $gBitUser->isInGroup($moderation['moderation_group_id'] ) ) ) {

			// Assign the moderation
			$gBitSmarty->assign('moderation', $moderation);

			// Check which way it is going
			if ( $moderation['source_user_id'] != $gBitUser->mUserId ) {
				/* TODO: Should probably just join what we need in the getModeration request */
				$gBitUser->mUserId = $moderation['source_user_id'];
				$gBitUser->load();
				$source_user = $gBitUser;
				$gBitSmarty->assign('source_user', $source_user);

				// Display the template
				$gBitSystem->display('bitpackage:moderation/moderate.tpl', 'Moderate Request');

			}
			else {
				// No need for the source user.
				$gBitSystem->display('bitpackage:moderation/request.tpl', 'My Request');
			}

			die;
		}
		else {
			$gBitSystem->setHttpStatus(403);
			$gBitSystem->fatalError(tra("You don't have permission to see this moderation."));
		}

	}
	else {
		$gBitSystem->setHttpStatus(404);
		$gBitSystem->fatalError(tra("There is no moderation with that id."));
	}
}

if (!$gBitUser->isAdmin()) {
	$myModerationHash = array('moderator_user_id' => $gBitUser->mUserId,
							  'moderator_group_id' => array_keys($gBitUser->getGroups()),
							  'source_user_id' => $gBitUser->mUserId,
							  'where_join' => 'OR');
}
else {
	$myModerationHash = array();
}
$myModerations = $gModerationSystem->getList($myModerationHash);
$gBitSmarty->assign('myModerations', $myModerations);

$gBitSystem->display('bitpackage:moderation/list_moderations.tpl', 'Moderations');

?>