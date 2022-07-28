<?php

include "../config/config.php";
include "../define/constants.php";
include "../lib/SimpleXLSX.php";
require_once("../../../app/Mage.php");
umask(0);
Mage::app();

Mage::app("default")->setCurrentStore(Mage_Core_Model_App :: ADMIN_STORE_ID);

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
ini_set('max_execution_time', 0);
ini_set('memory_limit','3G');

$response_data = array();

$brands = array(); 
$selected_cats = $_POST['selected_cats'];
$type = $_POST['type'];

switch ($type) {
case "category_brands":
  $cat_que = "";
  if($selected_cats != "") {
    $cat_que = " WHERE mccp.category_id IN (".$selected_cats.")";
  }
  $sql = "SELECT DISTINCT
  mcpe.entity_id AS product_id,
  meaov.value AS brand
  FROM
  mage_catalog_product_entity AS mcpe
  INNER JOIN
  mage_catalog_category_product AS mccp ON mccp.product_id = mcpe.entity_id
  INNER JOIN
  price_management_data AS pmd ON pmd.product_id = mcpe.entity_id
  LEFT JOIN
  mage_catalog_product_entity_int AS mcpei ON mcpei.entity_id = pmd.product_id
  AND mcpei.attribute_id = '".MERK."'
  LEFT JOIN
  mage_eav_attribute_option_value AS meaov ON meaov.option_id = mcpei.value
  ".$cat_que."
  ORDER BY meaov.value ASC";

  if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $brand = trim(mb_convert_encoding($row['brand'], 'UTF-8', 'UTF-8'));
        if($brand !== NULL && $brand != "") {
        $brands[$brand] = $brand;
      }
    }
  }
  $response_data['msg'] = $brands;
  break;
  case "save_rule":
    $customerGroup = $_POST['customer_group'];
    $catIdNew = $_POST['cat_id_new'];
    $catId_new_arr = explode(',', $catIdNew);

    $before_update_cats = $_POST['on_load_categories'];

    $old_cats_arr = explode(',', $before_update_cats);
    $check_old_is_removed = implode(',', array_diff($old_cats_arr, $catId_new_arr));
    if($check_old_is_removed) {// yes reset prices
      $sql_reset_products = "SELECT product_id FROM mage_catalog_category_product WHERE category_id IN ($check_old_is_removed)";
      $result = $conn->query($sql_reset_products);
      $product_ids = "";
      while($row = $result->fetch_assoc()) {
        $product_ids .= ','.$row['product_id'];
      }
      $product_ids = ltrim($product_ids, ',');

      $sql = "SELECT customer_group_name, product_ids  FROM price_management_customer_groups JOIN price_management_debter_categories ON price_management_debter_categories.customer_group = price_management_customer_groups.magento_id AND  price_management_customer_groups.magento_id=$customerGroup";
      $result = $conn->query($sql);
      $row = $result->fetch_assoc();
      $debter_name = $row['customer_group_name'];
      $debter_col_1 = "group_".$debter_name."_debter_selling_price";
      $debter_col_2 = "group_".$debter_name."_margin_on_buying_price";
      $debter_col_3 = "group_".$debter_name."_margin_on_selling_price";
      $debter_col_4 = "group_".$debter_name."_discount_on_grossprice_b_on_deb_selling_price";
      $sql_reset_price = "UPDATE price_management_data SET $debter_col_1=0,$debter_col_2=0,$debter_col_3=0,$debter_col_4=0 WHERE product_id IN (".$product_ids.")";
      $msg_error = "successfull update";
      if(!$conn->query($sql_reset_price)) {
        $msg_error =  $conn->error;
      }
    }
    
    $product_ids = '';
    if($catIdNew) {
      $sql_product = "SELECT product_id FROM mage_catalog_category_product WHERE category_id IN ($catIdNew)";
      $result = $conn->query($sql_product);
      $product_ids = "";
      while($row = $result->fetch_assoc()) {
        $product_ids .= ','.$row['product_id'];
      }
      $product_ids = ltrim($product_ids, ',');
      $product_ids = array_unique(explode(',', $product_ids));
      $product_ids = implode(',', $product_ids);
    }

    $sql = "INSERT INTO price_management_debter_categories(category_ids,product_ids,customer_group,created_at,updated_at) VALUES ";
    $all_col_data = "('".$catIdNew."', '".$product_ids."','".$customerGroup."',  '".NOW()."', '".NOW()."')";
    $sql .= $all_col_data;
    $updated_at = date('Y-m-d h:i:s');
    $sql .= " ON DUPLICATE KEY UPDATE category_ids = VALUES(category_ids), customer_group = VALUES(customer_group), product_ids = VALUES(product_ids), updated_at = VALUES(updated_at)";
  
    if($conn->query($sql)) {
      $response_data['msg'] = "Data is saved successfully!";
    } else {
      $response_data['msg'] = "Error in debter rule insertion:- ".mysqli_error($conn);   
    }
    break;
    case "get_categories":
      $by_group = $_POST['customer_group'];
      $sql = "select * from price_management_debter_categories where customer_group={$by_group} limit 1";
      if ($result = $conn->query($sql)){
        $row = $result->fetch_assoc();
        $response_data['msg'] = $row['category_ids'];
      } else {
        $response_data['msg'] = "Error in debter rule selection:- ".mysqli_error($conn);
      }
    break;
    case "copy_categories":
      //read request
      $from_group_id = $_POST['source_group_id'];
      $to_group_id = $_POST['destination_group_id'];

      $copied = "";
      $sql = "SELECT category_ids, product_ids FROM price_management_debter_categories WHERE customer_group={$from_group_id}";
        if ($result = $conn->query($sql)) {
          $row = $result->fetch_assoc();
          $copied = $row['category_ids'];
        }

      if(resetPrice($to_group_id, $copied)) {
        $sql = "SELECT category_ids, product_ids FROM price_management_debter_categories WHERE customer_group={$from_group_id}";
        if ($result = $conn->query($sql)) { 
          $row = $result->fetch_assoc();
          $copied = $row['category_ids'];
          $copied_products = $row['product_ids'];

          $sql_2 = "INSERT INTO price_management_debter_categories(category_ids,product_ids,customer_group,created_at,updated_at) VALUES ";
          $all_col_data = "('".$copied."','".$copied_products."' ,'".$to_group_id."','".NOW()."', '".NOW()."')";
      
          $sql_2 .= $all_col_data;
          $updated_at = date('Y-m-d h:i:s');
          $sql_2 .= " ON DUPLICATE KEY UPDATE category_ids = VALUES(category_ids), product_ids=VALUES(product_ids),customer_group = VALUES(customer_group),updated_at = VALUES(updated_at)";
          
          if ($result_2 = $conn->query($sql_2)) {
            $response_data['msg'] = "Data is copied and prices are reset successfully.";
          } 
        } else {
          $response_data['msg'] = "Error in debter copy-rule selection:-".mysqli_error($conn);
        }
    } else {
      $response_data['msg'] = "Both debters are already same.";
    }
    break;
    case "multiple_group_query":
      if ($_POST['customer_group']) {
        $multiple_groups = $_POST['customer_group'];
        $sql = "SELECT * FROM price_management_customer_groups JOIN price_management_debter_categories ON price_management_debter_categories.customer_group = price_management_customer_groups.magento_id WHERE price_management_customer_groups.customer_group_name IN ($multiple_groups)";
        
        if ($result = $conn->query($sql)) {
          $join_categories = '';
          while($row = $result->fetch_assoc()) {
            $join_categories .= ','.$row['category_ids'];
          }
          $all_cats_arr = array_filter(explode(',', $join_categories));
          $response_data['msg'] = implode(',', $all_cats_arr);
        } else {
          $response_data['msg'] = "Error in debter rule join:- ".mysqli_error($conn);
        } 
      } else {
        $response_data['msg'] =  '';
      }
      break;
}
echo json_encode($response_data); 

