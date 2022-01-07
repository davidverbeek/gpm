  <?php

 include "../define/constants.php";
 include "../config/dbconfig.php";

$table = "price_management_bol_minimum AS bm";

$table = "mage_catalog_product_entity AS mcpe
INNER JOIN price_management_bol_minimum AS pmbm ON pmbm.product_id = mcpe.entity_id
LEFT JOIN price_management_bol_commission AS pmbc ON pmbc.product_id = pmbm.product_id
LEFT JOIN mage_catalog_product_entity_varchar AS mcpev_productname ON mcpev_productname.entity_id = pmbm.product_id AND mcpev_productname.attribute_id = '".PRODUCTNAME."'
LEFT JOIN price_management_ec_deliverytime AS pmecdtime ON pmecdtime.option_id = pmbm.ec_deliverytime
LEFT JOIN price_management_ec_deliverytime_be AS pmecdtimebe ON pmecdtimebe.option_id = pmbm.ec_deliverytime_be

LEFT JOIN mage_catalog_product_entity_text AS mcpet_af ON mcpet_af.entity_id = pmbm.product_id AND mcpet_af.attribute_id = '".afwijkenidealeverpakking."'
LEFT JOIN mage_catalog_product_entity_text AS mcpet ON mcpet.entity_id = pmbm.product_id AND mcpet.attribute_id = '".IDEALEVERPAKKING."'

LEFT JOIN mage_catalog_product_entity_varchar AS mcpev_afw ON mcpev_afw.entity_id = pmbm.product_id AND mcpev_afw.attribute_id = '".afwijkenidealeverpakking."'
LEFT JOIN mage_catalog_product_entity_varchar AS mcpev_ideal ON mcpev_ideal.entity_id = pmbm.product_id AND mcpev_ideal.attribute_id = '".IDEALEVERPAKKING."'

LEFT JOIN mage_catalog_product_entity_decimal AS mcped ON mcped.entity_id = pmbm.product_id AND mcped.attribute_id = '".COST."'
LEFT JOIN mage_catalog_product_entity_decimal AS mcped_selling_price ON mcped_selling_price.entity_id = pmbm.product_id AND mcped_selling_price.attribute_id = '".PRICE."'";

// Table's primary key
$primaryKey = 'DISTINCT mcpe.entity_id';

$extra_where = "";


// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes

$columns = array(
  array( 'db' => 'DISTINCT mcpe.entity_id AS product_id', 'dt' => 0),
  array( 'db' => 'mcpe.sku AS sku', 'dt' => 1),
  array( 'db' => 'mcpev_productname.value AS product_name', 'dt' => 2),
  array( 'db' => 'pmbm.supplier_type AS supplier_type', 'dt' => 3),
  array( 'db' => 'pmbm.product_bol_minimum_price AS product_bol_minimum_price', 'dt' => 4),
  array( 'db' => '(CASE WHEN (CASE WHEN mcpet_af.value IS NOT NULL THEN mcpet_af.value = 0 ELSE mcpev_afw.value = 0 END) THEN CAST((mcped.value * CASE WHEN mcpet.value IS NOT NULL THEN mcpet.value ELSE mcpev_ideal.value END) AS DECIMAL (10 , '.$scale.' )) ELSE CAST((mcped.value) AS DECIMAL (10 , '.$scale.' )) END) AS buying_price', 'dt' => 5),
  array( 'db' => 'pmbm.updated_date_time AS updated_date_time', 'dt' => 6),
  array( 'db' => 'pmecdtime.option_value AS ec_deliverytime', 'dt' => 7),
  array( 'db' => 'pmecdtimebe.option_value AS ec_deliverytime_be', 'dt' => 8),
  array( 'db' => 'pmbm.product_bol_selling_price AS product_bol_selling_price', 'dt' => 9),
  array( 'db' => '(CASE WHEN (CASE WHEN mcpet_af.value IS NOT NULL THEN mcpet_af.value = 0 ELSE mcpev_afw.value = 0 END) THEN CAST((mcped_selling_price.value * CASE WHEN mcpet.value IS NOT NULL THEN mcpet.value ELSE mcpev_ideal.value END) AS DECIMAL (10 , '.$scale.' )) ELSE CAST((mcped_selling_price.value) AS DECIMAL (10 , '.$scale.' )) END) AS selling_price', 'dt' => 10),
  array( 'db' => 'pmbm.product_bol_minimum_price_cal AS product_bol_minimum_price_cal', 'dt' => 11)
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
