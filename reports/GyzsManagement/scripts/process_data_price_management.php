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

  $type = $_REQUEST['type'];
  $response_data = array();

 

switch ($type) {

case "check_all_func":
$get_check_all_file = $document_root_url."/getquery.txt";
$get_data = json_decode(file_get_contents($get_check_all_file),true);
$response_data['msg'] = $get_data["total_records"];
break;

case "immediate_update":

$sql = "SELECT * FROM price_management_data WHERE is_updated = '1'";
$result = $conn->query($sql);
$allDataToExport = $result->fetch_all(MYSQLI_ASSOC);

$chunk_updated_data = array_chunk($allDataToExport, PMCHUNK);

$get_msg = "";
if(count($chunk_updated_data)) {
  foreach($chunk_updated_data as $chunked_idx=>$chunked_updated_values) {
    $sql_update_price = "INSERT INTO mage_catalog_product_entity_decimal (entity_type_id,entity_id,attribute_id,value) VALUES ";
    $sql_update_debter_price = "INSERT INTO mage_catalog_product_entity_group_price (entity_id,all_groups,customer_group_id,value) VALUES ";

    $updateArray = array();
    $updateDebterArray = array();
    $updated_product_ids = array();
    foreach($chunked_updated_values as $chunked_updated_value) {
      $entity_id = $chunked_updated_value['product_id'];
      $afwijkenidealeverpakking = $chunked_updated_value['afwijkenidealeverpakking'];
      $idealeverpakking = $chunked_updated_value['idealeverpakking'];
      $selling_price = $chunked_updated_value['selling_price'];
      $buying_price = $chunked_updated_value['buying_price'];
      
        if($afwijkenidealeverpakking == 0) { 
          $price_to_update = round($selling_price / $idealeverpakking,4); // per piece
          $buying_price_to_update = round($buying_price / $idealeverpakking,4);  
        } else {
          $price_to_update = $selling_price;
          $buying_price_to_update = $buying_price;  
        }
        $updateArray[] = "(4,".$entity_id.",".PRICE.",".$price_to_update.")";
        $updateArray[] = "(4,".$entity_id.",".COST.",".$buying_price_to_update.")";

        //Debter
        for($d=0;$d<=10;$d++) { 
          $cust_group = intval(4027100 + $d);
          $deb_id_column = "group_".$cust_group."_magento_id";
          $deb_id_exists = $chunked_updated_value[$deb_id_column];
          if($deb_id_exists == 0) {
            continue;
          }
          
          if($afwijkenidealeverpakking == 0) {
            $deb_price_column = "group_".$cust_group."_debter_selling_price";
            $deb_price_to_update = round($chunked_updated_value[$deb_price_column] / $idealeverpakking,4); // per piece
          } else {
            $deb_price_column = "group_".$cust_group."_debter_selling_price";
            $deb_price_to_update = $chunked_updated_value[$deb_price_column];
          }
          $updateDebterArray[] = "(".$entity_id.",0,".array_search($cust_group,getCustomerGroups()).",".$deb_price_to_update.")";
        }
        //Debter
        $updated_product_ids[] = $entity_id;
      }
      $sql_update_price .= implode(",", $updateArray) . " ON DUPLICATE KEY UPDATE value = VALUES(value)";
      $sql_update_debter_price .= implode(",", $updateDebterArray) . " ON DUPLICATE KEY UPDATE value = VALUES(value)";

        if($conn->query($sql_update_price)) {
          file_put_contents("../pm_logs/export_immidiate_pm_log.txt","".date("d-m-Y H:i:s")."\nExported Chunk (".$chunked_idx."):-".print_r($updateArray,true)."\n",FILE_APPEND);
          $total_updated_records[] = count($chunked_updated_values);
          resetUpdateStatus($conn, implode(",", $updated_product_ids));
          resetHistoryStatus($conn, implode(",", $updated_product_ids)); 
        } else {
          file_put_contents("../pm_logs/export_immidiate_pm_log.txt","".date("d-m-Y H:i:s")."\nExported Chunk Error (".$chunked_idx."):-".mysqli_error($conn).$sql."\n\n".print_r($updateArray,true)."\n",FILE_APPEND);
        }

        if($conn->query($sql_update_debter_price)) {
          file_put_contents("../pm_logs/export_immidiate_pm_log_debter.txt","".date("d-m-Y H:i:s")."\nExported Debtor Chunk (".$chunked_idx."):-".print_r($updateDebterArray,true)."\n",FILE_APPEND); 
        } else {
          file_put_contents("../pm_logs/export_immidiate_pm_log_debter.txt","".date("d-m-Y H:i:s")."\nExported Debtor Chunk Error (".$chunked_idx."):-".mysqli_error($conn).$sql."\n\n".print_r($updateDebterArray,true)."\n",FILE_APPEND);
        }
  }
  $get_msg = "Product Price Exported:-".array_sum($total_updated_records);
} else {
  $get_msg = "Data is not updated yet!! First, please update any data";
}

//$process = Mage::getModel('index/indexer')->getProcessByCode('catalog_product_price');
//$process->reindexAll(); 
$response_data['msg'] = $get_msg;
break;

case "refresh_magento":
  $msg = "";
  
  if(Mage::getSingleton('amfpc/fpc')->flush()) {
    $msg = "FPC is refreshed";  
  } else {
    $msg = "Cannot refresh FPC";
  }

  try {
     $msg .= "<br>"; 
     $msg .= shell_exec('php ../../../shell/indexer.php -reindex catalog_product_price');
  } catch (Exception $e) {
     $msg .= "<br>".$e->getMessage();
  }

  $response_data['msg'] = $msg;
break;


  // Debter Code Starts //
case "update_debter_selling_price":
$debter_selling_price = $_REQUEST['debter_selling_price'];
$debter_number = $_REQUEST['debter_number'];
$pmd_buying_price = $_REQUEST['pmd_buying_price'];
$supplier_gross_price = ($_REQUEST['supplier_gross_price'] == 0 ? 1:$_REQUEST['supplier_gross_price']);
$product_id = $_REQUEST['product_id'];


$c_d_id = "group_".$debter_number."_magento_id";
$c_d_sp = "group_".$debter_number."_debter_selling_price";
$c_d_m_bp = "group_".$debter_number."_margin_on_buying_price";
$c_d_m_sp = "group_".$debter_number."_margin_on_selling_price";
$c_d_o_gp = "group_".$debter_number."_discount_on_grossprice_b_on_deb_selling_price";

$deb_id = array_search($debter_number,getCustomerGroups());
$deb_margin_on_buying_price = roundValue((($debter_selling_price - $pmd_buying_price)/$pmd_buying_price) * 100);
$deb_margin_on_selling_price = roundValue((($debter_selling_price - $pmd_buying_price)/$debter_selling_price) * 100);
$deb_discount_on_gross_price = roundValue((1 - ($debter_selling_price/$supplier_gross_price)) * 100);

$sql = "UPDATE price_management_data SET ".$c_d_id." = '".$deb_id."', ".$c_d_sp." = '".$debter_selling_price."', ".$c_d_m_bp."   = '".$deb_margin_on_buying_price."', ".$c_d_m_sp." = '".$deb_margin_on_selling_price."', ".$c_d_o_gp." = '".$deb_discount_on_gross_price."'  WHERE product_id = '".$product_id."'";

$conn->query($sql);
$response_data['msg'] = $sql;

changeUpdateStatus($conn,$product_id);
break;

case "update_debter_margin_on_buying_price":
$debter_margin_on_buying_price = $_REQUEST['debter_margin_on_buying_price'];
$debter_number = $_REQUEST['debter_number'];
$pmd_buying_price = $_REQUEST['pmd_buying_price'];
$supplier_gross_price = ($_REQUEST['supplier_gross_price'] == 0 ? 1:$_REQUEST['supplier_gross_price']);
$product_id = $_REQUEST['product_id'];

$c_d_id = "group_".$debter_number."_magento_id";
$c_d_sp = "group_".$debter_number."_debter_selling_price";
$c_d_m_bp = "group_".$debter_number."_margin_on_buying_price";
$c_d_m_sp = "group_".$debter_number."_margin_on_selling_price";
$c_d_o_gp = "group_".$debter_number."_discount_on_grossprice_b_on_deb_selling_price";

$deb_id = array_search($debter_number,getCustomerGroups());
$deb_margin_on_buying_price = $debter_margin_on_buying_price;
$debter_selling_price = roundValue((1 + ($deb_margin_on_buying_price/100)) * $pmd_buying_price);
$deb_margin_on_selling_price = roundValue((($debter_selling_price - $pmd_buying_price)/$debter_selling_price) * 100);
$deb_discount_on_gross_price = roundValue((1 - ($debter_selling_price/$supplier_gross_price)) * 100);

$sql = "UPDATE price_management_data SET ".$c_d_id." = '".$deb_id."', ".$c_d_sp." = '".$debter_selling_price."', ".$c_d_m_bp."   = '".$deb_margin_on_buying_price."', ".$c_d_m_sp." = '".$deb_margin_on_selling_price."', ".$c_d_o_gp." = '".$deb_discount_on_gross_price."'  WHERE product_id = '".$product_id."'";

$conn->query($sql);
$response_data['msg'] = $sql;

changeUpdateStatus($conn,$product_id);
break;

case "update_debter_margin_on_selling_price":
$debter_margin_on_selling_price = $_REQUEST['debter_margin_on_selling_price'];
$debter_number = $_REQUEST['debter_number'];
$pmd_buying_price = $_REQUEST['pmd_buying_price'];
$supplier_gross_price = ($_REQUEST['supplier_gross_price'] == 0 ? 1:$_REQUEST['supplier_gross_price']);
$product_id = $_REQUEST['product_id'];

$c_d_id = "group_".$debter_number."_magento_id";
$c_d_sp = "group_".$debter_number."_debter_selling_price";
$c_d_m_bp = "group_".$debter_number."_margin_on_buying_price";
$c_d_m_sp = "group_".$debter_number."_margin_on_selling_price";
$c_d_o_gp = "group_".$debter_number."_discount_on_grossprice_b_on_deb_selling_price";

$deb_id = array_search($debter_number,getCustomerGroups());
$deb_margin_on_selling_price = $debter_margin_on_selling_price;
$debter_selling_price = roundValue($pmd_buying_price / (1-($deb_margin_on_selling_price/100)));
$deb_margin_on_buying_price = roundValue((($debter_selling_price - $pmd_buying_price)/$pmd_buying_price) * 100);
$deb_discount_on_gross_price = roundValue((1 - ($debter_selling_price/$supplier_gross_price)) * 100);

$sql = "UPDATE price_management_data SET ".$c_d_id." = '".$deb_id."', ".$c_d_sp." = '".$debter_selling_price."', ".$c_d_m_bp."   = '".$deb_margin_on_buying_price."', ".$c_d_m_sp." = '".$deb_margin_on_selling_price."', ".$c_d_o_gp." = '".$deb_discount_on_gross_price."'  WHERE product_id = '".$product_id."'";

$conn->query($sql);
$response_data['msg'] = $sql;

changeUpdateStatus($conn,$product_id);
break;

case "update_debter_discount_on_gross_price":
$debter_discount_on_gross_price = $_REQUEST['debter_discount_on_gross_price'];
$debter_number = $_REQUEST['debter_number'];
$pmd_buying_price = $_REQUEST['pmd_buying_price'];
$supplier_gross_price = ($_REQUEST['supplier_gross_price'] == 0 ? 1:$_REQUEST['supplier_gross_price']);
$product_id = $_REQUEST['product_id'];

$c_d_id = "group_".$debter_number."_magento_id";
$c_d_sp = "group_".$debter_number."_debter_selling_price";
$c_d_m_bp = "group_".$debter_number."_margin_on_buying_price";
$c_d_m_sp = "group_".$debter_number."_margin_on_selling_price";
$c_d_o_gp = "group_".$debter_number."_discount_on_grossprice_b_on_deb_selling_price";

$deb_id = array_search($debter_number,getCustomerGroups());
$debter_selling_price = roundValue((1 - ($debter_discount_on_gross_price/100)) * $supplier_gross_price);
$deb_margin_on_buying_price = roundValue((($debter_selling_price - $pmd_buying_price)/$pmd_buying_price) * 100);
$deb_margin_on_selling_price = roundValue((($debter_selling_price - $pmd_buying_price)/$debter_selling_price) * 100);
$deb_discount_on_gross_price = $debter_discount_on_gross_price;

$sql = "UPDATE price_management_data SET ".$c_d_id." = '".$deb_id."', ".$c_d_sp." = '".$debter_selling_price."', ".$c_d_m_bp."   = '".$deb_margin_on_buying_price."', ".$c_d_m_sp." = '".$deb_margin_on_selling_price."', ".$c_d_o_gp." = '".$deb_discount_on_gross_price."'  WHERE product_id = '".$product_id."'";

$conn->query($sql);
$response_data['msg'] = $sql;
changeUpdateStatus($conn,$product_id);
break;


  // Debter Code Ends //

case "update_selling_price":

$product_id = $_REQUEST['product_id'];
$selling_price = trim($_REQUEST['product_price']);
$webshop_selling_price = $_REQUEST['webshop_selling_price'];
$supplier_gross_price = ($_REQUEST['supplier_gross_price'] == 0 ? 1:$_REQUEST['supplier_gross_price']);
$pmd_buying_price = $_REQUEST['pmd_buying_price'];

$discount_percentage = 0;
$profit_margin = roundValue((($selling_price - $pmd_buying_price)/$pmd_buying_price) * 100);
$profit_margin_sp = roundValue((($selling_price - $pmd_buying_price)/$selling_price) * 100);
$percentage_increase = roundValue((($selling_price - $webshop_selling_price)/$webshop_selling_price) * 100);
$discount_percentage = roundValue((1 - ($selling_price/$supplier_gross_price)) * 100);

  $sql = "UPDATE price_management_data SET selling_price = '".$selling_price."',profit_percentage_buying_price = '".$profit_margin."', profit_percentage_selling_price = '".$profit_margin_sp."', percentage_increase = '".$percentage_increase."', discount_on_gross = '".$discount_percentage."'  WHERE product_id = '".$product_id."'";
  
  if($conn->query($sql)) {
    $response_data['msg'] = $sql;
    
    //Add in history
      $historyArray = array();
      $fields_changed[] = "new_selling_price";
      $buying_price_changed = 0;

      $historyArray[] = "('".$product_id."',
      '".$_REQUEST['buying_price']."',
      '".$_REQUEST['webshop_supplier_gross_price']."',
      '".$_REQUEST['webshop_idealeverpakking']."',
      '".$_REQUEST['webshop_afwijkenidealeverpakking']."',
      '".$_REQUEST['buying_price']."',
      '".$_REQUEST['webshop_selling_price']."',
      '".roundValue($pmd_buying_price)."',
      '".$_REQUEST['supplier_gross_price']."',
      '".$_REQUEST['idealeverpakking']."',
      '".$_REQUEST['afwijkenidealeverpakking']."',
      '".roundValue($pmd_buying_price)."',
      '".roundValue($selling_price)."',
      '".date("Y-m-d H:i:s")."',
      'Price Management',
      'No',
      '".json_encode($fields_changed)."',
      '".$buying_price_changed."',
      'No',
      NULL
      )";

      addInHistory($conn,$historyArray);
    //Add in history

    changeUpdateStatus($conn,$product_id);
  }
