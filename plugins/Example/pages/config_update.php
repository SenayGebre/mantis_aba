<?php
form_security_validate( 'plugin_Example_config_update' );

$f_foo_or_bar = gpc_get_string( 'foo_or_bar' );
$f_reset = gpc_get_bool( 'reset', false );

form_security_purge( 'plugin_Example_config_update' );

if( $f_reset ) {
    plugin_config_delete( 'foo_or_bar' );
} elseif( $f_foo_or_bar == 'foo' || $f_foo_or_bar == 'bar' ) {
    plugin_config_set( 'foo_or_bar', $f_foo_or_bar );
} else {
    error_parameters( 'foo_or_bar', string_attribute( $f_foo_or_bar ) );
    trigger_error( ERROR_CONFIG_OPT_INVALID, ERROR );
}

print_successful_redirect( plugin_page( 'config', true ) );
