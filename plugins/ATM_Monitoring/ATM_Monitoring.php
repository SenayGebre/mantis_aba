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
			'EVENT_LAYOUT_RESOURCES' => 'atm_resources',
			'EVENT_LAYOUT_CONTENT_BEGIN' => 'alert_message',
            

        );
    }

    function atm_resources() {
        printf( "\t<script src=\"%s\"></script>\n",
				plugin_file( 'alem.js' )
			);
    }

    function alert_message() {
        echo '<script type="text/javascript">
    alert_message();
</script>';
    }


    function select_atm()
    {
        $t_project_id = helper_get_current_project();
        $current_project = project_cache_row($t_project_id);
        $t_query = 'SELECT * FROM ' . plugin_table('atm');
        $t_result = db_query($t_query);
        $t_query = 'SELECT * FROM ' . plugin_table('atm');
        $t_result = db_query($t_query);
        $t_query = 'SELECT * FROM ' . plugin_table('atm');
        $t_result = db_query($t_query);

        if ('ATM Monitoring' === $current_project['name']) {
            
           
            $_SESSION['terminal_id'] = "Terminal ID";
            echo "\t", '<link rel="stylesheet" type="text/css" href="', string_sanitize_url(plugin_file('bootstrap-select.min.s.css'), true), '" />', "\n";
            echo "\t", '<script type="text/javascript" src="', plugin_file('bootstrap-select.min.s.js'), '"></script>', "\n";
            echo "\t", '<link rel="stylesheet" type="text/css" href="', string_sanitize_url(plugin_file('atm_monitoring_custom_css.css'), true), '" />', "\n";



            echo '<tr>';
            echo '<th class="category">';
            echo '<span class="required">*</span><label for="terminal_id">' . $_SESSION['terminal_id'] . '</label>';
            echo '<td>';
            echo '<form id="form" action="select_atm.php" method="post">';
            echo '<select class="senselectpicker senform-control" data-live-search="true" name="terminal_id" id="terminal_id">';
            echo '<option disabled selected value="">Select Terminal ID</option>';
            while ($t_atm_row = db_fetch_array($t_result)) {
                echo '<option value="'.$t_atm_row['terminal_id'].'">' . $t_atm_row['terminal_id'] . '</option>';
            }
            echo '</select>';
            echo '</form>';
            echo '</td>';
            echo '</tr>';
        }
    }
    function process_data($event, $t_issue)
    {
        require_once('api_atm.php');
        
        

        $p_terminal_id = gpc_get("terminal_id");

      

        $t_atms = [];
        $r_atm = atm_get_by_terminal_id($p_terminal_id);

        array_push($t_atms, $r_atm);

        $t_issue->atm = $t_atms;


        // echo '<pre>';
        // print_r($t_issue);
        // echo '</pre>';

        return $t_issue;
    }
    function store_data($event, $t_issue)
    {
        require_once('mc_atm_api.php');


        $p_user_id = auth_get_current_user_id();

        mci_atm_set_for_issue($t_issue->id, $t_issue->atm, $p_user_id);
        mci_atm_set_for_issue($t_issue->id, $t_issue->atm, $p_user_id);
        mci_atm_set_for_issue($t_issue->id, $t_issue->atm, $p_user_id);
    }
    function view_details($event, $t_issue)
    {
        $d_query = 'SELECT atm_id FROM ' . plugin_table('bug_atm') . ' WHERE bug_id =' . $t_issue;
        $d_result_atm_id = db_query($d_query);
        $t_row = db_fetch_array($d_result_atm_id);
        if (!empty($t_row)) {

            $d_query_2 = 'SELECT * FROM ' . plugin_table('atm') . ' WHERE id =' . $t_row['atm_id'];
            $d_result_terminal_id = db_query($d_query_2);
            $t_row_2 = db_fetch_array($d_result_terminal_id);



            echo '<th class="bug-summary category">Terminal ID</th>';
            // echo '<td class="bug-summary" colspan="5">', string_display_links($t_row['terminal_id']), '</td>';
            echo '<td class="bug-summary" colspan="5"><a href="' . plugin_page('view_atm_page') . '?atm_id=' . $t_row_2['id'] . '">' . string_display_links($t_row_2['terminal_id']) . '</a></td>';

            echo '</tr>';
            echo '<th class="bug-summary category">Branch Name</th>';
            echo '<td class="bug-summary" colspan="5">', string_display_links($t_row_2['branch_name']), '</td>';
            echo '</tr>';
        }
    }
    function manage_atm_menu()
    {
        return array('<a href="' . plugin_page('manage_atm_page') . '">' . plugin_lang_get('manage_atm_page') . '</a>');
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
                branch_name             C(100)  DEFAULT NULL,
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