break;

case "update_profit_margin":
$product_id = $_REQUEST['product_id'];
$profit_margin = $_REQUEST['profit_margin'];
$webshop_selling_price = $_REQUEST['webshop_selling_price'];
$supplier_gross_price = ($_REQUEST['supplier_gross_price'] == 0 ? 1:$_REQUEST['supplier_gross_price']);
$pmd_buying_price = $_REQUEST['pmd_buying_price'];


$discount_percentage = 0;

$selling_price = roundValue((1 + ($profit_margin/100)) * $pmd_buying_price);
$profit_margin_sp = roundValue((($selling_price - $pmd_buying_price)/$selling_price) * 100);
$percentage_increase = roundValue((($selling_price - $webshop_selling_price)/$webshop_selling_price) * 100);
$discount_percentage = roundValue((1 - ($selling_price/$supplier_gross_price)) * 100);

  $sql = "UPDATE price_management_data SET selling_price = '".$selling_price."', profit_percentage_buying_price = '".$profit_margin."', profit_percentage_selling_price = '".$profit_margin_sp."', percentage_increase = '".$percentage_increase."', discount_on_gross = '".$discount_percentage."' WHERE product_id = '".$product_id."'";

  if($conn->query($sql)) {
    $response_data['msg'] = $sql;
    
    //Add in history
      $historyArray = array();
      $fields_changed[] = "new_selling_price";
      $buying_price_changed = 0;

      $historyArray[] = "('".$product_id."',
      '".$_REQUEST['buying_price']."',
      '".$_REQUEST['webshop_supplier_gross_price']."',
      '".$_REQUEST['webshop_idealeverpakking']."',
      '".$_REQUEST['webshop_afwijkenidealeverpakking']."',
      '".$_REQUEST['buying_price']."',
      '".$_REQUEST['webshop_selling_price']."',
      '".roundValue($pmd_buying_price)."',
      '".$_REQUEST['supplier_gross_price']."',
      '".$_REQUEST['idealeverpakking']."',
      '".$_REQUEST['afwijkenidealeverpakking']."',
      '".roundValue($pmd_buying_price)."',
      '".roundValue($selling_price)."',
      '".date("Y-m-d H:i:s")."',
      'Price Management',
      'No',
      '".json_encode($fields_changed)."',
      '".$buying_price_changed."',
      'No',
      NULL
      )";

      addInHistory($conn,$historyArray);
    //Add in history

    changeUpdateStatus($conn,$product_id);
  }
  
break;


case "update_profit_margin_sp":
$product_id = $_REQUEST['product_id'];
$profit_margin_sp = $_REQUEST['profit_margin_sp'];
$webshop_selling_price = $_REQUEST['webshop_selling_price'];
$supplier_gross_price = ($_REQUEST['supplier_gross_price'] == 0 ? 1:$_REQUEST['supplier_gross_price']);  
$pmd_buying_price = $_REQUEST['pmd_buying_price'];

$discount_percentage = 0;

$selling_price = roundValue($pmd_buying_price / (1-($profit_margin_sp/100)));
$profit_margin = roundValue((($selling_price - $pmd_buying_price)/$pmd_buying_price) * 100);
$profit_margin_sp = $profit_margin_sp;
$percentage_increase = roundValue((($selling_price - $webshop_selling_price)/$webshop_selling_price) * 100);
$discount_percentage = roundValue((1 - ($selling_price/$supplier_gross_price)) * 100);
$sql = "UPDATE price_management_data SET selling_price = '".$selling_price."', profit_percentage_buying_price = '".$profit_margin."', profit_percentage_selling_price = '".$profit_margin_sp."', percentage_increase = '".$percentage_increase."', discount_on_gross = '".$discount_percentage."' WHERE product_id = '".$product_id."'";

  if($conn->query($sql)) {
    $response_data['msg'] = $sql;
    
    //Add in history
      $historyArray = array();
      $fields_changed[] = "new_selling_price";
      $buying_price_changed = 0;

      $historyArray[] = "('".$product_id."',
      '".$_REQUEST['buying_price']."',
      '".$_REQUEST['webshop_supplier_gross_price']."',
      '".$_REQUEST['webshop_idealeverpakking']."',
      '".$_REQUEST['webshop_afwijkenidealeverpakking']."',
      '".$_REQUEST['buying_price']."',
      '".$_REQUEST['webshop_selling_price']."',
      '".roundValue($pmd_buying_price)."',
      '".$_REQUEST['supplier_gross_price']."',
      '".$_REQUEST['idealeverpakking']."',
      '".$_REQUEST['afwijkenidealeverpakking']."',
      '".roundValue($pmd_buying_price)."',
      '".roundValue($selling_price)."',
      '".date("Y-m-d H:i:s")."',
      'Price Management',
      'No',
      '".json_encode($fields_changed)."',
      '".$buying_price_changed."',
      'No',
      NULL
      )";

      addInHistory($conn,$historyArray);
    //Add in history

    changeUpdateStatus($conn,$product_id);
  }

break;

case "update_discount":

$product_id = $_REQUEST['product_id'];    
$discount_percentage = $_REQUEST['discount_percentage'];
$webshop_selling_price = $_REQUEST['webshop_selling_price'];
$supplier_gross_price = ($_REQUEST['supplier_gross_price'] == 0 ? 1:$_REQUEST['supplier_gross_price']);
$pmd_buying_price = $_REQUEST['pmd_buying_price'];

$selling_price = roundValue((1 - ($discount_percentage/100)) * $supplier_gross_price);
$profit_margin = roundValue((($selling_price - $pmd_buying_price)/$pmd_buying_price) * 100);
$profit_margin_sp = roundValue((($selling_price - $pmd_buying_price)/$selling_price) * 100);
$percentage_increase = roundValue((($selling_price - $webshop_selling_price)/$webshop_selling_price) * 100);
$sql = "UPDATE price_management_data SET selling_price = '".$selling_price."', profit_percentage_buying_price = '".$profit_margin."', profit_percentage_selling_price = '".$profit_margin_sp."', percentage_increase = '".$percentage_increase."', discount_on_gross = '".$discount_percentage."' WHERE product_id = '".$product_id."'";

  if($conn->query($sql)) {
    $response_data['msg'] = $sql;
    
    //Add in history
      $historyArray = array();
      $fields_changed[] = "new_selling_price";
      $buying_price_changed = 0;

      $historyArray[] = "('".$product_id."',
      '".$_REQUEST['buying_price']."',
      '".$_REQUEST['webshop_supplier_gross_price']."',
      '".$_REQUEST['webshop_idealeverpakking']."',
      '".$_REQUEST['webshop_afwijkenidealeverpakking']."',
      '".$_REQUEST['buying_price']."',
      '".$_REQUEST['webshop_selling_price']."',
      '".roundValue($pmd_buying_price)."',
      '".$_REQUEST['supplier_gross_price']."',
      '".$_REQUEST['idealeverpakking']."',
      '".$_REQUEST['afwijkenidealeverpakking']."',
      '".roundValue($pmd_buying_price)."',
      '".roundValue($selling_price)."',
      '".date("Y-m-d H:i:s")."',
      'Price Management',
      'No',
      '".json_encode($fields_changed)."',
      '".$buying_price_changed."',
      'No',
      NULL
      )";

      addInHistory($conn,$historyArray);
    //Add in history

    changeUpdateStatus($conn,$product_id);
  }

break;

  // Debter bulk update starts

case "bulk_update_debter_selling_price":

$isAllChecked = $_REQUEST['isAllChecked'];
$positive_or_negative = $_REQUEST['positive_or_negative'];
$profit_margin = $_REQUEST['profit_margin'];
$debter_number = $_REQUEST['debter_number'];
$selling_percentage = $_REQUEST['selling_percentage'];
$selected_debter_column_index = "group_".$debter_number."_debter_selling_price";

$common_data = array();
$common_data["debter_number"] = $debter_number;

