<?php


include "config/config.php";
include "define/constants.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, origin");
 error_reporting(E_ALL);
//error_reporting(1);
ini_set("memory_limit", "10G");
ini_set("max_execution_time", 0);

$data = json_decode(file_get_contents('php://input'), true);
$calling_page = $data[0];
$apply_rule_number = isset($data[1])?$data[1]:1;

global $conn;


if($calling_page == 'setting_page') {
  $condition = '';
  $method_of_updation = "Man";
} elseif($calling_page == 'setprices_page') {
  $condition = 'WHERE is_updated = 1';
  $method_of_updation = "Man";
} else {
  $condition = 'WHERE is_updated = 1';
  $method_of_updation = "Auto";
}

$SQL = "SELECT sku,product_id,selling_price,buying_price,profit_percentage_selling_price, webshop_selling_price, profit_percentage,CAST(pmd.gross_unit_price AS DECIMAL(10,4)) AS supplier_gross_price FROM price_management_data AS pmd ".$condition." WHERE sku in ('0152.02451', '0152.02452')";



$count = [];
$fixed_rule = [];
$many_rules = [];
$fixed_rule_2 = [];


$roas_SQL ="SELECT roas FROM pm_settings WHERE id = 1";
$setting_resource =$conn->query($roas_SQL);
$setting_row = $setting_resource->fetch_assoc();
$setting_row["roas"] = json_decode($setting_row["roas"], true);
if (!$conn->query($SQL)) {
  die('Query failed: ' . mysqli_error($conn).$SQL);
  exit;
}


applyFirstRule();
applySecondRule();

$fetch_response_data["execution_time"] = date("Y-m-d H:i:s");
$fetch_response_data["count"] = $count;
echo json_encode($fetch_response_data);


function applyFirstRule() {

  global $SQL, $conn, $count,$method_of_updation,$fixed_rule,$many_rules,$fixed_rule_2,$setting_row;
  $discount_rule_mid = getMagentoIdOfDiscountRule('one');
  $result = $conn->query($SQL);
 
$fixed_rule = $setting_row["roas"]["discount_rule_1_first"];
$many_rules = $setting_row["roas"]["discount_rule_one_range"];
 


$sku_discounted_fields = array();
while ($pm_product = $result->fetch_assoc()) {
  $sku_discounted_fields[$pm_product["sku"]]["product_id"] = $pm_product["product_id"];
  $sku_discounted_fields[$pm_product["sku"]]["sku"] = $pm_product["sku"];
  $bp = $pm_product["buying_price"];
  $sp = $pm_product["selling_price"];

  $profit = $sp - $bp;

  // write function to check marge_verkpr of each product lies in which range and get is discount percentage;
  $discount_percentage = checkDiscountRule($pm_product["profit_percentage_selling_price"]);


  $sku_discounted_fields[$pm_product["sku"]]["absolute_margin"] = $profit ;
  $discounted_sp = $sku_discounted_fields[$pm_product["sku"]]["discount_rule_1_sp"] = $profit*(1-($discount_percentage/100)) + $bp;
  $discounted_sp = round($discounted_sp, 4);
  $sku_discounted_fields[$pm_product["sku"]]["discount_rule_1_marge_verkpr"] = (($discounted_sp-$bp)/$discounted_sp)*100;
  $sku_discounted_fields[$pm_product["sku"]]["discount_rule_1_calculations"] = 
      <<<EOT
BP: {$bp}. SP: $sp.
Abs. Margin: {$profit} [$sp-$bp].
Discounted SP:{$discounted_sp} [((1-($discount_percentage/100))*$profit)+{$bp}]
EOT;
$sku_discounted_fields[$pm_product["sku"]]["discount_rule_1_executed_at"] = date("Y-m-d H:i:s").' - '.$method_of_updation;
$sku_discounted_fields[$pm_product["sku"]]["discount_rule_1_magento_id"] = $discount_rule_mid;
}


  $chunked_data = array_chunk($sku_discounted_fields, 1000);

  foreach($chunked_data as $key=>$chunk) {
   
    $sql = "UPDATE price_management_data SET ";
    
    
    $updated_sku = array();
    $col_1 = "absolute_margin = (CASE sku";
    $col_2 = "discount_rule_1_sp = (CASE sku";
    $col_3 = "discount_rule_1_marge_vkpr = (CASE sku";
    $col_4 = "discount_rule_1_cal = (CASE sku";
    $col_5 = "discount_rule_1_executed_at = (CASE sku";
    $col_6 = "discount_rule_1_magento_id = (CASE sku";

    foreach($chunk as $value) {
      $col_1 .= " WHEN '". $value["sku"] ."' THEN  ".round($value["absolute_margin"],4);
      $col_2 .= " WHEN '". $value["sku"] ."' THEN  ".round($value["discount_rule_1_sp"],4);
      $col_3 .= " WHEN '". $value["sku"] ."' THEN  ".round($value["discount_rule_1_marge_verkpr"],4);
      $col_4 .= " WHEN '". $value["sku"] ."' THEN  '".$value["discount_rule_1_calculations"]."'";
      $col_5 .= " WHEN '". $value["sku"] ."' THEN  '".$value["discount_rule_1_executed_at"]."'";
      $col_6 .= " WHEN '". $value["sku"] ."' THEN  '".$value["discount_rule_1_magento_id"]."'";
      $updated_sku[] = $value["sku"];
    }

    $col_1 .= " END)";
    $col_2 .= " END)";
    $col_3 .=  " END)";
    $col_4 .= " END)";
    $col_5 .=" END)";
    $col_6 .=" END)";

    $sql .= $col_1.", ".$col_2.", ".$col_3.", ".$col_4.",".$col_5.",".$col_6;
    $sku_single_quoted = "'" . implode ( "', '", $updated_sku ) . "'";
    $sql .= "WHERE sku IN (".$sku_single_quoted .")";
    if(!$conn->query($sql)) {
      bulkInsertLog($key,"Bulk Update Failed:".mysqli_error($conn)."\n".$sql);
    } else {
      bulkInsertLog($key,"Bulk Update Succeded of Rule#1:".count($chunk).' Records.',1);
      $count[]= mysqli_affected_rows($conn);
    };
  }
  

}//end applyFirstRule()


