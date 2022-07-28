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
 * Tag Update
 *
 * @package MantisBT
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses compress_api.php
 * @uses config_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses print_api.php
 * @uses atm_api.php
 * @uses user_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'compress_api.php' );
require_api( 'config_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'print_api.php' );
require_api( 'atm_api.php' );
require_api( 'user_api.php' );

form_security_validate( 'atm_update' );

compress_enable();

$f_atm_id = gpc_get_int( 'atm_id' );
atm_ensure_exists( $f_atm_id );
$t_atm_row = atm_get( $f_atm_id );

$t_can_edit = access_has_global_level( config_get( 'atm_edit_threshold' ) );
if( $t_can_edit ) {
	$f_new_user_id = gpc_get_int( 'user_id', $t_atm_row['user_id'] );
} else {
	$t_can_edit_own_atm = access_has_global_level( config_get( 'atm_edit_own_threshold' ) );
	if( !( $t_can_edit_own_atm && auth_get_current_user_id() == $t_atm_row['user_id'] ) ) {
		access_denied();
	}
	# Never change the owner when user is editing their own atm
	$f_new_user_id = $t_atm_row['user_id'];
}

$f_new_name = gpc_get_string( 'name', $t_atm_row['name'] );
$f_new_description = gpc_get_string( 'description', $t_atm_row['description'] );

atm_update( $f_atm_id, $f_new_name, $f_new_user_id, $f_new_description );

form_security_purge( 'atm_update' );

$t_url = 'atm_view_page.php?atm_id='.$f_atm_id;
print_successful_redirect( $t_url );
