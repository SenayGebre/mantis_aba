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
 * Tag Update Page
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
 * @uses constant_inc.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses prepare_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses atm_api.php
 * @uses user_api.php
 */

require_once('core.php');
require_once('./plugins/ATM_Monitoring/api_atm.php');
require_once('./plugins/ATM_Monitoring/atm_helper.php');

require_api('access_api.php');
require_api('authentication_api.php');

require_api('compress_api.php');
require_api('config_api.php');
require_api('constant_inc.php');
require_api('form_api.php');
require_api('gpc_api.php');
require_api('helper_api.php');
require_api('html_api.php');
require_api('lang_api.php');
require_api('prepare_api.php');
require_api('print_api.php');
require_api('string_api.php');

require_api('user_api.php');

compress_enable();

$f_atm_id = gpc_get_int('atm_id');
echo $f_atm_id;
atm_ensure_exists($f_atm_id);
$t_atm_row = atm_get($f_atm_id);

$t_terminal_id = string_display_line($t_atm_row['terminal_id']);
$t_branch_name = string_display($t_atm_row['branch_name']);
$t_model = string_display($t_atm_row['model']);
$t_ip = string_display($t_atm_row['ip_address']);
$t_port = string_display($t_atm_row['port']);
$t_country = string_display($t_atm_row['country']);
$t_city = string_display($t_atm_row['city']);
$t_spec_loc = string_display($t_atm_row['specifc_location']);


if (!(access_has_global_level(plugin_config_get('atm_edit_threshold'))
	|| (auth_get_current_user_id() == $t_atm_row['user_id'])
	&& access_has_global_level(plugin_config_get('atm_edit_own_threshold')))) {
	access_denied();
}

layout_page_header(sprintf(plugin_lang_get('update_atm'), $t_terminal_id));

layout_page_begin();
?>

<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>
	<form method="post" action="<?php echo plugin_page('update_atm') ?>">
		<div class="widget-box widget-color-blue2">
			<div class="widget-header widget-header-small">
				<h4 class="widget-title lighter">
					<?php print_icon('fa-atm', 'ace-icon'); ?>
					<?php echo sprintf(plugin_lang_get('atm_update'), $t_terminal_id) ?>
				</h4>
			</div>
			<div class="widget-body">
				<div class="widget-main no-padding">
					<div class="widget-toolbox padding-8 clearfix">
						<?php print_link_button(
							plugin_page('view_atm_page?atm_id='.$f_atm_id),
							plugin_lang_get('atm_update_return'),
							'btn-sm pull-right'
						); ?>
					</div>
					<div class="form-container">
						<div class="table-responsive">
							<table class="table table-bordered table-condensed table-striped">
								<fieldset>
									<input type="hidden" name="atm_id" value="<?php echo $f_atm_id ?>" />
									<?php echo form_security_field('update_atm') ?>
									<tr>
										<td class="category">
											<?php echo plugin_lang_get('atm_id') ?>
										</td>
										<td><?php echo $t_atm_row['id'] ?></td>
									</tr>
									<tr>
										<td class="category">
											<?php echo plugin_lang_get('atm_terminal_id') ?>
										</td>
										<td>
											<input type="text" <?php echo helper_get_tab_index() ?> id="atm-name" name="terminal_id" class="input-sm" value="<?php echo $t_terminal_id ?>" />
										</td>
									</tr>
									<tr>
										<?php
										if (access_has_global_level(plugin_config_get('atm_edit_threshold'))) {
											echo '<td class="category">', plugin_lang_get('atm_creator'), '</td>';
											echo '<td><select ', helper_get_tab_index(), ' id="atm-user-id" name="user_id" class="input-sm">';
											print_user_option_list((int)$t_atm_row['user_id'], ALL_PROJECTS, (int)plugin_config_get('atm_create_threshold'));
											echo '</select></td>';
										} else { ?>
											<td class="category"><?php echo lang_get('atm_creator'); ?></td>
											<td><?php echo string_display_line(user_get_name($t_atm_row['user_id'])); ?></td><?php
																															} ?>
									</tr>
									<tr>
										<td class="category">
											<?php echo plugin_lang_get('atm_created') ?>
										</td>
										<td><?php echo  $t_atm_row['date_created']  ?></td>
									</tr>
									<tr>
										<td class="category">
											<?php echo plugin_lang_get('atm_updated') ?>
										</td>
										<td><?php echo  $t_atm_row['date_updated']  ?></td>
									</tr>
				
									<tr>
										<td class="category">
											<?php echo plugin_lang_get('atm_branch_name') ?>
										</td>
										<td>
										<input type="text" <?php echo helper_get_tab_index() ?> id="atm-name" name="branch_name" class="input-sm" value="<?php echo $t_branch_name ?>" />
										</td>
									</tr>
									<tr>
										<td class="category">
											<?php echo plugin_lang_get('atm_model') ?>
										</td>
										<td>
										<input type="text" <?php echo helper_get_tab_index() ?> id="atm-name" name="model" class="input-sm" value="<?php echo $t_model ?>" />

									</td>
									</tr>
									
									<tr>
										<td class="category">
											<?php echo plugin_lang_get('atm_ip') ?>
										</td>
										<td>
										<input type="text" <?php echo helper_get_tab_index() ?> id="atm-name" name="ip" class="input-sm" value="<?php echo $t_ip ?>" />
	
										</td>
									</tr>
									<tr>
										<td class="category">
											<?php echo plugin_lang_get('atm_port') ?>
										</td>
										<td>
										<input type="text" <?php echo helper_get_tab_index() ?> id="atm-name" name="port" class="input-sm" value="<?php echo $t_port ?>" />	
										</td>
									</tr>
									<tr>
										<td class="category">
											<?php echo plugin_lang_get('atm_country') ?>
										</td>
										<td>
										<input type="text" <?php echo helper_get_tab_index() ?> id="atm-name" name="country" class="input-sm" value="<?php echo $t_country ?>" />

										</td>
									</tr>
									<tr>
										<td class="category">
											<?php echo plugin_lang_get('atm_city') ?>
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
											<?php echo plugin_lang_get('atm_spec_loc') ?>
										</td>
										<td>
										<input type="text" <?php echo helper_get_tab_index() ?> id="atm-name" name="spec_loc" class="input-sm" value="<?php echo $t_spec_loc ?>" />
	
										</td>
									</tr>
								</fieldset>


							</table>
						</div>
					</div>
				</div>
			</div>
			<div class="widget-toolbox padding-8 clearfix">
				<input <?php echo helper_get_tab_index() ?> type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo plugin_lang_get('atm_update_button') ?>" />
			</div>
		</div>
	</form>
</div>

<?php
layout_page_end();
