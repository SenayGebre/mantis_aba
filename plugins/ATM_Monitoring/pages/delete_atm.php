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
 * Delete a atm
 *
 * @package MantisBT
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses config_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses atm_api.php
 */

require_once( 'core.php' );
require_once( './plugins/ATM_Monitoring/api_atm.php' );
require_api( 'access_api.php' );
require_api( 'config_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );

form_security_validate('delete_atm');

access_ensure_global_level( plugin_config_get( 'atm_edit_threshold' ) );

$f_atm_id = gpc_get_int( 'atm_id' );
atm_ensure_exists( $f_atm_id );
$t_atm_row = atm_get( $f_atm_id );

helper_ensure_confirmed( plugin_lang_get( 'atm_delete_message' ), plugin_lang_get( 'atm_delete_button' ) );

atm_delete( $f_atm_id );

form_security_purge( 'delete_atm' );

print_successful_redirect( plugin_page('manage_atm_page' ,true));