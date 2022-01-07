  <?php

include "../define/constants.php";
include "../config/dbconfig.php";

$table = "roas AS r
          INNER JOIN
          mage_catalog_category_product AS mccp ON mccp.product_id = r.product_id
          LEFT JOIN
          mage_catalog_product_entity_int AS mcpei ON mcpei.entity_id = r.product_id
          AND mcpei.attribute_id = '".MERK."'
          LEFT JOIN
          mage_eav_attribute_option_value AS meaov ON meaov.option_id = mcpei.value
          LEFT JOIN
          mage_catalog_product_entity_varchar AS mcpev_productname ON mcpev_productname.entity_id = r.product_id
          AND mcpev_productname.attribute_id = '".PRODUCTNAME."'
          ";

// Table's primary key
$primaryKey = 'DISTINCT r.sku';

// Extra Where
if($_POST['categories']) {
   //$selected_categories = implode(",",$_REQUEST['categories']);
    $extra_where = "mccp.category_id IN (".$_POST['categories'].")";
} else {
    $extra_where = "";
}


// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes

$columns = array(
  array( 'db' => 'DISTINCT r.id AS id', 'dt' => $column_index_roas_feed_log["id"]),
  array( 'db' => 'r.sku AS sku', 'dt' => $column_index_roas_feed_log["sku"]),
  array( 'db' => 'mcpev_productname.value AS name', 'dt' => $column_index_roas_feed_log["name"]),
  array( 'db' => 'meaov.value AS brand',  'dt' => $column_index_roas_feed_log["brand"]),


  array( 'db' => 'r.carrier_level AS carrier_level', 'dt' => $column_index_roas_feed_log["carrier_level"]),
  array( 'db' => 'r.total_quantity AS total_quantity', 'dt' => $column_index_roas_feed_log["total_quantity"]),
  array( 'db' => 'r.total_orders AS total_orders', 'dt' => $column_index_roas_feed_log["total_orders"]),

  array( 'db' => 'r.total_orders_bol AS total_orders_bol', 'dt' => $column_index_roas_feed_log["total_orders_bol"]),
  array( 'db' => 'r.total_quantity_bol AS total_quantity_bol', 'dt' => $column_index_roas_feed_log["total_quantity_bol"]),

  array( 'db' => 'r.return_general AS return_general', 'dt' => $column_index_roas_feed_log["return_general"]),
  array( 'db' => 'r.return_bol AS return_bol', 'dt' => $column_index_roas_feed_log["return_bol"]),
  array( 'db' => 'r.return_nobol AS return_nobol', 'dt' => $column_index_roas_feed_log["return_nobol"]),

  array( 'db' => 'r.return_order_general AS return_order_general', 'dt' => $column_index_roas_feed_log["return_order_general"]),
  array( 'db' => 'r.return_order_bol AS return_order_bol', 'dt' => $column_index_roas_feed_log["return_order_bol"]),
  array( 'db' => 'r.return_order_nobol AS return_order_nobol', 'dt' => $column_index_roas_feed_log["return_order_nobol"]),


  array( 'db' => 'r.parent_product_factor AS parent_product_factor', 'dt' => $column_index_roas_feed_log["parent_product_factor"]),
  array( 'db' => 'r.parent_absolute_margin AS parent_absolute_margin', 'dt' => $column_index_roas_feed_log["parent_absolute_margin"]),
  array( 'db' => 'r.parent_return_margin AS parent_return_margin', 'dt' => $column_index_roas_feed_log["parent_return_margin"]),
  array( 'db' => 'r.total_parent_margin AS total_parent_margin', 'dt' => $column_index_roas_feed_log["total_parent_margin"]),
  array( 'db' => 'r.average_order_per_month AS average_order_per_month', 'dt' => $column_index_roas_feed_log["average_order_per_month"]),
  array( 'db' => 'r.other_absolute_margin AS other_absolute_margin', 'dt' => $column_index_roas_feed_log["other_absolute_margin"]),
  array( 'db' => 'r.total_absolute_margin AS total_absolute_margin', 'dt' => $column_index_roas_feed_log["total_absolute_margin"]),
  array( 'db' => 'r.shipment_revenue AS shipment_revenue', 'dt' => $column_index_roas_feed_log["shipment_revenue"]),
  array( 'db' => 'r.shipment_cost AS shipment_cost', 'dt' => $column_index_roas_feed_log["shipment_cost"]),
  array( 'db' => 'r.shipment_diff AS shipment_diff', 'dt' => $column_index_roas_feed_log["shipment_diff"]),
  array( 'db' => 'r.employee_cost AS employee_cost', 'dt' => $column_index_roas_feed_log["employee_cost"]),
  array( 'db' => 'r.margin_after_deductions AS margin_after_deductions', 'dt' => $column_index_roas_feed_log["margin_after_deductions"]),
  array( 'db' => 'r.total_selling_price AS total_selling_price', 'dt' => $column_index_roas_feed_log["total_selling_price"]),
  array( 'db' => 'r.payment_other_company_cost AS payment_other_company_cost', 'dt' => $column_index_roas_feed_log["payment_other_company_cost"]),
  array( 'db' => 'r.burning_margin AS burning_margin', 'dt' => $column_index_roas_feed_log["burning_margin"]),
  array( 'db' => 'r.roas_target AS roas_target', 'dt' => $column_index_roas_feed_log["roas_target"]),
  
  array( 'db' => 'r.google_kosten AS google_kosten', 'dt' => $column_index_roas_feed_log["google_kosten"]),
  array( 'db' => 'r.google_roas AS google_roas', 'dt' => $column_index_roas_feed_log["google_roas"]),
  array( 'db' => 'r.performance AS performance', 'dt' => $column_index_roas_feed_log["performance"]),

  array( 'db' => 'r.avg_per_cat AS avg_per_cat', 'dt' => $column_index_roas_feed_log["avg_per_cat"]),
  array( 'db' => 'r.avg_per_cat_per_brand AS avg_per_cat_per_brand', 'dt' => $column_index_roas_feed_log["avg_per_cat_per_brand"]),
  array( 'db' => 'r.roas_per_cat_per_brand AS roas_per_cat_per_brand', 'dt' => $column_index_roas_feed_log["roas_per_cat_per_brand"]),
  array( 'db' => 'r.end_roas AS end_roas', 'dt' => $column_index_roas_feed_log["end_roas"]),

  array( 'db' => 'r.return_help AS return_help', 'dt' => $column_index_roas_feed_log["return_help"]),
  array( 'db' => 'r.return_order_help AS return_order_help', 'dt' => $column_index_roas_feed_log["return_order_help"]),
  array( 'db' => 'r.roas_per_cat_per_brand_help AS roas_per_cat_per_brand_help', 'dt' => $column_index_roas_feed_log["roas_per_cat_per_brand_help"]),
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
