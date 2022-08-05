<?php



function atm_get_param( $p_var_name, $p_default = null ) {

	if( isset( $_POST[$p_var_name] ) ) {
        $t_result = $_POST[$p_var_name];
	} else if( isset( $_GET[$p_var_name] ) ) {
		$t_result = $_GET[$p_var_name];
	} else if( func_num_args() > 1 ) {
		# check for a default passed in (allowing null)
		$t_result = $p_default;
	} else {
		error_parameters( $p_var_name );
		trigger_error( ERROR_GPC_VAR_NOT_FOUND, ERROR );
		$t_result = null;
	}
	
		return str_contains($t_result,'=') ? explode( '=', $t_result )[1] : '';
}

?>