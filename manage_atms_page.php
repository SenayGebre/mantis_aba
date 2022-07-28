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
 * Tags Page
 *
 * @package MantisBT
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses compress_api.php
 * @uses config_api.php
 * @uses database_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses user_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'compress_api.php' );
require_api( 'config_api.php' );
require_api( 'database_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );
require_api( 'user_api.php' );

access_ensure_global_level( config_get( 'atm_edit_threshold' ) );

compress_enable();

$t_can_edit = access_has_global_level( config_get( 'atm_edit_threshold' ) );
$f_filter = mb_strtoupper( gpc_get_string( 'filter', config_get( 'default_manage_atm_prefix' ) ) );
$f_page_number = gpc_get_int( 'page_number', 1 );

# Start Index Menu
$t_prefix_array = array( 'ALL' );

for( $i = 'A'; $i != 'AA'; $i++ ) {
	$t_prefix_array[] = $i;
}

for( $i = 0; $i <= 9; $i++ ) {
	$t_prefix_array[] = (string)$i;
}
if( $f_filter === 'ALL' ) {
	$t_name_filter = '';
} else {
	$t_name_filter = $f_filter;
}

# Set the number of Tags per page.
$t_per_page = 20;
$t_offset = (( $f_page_number - 1 ) * $t_per_page );

# Determine number of atms in tag table
$t_total_atm_count = atm_count( $t_name_filter );

#Number of pages from result
$t_page_count = ceil( $t_total_atm_count / $t_per_page );

if( $t_page_count < 1 ) {
	$t_page_count = 1;
}

# Make sure $p_page_number isn't past the last page.
if( $f_page_number > $t_page_count ) {
	$f_page_number = $t_page_count;
}

# Make sure $p_page_number isn't before the first page
if( $f_page_number < 1 ) {
	$f_page_number = 1;
}

# Retrieve Tags from table
$t_result = atm_get_all( $t_name_filter, $t_per_page, $t_offset ) ;

layout_page_header( lang_get( 'manage_atms_link' ) );

layout_page_begin( 'manage_overview_page.php' );

print_manage_menu( 'manage_atms_page.php' );
?>

<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>
	<div class="center">
		<div class="btn-toolbar inline">
		<div class="btn-group">
	<?php
	foreach ( $t_prefix_array as $t_prefix ) {
		$t_caption = ( $t_prefix === 'ALL' ? lang_get( 'show_all_atms' ) : $t_prefix );
		$t_active = $t_prefix == $f_filter ? 'active' : '';
		echo '<a class="btn btn-xs btn-white btn-primary ' . $t_active .
		'" href="manage_atms_page.php?filter=' . $t_prefix .'">' . $t_caption . '</a>' ."\n";
	} ?>
		</div>
	</div>
	</div>

<div class="space-10"></div>

<div class="widget-box widget-color-blue2">
	<div class="widget-header widget-header-small">
		<h4 class="widget-title lighter">
			<?php print_icon( 'fa-atms', 'ace-icon' ); ?>
			<?php echo lang_get('manage_atms_link') ?>
			<span class="badge"><?php echo $t_total_atm_count ?></span>
		</h4>
	</div>

	<div class="widget-body">
		<?php if ($t_can_edit) { ?>
			<div class="widget-toolbox padding-8 clearfix">
				<?php print_small_button( '#atmcreate', lang_get('atm_create') ) ?>
			</div>
		<?php } ?>
	<div class="widget-main no-padding">
	<div class="table-responsive">
	<table class="table table-striped table-bordered table-condensed table-hover">
		<thead>
			<tr>
				<td><?php echo lang_get( 'atm_name' ) ?></td>
				<td><?php echo lang_get( 'atm_creator' ) ?></td>
				<td><?php echo lang_get( 'atm_created' ) ?></td>
				<td><?php echo lang_get( 'atm_updated' ) ?></td>
			</tr>
		</thead>
		<tbody>
<?php
		# Display all atms
		while( $t_atm_row = db_fetch_array( $t_result ) ) {
			$t_atm_name = string_display_line( $t_atm_row['name'] );
			$t_atm_description = string_display( $t_atm_row['description'] );
?>
			<tr>
			<?php if( $t_can_edit ) { ?>
				<td><a href="atm_view_page.php?atm_id=<?php echo $t_atm_row['id'] ?>" ><?php echo $t_atm_name ?></a></td>
			<?php } else { ?>
				<td><?php echo $t_atm_name ?></td>
			<?php } ?>
				<td><?php echo string_display_line( user_get_name( $t_atm_row['user_id'] ) ) ?></td>
				<td><?php echo date( config_get( 'normal_date_format' ), $t_atm_row['date_created'] ) ?></td>
				<td><?php echo date( config_get( 'normal_date_format' ), $t_atm_row['date_updated'] ) ?></td>
			</tr>
<?php
		} # end while loop on atms
?>
		</tbody>
	</table>
	</div>
	</div>
<?php
	# Do not display the section's footer if we have only one page of users,
	# otherwise it will be empty as the navigation controls won't be shown.
	if( $t_total_atm_count > $t_per_page ) {
?>
	<div class="widget-toolbox padding-8 clearfix">
		<div class="btn-toolbar pull-right"><?php
			# @todo hack - pass in the hide inactive filter via cheating the actual filter value
			print_page_links( 'manage_atms_page.php', 1, $t_page_count, (int)$f_page_number, $f_filter ); ?>
		</div>
	</div>
<?php } ?>
</div>
</div>

<?php if( $t_can_edit ) { ?>
<div class="space-10"></div>
	<form id="manage-atms-create-form" method="post" action="atm_create.php">
	<div class="widget-box widget-color-blue2">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<?php print_icon( 'fa-atm', 'ace-icon' ); ?>
				<?php echo lang_get('atm_create') ?>
			</h4>
		</div>
		<div class="widget-body">
			<a name="atmcreate"></a>
			<div class="widget-main no-padding">
		<div class="form-container">
		<div class="table-responsive">
		<table class="table table-bordered table-condensed table-striped">
		<fieldset>
			<?php echo form_security_field( 'atm_create' ); ?>
			<tr>
				<td class="category">
					<span class="required">*</span> <?php echo lang_get( 'atm_name' ) ?>
				</td>
				<td>
					<input type="text" id="atm-name" name="name" class="input-sm" size="40" maxlength="100" required />
					<small><?php echo sprintf( lang_get( 'atm_separate_by' ), config_get( 'atm_separator' ) ); ?></small>
				</td>
			</tr>
			<tr>
				<td class="category">
					<?php echo lang_get( 'atm_description' ) ?>
				</td>
				<td>
					<textarea class="form-control" id="atm-description" name="description" cols="80" rows="6"></textarea>
				</td>
			</tr>
		</fieldset>
		</table>
		</div>
		</div>
		</div>
			<div class="widget-toolbox padding-8 clearfix">
				<span class="required pull-right"> * <?php echo lang_get( 'required' ); ?></span>
				<input type="submit" name="config_set" class="btn btn-primary btn-sm btn-white btn-round"
					   value="<?php echo lang_get('atm_create') ?>"/>
			</div>
		</div>
	</div>
    </form>
<?php
} #End can Edit
echo '</div>';
layout_page_end();
