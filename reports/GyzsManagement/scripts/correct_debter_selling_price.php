<?php

include "../define/constants.php";
include "../config/dbconfig.php";
include "../lib/SimpleXLSXGen.php";

function changeUpdateStatus($conn,$product_id) {
  $change_status = "UPDATE price_management_data SET is_updated = '1' WHERE product_id IN (".$product_id.")";
  $conn->query($change_status);
}


function bulkExcelFile($all_excel_row) {
  $valid_header = array("Artikelnummer (Artikel)","4027100","4027101","4027102","4027103","4027104","4027105","4027106","4027107","4027108","4027109","4027110","4027111","4027112","4027113","4027114","4027115");
  array_unshift($all_excel_row, $valid_header);
  SimpleXLSXGen::fromArray($all_excel_row)->saveAs("../pm_logs/min_debter_price.xlsx");
}

function bulkUpdateProducts($type,$data,$update_type, $log_type, $excel_data) {
    $chunk_size = PMCHUNK;
    global $conn;
    $total_inserted_records =  $all_excel_row = array();
    $chunk_data = array_chunk($data,$chunk_size);
  
    if(count($chunk_data)) {
      foreach($chunk_data as $chunk_index=>$chunk_values) {
        $all_col_data = $updated_product_ids = array();
        //if($type == "debterprice") {
            $sql = "INSERT INTO price_management_data (product_id, sku";
            for($d=0; $d<=15; $d++) {
                $debter_number = intval(4027100 + $d);
                $c_d_id = "group_".$debter_number."_magento_id";
                $c_d_sp = "group_".$debter_number."_debter_selling_price";
                $c_d_m_bp = "group_".$debter_number."_margin_on_buying_price";
                $c_d_m_sp = "group_".$debter_number."_margin_on_selling_price";
                $c_d_o_gp = "group_".$debter_number."_discount_on_grossprice_b_on_deb_selling_price";
                $sql .= ", ".$c_d_id.", ".$c_d_sp.", ".$c_d_m_bp.", ".$c_d_m_sp.", ".$c_d_o_gp;
            }
            $sql .= ") VALUES ";
        //}
  
        foreach($chunk_values as $key=>$chunk_value) {
            $create_col_data = "";
          //if($type == "debterprice") {
            $create_col_data = "('".$chunk_value['product_id']."', '".$chunk_value['sku']."', '";
            for($d=0;$d<=15;$d++) {
                $debter_number = intval(4027100 + $d);
                $c_d_id = "group_".$debter_number."_magento_id";
                $c_d_sp = "group_".$debter_number."_debter_selling_price";
                $c_d_m_bp = "group_".$debter_number."_margin_on_buying_price";
                $c_d_m_sp = "group_".$debter_number."_margin_on_selling_price";
                $c_d_o_gp = "group_".$debter_number."_discount_on_grossprice_b_on_deb_selling_price";
                $create_col_data .= $chunk_value[$c_d_id]."', '".$chunk_value[$c_d_sp]."', '".$chunk_value[$c_d_m_bp]."', '".$chunk_value[$c_d_m_sp]."', '".$chunk_value[$c_d_o_gp]."','";
            }
            $create_col_data = rtrim($create_col_data, "'");
            $create_col_data = rtrim($create_col_data, ",");
            $all_col_data[] = $create_col_data .")";

            if(array_key_exists($chunk_value['product_id'],$excel_data))
            $all_excel_row[] = $excel_data[$chunk_value['product_id']];
            //}
          $updated_product_ids[] = $chunk_value["product_id"];
        }
        // if($type == "debterprice") {
            $sql .= implode(",", $all_col_data) . " ON DUPLICATE KEY UPDATE ";
            for($d=0; $d<=15; $d++) {
                $debter_number = intval(4027100 + $d);
                $c_d_id = "group_".$debter_number."_magento_id";
                $c_d_sp = "group_".$debter_number."_debter_selling_price";
                $c_d_m_bp = "group_".$debter_number."_margin_on_buying_price";
                $c_d_m_sp = "group_".$debter_number."_margin_on_selling_price";
                $c_d_o_gp = "group_".$debter_number."_discount_on_grossprice_b_on_deb_selling_price";
                $sql .= $c_d_id." = VALUES(".$c_d_id."), ".$c_d_sp." = VALUES(".$c_d_sp."), ".$c_d_m_bp." = VALUES(".$c_d_m_bp."), ".$c_d_m_sp." = VALUES(".$c_d_m_sp."), ".$c_d_o_gp." = VALUES(".$c_d_o_gp."), ";
              }
              $sql = rtrim($sql, ', ');
          if($conn->query($sql)) {
            changeUpdateStatus($conn, implode(",", $updated_product_ids));
            $total_inserted_records[] = count($chunk_values);
          } else {
            bulkInsertLog($chunk_index,"Bulk Update ".$update_type." Error For Respective Debters  (".$log_type."):".mysqli_error($conn));
          }
        //}
      }//end chunks
      bulkExcelFile($all_excel_row);
    }
    return array_sum($total_inserted_records);
  }

  function bulkInsertLog($chunk_index,$chunk_msg) {
    $file_pricechunks_log = "../pm_logs/debter_price_update_log.txt";
    file_put_contents($file_pricechunks_log,"".date("d-m-Y H:i:s")." Updated Price Chunk (".$chunk_index."):-".$chunk_msg."\n",FILE_APPEND);
  }

