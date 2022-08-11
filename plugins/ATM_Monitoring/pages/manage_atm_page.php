<?php

require_once( 'core.php' );
require_once( './plugins/ATM_Monitoring/api_atm.php' );
require_once( './plugins/ATM_Monitoring/atm_helper.php' );
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

access_ensure_project_level(plugin_config_get('atm_edit_threshold'));

compress_enable();

$t_can_edit = access_has_project_level(plugin_config_get('atm_edit_threshold'));
$f_filter_char = atm_get_param( 'page');
$f_filter = $f_filter_char !== '' ? mb_strtoupper( atm_get_param( 'page') ) : mb_strtoupper('ALL');
$f_page_number = gpc_get_int( 'page_number', 1 );

echo $f_filter;

# Start Index Menu
$t_prefix_array = array( 'ALL' );

for( $i = 'A'; $i != 'AA'; $i++ ) {
	$t_prefix_array[] = $i;
}

// for( $i = 0; $i <= 9; $i++ ) {
// 	$t_prefix_array[] = (string)$i;
// }

if( $f_filter === 'ALL' ) {
	$t_terminal_id_filter = '';
} else {
	$t_terminal_id_filter = $f_filter;
}



# Set the number of ATMs per page.
$t_per_page = 10;
$t_offset = (( $f_page_number - 1 ) * $t_per_page );


# Determine number of atms in atm table
$t_total_atm_count = atm_count( $t_terminal_id_filter );

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




// auth_reauthenticate();
$t_result = atm_get_all( $t_terminal_id_filter, $t_per_page, $t_offset ) ;

layout_page_header( plugin_lang_get( 'manage_atms_link' ) );

layout_page_begin('manage_overview_page.php');

layout_page_header(plugin_lang_get('manage_atm_page'));

print_manage_menu(plugin_page('manage_atm_page'));

// print_manage_menu('manage_atm_page.php');
$atm_project_found = false;

# We need a project to import into
$t_project_id = helper_get_current_project();
$current_project = project_cache_row($t_project_id);


// echo $current_project['name'];

if (ALL_PROJECTS !== $t_project_id and 'ATM Monitoring ' === $current_project['name']) {
	$atm_project_found = true;
} else {

	$atm_project = null;
	$all_projects = project_cache_all();
	// echo '<pre>'; print_r($all_projects); echo '</pre>';

	foreach ($all_projects as $array) {
		foreach ($array as $key => $value) {
			if (($key === 'name') and ($value === 'ATM Monitoring ')) {
				$atm_project = $array;
				break;
			}
		}
		if ($atm_project !== null) {
			break;
		}
	}
	// echo '<pre>'; print_r($atm_project); echo '</pre>';

	if ($atm_project !== null) {

		helper_set_current_project($atm_project['id']);
		# Reloading the page is required so that the project browser
		# reflects the new current project
		print_header_redirect($_SERVER['REQUEST_URI'], true, false, true);
		$atm_project_found = true;
	}
}
?>

<?php
// PHP program to pop an alert
// message box on the screen
  
// Display the alert box 
echo '<script>alert("Welcome to Geeks for Geeks")</script>';
  
?>

