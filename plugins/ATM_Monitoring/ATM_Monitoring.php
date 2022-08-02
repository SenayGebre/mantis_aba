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
            'EVENT_REPORT_BUG_FORM' => 'select_atm',
       );
    }
  

    function select_atm() {
		echo '<tr';
		echo '<th class="category">';
		echo '<td>dsafadsf &#160;</td>';
		echo '<span class="required">*</span><label for="summary">'.print_documentation_link( 'summary' ).'</label>';
		echo '</th>';
		echo '<td>';
		echo '<input type="text" id="summary" name="summary" size="105" maxlength="128" value=" " required />';
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