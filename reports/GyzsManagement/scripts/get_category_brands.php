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
    //$catId = $_POST['cat_id'];//print_r($catId);
    $customerGroup = $_POST['customer_group'];
    $catIdNew = $_POST['cat_id_new'];

    //$catId_arr = explode(',', $catId);
    $catId_new_arr = explode(',', $catIdNew);

//$new_categories = implode(',', array_filter(array_merge($catId_arr, $catId_new_arr)));//print_r($new_categories);exit;

    $sql = "INSERT INTO price_management_debter_categories(category_ids,customer_group,created_at,updated_at) VALUES ";
   // $all_col_data = "('".$new_categories."', '".$customerGroup."',  '".NOW()."', '".NOW()."')";
    $all_col_data = "('".$catIdNew."', '".$customerGroup."',  '".NOW()."', '".NOW()."')";

    $sql .= $all_col_data;
    $updated_at = date('Y-m-d h:i:s');
    $sql .= " ON DUPLICATE KEY UPDATE category_ids = VALUES(category_ids), customer_group = VALUES(customer_group),updated_at = VALUES(updated_at)";

   // print_r($sql);exit;
  
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

    case "get_categories_setprice":
      $by_group = $_POST['customer_group'];
      if ($by_group == 'all') {
         $sql = "select * from price_management_customer_groups join price_management_debter_categories on price_management_debter_categories.customer_group = price_management_customer_groups.magento_id";
        
         if ($result = $conn->query($sql)){
           $merge_categories = "";
          while($row = $result->fetch_assoc()) {
            $merge_categories .=  ','.$row['category_ids'];
          }
        //  print_r($merge_categories);
        $all_cats_arr = array_filter(explode(',', $merge_categories));
          $response_data['msg'] = implode(',', $all_cats_arr);
        } else {
          $response_data['msg'] = "Error in multiple debter rule selection:- ".mysqli_error($conn);
        }
      
        } else {
        $sql = "select * from price_management_customer_groups join price_management_debter_categories on price_management_debter_categories.customer_group = price_management_customer_groups.magento_id where price_management_customer_groups.customer_group_name={$by_group} limit 1";
        if ($result = $conn->query($sql)){
          $row = $result->fetch_assoc();
          $response_data['msg'] = $row['category_ids'];
        } else {
          $response_data['msg'] = "Error in debter rule selection:- ".mysqli_error($conn);
        }
      }
     


      break;
}

echo json_encode($response_data); 
?>