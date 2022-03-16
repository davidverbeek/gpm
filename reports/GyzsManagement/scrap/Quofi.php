<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('memory_limit', '-1');
ini_set('max_execution_time', '-1');

session_start();

require_once("../../../app/Mage.php");
//require_once("./scrap/competitors.php");
//require_once("./scrap/scrap.php");

umask(0);
Mage::app();

include "../config/config.php";
include "../define/constants.php";
include "../layout/header.php";

// Get Updated records categories
$sql = "SELECT DISTINCT
    mcpe.entity_id AS product_id,
    mcpe.sku AS sku,
    mcpev.value AS ean,
    mcpev_productname.value AS product_name,
    pmd.idealeverpakking AS idealeverpakking,
    pmd.afwijkenidealeverpakking AS afwijkenidealeverpakking,
    CAST(pmd.buying_price AS DECIMAL (10 , 4 )) AS buying_price,
    pmd.selling_price AS selling_price
FROM
    mage_catalog_product_entity AS mcpe
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
        AND mcped_selling_price.attribute_id = '75' ";
		
$offset = $_GET["offset"];
$limit = $_GET["limit"];

if($offset!="" && $limit!=""){
	$sql .= " limit $offset, $limit";
}

$result = $conn->query($sql);
$records = $result->fetch_all(MYSQLI_ASSOC);

$all_records = array();
$all_ean = array();
$all_sku = array();

foreach($records as $record) {
	$all_ean[$record->ean] = $record->product_id;
	$all_sku[$record->sku] = $record->product_id;
    //$all_records[] = $record;
}


$sql = "INSERT INTO gyzscompetitors_data (product_id,product_data) VALUES ";
$file = fopen($_GET['file_path'], 'r');
$i = 0;
$sqldata = "";
while (($data = fgetcsv($file)) !== FALSE) {
  if($i>0){
	echo $ean = $data[0];
	echo "<br>";
	if(isset($all_ean[$ean])){
		$ean = $data[0];
		$price_data = array("Quofi"=>$data[1]);
		$product_id = $all_ean[$ean];
		$sqldata = $sqldata."('".$product_id."','".$price_data."'),";	
	}
  }
  $i++;
}
fclose($file);


//update gyzscompetitors_data set product_data = JSON_SET(product_data, "$.Bouwsales", "45") where id = 2;

if($sqldata != "") {
	echo $sql.rtrim($sqldata,",").";";
} else {
	echo "No product of Quofi matches our system.";
}


?>