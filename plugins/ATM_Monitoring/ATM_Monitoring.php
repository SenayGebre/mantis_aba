<?php


// session_start();




class ATM_MonitoringPlugin extends MantisPlugin
{






    function register()
    {
        $this->name = 'ATM_Montoring';
        # Proper name of plugin
        $this->description = ' ';
        # Short description of the plugin
        $this->page = 'config';              # Default plugin page

        $this->version = '1.0.0';            # Plugin version string
        $this->requires = array(             # Plugin dependencies
            'MantisCore' => '2.0',           # Should always depend on an appropriate
            # version of MantisBT
        );

        $this->author = 'MantisBT Team';     # Author/team name
        $this->contact = 'mantisbt-dev@lists.sourceforge.net';
        # Author/team e-mail address
        $this->url = 'https://mantisbt.org'; # Support webpage
    }
    function events()
    {
        return array(
            'EVENT_CREATE_ATM' =>  'EVENT_TYPE_EXECUTE'
        );
    }
    function hooks()
    {
        return array(
            'EVENT_MENU_MANAGE' => 'manage_atm_menu',
            'EVENT_REPORT_BUG_FORM_TOP' => "select_atm",
            'EVENT_REPORT_BUG_DATA' => 'process_data',
            'EVENT_REPORT_BUG' => 'store_data',
            'EVENT_VIEW_BUG_DETAILS' => 'view_details',
            'EVENT_UPDATE_BUG_FORM' => 'update_atm',
            'EVENT_LAYOUT_RESOURCES' => 'atm_resources',
            'EVENT_LAYOUT_CONTENT_BEGIN' => 'alert_message',
        );
    }

    function atm_resources()
    {
        printf(
            "\t<script src=\"%s\"></script>\n",
            plugin_file('alem.js')
        );
    }

    function alert_message()
    {
        echo '<script type="text/javascript">
    alert_message();
</script>';
    }


    function select_atm()
    {
            require_once('atm_helper.php');
            atm_select();
    }
    function process_data($event, $t_issue)
    {

        require_once('api_atm.php');
        require_once('atm_helper.php');
        if (isProjectATMmonitoring()) {
            // echo '<h1>'.!empty(gpc_get("terminal_id")) ? true : false.'</h1>';
            // echo '<h1>'.!empty(gpc_get("terminal_id")) ? true : false.'</h1>';
            
            if(gpc_isset("terminal_id") && !is_blank(gpc_get("terminal_id"))) {
                $p_terminal_id = gpc_get("terminal_id");
                $p_atm = atm_get_by_terminal_id($p_terminal_id);
            } else if(!empty(gpc_get("branch_id")) && !is_blank(gpc_get("branch_id"))) {
                $p_atm = atm_get_atm_by_branch_id(gpc_get("branch_id"));
            }
                  
            
            $t_atms = [];
            
            if(isset($p_atm) && !empty($p_atm)) {
                array_push($t_atms, $p_atm);
    
                $t_issue->atm = $t_atms;
            }
            

        }

        return $t_issue;
    }

    function store_data($event, $t_issue)
    {
        require_once('mc_atm_api.php');

        if (isProjectATMmonitoring()) {

            $p_user_id = auth_get_current_user_id();

            mci_atm_set_for_issue($t_issue->id, $t_issue->atm, $p_user_id);
        }
    }
    function view_details($event, $t_issue_id) {
        require_once('api_atm.php');

        $t_terminal_id = get_terminal_id_by_issue_id($t_issue_id)['atm_id'];
        
        if (!empty($t_terminal_id)) {
            $t_atm = atm_get_by_terminal_id($t_terminal_id);
            if(isset($t_atm) && !empty($t_atm)) {
                if($t_atm['branch_id'] !== null && !is_blank($t_atm['branch_id'])) {
                    $d_branch = atm_get_branch_by_id($t_atm['branch_id']);
                }
            }
            
            // echo '<pre>'; print_r($t_row_2); echo '</pre>';

            if(isset($t_atm) && !empty($t_atm)) {
            echo '<th class="bug-summary category">Terminal ID</th>';
            // echo '<td class="bug-summary" colspan="5"    >', string_display_links($t_row['terminal_id']), '</td>';
            echo '<td class="bug-summary" colspan="5"><a href="' . plugin_page('view_atm_page') . '?atm_id=' . $t_atm['id'] . '">' . string_display_links($t_atm['terminal_id']) . '</a></td>';
            }

            if(isset($d_branch) && !empty($d_branch)) {
                echo '<tr>';
                echo '<th class="bug-summary category">Branch Name</th>';
                echo '<td class="bug-summary" colspan="5">', string_display_links($d_branch['name']), '</td>';
                echo '</tr>';
            }
        }
    }
    function manage_atm_menu()
    {
        return array('<a href="' . plugin_page('manage_atm_page') . '">' . plugin_lang_get('manage_atm_page') . '</a>');
    }
    
    function update_atm($event, $issue_id)
    {
        require_once('api_atm.php');
        require_once('atm_helper.php');

        if (isProjectATMmonitoring()) {

            $t_terminal_id = get_terminal_id_by_issue_id($issue_id)['atm_id'];
            $t_atm = atm_get_by_terminal_id($t_terminal_id);
            
            if(isset($t_atm) && !empty($t_atm)) {
                atm_select($t_atm['terminal_id']);
            }


        }
    }

    function schema()
    {
        require_api('install_helper_functions_api.php');

        require_api('database_api.php');

        return array(
            array('CreateTableSQL', array(
                plugin_table('atm'), "
                id						I		UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
	            user_id					I		UNSIGNED NOTNULL DEFAULT '0',
                terminal_id             C(40)   NOTNULL DEFAULT \" '' \",
                branch_id               C(100)  DEFAULT NULL,
                model                   C(64)   NOTNULL DEFAULT \" '' \",
                ip_address              C(100)  DEFAULT NULL,
                port                    I       DEFAULT NULL,
                country                 C(128)  NOTNULL DEFAULT \" '' \",
                city                    C(128)  NOTNULL DEFAULT \" '' \",
                specifc_location        C(128)  NOTNULL DEFAULT \" '' \",
                date_created			T		NOTNULL DEFAULT '" . db_null_date() . "',
	            date_updated			T		NOTNULL DEFAULT '" . db_null_date() . "' ",
                array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')
            )),

            array('CreateTableSQL', array(
                plugin_table('bug_atm'), "
                id						I		UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
                atm_id                  C(40)   NOTNULL DEFAULT \" '' \",
	            bug_id					I		UNSIGNED NOTNULL PRIMARY DEFAULT '0',
	            user_id					I		UNSIGNED NOTNULL DEFAULT '0',
	            date_attached			T		NOTNULL DEFAULT '" . db_null_date() . "'",
                array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')
            )),
        );
    }


    function config()
    {
        return array(
            "atm_manage_threshold" => ADMINISTRATOR,
            "atm_view_threshold" => VIEWER,
            'atm_edit_threshold' => DEVELOPER,
            'atm_detach_threshold' => DEVELOPER,
            'atm_attach_threshold' => REPORTER,
            'atm_create_threshold' => REPORTER,
            'atm_edit_own_threshold' => REPORTER,
            'atm_separator' => ',',
            'default_manage_tag_prefix' => 'ALL',
        );
    }
}
