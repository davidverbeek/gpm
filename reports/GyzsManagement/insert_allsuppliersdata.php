<?php

include "config/config.php";
include "define/constants.php";
 error_reporting(E_ALL);
//error_reporting(1);
ini_set("memory_limit", "10G");
ini_set("max_execution_time", 0);


// Path to the JSON file
$supplier_json_file = 'allsuppliersdata.json';

// $fileSize = filesize($supplier_json_file);
// echo "File size: $fileSize bytes\n";

// Read the JSON file
$supplier_json_data = file_get_contents($supplier_json_file);

// Decode JSON data into PHP array
$supplier_data_Array = json_decode($supplier_json_data, true);

// Check if data is decoded properly
if ($supplier_data_Array === null) {
    die("Failed to decode JSON data.");
}
    
$chunk_json_data = array_chunk($supplier_data_Array, PMCHUNK);
$progress_status = $upload_summary = array();
$current_rec = 1;
$progress_status["total_records"] = count($supplier_data_Array);
$valid_count = 0;
$progress_file_path = $document_root_path."/import_export/supplier_json_progress.txt";
if(count($chunk_json_data) > 0) {
    $sql = $last_part_sql = "";
    list($sql, $last_part_sql) = makeSqlDependingOnJson($chunk_json_data[0][0]);
    foreach($chunk_json_data as $chunked_idx=>$chunked_json_values) {
        $all_col_data = $updated_product_ids = array();
        $chunk_sql = "";
        foreach($chunked_json_values as $c_k=>$item) {
            /*  if((isset($item['product_id']) && !is_numeric($item['product_id'])) || (isset($item['supplier']) && is_numeric($item['supplier'])) || (isset($item['supplier_sku']) && !is_numeric($item['supplier_sku']))) {
            $progress_status['er_imp'][$current_rec] = "<div style='color:red;'><i class='fas fa-exclamation-triangle'></i>&nbsp; Row data not valid. (Row ".($current_rec).")</div>";
            $current_rec++; continue;
            }*/
            $valid_count++;

            $one_rowString = '';
            $join_cols_names = "(";

            $col_data = "'".$item["sku"]."', '".$item["product_id"]."', '".$item["supplier"]."', '".$item["supplier_sku"]."', '".$item["status"]."', '".$item["idealeverpakking"]."', '".$item["afwijkenidealeverpakking "]."', '".$item["verkoopeenheid"]."', '".$item["delivery_time"]."', '".$item["eancode"]."', '".$item["advise_price"]."', '".$item["net_price"]."', '".$item["currentdate"]."', '".$item["actual_supplier"]."', '".$item["present_in"]."'";

            $join_cols_names .= $col_data;
            $updated_product_ids[] = $item["product_id"];
            $join_cols_names .= ')';
            $all_col_data[] = $join_cols_names;
            $progress_status["current_record"] = $current_rec;
            $progress_status["percentage"] = intval($current_rec/$progress_status["total_records"] * 100);
            $progress_status['er_imp']["er_summary"] = "<div>Imported ".$valid_count." Out Of ".($progress_status["total_records"]-1)."</div>";
            //, FILE_APPEND
            file_put_contents($progress_file_path, json_encode($progress_status));
            $current_rec++;
            usleep(50000);
        }//one chunk ended

        if(count($all_col_data) > 0) {
            $chunk_sql = $sql.implode(",", $all_col_data) . $last_part_sql;


            //truncate first
            if($chunked_idx == 0) {
                $truncate_sql = "TRUNCATE TABLE all_suppliers_data_source";
                if($conn->query($truncate_sql)) {
                    $truncate_sql_format = "TRUNCATE TABLE suppliers_row_format";
                    bulkInsertLog($chunked_idx,"Truncated TABLE all_suppliers_data_source successfully");
                    if($conn->query($truncate_sql_format)) {
                        bulkInsertLog($chunked_idx,"Truncated TABLE suppliers_row_format successfully");
                    } else {
                        bulkInsertLog($chunked_idx,"Truncate failed:".mysqli_error($conn)."\n".$truncate_sql_format);
                    }
                } else {
                    bulkInsertLog($chunked_idx,"Truncate failed:".mysqli_error($conn)."\n".$truncate_sql);
                }
            }



            if($conn->query($chunk_sql)) {
                bulkInsertLog($chunked_idx,"Bulk Update:".count($chunked_json_values));    
            } else {
                bulkInsertLog($chunked_idx,"Bulk Update Error:".mysqli_error($conn)."\n".$chunk_sql);
            }
        }   
                            
    }
}

//print_r($supplier_data_Array);exit;


insertInRowFormat();

function makeSqlDependingOnJson($chunk_xlsx_heading_row) {
    $sql = "INSERT INTO all_suppliers_data_source (sku, product_id, supplier, supplier_sku, status, idealeverpakking, afwijkenidealeverpakking, verkoopeenheid, delivery_time, eancode, advise_price, net_price, currentdate, actual_supplier, is_present";
    $last_part_sql = " ON DUPLICATE KEY UPDATE";
    $back_part_cols = " sku = VALUES(sku), product_id = VALUES(product_id), supplier = VALUES(supplier), status = VALUES(status),idealeverpakking = VALUES(idealeverpakking),afwijkenidealeverpakking = VALUES(afwijkenidealeverpakking),verkoopeenheid = VALUES(verkoopeenheid),delivery_time = VALUES(delivery_time),eancode = VALUES(eancode),advise_price = VALUES(advise_price),net_price = VALUES(net_price),currentdate = VALUES(currentdate),actual_supplier = VALUES(actual_supplier), is_present = VALUES(is_present)";

    $last_part_sql .= $back_part_cols;
    $sql .= ") VALUES ";

    return array($sql, $last_part_sql);

}//end makeSqlDependingOnJson();


function bulkInsertLog($chunk_index,$chunk_msg) {
    global $document_root_path;
    $file_pricechunks_log =  "insert_supplier_json.txt";
    file_put_contents($file_pricechunks_log,"".date("d-m-Y H:i:s")." Updated Price Chunk (".$chunk_index."):-".$chunk_msg."\n", FILE_APPEND);
}