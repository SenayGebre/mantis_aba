<?php

// require_once('../api_atm.php');
// require_once(''.dirname(__DIR__).'/api_atm.php');


$terminal_id =   $_POST['terminal_data'];

// $t_query = "SELECT * FROM states WHERE country_id = $country_id";

// $state_qry = db_query($t_query);

echo '<option value="'.$terminal_id.'">'.$terminal_id.'</option>';

