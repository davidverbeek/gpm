<?php
include "../config/config.php";
include "../define/constants.php";
session_start();
exportData();

function exportData() {

  $get_all_product_categories = getGyzsProductCategories();

  global $conn, $document_root_path;

  $sql = getChkAllSql(); 
  $result = $conn->query($sql);
  $allData = $result->fetch_all(MYSQLI_ASSOC);
  
   if(count($allData)) {
    $get_data = array();

    $total_revenue = array();
    $total_abs_margin = array();
    $total_buying_price = array();
    $total_refund = array();

    foreach($allData as $key=>$data) {
      $get_data[$data["sku"]]["reportdate"] = $data["reportdate"];
      $get_data[$data["sku"]]["Leverancier"] = $data["supplier_type"];
      $get_data[$data["sku"]]["sku"] = $data["sku"];
      $get_data[$data["sku"]]["carrier_level"] = $data["carrier_level"];
      $get_data[$data["sku"]]["name"] = mb_convert_encoding($data["name"], 'UTF-8', 'UTF-8');
      $get_data[$data["sku"]]["brand"] = mb_convert_encoding($data["brand"], 'UTF-8', 'UTF-8');
      
      if(isset($get_all_product_categories[$data["product_id"]]["1"])) {
        $get_data[$data["sku"]]["category_level_1"] = mb_convert_encoding($get_all_product_categories[$data["product_id"]]["1"], 'UTF-8', 'UTF-8');
      } else {
        $get_data[$data["sku"]]["category_level_1"] = "";
      }
      
      if(isset($get_all_product_categories[$data["product_id"]]["2"])) {
        $get_data[$data["sku"]]["category_level_2"] = mb_convert_encoding($get_all_product_categories[$data["product_id"]]["2"], 'UTF-8', 'UTF-8');
      } else {
         $get_data[$data["sku"]]["category_level_2"] = "";
      }

      if(isset($get_all_product_categories[$data["product_id"]]["3"])) {
        $get_data[$data["sku"]]["category_level_3"] = mb_convert_encoding($get_all_product_categories[$data["product_id"]]["3"], 'UTF-8', 'UTF-8');
      } else {
        $get_data[$data["sku"]]["category_level_3"] = "";
      }

      if(isset($get_all_product_categories[$data["product_id"]]["4"])) {
        $get_data[$data["sku"]]["category_level_4"] = mb_convert_encoding($get_all_product_categories[$data["product_id"]]["4"], 'UTF-8', 'UTF-8');
      } else {
        $get_data[$data["sku"]]["category_level_4"] = "";
      }
      $get_data[$data["sku"]]["sku_total_quantity_sold_365"] = $data["sku_total_quantity_sold_365"];
      $get_data[$data["sku"]]["sku_total_quantity_sold"] = $data["sku_total_quantity_sold"];
      $get_data[$data["sku"]]["sku_total_price_excl_tax"] = $data["sku_total_price_excl_tax"];

      $total_revenue[] = $data["sku_total_price_excl_tax"];
      $get_data[$data["sku"]]["sku_vericale_som"] = $data["sku_vericale_som"];
      $get_data[$data["sku"]]["vericale_som_percentage"] = $data["vericale_som_percentage"];


      $get_data[$data["sku"]]["sku_bp_excl_tax"] = $data["sku_bp_excl_tax"];

      $total_buying_price[] = $data["sku_bp_excl_tax"];
      $get_data[$data["sku"]]["sku_sp_excl_tax"] = $data["sku_sp_excl_tax"];
      $get_data[$data["sku"]]["sku_abs_margin"] = $data["sku_abs_margin"];

      $total_abs_margin[] = $data["sku_abs_margin"];
      $get_data[$data["sku"]]["sku_margin_bp"] = $data["sku_margin_bp"];
      $get_data[$data["sku"]]["sku_margin_sp"] = $data["sku_margin_sp"];

      $get_data[$data["sku"]]["sku_vericale_som_bp"] = $data["sku_vericale_som_bp"];
      $get_data[$data["sku"]]["sku_vericale_som_bp_percentage"] = $data["sku_vericale_som_bp_percentage"];
      $get_data[$data["sku"]]["sku_refund_qty"] = $data["sku_refund_qty"];
      $get_data[$data["sku"]]["sku_refund_revenue_amount"] = $data["sku_refund_revenue_amount"];

      $total_refund[] = $data["sku_refund_revenue_amount"];
      $get_data[$data["sku"]]["sku_refund_bp_amount"] = $data["sku_refund_bp_amount"];
      $get_data[$data["sku"]]["sku_vericale_som_abs"] = $data["sku_vericale_som_abs"];
      $get_data[$data["sku"]]["sku_vericale_som_abs_percentage"] = $data["sku_vericale_som_abs_percentage"];
    }
  } 

  $header[0] = array("Revenue Date Range", "Leverancier","Artikelnummer (Artikel)","Carrier Level","Naam","Merk","Categorie 1","Categorie 2","Categorie 3","Categorie 4","Afzet ({$settings_data['roas']['sku_afzet_in_days']})","Afzet","Omzet","Vericale som","Vericale som (%)","Inkoopprijs (Inkpr)", "Verkoopprijs (Vkpr)","Absolute Margin", "Profit margin BP %","Profit margin SP %","Vericale som (BP)","Vericale som (BP %)","Refund Quantities","Refund Amount","Refund Amount (BP)","Abs Mar. Vericale som","Abs Mar. Vericale som %");
  $profit_margin_sp = round(array_sum($total_abs_margin) / array_sum($total_revenue),2);

  $footer[count($get_data)+1] = array("Total","-","-","-","-","-","-","-","-","-",number_format(array_sum($total_revenue), 2, ',', '.'),"-","-","-", number_format(array_sum($total_abs_margin), 2, ',', '.'),"-",$profit_margin_sp, number_format(array_sum($total_buying_price), 2, ',', '.'),"-","-", number_format(array_sum($total_refund), 2, ',', '.'),"-","-","-");
  
  $_SESSION['exported_revenue_data'] = array_values($header+$get_data+$footer);
  //echo "<pre>";
  //print_r($_SESSION['exported_revenue_data']);
  //exit;
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
         $products_with_cats[$row["product_id"]][$row["level"]] = $row["value"];
     } 
  }
  return $products_with_cats;  
}

function getChkAllSql() {
  global $document_root_url;

  $get_check_all_file = $document_root_url."/getquery.txt";
  $get_data = json_decode(file_get_contents($get_check_all_file),true);
  $query = $get_data["query"];
  $query_binding = $get_data["query_bindings"];


  $query_binding_search = array();
  $query_binding_replace = array();

  if(count($query_binding)) {
    foreach($query_binding as $b_k=>$b_v) {      
      $query_binding_search[] = "/\B".$b_v['key']."\b/";
      $query_binding_replace[] = "'".$b_v["val"]."'";
    }
  }

  return preg_replace($query_binding_search, $query_binding_replace, $query);

}

function roundValue($val) {
  global $scale;
  return round($val,$scale);
}