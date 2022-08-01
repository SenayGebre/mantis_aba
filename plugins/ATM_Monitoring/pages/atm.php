<?php


access_ensure_project_level( plugin_config_get( 'atm_manage_threshold' ) );

auth_reauthenticate( );

layout_page_header( plugin_lang_get( 'atm' ) );

layout_page_begin( 'manage_overview_page.php' );

$t_this_page = plugin_page( 'atm' ); # FIXME with plugins this does not work...
print_manage_menu( $t_this_page );
$atm_project_found = false;
$t_total_atm_count = 0;
$t_can_edit = access_has_project_level( plugin_config_get( 'atm_manage_threshold' ) );

# We need a project to import into
$t_project_id = helper_get_current_project( );
$current_project = project_cache_row($t_project_id);


// echo $current_project['name'];

if(ALL_PROJECTS !== $t_project_id and 'ATM Monitoring ' === $current_project['name']) {
    $atm_project_found = true;
} else {
    
    $atm_project=null;
    $all_projects = project_cache_all();
    // echo '<pre>'; print_r($all_projects); echo '</pre>';
    
    foreach ($all_projects as $array) {
        foreach ($array as $key => $value) {
            if( ($key === 'name') and ($value === 'ATM Monitoring ')) {
                $atm_project = $array;
                break;
            }
        }
        if($atm_project !== null) {
            break;
        }
    }
    // echo '<pre>'; print_r($atm_project); echo '</pre>';
    
    if($atm_project !== null) {
        
        helper_set_current_project($atm_project['id']);
        # Reloading the page is required so that the project browser
		# reflects the new current project
        print_header_redirect( $_SERVER['REQUEST_URI'], true, false, true );
        $atm_project_found = true;

  }   
}

?>
<?php if($atm_project_found){ ?>

    <div class="col-md-12 col-xs-12">
	<div class="space-10"></div>
	
		<div class="btn-toolbar inline">
	<div class="widget-box widget-color-blue2">
	<div class="widget-header widget-header-small">
		<h4 class="widget-title lighter">
			<?php print_icon( 'fa-atms', 'ace-icon' ); ?>
			<?php echo plugin_lang_get('manage_atms_link') ?>
			<span class="badge"><?php echo $t_total_atm_count ?></span>
		</h4>
	</div>
    <div class="widget-body">
		<?php if ($t_can_edit) { ?>
			<div class="widget-toolbox padding-8 clearfix">
				<?php print_small_button( '#atmcreate',  plugin_lang_get('atm_create') ) ?>
			</div>
		<?php } ?>
    	<div class="widget-main no-padding">
	<div class="table-responsive">
	<table class="table table-striped table-bordered table-condensed table-hover">
		<thead>
			<tr>
				<td><?php echo plugin_lang_get( 'atm_name' ) ?></td>
				<td><?php echo plugin_lang_get( 'atm_creator' ) ?></td>
				<td><?php echo plugin_lang_get( 'atm_created' ) ?></td>
				<td><?php echo plugin_lang_get( 'atm_updated' ) ?></td>
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

    </div>


        </div>
        </div>
    </div>







<?php
} 
?>