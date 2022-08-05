<?php


access_ensure_project_level(plugin_config_get('atm_view_threshold'));

auth_reauthenticate();

layout_page_header(plugin_lang_get('manage_atm_page'));

layout_page_begin('manage_overview_page.php');

$t_this_page = plugin_page('manage_atm_page'); # FIXME with plugins this does not work...
print_manage_menu($t_this_page);
$atm_project_found = false;
$t_total_atm_count = 0;
$f_page_number = gpc_get_int('page_number', 1);
# Set the number of Tags per page.
$t_per_page = 20;
$t_offset = (($f_page_number - 1) * $t_per_page);
$t_can_edit = access_has_project_level(plugin_config_get('atm_manage_threshold'));
# Retrieve ATM from table
$t_result = atm_getall();

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
function atm_getall()
{

	$t_query = 'SELECT * FROM {atm}  ORDER BY name';

	return db_query($t_query);
}
?>
<?php if ($atm_project_found) { ?>

	<div class="col-md-12 col-xs-12">
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
									<td><?php echo plugin_lang_get('atm_name') ?></td>
									<td><?php echo plugin_lang_get('atm_creator') ?></td>
									<td><?php echo plugin_lang_get('atm_created') ?></td>
									<td><?php echo plugin_lang_get('atm_updated') ?></td>
								</tr>
							</thead>
							<tbody>
								<?php
								# Display all atms
								while ($t_atm_row = db_fetch_array($t_result)) {
									$t_atm_name = string_display_line($t_atm_row['name']);
									$t_atm_description = string_display($t_atm_row['description']);
								?>

									<tr>
										<?php if ($t_can_edit) { ?>
											<td><a href="<?php echo plugin_page('view_atm_page') ?>?atm_id=<?php echo $t_atm_row['id'] ?>"><?php echo $t_atm_name ?></a></td>
										<?php } else { ?>
											<td><?php echo $t_atm_name ?></td>
										<?php } ?>
										<td><?php echo string_display_line(user_get_name($t_atm_row['user_id'])) ?></td>
										<td><?php echo date(config_get('normal_date_format'), $t_atm_row['date_created']) ?></td>
										<td><?php echo date(config_get('normal_date_format'), $t_atm_row['date_updated']) ?></td>
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
															print_page_links('manage_atm_page.php', 1, $t_page_count, (int)$f_page_number, $f_filter); ?>
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
													<span class="required">*</span> <?php echo plugin_lang_get('atm_name') ?>
												</td>
												<td>
													<input type="text" id="atm-name" name="name" class="input-sm" size="40" maxlength="100" required />
													<small><?php echo sprintf(plugin_lang_get('atm_separate_by'), config_get('atm_separator')); ?></small>
												</td>
											</tr>
											
											<tr>
												<td class="category">
													<?php echo plugin_lang_get('atm_description') ?>
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
							<span class="required pull-right"> * <?php echo plugin_lang_get('required'); ?></span>
							<input type="submit" name="config_set" class="btn btn-primary btn-sm btn-white btn-round" value="<?php echo plugin_lang_get('atm_create') ?>" />
						</div>
					</div>
				</div>
			</form>
	<?php
		} #End can Edit
	}
	echo '</div>';
	layout_page_end();

	?>