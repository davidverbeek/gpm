<?php

require_once("../../app/Mage.php");
ini_set('memory_limit', '10G');
ini_set('max_execution_time', 300); // 5 minutes


include "config/config.php";
include "lib/SimpleXLSX.php";
umask(0);
Mage::app();
/* $txt = 'comparePolvoCostprijs';

Mage::app("default")->setCurrentStore(3);

Mage::log("Update ".$txt." Started.", NULL, 'update-'.$txt.'.log');

$storeId = Mage::app()->getStore()->getStoreId(); */



#######################################################
              #STEP 1
#######################################################

$data_xlsx = "prijslijst/Artikel_Polvo.xlsx";

$xlsx = SimpleXLSX::parse($data_xlsx);
const PMCHUNK = 1000;
$chunk_xlsx_data = array_chunk($xlsx->rows(), PMCHUNK);
$all_col_data=array();
$current_rec = 1;
$status = 'success';
$file = 'people.txt';
// Open the file to get existing content
$current = file_get_contents($file);
$supplier_id = '2';$current_rec=1;$progress_status=array();
$progress_file_path = $document_root_path."/import_export/invalid_rows.txt";
if(count($chunk_xlsx_data[0]) > 1 && count($chunk_xlsx_data)) {
       
        foreach($chunk_xlsx_data as $chunked_idx=>$chunked_xlsx_values) {
            $sql_2 = "INSERT INTO all_supplier_products(sku,ean,supplier_id,bp_per_piece,bp_min_order_qty, min_order_qty, afwijkenidealeverpakking, created_at, modified_at) VALUES ";

            $all_col_data=array();
            foreach($chunked_xlsx_values as $xl_row=>$xl_rowdata) {
                if(($chunked_idx == 0 && in_array($xl_row, [0,1]) || $xl_rowdata[2] == '')) {
                    continue;
                }

                if( !is_numeric($xl_rowdata[4]) || (!is_numeric($xl_rowdata[6])) || (!is_numeric($xl_rowdata[2])) || (!is_numeric($xl_rowdata[0]))) {
                    $progress_status['er_imp'][$current_rec] = "<div style='color:red;'><i class='fas fa-exclamation-triangle'></i>&nbsp; Row data not valid. (Row ".($current_rec).")</div>";
                    file_put_contents($progress_file_path, json_encode($progress_status));
                    continue;
                }
                
                $cost = $xl_rowdata[4];
                $min_order_qty = 1;
                $afwijkenidealeverpakking = 0;
                $bp_per_piece = $cost;

                if($xl_rowdata[6] && $xl_rowdata[6] == 1) {
                    $afwijkenidealeverpakking = 1;
                }
                   
                if($xl_rowdata[6] && $xl_rowdata[6] != 'st')  
                {
                    $min_order_qty = str_replace(',','',$xl_rowdata[6]);
                    $bp_per_piece = $cost / $min_order_qty;
                }


                $sku = $xl_rowdata[0];
                $bp_min_order_qty = $xl_rowdata[4];
                $ean = $xl_rowdata[2];

                $all_col_data[] = "('".$sku."','".$ean."' ,'".$supplier_id."','". $bp_per_piece."','".$bp_min_order_qty."', '".$min_order_qty."','".$afwijkenidealeverpakking."','".NOW()."', '".NOW()."')";
         
            }

            $sql_2 .= implode(',', $all_col_data);
            $modified_at = date('Y-m-d h:i:s');
            $sql_2 .= " ON DUPLICATE KEY UPDATE sku = VALUES(sku), ean=VALUES(ean),supplier_id = VALUES(supplier_id),bp_per_piece = VALUES(bp_per_piece),bp_min_order_qty = VALUES(bp_min_order_qty), min_order_qty = VALUES(min_order_qty), afwijkenidealeverpakking = VALUES(afwijkenidealeverpakking),modified_at = VALUES(modified_at)";
            
            // Write the contents back to the file
            file_put_contents($file, $sql_2);
            if (!$conn->query($sql_2)) {  
                echo $sql_2.'<br>'.$chunked_idx;            
                echo("Error description: " . $conn -> error." import stopped at this point");
                $status = 'failed'; exit;
            }
            $current_rec++;
		}
    //}
    }
    if ($status == 'success') {
        echo "Excel Data is saved successfully.";
    }