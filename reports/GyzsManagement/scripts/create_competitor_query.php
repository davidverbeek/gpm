<?php

 include "../define/constants.php";
 include "../config/dbconfig.php";

$table = "mage_catalog_product_entity AS mcpe
        INNER JOIN
    price_management_data AS pmd ON pmd.product_id = mcpe.entity_id
        LEFT JOIN
    mage_catalog_product_entity_varchar AS mcpev ON mcpev.entity_id = pmd.product_id
        AND mcpev.attribute_id = '191'
        LEFT JOIN
    mage_catalog_product_entity_varchar AS mcpev_productname ON mcpev_productname.entity_id = pmd.product_id
        AND mcpev_productname.attribute_id = '71'
        LEFT JOIN
    mage_catalog_product_entity_text AS mcpet_af ON mcpet_af.entity_id = pmd.product_id
        AND mcpet_af.attribute_id = '1511'
        LEFT JOIN
    mage_catalog_product_entity_text AS mcpet ON mcpet.entity_id = pmd.product_id
        AND mcpet.attribute_id = '1495'
        LEFT JOIN
    mage_catalog_product_entity_varchar AS mcpev_afw ON mcpev_afw.entity_id = pmd.product_id
        AND mcpev_afw.attribute_id = '1511'
        LEFT JOIN
    mage_catalog_product_entity_varchar AS mcpev_ideal ON mcpev_ideal.entity_id = pmd.product_id
        AND mcpev_ideal.attribute_id = '1495'
        LEFT JOIN
    mage_catalog_product_entity_decimal AS mcped ON mcped.entity_id = pmd.product_id
        AND mcped.attribute_id = '79'
        LEFT JOIN
    mage_catalog_product_entity_decimal AS mcped_selling_price ON mcped_selling_price.entity_id = pmd.product_id
        AND mcped_selling_price.attribute_id = '75'
	LEFT JOIN gyzscompetitors_data gcd ON 
		gcd.product_id = pmd.product_id";

//mcpev_grossprice
//mcpev_netprice

//mcpip
// Table's primary key
$primaryKey = 'DISTINCT mcpe.entity_id';

// Extra Where
// if($_POST['categories']) {
   // //$selected_categories = implode(",",$_REQUEST['categories']);
  // $extra_where = "mccp.category_id IN (".$_POST['categories'].")";
// } else {
   // //$extra_where = "mccp.category_id IN ('')";
  // $extra_where = "";  
// }
  $extra_where = "";  

$columns = array(
	array( 'db' => 'DISTINCT mcpe.entity_id AS product_id', 'dt' => 0),
	array( 'db' => 'mcpev_productname.value AS product_name', 'dt' => 1),
	array( 'db' => 'mcpe.sku AS sku', 'dt' => 2 ),
	array( 'db' => 'mcpev.value AS ean', 'dt' => 3),
	array( 'db' => 'pmd.idealeverpakking AS idealeverpakking',  'dt' => 4),
	array( 'db' => 'pmd.afwijkenidealeverpakking AS afwijkenidealeverpakking',  'dt' => 5),
	array( 'db' => 'pmd.buying_price AS buying_price',  'dt' => 6),
	array( 'db' => 'pmd.selling_price AS selling_price',  'dt' => 7),
	  
	array( 'db' => 'gcd.product_data AS product_data', 'dt' => 8)
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