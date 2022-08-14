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
require_once(''.dirname(__DIR__).'/api_atm.php');


require_api( 'user_api.php' );


form_security_validate( 'update_atm' );

compress_enable();

$f_atm_id = gpc_get_int( 'atm_id' );
atm_ensure_exists( $f_atm_id );
$t_atm_row = atm_get( $f_atm_id );

$t_can_edit = access_has_global_level( config_get( 'atm_edit_threshold' ) );
echo "atm_mon_";
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


$f_new_terminal_id = gpc_get_string( 'terminal_id',  $t_atm_row['terminal_id']  );
$f_new_branch_name = gpc_get_string( 'branch_name',  $t_atm_row['branch_name']  );
$f_new_model = gpc_get_string( 'model',  $t_atm_row['model']  );
$f_new_ip = gpc_get_string( 'ip',  $t_atm_row['ip_address']  );
$f_new_port = gpc_get_string( 'port',  $t_atm_row['port']  );
$f_new_country = gpc_get_string( 'country',  $t_atm_row['country']  );
$f_new_city = gpc_get_string( 'city',  $t_atm_row['city']  );
$f_new_spec_loc = gpc_get_string( 'spec_loc',  $t_atm_row['specifc_location']  );

atm_update( $f_atm_id, $f_new_terminal_id, $f_new_user_id, $f_new_branch_name, $f_new_model,$f_new_ip,$f_new_port, $f_new_country,$f_new_city,$f_new_spec_loc );

form_security_purge( 'update_atm' );

$t_url = 'view_atm_page.php?atm_id='.$f_atm_id;

print_successful_redirect( plugin_page( $t_url,true));
?>
