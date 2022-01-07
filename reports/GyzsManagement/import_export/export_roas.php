<?php
require_once("../../../app/Mage.php");
umask(0);
Mage::app();



include "../config/config.php";
include "../define/constants.php";
include "../lib/SimpleXLSXGen.php";


/* Get Google Actual roas starts */
$resource = Mage::getSingleton('core/resource');
$readConnection = $resource->getConnection('core_read');
$sql_google_actual_roas = "SELECT * FROM roas_google";
$google_roas_recs = $readConnection->fetchAll($sql_google_actual_roas);
$google_actual_roas = array();
foreach($google_roas_recs as $google_recs) {
  $google_actual_roas[strtolower($google_recs['sku'])]["kosten"] = $google_recs['kosten'];
  $google_actual_roas[strtolower($google_recs['sku'])]["actual_roas"] = $google_recs['actual_roas'];
}
/* Get Google Actual roas ends */


$type = $_REQUEST['type'];
$from = $_REQUEST['roasfrom'];
$to = $_REQUEST['roasto'];
$hdnexlbol = $settings_data['roas']['exclude_bol'];

exportData($type,$from,$to,$hdnexlbol,$google_actual_roas);

function exportData($type,$from,$to,$hdnexlbol,$google_actual_roas) {

  $get_all_product_categories = getGyzsProductCategories();

  $tbl_name = "";
  if($type == "roascurrent") {
    $tbl_name = "roascurrent";
  } else {
    $tbl_name = "roas";
  }

  if($hdnexlbol == 1) {
    $bolftext = "bol_excluded";
  } else {
    $bolftext = "all";
  }

 
  global $conn, $document_root_path;

  //$sql = "SELECT * FROM ".$tbl_name."";

  $sql = "SELECT 
    DISTINCT rc.product_id, rc.*, mcpev_productname.value AS product_name , meaov.value AS brand
    FROM
    roascurrent AS rc
        LEFT JOIN
    mage_catalog_product_entity_int AS mcpei ON mcpei.entity_id = rc.product_id
        AND mcpei.attribute_id = '".MERK."'
        LEFT JOIN
    mage_eav_attribute_option_value AS meaov ON meaov.option_id = mcpei.value
        LEFT JOIN
    mage_catalog_product_entity_varchar AS mcpev_productname ON mcpev_productname.entity_id = rc.product_id
        AND mcpev_productname.attribute_id = '".PRODUCTNAME."'";


  $get_data = array();
  $temp = 0;
  if($result = $conn->query($sql)) {

    
    while ($line_row = $result->fetch_assoc()) {

      $product_category_id = array();
      $averages = array();


      $get_data[$temp]["sku"] = $line_row["sku"];
      $get_data[$temp]["product_name"] = mb_convert_encoding($line_row["product_name"], 'UTF-8', 'UTF-8');
      $get_data[$temp]["brand"] = mb_convert_encoding($line_row["brand"], 'UTF-8', 'UTF-8');

      if(isset($get_all_product_categories[$line_row["product_id"]]["1"])) {
        $get_data[$temp]["category_1"] = mb_convert_encoding($get_all_product_categories[$line_row["product_id"]]["1"]["cat_name"], 'UTF-8', 'UTF-8');
      } else {
        $get_data[$temp]["category_1"]  = "";
      }
      
      if(isset($get_all_product_categories[$line_row["product_id"]]["2"])) {
        $get_data[$temp]["category_2"] = mb_convert_encoding($get_all_product_categories[$line_row["product_id"]]["2"]["cat_name"], 'UTF-8', 'UTF-8');
      } else {
         $get_data[$temp]["category_2"]  = "";
      }

      if(isset($get_all_product_categories[$line_row["product_id"]]["3"])) {
        $get_data[$temp]["category_3"] = mb_convert_encoding($get_all_product_categories[$line_row["product_id"]]["3"]["cat_name"], 'UTF-8', 'UTF-8');
      } else {
        $get_data[$temp]["category_3"]  = "";
      }

      if(isset($get_all_product_categories[$line_row["product_id"]]["4"])) {
        $get_data[$temp]["category_4"] = mb_convert_encoding($get_all_product_categories[$line_row["product_id"]]["4"]["cat_name"], 'UTF-8', 'UTF-8');
      } else {
        $get_data[$temp]["category_4"]  = "";
      }
      
      


      $get_data[$temp]["carrier_level"] = $line_row["carrier_level"];
      $get_data[$temp]["total_quantity"] = $line_row["total_quantity"];
      $get_data[$temp]["total_orders"] = $line_row["total_orders"];
      $get_data[$temp]["total_orders_bol"] = $line_row["total_orders_bol"];
      $get_data[$temp]["total_quantity_bol"] = $line_row["total_quantity_bol"];


      $get_data[$temp]["return_general"] = $line_row["return_general"];
      $get_data[$temp]["return_bol"] = $line_row["return_bol"];
      $get_data[$temp]["return_nobol"] = $line_row["return_nobol"];
      $get_data[$temp]["return_order_general"] = $line_row["return_order_general"];
      $get_data[$temp]["return_order_bol"] = $line_row["return_order_bol"];
      $get_data[$temp]["return_order_nobol"] = $line_row["return_order_nobol"];
      $get_data[$temp]["parent_product_factor"] = $line_row["parent_product_factor"];
      $get_data[$temp]["parent_absolute_margin"] = $line_row["parent_absolute_margin"];
      $get_data[$temp]["parent_return_margin"] = $line_row["parent_return_margin"];
      $get_data[$temp]["total_parent_margin"] = $line_row["total_parent_margin"];
      $get_data[$temp]["average_order_per_month"] = $line_row["average_order_per_month"];
      $get_data[$temp]["other_absolute_margin"] = $line_row["other_absolute_margin"];
      $get_data[$temp]["total_absolute_margin"] = $line_row["total_absolute_margin"];
      $get_data[$temp]["shipment_revenue"] = $line_row["shipment_revenue"];
      $get_data[$temp]["shipment_cost"] = $line_row["shipment_cost"];
      $get_data[$temp]["shipment_diff"] = $line_row["shipment_diff"];
      $get_data[$temp]["employee_cost"] = $line_row["employee_cost"];
      $get_data[$temp]["margin_after_deductions"] = $line_row["margin_after_deductions"];
      $get_data[$temp]["total_selling_price"] = $line_row["total_selling_price"];
      $get_data[$temp]["payment_other_company_cost"] = $line_row["payment_other_company_cost"];
      $get_data[$temp]["burning_margin"] = $line_row["burning_margin"];
      $get_data[$temp]["roas_target"] = $line_row["roas_target"];

      $get_data[$temp]["google_kosten"] = $line_row["google_kosten"];
      $get_data[$temp]["google_roas"] = $line_row["google_roas"];
      
      $get_data[$temp]["performance"] = $line_row["performance"];
      

      $get_data[$temp]["avg_per_cat"] = $line_row["avg_per_cat"];
      $get_data[$temp]["avg_per_cat_per_brand"] = $line_row["avg_per_cat_per_brand"];
      $get_data[$temp]["roas_per_cat_per_brand"] = $line_row["roas_per_cat_per_brand"];

      $get_data[$temp]["end_roas"] = $line_row["end_roas"];


      $temp++;
    }

    $euro_sign = "\xE2\x82\xAc";


    

    $header_row = array("sku","product_name","brand","category_1","category_2","category_3","category_4","carrier_level","total_quantity","total_orders","total_orders_bol","total_quantity_bol","return_general (% Qty)","return_bol (% Qty)","return_nobol (% Qty)", "return_order_general (% Order)","return_order_bol (% Order)","return_order_nobol (% Order)", "parent_product_factor","parent_absolute_margin (".$euro_sign.")","parent_return_margin (".$euro_sign.")","total_parent_margin (".$euro_sign.")","average_order_per_month","other_absolute_margin (".$euro_sign.")","total_absolute_margin (".$euro_sign.")","shipment_revenue (".$euro_sign.")","shipment_cost (".$euro_sign.")","shipment_diff (".$euro_sign.")","employee_cost (".$euro_sign.")","margin_after_deductions (".$euro_sign.")","total_selling_price (".$euro_sign.")","payment_other_company_cost (%)","burning_margin (%)","roas_target (%)","google_kosten (".$euro_sign.")","google_roas (%)","performance","avg_per_cat","avg_per_cat_per_brand", "roas_per_cat_per_brand","end_roas (%)");
    array_unshift($get_data, $header_row);

    $fname = "".$tbl_name."_".$from."_".$to."_".$bolftext."_".time().".xlsx";
    //$fpath = "".$document_root_path."/file/export/".$fname."";
    //echo "<pre>";
    //print_r($get_data);
    //exit;

    SimpleXLSXGen::fromArray($get_data)->downloadAs($fname);
    //SimpleXLSXGen::fromArray($get_data)->saveAs($fpath);
  }  
}



function getGyzsProductCategories() {
  
  global $conn;
  
  $sql = "SELECT 
            mccp.category_id,
            mccp.product_id,
            mccev.value,
            mcce.level
          FROM 
            mage_catalog_category_product AS mccp 
          LEFT JOIN
            mage_catalog_category_entity_varchar AS mccev ON mccev.entity_id = mccp.category_id AND mccev.attribute_id = '".CATEGORYNAME_ATTRIBUTE_ID."' AND mccev.store_id = '0'
          LEFT JOIN
            mage_catalog_category_entity AS mcce ON mcce.entity_id = mccp.category_id";

  $products_with_cats = array();
  if ($result = $conn->query($sql)) {
     while ($row = $result->fetch_assoc()) {
         $products_with_cats[$row["product_id"]][$row["level"]]['cat_name'] = $row["value"];
         //$products_with_cats[$row["product_id"]][$row["level"]]['cat_id'] = $row["category_id"];
     } 
  }
  return $products_with_cats;  
}