$from = "";
if($isAllChecked == 1) {
    // Check All ignore paging
  $sql_chk_all = getChkAllSql(); 
  $result_chk_all = $conn->query($sql_chk_all);
  $allData = $result_chk_all->fetch_all(MYSQLI_ASSOC);
  $from = "From Check All";
} else {
  $allData = $_REQUEST['sellingPrices'];
  $from = "From Multiple Select";
} 

if(count($allData) > 0) {
  $all_selected_data = array();
  foreach($allData as $k=>$v) {
    $all_selected_data[$v["product_id"]]['product_id'] = $v["product_id"];
    $all_selected_data[$v["product_id"]]['sku'] = $v["sku"];


    $actual_selling_price = $v[$selected_debter_column_index];
    $pmd_buying_price = $v['buying_price'];
    $supplier_gross_price = ($v['supplier_gross_price'] == 0 ? 1:$v['supplier_gross_price']);

    if($positive_or_negative == "+") {
      $debter_selling_price = roundValue((1 + ($selling_percentage/100)) * $actual_selling_price);
    } else if($positive_or_negative == "-") {
      $debter_selling_price =  roundValue($actual_selling_price - (($selling_percentage/100) * $actual_selling_price));
    }

    $deb_margin_on_buying_price = roundValue((($debter_selling_price - $pmd_buying_price)/$pmd_buying_price) * 100);
    $deb_margin_on_selling_price = roundValue((($debter_selling_price - $pmd_buying_price)/$debter_selling_price) * 100);
    $deb_discount_on_gross_price = roundValue((1 - ($debter_selling_price/$supplier_gross_price)) * 100);


    $all_selected_data[$v['product_id']]['selling_price'] = $debter_selling_price;
    $all_selected_data[$v['product_id']]['profit_margin_bp'] = $deb_margin_on_buying_price;
    $all_selected_data[$v['product_id']]['profit_margin_sp'] = $deb_margin_on_selling_price;
    $all_selected_data[$v['product_id']]['discount_on_gross'] = $deb_discount_on_gross_price;      
  }
  $updated_recs = bulkUpdateProducts("debterprice",$all_selected_data,$common_data,$from,"Selling Price For ".$debter_number."");
}
$response_data['msg'] = "Products Updated:-".$updated_recs;
break;

case "multiple_update_debter_selling_price":
  $debter_number = $_REQUEST['debter_number'];
  $allData = array_filter($_REQUEST['multipledebtersellingPrices']);
  $from = "From Multiple Update Debter Selling Price";
  $common_data = array();
  $common_data["debter_number"] = $debter_number;



  if(count($allData) > 0) {
    $all_selected_data = array();
    foreach($allData as $k=>$v) {
      $all_selected_data[$v["product_id"]]['product_id'] = $v["product_id"];
      $all_selected_data[$v["product_id"]]['sku'] = $v["sku"];


      $debter_selling_price = $v['deb_selling_price'];
      $pmd_buying_price = $v['buying_price'];
      $supplier_gross_price = ($v['supplier_gross_price'] == 0 ? 1:$v['supplier_gross_price']);

      

      $deb_margin_on_buying_price = roundValue((($debter_selling_price - $pmd_buying_price)/$pmd_buying_price) * 100);
      $deb_margin_on_selling_price = roundValue((($debter_selling_price - $pmd_buying_price)/$debter_selling_price) * 100);
      $deb_discount_on_gross_price = roundValue((1 - ($debter_selling_price/$supplier_gross_price)) * 100);


      $all_selected_data[$v['product_id']]['selling_price'] = $debter_selling_price;
      $all_selected_data[$v['product_id']]['profit_margin_bp'] = $deb_margin_on_buying_price;
      $all_selected_data[$v['product_id']]['profit_margin_sp'] = $deb_margin_on_selling_price;
      $all_selected_data[$v['product_id']]['discount_on_gross'] = $deb_discount_on_gross_price;      
    }
    $updated_recs = bulkUpdateProducts("debterprice",$all_selected_data,$common_data,$from,"Selling Price For ".$debter_number."");
  }
  $response_data['msg'] = "Products Updated:-".$updated_recs;
break;


case "bulk_update_debter_margin_on_buying_price":
$isAllChecked = $_REQUEST['isAllChecked'];
$profit_margin = $_REQUEST['profit_margin'];
$common_data = array();
$common_data["debter_number"] = $_REQUEST['debter_number'];

$from = "";
if($isAllChecked == 1) {
    // Check All ignore paging
  $sql_chk_all = getChkAllSql(); 
  $result_chk_all = $conn->query($sql_chk_all);
  $allData = $result_chk_all->fetch_all(MYSQLI_ASSOC);
  $from = "From Check All";
} else {
  $allData = $_REQUEST['profitMargins'];
  $from = "From Multiple Select";
} 

if(count($allData) > 0) {
  $all_selected_data = array();
  foreach($allData as $k=>$v) {
    $all_selected_data[$v["product_id"]]['product_id'] = $v["product_id"];
    $all_selected_data[$v["product_id"]]['sku'] = $v["sku"];

    $pmd_buying_price = $v['buying_price'];
    $supplier_gross_price = ($v['supplier_gross_price'] == 0 ? 1:$v['supplier_gross_price']);


    $selling_price = roundValue((1 + ($profit_margin/100)) * $pmd_buying_price);
    $profit_margin_bp = $profit_margin;
    $profit_margin_sp = roundValue((($selling_price - $pmd_buying_price)/$selling_price) * 100);
    $discount_percentage = roundValue((1 - ($selling_price/$supplier_gross_price)) * 100);


    $all_selected_data[$v['product_id']]['selling_price'] = $selling_price;
    $all_selected_data[$v['product_id']]['profit_margin_bp'] = $profit_margin_bp;
    $all_selected_data[$v['product_id']]['profit_margin_sp'] = $profit_margin_sp;
    $all_selected_data[$v['product_id']]['discount_on_gross'] = $discount_percentage;

  }
  $updated_recs = bulkUpdateProducts("debterprice",$all_selected_data,$common_data,$from,"Profit Margin (BP) For ".$debter_number."");
}
$response_data['msg'] = "Products Updated:-".$updated_recs;
break;

case "multiple_update_debter_margin_on_buying_price":
  $debter_number = $_REQUEST['debter_number'];
  $allData = array_filter($_REQUEST['multipledebprofitMargins']);
  $from = "From Multiple Update Debter Margin On Buying Price";
  $common_data = array();
  $common_data["debter_number"] = $debter_number;

  if(count($allData) > 0) {
  $all_selected_data = array();
  foreach($allData as $k=>$v) {
    $all_selected_data[$v["product_id"]]['product_id'] = $v["product_id"];
    $all_selected_data[$v["product_id"]]['sku'] = $v["sku"];

    $pmd_buying_price = $v['buying_price'];
    $supplier_gross_price = ($v['supplier_gross_price'] == 0 ? 1:$v['supplier_gross_price']);


    $selling_price = roundValue((1 + ($v['deb_m_bp']/100)) * $pmd_buying_price);
    $profit_margin_bp = $v['deb_m_bp'];
    $profit_margin_sp = roundValue((($selling_price - $pmd_buying_price)/$selling_price) * 100);
    $discount_percentage = roundValue((1 - ($selling_price/$supplier_gross_price)) * 100);


    $all_selected_data[$v['product_id']]['selling_price'] = $selling_price;
    $all_selected_data[$v['product_id']]['profit_margin_bp'] = $profit_margin_bp;
    $all_selected_data[$v['product_id']]['profit_margin_sp'] = $profit_margin_sp;
    $all_selected_data[$v['product_id']]['discount_on_gross'] = $discount_percentage;

  }
  $updated_recs = bulkUpdateProducts("debterprice",$all_selected_data,$common_data,$from,"Multiple Profit Margin (BP) For ".$debter_number."");
}
$response_data['msg'] = "Products Updated:-".$updated_recs;
 
break;


case "bulk_update_debter_profit_margin_on_selling_price":
$isAllChecked = $_REQUEST['isAllChecked'];
$profit_margin = $_REQUEST['profit_margin'];
$common_data = array();
$common_data["debter_number"] = $_REQUEST['debter_number'];

$from = "";
if($isAllChecked == 1) {
    // Check All ignore paging
  $sql_chk_all = getChkAllSql(); 
  $result_chk_all = $conn->query($sql_chk_all);
  $allData = $result_chk_all->fetch_all(MYSQLI_ASSOC);
  $from = "From Check All";
} else {
  $allData = $_REQUEST['profitMargins'];
  $from = "From Multiple Select";
} 

if(count($allData) > 0) {
  $all_selected_data = array();
  foreach($allData as $k=>$v) {
    $all_selected_data[$v["product_id"]]['product_id'] = $v["product_id"];
    $all_selected_data[$v["product_id"]]['sku'] = $v["sku"];

    $pmd_buying_price = $v['buying_price'];
    $supplier_gross_price = ($v['supplier_gross_price'] == 0 ? 1:$v['supplier_gross_price']);


    $deb_margin_on_selling_price = $profit_margin;
    $debter_selling_price = roundValue($pmd_buying_price / (1-($deb_margin_on_selling_price/100)));
    $deb_margin_on_buying_price = roundValue((($debter_selling_price - $pmd_buying_price)/$pmd_buying_price) * 100);
    $deb_discount_on_gross_price = roundValue((1 - ($debter_selling_price/$supplier_gross_price)) * 100);


    $all_selected_data[$v['product_id']]['selling_price'] = $debter_selling_price;
    $all_selected_data[$v['product_id']]['profit_margin_bp'] = $deb_margin_on_buying_price;
    $all_selected_data[$v['product_id']]['profit_margin_sp'] = $deb_margin_on_selling_price;
    $all_selected_data[$v['product_id']]['discount_on_gross'] = $deb_discount_on_gross_price;

  }
  $updated_recs = bulkUpdateProducts("debterprice",$all_selected_data,$common_data,$from,"Profit Margin (SP) For ".$debter_number."");
}
$response_data['msg'] = "Products Updated:-".$updated_recs;
break;

case "multiple_update_debter_margin_on_selling_price":
  $debter_number = $_REQUEST['debter_number'];
  $allData = array_filter($_REQUEST['multipledebprofitMarginsOnSP']);
  $from = "From Multiple Update Debter Margin On Selling Price";
  $common_data = array();
  $common_data["debter_number"] = $debter_number;

  if(count($allData) > 0) {
  $all_selected_data = array();
  foreach($allData as $k=>$v) {
    $all_selected_data[$v["product_id"]]['product_id'] = $v["product_id"];
    $all_selected_data[$v["product_id"]]['sku'] = $v["sku"];

    $pmd_buying_price = $v['buying_price'];
    $supplier_gross_price = ($v['supplier_gross_price'] == 0 ? 1:$v['supplier_gross_price']);


    $deb_margin_on_selling_price = $v['deb_m_sp'];
    $debter_selling_price = roundValue($pmd_buying_price / (1-($deb_margin_on_selling_price/100)));
    $deb_margin_on_buying_price = roundValue((($debter_selling_price - $pmd_buying_price)/$pmd_buying_price) * 100);
    $deb_discount_on_gross_price = roundValue((1 - ($debter_selling_price/$supplier_gross_price)) * 100);


    $all_selected_data[$v['product_id']]['selling_price'] = $debter_selling_price;
    $all_selected_data[$v['product_id']]['profit_margin_bp'] = $deb_margin_on_buying_price;
    $all_selected_data[$v['product_id']]['profit_margin_sp'] = $deb_margin_on_selling_price;
    $all_selected_data[$v['product_id']]['discount_on_gross'] = $deb_discount_on_gross_price;

  }
  $updated_recs = bulkUpdateProducts("debterprice",$all_selected_data,$common_data,$from,"Multiple Profit Margin (SP) For ".$debter_number."");
}
$response_data['msg'] = "Products Updated:-".$updated_recs;

