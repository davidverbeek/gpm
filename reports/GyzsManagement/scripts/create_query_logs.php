  <?php

include "../define/constants.php";
include "../config/dbconfig.php";

$table = "price_management_history";

// Table's primary key
$primaryKey = 'history_id';


// Extra Where
if($_POST['product_id']) {
  $extra_where = "product_id = '".$_POST['product_id']."'";
} 



$columns = array(
  array( 'db' => 'updated_date_time AS updated_date_time', 'dt' => $column_index_price_log["updated_date_time"]),
  array( 'db' => 'old_net_unit_price AS old_net_unit_price', 'dt' => $column_index_price_log["old_net_unit_price"]),
  array( 'db' => 'old_gross_unit_price AS old_gross_unit_price', 'dt' => $column_index_price_log["old_gross_unit_price"]),
  array( 'db' => 'old_idealeverpakking AS old_idealeverpakking', 'dt' => $column_index_price_log["old_idealeverpakking"] ),
  array( 'db' => 'old_afwijkenidealeverpakking AS old_afwijkenidealeverpakking', 'dt' => $column_index_price_log["old_afwijkenidealeverpakking"]),
  array( 'db' => 'old_buying_price AS old_buying_price',  'dt' => $column_index_price_log["old_buying_price"]),
  array( 'db' => 'old_selling_price AS old_selling_price',  'dt' => $column_index_price_log["old_selling_price"]),
  array( 'db' => 'new_net_unit_price AS new_net_unit_price',  'dt' => $column_index_price_log["new_net_unit_price"]),
  array( 'db' => 'new_gross_unit_price AS new_gross_unit_price',  'dt' => $column_index_price_log["new_gross_unit_price"]),
  array( 'db' => 'new_idealeverpakking AS new_idealeverpakking',  'dt' => $column_index_price_log["new_idealeverpakking"]),
  array( 'db' => 'new_afwijkenidealeverpakking AS new_afwijkenidealeverpakking',  'dt' => $column_index_price_log["new_afwijkenidealeverpakking"]),
  array( 'db' => 'new_buying_price AS new_buying_price',  'dt' => $column_index_price_log["new_buying_price"]),
  array( 'db' => 'new_selling_price AS new_selling_price',  'dt' => $column_index_price_log["new_selling_price"]),
  array( 'db' => 'updated_by AS updated_by',  'dt' => $column_index_price_log["updated_by"]),
  array( 'db' => 'is_viewed AS is_viewed',  'dt' => $column_index_price_log["is_viewed"]),
  array( 'db' => 'history_id AS history_id',  'dt' => $column_index_price_log["history_id"]),
  array( 'db' => 'product_id AS product_id',  'dt' => $column_index_price_log["product_id"]),
  array( 'db' => 'fields_changed AS fields_changed',  'dt' => $column_index_price_log["fields_changed"])
);

// SQL server connection information
$sql_details = array(
  'user' => $username,
  'pass' => $password,
  'db'   => $dbname,
  'host' => $servername
);

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */

require( 'ssp.class.php' );

echo json_encode(
  SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns, $extra_where)
);


?>