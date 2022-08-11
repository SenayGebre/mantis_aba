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
 * ATM API
 *
 * @package CoreAPI
 * @subpackage ATMAPI
 * @author John Reese
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses antispam_api.php
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses database_api.php
 * @uses error_api.php
 * @uses form_api.php
 * @uses history_api.php
 * @uses lang_api.php
 * @uses string_api.php
 * @uses user_api.php
 * @uses utility_api.php
 */

require_api('access_api.php');
require_api('antispam_api.php');
require_api('authentication_api.php');
require_api('bug_api.php');
require_api('config_api.php');
require_api('constant_inc.php');
require_api('database_api.php');
require_api('error_api.php');
require_api('form_api.php');
require_api('history_api.php');
require_api('lang_api.php');
require_api('string_api.php');
require_api('user_api.php');
require_api('utility_api.php');
require_once( './plugins/ATM_Monitoring/constants.php' );

use Mantis\Exceptions\ClientException;

# cache the atm definitions, indexed by atm id
# atm ids that don't exist are stored as 'false', to avoid repeated searches
$g_cache_atms = array();

# cache the bug atms indexed by [bug_id, atm_id]. Items stored are rows (arrays) fetched from table {bug_atms}
# bugs with no atms will be stored as 'false'.
$g_cache_bug_atms = array();

/**
 * Loads into cache a set of atm definitions from atm table
 * Non existent ids are cached as 'false'
 * @global array $g_cache_atms
 * @param array $p_atm_ids	Array of atm ids
 * @return void
 */
function atm_cache_rows(array $p_atm_ids)
{
	global $g_cache_atms;

	$t_ids_to_search = array();
	foreach ($p_atm_ids as $t_id) {
		if (!isset($g_cache_atms[(int)$t_id])) {
			$t_ids_to_search[(int)$t_id] = (int)$t_id;
		}
	}
	if (empty($t_ids_to_search)) {
		return;
	}

	db_param_push();
	$t_sql_in_params = array();
	$t_params = array();
	foreach ($t_ids_to_search as $t_id) {
		$t_sql_in_params[] = db_param();
		$t_params[] = $t_id;
	}
	$t_query = 'SELECT * FROM ' . plugin_table('atm') . ' where id IN (' . implode(',', $t_sql_in_params) . ')';
	$t_result = db_query($t_query, $t_params);

	while ($t_row = db_fetch_array($t_result)) {
		$c_id = (int)$t_row['id'];
		$g_cache_atms[$c_id] = $t_row;
		unset($t_ids_to_search[$c_id]);
	}
	# mark the non existent ids
	foreach ($t_ids_to_search as $t_id) {
		$g_cache_atms[$t_id] = false;
	}
}

/**
 * Loads into cache the atms associated to a set of bug ids
 * A bug id that has no atms will be cached as 'false'
 * @global array $g_cache_bug_atms
 * @param array $p_bug_ids	Array of bug ids
 * @return void
 */
function atm_cache_bug_atm_rows(array $p_bug_ids)
{
	global $g_cache_bug_atms;

	$t_ids_to_search = array();
	foreach ($p_bug_ids as $t_id) {
		if (!isset($g_cache_bug_atms[(int)$t_id])) {
			$t_ids_to_search[] = (int)$t_id;
		}
	}

	if (empty($t_ids_to_search)) {
		return;
	}

	db_param_push();
	$t_sql_in_params = array();
	$t_params = array();
	foreach ($t_ids_to_search as $t_id) {
		$t_sql_in_params[] = db_param();
		$t_params[] = $t_id;
	}
	$t_query = 'SELECT B.id AS bug_id, BT.atm_id, BT.user_id, BT.date_attached FROM {bug} B LEFT OUTER JOIN ' . plugin_table('bug_atm') . ' BT ON B.id=BT.bug_id'
		. ' WHERE B.id IN (' . implode(',', $t_sql_in_params) . ')';
	$t_result = db_query($t_query, $t_params);

	$t_found_atms = array();
	while ($t_row = db_fetch_array($t_result)) {
		$c_bug_id = (int)$t_row['bug_id'];
		$t_has_atms = !empty($t_row['atm_id']);
		# create a bug index if needed
		if (!isset($g_cache_bug_atms[$c_bug_id])) {
			$g_cache_bug_atms[$c_bug_id] = $t_has_atms ? array() : false;
		}
		if ($t_has_atms) {
			$c_atm_id = (int)$t_row['atm_id'];
			$g_cache_bug_atms[$c_bug_id][$c_atm_id] = $t_row;
			$t_found_atms[$c_atm_id] = $c_atm_id;
		}
	}
	# also cache the atms founds
	if (!empty($t_found_atms)) {
		atm_cache_rows($t_found_atms);
	}
}

/**
 * Clear the bug atms cache (or just the given bug id if specified)
 * @global array $g_cache_bug_atms
 * @param integer $p_bug_id	Bug id
 * @return void
 */