function resetPrice($to_group_id, $new_cats) {
  global $conn;
  // get products of this group
  $sql = "SELECT customer_group_name, product_ids, category_ids FROM price_management_customer_groups JOIN price_management_debter_categories ON price_management_debter_categories.customer_group = price_management_customer_groups.magento_id AND  price_management_customer_groups.magento_id=$to_group_id";
  $result = $conn->query($sql);
  $row = $result->fetch_assoc();
  $before_update_cats = $row['category_ids'];
  $check_old_is_removed = array();
  $catId_new_arr = explode(',', $new_cats);
  $old_cats_arr = explode(',', $before_update_cats);
  sort($catId_new_arr);
  sort($old_cats_arr);
  if($before_update_cats) {
    $check_old_is_removed = array_diff($old_cats_arr, $catId_new_arr);
  } else {
    $old_cats_arr =  $before_update_cats;
  }
  
  if((count($check_old_is_removed)) > 0) { // yes reset prices

    $unassigned_cats = implode(',', $check_old_is_removed);

    $sql = "SELECT DISTINCT mcpe.entity_id AS product_ids FROM mage_catalog_product_entity AS mcpe
    INNER JOIN mage_catalog_category_product AS mccp ON mccp.product_id = mcpe.entity_id
    INNER JOIN price_management_data AS pmd ON pmd.product_id = mcpe.entity_id
    WHERE mccp.category_id IN (".$unassigned_cats.")"; //echo $sql;exit;

    $result = $conn->query($sql);$product_ids=array();
    while ($row_products = $result->fetch_assoc()) {
      $product_ids[] = $row_products['product_ids'];
    }
    $unassign_products = implode(',', $product_ids);

    $debter_name = $row['customer_group_name'];
    $debter_col_1 = "group_".$debter_name."_debter_selling_price";
    $debter_col_2 = "group_".$debter_name."_margin_on_buying_price";
    $debter_col_3 = "group_".$debter_name."_margin_on_selling_price";
    $debter_col_4 = "group_".$debter_name."_discount_on_grossprice_b_on_deb_selling_price";
    $sql_reset_price = "UPDATE price_management_data SET $debter_col_1=0,$debter_col_2=0,$debter_col_3=0,$debter_col_4=0 WHERE product_id IN (".$unassign_products.")";
    $msg_error = "successfull reset ";
    if(!$conn->query($sql_reset_price)) {
      $msg_error =  $conn->mysqli_error();
      return false;
    }
  }
  return ($catId_new_arr != $old_cats_arr);
}