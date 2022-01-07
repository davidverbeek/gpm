<?php
include "../config/config.php";
include "../define/constants.php";
session_start();


exportwebData();

function exportwebData() {

  $get_all_product_categories = getGyzsProductCategories();

  global $conn, $document_root_path;

  $sql = getChkAllSql(); 
  $result = $conn->query($sql);
  $allData = $result->fetch_all(MYSQLI_ASSOC);
  
   if(count($allData)) {
    $get_data = array();
    foreach($allData as $key=>$data) {
      $get_data[$data["sku"]]["product_id"] = $data["product_id"];
      $get_data[$data["sku"]]["product_name"] = mb_convert_encoding($data["product_name"], 'UTF-8', 'UTF-8');
      $get_data[$data["sku"]]["sku"] = $data["sku"];
      $get_data[$data["sku"]]["ean"] = $data["ean"];
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

      $get_data[$data["sku"]]["webshop_afwijkenidealeverpakking"] = $data["webshop_afwijkenidealeverpakking"];
      $get_data[$data["sku"]]["webshop_idealeverpakking"] = $data["webshop_idealeverpakking"];

      $get_data[$data["sku"]]["gyzs_buying_price"] = $data["gyzs_buying_price"];
      $get_data[$data["sku"]]["gyzs_selling_price"] = $data["gyzs_selling_price"];
      

      //Debters
      for($hc=19;$hc<=29;$hc++) { 
        $deb_price = $data["mcpegp_".$hc];
        //$get_data[$data["sku"]][$h_cust_group] = $data[$deb_selling_price_col];
        $get_data[$data["sku"]]["mcpegp_".$hc] = $deb_price;
      }
      //Debters

   
    }
  } 

  $header[0] = array("Product Id","Naam","Artikelnummer (Artikel)","Ean","Merk","Categorie 1","Categorie 2","Categorie 3","Categorie 4","Afwijkenidealeverpakking (Afw.Ideal.verp)", "Idealeverpakking (Ideal.verp)","Inkoopprijs (Inkpr per piece)","Nieuwe Verkoopprijs (Niewe Vkpr per piece)", "4027100","4027101","4027102","4027103","4027104","4027105","4027106","4027107","4027108","4027109","4027110");
  
  $_SESSION['exported_data_webshop_prices'] = array_values($header+$get_data);
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