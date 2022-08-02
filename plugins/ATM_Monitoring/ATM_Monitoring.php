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
    // function events()
    // {

    // }
    function hooks()
    {
        return array(
            'EVENT_MENU_MANAGE' => 'manage_atm_menu',
            'EVENT_REPORT_BUG_FORM_TOP' => 'select_atm',
            'EVENT_REPORT_BUG_DATA' => 'process_data',
            'EVENT_REPORT_BUG' => 'store_data',
        );
    }


    function select_atm()
    {
        $_SESSION['terminal_id'] = "this is session id";

        echo '<tr>';
        echo '<th class="category">';
        echo '<span class="required">*</span><label for="terminal_id">' .$_SESSION['terminal_id']. '</label>';
        echo '</th>';
        echo '<td>';
        echo '<input type="text" id="terminal_id" class="btn" name="terminal_id" size="105" maxlength="128" value="'.isset($_POST['"terminal_id"']) .'" required />';
        echo '</td>';
        echo '</tr>';

    }
    function process_data($event, $issue)
    {
        $issue->atm = gpc_get('terminal_id');
    
        // echo '<pre>';
        // print_r($issue);
        // echo '</pre>';
        
        return $issue;
    }
    function store_data($event, $issue)
    {
         $d_query_1 = 'IF EXISTS(SELECT 1 FROM sys.columns WHERE Name = N'atm' AND Object_ID = Object_ID(N'schemaName.tableName'))';
        $d_query = 'ALTER TABLE mantis_atm_table ADD atm varchar(255)';

        // $d_query = 'INSERT INTO mantis_atm_table(name, description) VALUES ("' .$issue->atm.'", "hello")';
        $d_result = db_query($d_query);
    }
    function manage_atm_menu()
    {
        return array('<a href="' . plugin_page('atm') . '">' . plugin_lang_get('atm') . '</a>');
    }
    //     function schema()
    //     {

    //     }
    function config()
    {
        return array(
            // "atm_edit_threshold" => ADMINISTRATOR,
            "atm_manage_threshold" => ADMINISTRATOR,
        );
    }
}
