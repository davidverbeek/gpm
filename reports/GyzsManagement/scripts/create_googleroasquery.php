  <?php
  include "../config/config.php";
  include "../define/constants.php";


$table = "google_actual_roas AS gar";

// Table's primary key
$primaryKey = 'id';

$extra_where = "";


// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes

$columns = array(
  array( 'db' => 'gar.id AS id', 'dt' => 0),
  array( 'db' => 'gar.from_date AS from_date', 'dt' => 1),
  array( 'db' => 'gar.to_date AS to_date', 'dt' => 2),
  array( 'db' => 'gar.file_name AS file_name', 'dt' => 3),
  array( 'db' => 'gar.active AS active', 'dt' => 4),
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
