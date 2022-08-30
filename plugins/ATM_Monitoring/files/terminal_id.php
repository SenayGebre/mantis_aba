<?php
require_once('api_atm.php');


$state_id =  $_POST['terminal_data'];

$terminal_rows = atmGetTerminals();

$city = "SELECT * FROM cities WHERE state_id = $state_id";
$city_qry = mysqli_query($conn, $city);


$output = '<option value="">Select State</option>';
while ($city_row = mysqli_fetch_assoc($city_qry)) {
    $output .= '<option value="' . $city_row['id'] . '">' . $city_row['name'] . '</option>';
}
echo $output;
