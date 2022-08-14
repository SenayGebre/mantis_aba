<?php

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'error_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'print_api.php' );
require_once(''.dirname(__DIR__).'/api_atm.php');

require_api( 'project_api.php' );

echo "senay";

// project_create( "ATM Monitoring", "This is project is for ATM monitoring", 50, 10, '');
// form_security_purge( 'create_atm_monitoring_project' );
// print_successful_redirect( plugin_page( 'manage_atm_page',true));