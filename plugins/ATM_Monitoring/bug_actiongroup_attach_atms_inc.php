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
 * Bug action group attach atms include file
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses atm_api.php
 */

if( !defined( 'BUG_ACTIONGROUP_INC_ALLOW' ) ) {
	return;
}

require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'atm_api.php' );

/**
 * Prints the title for the custom action page.
 * @return void
 */
function action_attach_atms_print_title() {
	echo plugin_lang_get( 'atm_attach_long' );
}

/**
 * Prints the table and form for the Attach ATMs group action page.
 * @return void
 */
function action_attach_atms_print_fields() {
	echo '<tr><th class="category">', plugin_lang_get( 'atm_attach_long' ), '</th><td>';
	print_atm_input();
	echo '<input type="submit" class="btn btn-primary btn-white btn-round btn-sm" value="' . plugin_lang_get( 'atm_attach' ) . ' " /></td></tr>';
}

/**
 * Validates the Attach ATMs group action.
 * Checks if a user can attach the requested atms to a given bug.
 * @param integer $p_bug_id A bug identifier.
 * @return string|null On failure: the reason for atms failing validation for the given bug. On success: null.
 */
function action_attach_atms_validate( $p_bug_id ) {
	global $g_action_attach_atms_atms;
	global $g_action_attach_atms_attach;
	global $g_action_attach_atms_create;

	$t_can_attach = access_has_bug_level( plugin_config_get( 'atm_attach_threshold' ), $p_bug_id );
	if( !$t_can_attach ) {
		return plugin_lang_get( 'atm_attach_denied' );
	}

	if( !isset( $g_action_attach_atms_atms ) ) {
		if( !isset( $g_action_attach_atms_attach ) ) {
			$g_action_attach_atms_attach = array();
			$g_action_attach_atms_create = array();
		}
		$g_action_attach_atms_atms = atm_parse_string( gpc_get_string( 'atm_string' ) );
		foreach ( $g_action_attach_atms_atms as $t_atm_row ) {
			if( $t_atm_row['id'] == -1 ) {
				$g_action_attach_atms_create[$t_atm_row['terminal_id']] = $t_atm_row;
			} else if( $t_atm_row['id'] >= 0 ) {
				$g_action_attach_atms_attach[$t_atm_row['terminal_id']] = $t_atm_row;
			}
		}
	}

	$t_can_create = access_has_bug_level( plugin_config_get( 'atm_create_threshold' ), $p_bug_id );
	if( count( $g_action_attach_atms_create ) > 0 && !$t_can_create ) {
		return plugin_lang_get( 'atm_create_denied' );
	}

	if( count( $g_action_attach_atms_create ) == 0 &&
		count( $g_action_attach_atms_attach ) == 0 ) {
		return plugin_lang_get( 'atm_none_attached' );
	}

	return null;
}

/**
 * Attaches all the atms to each bug in the group action.
 * @param integer $p_bug_id A bug identifier.
 * @return null Previous validation ensures that this function doesn't fail. Therefore we can always return null to indicate no errors occurred.
 */
function action_attach_atms_process( $p_bug_id ) {
	global $g_action_attach_atms_attach, $g_action_attach_atms_create;

	foreach( $g_action_attach_atms_create as $t_atm_row ) {
		$g_action_attach_atms_attach[] = array( 'terminal_id' => $t_atm_row['terminal_id'] );
	}

	$g_action_attach_atms_create = array();

	$t_data = array(
		'query' => array( 'issue_id' => $p_bug_id ),
		'payload' => array(
			'atms' => $g_action_attach_atms_attach
		)
	);

	$t_command = new ATMAttachCommand( $t_data );
	$t_command->execute();

	return null;
}
