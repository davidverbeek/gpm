  <?php

include "../define/constants.php";
include "../config/dbconfig.php";

$date_range = explode("To",$_POST['hdn_date_range']);
$sku = $_POST['product_sku'];

$table = "mage_sales_flat_order AS msfo
          INNER JOIN
          mage_sales_flat_order_item AS msfoi ON msfo.entity_id = msfoi.order_id 
          AND msfo.created_at >= '".trim($date_range[0])."'
          AND msfo.created_at < '".trim($date_range[1])."'
          AND msfoi.sku = '".$sku."'";

// Table's primary key
$primaryKey = 'msfo.created_at';

$extra_where = "msfo.state != 'canceled'";


$columns = array(
  array( 'db' => 'msfo.created_at AS created_at', 'dt' => 0),
  array( 'db' => 'msfoi.order_id AS order_id', 'dt' => 1),
  array( 'db' => 'msfoi.qty_ordered AS qty_ordered', 'dt' => 2),
  array( 'db' => 'msfoi.qty_refunded AS qty_refunded', 'dt' => 3),
  
  array( 'db' => 'msfoi.base_cost AS base_cost',  'dt' => 4),
  array( 'db' => 'msfoi.base_price AS base_price', 'dt' => 5),

  array( 'db' => '(CASE msfoi.afwijkenidealeverpakking WHEN 0 THEN (msfoi.base_cost * msfoi.qty_ordered * msfoi.idealeverpakking)  ELSE (msfoi.base_cost * msfoi.qty_ordered) END) AS cost', 'dt' => 6),
  
  array( 'db' => '(msfoi.base_price * msfoi.qty_ordered) AS price',  'dt' => 7),
  
  array( 'db' => '((msfoi.base_price * msfoi.qty_ordered) - (CASE msfoi.afwijkenidealeverpakking WHEN 0 THEN (msfoi.base_cost * msfoi.qty_ordered * msfoi.idealeverpakking)  ELSE (msfoi.base_cost * msfoi.qty_ordered) END) ) AS absolute_margin', 'dt' => 8),

  array( 'db' => 'msfoi.afwijkenidealeverpakking AS afwijkenidealeverpakking',  'dt' => 9),
  array( 'db' => 'msfoi.idealeverpakking AS idealeverpakking',  'dt' => 10)
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