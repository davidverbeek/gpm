  <?php

 include "../define/constants.php";
 include "../config/dbconfig.php";

$table = "mage_catalog_product_entity AS mcpe
INNER JOIN mage_catalog_category_product AS mccp ON mccp.product_id = mcpe.entity_id
LEFT JOIN mage_catalog_product_entity_varchar AS mcpev ON mcpev.entity_id = mcpe.entity_id AND mcpev.attribute_id = '".EAN."'
LEFT JOIN mage_catalog_product_entity_int AS mcpei ON mcpei.entity_id = mcpe.entity_id AND mcpei.attribute_id = '".MERK."'
LEFT JOIN mage_eav_attribute_option_value AS meaov ON meaov.option_id = mcpei.value
LEFT JOIN mage_catalog_product_entity_varchar AS mcpev_productname ON mcpev_productname.entity_id = mcpe.entity_id AND mcpev_productname.attribute_id = '".PRODUCTNAME."'

LEFT JOIN mage_catalog_product_entity_text AS mcpet_af ON mcpet_af.entity_id = mcpe.entity_id AND mcpet_af.attribute_id = '".afwijkenidealeverpakking."'
LEFT JOIN mage_catalog_product_entity_text AS mcpet ON mcpet.entity_id = mcpe.entity_id AND mcpet.attribute_id = '".IDEALEVERPAKKING."'

LEFT JOIN mage_catalog_product_entity_varchar AS mcpev_afw ON mcpev_afw.entity_id = mcpe.entity_id AND mcpev_afw.attribute_id = '".afwijkenidealeverpakking."'
LEFT JOIN mage_catalog_product_entity_varchar AS mcpev_ideal ON mcpev_ideal.entity_id = mcpe.entity_id AND mcpev_ideal.attribute_id = '".IDEALEVERPAKKING."'

LEFT JOIN mage_catalog_product_entity_decimal AS mcped ON mcped.entity_id = mcpe.entity_id AND mcped.attribute_id = '".COST."'
LEFT JOIN mage_catalog_product_entity_decimal AS mcped_selling_price ON mcped_selling_price.entity_id = mcpe.entity_id AND mcped_selling_price.attribute_id = '".PRICE."'";

$customer_group_query = "";
for($cg = 19 ; $cg <= 29 ;$cg++) {
  $customer_group_query .= " LEFT JOIN mage_catalog_product_entity_group_price AS mcpegp_".$cg." ON mcpegp_".$cg.".entity_id = mcpe.entity_id AND (mcpegp_".$cg.".customer_group_id = '".$cg."' AND mcpegp_".$cg.".all_groups = 0)";
}

$table = $table.$customer_group_query;
//mcpev_grossprice
//mcpev_netprice

//mcpip
// Table's primary key
$primaryKey = 'DISTINCT mcpe.entity_id';

$extra_where = "mcpe.type_id = 'simple'";

// Extra Where
if($_POST['categories']) {
   //$selected_categories = implode(",",$_REQUEST['categories']);
  $extra_where .= " AND mccp.category_id IN (".$_POST['categories'].")";
} 


if(isset(($_POST['hdn_filters']))) {
  switch($_POST['hdn_filters']) {
    case "1":
      $extra_where .= " AND CAST((mcped.value) AS DECIMAL (10 , ".$scale.")) = 0";  
    break;
    
    case "2":
      $extra_where .= " AND CAST((mcped_selling_price.value) AS DECIMAL (10 , ".$scale.")) = 0";  
    break;
    
    case "3":
      $extra_where .= " AND CAST((mcpegp_19.value) AS DECIMAL (10 , ".$scale.")) = 0";  
    break;

    case "4":
      $extra_where .= " AND CAST((mcpegp_20.value) AS DECIMAL (10 , ".$scale.")) = 0";  
    break;

    case "5":
      $extra_where .= " AND CAST((mcpegp_21.value) AS DECIMAL (10 , ".$scale.")) = 0";  
    break;

    case "6":
      $extra_where .= " AND CAST((mcpegp_22.value) AS DECIMAL (10 , ".$scale.")) = 0";  
    break;

    case "7":
      $extra_where .= " AND CAST((mcpegp_23.value) AS DECIMAL (10 , ".$scale.")) = 0";  
    break;

    case "8":
      $extra_where .= " AND CAST((mcpegp_24.value) AS DECIMAL (10 , ".$scale.")) = 0";  
    break;

    case "9":
      $extra_where .= " AND CAST((mcpegp_25.value) AS DECIMAL (10 , ".$scale.")) = 0";  
    break;

    case "10":
      $extra_where .= " AND CAST((mcpegp_26.value) AS DECIMAL (10 , ".$scale.")) = 0";  
    break;

    case "11":
      $extra_where .= " AND CAST((mcpegp_27.value) AS DECIMAL (10 , ".$scale.")) = 0";  
    break;

    case "12":
      $extra_where .= " AND CAST((mcpegp_28.value) AS DECIMAL (10 , ".$scale.")) = 0";  
    break;

    case "13":
      $extra_where .= " AND CAST((mcpegp_29.value) AS DECIMAL (10 , ".$scale.")) = 0";  
    break;

  }  
}


//$extra_where = "mccp.category_id IN (2004,2114,2122,2407)";

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
  array( 'db' => 'DISTINCT mcpe.entity_id AS product_id', 'dt' => 0),
  array( 'db' => 'mcpev_productname.value AS product_name', 'dt' => 1),
  array( 'db' => 'mcpe.sku AS sku', 'dt' => 2),
  array( 'db' => 'mcpev.value AS ean', 'dt' => 3),
  array( 'db' => 'meaov.value AS brand',  'dt' => 4),
  array( 'db' => '(CASE WHEN mcpet_af.value IS NULL THEN mcpev_afw.value ELSE mcpet_af.value END) AS webshop_afwijkenidealeverpakking',  'dt' => 5),
  array( 'db' => '(CASE WHEN mcpet.value IS NULL THEN mcpev_ideal.value ELSE mcpet.value END) AS webshop_idealeverpakking',  'dt' => 6),
  array( 'db' => 'CAST((mcped.value) AS DECIMAL (10 , '.$scale.' )) AS gyzs_buying_price',  'dt' => 7),
  array( 'db' => 'CAST((mcped_selling_price.value) AS DECIMAL (10 , '.$scale.' )) AS gyzs_selling_price',  'dt' => 8),

  array( 'db' => 'mcpegp_19.value AS mcpegp_19', 'dt' => 9),
  array( 'db' => 'mcpegp_20.value AS mcpegp_20', 'dt' => 10),
  array( 'db' => 'mcpegp_21.value AS mcpegp_21', 'dt' => 11),
  array( 'db' => 'mcpegp_22.value AS mcpegp_22', 'dt' => 12),
  array( 'db' => 'mcpegp_23.value AS mcpegp_23', 'dt' => 13),
  array( 'db' => 'mcpegp_24.value AS mcpegp_24', 'dt' => 14),
  array( 'db' => 'mcpegp_25.value AS mcpegp_25', 'dt' => 15),
  array( 'db' => 'mcpegp_26.value AS mcpegp_26', 'dt' => 16),
  array( 'db' => 'mcpegp_27.value AS mcpegp_27', 'dt' => 17),
  array( 'db' => 'mcpegp_28.value AS mcpegp_28', 'dt' => 18),
  array( 'db' => 'mcpegp_29.value AS mcpegp_29', 'dt' => 19)
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