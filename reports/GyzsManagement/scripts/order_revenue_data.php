<?php
 
/*
 * DataTables example server-side processing script.
 *
 * Please note that this script is intentionally extremely simple to show how
 * server-side processing can be implemented, and probably shouldn't be used as
 * the basis for a large complex system. It is suitable for simple use cases as
 * for learning.
 *
 * See http://datatables.net/usage/server-side for full details on the server-
 * side processing requirements of DataTables.
 *
 * @license MIT - http://datatables.net/license_mit
 */
 
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */
 
 include "../config/dbconfig.php";
 include "../define/constants.php";

// DB table to use
 $table = "mage_sales_flat_order_item AS msfoi INNER JOIN mage_sales_flat_order AS msfo ON msfo.entity_id = msfoi.order_id 
 LEFT JOIN mage_catalog_product_entity_text AS mcpet_af ON mcpet_af.entity_id = msfoi.product_id AND mcpet_af.attribute_id = '".afwijkenidealeverpakking."'
 LEFT JOIN mage_catalog_product_entity_text AS mcpet ON mcpet.entity_id = msfoi.product_id AND mcpet.attribute_id = '".IDEALEVERPAKKING."'";
 
// Table's primary key
$primaryKey = 'msfo.entity_id';
$_POST['dateFrom']; 
$fromDate = date('Y-m-d 00:00:00', strtotime($_POST['dateFrom']));
$toDate = date('Y-m-d 23:59:59', strtotime($_POST['dateTo']));

$extra_where = "state IN('complete','processing_processed','icecore_ok','closed') AND msfo.created_at >= '".$fromDate."' AND msfo.created_at <= '".$toDate."' group by msfoi.sku";

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
    array( 'db' => 'msfoi.sku AS sku', 'dt' => 0 ),
    array( 'db' => 'msfoi.name AS name',  'dt' => 1 ),
    array( 'db' => 'SUM((msfoi.qty_ordered * CASE WHEN mcpet_af.value = 0 THEN mcpet.value ELSE 1 END ) ) AS qty_ordered',   'dt' => 2 ),
    array( 'db' => 'msfoi.base_cost AS base_cost',     'dt' => 3 ),
    array( 'db' => 'msfoi.price AS price',     'dt' => 4 ),
    array( 'db' => 'qty_ordered * msfoi.price AS totalprice',     'dt' => 5 ),
    array( 'db' => 'qty_ordered * base_cost AS inkwrdeprice',    'dt' => 6 ),
    array( 'db' => '((price - base_cost) * qty_ordered) AS marginprice',     'dt' => 7 ),
    array( 'db' => '(((price / base_cost) - 1) * 100) AS marginpercent',     'dt' => 8 ),
    array( 'db' => '0 AS aantalbol',     'dt' => 9 ),
    array( 'db' => '0 AS omzetbol',     'dt' => 10 )
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
    SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns, $extra_where )
);

   /* require_once("../../app/Mage.php");
    umask(0);
    Mage::app();

    if ($_POST['dateFrom'] != null && $_POST['dateTo'] != null) {
    
      $fromDate = date('Y-m-d 00:00:00', strtotime($_POST['dateFrom']));
      $toDate = date('Y-m-d 23:59:59', strtotime($_POST['dateTo']));
      
      $resource = Mage::getSingleton('core/resource');
      $readConnection = $resource->getConnection('core_read');
      $orderTable = $resource->getTableName('sales/order');
      $orderItemTable = $resource->getTableName('sales/order_item');

      $query = 'SELECT * FROM ' . $orderTable . '
          WHERE 
      state IN("complete","processing_processed","icecore_ok","closed")
          AND
          (
          `created_at` >= "'.$fromDate.'"
          AND
          `created_at` <= "'.$toDate.'"
          )';

      echo 'Helllo' die;
    }
    */