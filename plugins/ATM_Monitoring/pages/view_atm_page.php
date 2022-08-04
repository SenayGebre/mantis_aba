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
 * Tag View Page
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
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
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
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );
require_api( 'atm_api.php' );
require_api( 'user_api.php' );

access_ensure_global_level( config_get( 'atm_view_threshold' ) );
compress_enable();

// $f_atm_id = gpc_get_int( 'atm_id' );
echo '<pre>'; print_r($_SESSION); echo '</pre>';
// echo $f_atm_id;
// atm_ensure_exists( $f_atm_id );
// $t_atm_row = atm_get( $f_atm_id );

// $t_name = string_display_line( $t_atm_row['name'] );
// $t_description = string_display( $t_atm_row['description'] );
// $t_can_edit = access_has_global_level( config_get( 'atm_edit_threshold' ) );
// $t_can_edit_own = $t_can_edit || auth_get_current_user_id() == atm_get_field( $f_atm_id, 'user_id' )
// 	&& access_has_global_level( config_get( 'atm_edit_own_threshold' ) );


// layout_page_header( sprintf( lang_get( 'atm_details' ), $t_name ) );

// layout_page_begin();
?>