function atm_clear_cache_bug_atms($p_bug_id = null)
{
	global $g_cache_bug_atms;

	if (null === $p_bug_id) {
		$g_cache_bug_atms = array();
	} else {
		if (isset($g_cache_bug_atms[(int)$p_bug_id])) {
			unset($g_cache_bug_atms[(int)$p_bug_id]);
		}
	}
}

/**
 * Determine if a atm exists with the given ID.
 * @param integer $p_atm_id A atm ID to check.
 * @return boolean True if atm exists
 */
function atm_exists($p_atm_id)
{
	return (atm_get($p_atm_id) !== false);
}

/**
 * Ensure a atm exists with the given ID.
 * @param integer $p_atm_id A atm ID to check.
 * @return void
 */
function atm_ensure_exists($p_atm_id)
{
	if (!atm_exists($p_atm_id)) {
		throw new ClientException(
			sprintf("ATM '%d' does not exist", $p_atm_id),
			ERROR_ATM_NOT_FOUND,
			array($p_atm_id)
		);
	}
}

/**
 * Determine if a given terminal_id is unique (not already used).
 * Uses a case-insensitive search of the database for existing atms with the same terminal_id.
 * @param string $p_terminal_id The atm terminal_id to check.
 * @return boolean True if terminal_id is unique
 */
function atm_is_unique($p_terminal_id)
{
	$c_terminal_id = trim($p_terminal_id);

	$t_query = 'SELECT id FROM ' . plugin_table('atm') . '  WHERE ' . db_helper_like('terminal_id');
	$t_result = db_query($t_query, array($c_terminal_id));

	if (db_result($t_result)) {
		return false;
	}
	return true;
}

/**
 * Ensure that a terminal_id is unique.
 * @param string $p_terminal_id The atm terminal_id to check.
 * @return void
 */
function atm_ensure_unique($p_terminal_id)
{
	if (!atm_is_unique($p_terminal_id)) {
		error_parameters($p_terminal_id);
		trigger_error(ERROR_ATM_DUPLICATE, ERROR);
	}
}

/**
 * Determine if a given terminal_id is valid.
 *
 * terminal_id must not begin with '+' and '-' characters (they are used for
 * filters) and must not contain the configured atm separator.
 * The matches parameter allows to also receive an array of regex matches,
 * which by default only includes the valid atm terminal_id itself.
 * The prefix parameter is optional, but allows you to prefix the regex
 * check, which is useful for filters, etc.
 * @param string $p_terminal_id     The atm terminal_id to check.
 * @param array  &$p_matches Array reference for regex matches.
 * @param string $p_prefix   The regex pattern to use as a prefix.
 * @return boolean True if the terminal_id is valid.
 */
function atm_terminal_id_is_valid($p_terminal_id, array &$p_matches, $p_prefix = '')
{
	$t_separator = plugin_config_get('atm_separator');
	$t_pattern = '/^' . $p_prefix . '([^\+\-' . $t_separator . '][^' . $t_separator . ']*)$/';
	return preg_match($t_pattern, $p_terminal_id, $p_matches);
}

function atm_ip_address_is_valid($p_ip_address, array &$p_matches, $p_prefix = '')
{
	return filter_var($p_ip_address, FILTER_VALIDATE_IP);
}

/**
 * Ensure a atm terminal_id is valid.
 * @param string $p_terminal_id The atm terminal_id to check.
 * @return void
 */
function atm_ensure_terminal_id_is_valid($p_terminal_id)
{
	$t_matches = array();
	if (!atm_terminal_id_is_valid($p_terminal_id, $t_matches)) {
		error_parameters($p_terminal_id);
		trigger_error(ERROR_TERMINAL_ID_INVALID, ERROR);
	}
}

// function atm_ensure_terminal_ip_address_is_valid($p_terminal_id)
// {
// 	$t_matches = array();
// 	if (!atm_ip_address_is_valid($p_terminal_id, $t_matches)) {
// 		error_parameters($p_terminal_id);
// 		trigger_error(ERROR_TERMINAL_IP_ADDRESS_INVALID, ERROR);
// 	}
// }

/**
 * Compare two atm rows based on atm terminal_id.
 * @param array $p_atm1 The first atm row to compare.
 * @param array $p_atm2 The second atm row to compare.
 * @return int -1 when ATM 1 < ATM 2, 1 when ATM 1 > ATM 2, 0 otherwise
 */
function atm_cmp_terminal_id(array $p_atm1, array $p_atm2)
{
	return strcasecmp($p_atm1['terminal_id'], $p_atm2['terminal_id']);
}

/**
 * Parse a form input string to extract existing and new atms.
 * When given a string, parses for atm terminal_ids separated by configured separator,
 * then returns an array of atm rows for each atm.  Existing atms get the full
 * row of information returned.  If the atm does not exist, a row is returned with
 * id = -1 and the atm terminal_id, and if the terminal_id is invalid, a row is returned with
 * id = -2 and the atm terminal_id.  The resulting array is then sorted by atm terminal_id.
 * @param string $p_string Input string to parse.
 * @return array Rows of atms parsed from input string
 */
