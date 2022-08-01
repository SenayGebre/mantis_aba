<?php
class ATM_MonitoringPlugin extends MantisPlugin {
    function register() {
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
			
       );
    }
    function manage_atm_menu() {
		return array( '<a href="' . plugin_page( 'atm' ) . '">' . plugin_lang_get( 'atm' ) . '</a>', );
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