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
 * Tag Create
 *
 * @package MantisBT
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses print_api.php
 * @uses atm_api.php
 */

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'error_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'print_api.php' );
require_once( './plugins/ATM_Monitoring/api_atm.php' );

form_security_validate( 'atm_create' );

$f_atm_name = gpc_get_string( 'name' );
$f_atm_description = gpc_get_string( 'description' );

$t_atm_user = auth_get_current_user_id();

if( !is_null( $f_atm_name ) ) {
	$t_atms = atm_parse_string( $f_atm_name );
	foreach ( $t_atms as $t_atm_row ) {
		switch( $t_atm_row['id'] ) {
			case -1:
				atm_create( $t_atm_row['name'], $t_atm_user, $f_atm_description );
				break;
			case -2:
				error_parameters( $t_atm_row['name'] );
				trigger_error( ERROR_TERMINAL_ID_INVALID, ERROR );
		}
	}
}

form_security_purge( 'create_atm' );
print_successful_redirect( plugin_page( 'manage_atm_page',true));
?>