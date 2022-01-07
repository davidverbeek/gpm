  <?php

 include "../define/constants.php";
 include "../config/dbconfig.php";

$table = "price_management_bol_commission AS bc";

// Table's primary key
$primaryKey = 'bc.id';

$extra_where = "";


// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes

$columns = array(
  array( 'db' => 'bc.id AS id', 'dt' => 0),
  array( 'db' => 'bc.product_sku AS product_sku', 'dt' => 1),
  array( 'db' => 'bc.product_verpakkingsEAN AS product_verpakkingsEAN', 'dt' => 2),
  array( 'db' => 'bc.api_condition AS api_condition', 'dt' => 3),
  array( 'db' => 'bc.api_unit_price AS api_unit_price', 'dt' => 4),
  array( 'db' => 'bc.api_fixed_amout AS api_fixed_amout', 'dt' => 5),
  array( 'db' => 'bc.api_percentage AS api_percentage', 'dt' => 6),
  array( 'db' => 'bc.api_total_cost AS api_total_cost', 'dt' => 7),
  array( 'db' => 'bc.updated_date_time AS updated_date_time', 'dt' => 8)
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
