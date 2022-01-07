  <?php

  include "../config/config.php";
  include "../define/constants.php";
  require_once("../../../app/Mage.php");
  umask(0);
  Mage::app();
  
  Mage::app("default")->setCurrentStore(Mage_Core_Model_App :: ADMIN_STORE_ID);

  //ini_set('display_errors', 1);
  //ini_set('display_startup_errors', 1);
  //error_reporting(E_ALL);
  ini_set('max_execution_time', 0);
  ini_set('memory_limit','3G');

  $type = $_REQUEST['type'];
  $response_data = array();

switch ($type) {
  case "confirm_ecdtime_update":
    $isAllChecked = $_REQUEST['isAllChecked'];
    $ec_delivery_time = $_REQUEST['ec_delivery_time'];
    $selected_supplier = $_REQUEST['selected_supplier'];

    $from = "";
    if($isAllChecked == 1) {
        // Check All ignore paging

      $supplier_query = "";
      if($selected_supplier != "") {
        $supplier_query = "WHERE pmbm.supplier_type = '".$selected_supplier."'";
      }

      $sql_chk_all = "SELECT DISTINCT 
                      mcpe.entity_id AS product_id,
                      mcpe.sku AS sku,
                      pmbm.supplier_type AS supplier_type,
                      pmbm.product_bol_minimum_price AS product_bol_minimum_price,
                      IF(mcpet_af.value = 0, mcped.value * mcpet.value, mcped.value) AS buying_price,
                      pmbm.updated_date_time AS updated_date_time,
                      pmecdtime.option_value AS ec_deliverytime,
                      pmbm.product_bol_minimum_price_cal AS product_bol_minimum_price_cal 
                      FROM mage_catalog_product_entity AS mcpe 
                      INNER JOIN price_management_bol_minimum AS pmbm 
                      ON pmbm.product_id = mcpe.entity_id 
                      LEFT JOIN price_management_bol_commission AS pmbc 
                      ON pmbc.product_id = pmbm.product_id 
                      LEFT JOIN price_management_ec_deliverytime AS pmecdtime 
                      ON pmecdtime.option_id = pmbm.ec_deliverytime 
                      LEFT JOIN mage_catalog_product_entity_text AS mcpet_af 
                      ON mcpet_af.entity_id = pmbm.product_id AND mcpet_af.attribute_id = '".afwijkenidealeverpakking."' 
                      LEFT JOIN mage_catalog_product_entity_text AS mcpet 
                      ON mcpet.entity_id = pmbm.product_id AND mcpet.attribute_id = '".IDEALEVERPAKKING."' 
                      LEFT JOIN mage_catalog_product_entity_decimal AS mcped 
                      ON mcped.entity_id = pmbm.product_id AND mcped.attribute_id = '".COST."' 
                      ".$supplier_query."";

      $result_chk_all = $conn->query($sql_chk_all);
      $allData = $result_chk_all->fetch_all(MYSQLI_ASSOC);
      $from = "From Check All EC DeliveryTime";
    } else {
      $allData = $_REQUEST['all_selected_records'];
      $from = "From Multiple Select EC DeliveryTime";
    } 

    if(count($allData) > 0) {
      $all_selected_data = array();
      foreach($allData as $k=>$v) {
        $all_selected_data[$v["product_id"]]['product_id'] = $v["product_id"];
        $all_selected_data[$v["product_id"]]['sku'] = $v["sku"];
        $all_selected_data[$v['product_id']]['ec_delivery_time'] = $ec_delivery_time;
      }
      $updated_recs = bulkUpdateECDeliveryTime($all_selected_data,$from);
    }
    $response_data['msg'] = "Delivery Time Updated:-".$updated_recs;
    break;

    case "confirm_ecdtime_be_update":
    $isAllChecked = $_REQUEST['isAllChecked'];
    $ec_delivery_time_be = $_REQUEST['ec_delivery_time_be'];
    $selected_supplier = $_REQUEST['selected_supplier'];

    $from = "";
    if($isAllChecked == 1) {
        // Check All ignore paging

      $supplier_query = "";
      if($selected_supplier != "") {
        $supplier_query = "WHERE pmbm.supplier_type = '".$selected_supplier."'";
      }

      $sql_chk_all = "SELECT DISTINCT 
                      mcpe.entity_id AS product_id,
                      mcpe.sku AS sku,
                      pmbm.supplier_type AS supplier_type,
                      pmbm.product_bol_minimum_price AS product_bol_minimum_price,
                      IF(mcpet_af.value = 0, mcped.value * mcpet.value, mcped.value) AS buying_price,
                      pmbm.updated_date_time AS updated_date_time,
                      pmecdtimebe.option_value AS ec_deliverytime_be,
                      pmbm.product_bol_minimum_price_cal AS product_bol_minimum_price_cal 
                      FROM mage_catalog_product_entity AS mcpe 
                      INNER JOIN price_management_bol_minimum AS pmbm 
                      ON pmbm.product_id = mcpe.entity_id 
                      LEFT JOIN price_management_bol_commission AS pmbc 
                      ON pmbc.product_id = pmbm.product_id 
                      LEFT JOIN price_management_ec_deliverytime_be AS pmecdtimebe 
                      ON pmecdtimebe.option_id = pmbm.ec_deliverytime_be 
                      LEFT JOIN mage_catalog_product_entity_text AS mcpet_af 
                      ON mcpet_af.entity_id = pmbm.product_id AND mcpet_af.attribute_id = '".afwijkenidealeverpakking."' 
                      LEFT JOIN mage_catalog_product_entity_text AS mcpet 
                      ON mcpet.entity_id = pmbm.product_id AND mcpet.attribute_id = '".IDEALEVERPAKKING."' 
                      LEFT JOIN mage_catalog_product_entity_decimal AS mcped 
                      ON mcped.entity_id = pmbm.product_id AND mcped.attribute_id = '".COST."' 
                      ".$supplier_query."";

      $result_chk_all = $conn->query($sql_chk_all);
      $allData = $result_chk_all->fetch_all(MYSQLI_ASSOC);
      $from = "From Check All EC DeliveryTime BE";
    } else {
      $allData = $_REQUEST['all_selected_records'];
      $from = "From Multiple Select EC DeliveryTime BE";
    } 

    if(count($allData) > 0) {
      $all_selected_data = array();
      foreach($allData as $k=>$v) {
        $all_selected_data[$v["product_id"]]['product_id'] = $v["product_id"];
        $all_selected_data[$v["product_id"]]['sku'] = $v["sku"];
        $all_selected_data[$v['product_id']]['ec_deliverytime_be'] = $ec_delivery_time_be;
      }
      $updated_recs = bulkUpdateECDeliveryTimeBE($all_selected_data,$from);
    }
    $response_data['msg'] = "BE Delivery Time Updated:-".$updated_recs;

    break;





    case "check_all_func_ec":
    $selected_supplier = $_REQUEST['selected_supplier'];
    $supplier_query = "";
    if($selected_supplier != "") {
      $supplier_query = "WHERE supplier_type = '".$selected_supplier."'";
    }
    $sql = "SELECT COUNT(*) as total_records FROM price_management_bol_minimum ".$supplier_query.""; 
    $result = $conn->query($sql);
    $get_data = $result->fetch_assoc();
    $response_data['msg'] = $get_data["total_records"];
    break;   
}

