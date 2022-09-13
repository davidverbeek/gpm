<?php
require_once("../../app/Mage.php");
include "config/config.php";
ini_set('memory_limit', '6G');
ini_set('max_execution_time', 300); // 5 minutes
umask(0);
Mage::app();
/* $txt = 'compareDozonCostprijs';

Mage::app("default")->setCurrentStore(3);

Mage::log("Update ".$txt." Started.", NULL, 'update-'.$txt.'.log');

$storeId = Mage::app()->getStore()->getStoreId(); */
// Read Dozon xml
$xmlfile = "prijslijst/301141200000BM_dozon.xml";
$reader = new XMLReader();
$reader->open($xmlfile);
$dozon_xml_price_data = array();
$all_col_data=array();
//libxml_use_internal_errors(TRUE);

while ($reader->read()) { 
  if($reader->nodeType == XMLReader::ELEMENT && $reader->name == 'Grouping') { 
   $grouping_data = new SimpleXMLElement($reader->readOuterXml());
   
    foreach($grouping_data->TradeItemLine AS $trade_item_key=>$trade_item_data) {
        $trade_item_data = convertoarray($trade_item_data);
        
        $xml_sku = trim($trade_item_data["TradeItemIdentification"]["SuppliersTradeItemIdentification"]);
        $dozon_xml_price_data[$xml_sku]["sku"] = $xml_sku;

        $xml_ean = trim($trade_item_data["TradeItemIdentification"]["GTIN"]);
        $dozon_xml_price_data[$xml_sku]["ean"] = $xml_ean;

        $MinimumOrderQuantity = $trade_item_data["OrderConditions"]["MinimumOrderQuantity"];
        $NumberOfUnitsInPriceBasis = $trade_item_data["PriceInformation"]["PriceBase"]["NumberOfUnitsInPriceBasis"];
        $NetPrice = $trade_item_data["PriceInformation"]["NetPrice"];

        if($MinimumOrderQuantity == 1) {
            $dozon_xml_price_data[$xml_sku]["afwijkenidealeverpakking"] = 1;
            $dozon_xml_price_data[$xml_sku]["min_order_qty"] = 1;
        } else {
            $dozon_xml_price_data[$xml_sku]["afwijkenidealeverpakking"] = 0;
            $dozon_xml_price_data[$xml_sku]["min_order_qty"] = $MinimumOrderQuantity;
        }

        $cost = round((($NetPrice / $NumberOfUnitsInPriceBasis) * $MinimumOrderQuantity),2);
        $dozon_xml_price_data[$xml_sku]["bp_min_order_qty"] = $cost;
        $dozon_xml_price_data[$xml_sku]["bp_per_piece"] = $cost/$dozon_xml_price_data[$xml_sku]["min_order_qty"];
    }
 }

}
const PMCHUNK = 1000;
$supplier_id='3';
$chunk_xlsx_data = array_chunk( $dozon_xml_price_data, PMCHUNK);
if(count($chunk_xlsx_data)>0 && count($chunk_xlsx_data[0]) > 1) {
    foreach($chunk_xlsx_data as $chunked_idx=>$chunked_xlsx_values) {
        $all_col_data = array();
        foreach($chunked_xlsx_values as $xl_row=>$data) {
            $all_col_data[] = "('".$data['sku']."','".$data['ean']."' ,'".$supplier_id."','".$data['bp_per_piece']."','".$data['bp_min_order_qty']."', '".$data['min_order_qty']."','".$data['afwijkenidealeverpakking']."', '".NOW()."', '".NOW()."')";
        }
        $sql_2 = "INSERT INTO all_supplier_products(sku,ean,supplier_id,bp_per_piece,bp_min_order_qty, min_order_qty, afwijkenidealeverpakking, created_at, modified_at) VALUES ";
        $sql_2 .= implode(',', $all_col_data);
        $modified_at = date('Y-m-d h:i:s');
        $sql_2 .= " ON DUPLICATE KEY UPDATE sku = VALUES(sku), ean=VALUES(ean),supplier_id = VALUES(supplier_id),bp_per_piece = VALUES(bp_per_piece),bp_min_order_qty = VALUES(bp_min_order_qty), min_order_qty = VALUES(min_order_qty), afwijkenidealeverpakking = VALUES(afwijkenidealeverpakking), modified_at = VALUES(modified_at)";
    
        //echo $sql_2;exit;
        if ($result_2 = $conn->query($sql_2)) {
            $status = 'success';
        } else {
            echo mysqli_error();exit;
        }

       
    }
}

if($status == 'success')
echo $response_data['msg'] = "Excel Data is saved successfully.";

function convertoarray($normalizedproperties) {
    $json = json_encode($normalizedproperties);
    $get_data = json_decode($json,TRUE);
    return $get_data;
}