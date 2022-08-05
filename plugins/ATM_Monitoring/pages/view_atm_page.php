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
require_once( './plugins/ATM_Monitoring/atm_helper.php' );
require_once( './plugins/ATM_Monitoring/api_atm.php' );
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
// require_api( 'atm_api.php' );
require_api( 'user_api.php' );

access_ensure_global_level( plugin_config_get( 'atm_view_threshold' ) );
auth_reauthenticate();		
compress_enable();

$f_atm_id = atm_get_param( 'page' );

echo $f_atm_id;
atm_ensure_exists( $f_atm_id );
$t_atm_row = atm_get( $f_atm_id );
$t_name = string_display_line( $t_atm_row['name'] );
$t_description = string_display( $t_atm_row['description'] );
$t_can_edit = access_has_global_level( config_get( 'atm_edit_threshold' ) );
$t_can_edit_own = $t_can_edit || auth_get_current_user_id() == atm_get_field( $f_atm_id, 'user_id' )
	&& access_has_global_level( config_get( 'atm_edit_own_threshold' ) );


layout_page_header( sprintf( lang_get( 'atm_details' ), $t_name ) );

layout_page_begin();
?>

<div class="col-md-12 col-xs-12">
<div class="space-10"></div>
<div class="widget-box widget-color-blue2">
<div class="widget-header widget-header-small">
<h4 class="widget-title lighter">
	<?php print_icon( 'fa-atm', 'ace-icon' ); ?>
	<?php echo sprintf( lang_get( 'atm_details' ), $t_name ) ?>
</h4>
</div>

<div class="widget-body">  
<div class="widget-main no-padding">
	<div class="widget-toolbox padding-8 clearfix">
		<?php print_link_button( 'search.php?atm_string='.urlencode($t_atm_row['name']),
			sprintf( lang_get( 'atm_filter_default' ), atm_stats_attached( $f_atm_id ) ),
			'btn-sm pull-right'); ?>
	</div>
	<div class="table-responsive">
		<table class="table table-bordered table-condensed table-striped">

	<tr>
		<td class="category">
			<?php echo lang_get( 'atm_id' ) ?>
		</td>
		<td><?php echo $t_atm_row['id'] ?></td>
	</tr>
	<tr>
		<td class="category">
			<?php echo lang_get( 'atm_name' ) ?>
		</td>
		<td><?php echo $t_name ?></td>
	</tr>
	<tr>
		<td class="category">
			<?php echo lang_get( 'atm_creator' ) ?>
		</td>
		<td><?php echo string_display_line( user_get_name($t_atm_row['user_id']) ) ?></td>
	</tr>
	<tr>
		<td class="category">
			<?php echo lang_get( 'atm_created' ) ?>
		</td>
		<td><?php echo date( config_get( 'normal_date_format' ), $t_atm_row['date_created'] ) ?></td>
	</tr>
	<tr>
		<td class="category">
			<?php echo lang_get( 'atm_updated' ) ?>
		</td>
		<td><?php echo date( config_get( 'normal_date_format' ), $t_atm_row['date_updated'] ) ?></td>
	</tr>
	<tr>
		<td class="category">
			<?php echo lang_get( 'atm_description' ) ?>
		</td>
		<td><?php echo $t_description ?></td>
	</tr>

<?php
	# Related atms

	$t_atms_related = atm_stats_related( $f_atm_id );
	if( count( $t_atms_related ) ) {
?>
	<tr>
		<td class="category">
			<?php echo lang_get( 'atm_related' ) ?>
		</td>
			<td>
<?php
		foreach( $t_atms_related as $t_atm ) {
			$t_name = string_display_line( $t_atm['name'] );
			$t_description = string_display_line( $t_atm['description'] );
			$t_count = $t_atm['count'];
			$t_link = string_html_specialchars( 'search.php?atm_string='.urlencode( '+' . $t_atm_row['name'] . config_get( 'atm_separator' ) . '+' . $t_name ) );
			$t_label = sprintf( lang_get( 'atm_related_issues' ), $t_atm['count'] ); ?>
			<div class="col-md-3 col-xs-6 no-padding"><a href="view_atm_page.php?atm_id=<?php echo $t_atm['id']; ?>" title="<?php echo $t_description; ?>"><?php echo $t_name; ?></a></div>
			<div class="col-md-9 col-xs-6 no-padding"><a href="<?php echo $t_link; ?>" class="btn btn-xs btn-primary btn-white btn-round"><?php echo $t_label; ?></a></div>
			<div class="clearfix"></div>
			<div class="space-4"></div>
<?php
		}
?>
			</td>
	</tr>
<?php
	} ?>
</table >
</div >

<?php
	if( $t_can_edit_own || $t_can_edit ) {
?>
	<div class="widget-toolbox padding-8 clearfix">
<?php
		if( $t_can_edit_own ) {
?>
		<form class="form-inline pull-left" action="<?php echo plugin_page('update_atm_page') ?>?atm_id=<?php echo $f_atm_id ?>" method="post">
			<fieldset>
				<?php # CSRF protection not required here - form does not result in modifications ?>
				<input type="hidden" name="atm_id" value="<?php echo $f_atm_id ?>" />
				<input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'atm_update_button' ) ?>" />
			</fieldset>
		</form><?php
		}

		if( $t_can_edit ) { ?>
		<form class="form-inline pull-left" action="<?php echo plugin_page('delete_atm') ?>?atm_id=<?php echo $f_atm_id ?>" method="post">
			<fieldset>
				<?php echo form_security_field( 'delete_atm' ) ?>
				<input type="hidden" name="atm_id" value="<?php echo $f_atm_id ?>" />
				<input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'atm_delete_button' ) ?>" />
			</fieldset>
		</form><?php
		} ?>
	</div><?php
	} ?>
	</div>
	</div>
	</div>
</div>

<?php
layout_page_end();