break;

case "bulk_update_debter_discount_on_gross_price":

$isAllChecked = $_REQUEST['isAllChecked'];
$discount_percentage = $_REQUEST['discount_percentage'];
$common_data = array();
$common_data["debter_number"] = $_REQUEST['debter_number'];

$from = "";
if($isAllChecked == 1) {
    // Check All ignore paging
  $sql_chk_all = getChkAllSql(); 
  $result_chk_all = $conn->query($sql_chk_all);
  $allData = $result_chk_all->fetch_all(MYSQLI_ASSOC);
  $from = "From Check All";
} else {
  $allData = $_REQUEST['discountPercentages'];
  $from = "From Multiple Select";
} 

if(count($allData) > 0) {
  $all_selected_data = array();
  foreach($allData as $k=>$v) {
    $all_selected_data[$v["product_id"]]['product_id'] = $v["product_id"];
    $all_selected_data[$v["product_id"]]['sku'] = $v["sku"];

    $pmd_buying_price = $v['buying_price'];
    $supplier_gross_price = ($v['supplier_gross_price'] == 0 ? 1:$v['supplier_gross_price']);


    $debter_selling_price = roundValue((1 - ($discount_percentage/100)) * $supplier_gross_price);
    $deb_margin_on_buying_price = roundValue((($debter_selling_price - $pmd_buying_price)/$pmd_buying_price) * 100);
    $deb_margin_on_selling_price = roundValue((($debter_selling_price - $pmd_buying_price)/$debter_selling_price) * 100);
    $deb_discount_on_gross_price = $discount_percentage;


    $all_selected_data[$v['product_id']]['selling_price'] = $debter_selling_price;
    $all_selected_data[$v['product_id']]['profit_margin_bp'] = $deb_margin_on_buying_price;
    $all_selected_data[$v['product_id']]['profit_margin_sp'] = $deb_margin_on_selling_price;
    $all_selected_data[$v['product_id']]['discount_on_gross'] = $deb_discount_on_gross_price;

  }
  $updated_recs = bulkUpdateProducts("debterprice",$all_selected_data,$common_data,$from,"Discount On Gross For ".$debter_number."");
}
$response_data['msg'] = "Products Updated:-".$updated_recs;
break;

case "multiple_update_debter_discount_on_gross_price":
  $debter_number = $_REQUEST['debter_number'];
  $allData = array_filter($_REQUEST['multipledebDiscountOnGP']);
  $from = "From Multiple Update Debter Discount On Gross Price";
  $common_data = array();
  $common_data["debter_number"] = $debter_number;

  if(count($allData) > 0) {
  $all_selected_data = array();
  foreach($allData as $k=>$v) {
    $all_selected_data[$v["product_id"]]['product_id'] = $v["product_id"];
    $all_selected_data[$v["product_id"]]['sku'] = $v["sku"];

    $pmd_buying_price = $v['buying_price'];
    $supplier_gross_price = ($v['supplier_gross_price'] == 0 ? 1:$v['supplier_gross_price']);


    $debter_selling_price = roundValue((1 - ($v['deb_d_gp']/100)) * $supplier_gross_price);
    $deb_margin_on_buying_price = roundValue((($debter_selling_price - $pmd_buying_price)/$pmd_buying_price) * 100);
    $deb_margin_on_selling_price = roundValue((($debter_selling_price - $pmd_buying_price)/$debter_selling_price) * 100);
    $deb_discount_on_gross_price = $v['deb_d_gp'];


    $all_selected_data[$v['product_id']]['selling_price'] = $debter_selling_price;
    $all_selected_data[$v['product_id']]['profit_margin_bp'] = $deb_margin_on_buying_price;
    $all_selected_data[$v['product_id']]['profit_margin_sp'] = $deb_margin_on_selling_price;
    $all_selected_data[$v['product_id']]['discount_on_gross'] = $deb_discount_on_gross_price;

  }
  $updated_recs = bulkUpdateProducts("debterprice",$all_selected_data,$common_data,$from,"Multiple Discount On Gross For ".$debter_number."");
}
$response_data['msg'] = "Products Updated:-".$updated_recs;

break;

  // Debter bulk update ends



case "bulk_update_selling_price":
$isAllChecked = $_REQUEST['isAllChecked'];
$selling_percentage = $_REQUEST['selling_percentage'];
$positive_or_negative = $_REQUEST['positive_or_negative'];
$common_data = array();

$from = "";
if($isAllChecked == 1) {
    // Check All ignore paging
  $sql_chk_all = getChkAllSql(); 
  $result_chk_all = $conn->query($sql_chk_all);
  $allData = $result_chk_all->fetch_all(MYSQLI_ASSOC);
  $from = "From Check All";
} else {
  $allData = $_REQUEST['sellingPrices'];
  $from = "From Multiple Select";
} 


if(count($allData) > 0) {
  $all_selected_data = array();
  foreach($allData as $k=>$v) {
    $all_selected_data[$v["product_id"]]['product_id'] = $v["product_id"];
    $all_selected_data[$v["product_id"]]['sku'] = $v["sku"];


    $actual_selling_price = $v['selling_price'];
    $pmd_buying_price = $v['buying_price'];
    $webshop_selling_price = $v['gyzs_selling_price'];
    $supplier_gross_price = ($v['supplier_gross_price'] == 0 ? 1:$v['supplier_gross_price']);


    if($positive_or_negative == "+") {
      $new_selling_price = roundValue((1 + ($selling_percentage/100)) * $actual_selling_price);
    } else if($positive_or_negative == "-") {
      $new_selling_price =  roundValue($actual_selling_price - (($selling_percentage/100) * $actual_selling_price));
    }


    $profit_margin = roundValue((($new_selling_price - $pmd_buying_price)/$pmd_buying_price) * 100);
    $profit_margin_sp = roundValue((($new_selling_price - $pmd_buying_price)/$new_selling_price) * 100);
    $percentage_increase = roundValue((($new_selling_price - $webshop_selling_price)/$webshop_selling_price) * 100);
    $discount_percentage = roundValue((1 - ($new_selling_price/$supplier_gross_price)) * 100);

    $all_selected_data[$v['product_id']]['selling_price'] = $new_selling_price;
    $all_selected_data[$v['product_id']]['profit_margin_bp'] = $profit_margin;
    $all_selected_data[$v['product_id']]['profit_margin_sp'] = $profit_margin_sp;
    $all_selected_data[$v['product_id']]['discount_on_gross'] = $discount_percentage;
    $all_selected_data[$v['product_id']]['percentage_increase'] = $percentage_increase;

    // Create History Array //
    $all_selected_data[$v['product_id']]['old_net_unit_price'] = $v['webshop_supplier_buying_price'];
    $all_selected_data[$v['product_id']]['old_gross_unit_price'] = $v['webshop_supplier_gross_price'];
    $all_selected_data[$v['product_id']]['old_idealeverpakking'] = $v['webshop_idealeverpakking'];
    $all_selected_data[$v['product_id']]['old_afwijkenidealeverpakking'] = $v['webshop_afwijkenidealeverpakking'];
    $all_selected_data[$v['product_id']]['old_buying_price'] = $v['webshop_supplier_buying_price'];
    $all_selected_data[$v['product_id']]['old_selling_price'] = $v['gyzs_selling_price'];

    $all_selected_data[$v['product_id']]['new_net_unit_price'] = $pmd_buying_price;
    $all_selected_data[$v['product_id']]['new_gross_unit_price'] = $v['supplier_gross_price'];
    $all_selected_data[$v['product_id']]['new_idealeverpakking'] = $v['idealeverpakking'];
    $all_selected_data[$v['product_id']]['new_afwijkenidealeverpakking'] = $v['afwijkenidealeverpakking'];
    $all_selected_data[$v['product_id']]['new_buying_price'] = $pmd_buying_price;
    $all_selected_data[$v['product_id']]['new_selling_price'] = $new_selling_price;
    // Create History Array //

  }
  $updated_recs = bulkUpdateProducts("webshopprice",$all_selected_data,$common_data,$from,"Selling Price");
}
$response_data['msg'] = "Products Updated:-".$updated_recs;
break;

// Update Multiples
case "update_multiple_selling_prices":
  $allData = array_filter($_REQUEST['multipleSellingPrices']);

  $from = "From Multiple Selling Price Update";
  $common_data = array();
  
  if(count($allData) > 0) {
    $all_selected_data = array();
    foreach($allData as $k=>$v) {
      $all_selected_data[$v["product_id"]]['product_id'] = $v["product_id"];
      $all_selected_data[$v["product_id"]]['sku'] = $v["sku"];


      $new_selling_price = $v['selling_price'];
      $pmd_buying_price = $v['buying_price'];
      $webshop_selling_price = $v['gyzs_selling_price'];
      $supplier_gross_price = ($v['supplier_gross_price'] == 0 ? 1:$v['supplier_gross_price']);


      $profit_margin = roundValue((($new_selling_price - $pmd_buying_price)/$pmd_buying_price) * 100);
      $profit_margin_sp = roundValue((($new_selling_price - $pmd_buying_price)/$new_selling_price) * 100);
      $percentage_increase = roundValue((($new_selling_price - $webshop_selling_price)/$webshop_selling_price) * 100);
      $discount_percentage = roundValue((1 - ($new_selling_price/$supplier_gross_price)) * 100);

      $all_selected_data[$v['product_id']]['selling_price'] = $new_selling_price;
      $all_selected_data[$v['product_id']]['profit_margin_bp'] = $profit_margin;
      $all_selected_data[$v['product_id']]['profit_margin_sp'] = $profit_margin_sp;
      $all_selected_data[$v['product_id']]['discount_on_gross'] = $discount_percentage;
      $all_selected_data[$v['product_id']]['percentage_increase'] = $percentage_increase;

      // Create History Array //
      $all_selected_data[$v['product_id']]['old_net_unit_price'] = $v['webshop_supplier_buying_price'];
      $all_selected_data[$v['product_id']]['old_gross_unit_price'] = $v['webshop_supplier_gross_price'];
      $all_selected_data[$v['product_id']]['old_idealeverpakking'] = $v['webshop_idealeverpakking'];
      $all_selected_data[$v['product_id']]['old_afwijkenidealeverpakking'] = $v['webshop_afwijkenidealeverpakking'];
      $all_selected_data[$v['product_id']]['old_buying_price'] = $v['webshop_supplier_buying_price'];
      $all_selected_data[$v['product_id']]['old_selling_price'] = $v['gyzs_selling_price'];

      $all_selected_data[$v['product_id']]['new_net_unit_price'] = $pmd_buying_price;
      $all_selected_data[$v['product_id']]['new_gross_unit_price'] = $v['supplier_gross_price'];
      $all_selected_data[$v['product_id']]['new_idealeverpakking'] = $v['idealeverpakking'];
      $all_selected_data[$v['product_id']]['new_afwijkenidealeverpakking'] = $v['afwijkenidealeverpakking'];
      $all_selected_data[$v['product_id']]['new_buying_price'] = $pmd_buying_price;
      $all_selected_data[$v['product_id']]['new_selling_price'] = $new_selling_price;
      // Create History Array //

    }
    $updated_recs = bulkUpdateProducts("webshopprice",$all_selected_data,$common_data,$from,"Selling Price");
  }
  $response_data['msg'] = "Products Updated:-".$updated_recs;
break;