function atm_parse_string($p_string)
{
	$t_atms = array();

	$t_strings = explode(plugin_config_get('atm_separator'), $p_string);
	foreach ($t_strings as $t_terminal_id) {
		$t_terminal_id = trim($t_terminal_id);
		if (is_blank($t_terminal_id)) {
			continue;
		}

		$t_matches = array();
		$t_atm_row = atm_get_by_terminal_id($t_terminal_id);
		if ($t_atm_row !== false) {
			$t_atms[] = $t_atm_row;
		} else {
			if (atm_terminal_id_is_valid($t_terminal_id, $t_matches)) {
				$t_id = -1;
			} else {
				$t_id = -2;
			}
			$t_atms[] = array(
				'id' => $t_id,
				'terminal_id' => $t_terminal_id,
			);
		}
	}
	usort($t_atms, 'atm_cmp_terminal_id');
	return $t_atms;
}

/**
 * Attaches a bunch of atms to the specified issue.
 *
 * @param int    $p_bug_id     The bug id.
 * @param string $p_atm_string String of atms separated by configured separator.
 * @param int    $p_atm_id     ATM id to add or 0 to skip.
 * @return array|bool true for success, otherwise array of failures.  The array elements follow the atm_parse_string()
 *                    format.
 */
function atm_attach_many($p_bug_id, $p_atm_string, $p_atm_id = 0)
{
	# If no work, then there is no need to do access check.
	if ($p_atm_id === 0 && is_blank($p_atm_string)) {
		return true;
	}

	access_ensure_bug_level(plugin_config_get('atm_attach_threshold'), $p_bug_id);

	$t_atms = atm_parse_string($p_atm_string);
	$t_can_create = atm_can_create();

	$t_atms_create = array();
	$t_atms_attach = array();
	$t_atms_failed = array();

	foreach ($t_atms as $t_atm_row) {
		if (-1 == $t_atm_row['id']) {
			if ($t_can_create) {
				$t_atms_create[] = $t_atm_row;
			} else {
				$t_atms_failed[] = $t_atm_row;
			}
		} else if (-2 == $t_atm_row['id']) {
			$t_atms_failed[] = $t_atm_row;
		} else {
			$t_atms_attach[] = $t_atm_row;
		}
	}

	if (0 < $p_atm_id && atm_exists($p_atm_id)) {
		$t_atms_attach[] = atm_get($p_atm_id);
	}

	# failed to attach at least one atm
	if (count($t_atms_failed) > 0) {
		return $t_atms_failed;
	}

	foreach ($t_atms_create as $t_atm_row) {
		$t_atm_row['id'] = atm_create($t_atm_row['terminal_id']);
		$t_atms_attach[] = $t_atm_row;
	}

	foreach ($t_atms_attach as $t_atm_row) {
		if (!atm_bug_is_attached($t_atm_row['id'], $p_bug_id)) {
			atm_bug_attach($t_atm_row['id'], $p_bug_id);
		}
	}

	event_signal('EVENT_ATM_ATTACHED', array($p_bug_id, $t_atms_attach));
	return true;
}

/**
 * Parse a filter string to extract existing and new atms.
 * When given a string, parses for atm terminal_ids separated by configured separator,
 * then returns an array of atm rows for each atm.  Existing atms get the full
 * row of information returned.  If the atm does not exist, a row is returned with
 * id = -1 and the atm terminal_id, and if the terminal_id is invalid, a row is returned with
 * id = -2 and the atm terminal_id.  The resulting array is then sorted by atm terminal_id.
 * @param string $p_string Filter string to parse.
 * @return array Rows of atms parsed from filter string
 */
function atm_parse_filters($p_string)
{
	$t_atms = array();
	$t_prefix = '[+-]{0,1}';

	$t_strings = explode(plugin_config_get('atm_separator'), $p_string);
	foreach ($t_strings as $t_terminal_id) {
		$t_terminal_id = trim($t_terminal_id);
		$t_matches = array();

		if (!is_blank($t_terminal_id) && atm_terminal_id_is_valid($t_terminal_id, $t_matches, $t_prefix)) {
			$t_atm_row = atm_get_by_terminal_id($t_matches[1]);
			if ($t_atm_row !== false) {
				$t_filter = mb_substr($t_terminal_id, 0, 1);

				if ('+' == $t_filter) {
					$t_atm_row['filter'] = 1;
				} else if ('-' == $t_filter) {
					$t_atm_row['filter'] = -1;
				} else {
					$t_atm_row['filter'] = 0;
				}

				$t_atms[] = $t_atm_row;
			}
		} else {
			continue;
		}
	}
	usort($t_atms, 'atm_cmp_terminal_id');
	return $t_atms;
}

