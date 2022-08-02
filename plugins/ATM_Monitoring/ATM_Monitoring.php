<?php
$terminal_id  = gpc_get_string( 'terminal_id', '' );


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
            'EVENT_REPORT_BUG_FORM_TOP' => 'select_atm',
       );
    }
  

    function select_atm() {
        global $terminal_id;
        echo '<tr>';
		echo '<th class="category">';
		echo '<span class="required">*</span><label for="termial_id">'. plugin_lang_get('terminal_id') . '</label>';
		echo '</th>';
		echo '<td>';
		echo '<input echo' .  helper_get_tab_index() . 'type="text" id="termial_id" name="termial_id" size="105" maxlength="128" value=" ' . string_attribute( $terminal_id) .'" required />';
		echo $terminal_id;
        echo '</td>';
	    echo '</tr>';
        
		
        
    }

    function manage_atm_menu() {
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