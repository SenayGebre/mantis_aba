<?php



function atm_get_param($p_var_name, $p_default = null)
{

    if (isset($_POST[$p_var_name])) {
        $t_result = $_POST[$p_var_name];
    } else if (isset($_GET[$p_var_name])) {
        $t_result = $_GET[$p_var_name];
    } else if (func_num_args() > 1) {
        # check for a default passed in (allowing null)
        $t_result = $p_default;
    } else {
        error_parameters($p_var_name);
        trigger_error(ERROR_GPC_VAR_NOT_FOUND, ERROR);
        $t_result = null;
    }

    return str_contains($t_result, '=') ? explode('=', $t_result)[1] : '';
}

function atm_select($terminal_id = null, $branch_id = null)
{
    
    
    require_once('api_atm.php');
    
    if (isProjectATMmonitoring()) {
        
        $terminal_rows = atmGetTerminals();
        $branch_rows = atm_get_atm_branches();
    



        echo "\t", '<link rel="stylesheet" type="text/css" href="', string_sanitize_url(plugin_file('bootstrap-select.min.s.css'), true), '" />', "\n";
        echo "\t", '<script type="text/javascript" src="', plugin_file('bootstrap-select.min.s.js'), '"></script>', "\n";
        echo "\t", '<link rel="stylesheet" type="text/css" href="', string_sanitize_url(plugin_file('atm_monitoring_custom_css.css'), true), '" />', "\n";


        echo '<tr>';
        echo '<th class="category">';
        echo '<span class="required">*</span><label for="terminal_id">Terminal ID</label>';
        echo '<td>';
        echo '<select class="senselectpicker" data-live-search="true" name="terminal_id" id="terminal_id">';
        if ($terminal_id !== null) {
            echo '<option selected value="' . $terminal_id . '">' . $terminal_id . '</option>';
            echo '<option disabled value="">Select Terminal ID</option>';
        } else {
            echo '<option disabled selected value="">Select Terminal ID</option>';
        }

        foreach ($terminal_rows as $terminal) {
            echo '<option value="' . $terminal['terminal_id'] . '">' . $terminal['terminal_id'] . '</option>';
        }
        echo '</select>';
        echo '<div class="input-sm" ><span> - OR - </span></div>';
        echo '<select class="senselectpicker" data-live-search="true" name="branch_id" id="branch_id">';
        // if ($branch_id !== null) {
        //     $branch_name_by_id = atm_get_branch_by_id($branch_id);
        //     echo '<option disabled value="">Select Branch Name</option>';
        //     echo '<option selected value="' . $branch_id . '">' . $branch_name_by_id["name"] . '</option>';
        // } else {
        //     echo '<option disabled selected value="">Select Branch Name</option>';
        // }
        echo '<option disabled selected value="">Select Branch Name</option>';

       
        // foreach ($branch_rows as $branch) {
        //     if ($branch["id"] === $branch_id){
        //         continue;
        //     }
        //     echo '<option value="' . $branch['id'] . '">' . $branch['name'] . '</option>';
        // }
        echo '</select>';
        echo '</td>';
        echo '</tr>';
    }
}

function isProjectATMmonitoring()
{
    $t_project_id = helper_get_current_project();
    $current_project = project_cache_row($t_project_id);

    if ('ATM Monitoring' === $current_project['name']) {
        return true;
    } else {
        return false;
    }
}