/**
 * Returns all available atms
 *
 * @param integer $p_terminal_id_filter A string to match the beginning of the atm terminal_id.
 * @param integer $p_count       The number of atms to return.
 * @param integer $p_offset      The offset of the result.
 *
 * @return ADORecordSet|boolean ATMs sorted by terminal_id, or false if the query failed.
 */
function atm_get_all($p_terminal_id_filter, $p_count, $p_offset)
{
	$t_where = '';
	$t_where_params = array();

	if (!is_blank($p_terminal_id_filter)) {
		$t_where = 'WHERE ' . db_helper_like('terminal_id');
		$t_where_params[] = $p_terminal_id_filter . '%';
	}

	$t_query = 'SELECT * FROM ' . plugin_table('atm') . '  ' . $t_where . ' ORDER BY terminal_id';

	return db_query($t_query, $t_where_params, $p_count, $p_offset);
}

/**
 * Counts all available atms
 * @param integer $p_terminal_id_filter A string to match the beginning of the atm terminal_id.
 * @return integer
 */
function atm_count($p_terminal_id_filter)
{
	$t_where = '';
	$t_where_params = array();

	if ($p_terminal_id_filter) {
		$t_where = ' WHERE ' . db_helper_like('terminal_id');
		$t_where_params[] = $p_terminal_id_filter . '%';
	}

	$t_query = 'SELECT count(*) FROM ' . plugin_table('atm') . ' ' . $t_where;

	$t_result = db_query($t_query, $t_where_params);
	$t_row = db_fetch_array($t_result);
	return (int)db_result($t_result);
}

/**
 * Return a atm row for the given ID.
 * @param integer $p_atm_id The atm ID to retrieve from the database.
 * @return boolean|array ATM row, or false if not found
 */
function atm_get($p_atm_id)
{
	global $g_cache_atms;

	$c_atm_id = (int)$p_atm_id;
	if (!isset($g_cache_atms[$c_atm_id])) {
		atm_cache_rows(array($c_atm_id));
	}

	$t_atm = $g_cache_atms[$c_atm_id];
	if (null === $t_atm) {
		return false;
	} else {
		return $t_atm;
	}
}

/**
 * Get atm terminal_id by id.
 * @param integer $p_atm_id The atm ID to retrieve from the database.
 * @return string atm terminal_id or empty string if not found.
 */
function atm_get_terminal_id($p_atm_id)
{
	$t_atm_row = atm_get($p_atm_id);
	if ($t_atm_row === false) {
		return '';
	}

	return $t_atm_row['terminal_id'];
}

/**
 * Return a atm row for the given terminal_id.
 * @param string $p_terminal_id The atm terminal_id to retrieve from the database.
 * @return array|false ATM row
 */
function atm_get_by_terminal_id($p_terminal_id)
{
	db_param_push();
	$t_query = 'SELECT * FROM ' . plugin_table('atm') . '  WHERE ' . db_helper_like('terminal_id');
	$t_result = db_query($t_query, array($p_terminal_id));

	$t_row = db_fetch_array($t_result);

	if (!$t_row) {
		return false;
	}

	return $t_row;
}

/**
 * Return a single field from a atm row for the given ID.
 * @param integer $p_atm_id     The atm id to lookup.
 * @param string  $p_field_name The field name to retrieve from the atm.
 * @return array Field value
 */
function atm_get_field($p_atm_id, $p_field_name)
{
	$t_row = atm_get($p_atm_id);

	if (isset($t_row[$p_field_name])) {
		return $t_row[$p_field_name];
	} else {
		error_parameters($p_field_name);
		trigger_error(ERROR_DB_FIELD_NOT_FOUND, WARNING);
		return '';
	}
}

/**
 * Can the specified user create a atm?
 *
 * @param integer $p_user_id The id of the user to check access rights for.
 * @return bool true: can create, false: otherwise.
 */
function atm_can_create($p_user_id = null)
{
	return access_has_global_level(plugin_config_get('atm_create_threshold'), $p_user_id);
}

/**
 * Ensure specified user can create atms.
 *
 * @param integer $p_user_id The id of the user to check access rights for.
 * @return void
 */
function atm_ensure_can_create($p_user_id = null)
{
	access_ensure_global_level(plugin_config_get('atm_create_threshold'), $p_user_id);
}

/**
 * Create a atm with the given terminal_id, creator, and description.
 * Defaults to the currently logged in user, and a blank description.
 * @param string  $p_terminal_id        The atm terminal_id to create.
 * @param integer $p_user_id     The user ID to link the new atm to.
 * @param string  $p_description A Description for the atm.
 * @return int ATM ID
 */