$table = "mage_catalog_product_entity AS mcpe
INNER JOIN mage_catalog_category_product AS mccp ON mccp.product_id = mcpe.entity_id
INNER JOIN price_management_data AS pmd ON pmd.product_id = mcpe.entity_id
LEFT JOIN mage_catalog_product_entity_decimal AS mcped_selling_price ON mcped_selling_price.entity_id = pmd.product_id AND mcped_selling_price.attribute_id = '".PRICE."'";

$columns = array(
  array( 'db' => 'DISTINCT mcpe.entity_id AS product_id'),
  array( 'db' => 'pmd.gross_unit_price AS gross_unit_price'),
  array( 'db' => 'mcpe.sku AS sku'),
  array( 'db' => 'pmd.buying_price AS buying_price'),
  array( 'db' => 'pmd.selling_price AS selling_price'),
  array( 'db' => 'pmd.profit_percentage_buying_price AS profit_percentage_buying_price' ),
  array( 'db' => 'pmd.profit_percentage_selling_price AS profit_percentage_selling_price'),

  array( 'db' => 'pmd.group_4027100_magento_id AS group_4027100_magento_id'),
  array( 'db' => 'pmd.group_4027100_debter_selling_price AS group_4027100_debter_selling_price'),
  array( 'db' => 'pmd.group_4027100_margin_on_buying_price AS group_4027100_margin_on_buying_price'),
  array( 'db' => 'pmd.group_4027100_margin_on_selling_price AS group_4027100_margin_on_selling_price'),
  array( 'db' => 'pmd.group_4027100_discount_on_grossprice_b_on_deb_selling_price AS group_4027100_discount_on_grossprice_b_on_deb_selling_price'),
  array( 'db' => 'pmd.group_4027101_magento_id AS group_4027101_magento_id'),
  array( 'db' => 'pmd.group_4027101_debter_selling_price AS group_4027101_debter_selling_price'),
  array( 'db' => 'pmd.group_4027101_margin_on_buying_price AS group_4027101_margin_on_buying_price'),
  array( 'db' => 'pmd.group_4027101_margin_on_selling_price AS group_4027101_margin_on_selling_price'),
  array( 'db' => 'pmd.group_4027101_discount_on_grossprice_b_on_deb_selling_price AS group_4027101_discount_on_grossprice_b_on_deb_selling_price'),
  array( 'db' => 'pmd.group_4027102_magento_id AS group_4027102_magento_id'),
  array( 'db' => 'pmd.group_4027102_debter_selling_price AS group_4027102_debter_selling_price'),
  array( 'db' => 'pmd.group_4027102_margin_on_buying_price AS group_4027102_margin_on_buying_price'),
  array( 'db' => 'pmd.group_4027102_margin_on_selling_price AS group_4027102_margin_on_selling_price'),
  array( 'db' => 'pmd.group_4027102_discount_on_grossprice_b_on_deb_selling_price AS group_4027102_discount_on_grossprice_b_on_deb_selling_price'),
  array( 'db' => 'pmd.group_4027103_magento_id AS group_4027103_magento_id'),
  array( 'db' => 'pmd.group_4027103_debter_selling_price AS group_4027103_debter_selling_price'),
  array( 'db' => 'pmd.group_4027103_margin_on_buying_price AS group_4027103_margin_on_buying_price'),
  array( 'db' => 'pmd.group_4027103_margin_on_selling_price AS group_4027103_margin_on_selling_price'),
  array( 'db' => 'pmd.group_4027103_discount_on_grossprice_b_on_deb_selling_price AS group_4027103_discount_on_grossprice_b_on_deb_selling_price'),
  array( 'db' => 'pmd.group_4027104_magento_id AS group_4027104_magento_id'),
  array( 'db' => 'pmd.group_4027104_debter_selling_price AS group_4027104_debter_selling_price'),
  array( 'db' => 'pmd.group_4027104_margin_on_buying_price AS group_4027104_margin_on_buying_price'),
  array( 'db' => 'pmd.group_4027104_margin_on_selling_price AS group_4027104_margin_on_selling_price'),
  array( 'db' => 'pmd.group_4027104_discount_on_grossprice_b_on_deb_selling_price AS group_4027104_discount_on_grossprice_b_on_deb_selling_price'),
  array( 'db' => 'pmd.group_4027105_magento_id AS group_4027105_magento_id'),
  array( 'db' => 'pmd.group_4027105_debter_selling_price AS group_4027105_debter_selling_price'),
  array( 'db' => 'pmd.group_4027105_margin_on_buying_price AS group_4027105_margin_on_buying_price'),
  array( 'db' => 'pmd.group_4027105_margin_on_selling_price AS group_4027105_margin_on_selling_price'),
  array( 'db' => 'pmd.group_4027105_discount_on_grossprice_b_on_deb_selling_price AS group_4027105_discount_on_grossprice_b_on_deb_selling_price'),
  array( 'db' => 'pmd.group_4027106_magento_id AS group_4027106_magento_id'),
  array( 'db' => 'pmd.group_4027106_debter_selling_price AS group_4027106_debter_selling_price'),
  array( 'db' => 'pmd.group_4027106_margin_on_buying_price AS group_4027106_margin_on_buying_price'),
  array( 'db' => 'pmd.group_4027106_margin_on_selling_price AS group_4027106_margin_on_selling_price'),
  array( 'db' => 'pmd.group_4027106_discount_on_grossprice_b_on_deb_selling_price AS group_4027106_discount_on_grossprice_b_on_deb_selling_price'),
  array( 'db' => 'pmd.group_4027107_magento_id AS group_4027107_magento_id'),
  array( 'db' => 'pmd.group_4027107_debter_selling_price AS group_4027107_debter_selling_price'),
  array( 'db' => 'pmd.group_4027107_margin_on_buying_price AS group_4027107_margin_on_buying_price'),
  array( 'db' => 'pmd.group_4027107_margin_on_selling_price AS group_4027107_margin_on_selling_price'),
  array( 'db' => 'pmd.group_4027107_discount_on_grossprice_b_on_deb_selling_price AS group_4027107_discount_on_grossprice_b_on_deb_selling_price'),
  array( 'db' => 'pmd.group_4027108_magento_id AS group_4027108_magento_id'),
  array( 'db' => 'pmd.group_4027108_debter_selling_price AS group_4027108_debter_selling_price'),
  array( 'db' => 'pmd.group_4027108_margin_on_buying_price AS group_4027108_margin_on_buying_price'),
  array( 'db' => 'pmd.group_4027108_margin_on_selling_price AS group_4027108_margin_on_selling_price'),
  array( 'db' => 'pmd.group_4027108_discount_on_grossprice_b_on_deb_selling_price AS group_4027108_discount_on_grossprice_b_on_deb_selling_price'),
  array( 'db' => 'pmd.group_4027109_magento_id AS group_4027109_magento_id'),
  array( 'db' => 'pmd.group_4027109_debter_selling_price AS group_4027109_debter_selling_price'),
  array( 'db' => 'pmd.group_4027109_margin_on_buying_price AS group_4027109_margin_on_buying_price'),
  array( 'db' => 'pmd.group_4027109_margin_on_selling_price AS group_4027109_margin_on_selling_price'),
  array( 'db' => 'pmd.group_4027109_discount_on_grossprice_b_on_deb_selling_price AS group_4027109_discount_on_grossprice_b_on_deb_selling_price'),
  array( 'db' => 'pmd.group_4027110_magento_id AS group_4027110_magento_id'),
  array( 'db' => 'pmd.group_4027110_debter_selling_price AS group_4027110_debter_selling_price'),
  array( 'db' => 'pmd.group_4027110_margin_on_buying_price AS group_4027110_margin_on_buying_price'),
  array( 'db' => 'pmd.group_4027110_margin_on_selling_price AS group_4027110_margin_on_selling_price'),
  array( 'db' => 'pmd.group_4027110_discount_on_grossprice_b_on_deb_selling_price AS group_4027110_discount_on_grossprice_b_on_deb_selling_price'),
  array( 'db' => 'pmd.group_4027111_magento_id AS group_4027111_magento_id'),
  array( 'db' => 'pmd.group_4027111_debter_selling_price AS group_4027111_debter_selling_price'),
  array( 'db' => 'pmd.group_4027111_margin_on_buying_price AS group_4027111_margin_on_buying_price'),
  array( 'db' => 'pmd.group_4027111_margin_on_selling_price AS group_4027111_margin_on_selling_price'),
  array( 'db' => 'pmd.group_4027111_discount_on_grossprice_b_on_deb_selling_price AS group_4027111_discount_on_grossprice_b_on_deb_selling_price'),
  
  array( 'db' => 'pmd.group_4027112_magento_id AS group_4027112_magento_id'),
  array( 'db' => 'pmd.group_4027112_debter_selling_price AS group_4027112_debter_selling_price'),
  array( 'db' => 'pmd.group_4027112_margin_on_buying_price AS group_4027112_margin_on_buying_price'),
  array( 'db' => 'pmd.group_4027112_margin_on_selling_price AS group_4027112_margin_on_selling_price'),
  array( 'db' => 'pmd.group_4027112_discount_on_grossprice_b_on_deb_selling_price AS group_4027112_discount_on_grossprice_b_on_deb_selling_price'),
  
  array( 'db' => 'pmd.group_4027113_magento_id AS group_4027113_magento_id'),
  array( 'db' => 'pmd.group_4027113_debter_selling_price AS group_4027113_debter_selling_price'),
  array( 'db' => 'pmd.group_4027113_margin_on_buying_price AS group_4027113_margin_on_buying_price'),
  array( 'db' => 'pmd.group_4027113_margin_on_selling_price AS group_4027113_margin_on_selling_price'),
  array( 'db' => 'pmd.group_4027113_discount_on_grossprice_b_on_deb_selling_price AS group_4027113_discount_on_grossprice_b_on_deb_selling_price'),
  
  array( 'db' => 'pmd.group_4027114_magento_id AS group_4027114_magento_id'),
  array( 'db' => 'pmd.group_4027114_debter_selling_price AS group_4027114_debter_selling_price'),
  array( 'db' => 'pmd.group_4027114_margin_on_buying_price AS group_4027114_margin_on_buying_price'),
  array( 'db' => 'pmd.group_4027114_margin_on_selling_price AS group_4027114_margin_on_selling_price'),
  array( 'db' => 'pmd.group_4027114_discount_on_grossprice_b_on_deb_selling_price AS group_4027114_discount_on_grossprice_b_on_deb_selling_price'),
  
  array( 'db' => 'pmd.group_4027115_magento_id AS group_4027115_magento_id'),
  array( 'db' => 'pmd.group_4027115_debter_selling_price AS group_4027115_debter_selling_price'),
  array( 'db' => 'pmd.group_4027115_margin_on_buying_price AS group_4027115_margin_on_buying_price'),
  array( 'db' => 'pmd.group_4027115_margin_on_selling_price AS group_4027115_margin_on_selling_price'),
  array( 'db' => 'pmd.group_4027115_discount_on_grossprice_b_on_deb_selling_price AS group_4027115_discount_on_grossprice_b_on_deb_selling_price'),
  

);