function checkDiscountRule($pm_product_marge_verkpr) {
  global $fixed_rule, $many_rules, $fixed_rule_2;
  $fixed_range = array_keys($fixed_rule);
  $range_array = explode("-", $fixed_range[0]);
  $discount_percentage = 0.0;

  if(count($fixed_rule_2) > 0) {
     $fixed_range_2 = array_keys($fixed_rule_2);
     $range_array_2 = explode("-", $fixed_range_2[0]);
  }

  if($pm_product_marge_verkpr >= $range_array[0] &&  $pm_product_marge_verkpr <= $range_array[1]) {
    $a = $fixed_range[0];
    $discount_percentage = (isset($fixed_range[0])) ? $fixed_rule[$a] : 0;
    
  } elseif (count($fixed_rule_2) > 0 && $pm_product_marge_verkpr >= $range_array_2[0] &&  $pm_product_marge_verkpr <= $range_array_2[1]) {
    $discount_percentage = $fixed_rule_2;
    $discount_percentage = $discount_percentage[0];
  } elseif(count($many_rules) > 0) {
    foreach(array_keys($many_rules) as $key=>$value) {
      $range_array_m = explode("-", $value);
      if($pm_product_marge_verkpr >= $range_array_m[0] &&  $pm_product_marge_verkpr <= $range_array_m[1] ) {
        $discount_percentage = $many_rules[$value];
        break;
      }
    }
  }
   $fp = fopen('monday2.json', 'w');
   fwrite($fp, json_encode($fixed_rule));
  return $discount_percentage;
}//end checkDiscountRule()

function bulkInsertLog($chunk_index, $chunk_msg, $rule=1) {
  file_put_contents('jthu.txt', $rule);

  global $apply_rule_number;
  $chunk_index = (int)$chunk_index+1;
  $file_pricechunks_log = "pm_logs/update_discount_rule.txt";
  file_put_contents($file_pricechunks_log,"".date("d-m-Y H:i:s")." Applied discount Rule #".$rule." on Chunk (".$chunk_index."):-".$chunk_msg."\n",FILE_APPEND);
}//end bulkInsertLog()

function getMagentoIdOfDiscountRule($discount_rule_number) {
  global $conn;
  $sql = "SELECT magento_id FROM discount_rule_magento_id WHERE discount_rule_number='".$discount_rule_number."'";
  if(!$conn->query($sql)) {
    echo "DB Error, could not query the database for getMagentoIdOfDiscountRule\n";
    echo 'MySQL Error: ' . mysqli_error($connection);
    exit;
  }
  $result = $conn->query($sql);
  $pm_product = $result->fetch_assoc();
  return $pm_product['magento_id'];
}



