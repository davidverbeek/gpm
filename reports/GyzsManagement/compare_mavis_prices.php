<?php
require_once("../../app/Mage.php");
include "config/config.php";
include "lib/SimpleXLSX.php";
umask(0);
Mage::app();
ini_set('max_execution_time', 300); // 5 minutes

$txt = 'compareMavisCostprijs';

Mage::app("default")->setCurrentStore(3);

Mage::log("Update ".$txt." Started.", NULL, 'update-'.$txt.'.log');

$storeId = Mage::app()->getStore()->getStoreId();

#######################################################
              #STEP 1
#######################################################

$data_xlsx = "prijslijst/prijslijst_20_07_2022_mavis.xlsx";
/* if (!$result_2 = $conn->query("delete from all_supplier_products where supplier_id=1")) {                
    echo mysqli_error();
} else {
    $status = 'success';
}
exit; */

$status = '';
$xlsx = SimpleXLSX::parse($data_xlsx);
const PMCHUNK = 1000;
$chunk_xlsx_data = array_chunk($xlsx->rows(), PMCHUNK);
$all_col_data=array();
if(count($chunk_xlsx_data[0]) > 1 && count($chunk_xlsx_data)) {
	//if(count($xlsx->rows())) {
        //print_r(count($xlsx->rows()));exit;		
		$salesunits_1 = array('Rol','Koker','Kkr','Stuk','Mtr','Meter','Ltr','Liter','Lengte','Lgt','Stel','Worst','Haspel','Doos','Bus','Kg','Pak','Paar','Zak','Set','Blister');
		$salesunits_2 = array('10 Stuks','100 Stuks','1000 Stuks');
        //$i = 0;
        foreach($chunk_xlsx_data as $chunked_idx=>$chunked_xlsx_values) {
            $all_col_data = array();
            $products = array();
            $field_names = array();
            foreach($chunked_xlsx_values as $xl_row=>$xl_rowdata) {
                $cost = 0;
                $idealverpakking = $min_order_qty = 1;
                if($xl_row == 0) {
                    foreach ($xl_rowdata as $key=>$val) {
                        if(!in_array($val, ['ARTIOM', 'MERKOM', 'KRNAM1', 'IKPROM', 'VKPROM', 'VERKOOP', 'FACTOR', 'ARKRNR'])) {
                            $field_names[$key] = $val;
                        }
                    }
                    continue;
                }

                 if((!is_numeric($xl_rowdata[7])) || (!is_numeric($xl_rowdata[0]))) {
                    $progress_status['er_imp'][$current_rec] = "<div style='color:red;'><i class='fas fa-exclamation-triangle'></i>&nbsp; Row data not valid. (Row ".($current_rec).")</div>";
                    file_put_contents($progress_file_path, json_encode($progress_status));
                    continue;
                } 
                //print_r($field_names);exit;
                if(trim($xl_rowdata[7])) {
                    foreach ($xl_rowdata as $key=>$val) {                      
                        
                        if($key == 0) {
                            $sku = $val;
                        }
                        if($key == 7) {
                            $ean = $val;
                        }
        
                        if($key == 5) {
                            
                            if(in_array($val,$salesunits_1)) {
                                $idealverpakking = 1;
                            }
                            if(in_array($val,$salesunits_2)) {
                                $value = explode(' ',$val);
                                $idealverpakking = $value[0];
                            }
                            $min_order_qty = $idealverpakking;
                        }
                        if($key == 8) {
                            $cost = str_replace(',','.',$val);
                            $bp_per_piece = $cost / $min_order_qty;
                        }
                        
                        if($key == 10) {
                            $afwijkenidealeverpakking = ($val == 'J') ? 1 : 0;
                        }
                    }
                    
                    
                    $products[$xl_rowdata[0]]['sku'] = $sku;
                    $products[$xl_rowdata[0]]['idealverpakking'] = $idealverpakking;
                    $products[$xl_rowdata[0]]['bp_min_order_qty'] = $cost;
                    $products[$xl_rowdata[0]]['afwijkenidealeverpakking'] = $afwijkenidealeverpakking;
                    $products[$xl_rowdata[0]]['min_order_qty'] = (int)$min_order_qty;
                    $products[$xl_rowdata[0]]['bp_per_piece'] = $bp_per_piece;
                    $products[$xl_rowdata[0]]['ean'] = $ean;
                    $products[$xl_rowdata[0]]['supplier_id'] = '1';
                }
            }

            $sql_2 = "INSERT INTO all_supplier_products(sku,ean,supplier_id,bp_per_piece,bp_min_order_qty, min_order_qty, afwijkenidealeverpakking, created_at, modified_at) VALUES ";

            foreach ($products as $key=>$data) {
                $all_col_data[] = "('".$data['sku']."','".$data['ean']."' ,'".$data['supplier_id']."','".$data['bp_per_piece']."','".$data['bp_min_order_qty']."', '".$data['min_order_qty']."','".$data['afwijkenidealeverpakking']."', '".NOW()."', '".NOW()."')";
            }

            $sql_2 .= implode(',', $all_col_data);
            $modified_at = date('Y-m-d h:i:s');
            $sql_2 .= " ON DUPLICATE KEY UPDATE sku = VALUES(sku), ean=VALUES(ean),supplier_id = VALUES(supplier_id),bp_per_piece = VALUES(bp_per_piece),bp_min_order_qty = VALUES(bp_min_order_qty), min_order_qty = VALUES(min_order_qty), afwijkenidealeverpakking = VALUES(afwijkenidealeverpakking), modified_at = VALUES(modified_at)";

            //echo $sql_2;exit;
            if (!$result_2 = $conn->query($sql_2)) {                
                echo mysqli_error();
            } else {
                $status = 'success';
            }
        }
        
   // }
}
if($status == 'success')
echo $response_data['msg'] = "Excel Data is saved successfully.";