case "update_multiple_profit_margin":
  $allData = array_filter($_REQUEST['multipleprofitMargins']);
  $from = "From Multiple Profit Margin On BP Update";
  $common_data = array();

  if(count($allData) > 0) {
    $all_selected_data = array();
    foreach($allData as $k=>$v) {
      $all_selected_data[$v["product_id"]]['product_id'] = $v["product_id"];
      $all_selected_data[$v["product_id"]]['sku'] = $v["sku"];

      $pmd_buying_price = $v['buying_price'];
      $supplier_gross_price = ($v['supplier_gross_price'] == 0 ? 1:$v['supplier_gross_price']);
      $webshop_selling_price = $v['gyzs_selling_price'];

      $selling_price = roundValue((1 + ($v['profit_percentage']/100)) * $pmd_buying_price);
      $profit_margin_bp = $v['profit_percentage'];
      $profit_margin_sp = roundValue((($selling_price - $pmd_buying_price)/$selling_price) * 100);
      $discount_percentage = roundValue((1 - ($selling_price/$supplier_gross_price)) * 100);
      $percentage_increase = roundValue((($selling_price - $webshop_selling_price)/$webshop_selling_price) * 100);

      $all_selected_data[$v['product_id']]['selling_price'] = $selling_price;
      $all_selected_data[$v['product_id']]['profit_margin_bp'] = $profit_margin_bp;
      $all_selected_data[$v['product_id']]['profit_margin_sp'] = $profit_margin_sp;
      $all_selected_data[$v['product_id']]['discount_on_gross'] = $discount_percentage;
      $all_selected_data[$v['product_id']]['percentage_increase'] = $percentage_increase;
      
      // Create History Array //
      $all_selected_data[$v['product_id']]['old_net_unit_price'] = $v['webshop_supplier_buying_price'];
      $all_selected_data[$v['product_id']]['old_gross_unit_price'] = $v['webshop_supplier_gross_price'];
      $all_selected_data[$v['product_id']]['old_idealeverpakking'] = $v['webshop_idealeverpakking'];
      $all_selected_data[$v['product_id']]['old_afwijkenidealeverpakking'] = $v['webshop_afwijkenidealeverpakking'];
      $all_selected_data[$v['product_id']]['old_buying_price'] = $v['webshop_supplier_buying_price'];
      $all_selected_data[$v['product_id']]['old_selling_price'] = $v['gyzs_selling_price'];

      $all_selected_data[$v['product_id']]['new_net_unit_price'] = $pmd_buying_price;
      $all_selected_data[$v['product_id']]['new_gross_unit_price'] = $v['supplier_gross_price'];
      $all_selected_data[$v['product_id']]['new_idealeverpakking'] = $v['idealeverpakking'];
      $all_selected_data[$v['product_id']]['new_afwijkenidealeverpakking'] = $v['afwijkenidealeverpakking'];
      $all_selected_data[$v['product_id']]['new_buying_price'] = $pmd_buying_price;
      $all_selected_data[$v['product_id']]['new_selling_price'] = $selling_price;
      // Create History Array //
    }
    $updated_recs = bulkUpdateProducts("webshopprice",$all_selected_data,$common_data,$from,"Profit Margin (BP)");
  }
$response_data['msg'] = "Products Updated:-".$updated_recs;
break;

case "update_multiple_profit_margin_on_sp":
  $allData = array_filter($_REQUEST['multipleprofitMarginsSP']);
  $from = "From Multiple Profit Margin On SP Update";
  $common_data = array();

  if(count($allData) > 0) {
  $all_selected_data = array();
  foreach($allData as $k=>$v) {
    $all_selected_data[$v["product_id"]]['product_id'] = $v["product_id"];
    $all_selected_data[$v["product_id"]]['sku'] = $v["sku"];

    $pmd_buying_price = $v['buying_price'];
    $supplier_gross_price = ($v['supplier_gross_price'] == 0 ? 1:$v['supplier_gross_price']);
    $webshop_selling_price = $v['gyzs_selling_price'];

    $selling_price = roundValue($pmd_buying_price / (1-($v['profit_percentage_selling_price']/100)));
    $profit_margin_bp = roundValue((($selling_price - $pmd_buying_price)/$pmd_buying_price) * 100);
    $profit_margin_sp = $v['profit_percentage_selling_price'];
    $percentage_increase = roundValue((($selling_price - $webshop_selling_price)/$webshop_selling_price) * 100);
    $discount_percentage = roundValue((1 - ($selling_price/$supplier_gross_price)) * 100);

    $all_selected_data[$v['product_id']]['selling_price'] = $selling_price;
    $all_selected_data[$v['product_id']]['profit_margin_bp'] = $profit_margin_bp;
    $all_selected_data[$v['product_id']]['profit_margin_sp'] = $profit_margin_sp;
    $all_selected_data[$v['product_id']]['discount_on_gross'] = $discount_percentage;
    $all_selected_data[$v['product_id']]['percentage_increase'] = $percentage_increase;

    // Create History Array //
    $all_selected_data[$v['product_id']]['old_net_unit_price'] = $v['webshop_supplier_buying_price'];
    $all_selected_data[$v['product_id']]['old_gross_unit_price'] = $v['webshop_supplier_gross_price'];
    $all_selected_data[$v['product_id']]['old_idealeverpakking'] = $v['webshop_idealeverpakking'];
    $all_selected_data[$v['product_id']]['old_afwijkenidealeverpakking'] = $v['webshop_afwijkenidealeverpakking'];
    $all_selected_data[$v['product_id']]['old_buying_price'] = $v['webshop_supplier_buying_price'];
    $all_selected_data[$v['product_id']]['old_selling_price'] = $v['gyzs_selling_price'];

    $all_selected_data[$v['product_id']]['new_net_unit_price'] = $pmd_buying_price;
    $all_selected_data[$v['product_id']]['new_gross_unit_price'] = $v['supplier_gross_price'];
    $all_selected_data[$v['product_id']]['new_idealeverpakking'] = $v['idealeverpakking'];
    $all_selected_data[$v['product_id']]['new_afwijkenidealeverpakking'] = $v['afwijkenidealeverpakking'];
    $all_selected_data[$v['product_id']]['new_buying_price'] = $pmd_buying_price;
    $all_selected_data[$v['product_id']]['new_selling_price'] = $selling_price;
    // Create History Array //

  }
  $updated_recs = bulkUpdateProducts("webshopprice",$all_selected_data,$common_data,$from,"Profit Margin (SP)");
}
$response_data['msg'] = "Products Updated:-".$updated_recs;
break;

case "update_multiple_discount":
  $allData = array_filter($_REQUEST['multiplediscountPercentages']);
  $from = "From Multiple Discount On Gross Update";
  $common_data = array();

  if(count($allData) > 0) {
  $all_selected_data = array();
  foreach($allData as $k=>$v) {
    $all_selected_data[$v["product_id"]]['product_id'] = $v["product_id"];
    $all_selected_data[$v["product_id"]]['sku'] = $v["sku"];

    $pmd_buying_price = $v['buying_price'];
    $supplier_gross_price = ($v['supplier_gross_price'] == 0 ? 1:$v['supplier_gross_price']);
    $webshop_selling_price = $v['gyzs_selling_price'];

    $selling_price = roundValue((1 - ($v['discount_on_gross_price']/100)) * $supplier_gross_price);
    $percentage_increase = roundValue((($selling_price - $webshop_selling_price)/$webshop_selling_price) * 100);
    $profit_margin = roundValue((($selling_price - $pmd_buying_price)/$pmd_buying_price) * 100);
    $profit_margin_sp = roundValue((($selling_price - $pmd_buying_price)/$selling_price) * 100);


    $all_selected_data[$v['product_id']]['selling_price'] = $selling_price;
    $all_selected_data[$v['product_id']]['profit_margin_bp'] = $profit_margin;
    $all_selected_data[$v['product_id']]['profit_margin_sp'] = $profit_margin_sp;
    $all_selected_data[$v['product_id']]['discount_on_gross'] = $v['discount_on_gross_price'];
    $all_selected_data[$v['product_id']]['percentage_increase'] = $percentage_increase;

    // Create History Array //
    $all_selected_data[$v['product_id']]['old_net_unit_price'] = $v['webshop_supplier_buying_price'];
    $all_selected_data[$v['product_id']]['old_gross_unit_price'] = $v['webshop_supplier_gross_price'];
    $all_selected_data[$v['product_id']]['old_idealeverpakking'] = $v['webshop_idealeverpakking'];
    $all_selected_data[$v['product_id']]['old_afwijkenidealeverpakking'] = $v['webshop_afwijkenidealeverpakking'];
    $all_selected_data[$v['product_id']]['old_buying_price'] = $v['webshop_supplier_buying_price'];
    $all_selected_data[$v['product_id']]['old_selling_price'] = $v['gyzs_selling_price'];

    $all_selected_data[$v['product_id']]['new_net_unit_price'] = $pmd_buying_price;
    $all_selected_data[$v['product_id']]['new_gross_unit_price'] = $v['supplier_gross_price'];
    $all_selected_data[$v['product_id']]['new_idealeverpakking'] = $v['idealeverpakking'];
    $all_selected_data[$v['product_id']]['new_afwijkenidealeverpakking'] = $v['afwijkenidealeverpakking'];
    $all_selected_data[$v['product_id']]['new_buying_price'] = $pmd_buying_price;
    $all_selected_data[$v['product_id']]['new_selling_price'] = $selling_price;
    // Create History Array //
    

  }
  $updated_recs = bulkUpdateProducts("webshopprice",$all_selected_data,$common_data,$from,"Discount On Gross");
}
$response_data['msg'] = "Products Updated:-".$updated_recs;


break;
// Update Multiples

case "bulk_update_profit_margin":
$isAllChecked = $_REQUEST['isAllChecked'];
$profit_margin = $_REQUEST['profit_margin'];
$common_data = array();
$from = "";
if($isAllChecked == 1) {
    // Check All ignore paging
  $sql_chk_all = getChkAllSql(); 
  $result_chk_all = $conn->query($sql_chk_all);
  $allData = $result_chk_all->fetch_all(MYSQLI_ASSOC);
  $from = "From Check All";
} else {
  $allData = $_REQUEST['profitMargins'];
  $from = "From Multiple Select";
} 

