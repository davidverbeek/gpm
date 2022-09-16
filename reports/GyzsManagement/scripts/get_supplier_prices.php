<?php

include "../define/constants.php";
include "../config/dbconfig.php";
ini_set('memory_limit', '6G');
 
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
 
// DB table to use, 
//INNER JOIN price_management_data AS pmd ON pmd.sku = prod.sku

/* $table = "all_supplier_products AS prod
INNER JOIN suppliers ON suppliers.id = prod.supplier_id"; */

$table = "all_supplier_products prod
LEFT OUTER JOIN all_supplier_products prop1001 ON prod.ean = prop1001.ean 
    AND prop1001.supplier_id=1
LEFT OUTER JOIN all_supplier_products prop1002 ON prod.ean = prop1002.ean
    AND prop1002.supplier_id=2
LEFT OUTER JOIN all_supplier_products prop1003 ON prod.ean = prop1003.ean
    AND prop1003.supplier_id=3
LEFT OUTER JOIN all_supplier_products prop1004 ON prod.ean = prop1004.ean
    AND prop1004.supplier_id=4";
 
// Table's primary key
$primaryKey = 'DISTINCT prod.id';
 

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
//array( 'db' => 'prod.id AS id', 'dt' => 0),,
 //   array( 'db' => 'supplier_name AS supplier', 'dt' => 6 )
$columns = array(
    array( 'db' => 'prod.ean AS m_ean', 'dt' => 0 ),
    array( 'db' => 'prop1001.sku AS m_sku', 'dt' => 1),
    array( 'db' => 'prop1001.bp_min_order_qty AS m_buying_price', 'dt' => 2 ),
    array( 'db' => 'prop1001.min_order_qty AS m_qty', 'dt' => 3 ),
    array( 'db' => 'prop1001.afwijkenidealeverpakking AS m_afw', 'dt' => 4 ),
    array( 'db' => 'prop1001.bp_per_piece AS m_piece', 'dt' => 5 ),/* ,
    array( 'db' => 'prop1001.supplier_id AS m_supplier_id', 'dt' => 6 ), */
    array( 'db' => 'prop1002.sku AS p_sku', 'dt' => 6),
    array( 'db' => 'prop1002.bp_min_order_qty AS p_buying_price', 'dt' => 7),
    array( 'db' => 'prop1002.min_order_qty AS p_qty', 'dt' => 8),
    array( 'db' => 'prop1002.afwijkenidealeverpakking AS p_afw', 'dt' => 9),
    array( 'db' => 'prop1002.bp_per_piece AS p_piece', 'dt' => 10),/* 
    array( 'db' => 'prop1002.supplier_id AS p_supplier_id', 'dt' => 12), */
    array( 'db' => 'prop1003.sku AS t_sku', 'dt' => 11),
    array( 'db' => 'prop1003.bp_min_order_qty AS t_buying_price', 'dt' => 12 ),
    array( 'db' => 'prop1003.min_order_qty AS t_qty', 'dt' => 13 ),
    array( 'db' => 'prop1003.afwijkenidealeverpakking AS t_afw', 'dt' => 14),
    array( 'db' => 'prop1003.bp_per_piece AS t_piece', 'dt' => 15),/* 
    array( 'db' => 'prop1003.supplier_id AS t_supplier_id', 'dt' => 18), */
    array( 'db' => 'prop1004.sku AS d_sku', 'dt' => 16),
    array( 'db' => 'prop1004.bp_min_order_qty AS d_buying_price', 'dt' => 17 ),
    array( 'db' => 'prop1004.min_order_qty AS d_qty', 'dt' => 18 ),
    array( 'db' => 'prop1004.afwijkenidealeverpakking AS d_afw', 'dt' => 19 ),
    array( 'db' => 'prop1004.bp_per_piece AS d_piece', 'dt' => 20 ),
    array( 'db' => 'prop1001.supplier_id AS m_supplier_id', 'dt' => 21 ),
    array( 'db' => 'prop1001.supplier_id AS m_supplier_id', 'dt' => 22 ),
    array( 'db' => 'prop1001.supplier_id AS m_supplier_id', 'dt' => 23 ),
    array( 'db' => 'prop1001.supplier_id AS m_supplier_id', 'dt' => 24 ),




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
    SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns, "", 'prod.ean' )
);