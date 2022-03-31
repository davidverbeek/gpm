  <?php

 include "../define/constants.php";
 include "../config/dbconfig.php";

$table = "gyzsrevenuedata AS rd
          LEFT JOIN
          mage_catalog_category_product AS mccp ON mccp.product_id = rd.product_id
          LEFT JOIN
          mage_catalog_product_entity_int AS mcpei ON mcpei.entity_id = rd.product_id
          AND mcpei.attribute_id = '".MERK."'
          LEFT JOIN
          mage_catalog_product_entity_int AS mcpei_transmission ON mcpei_transmission.entity_id = rd.product_id
          AND mcpei_transmission.attribute_id = '".tansmission."'
          LEFT JOIN
          mage_catalog_product_entity_int AS mcpei_brieven ON mcpei_brieven.entity_id = rd.product_id
          AND mcpei_brieven.attribute_id = '".brievenbuspakket."'
          LEFT JOIN
          mage_eav_attribute_option_value AS meaov ON meaov.option_id = mcpei.value
          LEFT JOIN
          mage_catalog_product_entity_varchar AS mcpev_productname ON mcpev_productname.entity_id = rd.product_id AND mcpev_productname.attribute_id = '".PRODUCTNAME."'
          LEFT JOIN
          price_management_data AS pmd ON pmd.product_id = rd.product_id";

// Table's primary key
$primaryKey = 'DISTINCT rd.sku';

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
  array( 'db' => 'DISTINCT rd.id AS id', 'dt' => $column_index_revenue_report["id"]),
  array( 'db' => 'pmd.supplier_type AS supplier_type', 'dt' => $column_index_revenue_report["supplier_type"]),
  array( 'db' => 'rd.sku AS sku', 'dt' => $column_index_revenue_report["sku"]),
  array( 'db' => '(CASE WHEN (mcpei_transmission.value = 1) THEN "Transmission" ELSE (CASE WHEN (mcpei_brieven.value = 1) THEN "Briefpost" ELSE "Pakketpost" END) END) AS carrier_level',  'dt' => $column_index_revenue_report["carrier_level"]),
  array( 'db' => 'mcpev_productname.value AS name', 'dt' => $column_index_revenue_report["name"]),
  array( 'db' => 'meaov.value AS brand',  'dt' => $column_index_revenue_report["brand"]),
  array( 'db' => 'rd.sku_total_quantity_sold_365 AS sku_total_quantity_sold_365', 'dt' => $column_index_revenue_report["sku_total_quantity_sold_365"]),
  array('db' => 'rd.sku_total_quantity_sold AS sku_total_quantity_sold', 'dt' =>  $column_index_revenue_report["sku_total_quantity_sold"]),
  array('db' => 'rd.sku_total_price_excl_tax AS sku_total_price_excl_tax', 'dt' => $column_index_revenue_report["sku_total_price_excl_tax"]),
  array( 'db' => 'rd.sku_vericale_som AS sku_vericale_som', 'dt' => $column_index_revenue_report["sku_vericale_som"]),
  array( 'db' => 'rd.vericale_som_percentage AS vericale_som_percentage', 'dt' => $column_index_revenue_report["vericale_som_percentage"]),
  array( 'db' => 'rd.sku_bp_excl_tax AS sku_bp_excl_tax', 'dt' => $column_index_revenue_report["sku_bp_excl_tax"]),
  array( 'db' => 'rd.sku_sp_excl_tax AS sku_sp_excl_tax', 'dt' => $column_index_revenue_report["sku_sp_excl_tax"]),
  array( 'db' => 'rd.sku_abs_margin AS sku_abs_margin', 'dt' => $column_index_revenue_report["sku_abs_margin"]),
  array( 'db' => 'rd.sku_margin_bp AS sku_margin_bp', 'dt' => $column_index_revenue_report["sku_margin_bp"]),
  array( 'db' => 'rd.sku_margin_sp AS sku_margin_sp', 'dt' => $column_index_revenue_report["sku_margin_sp"]),
  array( 'db' => 'rd.sku_vericale_som_bp AS sku_vericale_som_bp', 'dt' => $column_index_revenue_report["sku_vericale_som_bp"]),
  array( 'db' => 'rd.sku_vericale_som_bp_percentage AS sku_vericale_som_bp_percentage', 'dt' => $column_index_revenue_report["sku_vericale_som_bp_percentage"]),
  array( 'db' => 'rd.sku_refund_qty AS sku_refund_qty', 'dt' => $column_index_revenue_report["sku_refund_qty"]),
  array( 'db' => 'rd.sku_refund_revenue_amount AS sku_refund_revenue_amount', 'dt' => $column_index_revenue_report["sku_refund_revenue_amount"]),
  array( 'db' => 'rd.sku_refund_bp_amount AS sku_refund_bp_amount', 'dt' => $column_index_revenue_report["sku_refund_bp_amount"]),
  array( 'db' => 'rd.sku_vericale_som_abs AS sku_vericale_som_abs', 'dt' => $column_index_revenue_report["sku_vericale_som_abs"]),
  array( 'db' => 'rd.sku_vericale_som_abs_percentage AS sku_vericale_som_abs_percentage', 'dt' => $column_index_revenue_report["sku_vericale_som_abs_percentage"]),
  array( 'db' => 'rd.product_id AS product_id', 'dt' => $column_index_revenue_report["product_id"]),
  array( 'db' => 'rd.reportdate AS reportdate', 'dt' => $column_index_revenue_report["reportdate"])
);

