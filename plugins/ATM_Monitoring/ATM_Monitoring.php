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
            'EVENT_REPORT_BUG_FORM_TOP' => 'select_atm',
            'EVENT_REPORT_BUG_DATA' => 'process_data',
            'EVENT_REPORT_BUG' => 'store_data',
            'EVENT_VIEW_BUG_DETAILS' => 'view_details',


            'EVENT_CREATE_ATM' => 'create_atm'
        );
    }


    function select_atm()
    {

        $t_project_id = helper_get_current_project();
        $current_project = project_cache_row($t_project_id);

        if ('ATM Monitoring ' === $current_project['name']) {
            $_SESSION['terminal_id'] = "Terminal ID";

            echo '<tr>';
            echo '<th class="category">';
            echo '<span class="required">*</span><label for="terminal_id">' . $_SESSION['terminal_id'] . '</label>';
            echo '</th>';
            echo '<td>';
            echo '<input ' . helper_get_tab_index() . 'type="text" id="terminal_id"  name="terminal_id" size="105" maxlength="128" value="' . isset($_POST['"terminal_id"']) . '" required />';
            echo '</td>';
            echo '</tr>';
        }
    }
    function process_data($event, $t_t_issue)
    {


        $t_t_issue->atm = gpc_get('terminal_id');

        // echo '<pre>';
        // print_r($issue);
        // echo '</pre>';

        return $t_t_issue;
    }
    function store_data($event, $t_issue)
    {

        $d_query_1 = 'ALTER TABLE mantis_bug_table ADD COLUMN IF NOT EXISTS terminal_id VARCHAR(255)';
        $d_result = db_query($d_query_1);
        $d_query = 'UPDATE mantis_bug_table SET terminal_id ="' . $t_issue->atm . '" WHERE id =' . $t_issue->id . '';
        $d_result = db_query($d_query);
    }
    function view_details($event, $issue)
    {

        $d_query = 'SELECT terminal_id FROM mantis_bug_table WHERE id =' . $issue . '';
        $d_result = db_query($d_query);
        $t_row = db_fetch_array($d_result);
        // echo $t_row;
        echo '<th class="bug-summary category">Terminal ID</th>';
        echo '<td class="bug-summary" colspan="5">', string_display_links($t_row['terminal_id']), '</td>';
        echo '</tr>';
    }
    function manage_atm_menu()
    {
        return array('<a href="' . plugin_page('manage_atm_page') . '">' . plugin_lang_get('manage_atm_page') . '</a>');
    }
    
    function schema()
    {
        require_api( 'install_helper_functions_api.php' );

        require_api( 'database_api.php' );

        return array(
            array('CreateTableSQL', array(
                plugin_table('atm'), "
                id						I		UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
                terminal_id             C(40)   NOTNULL DEFAULT \" '' \",
                merchant_id             C(40)   NOTNULL DEFAULT \" '' \",
                branch_name             C(100)  DEFAULT NULL,
                model                   C(64)   NOTNULL DEFAULT \" '' \",
                ip_address              C(100)  DEFAULT NULL,
                port                    I       DEFAULT NULL,
                country                 C(128)  NOTNULL DEFAULT \" '' \",
                city                    C(128)  NOTNULL DEFAULT \" '' \",
                precinct                C(128)  NOTNULL DEFAULT \" '' \"",
                array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')
            )),
           
            array('CreateTableSQL', array(
                plugin_table('bug_atm'), "
                id						I		UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
                terminal_id             C(40)   NOTNULL DEFAULT \" '' \",
	            bug_id					I		UNSIGNED NOTNULL PRIMARY DEFAULT '0',
	            date_attached			T		NOTNULL DEFAULT '" . db_null_date() . "'",
                array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')
            )),
        );
    }


    function create_atm()
    {
        $d_query = 'INSERT INTO  mantis_bug_table VALUES terminal_id ="';
    }
    function config()
    {
        return array(
            "atm_manage_threshold" => ADMINISTRATOR,
            "atm_view_threshold" => VIEWER,
            'atm_edit_threshold' => DEVELOPER,
            'atm_edit_own_threshold' => REPORTER,
            'default_manage_tag_prefix' => 'ALL',

        );
    }
}
