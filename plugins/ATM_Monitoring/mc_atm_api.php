<?php
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * A webservice interface to Mantis Bug Tracker
 *
 * @package MantisBT
 * @copyright Copyright 2004  Victor Boctor - vboctor@users.sourceforge.net
 * @copyright Copyright 2005  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

use Mantis\Exceptions\ClientException;

/**
 * Retrieves all atms, unless the users
 *
 * @param string  $p_username    The user's username.
 * @param string  $p_password    The user's password.
 * @param integer $p_page_number The page number to return data for.
 * @param string  $p_per_page    The number of issues to return per page.
 * @return array The atm data
 */
function mc_atm_get_all( $p_username, $p_password, $p_page_number, $p_per_page ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_fault_login_failed();
	}

	if( !access_has_global_level( plugin_config_get( 'atm_view_threshold' ) ) ) {
		return mci_fault_access_denied( $t_user_id, 'No rights to view atms' );
	}

	if( $p_per_page == 0 ) {
		$p_per_page = 1;
	}

	$t_results = array();
	$t_total_results = atm_count( '' );
	$t_atms = atm_get_all( '', $p_per_page, $p_per_page *  ( $p_page_number - 1 ) );

	while( $t_atm = db_fetch_array( $t_atms ) ) {
		$t_atm['user_id'] = mci_account_get_array_by_id( $t_atm['user_id'] );
		$t_atm['date_created'] = ApiObjectFactory::datetime( $t_atm['date_created'] );
		$t_atm['date_updated'] = ApiObjectFactory::datetime( $t_atm['date_updated'] );
		$t_results[] = $t_atm;
	}

	log_event( LOG_WEBSERVICE,
		'retrieved ' . count( $t_results ) .
		'/' . $t_total_results . ' atms (page #' . $p_page_number . ')'
	);

	return array(
		'results' => $t_results,
		'total_results' => $t_total_results
	);
}

/**
 * Creates a atm
 *
 * @param string   $p_username The user's username.
 * @param string   $p_password The user's password.
 * @param stdClass $p_atm      The atm to create.
 * @return soap_fault|integer
 */
function mc_atm_add( $p_username, $p_password, stdClass $p_atm ) {
	$t_user_id = mci_check_login( $p_username, $p_password );

	if( $t_user_id === false ) {
		return mci_fault_login_failed();
	}

	if( !access_has_global_level( config_get( 'atm_create_threshold' ) ) ) {
		return mci_fault_access_denied( $t_user_id );
	}

	$t_valid_matches = array();

	$p_atm = ApiObjectFactory::objectToArray( $p_atm );

	$t_atm_terminal_id = $p_atm['terminal_id'];
	$t_atm_description = array_key_exists( 'description', $p_atm ) ? $p_atm['description'] : '';

	if( !atm_terminal_id_is_valid( $t_atm_terminal_id, $t_valid_matches ) ) {
		return ApiObjectFactory::faultBadRequest( 'Invalid atm terminal_id : "' . $t_atm_terminal_id . '"' );
	}

	$t_matching_by_terminal_id = atm_get_by_terminal_id( $t_atm_terminal_id );
	if( $t_matching_by_terminal_id != false ) {
		return ApiObjectFactory::faultConflict( 'A atm with the same terminal_id already exists , id: ' . $t_matching_by_terminal_id['id'] );
	}

	log_event( LOG_WEBSERVICE, 'creating atm \'' . $t_atm_terminal_id . '\' for user \'' . $t_user_id . '\'' );
	return atm_create( $t_atm_terminal_id, $t_user_id, $t_atm_description );
}

/**
 *
 * Deletes a atm
 *
 * @param string  $p_username The user's username.
 * @param string  $p_password The user's password.
 * @param integer $p_atm_id   The id of the atm.
 * @return soap_fault|boolean
 */
function mc_atm_delete( $p_username, $p_password, $p_atm_id ) {
	$t_user_id = mci_check_login( $p_username, $p_password );

	if( $t_user_id === false ) {
		return mci_fault_login_failed();
	}

	if( !access_has_global_level( config_get( 'atm_edit_threshold' ) ) ) {
		return mci_fault_access_denied( $t_user_id );
	}

	if( !atm_exists( $p_atm_id ) ) {
		return ApiObjectFactory::faultNotFound( 'No atm with id ' . $p_atm_id );
	}

	log_event( LOG_WEBSERVICE, 'deleting atm id \'' . $p_atm_id . '\'' );
	return atm_delete( $p_atm_id );
}

/**
 * Set atm(s) for a given issue id
 * @param integer $p_issue_id Issue id.
 * @param array   $p_atms     Array of atms.
 * @param integer $p_user_id  User id.
 * @return void|RestFault|SoapFault
 */
function mci_atm_set_for_issue ( $p_issue_id, $p_atms, $p_user_id ) {
	$t_atm_ids_to_attach = array();
	$t_atm_ids_to_detach = array();

	$t_submitted_atm_ids = array();
	$t_attached_atms = atm_bug_get_attached( $p_issue_id );
	$t_attached_atm_ids = array();
	foreach( $t_attached_atms as $t_attached_atm ) {
		$t_attached_atm_ids[] = $t_attached_atm['id'];
	}

	echo $t_atm;
	foreach( $p_atms as $t_atm ) {
		$t_atm = ApiObjectFactory::objectToArray( $t_atm );

		if( isset( $t_atm['id'] ) ) {
			$t_atm_id = $t_atm['id'];
			if( !atm_exists( $t_atm_id ) ) {
				throw new ClientException(
					"ATM with id $t_atm_id not found.",
					ERROR_ATM_NOT_FOUND
				);
			}
		} else if( isset( $t_atm['terminal_id'] ) ) {
			$t_get_atm = atm_get_by_terminal_id( $t_atm['terminal_id'] );
			if( $t_get_atm === false ) {
				throw new ClientException(
					"ATM '{$t_atm['terminal_id']}' not found.",
					ERROR_ATM_NOT_FOUND
				);
			}

			$t_atm_id = $t_get_atm['id'];
		} else {
			throw new ClientException(
				'ATM without id or terminal_id.',
				ERROR_TERMINAL_ID_INVALID
			);
		}

		$t_submitted_atm_ids[] = $t_atm_id;

		if( in_array( $t_atm_id, $t_attached_atm_ids ) ) {
			continue;
		}

		$t_atm_ids_to_attach[] = $t_atm_id;
	}

	foreach( $t_attached_atm_ids as $t_attached_atm_id ) {
		if( in_array( $t_attached_atm_id, $t_submitted_atm_ids ) ) {
			continue;
		}

		$t_atm_ids_to_detach[] = $t_attached_atm_id;
	}

	foreach( $t_atm_ids_to_detach as $t_atm_id ) {
		if( access_has_bug_level( plugin_config_get( 'atm_detach_threshold' ), $p_issue_id, $p_user_id ) ) {
			log_event( LOG_WEBSERVICE, 'detaching atm id \'' . $t_atm_id . '\' from issue \'' . $p_issue_id . '\'' );
			atm_bug_detach( $t_atm_id, $p_issue_id );
		}
	}

	foreach ( $t_atm_ids_to_attach as $t_atm_id ) {
		if( access_has_bug_level( plugin_config_get( 'atm_attach_threshold' ), $p_issue_id, $p_user_id ) ) {
			log_event( LOG_WEBSERVICE, 'attaching atm id \'' . $t_atm_id . '\' to issue \'' . $p_issue_id . '\'' );
			atm_bug_attach( $t_atm_id, $p_issue_id );
		}
	}
}