if(count($allData) > 0) {
  $all_selected_data = array();
  
  foreach($allData as $k=>$v) {
    $all_selected_data[$v["product_id"]]['product_id'] = $v["product_id"];
    $all_selected_data[$v["product_id"]]['sku'] = $v["sku"];

    $pmd_buying_price = $v['buying_price'];
    $supplier_gross_price = ($v['supplier_gross_price'] == 0 ? 1:$v['supplier_gross_price']);
    $webshop_selling_price = $v['gyzs_selling_price'];

    $selling_price = roundValue((1 + ($profit_margin/100)) * $pmd_buying_price);
    $profit_margin_bp = $profit_margin;
    $profit_margin_sp = roundValue((($selling_price - $pmd_buying_price)/$selling_price) * 100);
    $discount_percentage = roundValue((1 - ($selling_price/$supplier_gross_price)) * 100);
    $percentage_increase = roundValue((($selling_price - $webshop_selling_price)/$webshop_selling_price) * 100);

    $all_selected_data[$v['product_id']]['selling_price'] = $selling_price;
    $all_selected_data[$v['product_id']]['profit_margin_bp'] = $profit_margin_bp;
    $all_selected_data[$v['product_id']]['profit_margin_sp'] = $profit_margin_sp;
    $all_selected_data[$v['product_id']]['discount_on_gross'] = $discount_percentage;
    $all_selected_data[$v['product_id']]['percentage_increase'] = $percentage_increase;
    
    // Create History Array //
    $all_selected_data[$v['product_id']]['old_net_unit_price'] = $v['webshop_supplier_buying_price'];
    $all_selected_data[$v['product_id']]['old_gross_unit_price'] = $v['webshop_supplier_gross_price'];
    $all_selected_data[$v['product_id']]['old_idealeverpakking'] = $v['webshop_idealeverpakking'];
    $all_selected_data[$v['product_id']]['old_afwijkenidealeverpakking'] = $v['webshop_afwijkenidealeverpakking'];
    $all_selected_data[$v['product_id']]['old_buying_price'] = $v['webshop_supplier_buying_price'];
    $all_selected_data[$v['product_id']]['old_selling_price'] = $v['gyzs_selling_price'];

    $all_selected_data[$v['product_id']]['new_net_unit_price'] = $pmd_buying_price;
    $all_selected_data[$v['product_id']]['new_gross_unit_price'] = $v['supplier_gross_price'];
    $all_selected_data[$v['product_id']]['new_idealeverpakking'] = $v['idealeverpakking'];
    $all_selected_data[$v['product_id']]['new_afwijkenidealeverpakking'] = $v['afwijkenidealeverpakking'];
    $all_selected_data[$v['product_id']]['new_buying_price'] = $pmd_buying_price;
    $all_selected_data[$v['product_id']]['new_selling_price'] = $selling_price;
    // Create History Array //
  }
  $updated_recs = bulkUpdateProducts("webshopprice",$all_selected_data,$common_data,$from,"Profit Margin (BP)");
}
$response_data['msg'] = "Products Updated:-".$updated_recs;
break;


case "bulk_update_profit_margin_on_selling_price":

$isAllChecked = $_REQUEST['isAllChecked'];
$profit_margin = $_REQUEST['profit_margin'];
$common_data = array();
$from = "";
if($isAllChecked == 1) {
    // Check All ignore paging
  $sql_chk_all = getChkAllSql(); 
  $result_chk_all = $conn->query($sql_chk_all);
  $allData = $result_chk_all->fetch_all(MYSQLI_ASSOC);
  $from = "From Check All";
} else {
  $allData = $_REQUEST['profitMargins'];
  $from = "From Multiple Select";
} 

if(count($allData) > 0) {
  $all_selected_data = array();
  foreach($allData as $k=>$v) {
    $all_selected_data[$v["product_id"]]['product_id'] = $v["product_id"];
    $all_selected_data[$v["product_id"]]['sku'] = $v["sku"];

    $pmd_buying_price = $v['buying_price'];
    $supplier_gross_price = ($v['supplier_gross_price'] == 0 ? 1:$v['supplier_gross_price']);
    $webshop_selling_price = $v['gyzs_selling_price'];

    $selling_price = roundValue($pmd_buying_price / (1-($profit_margin/100)));
    $profit_margin_bp = roundValue((($selling_price - $pmd_buying_price)/$pmd_buying_price) * 100);
    $profit_margin_sp = $profit_margin;
    $percentage_increase = roundValue((($selling_price - $webshop_selling_price)/$webshop_selling_price) * 100);
    $discount_percentage = roundValue((1 - ($selling_price/$supplier_gross_price)) * 100);

    $all_selected_data[$v['product_id']]['selling_price'] = $selling_price;
    $all_selected_data[$v['product_id']]['profit_margin_bp'] = $profit_margin_bp;
    $all_selected_data[$v['product_id']]['profit_margin_sp'] = $profit_margin_sp;
    $all_selected_data[$v['product_id']]['discount_on_gross'] = $discount_percentage;
    $all_selected_data[$v['product_id']]['percentage_increase'] = $percentage_increase;

    // Create History Array //
    $all_selected_data[$v['product_id']]['old_net_unit_price'] = $v['webshop_supplier_buying_price'];
    $all_selected_data[$v['product_id']]['old_gross_unit_price'] = $v['webshop_supplier_gross_price'];
    $all_selected_data[$v['product_id']]['old_idealeverpakking'] = $v['webshop_idealeverpakking'];
    $all_selected_data[$v['product_id']]['old_afwijkenidealeverpakking'] = $v['webshop_afwijkenidealeverpakking'];
    $all_selected_data[$v['product_id']]['old_buying_price'] = $v['webshop_supplier_buying_price'];
    $all_selected_data[$v['product_id']]['old_selling_price'] = $v['gyzs_selling_price'];

    $all_selected_data[$v['product_id']]['new_net_unit_price'] = $pmd_buying_price;
    $all_selected_data[$v['product_id']]['new_gross_unit_price'] = $v['supplier_gross_price'];
    $all_selected_data[$v['product_id']]['new_idealeverpakking'] = $v['idealeverpakking'];
    $all_selected_data[$v['product_id']]['new_afwijkenidealeverpakking'] = $v['afwijkenidealeverpakking'];
    $all_selected_data[$v['product_id']]['new_buying_price'] = $pmd_buying_price;
    $all_selected_data[$v['product_id']]['new_selling_price'] = $selling_price;
    // Create History Array //

  }
  $updated_recs = bulkUpdateProducts("webshopprice",$all_selected_data,$common_data,$from,"Profit Margin (SP)");
}
$response_data['msg'] = "Products Updated:-".$updated_recs;
break;


case "bulk_update_discount":
$isAllChecked = $_REQUEST['isAllChecked'];
$discount_percentage = $_REQUEST['discount_percentage'];
$common_data = array();
$from = "";
if($isAllChecked == 1) {
    // Check All ignore paging
  $sql_chk_all = getChkAllSql(); 
  $result_chk_all = $conn->query($sql_chk_all);
  $allData = $result_chk_all->fetch_all(MYSQLI_ASSOC);
  $from = "From Check All";
} else {
  $allData = $_REQUEST['discountPercentages'];
  $from = "From Multiple Select";
} 

if(count($allData) > 0) {
  $all_selected_data = array();
  foreach($allData as $k=>$v) {
    $all_selected_data[$v["product_id"]]['product_id'] = $v["product_id"];
    $all_selected_data[$v["product_id"]]['sku'] = $v["sku"];

    $pmd_buying_price = $v['buying_price'];
    $supplier_gross_price = ($v['supplier_gross_price'] == 0 ? 1:$v['supplier_gross_price']);
    $webshop_selling_price = $v['gyzs_selling_price'];

    $selling_price = roundValue((1 - ($discount_percentage/100)) * $supplier_gross_price);
    $percentage_increase = roundValue((($selling_price - $webshop_selling_price)/$webshop_selling_price) * 100);
    $profit_margin = roundValue((($selling_price - $pmd_buying_price)/$pmd_buying_price) * 100);
    $profit_margin_sp = roundValue((($selling_price - $pmd_buying_price)/$selling_price) * 100);


    $all_selected_data[$v['product_id']]['selling_price'] = $selling_price;
    $all_selected_data[$v['product_id']]['profit_margin_bp'] = $profit_margin;
    $all_selected_data[$v['product_id']]['profit_margin_sp'] = $profit_margin_sp;
    $all_selected_data[$v['product_id']]['discount_on_gross'] = $discount_percentage;
    $all_selected_data[$v['product_id']]['percentage_increase'] = $percentage_increase;

    // Create History Array //
    $all_selected_data[$v['product_id']]['old_net_unit_price'] = $v['webshop_supplier_buying_price'];
    $all_selected_data[$v['product_id']]['old_gross_unit_price'] = $v['webshop_supplier_gross_price'];
    $all_selected_data[$v['product_id']]['old_idealeverpakking'] = $v['webshop_idealeverpakking'];
    $all_selected_data[$v['product_id']]['old_afwijkenidealeverpakking'] = $v['webshop_afwijkenidealeverpakking'];
    $all_selected_data[$v['product_id']]['old_buying_price'] = $v['webshop_supplier_buying_price'];
    $all_selected_data[$v['product_id']]['old_selling_price'] = $v['gyzs_selling_price'];

    $all_selected_data[$v['product_id']]['new_net_unit_price'] = $pmd_buying_price;
    $all_selected_data[$v['product_id']]['new_gross_unit_price'] = $v['supplier_gross_price'];
    $all_selected_data[$v['product_id']]['new_idealeverpakking'] = $v['idealeverpakking'];
    $all_selected_data[$v['product_id']]['new_afwijkenidealeverpakking'] = $v['afwijkenidealeverpakking'];
    $all_selected_data[$v['product_id']]['new_buying_price'] = $pmd_buying_price;
    $all_selected_data[$v['product_id']]['new_selling_price'] = $selling_price;
    // Create History Array //
    

  }
  $updated_recs = bulkUpdateProducts("webshopprice",$all_selected_data,$common_data,$from,"Discount On Gross");
}
$response_data['msg'] = "Products Updated:-".$updated_recs;
break;

case "category_brands":

$brands = array(); 
$selected_cats = $_POST['selected_cats'];

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



case "get_history":
$product_id = $_REQUEST['product_id'];
$change_status = $_REQUEST['change_status'];
if($change_status == 1) {
  $update_viewed_status = "UPDATE price_management_history SET is_viewed = 'Yes' WHERE product_id = '".$product_id."'";
  $conn->query($update_viewed_status);
  $response_data['msg'] = "Status Updated";
}

break;

case "read_all":
$update_viewed_status = "UPDATE price_management_history SET is_viewed = 'Yes'";
$conn->query($update_viewed_status);
$response_data['msg'] = "Status Updated";
break;

case "check_activate":
$sql = "SELECT COUNT(*) AS updated_count FROM price_management_data WHERE is_updated = '1'";
$result = $conn->query($sql);
$data = $result->fetch_assoc();
$response_data['msg'] = $data["updated_count"];
break;

case "confirm_activate":
$sql = "SELECT product_id FROM price_management_data WHERE is_updated = '1'";
if ($result = $conn->query($sql)) {
  $updated_product_ids = array();
  while ($row = $result->fetch_assoc()) {
    $updated_product_ids[] = $row['product_id'];
  }
  $confirm_activate = "UPDATE price_management_data SET is_activated = '1' WHERE product_id IN (".implode(",", $updated_product_ids).")";
  $conn->query($confirm_activate);
  $response_data['msg'] = "Success";
}
break;

case "get_averages":
$products_category_brand_info = getProductCategoryBrandInfo();
getAverageBasedOnSpMargin($products_category_brand_info);
$response_data['msg'] = "Success";
break;

case "undo_selling_price":

$isAllChecked = $_REQUEST['isAllChecked'];