function atm_create(
	$p_terminal_id,
	$p_user_id = null,
	$p_branch_name = null,
	$p_model = null,
	$p_ip_address = null,
	$p_port = null,
	$p_country = '',
	$p_city = '',
	$p_specifc_location = ''
) {
	atm_ensure_can_create($p_user_id);

	atm_ensure_terminal_id_is_valid($p_terminal_id);
	atm_ensure_unique($p_terminal_id);

	if (null == $p_user_id) {
		$p_user_id = auth_get_current_user_id();
	} else {
		user_ensure_exists($p_user_id);
	}
	date_default_timezone_set("Africa/Nairobi");
	$c_date_created = date('y-m-d h:i');
	$c_date_updated =  date('y-m-d h:i');
	db_param_push();
	$t_query = 'INSERT INTO ' . plugin_table('atm') . ' 
				( user_id, terminal_id, branch_name, model, ip_address, port,  country, city, specifc_location, date_created, date_updated )
				VALUES
				( ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param()  . ',
				 ' . db_param() . ',' . db_param() . ',' . db_param() . ',' . db_param() . ', ' . db_param() . ',' . db_param()  . ')';
	db_query($t_query, array($p_user_id, $p_terminal_id, $p_branch_name, $p_model, $p_ip_address, $p_port, $p_country, $p_city, $p_specifc_location, $c_date_created, $c_date_updated));

	return db_insert_id(db_get_table(plugin_table('atm')));
}

/**
 * Update a atm with given terminal_id, creator, and description.
 * @param integer $p_atm_id      The atm ID which is being updated.
 * @param string  $p_terminal_id        The terminal_id of the atm.
 * @param integer $p_user_id     The user ID to set when updating the atm.
 *                               Note: This replaces the existing user id.
 * @param string  $p_description An updated description for the atm.
 * @return boolean
 * @throws ClientException
 */
function atm_update(
	$p_atm_id,
	$p_terminal_id,
	$p_user_id,	
	$p_branch_name,
	$p_model,
	$p_ip_address,
	$p_port,
	$p_country,
	$p_city,
	$p_specifc_location
)
{
	$t_atm_row = atm_get($p_atm_id);
	$t_atm_terminal_id = $t_atm_row['terminal_id'];

	if (
		$t_atm_terminal_id == $p_terminal_id &&
		$t_atm_row['user_id'] == $p_user_id &&
		$t_atm_row['branch_name'] == $p_branch_name &&
		$t_atm_row['model'] == $p_model &&
		$t_atm_row['ip_address'] == $p_ip_address &&
		$t_atm_row['port'] == $p_port &&
		$t_atm_row['country'] == $p_country &&
		$t_atm_row['city'] == $p_city &&
		$t_atm_row['specifc_location'] == $p_specifc_location
	) {
		# nothing has changed
		return true;
	}

	user_ensure_exists($p_user_id);

	if (auth_get_current_user_id() == $t_atm_row['user_id']) {
		$t_update_level = plugin_config_get('atm_edit_own_threshold');
	} else {
		$t_update_level = plugin_config_get('atm_edit_threshold');
	}
	access_ensure_global_level($t_update_level);

	atm_ensure_terminal_id_is_valid($p_terminal_id);

	# Do not allow assigning a atm to a user who is not allowed to create one
	if (!access_has_global_level(plugin_config_get('atm_create_threshold'), $p_user_id)) {
		trigger_error(ERROR_USER_DOES_NOT_HAVE_REQ_ACCESS, ERROR);
	}

	$t_rename = false;
	if (mb_strtolower($p_terminal_id) != mb_strtolower($t_atm_terminal_id)) {
		atm_ensure_unique($p_terminal_id);
		$t_rename = true;
	}
	date_default_timezone_set("Africa/Nairobi");

	$c_date_updated =  date('y-m-d h:i');
	

	db_param_push();
	$t_query = 'UPDATE ' . plugin_table('atm') . ' 
					SET user_id=' . db_param() . ',
						terminal_id=' . db_param() . ',
						branch_name=' . db_param() . ',
						model=' . db_param() . ',
						ip_address=' . db_param() . ',
						port=' . db_param() . ',
						country=' . db_param() . ',
						city=' . db_param() . ',
						specifc_location=' . db_param() . ',
						date_updated=' . db_param() . '
					WHERE id=' . db_param();
	db_query($t_query, array((int)$p_user_id, $p_terminal_id,  $p_branch_name, $p_model, $p_ip_address, $p_port, $p_country, $p_city, $p_specifc_location, $c_date_updated, $p_atm_id));

	if ($t_rename) {
		$t_bugs = atm_get_bugs_attached($p_atm_id);

		foreach ($t_bugs as $t_bug_id) {
			history_log_event_special($t_bug_id, ATM_TERMINAL_ID_CHANGED, $t_atm_terminal_id, $p_terminal_id);
		}
	}

	return true;
}

/**
 * Delete a atm with the given ID.
 * @param integer $p_atm_id The atm ID to delete.
 * @return boolean
 */
function atm_delete($p_atm_id)
{
	atm_ensure_exists($p_atm_id);

	access_ensure_global_level(plugin_config_get('atm_edit_threshold'));

	$t_bugs = atm_get_bugs_attached($p_atm_id);
	foreach ($t_bugs as $t_bug_id) {
		atm_bug_detach($p_atm_id, $t_bug_id);
	}

	db_param_push();
	$t_query = 'DELETE FROM ' . plugin_table('atm') . '  WHERE id=' . db_param();
	db_query($t_query, array($p_atm_id));

	return true;
}

