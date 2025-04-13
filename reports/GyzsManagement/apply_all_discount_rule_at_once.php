<?php


include "config/config.php";
include "define/constants.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, origin");
 error_reporting(E_ALL);
ini_set("memory_limit", "10G");
 ini_set('max_execution_time', '300');

$data = json_decode(file_get_contents('php://input'), true);
$calling_page = $data[0];
//$apply_rule_number = isset($data[1])?$data[1]:1;

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



class DivisionByZeroException extends Exception {
    public function __construct($message = "Division by zero error", $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}

//." AND sku='0152.02452'" AND sku IN ('0152.02452', '0152.02451', '0152.00186') 
$SQL = "SELECT sku,product_id,selling_price,buying_price,profit_percentage_selling_price, webshop_selling_price, profit_percentage,CAST(pmd.gross_unit_price AS DECIMAL(10,4)) AS supplier_gross_price FROM price_management_data AS pmd ".$condition. " AND sku IN ('0152.02451', '0152.02452', '0152.02453')";

//echo $SQL;exit;
$count = [];
$i=0;
$count_sku = [];
$fixed_rule = [];
$many_rules = [];
$fixed_rule_2 = [];


$roas_SQL ="SELECT roas FROM pm_settings WHERE id = 1";
$setting_resource =$conn->query($roas_SQL);
$setting_row = $setting_resource->fetch_assoc();
$setting_row["roas"] = json_decode($setting_row["roas"], true);



applyFirstRule();
$fetch_response_data["execution_time"] = date("Y-m-d H:i:s");
$fetch_response_data["count"] = $count;
echo json_encode($fetch_response_data);


function applyFirstRule() {

  global $SQL, $conn, $count,$method_of_updation,$fixed_rule,$many_rules,$fixed_rule_2,$setting_row;
  if (!$conn->query($SQL)) {
    die('Query failed: ' . mysqli_error($conn).$SQL);
    exit;
  }
  $result = $conn->query($SQL);

 

  $sku_discounted_fields = array();
  $discount_rule_mid_1 = getMagentoIdOfDiscountRule('one');
  $discount_rule_mid_2 = getMagentoIdOfDiscountRule('two');
  $discount_rule_mid_3 = getMagentoIdOfDiscountRule('three');
  $discount_rule_mid_4 = getMagentoIdOfDiscountRule('four');
  $discount_rule_mid_5 = getMagentoIdOfDiscountRule('five');

//ile_put_contents('lll.txt', $result.$SQL);
  $sku_discounted_fields_5 = $col_5 = array();
  $sku_discounted_fields_1 = $col_1 = array();
  $sku_discounted_fields_2 = $col_2 = array();
  $sku_discounted_fields_4 = $col_4 = array();
  $sku_discounted_fields_3 = $col_3 = array();

while ($pm_product = $result->fetch_assoc()) {

  // get a product details
  //  $sku_discounted_fields[$pm_product["sku"]]["product_id"] = $pm_product["product_id"];
  // $sku_discounted_fields[$pm_product["sku"]]["sku"] = $pm_product["sku"];
  $pmd_buying_price = $bp = $pm_product["buying_price"];
  $sp = $pm_product["selling_price"];

    try {
     
      // Apply Discount rule #1
      $profit = $sp - $bp;
       $fixed_rule = $setting_row["roas"]["discount_rule_1_first"];
       $many_rules = $setting_row["roas"]["discount_rule_one_range"];

      // write function to check marge_verkpr of each product lies in which range and get is discount percentage;
      $discount_percentage = checkDiscountRule($pm_product["profit_percentage_selling_price"]);

      if($discount_percentage !== '') {
      
      $sku_discounted_fields_1[$pm_product["sku"]]["absolute_margin"] = $profit ;

      $discounted_sp = $sku_discounted_fields_1[$pm_product["sku"]]["discount_rule_1_sp"] = $profit*(1-($discount_percentage/100)) + $bp;
      $discounted_sp = round($discounted_sp, 4);

      $denominator = $discounted_sp;
      $numerator = $discounted_sp-$bp;
      $result_hello = divide($numerator, $denominator, $pm_product["sku"], "discount_rule_1");

      $sku_discounted_fields_1[$pm_product["sku"]]["discount_rule_1_marge_vkpr"] = (($discounted_sp-$bp)/$discounted_sp)*100;

      $sku_discounted_fields_1[$pm_product["sku"]]["discount_rule_1_cal"] = 
      "
      BP: {$bp}. SP: $sp.
      Abs. Margin: {$profit} [$sp-$bp].
      Discounted SP: {$discounted_sp} [((1-($discount_percentage/100))*$profit)+{$bp}]
      ";     
    } else {
      $sku_discounted_fields_1[$pm_product["sku"]]["absolute_margin"] = 0.0000 ;
      $discounted_sp = $sku_discounted_fields_1[$pm_product["sku"]]["discount_rule_1_sp"] = 0.0000;
      $sku_discounted_fields_1[$pm_product["sku"]]["discount_rule_1_marge_vkpr"] = 0.0000;

      $sku_discounted_fields_1[$pm_product["sku"]]["discount_rule_1_cal"] = "Not-in-range";
      $sku_discounted_fields_1[$pm_product["sku"]]["discount_rule_1_magento_id"] = $discount_rule_mid_1;
      $sku_discounted_fields_1[$pm_product["sku"]]["discount_rule_executed_at"] = date("Y-m-d H:i:s").' - '.$method_of_updation;
    }
    $sku_discounted_fields_1[$pm_product["sku"]]["discount_rule_1_magento_id"] = $discount_rule_mid_1;
    $sku_discounted_fields_1[$pm_product["sku"]]["discount_rule_executed_at"] = date("Y-m-d H:i:s").' - '.$method_of_updation;

    $sku_discounted_fields_1[$pm_product["sku"]]["product_id"] = $pm_product["product_id"];
    $sku_discounted_fields_1[$pm_product["sku"]]["sku"] = $pm_product["sku"];
  //  $sku_discounted_fields_1[$pm_product["sku"]]["product_id"] = $discount_percentage;


      
    } catch (Exception $e) {
      unset($sku_discounted_fields_1[$pm_product["sku"]]);
      logError($e->getMessage());
    }

     

    try {

    $fixed_rule = $setting_row["roas"]["discount_rule_2_first_fixed"];
    $fixed_rule_2 = $setting_row["roas"]["discount_rule_2_second_fixed"];
    $many_rules = $setting_row["roas"]["discount_rule_2_range"];


    $new_ppsp = checkDiscountRule($pm_product["profit_percentage_selling_price"]);


        if($new_ppsp !== '') {

          $ppsp = (float)$new_ppsp;

          $denominator = (1 - ($ppsp / 100));
          $numerator = $pmd_buying_price;
          $result_hello = divide($numerator, $denominator, $pm_product["sku"], "discount_rule_2");

          $discounted_sp = ($pmd_buying_price / (1 - ($ppsp / 100)));
          $discounted_sp_display = round($discounted_sp,4);
          $sku_discounted_fields_2[$pm_product["sku"]]["discount_rule_2_sp"] = $discounted_sp;
          $sku_discounted_fields_2[$pm_product["sku"]]["discount_rule_2_marge_vkpr"] = round($ppsp,4);
          $sku_discounted_fields_2[$pm_product["sku"]]["sku"] = $pm_product["sku"];

          $sku_discounted_fields_2[$pm_product["sku"]]["discount_rule_2_cal"] = "
          BP: {$pmd_buying_price}, SP: $sp
          Marge Verkpr %: {$ppsp}
          Discounted SP: {$discounted_sp_display} [($pmd_buying_price / (1 - ($ppsp / 100)))]
          ";    
       } else {
          $sku_discounted_fields_2[$pm_product["sku"]]["discount_rule_2_sp"] = 0;
          $sku_discounted_fields_2[$pm_product["sku"]]["discount_rule_2_marge_vkpr"] = 0;
          $sku_discounted_fields_2[$pm_product["sku"]]["discount_rule_2_cal"] = "Not-in-range";          
       }
       $sku_discounted_fields_2[$pm_product["sku"]]["discount_rule_2_magento_id"] = $discount_rule_mid_2;
       $sku_discounted_fields_2[$pm_product["sku"]]["discount_rule_executed_at"] = date("Y-m-d H:i:s").' - '.$method_of_updation;
       $sku_discounted_fields_2[$pm_product["sku"]]["product_id"] = $pm_product["product_id"];
       $sku_discounted_fields_2[$pm_product["sku"]]["sku"] = $pm_product["sku"];

    
    } catch (Exception $e) {
      unset($sku_discounted_fields_2[$pm_product["sku"]]);
      logError($e->getMessage());
    }

   


    try { 
      $fixed_rule = $setting_row["roas"]["discount_rule_3_first_fixed"];
      $fixed_rule_2 = $setting_row["roas"]["discount_rule_3_second_fixed"];
      $many_rules = $setting_row["roas"]["discount_rule_3_range"];
      $new_pp = checkDiscountRule($pm_product["profit_percentage"]);
      if($new_pp !== '') {

        $discounted_sp = ((1 + ($new_pp / 100)) * $pmd_buying_price);
        $discounted_sp_display = round($discounted_sp,4);           
        $sku_discounted_fields_3[$pm_product["sku"]]["discount_rule_3_sp"] = round($discounted_sp,4);
        $sku_discounted_fields_3[$pm_product["sku"]]["discount_rule_3_marge_inkpr"] = round($new_pp,4);
        $sku_discounted_fields_3[$pm_product["sku"]]["discount_rule_3_cal"] = 
        "
        BP: {$pmd_buying_price}, SP: {$sp}
        Marge Inkpr %: {$new_pp}
        Discounted SP: {$discounted_sp_display} [((1 + ($new_pp / 100)) * $pmd_buying_price)]
        ";     
      } else {
        $sku_discounted_fields_3[$pm_product["sku"]]["discount_rule_3_sp"] = 0;
        $sku_discounted_fields_3[$pm_product["sku"]]["discount_rule_3_marge_inkpr"] = 0;
        $sku_discounted_fields_3[$pm_product["sku"]]["discount_rule_3_cal"] = 'Not-in-range';
      }
      $sku_discounted_fields_3[$pm_product["sku"]]["discount_rule_3_magento_id"] = $discount_rule_mid_3;
      $sku_discounted_fields_3[$pm_product["sku"]]["discount_rule_executed_at"] = date("Y-m-d H:i:s").' - '.$method_of_updation;

      $sku_discounted_fields_3[$pm_product["sku"]]["product_id"] = $pm_product["product_id"];
      $sku_discounted_fields_3[$pm_product["sku"]]["sku"] = $pm_product["sku"];     

    } catch (Exception $e) {
      unset($sku_discounted_fields_3[$pm_product["sku"]]);
      logError($e->getMessage());
    }

   

    try { 
    $fixed_rule = $setting_row["roas"]["discount_rule_4"];

    $new_pp = applyUpdate($pm_product, $fixed_rule);
    $sku_discounted_fields_4[$pm_product["sku"]]["discount_rule_4_sp"] = 0;

    $sku_discounted_fields_4[$pm_product["sku"]]["discount_rule_4_marge_inkpr"] = 0;        
    $sku_discounted_fields_4[$pm_product["sku"]]["discount_rule_4_magento_id"] = $discount_rule_mid_4;
    $sku_discounted_fields_4[$pm_product["sku"]]["discount_rule_4_cal"] = '';


    $new_pp = round($new_pp,4);
    $discounted_sp = ((1 + ($new_pp / 100)) * $pmd_buying_price); 
    $discounted_sp_display = round($discounted_sp,4);           
    $sku_discounted_fields_4[$pm_product["sku"]]["discount_rule_4_sp"] = $discounted_sp;
    $sku_discounted_fields_4[$pm_product["sku"]]["discount_rule_4_marge_inkpr"] = $new_pp;  
    $sku_discounted_fields_4[$pm_product["sku"]]["discount_rule_4_cal"] = "BP: {$pmd_buying_price}, SP: {$sp}
    Marge Inkpr %: {$new_pp}
    Discounted SP: {$discounted_sp_display} [((1 + ($new_pp / 100)) * $pmd_buying_price)]";
    $sku_discounted_fields_4[$pm_product["sku"]]["discount_rule_executed_at"] = date("Y-m-d H:i:s").' - '.$method_of_updation;
    $sku_discounted_fields_4[$pm_product["sku"]]["product_id"] = $pm_product["product_id"];
    $sku_discounted_fields_4[$pm_product["sku"]]["sku"] = $pm_product["sku"];
    } catch (Exception $e) {
      unset($sku_discounted_fields_4[$pm_product["sku"]]);
      logError($e->getMessage());
    }

 

    try { 

      $fixed_rule_2 = $setting_row["roas"]["discount_rule_5"]; 
      $new_ppsp = applyUpdate($pm_product, $fixed_rule_2);
      $ppsp = round((float)$new_ppsp,4);

      $denominator = (1 - ($ppsp / 100));
      $numerator = $pmd_buying_price;
      $result_hello = divide($numerator, $denominator, $pm_product["sku"], "discount_rule_5");


      $discounted_sp = ($pmd_buying_price / (1 - ($ppsp / 100)));
      $discounted_sp_display = round($discounted_sp,4);
      $sku_discounted_fields_5[$pm_product["sku"]]["discount_rule_5_sp"] = $discounted_sp;
      $sku_discounted_fields_5[$pm_product["sku"]]["discount_rule_5_marge_verkpr"] = $ppsp;        
      $sku_discounted_fields_5[$pm_product["sku"]]["discount_rule_5_magento_id"] = $discount_rule_mid_5;
      $sku_discounted_fields_5[$pm_product["sku"]]["discount_rule_5_cal"] = "BP: {$pmd_buying_price}, SP: $sp
      Marge Verkpr %: {$ppsp}
      Discounted SP: {$discounted_sp_display} [($pmd_buying_price / (1 - ($ppsp / 100)))]";

      $sku_discounted_fields_5[$pm_product["sku"]]["discount_rule_executed_at"] = date("Y-m-d H:i:s").' - '.$method_of_updation;

      $sku_discounted_fields_5[$pm_product["sku"]]["product_id"] = $pm_product["product_id"];
      $sku_discounted_fields_5[$pm_product["sku"]]["sku"] = $pm_product["sku"];
  } catch (Exception $e) {
    unset($sku_discounted_fields_5[$pm_product["sku"]]);
      logError($e->getMessage());
    }
}
file_put_contents('check_2106.txt', json_encode($sku_discounted_fields_1), FILE_APPEND);

if(count($sku_discounted_fields_1) > 0) {
  $col_1[] = "absolute_margin";
  $col_1[] = "discount_rule_1_sp";
  $col_1[] = "discount_rule_1_marge_vkpr";
  $col_1[] = "discount_rule_1_cal";
  $col_1[] = "discount_rule_1_magento_id";
  $col_1[] = "discount_rule_executed_at";
  InsertByChunk($col_1, $sku_discounted_fields_1, '1');



} else {
  file_put_contents('no_data.txt', $sku_discounted_fields_1);
}

if(count($sku_discounted_fields_2) > 0) {
  $col_2[] = "discount_rule_2_sp";
  $col_2[] = "discount_rule_2_marge_vkpr";
  $col_2[] = "discount_rule_2_cal";
  $col_2[] = "discount_rule_2_magento_id";
  $col_2[] = "discount_rule_executed_at";
  InsertByChunk($col_2, $sku_discounted_fields_2,'2');
} else {
  file_put_contents('no_data.txt', $sku_discounted_fields_2);
}

if(count($sku_discounted_fields_3) > 0) {
  $col_3[] = "discount_rule_3_sp";
  $col_3[] = "discount_rule_3_marge_inkpr";
  $col_3[] = "discount_rule_3_cal";
  $col_3[] = "discount_rule_3_magento_id";
  $col_3[] = "discount_rule_executed_at";
  InsertByChunk($col_3, $sku_discounted_fields_3,'3');
} else {
  file_put_contents('no_data.txt', $sku_discounted_fields_3);
}

if(count($sku_discounted_fields_4) > 0) {
  $col_4[] = "discount_rule_4_sp";
  $col_4[] = "discount_rule_4_marge_inkpr";
  $col_4[] = "discount_rule_4_cal";
  $col_4[] = "discount_rule_4_magento_id";
  $col_4[] = "discount_rule_executed_at";
  InsertByChunk($col_4, $sku_discounted_fields_4,'4');
} else {
  file_put_contents('no_data.txt', $sku_discounted_fields_4);
}

if(count($sku_discounted_fields_5) > 0) {
  $col_5[] = "discount_rule_5_sp";
  $col_5[] = "discount_rule_5_marge_verkpr";
  $col_5[] = "discount_rule_5_cal";
  $col_5[] = "discount_rule_5_magento_id";
  $col_5[] = "discount_rule_executed_at";
  InsertByChunk($col_5, $sku_discounted_fields_5,'5');
} else {
  file_put_contents('no_data.txt', $sku_discounted_fields_5);
}

$mergedArray = array_merge( $sku_discounted_fields_1,  $sku_discounted_fields_2,  $sku_discounted_fields_3,  $sku_discounted_fields_4,  $sku_discounted_fields_5);

// Get all keys
$allKeys = array_keys($mergedArray);

// Get unique keys
$uniqueKeys = array_unique($allKeys);

// Count unique keys
$count = count($uniqueKeys);


}//end applyFirstRule()




function checkDiscountRule($pm_product_marge_verkpr) {
  global $fixed_rule, $many_rules, $fixed_rule_2;

  $fixed_range = array_keys($fixed_rule);
  $range_array = explode("-", $fixed_range[0]);
 // $discount_percentage = 0.0;
  $discount_percentage = '';
  $any_rule = 'no';

  if(count($fixed_rule_2) > 0) {
     $fixed_range_2 = array_keys($fixed_rule_2);
     $range_array_2 = explode("-", $fixed_range_2[0]);
  }

  if($pm_product_marge_verkpr >= $range_array[0] &&  $pm_product_marge_verkpr <= $range_array[1]) {  
      $discount_percentage = (float)$fixed_rule[$fixed_range[0]];
      $any_rule = 'yes';    
  } elseif (count($fixed_rule_2) > 0 && $pm_product_marge_verkpr >= $range_array_2[0] &&  $pm_product_marge_verkpr <= $range_array_2[1]) {
    $discount_percentage = (float)$fixed_rule_2[$fixed_range_2[0]];
    $any_rule = 'yes';    
  } elseif(count($many_rules) > 0) {
    foreach(array_keys($many_rules) as $key=>$value) {
      $range_array_m = explode("-", $value);
      if($pm_product_marge_verkpr >= $range_array_m[0] &&  $pm_product_marge_verkpr <= $range_array_m[1] ) {
        $discount_percentage = (float)$many_rules[$value];
        $any_rule = 'yes';    
        break;
      }
    }
  }
   
  return $discount_percentage;
}//end checkDiscountRule()

function bulkInsertLog($chunk_index, $chunk_msg) {

  //global $apply_rule_number;
  $chunk_index = (int)$chunk_index+1;
  $file_pricechunks_log = "pm_logs/update_discount_rule.txt";
  file_put_contents($file_pricechunks_log,"".date("d-m-Y H:i:s")."(".$chunk_index."):-".$chunk_msg."\n",FILE_APPEND);
}//end bulkInsertLog()


function InsertByChunk($col_array ,$productData, $rule_number='1') {
 
  global $conn, $count,$i;

  $chunked_data = array_chunk($productData, 1);
//$i = 0;
  foreach($chunked_data as $key=>$chunk) {
  //  file_put_contents('jju.txt', $i, FILE_APPEND);

    $sql = "UPDATE price_management_data SET ";
    $updated_sku = array();

    foreach($col_array as $key_db_col_for_sql=>$val) { 
      $col_{$key_db_col_for_sql} = "";   
      $col_{$key_db_col_for_sql} .= $val." = (CASE sku ";
    }

    foreach($chunk as $value) {

      foreach($col_array as $key_db_col_for_sql=>$val) {          
        $a = $value[$val];            
        $col_{$key_db_col_for_sql} .= " WHEN '". $value["sku"] ."' THEN  '{$a}'";
      }

      $updated_sku[] = $value["sku"];
    }

    foreach($col_array as $key_db_col=>$val) {
      $col_{$key_db_col} .= " END)";
      $sql .= $col_{$key_db_col}.", ";
    }
    $sql = rtrim($sql, ", ");

    $sku_single_quoted = "'" . implode ( "', '", $updated_sku ) . "'";
    $sql .= " WHERE sku IN (".$sku_single_quoted .")";
     

    if(!$conn->query($sql)) {
       bulkInsertLog($key,"Bulk Update failed #discount rule {$rule_number}".$conn->error."\n".$sql);        
      //echo json_encode(["msg"=>$conn->error]);
    } else {
      $i++;
      //$fetch_response_data["msg"] = "Affected rows (SELECT): %d\n".mysqli_affected_rows($conn);
      bulkInsertLog($key,"Bulk Update Succeeded {$i} #discount rule {$rule_number}:".count($chunk).' Records.');
      //$count[] = mysqli_affected_rows($conn);
      //$count[] =$count;
    };
  }
 

}

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


function applyUpdate($row, $update) {
    if ( $update['operation'] === 'increment') {        
      $percentage_increase = ($row[$update['columnName']] * $update['value'])/100;
      $new_pp = $row[$update['columnName']] + $percentage_increase;
    } else if ($update['operation'] === 'decrement') {
      $percentage_decrease = ( $row[$update['columnName']] * $update['value'])/100;
      $new_pp =  $row[$update['columnName']] - $percentage_decrease;
    } else {
      echo('Invalid operation:'. $update['operation']);
    }

    return $new_pp;
  }

//i will send product id
function logError($message) {
    $logFile = 'pm_logs/error_log.txt';
    $currentDate = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$currentDate] $message"."\n", FILE_APPEND);

}

function divide($numerator, $denominator, $sku, $rule) {
    if ($denominator == 0) {    
        throw new DivisionByZeroException("Cannot divide by zero: $numerator / $denominator"." for {$sku} and {$rule}");
    }
    return $numerator / $denominator;
}