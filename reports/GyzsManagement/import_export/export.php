<?php
include "../config/config.php";
include "../define/constants.php";
session_start();


exportData();

function exportData() {

  $get_all_product_categories = getGyzsProductCategories();
  $locatie = getLocatie();

  global $conn, $document_root_path;

  $sql = getChkAllSql(); 
  $result = $conn->query($sql);
  $allData = $result->fetch_all(MYSQLI_ASSOC);
  
   if(count($allData)) {
    $get_data = array();
    foreach($allData as $key=>$data) {
      $get_data[$data["sku"]]["supplier_type"] = $data["supplier_type"];
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

      $get_data[$data["sku"]]["supplier_gross_price"] = $data["supplier_gross_price"];
      $get_data[$data["sku"]]["supplier_net_price"] = $data["supplier_buying_price"];
      $get_data[$data["sku"]]["idealeverpakking"] = $data["idealeverpakking"];
      $get_data[$data["sku"]]["afwijkenidealeverpakking"] = $data["afwijkenidealeverpakking"];


       if($data["afwijkenidealeverpakking"] === "0") {
         $buying_price_per_piece = roundValue($data["buying_price"] / $data["idealeverpakking"]);
         $selling_price_per_piece = roundValue($data["selling_price"] / $data["idealeverpakking"]);
        } else  {
         $buying_price_per_piece = $data["buying_price"];
         $selling_price_per_piece = $data["selling_price"];
        }


      $get_data[$data["sku"]]["buying_price"] = $data["buying_price"];
      $get_data[$data["sku"]]["buying_price_per_piece"] = $buying_price_per_piece;
      $get_data[$data["sku"]]["selling_price"] = $data["selling_price"];
      $get_data[$data["sku"]]["selling_price_per_piece"] = $selling_price_per_piece;

      //Debters
      for($hc=0;$hc<=10;$hc++) { 
        $h_cust_group = intval(4027100 + $hc);
        $deb_selling_price_col = "group_".$h_cust_group."_debter_selling_price";
        
        $deb_price = "";
        if($data["afwijkenidealeverpakking"] === "0") {
          $deb_price = roundValue($data[$deb_selling_price_col] / $data["idealeverpakking"]);
        } else {
          $deb_price = $data[$deb_selling_price_col];
        }

        //$get_data[$data["sku"]][$h_cust_group] = $data[$deb_selling_price_col];
        $get_data[$data["sku"]][$h_cust_group] = $deb_price;
      }
      //Debters

      $get_data[$data["sku"]]["percentage_increase"] = $data["percentage_increase"];

      $get_data[$data["sku"]]["avg_category"] = $data["avg_category"];
      $get_data[$data["sku"]]["avg_brand"] = $data["avg_brand"];
      $get_data[$data["sku"]]["avg_per_category_per_brand"] = $data["avg_per_category_per_brand"];
      $get_data[$data["sku"]]["locatie"] = $locatie[$data["product_id"]]["locatie"];

      $get_data[$data["sku"]]["artikelnummer"] = $locatie[$data["product_id"]]["gyzs_sku"];
      

    }
  } 

  $header[0] = array("Leverancier","Naam","Artikelnummer (Artikel)","Ean","Merk","Categorie 1","Categorie 2","Categorie 3","Categorie 4","Brutoprijs (Brutopr)","Leverancier Netto Prijs (Lev.Nettopr)","Idealeverpakking (Ideal.verp)","Afwijkenidealeverpakking (Afw.Ideal.verp)","Inkoopprijs (Inkpr)","Inkoopprijs (Inkpr per piece)", "Nieuwe Verkoopprijs (Niewe Vkpr)","Nieuwe Verkoopprijs (Niewe Vkpr per piece)", "4027100","4027101","4027102","4027103","4027104","4027105","4027106","4027107","4027108","4027109","4027110","Stijging","Cat Gem","Merk Gem","Cat Merk Gem","Locatie","Artikelnummer");
  
  $_SESSION['exported_data'] = array_values($header+$get_data);
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

function getLocatie() {
  global $conn;
  $sql = "SELECT mcpe.entity_id AS product_id, mcpev.value AS locatie, mcpet.value AS gyzssku 
          FROM mage_catalog_product_entity AS mcpe 
          LEFT JOIN mage_catalog_product_entity_varchar AS mcpev ON mcpev.entity_id = mcpe.entity_id AND mcpev.attribute_id = '".LOCATIE."'  
          LEFT JOIN mage_catalog_product_entity AS mcpet ON mcpet.entity_id = mcpe.entity_id AND mcpet.attribute_id = '".GYZSSKU."'";

  $locaties = array();
  if ($result = $conn->query($sql)) {
     while ($row = $result->fetch_assoc()) {
         $locaties[$row["product_id"]]["locatie"] = $row["locatie"];
         $locaties[$row["product_id"]]["gyzs_sku"] = $row["gyzssku"];
     } 
  }
  return $locaties;  
          
}