/**
 * Gets the atms that are not associated with the specified bug.
 *
 * @param integer $p_bug_id The bug id, if 0 returns all available atms.
 *
 * @return array List of atm rows, each with id, terminal_id, and description.
 */
function atm_get_candidates_for_bug($p_bug_id)
{
	db_param_push();
	$t_query = 'SELECT id, terminal_id, description FROM ' . plugin_table('atm') . ' ';
	$t_params = array();

	if (0 != $p_bug_id) {
		$t_assoc_atms_query = 'SELECT atm_id FROM ' . plugin_table('bug_atm') . '  WHERE bug_id = ' . db_param();
		$t_params[] = $p_bug_id;

		# Define specific where clause to exclude atms already attached to the bug
		# Special handling for odbc_mssql which does not support bound subqueries (#14774)
		if (config_get_global('db_type') == 'odbc_mssql') {
			db_param_push();
			$t_result = db_query($t_assoc_atms_query, $t_params);

			$t_subquery_results = array();
			while ($t_row = db_fetch_array($t_result)) {
				$t_subquery_results[] = (int)$t_row['atm_id'];
			}
			if ($t_subquery_results) {
				$t_where = ' WHERE id NOT IN (' . implode(', ', $t_subquery_results) . ')';
			} else {
				$t_where = '';
			}
			$t_params = null;
		} else {
			$t_where = " WHERE id NOT IN ($t_assoc_atms_query)";
		}
		$t_query .= $t_where;
	}

	$t_query .= ' ORDER BY terminal_id ASC ';
	$t_result = db_query($t_query, $t_params);

	$t_results_to_return = array();

	while ($t_row = db_fetch_array($t_result)) {
		$t_results_to_return[] = $t_row;
	}

	return $t_results_to_return;
}

/**
 * Determine if a atm is attached to a bug.
 * @param integer $p_atm_id The atm ID to check.
 * @param integer $p_bug_id The bug ID to check.
 * @return boolean True if the atm is attached
 */
function atm_bug_is_attached($p_atm_id, $p_bug_id)
{
	db_param_push();
	$t_query = 'SELECT bug_id FROM ' . plugin_table('bug_atm') . '  WHERE atm_id=' . db_param() . ' AND bug_id=' . db_param();
	$t_result = db_query($t_query, array($p_atm_id, $p_bug_id));
	return (db_result($t_result) !== false);
}

/**
 * Return the atm attachment row.
 * @param integer $p_atm_id The atm ID to check.
 * @param integer $p_bug_id The bug ID to check.
 * @return array ATM attachment row
 */
function atm_bug_get_row($p_atm_id, $p_bug_id)
{
	global $g_cache_bug_atms;

	$c_bug_id = (int)$p_bug_id;
	if (!isset($g_cache_bug_atms[$c_bug_id])) {
		atm_cache_bug_atm_rows(array($c_bug_id));
	}

	$t_bug_atms = $g_cache_bug_atms[$c_bug_id];
	if (!$t_bug_atms || !isset($t_bug_atms[$p_atm_id])) {
		trigger_error(ERROR_ATM_NOT_ATTACHED, ERROR);
	}
	return $t_bug_atms[$p_atm_id];
}

/**
 * Return an array of atms attached to a given bug sorted by atm terminal_id.
 * @param integer $p_bug_id The bug ID to check.
 * @return array Array of atm rows with attachment information
 */
function atm_bug_get_attached($p_bug_id)
{
	global $g_cache_bug_atms;

	$c_bug_id = (int)$p_bug_id;
	if (!isset($g_cache_bug_atms[$c_bug_id])) {
		atm_cache_bug_atm_rows(array($c_bug_id));
	}

	$t_bug_atms = $g_cache_bug_atms[$c_bug_id];
	if (!$t_bug_atms) {
		return array();
	}

	$t_atm_info_rows = array();
	foreach ($t_bug_atms as $t_row) {
		$t_atm_data = atm_get($t_row['atm_id']);
		$t_atm_data['user_attached'] = $t_row['user_id'];
		$t_atm_data['date_attached'] = $t_row['date_attached'];
		$t_atm_info_rows[] = $t_atm_data;
	}
	usort($t_atm_info_rows, 'atm_cmp_terminal_id');
	return $t_atm_info_rows;
}

/**
 * Return an array of bugs that a atm is attached to.
 * @param integer $p_atm_id The atm ID to check.
 * @return array Array of bug ID's.
 */
function atm_get_bugs_attached($p_atm_id)
{
	db_param_push();
	$t_query = 'SELECT bug_id FROM ' . plugin_table('bug_atm') . '  WHERE atm_id=' . db_param();
	$t_result = db_query($t_query, array($p_atm_id));

	$t_bugs = array();
	while ($t_row = db_fetch_array($t_result)) {
		$t_bugs[] = $t_row['bug_id'];
	}

	return $t_bugs;
}

