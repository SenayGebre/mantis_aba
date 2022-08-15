<?php

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'error_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'print_api.php' );
require_once(''.dirname(__DIR__).'/api_atm.php');

require_api( 'project_api.php' );

$t_project_name = "ATM Monitoring";
$t_project_description = "This is project is for ATM monitoring";


project_create( $t_project_name, $t_project_description, 50, 10, '');
// $t_project_id = project_get_id_by_name("ATM Monitoring");
// category_add($t_project_id,t_project_name );
form_security_purge( 'create_atm_monitoring_project' );
print_successful_redirect( plugin_page( 'manage_atm_page',true));