function roundValue($val) {
    global $scale;
    return round($val,$scale);
}
simple($table, $columns);


function simple ($table, $columns)
	{
		global $conn;
				
		 $raw_sql = "SELECT ".implode(",", pluck($columns, 'db'))."
			 FROM $table";

		$excel_data = array();
    $all_selected_data = $db_data = $all_updated_data = array();

		// Main query to actually get the data,  limit $chunk_size
		$sql = 
			"SELECT ".implode(",", pluck($columns, 'db'))."
			 FROM $table";
           $data = $conn->query($sql);
             while ($v = $data->fetch_assoc()) {
             
                $db_data[$v["product_id"]]['product_id'] = $v["product_id"];
                $db_data[$v["product_id"]]['sku'] = $v["sku"];

                //$excel_data[$v["product_id"]]['product_id'] = $v["product_id"];
                $excel_data[$v["product_id"]]['sku'] = $v["sku"];
                for($d=0;$d<=15;$d++) {
                    $cust_group = intval(4027100 + $d);
                    $selected_debter_column_mid = "group_".$cust_group."_magento_id";
                    $selected_debter_column_sp = "group_".$cust_group."_debter_selling_price";
                    $selected_debter_column_bp = "group_".$cust_group."_margin_on_buying_price";
                    $selected_debter_column_msp = "group_".$cust_group."_margin_on_selling_price";
                    $selected_debter_column_gp = "group_".$cust_group."_discount_on_grossprice_b_on_deb_selling_price";
                    $db_data[$v["product_id"]][$selected_debter_column_mid] = is_null($v[$selected_debter_column_mid])?0.00:$v[$selected_debter_column_mid];
                    $db_data[$v["product_id"]][$selected_debter_column_sp] = is_null($v[$selected_debter_column_sp])?0.00:$v[$selected_debter_column_sp];
                    $db_data[$v["product_id"]][$selected_debter_column_bp] = is_null($v[$selected_debter_column_bp])?0.00:$v[$selected_debter_column_bp];;
                    $db_data[$v["product_id"]][$selected_debter_column_msp] = is_null($v[$selected_debter_column_msp])?0.00:$v[$selected_debter_column_msp];
                    $db_data[$v["product_id"]][$selected_debter_column_gp] = is_null($v[$selected_debter_column_gp])?0.00:$v[$selected_debter_column_gp];

                    $excel_data[$v["product_id"]][$cust_group] = '-';
                }

                $sql_2 = 
                "SELECT MID.customer_group AS m_id, CS.customer_group_name AS debter_number FROM price_management_debter_categories as MID LEFT JOIN price_management_customer_groups AS CS on MID.customer_group=CS.magento_id WHERE product_ids LIKE '%".$v['product_id']."%'";

                $debters_of_product = $conn->query($sql_2);
                while($row_2 = $debters_of_product->fetch_assoc()) {
                    $selected_debter_column_sp = "group_".$row_2['debter_number']."_debter_selling_price";
                   
                    if ($v[$selected_debter_column_sp] > $v['selling_price']) {
                        $selected_debter_column_bp = "group_".$row_2['debter_number']."_margin_on_buying_price";
                        $selected_debter_column_msp = "group_".$row_2['debter_number']."_margin_on_selling_price";
                        $selected_debter_column_gp = "group_".$row_2['debter_number']."_discount_on_grossprice_b_on_deb_selling_price";
                        //do calculations
                        $deb_selling_price = $v['selling_price']; 
                        $all_selected_data[$v['product_id']][$selected_debter_column_sp] = $deb_selling_price;
                        $supplier_gross_price = ($v['gross_unit_price'] == 0 ? 1:$v['gross_unit_price']);

                        $deb_margin_on_buying_price = roundValue((($deb_selling_price - $v['buying_price'])/$v['buying_price']) * 100);
                        $deb_margin_on_selling_price = roundValue((($deb_selling_price - $v['buying_price'])/$deb_selling_price) * 100);
                        $deb_discount_on_gross_price = roundValue((1 - ($deb_selling_price/$supplier_gross_price)) * 100);

                        $all_selected_data[$v['product_id']][$selected_debter_column_bp] =  $deb_margin_on_buying_price;
                   
                        $all_selected_data[$v['product_id']][$selected_debter_column_msp] = $deb_margin_on_selling_price;
                        $all_selected_data[$v['product_id']][$selected_debter_column_gp] = $deb_discount_on_gross_price;
                        $excel_data_updated[$v["product_id"]][$row_2['debter_number']] = $deb_selling_price;
                    }
                }

                if(isset($all_selected_data[$v['product_id']])) {
                  $all_updated_data[$v['product_id']] = array_replace($db_data[$v['product_id']], $all_selected_data[$v['product_id']]);
                  $all_updated_excel[$v['product_id']] = array_replace($excel_data[$v['product_id']], $excel_data_updated[$v['product_id']]);
                }
             }
              if(count($all_updated_data) > 0) {
                $total_updated_recs = bulkUpdateProducts("debterprice",$all_updated_data,"Debter prices more than their PM Vkpr", "All Price Management Products", $all_updated_excel);
                echo "Updated {$total_updated_recs} products. Please check excel file.";
              } else {
                echo 'Products are already updated.OR No updation because dont have any Debter';
              }
            
            }//end simple()
             function pluck ( $a, $prop )
	{
		$out = array();

		for ( $i=0, $len=count($a) ; $i<$len ; $i++ ) {
            if(empty($a[$i][$prop])){
                continue;
			}
			//removing the $out array index confuses the filter method in doing proper binding,
			//adding it ensures that the array data are mapped correctly
			$out[$i] = $a[$i][$prop];
		}

		return $out;
	}