$from = "";
if($isAllChecked == 1) {
    // Check All ignore paging
  $sql_chk_all = getChkAllSql(); 
  $result_chk_all = $conn->query($sql_chk_all);
  $allData = $result_chk_all->fetch_all(MYSQLI_ASSOC);
  $from = "Undo Selling Price From Check All";
} else {
  $allData = $_REQUEST['all_selected_records'];
  $from = "Undo Selling Price From Multiple Select";
} 
$common_data = array();
$all_selected_data = array();
if(count($allData) > 0) {
  foreach($allData as $k=>$v) {
    
    $get_previous_data_from_history = getPreviousSellingPriceFromHistory($conn, $v["product_id"]);
    if(isset($get_previous_data_from_history['new_selling_price'])) {


      if($v['afwijkenidealeverpakking'] == 0 && $get_previous_data_from_history['new_afwijkenidealeverpakking'] == 1) {
        $new_selling_price = $get_previous_data_from_history['new_selling_price'] * $v['idealeverpakking'];
      } else if($v['afwijkenidealeverpakking'] == 1 && $get_previous_data_from_history['new_afwijkenidealeverpakking'] == 0) {
        $new_selling_price = roundValue($get_previous_data_from_history['new_selling_price'] / $v['idealeverpakking']);
      } else {
        $new_selling_price = $get_previous_data_from_history['new_selling_price'];
      }
      

      //$pmd_buying_price = $get_previous_data_from_history['new_buying_price'];
      $pmd_buying_price = $v["buying_price"];
      $supplier_gross_price =($v['supplier_gross_price'] == 0 ? 1:$v['supplier_gross_price']);
      $webshop_selling_price = $v['gyzs_selling_price'];

      $profit_margin = roundValue((($new_selling_price - $pmd_buying_price)/$pmd_buying_price) * 100);
      $profit_margin_sp = roundValue((($new_selling_price - $pmd_buying_price)/$new_selling_price) * 100);
      $percentage_increase = roundValue((($new_selling_price - $webshop_selling_price)/$webshop_selling_price) * 100);
      $discount_percentage = roundValue((1 - ($new_selling_price/$supplier_gross_price)) * 100);

      $all_selected_data[$v["product_id"]]['product_id'] = $v["product_id"];
      $all_selected_data[$v["product_id"]]['sku'] = $v["sku"];

      $all_selected_data[$v['product_id']]['selling_price'] = $new_selling_price;
      $all_selected_data[$v['product_id']]['profit_margin_bp'] = $profit_margin;
      $all_selected_data[$v['product_id']]['profit_margin_sp'] = $profit_margin_sp;
      $all_selected_data[$v['product_id']]['discount_on_gross'] = $discount_percentage;
      $all_selected_data[$v['product_id']]['percentage_increase'] = $percentage_increase;

      // Create History Array //
      $all_selected_data[$v['product_id']]['old_net_unit_price'] = $v["webshop_supplier_buying_price"];
      $all_selected_data[$v['product_id']]['old_gross_unit_price'] = $v['webshop_supplier_gross_price'];
      $all_selected_data[$v['product_id']]['old_idealeverpakking'] = $v['webshop_idealeverpakking'];
      $all_selected_data[$v['product_id']]['old_afwijkenidealeverpakking'] = $v['webshop_afwijkenidealeverpakking'];
      $all_selected_data[$v['product_id']]['old_buying_price'] = $v["gyzs_buying_price"];
      $all_selected_data[$v['product_id']]['old_selling_price'] = $v['gyzs_selling_price'];

      $all_selected_data[$v['product_id']]['new_net_unit_price'] = $pmd_buying_price;
      $all_selected_data[$v['product_id']]['new_gross_unit_price'] = $v['supplier_gross_price'];
      $all_selected_data[$v['product_id']]['new_idealeverpakking'] = $v['idealeverpakking'];
      $all_selected_data[$v['product_id']]['new_afwijkenidealeverpakking'] = $v['afwijkenidealeverpakking'];
      $all_selected_data[$v['product_id']]['new_buying_price'] = $pmd_buying_price;
      $all_selected_data[$v['product_id']]['new_selling_price'] = $new_selling_price;
      // Create History Array //
    }
  }
    $updated_recs = bulkUpdateProducts("webshopprice",$all_selected_data,$common_data,$from,"Selling Price");
}
$response_data['msg'] = "Products Updated:-".$updated_recs;
break;

case "buying_price_graph":
$product_id = $_REQUEST['product_id'];
$sql = "SELECT * FROM price_management_history WHERE is_synced = 'No' AND buying_price_changed = 1 AND product_id = '".$product_id."'";
$bp_changed_products = array();

if ($result = $conn->query($sql)) {
  while ($row = $result->fetch_assoc()) {
    $bp_changed_products[$row['old_buying_price']] = $row['updated_date_time'];
  }
  $response_data['msg'] = $bp_changed_products;
}
break;
}

function getPreviousSellingPriceFromHistory($conn, $product_id) {
  $sql_undo_from_history = "SELECT * FROM price_management_history WHERE product_id = '".$product_id."' ORDER BY updated_date_time DESC LIMIT 1, 1";
  $ex_prev_sql = $conn->query($sql_undo_from_history);
  $get_previous_data = $ex_prev_sql->fetch_assoc();
  return $get_previous_data;
  //echo "<pre>";
  //print_r($get_previous_data);
}

function changeUpdateStatus($conn,$product_id) {
  $change_status = "UPDATE price_management_data SET is_updated = '1' WHERE product_id IN (".$product_id.")";
  $conn->query($change_status);
}

function resetUpdateStatus($conn,$product_id) {
  $change_status = "UPDATE price_management_data SET is_updated = '0', percentage_increase = '0', is_activated = '0' WHERE product_id IN (".$product_id.")";
  $conn->query($change_status);
}
function resetHistoryStatus($conn,$product_id) {
  $change_history_status = "UPDATE price_management_history SET is_viewed = 'Yes', is_synced = 'Yes', synced_date = NOW() WHERE product_id IN (".$product_id.")";
  $conn->query($change_history_status); 
}



function getAverageBasedOnSpMargin($products_category_brand_info) {
  global $conn;
  $_SESSION["pm_avg_per_cat"] = array();
  $_SESSION["pm_avg_per_brand"] = array();
  $_SESSION["pm_avg_per_cat_per_brand"] = array();
  $_SESSION["pm_product_cat_id_relation"] = array();
  $_SESSION["pm_product_categories"] = array();

  $sql = "SELECT product_id FROM price_management_data";
  $result = $conn->query($sql);
  $all_skus = $result->fetch_all();

  $get_chunks = array_chunk($all_skus,ROASINSERTCHUNK,true);
  $get_all_product_categories = getGyzsProductCategories();

  foreach($get_chunks as $c_k=>$c_d) {
    $rec = insertAveragesPmChunks($c_k,$c_d,$products_category_brand_info,$get_all_product_categories);
    $file_insert_pm_averages = "../pm_logs/insertpmaverages.txt";
    file_put_contents($file_insert_pm_averages,"".date("d-m-Y H:i:s")." Inserted Average PM Chunk (".$rec."):-".$c_k."\n",FILE_APPEND);
  }

  $f_product_cat_id = "../pm_logs/product_cat_id.txt";
  file_put_contents($f_product_cat_id, json_encode($_SESSION["pm_product_cat_id_relation"]));

  $f_pm_product_categories = "../pm_logs/pm_product_categories.txt";
  file_put_contents($f_pm_product_categories, json_encode($_SESSION["pm_product_categories"]));

}

