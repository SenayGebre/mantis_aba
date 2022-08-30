<?php

// require_once('./core/plugin_api.php');
require_once(dirname(__DIR__).'/../core.php');


$branch_id =   $_POST['branch_data'];

$t_query = 'SELECT * FROM mantis_plugin_atm_monitoring_atm_table WHERE branch_id = '.$branch_id;

$terminal_list_qry = db_query($t_query);


echo '<div class="terminal_chips" id="terminal_chips">';

while($row = db_fetch_array($terminal_list_qry))
{
// echo '<input type="checkbox" id="'.$row['terminal_id'].'" name="'.$row['terminal_id'].'" value="'.$row['terminal_id'].'">
// <label for="'.$row['terminal_id'].'">'.$row['terminal_id'].'</label>';
echo '<div class="atm_chip" id="'.$row['terminal_id'].'" id="'.$row['terminal_id'].'">
  <span class="addButton" >'.$row['terminal_id'].' &plus;</span>
</div>';
}

echo '</div>';


