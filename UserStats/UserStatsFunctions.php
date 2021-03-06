<?php

class UserStatsFunctions {
	/**
	 * Updates user's points after they've made an edit in a namespace that is
	 * listed in the $wgNamespacesForEditPoints array.
	 */
	public static function incEditCount( $article, $revision, $baseRevId ) {
		global $wgUser, $wgNamespacesForEditPoints;

		// only keep tally for allowable namespaces
		if (
			!is_array( $wgNamespacesForEditPoints ) ||
			in_array( $article->getTitle()->getNamespace(), $wgNamespacesForEditPoints )
		) {
			$stats = new UserStatsTrack( $wgUser->getID(), $wgUser->getName() );
			$stats->incStatField( 'edit' );
		}

		return true;
	}

	/**
	 * Updates user's points after a page in a namespace that is listed in the
	 * $wgNamespacesForEditPoints array that they've edited has been deleted.
	 */
	public static function removeDeletedEdits( &$article, &$user, &$reason ) {
		global $wgNamespacesForEditPoints;

		// only keep tally for allowable namespaces
		if (
			!is_array( $wgNamespacesForEditPoints ) ||
			in_array( $article->getTitle()->getNamespace(), $wgNamespacesForEditPoints )
		) {
			$dbr = wfGetDB( DB_MASTER );
			$res = $dbr->select(
				'revision',
				array( 'rev_user_text', 'rev_user', 'COUNT(*) AS the_count' ),
				array( 'rev_page' => $article->getID(), 'rev_user <> 0' ),
				__METHOD__,
				array( 'GROUP BY' => 'rev_user_text' )
			);
			foreach ( $res as $row ) {
				$stats = new UserStatsTrack( $row->rev_user, $row->rev_user_text );
				$stats->decStatField( 'edit', $row->the_count );
			}
		}

		return true;
	}

	/**
	 * Updates user's points after a page in a namespace that is listed in the
	 * $wgNamespacesForEditPoints array that they've edited has been restored after
	 * it was originally deleted.
	 */
	public static function restoreDeletedEdits( &$title, $new ) {
		global $wgNamespacesForEditPoints;

		// only keep tally for allowable namespaces
		if (
			!is_array( $wgNamespacesForEditPoints ) ||
			in_array( $title->getNamespace(), $wgNamespacesForEditPoints )
		) {
			$dbr = wfGetDB( DB_MASTER );
			$res = $dbr->select(
				'revision',
				array( 'rev_user_text', 'rev_user', 'COUNT(*) AS the_count' ),
				array( 'rev_page' => $title->getArticleID(), 'rev_user <> 0' ),
				__METHOD__,
				array( 'GROUP BY' => 'rev_user_text' )
			);
			foreach ( $res as $row ) {
				$stats = new UserStatsTrack( $row->rev_user, $row->rev_user_text );
				$stats->incStatField( 'edit', $row->the_count );
			}
		}

		return true;
	}

}