function getCategoriesFromProductId($product_id,$get_all_product_categories) {
  $product_categories = array();

  if(isset($get_all_product_categories[$product_id]["1"])) {
    $product_categories[] = mb_convert_encoding($get_all_product_categories[$product_id]["1"], 'UTF-8', 'UTF-8');
  } else {
    $product_categories[] = "";
  }
  
  if(isset($get_all_product_categories[$product_id]["2"])) {
    $product_categories[] = mb_convert_encoding($get_all_product_categories[$product_id]["2"], 'UTF-8', 'UTF-8');
  } else {
     $product_categories[] = "";
  }

  if(isset($get_all_product_categories[$product_id]["3"])) {
    $product_categories[] = mb_convert_encoding($get_all_product_categories[$product_id]["3"], 'UTF-8', 'UTF-8');
  } else {
    $product_categories[] = "";
  }

  if(isset($get_all_product_categories[$product_id]["4"])) {
    $product_categories[] = mb_convert_encoding($get_all_product_categories[$product_id]["4"], 'UTF-8', 'UTF-8');
  } else {
    $product_categories[] = "";
  }

  return implode("/", array_filter($product_categories));
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


function insertAveragesPmChunks($c_k,$c_d,$products_category_brand_info,$get_all_product_categories) {
    global $conn;
    $all_pm_avg_data = array();

    $sql = "INSERT INTO price_management_averages (product_id,avg_category,avg_brand,avg_per_category_per_brand) VALUES ";
    foreach($c_d as $key_avg=>$data_avg) {
        $get_product_id = $data_avg[0];
        $cat_id = "";
        $cat_name = "";
        $brand_name = "";

            if(isset($products_category_brand_info[$get_product_id]["4"])) {
            $cat_id = $products_category_brand_info[$get_product_id]["4"]["cat_id"];
            $cat_name = $products_category_brand_info[$get_product_id]["4"]["cat_name"];
            $brand_name = $products_category_brand_info[$get_product_id]["4"]["brand_name"];
            } 
            else if (isset($products_category_brand_info[$get_product_id]["3"])) {
            $cat_id = $products_category_brand_info[$get_product_id]["3"]["cat_id"];
            $cat_name = $products_category_brand_info[$get_product_id]["3"]["cat_name"];
            $brand_name = $products_category_brand_info[$get_product_id]["3"]["brand_name"];
            } 
            else if (isset($products_category_brand_info[$get_product_id]["2"])) {
            $cat_id = $products_category_brand_info[$get_product_id]["2"]["cat_id"];
            $cat_name = $products_category_brand_info[$get_product_id]["2"]["cat_name"];
            $brand_name = $products_category_brand_info[$get_product_id]["2"]["brand_name"];
            }
            else if (isset($products_category_brand_info[$get_product_id]["1"])) {
            $cat_id = $products_category_brand_info[$get_product_id]["1"]["cat_id"];
            $cat_name = $products_category_brand_info[$get_product_id]["1"]["cat_name"];
            $brand_name = $products_category_brand_info[$get_product_id]["1"]["brand_name"];
            }

            $_SESSION["pm_product_cat_id_relation"][$get_product_id] = $cat_id;
            $_SESSION["pm_product_categories"][$get_product_id] = getCategoriesFromProductId($get_product_id,$get_all_product_categories);
            
            if(isset($_SESSION["pm_avg_per_cat"][$cat_id])) {
            $pm_averages_per_cat = $_SESSION["pm_avg_per_cat"][$cat_id];
            } else {
            $pm_averages_per_cat = calculateAverageBasedOnSpMargin($cat_id,"");
            $_SESSION["pm_avg_per_cat"][$cat_id] = $pm_averages_per_cat;
            //$file_pm_avg_per_cat = "pm_avg_per_cat.txt";
            //file_put_contents($file_pm_avg_per_cat, print_r($_SESSION["pm_avg_per_cat"],true));   
            }

            if(isset($_SESSION["pm_avg_per_brand"][$brand_name])) {
            $pm_avg_per_brand = $_SESSION["pm_avg_per_brand"][$brand_name];
            } else {
            $pm_avg_per_brand = calculateAverageBasedOnSpMargin("",$brand_name);
            $_SESSION["pm_avg_per_brand"][$brand_name] = $pm_avg_per_brand;
            //$file_pm_avg_per_brand = "pm_avg_per_brand.txt";
            //file_put_contents($file_pm_avg_per_brand, print_r($_SESSION["pm_avg_per_brand"],true));
            }

            if(isset($_SESSION["pm_avg_per_cat_per_brand"][$cat_id][$brand_name])) {
            $pm_avg_per_cat_per_brand = $_SESSION["pm_avg_per_cat_per_brand"][$cat_id][$brand_name];
            } else {
            $pm_avg_per_cat_per_brand = calculateAverageBasedOnSpMargin($cat_id,$brand_name);
            $_SESSION["pm_avg_per_cat_per_brand"][$cat_id][$brand_name] = $pm_avg_per_cat_per_brand;
            //$file_pm_avg_per_cat_per_brand = "pm_avg_per_cat_per_brand.txt";
            //file_put_contents($file_pm_avg_per_cat_per_brand, print_r($_SESSION["pm_avg_per_cat_per_brand"],true));
            }

            $all_pm_avg_data[] = "('".$get_product_id."','".$pm_averages_per_cat."','".$pm_avg_per_brand."','".$pm_avg_per_cat_per_brand."')";
    }

    $sql .= implode(",", $all_pm_avg_data) . " ON DUPLICATE KEY UPDATE product_id = VALUES(product_id), avg_category = VALUES(avg_category), avg_brand = VALUES(avg_brand), avg_per_category_per_brand = VALUES(avg_per_category_per_brand)";

    if($conn->query($sql)) {
      return count($c_d);
    } else {
      return "Error in average chunk  insertion:- ".mysqli_error($conn);
    }
}


function calculateAverageBasedOnSpMargin($selected_cats,$selected_brand) {

  global $conn;
  $allavgs = array();
  $total_orders_cnt = array();

  $extra_where = "";
  $make_join = "";
  if(!empty($selected_cats) && !empty($selected_brand)) {
    
    $extra_where = "WHERE mccp.category_id IN (".$selected_cats.") AND meaov.value = '".$selected_brand."'";
    
    $make_join = "INNER JOIN mage_catalog_category_product AS mccp ON mccp.product_id = mcpe.entity_id
          LEFT JOIN mage_catalog_product_entity_int AS mcpei ON mcpei.entity_id = pmd.product_id AND mcpei.attribute_id = '".MERK."'
          LEFT JOIN mage_eav_attribute_option_value AS meaov ON meaov.option_id = mcpei.value";

  } else if(!empty($selected_cats) && empty($selected_brand)) {

    $extra_where = "WHERE mccp.category_id IN (".$selected_cats.")";
    $make_join = "INNER JOIN mage_catalog_category_product AS mccp ON mccp.product_id = mcpe.entity_id";    

  } else if(!empty($selected_brand) && empty($selected_cats)) {
    $extra_where = "WHERE meaov.value = '".$selected_brand."'";

    $make_join = "LEFT JOIN mage_catalog_product_entity_int AS mcpei ON mcpei.entity_id = pmd.product_id AND mcpei.attribute_id = '".MERK."'
                  LEFT JOIN mage_eav_attribute_option_value AS meaov ON meaov.option_id = mcpei.value";
  }

  $sql = "SELECT SUM(CAST(pmd.profit_percentage_selling_price AS DECIMAL(10,4))) / COUNT(DISTINCT mcpe.entity_id) AS average_of_sp_margin
          FROM mage_catalog_product_entity AS mcpe
          INNER JOIN price_management_data AS pmd ON pmd.product_id = mcpe.entity_id
          ".$make_join."
          ".$extra_where." AND pmd.selling_price != '0'";

  $avg_data = $conn->query($sql);
  $avg_of_sp_margin = $avg_data->fetch_assoc();
  return $avg_of_sp_margin["average_of_sp_margin"];

}


function getProductCategoryBrandInfo() {
  global $conn;
  $sql = "SELECT 
            mccp.category_id,
            mccp.product_id,
            mccev.value AS cat_name,
            mcce.level,
            meaov.value AS brand_name
          FROM 
            mage_catalog_category_product AS mccp
          LEFT JOIN
          mage_catalog_product_entity_int AS mcpei ON mcpei.entity_id = mccp.product_id
          AND mcpei.attribute_id = '".MERK."'
          LEFT JOIN
          mage_eav_attribute_option_value AS meaov ON meaov.option_id = mcpei.value
          LEFT JOIN
            mage_catalog_category_entity_varchar AS mccev ON mccev.entity_id = mccp.category_id AND mccev.attribute_id = '".CATEGORYNAME_ATTRIBUTE_ID."' AND mccev.store_id = '0'
          LEFT JOIN
            mage_catalog_category_entity AS mcce ON mcce.entity_id = mccp.category_id";

  $products_with_cat_brand_info = array();
  if ($result = $conn->query($sql)) {
     while ($row = $result->fetch_assoc()) {
       $products_with_cat_brand_info[$row["product_id"]][$row["level"]]['cat_id'] = $row["category_id"];
       $products_with_cat_brand_info[$row["product_id"]][$row["level"]]['cat_name'] = $row["cat_name"];
       $products_with_cat_brand_info[$row["product_id"]][$row["level"]]['brand_name'] = $row["brand_name"];
     } 
  }
  return $products_with_cat_brand_info;  
}

function addInHistory($conn,$updateLogs,$chunk_index="") {

  $insertdata = implode(",", $updateLogs);

  $history = "INSERT INTO 
  price_management_history (
  product_id,
  old_net_unit_price,
  old_gross_unit_price,
  old_idealeverpakking,
  old_afwijkenidealeverpakking,
  old_buying_price,
  old_selling_price,
  new_net_unit_price,
  new_gross_unit_price,
  new_idealeverpakking,
  new_afwijkenidealeverpakking,
  new_buying_price,
  new_selling_price,
  updated_date_time,
  updated_by,
  is_viewed,
  fields_changed,
  buying_price_changed,
  is_synced,
  synced_date
  ) 
  VALUES ".$insertdata."";

  if($conn->query($history)) {
    $chunk_msg = "Processed Chunk (".$chunk_index.")\n";
    $chunk_msg .= "Added in history:-".count($updateLogs)."\n".print_r($updateLogs,true)."\n";
    historyLog($chunk_index,$chunk_msg);
  }
}

function historyLog($chunk_index,$chunk_msg) {
  $file_history_log = "../pm_logs/import_history_log.txt";
  file_put_contents($file_history_log,"".date("d-m-Y H:i:s")."\n".$chunk_msg."\n",FILE_APPEND);
}

function roundValue($val) {
  global $scale;
  return round($val,$scale);
}

function getCustomerGroups() {
  global $conn;
  $sql = "SELECT * FROM price_management_customer_groups ORDER BY magento_id";
  $all_customer_groups = array();

  if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
      $all_customer_groups[$row['magento_id']] = $row['customer_group_name'];
    }
  }
  return $all_customer_groups;
}


function bulkUpdateProducts($type,$data,$common_data,$log_type,$update_type) {
  $chunk_size = PMCHUNK;
  global $conn;
  $total_inserted_records = array();
  $chunk_data = array_chunk($data,$chunk_size);

  if(count($chunk_data)) {
    foreach($chunk_data as $chunk_index=>$chunk_values) {
      $all_col_data = array();
      $updated_product_ids = array();
      $all_history_data = array();

      if($type == "webshopprice") {
        $sql = "INSERT INTO price_management_data (product_id, sku, selling_price, profit_percentage_buying_price, profit_percentage_selling_price, percentage_increase, discount_on_gross) VALUES ";

        $history_sql = "INSERT INTO price_management_history (product_id,old_net_unit_price,old_gross_unit_price,old_idealeverpakking,old_afwijkenidealeverpakking,old_buying_price,old_selling_price,new_net_unit_price,new_gross_unit_price,new_idealeverpakking,new_afwijkenidealeverpakking,new_buying_price,new_selling_price,updated_date_time,updated_by,is_viewed,fields_changed,buying_price_changed) VALUES ";


      } else if($type == "debterprice") {
        $debter_number = $common_data["debter_number"];
        $c_d_id = "group_".$debter_number."_magento_id";
        $c_d_sp = "group_".$debter_number."_debter_selling_price";
        $c_d_m_bp = "group_".$debter_number."_margin_on_buying_price";
        $c_d_m_sp = "group_".$debter_number."_margin_on_selling_price";
        $c_d_o_gp = "group_".$debter_number."_discount_on_grossprice_b_on_deb_selling_price";
        $sql = "INSERT INTO price_management_data (product_id, sku, ".$c_d_id.", ".$c_d_sp.", ".$c_d_m_bp.", ".$c_d_m_sp.", ".$c_d_o_gp.") VALUES ";
      }

      foreach($chunk_values as $key=>$chunk_value) {

        if($type == "webshopprice") {

          $all_col_data[] = "('".$chunk_value['product_id']."', '".$chunk_value['sku']."', '".$chunk_value['selling_price']."', '".$chunk_value['profit_margin_bp']."', '".$chunk_value['profit_margin_sp']."', '".$chunk_value['percentage_increase']."', '".$chunk_value['discount_on_gross']."')";

          $fields_changed[0] = "new_selling_price";
          $buying_price_changed = 0;
          $all_history_data[] = "('".$chunk_value['product_id']."', '".$chunk_value['old_net_unit_price']."', '".$chunk_value['old_gross_unit_price']."', '".$chunk_value['old_idealeverpakking']."', '".$chunk_value['old_afwijkenidealeverpakking']."', '".$chunk_value['old_buying_price']."', '".$chunk_value['old_selling_price']."','".$chunk_value['new_net_unit_price']."', '".$chunk_value['new_gross_unit_price']."', '".$chunk_value['new_idealeverpakking']."', '".$chunk_value['new_afwijkenidealeverpakking']."', '".$chunk_value['new_buying_price']."', '".$chunk_value['new_selling_price']."','".date("Y-m-d H:i:s")."','Price Management','No','".json_encode($fields_changed)."','".$buying_price_changed."')";

        } else if($type == "debterprice") {


          $all_col_data[] = "('".$chunk_value['product_id']."', '".$chunk_value['sku']."', '".array_search($debter_number,getCustomerGroups())."', '".$chunk_value['selling_price']."', '".$chunk_value['profit_margin_bp']."', '".$chunk_value['profit_margin_sp']."', '".$chunk_value['discount_on_gross']."')";
        }
        $updated_product_ids[] = $chunk_value["product_id"];
      }

      if($type == "webshopprice") {
        $sql .= implode(",", $all_col_data) . " ON DUPLICATE KEY UPDATE selling_price = VALUES(selling_price),profit_percentage_buying_price = VALUES(profit_percentage_buying_price),profit_percentage_selling_price = VALUES(profit_percentage_selling_price),percentage_increase = VALUES(percentage_increase),discount_on_gross = VALUES(discount_on_gross)";

        if($conn->query($sql)) {
            
            //Insert in history
            $history_sql .= implode(",", $all_history_data);
            $conn->query($history_sql);
            //Insert in history

          bulkInsertLog($chunk_index,"Bulk Update ".$update_type." (".$log_type."):".count($chunk_values));
          changeUpdateStatus($conn, implode(",", $updated_product_ids));
          $total_inserted_records[] = count($chunk_values);
        } else {
          bulkInsertLog($chunk_index,"Bulk Update ".$update_type." Error (".$log_type."):".mysqli_error($conn));
        }


      } elseif($type == "debterprice") {
        $sql .= implode(",", $all_col_data) . " ON DUPLICATE KEY UPDATE ".$c_d_id." = VALUES(".$c_d_id."), ".$c_d_sp." = VALUES(".$c_d_sp."), ".$c_d_m_bp." = VALUES(".$c_d_m_bp."), ".$c_d_m_sp." = VALUES(".$c_d_m_sp."), ".$c_d_o_gp." = VALUES(".$c_d_o_gp.")";

        if($conn->query($sql)) {
          bulkInsertLog($chunk_index,"Bulk Update ".$update_type." Debter ".$debter_number." (".$log_type."):".count($chunk_values));
          changeUpdateStatus($conn, implode(",", $updated_product_ids));
          $total_inserted_records[] = count($chunk_values);
        } else {
          bulkInsertLog($chunk_index,"Bulk Update ".$update_type." Error For Debter ".$debter_number." (".$log_type."):".mysqli_error($conn));
        }

      }

    }
  }
  return array_sum($total_inserted_records);
}


function bulkInsertLog($chunk_index,$chunk_msg) {
  $file_pricechunks_log = "../pm_logs/price_update_log.txt";
  file_put_contents($file_pricechunks_log,"".date("d-m-Y H:i:s")." Updated Price Chunk (".$chunk_index."):-".$chunk_msg."\n",FILE_APPEND);
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

echo json_encode($response_data); 
?>
