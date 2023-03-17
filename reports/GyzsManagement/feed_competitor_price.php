<?php
ini_set('memory_limit', '1024M');
include "config/dbconfig.php";
include "define/constants.php";
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
  set_time_limit(500);

   $xml=simplexml_load_file("bigshopper_price_data.xml") or die("Error: Cannot create object");
   
   $array = json_decode(json_encode($xml->children()), true);
   $chunk_xml_data = array_chunk($array['item'], PMCHUNK);
   $table_column_names = array('product_sku', 'lowest_price', 'highest_price');
   $current_rec = $valid_count = 0;
   $col_data = "";
   $progress_status["total_records"] = count($xml->children());
   $progress_file_path = $document_root_path."/import_export/progress_bigshopper_feed.txt";
   if(count($chunk_xml_data)) {
      $sql = "";
      list($sql, $last_part_sql) = makeSqlDependingOnXl($table_column_names);
		foreach($chunk_xml_data as $chunked_idx=>$chunked_xml_values) {
         $all_col_data[] = $updated_product_skus[] = array();
         foreach($chunked_xml_values as $products) {
            $col_data = "";
            if((isset($products['product_id']) && !is_numeric($products['product_id'])) || (isset($products['laagste_prijs_excl_verzending']) && !is_numeric($products['laagste_prijs_excl_verzending'])) || (isset($products['hoogste_prijs_excl_verzending']) && !is_numeric($products['hoogste_prijs_excl_verzending'])) ) {
               $progress_status['er_imp'][$current_rec] = "<div style='color:red;'><i class='fas fa-exclamation-triangle'></i>&nbsp; Row data not valid. (Row ".($current_rec).")</div>";
               $current_rec++; continue;
            }
            $valid_count++;
            $all_col_data[] = "('".$products['product_id']."', '".$products['laagste_prijs_excl_verzending']."', '".$products['hoogste_prijs_excl_verzending']."')";
            
            $updated_product_skus[] = $products['product_id'];

            $progress_status["current_record"] = $current_rec;
            $progress_status["percentage"] = intval($current_rec/$progress_status["total_records"] * 100);
            $progress_status['er_imp']["er_summary"] = "<div>Imported ".$valid_count." Out Of ".($progress_status["total_records"]-1)."</div>";

            file_put_contents($progress_file_path, json_encode($progress_status));
            $current_rec++;
            usleep(50000);
         }
         if(count($all_col_data)) {
            $chunk_sql = $sql.implode(",", array_filter($all_col_data)) . $last_part_sql;
            $msg = "Bulk Insert:";
            if($chunked_idx == 0) {
               $truncate_sql = "Truncate TABLE bigshopper_prices";
               if($conn->query($truncate_sql)) {
                  $msg = "Truncated first then Bulk Insert:";
               } else {
                  $msg = "Failed to Truncate first But Bulk Insert:";
               }
            }
            if($conn->query($chunk_sql)) {
               bulkInsertLog($chunked_idx,$msg.count($chunked_xml_values));
               //changeUpdateStatus($conn, implode("','", $updated_product_skus));
            } else {
               bulkInsertLog($chunked_idx,"Bulk Insert Error:".mysqli_error($conn)."\n".$chunk_sql);
            }
         }
      }
   }
   

function makeSqlDependingOnXl($chunk_xlsx_heading_row) {
   $sql = "INSERT INTO bigshopper_prices (product_sku, lowest_price, highest_price ";
   $last_part_sql = " ON DUPLICATE KEY UPDATE";
   $back_part_cols = " product_sku = VALUES(product_sku), lowest_price = VALUES(lowest_price), highest_price = VALUES(highest_price)";

   $back_part_cols = rtrim($back_part_cols, ', ');
   $last_part_sql .= $back_part_cols;
   $sql .= ") VALUES ";

   return array($sql, $last_part_sql);
}//end makeSqlDependingOnXl();


function bulkInsertLog($chunk_index,$chunk_msg) {
   $file_pricechunks_log = "pm_logs/import_bigshopper_prices.txt";
   file_put_contents($file_pricechunks_log,"".date("d-m-Y H:i:s")." Inserted bigshopper xml (".$chunk_index."):-".$chunk_msg."\n",FILE_APPEND);
 }

 function changeUpdateStatus($conn,$product_sku) {
   global $conn;
   $change_status = "UPDATE bigshopper_prices SET is_updated = '1' WHERE product_sku IN ('".$product_sku."')";
   $conn->query($change_status);
   //file_put_contents("../try.txt",$change_status,FILE_APPEND);
 }


 /**
  * function to calculate min bigshopper price and webshop selling price diff percentage
  * To calculate max bigshopper price
  * 
  * */
 function calculateDiffPercentage($sku, $bigshopper_lp, $bigshopper_hp) {
   global $conn, $scale;
   //get webshop selling price of the given product
   $sql = "SELECT pmd.product_id AS product_id,
   pmd.sku AS sku,
   pmd.selling_price AS selling_price
   FROM 
   price_management_data AS pmd
   WHERE pmd.sku = {$sku}";

   $result = $conn->query($sql);
   if (!$result) {
      echo 'Could not run query: ' . $conn->error;
      exit;
   }

   
   $row = $result->fetch_assoc();

   if($row !== NULL){
      $selling_price = $row['selling_price'];

      $bigshopper_lp_diff_percent = roundValue((($selling_price-$bigshopper_lp)/$selling_price)*100);
      $bigshopper_hp_diff_percent = roundValue((($bigshopper_hp-$selling_price)/$selling_price)*100);
      $col_data = "('".$sku."', '".$bigshopper_lp."', '".$bigshopper_hp."', '".$bigshopper_lp_diff_percent."',
       '".$bigshopper_hp_diff_percent."')";   
   } else {
      $col_data = "('".$sku."', '".$bigshopper_lp."', '".$bigshopper_hp."', NULL, NULL)";
   }

   return $col_data;
 }//end calculateDiffPercentage()


 function roundValue($val) {
  global $scale;
  return round($val,$scale);
}