/*
if($_POST['categories']) {
  
  $table = "gyzsrevenuedata AS rd cross join (select @s := 0) p
          LEFT JOIN
          mage_catalog_category_product AS mccp ON mccp.product_id = rd.product_id
          LEFT JOIN
          mage_catalog_product_entity_int AS mcpei ON mcpei.entity_id = rd.product_id
          AND mcpei.attribute_id = '".MERK."'
          LEFT JOIN
          mage_eav_attribute_option_value AS meaov ON meaov.option_id = mcpei.value
          LEFT JOIN
          mage_catalog_product_entity_varchar AS mcpev_productname ON mcpev_productname.entity_id = rd.product_id
          AND mcpev_productname.attribute_id = '".PRODUCTNAME."'
          ";

$columns = array();
  $columns = array(
    array( 'db' => 'DISTINCT rd.id AS id', 'dt' => $column_index_revenue_report["id"]),
    array( 'db' => 'rd.sku AS sku', 'dt' => $column_index_revenue_report["sku"]),
    array( 'db' => 'mcpev_productname.value AS name', 'dt' => $column_index_revenue_report["name"]),
    array( 'db' => 'meaov.value AS brand',  'dt' => $column_index_revenue_report["brand"]),
    array( 'db' => 'rd.sku_total_quantity_sold AS sku_total_quantity_sold', 'dt' => $column_index_revenue_report["sku_total_quantity_sold"]),
    array( 'db' => 'rd.sku_total_price_excl_tax AS sku_total_price_excl_tax', 'dt' => $column_index_revenue_report["sku_total_price_excl_tax"]),

    array( 'db' => '(@s := @s + rd.sku_total_price_excl_tax) AS sku_vericale_som', 'dt' => $column_index_revenue_report["sku_vericale_som"]),
    array( 'db' => 'rd.vericale_som_percentage AS vericale_som_percentage', 'dt' => $column_index_revenue_report["vericale_som_percentage"]),
    array( 'db' => 'rd.sku_bp_excl_tax AS sku_bp_excl_tax', 'dt' => $column_index_revenue_report["sku_bp_excl_tax"]),
    array( 'db' => 'rd.sku_sp_excl_tax AS sku_sp_excl_tax', 'dt' => $column_index_revenue_report["sku_sp_excl_tax"]),
    array( 'db' => 'rd.sku_abs_margin AS sku_abs_margin', 'dt' => $column_index_revenue_report["sku_abs_margin"]),
    array( 'db' => 'rd.sku_margin_bp AS sku_margin_bp', 'dt' => $column_index_revenue_report["sku_margin_bp"]),
    array( 'db' => 'rd.sku_margin_sp AS sku_margin_sp', 'dt' => $column_index_revenue_report["sku_margin_sp"]),
    array( 'db' => 'rd.sku_vericale_som_bp AS sku_vericale_som_bp', 'dt' => $column_index_revenue_report["sku_vericale_som_bp"]),
    array( 'db' => 'rd.sku_vericale_som_bp_percentage AS sku_vericale_som_bp_percentage', 'dt' => $column_index_revenue_report["sku_vericale_som_bp_percentage"]),
    array( 'db' => 'rd.sku_refund_qty AS sku_refund_qty', 'dt' => $column_index_revenue_report["sku_refund_qty"]),
    array( 'db' => 'rd.sku_refund_revenue_amount AS sku_refund_revenue_amount', 'dt' => $column_index_revenue_report["sku_refund_revenue_amount"]),
    array( 'db' => 'rd.sku_refund_bp_amount AS sku_refund_bp_amount', 'dt' => $column_index_revenue_report["sku_refund_bp_amount"]),
    array( 'db' => 'rd.sku_vericale_som_abs AS sku_vericale_som_abs', 'dt' => $column_index_revenue_report["sku_vericale_som_abs"]),
    array( 'db' => 'rd.sku_vericale_som_abs_percentage AS sku_vericale_som_abs_percentage', 'dt' => $column_index_revenue_report["sku_vericale_som_abs_percentage"])
  );  
}
*/



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