function bulkUpdateECDeliveryTime($data,$log_type) {
  $chunk_size = PMCHUNK;
  global $conn;
  $total_updated_records = array();
  $chunk_data = array_chunk($data,$chunk_size);
  
  if(count($chunk_data)) {
    foreach($chunk_data as $chunk_index=>$chunk_values) {
      $all_col_data = array();
      $sql = "INSERT INTO price_management_bol_minimum (product_id, product_sku, ec_deliverytime) VALUES ";
      foreach($chunk_values as $key=>$chunk_value) {
        $all_col_data[] = "('".$chunk_value['product_id']."', '".$chunk_value['sku']."', '".$chunk_value['ec_delivery_time']."')";
      }
      $sql .= implode(",", $all_col_data) . " ON DUPLICATE KEY UPDATE ec_deliverytime = VALUES(ec_deliverytime)";

      if($conn->query($sql)) {        
        bulkInsertECUpdateLog($chunk_index,"Bulk Update (".$log_type."):".count($chunk_values));
        $total_updated_records[] = count($chunk_values);
      } else {
        bulkInsertECUpdateLog($chunk_index,"Bulk Update Error (".$log_type."):".mysqli_error($conn));
      }
    }
  }
  return array_sum($total_updated_records);
}


function bulkUpdateECDeliveryTimeBE($data,$log_type) {
  $chunk_size = PMCHUNK;
  global $conn;
  $total_updated_records = array();
  $chunk_data = array_chunk($data,$chunk_size);
  
  if(count($chunk_data)) {
    foreach($chunk_data as $chunk_index=>$chunk_values) {
      $all_col_data = array();
      $sql = "INSERT INTO price_management_bol_minimum (product_id, product_sku, ec_deliverytime_be) VALUES ";
      foreach($chunk_values as $key=>$chunk_value) {
        $all_col_data[] = "('".$chunk_value['product_id']."', '".$chunk_value['sku']."', '".$chunk_value['ec_deliverytime_be']."')";
      }
      $sql .= implode(",", $all_col_data) . " ON DUPLICATE KEY UPDATE ec_deliverytime_be = VALUES(ec_deliverytime_be)";

      if($conn->query($sql)) {        
        bulkInsertECBEUpdateLog($chunk_index,"Bulk Update BE (".$log_type."):".count($chunk_values));
        $total_updated_records[] = count($chunk_values);
      } else {
        bulkInsertECBEUpdateLog($chunk_index,"Bulk Update BE Error (".$log_type."):".mysqli_error($conn));
      }
    }
  }
  return array_sum($total_updated_records);
}


function bulkInsertECUpdateLog($chunk_index,$chunk_msg) {
  $file_ec_update_log = "../pm_logs/effect_connect_delivery_time_update_log.txt";
  file_put_contents($file_ec_update_log,"".date("d-m-Y H:i:s")." Updated Effect Connect Delivery Time Chunk (".$chunk_index."):-".$chunk_msg."\n",FILE_APPEND);
}

function bulkInsertECBEUpdateLog($chunk_index,$chunk_msg) {
  $file_ec_be_update_log = "../pm_logs/effect_connect_delivery_time_be_update_log.txt";
  file_put_contents($file_ec_be_update_log,"".date("d-m-Y H:i:s")." Updated Effect Connect Delivery Time BE Chunk (".$chunk_index."):-".$chunk_msg."\n",FILE_APPEND);
}

echo json_encode($response_data); 
?>