/**
 * Attach a atm to a bug.
 * @param integer $p_atm_id  The atm ID to attach.
 * @param integer $p_bug_id  The bug ID to attach.
 * @param integer $p_user_id The user ID to attach.
 * @return boolean
 */
function atm_bug_attach($p_atm_id, $p_bug_id, $p_user_id = null)
{
	antispam_check();

	access_ensure_bug_level(plugin_config_get('atm_attach_threshold'), $p_bug_id, $p_user_id);

	atm_ensure_exists($p_atm_id);

	if (atm_bug_is_attached($p_atm_id, $p_bug_id)) {
		trigger_error(ERROR_ATM_ALREADY_ATTACHED, ERROR);
	}


	if (null == $p_user_id) {
		$p_user_id = auth_get_current_user_id();
	} else {
		user_ensure_exists($p_user_id);
	}

	db_param_push();
	$t_query = 'INSERT INTO ' . plugin_table('bug_atm') . ' 
					( atm_id, bug_id, user_id, date_attached )
					VALUES
					( ' . db_param() . ',' . db_param() . ',' . db_param() . ',' . db_param() . ')';
	db_query($t_query, array($p_atm_id, $p_bug_id, $p_user_id, db_now()));

	atm_clear_cache_bug_atms($p_bug_id);
	
	$t_atm_terminal_id = atm_get_field($p_atm_id, 'terminal_id');
	history_log_event_special($p_bug_id, ATM_ATTACHED, $t_atm_terminal_id);

	# updated the last_updated date
	bug_update_date($p_bug_id);

	return true;
}

/**
 * Detach a atm from a bug.
 * @param integer $p_atm_id      The atm ID to detach.
 * @param integer $p_bug_id      The bug ID to detach.
 * @param boolean $p_add_history Add history entries to bug.
 * @param integer $p_user_id     User Id (or null for current logged in user).
 * @return boolean
 */
function atm_bug_detach($p_atm_id, $p_bug_id, $p_add_history = true, $p_user_id = null)
{
	if ($p_user_id === null) {
		$t_user_id = auth_get_current_user_id();
	} else {
		$t_user_id = $p_user_id;
	}

	if (!atm_bug_is_attached($p_atm_id, $p_bug_id)) {
		trigger_error(ERROR_ATM_NOT_ATTACHED, ERROR);
	}

	$t_atm_row = atm_bug_get_row($p_atm_id, $p_bug_id);
	if ($t_user_id == atm_get_field($p_atm_id, 'user_id') || $t_user_id == $t_atm_row['user_id']) {
		$t_detach_level = plugin_config_get('atm_detach_own_threshold');
	} else {
		$t_detach_level = plugin_config_get('atm_detach_threshold');
	}

	if (!access_has_bug_level($t_detach_level, $p_bug_id, $t_user_id)) {
		throw new ClientException(
			sprintf("Access denied to detach '%s'", $t_atm_row['terminal_id']),
			ERROR_ACCESS_DENIED
		);
	}

	db_param_push();
	$t_query = 'DELETE FROM ' . plugin_table('bug_atm') . '  WHERE atm_id=' . db_param() . ' AND bug_id=' . db_param();
	db_query($t_query, array($p_atm_id, $p_bug_id));

	atm_clear_cache_bug_atms($p_bug_id);

	if ($p_add_history) {
		$t_atm_terminal_id = atm_get_field($p_atm_id, 'terminal_id');
		history_log_event_special($p_bug_id, ATM_DETACHED, $t_atm_terminal_id);
	}

	# updated the last_updated date
	bug_update_date($p_bug_id);

	return true;
}

/**
 * Detach all atms from a given bug.
 * @param integer $p_bug_id      The bug ID to detach.
 * @param boolean $p_add_history Add history entries to bug.
 * @param integer $p_user_id     User Id (or null for current logged in user).
 * @return void
 */
function atm_bug_detach_all($p_bug_id, $p_add_history = true, $p_user_id = null)
{
	$t_atms = atm_bug_get_attached($p_bug_id);
	foreach ($t_atms as $t_atm_row) {
		atm_bug_detach($t_atm_row['id'], $p_bug_id, $p_add_history, $p_user_id);
	}
}

/**
 * Builds a hyperlink to the ATM Detail page
 * @param array $p_atm_row ATM row.
 * @return string
 */
function atm_get_link(array $p_atm_row)
{
	return sprintf(
		'<a class="btn btn-xs btn-primary btn-white btn-round" href="atm_view_page.php?atm_id=%s" title="%s">%s</a>',
		$p_atm_row['id'],
		string_display_line($p_atm_row['description']),
		string_display_line($p_atm_row['terminal_id'])
	);
}