<?php if ($atm_project_found) { ?>

<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>

	<div class="center">
		<div class="btn-toolbar inline">
		<div class="btn-group">
	<?php


	foreach ( $t_prefix_array as $t_prefix ) {
		$t_caption = ( $t_prefix === 'ALL' ? plugin_lang_get( 'show_all_atms' ) : $t_prefix );
		$t_active = $t_prefix == $f_filter ? 'active' : '';
		echo '<a class="btn btn-xs btn-white btn-primary ' . $t_active .
		'" href="'. plugin_page( 'manage_atm_page.php?filter='.$t_prefix).'">' . $t_caption . '</a>' ."\n";
	} ?>
		</div>
	</div>
	</div>

<div class="space-10"></div>

	<div class="widget-box widget-color-blue2">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<?php print_icon('fa-atms', 'ace-icon'); ?>
				<?php echo plugin_lang_get('manage_atms_link') ?>
				<span class="badge"><?php echo $t_total_atm_count ?></span>
			</h4>
		</div>


		<div class="widget-body">
			<?php if ($t_can_edit) { ?>
				<div class="widget-toolbox padding-8 clearfix">
					<?php print_small_button('#atmcreate',  plugin_lang_get('atm_create')) ?>
				</div>
			<?php } ?>
			<div class="widget-main no-padding">
				<div class="table-responsive">
					<table class="table table-striped table-bordered table-condensed table-hover">
						<thead>
							<tr>
								<td><?php echo plugin_lang_get('atm_terminal_id') ?></td>
								<td><?php echo plugin_lang_get('atm_user') ?></td>
								<td><?php echo plugin_lang_get('atm_branch_name') ?></td>
								<td><?php echo plugin_lang_get('atm_model') ?></td>
								<td><?php echo plugin_lang_get('atm_ip') ?></td>
								<td><?php echo plugin_lang_get('atm_port') ?></td>
								<td><?php echo plugin_lang_get('atm_country') ?></td>
								<td><?php echo plugin_lang_get('atm_city') ?></td>
								<td><?php echo plugin_lang_get('atm_spec_loc') ?></td>
								<td><?php echo plugin_lang_get('atm_created') ?></td>
								<td><?php echo plugin_lang_get('atm_updated') ?></td>
							</tr>
						</thead>
						<tbody>
							<?php
							# Display all atms
							while ($t_atm_row = db_fetch_array($t_result)) {
								$t_atm_terminal_id = string_display_line($t_atm_row['terminal_id']);
								$t_atm_branch_name = string_display($t_atm_row['branch_name']);
								$t_atm_model = string_display($t_atm_row['model']);
								$t_atm_ip = string_display($t_atm_row['ip_address']);
								$t_atm_port = string_display($t_atm_row['port']);
								$t_atm_country = string_display($t_atm_row['country']);
								$t_atm_city= string_display($t_atm_row['city']);
								$t_atm_spec_loc = string_display($t_atm_row['specifc_location']);
								
							?>

								<tr>
									<?php if ($t_can_edit) { ?>
										<td><a href="<?php echo plugin_page('view_atm_page') ?>?atm_id=<?php echo $t_atm_row['id'] ?>"><?php echo $t_atm_terminal_id ?></a></td>
									<?php } else { ?>
										<td><?php echo $t_atm_terminal_id ?></td>
									<?php } ?>
									<td><?php echo string_display_line(user_get_name($t_atm_row['user_id'])) ?></td>
									<td><?php echo  $t_atm_branch_name ?></td>
									<td><?php echo  $t_atm_model ?></td>
									<td><?php echo  $t_atm_ip ?></td>
									<td><?php echo  $t_atm_port ?></td>
									<td><?php echo  $t_atm_country ?></td>
									<td><?php echo  $t_atm_city ?></td>
									<td><?php echo  $t_atm_spec_loc ?></td>
									
									<td><?php echo $t_atm_row['date_created'] ?></td>
									<td><?php echo $t_atm_row['date_updated'] ?></td>
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
			if ($t_total_atm_count > $t_per_page) {
			?>
				<div class="widget-toolbox padding-8 clearfix">
					<div class="btn-toolbar pull-right"><?php
														# @todo hack - pass in the hide inactive filter via cheating the actual filter value
														print_page_links(plugin_page('manage_atm_page'), 1, $t_page_count, (int)$f_page_number, $f_filter); ?>
					</div>
				</div>
			<?php } ?>

		</div>

	</div>

	<?php if ($t_can_edit) { ?>
		<div class="space-10"></div>
		<form id="manage-atms-create-form" method="post" action="<?php echo plugin_page('create_atm') ?>">
			<div class="widget-box widget-color-blue2">
				<div class="widget-header widget-header-small">
					<h4 class="widget-title lighter">
						<?php print_icon('fa-atm', 'ace-icon'); ?>
						<?php echo plugin_lang_get('atm_create') ?>
					</h4>
				</div>
				<div class="widget-body">
					<a name="atmcreate"></a>
					<div class="widget-main no-padding">
						<div class="form-container">
							<div class="table-responsive">
								<table class="table table-bordered table-condensed table-striped">
									<fieldset>
										<?php echo form_security_field('atm_create'); ?>
										<tr>
											<td class="category">
												<span class="required">*</span> <?php echo plugin_lang_get('atm_terminal_id') ?>
											</td>
											<td>
												<input type="text" id="atm-name" name="terminal_id" class="input-sm" size="40" maxlength="100" required />
											</td>
										</tr>
										<tr>
											<td class="category">
												<span class="required">*</span> <?php echo plugin_lang_get('atm_branch_name') ?>
											</td>
											<td>
												<input type="text" id="atm-name" name="branch_name" class="input-sm" size="40" maxlength="100" required />
											</td>
										</tr>
										<tr>
											<td class="category">
												<span class="required">*</span> <?php echo plugin_lang_get('atm_model') ?>
											</td>
											<td>
												<input type="text" id="atm-name" name="model" class="input-sm" size="40" maxlength="100" required />
											</td>
										</tr>
										<tr>
											<td class="category">
												<span class="required">*</span> <?php echo plugin_lang_get('atm_ip') ?>
											</td>
											<td>
												<input type="text" id="atm-name" name="ip" class="input-sm" size="40" maxlength="100" required />
											</td>
										</tr>
										<tr>
											<td class="category">
												<span class="required">*</span> <?php echo plugin_lang_get('atm_port') ?>
											</td>
											<td>
												<input type="text" id="atm-name" name="port" class="input-sm" size="40" maxlength="100" required />
											</td>
										</tr>
										<tr>
											<td class="category">
												<span class="required">*</span> <?php echo plugin_lang_get('atm_country') ?>
											</td>
											<td>
												<input type="text" id="atm-name" name="country" class="input-sm" size="40" maxlength="100"  value="Ethiopia" />
											</td>
										</tr>
										<tr>
											<td class="category">
												<span class="required">*</span> <?php echo plugin_lang_get('atm_city') ?>
											</td>
											<td>
											<select name="city" id="atm-name">
												<option value="Addis Ababa">Addis Ababa</option>
												<option value="Bahir Dar">Bahir Dar</option>
												<option value="Gondar">Gondar</option>
												<option value="Mekelle">Mekelle</option>
												<option value="Adama">	Adama</option>
												<option value="Awassa">Awassa</option>
												<option value="Dire Dawa">Dire Dawa</option>
												<option value="Dessie">Dessie</option>
												<option value="Jimma">Jimma</option>
												<option value="Bishoftu">Bishoftu</option>
												<option value="Arba Minch">Arba Minch</option>
												<option value="Harar">	Harar</option>
												<option value="Dilla">Dilla</option>
												<option value="Debre Birhan">Debre Birhan</option>
												<option value="Debre Mark'os">Debre Mark'os</option>
												<option value="Debre Tabor">Debre Tabor</option>
												<option value="Kombolcha">Kombolcha</option>
												<option value="Burayu">Burayu</option>
												<option value="Kobo">Kobo</option>
												<option value="Bonga">Bonga</option>
												<option value="Assosa">Assosa</option>
												<option value="Welkite">Welkite</option>
												
											</select>
												
											</td>
										</tr>
										<tr>
											<td class="category">
												<span class="required">*</span> <?php echo plugin_lang_get('atm_spec_loc') ?>
											</td>
											<td>
												<input type="text" id="atm-name" name="spec_loc" class="input-sm" size="40" maxlength="100" required />
											</td>
										</tr>
										<!-- <tr>
											<td class="category">
											
											</td>
											<td>
												<textarea class="form-control" id="atm-description" name="description" cols="80" rows="6"></textarea>
											</td>
										</tr> -->
									</fieldset>
								</table>
							</div>
						</div>
					</div>
					<div class="widget-toolbox padding-8 clearfix">
						<span class="required pull-right"> * <?php echo plugin_lang_get('required'); ?></span>
						<input type="submit" name="config_set" class="btn btn-primary btn-sm btn-white btn-round" value="<?php echo plugin_lang_get('atm_create') ?>" />
					</div>
				</div>
			</div>
		</form>
<?php
	} #End can Edit
} else {
	echo '<div class="alert alert-info">' . plugin_lang_get('no_atm_project_found') . '</div>';
} #End if atms exist
echo '</div>';
layout_page_end();

?>