/**
 * Display a atm hyperlink.
 * If a bug ID is passed, the atm link will include a detach link if the
 * user has appropriate privileges.
 * @param array   $p_atm_row ATM row.
 * @param integer $p_bug_id  The bug ID to display.
 * @return boolean
 */
function atm_display_link(array $p_atm_row, $p_bug_id = 0)
{
	static $s_security_token = null;
	if (is_null($s_security_token)) {
		$s_security_token = htmlspecialchars(form_security_param('atm_detach'));
	}

	echo atm_get_link($p_atm_row);

	if (
		isset($p_atm_row['user_attached']) && auth_get_current_user_id() == $p_atm_row['user_attached']
		|| auth_get_current_user_id() == $p_atm_row['user_id']
	) {
		$t_detach = plugin_config_get('atm_detach_own_threshold');
	} else {
		$t_detach = plugin_config_get('atm_detach_threshold');
	}

	if ($p_bug_id > 0 && access_has_bug_level($t_detach, $p_bug_id)) {
		$t_tooltip = string_html_specialchars(sprintf(plugin_lang_get('atm_detach'), string_display_line($p_atm_row['terminal_id'])));
		$t_href = 'atm_detach.php?bug_id=' . $p_bug_id . '&amp;atm_id=' . $p_atm_row['id'] . $s_security_token;
		echo ' <a class="btn btn-xs btn-primary btn-white btn-round" title="' . $t_tooltip . '" href="' . $t_href . '">';
		print_icon('fa-times');
		echo '</a>';
	}

	return true;
}

/**
 * Display a list of attached atm hyperlinks separated by the configured hyperlinks.
 * @param integer $p_bug_id The bug ID to display.
 * @return boolean
 */
function atm_display_attached($p_bug_id)
{
	$t_atm_rows = atm_bug_get_attached($p_bug_id);

	if (count($t_atm_rows) == 0) {
		echo plugin_lang_get('atm_none_attached');
	} else {
		$i = 0;
		foreach ($t_atm_rows as $t_atm) {
			echo ($i > 0 ? plugin_config_get('atm_separator') . ' ' : '');
			atm_display_link($t_atm, $p_bug_id);
			$i++;
		}
	}

	return true;
}

/**
 * Get all attached atms separated by the ATM Separator.
 * @param integer $p_bug_id The bug ID to display.
 * @return string atms separated by the configured ATM Separator
 */
function atm_bug_get_all($p_bug_id)
{
	$t_atm_rows = atm_bug_get_attached($p_bug_id);
	$t_value = '';

	$i = 0;
	foreach ($t_atm_rows as $t_atm) {
		$t_value .= ($i > 0 ? plugin_config_get('atm_separator') . ' ' : '');
		$t_value .= $t_atm['terminal_id'];
		$i++;
	}

	return $t_value;
}

/**
 * Get the number of bugs a given atm is attached to.
 * @param integer $p_atm_id The atm ID to retrieve statistics on.
 * @return int Number of attached bugs
 */
function atm_stats_attached($p_atm_id)
{
	db_param_push();
	$t_query = 'SELECT COUNT(*) FROM ' . plugin_table('bug_atm') . '  WHERE atm_id=' . db_param();
	$t_result = db_query($t_query, array($p_atm_id));

	return db_result($t_result);
}

/**
 * Get a list of related atms.
 * Returns a list of atms that are the most related to the given atm,
 * based on the number of times they have been attached to the same bugs.
 * Defaults to a list of five atms.
 * @param integer $p_atm_id The atm ID to retrieve statistics on.
 * @param integer $p_limit  List size.
 * @return array Array of atm rows, with share count added
 */
function atm_stats_related($p_atm_id, $p_limit = 5)
{

	# Use a filter to get all visible issues for this atm id
	$t_filter = array(
		FILTER_PROPERTY_HIDE_STATUS => array(META_FILTER_NONE),
		FILTER_PROPERTY_ATM_SELECT => $p_atm_id,
		FILTER_PROPERTY_PROJECT_ID => array(ALL_PROJECTS),
		'_view_type' => FILTER_VIEW_TYPE_ADVANCED,
	);
	$t_filter = filter_ensure_valid_filter($t_filter);

	$t_filter_subquery = new BugFilterQuery($t_filter, BugFilterQuery::QUERY_TYPE_IDS);

	$t_sql = 'SELECT atm_id, COUNT(1) AS atm_count FROM ' . plugin_table('bug_atm') . ' '
		. ' WHERE bug_id IN :filter AND atm_id <> :atmid'
		. ' GROUP BY atm_id ORDER BY atm_count DESC';
	$t_query = new DbQuery($t_sql);
	$t_query->bind('filter', $t_filter_subquery);
	$t_query->bind('atmid', (int)$p_atm_id);
	$t_query->execute();

	$t_atms = array();
	while ($t_row = $t_query->fetch()) {
		$t_atm_row = atm_get($t_row['atm_id']);
		$t_atm_row['count'] = (int)$t_row['atm_count'];
		$t_atms[] = $t_atm_row;
	}

	return $t_atms;
}
