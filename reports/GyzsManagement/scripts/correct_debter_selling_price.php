<?php

include "../define/constants.php";
include "../config/dbconfig.php";
include "../lib/SimpleXLSXGen.php";

function changeUpdateStatus($conn,$product_id) {
  $change_status = "UPDATE price_management_data SET is_updated = '1' WHERE product_id IN (".$product_id.")";
  $conn->query($change_status);
}

function bulkExcelFile($all_excel_row) {
  $valid_header = array("Artikelnummer (Artikel)","ZZP","Aannemer M","Aannemer L","Metaal","Interieurbouw","Slotenservice","Scholen/zorg","DHZ","Installatie","Glashandel","VVE","Groen","Timmerfabriek","Overheid","Weder1","Weder2");
  array_unshift($all_excel_row, $valid_header);
  SimpleXLSXGen::fromArray($all_excel_row)->saveAs("../pm_logs/min_debter_price.xlsx");
}

function bulkUpdateProducts($type,$data,$update_type, $log_type, $excel_data) {
    $chunk_size = PMCHUNK;
    global $conn;
    $total_inserted_records =  $all_excel_row = array();
    $chunk_data = array_chunk($data,3);
  
    if(count($chunk_data)) {
      foreach($chunk_data as $chunk_index=>$chunk_values) {
        $all_col_data = $updated_product_ids = $in_sku_arr = array();
        $sql = "UPDATE price_management_data SET ";
        $in_part = " WHERE sku IN(";
            for($d=0; $d<=15; $d++) {
                $debter_number = intval(4027100 + $d);
                $c_d_sp = "group_".$debter_number."_debter_selling_price = (CASE sku ";
                $c_d_m_bp = "group_".$debter_number."_margin_on_buying_price  = (CASE sku ";
                $c_d_m_sp = "group_".$debter_number."_margin_on_selling_price  = (CASE sku ";
                $c_d_o_gp = "group_".$debter_number."_discount_on_grossprice_b_on_deb_selling_price  = (CASE sku ";

                $c_d_sp_idx = "group_".$debter_number."_debter_selling_price";
                $c_d_m_bp_idx = "group_".$debter_number."_margin_on_buying_price";
                $c_d_m_sp_idx = "group_".$debter_number."_margin_on_selling_price";
                $c_d_o_gp_idx = "group_".$debter_number."_discount_on_grossprice_b_on_deb_selling_price";

                foreach($chunk_values as $key=>$chunk_value) {
                  $create_col_data = "";
                  $create_col_data = "('".$chunk_value['product_id']."', '".$chunk_value['sku']."', '";
                  $c_d_sp .= " WHEN '".$chunk_value['sku']."' THEN ".$chunk_value[$c_d_sp_idx];
                  $c_d_m_bp .= " WHEN '".$chunk_value['sku']."' THEN ".$chunk_value[$c_d_m_bp_idx];
                  $c_d_m_sp .= " WHEN '".$chunk_value['sku']."' THEN ".$chunk_value[ $c_d_m_sp_idx];
                  $c_d_o_gp .= " WHEN '".$chunk_value['sku']."' THEN ".$chunk_value[$c_d_o_gp_idx];

                  if(array_key_exists($chunk_value['product_id'],$excel_data) && !in_array($excel_data[$chunk_value['product_id']], $all_excel_row))
                  $all_excel_row[] = $excel_data[$chunk_value['product_id']];

                  $updated_product_ids[] = $chunk_value['product_id'];
                  $in_sku_arr[$chunk_value['sku']] = $chunk_value['sku'];
                }
                $c_d_sp .= " END)";
                $c_d_m_bp .= " END)";
                $c_d_m_sp .= " END)";
                $c_d_o_gp .= " END)";
                $sql .= $c_d_sp.', ';
                $sql .= $c_d_m_bp.', ';
                $sql .= $c_d_m_sp.', ';
                $sql .= $c_d_o_gp.', ';
            }
          $sql = rtrim($sql, ', ');
          $sql .= rtrim($in_part, ', ')."'".implode("', '", $in_sku_arr)."'";
          $sql .= ')';
          
          if($conn->query($sql)) {
            bulkInsertLog($chunk_index,"Bulk Update:".count($chunk_values));
            changeUpdateStatus($conn, implode(",", $updated_product_ids));
            $total_inserted_records[] = count($chunk_values);
          } else {
            bulkInsertLog($chunk_index,"Bulk Update Error".mysqli_error($conn)."\n".$sql);
          }
      }//end chunks
      bulkExcelFile($all_excel_row);
    }
    return array_sum($total_inserted_records);
  }

  function bulkInsertLog($chunk_index,$chunk_msg) {
    $file_pricechunks_log = "../pm_logs/min_debter_price.txt";
    file_put_contents($file_pricechunks_log,"".date("d-m-Y H:i:s")." Updated Price Chunk (".$chunk_index."):-".$chunk_msg."\n",FILE_APPEND);
  }

/* $table = "mage_catalog_product_entity AS mcpe
INNER JOIN mage_catalog_category_product AS mccp ON mccp.product_id = mcpe.entity_id
INNER JOIN price_management_data AS pmd ON pmd.product_id = mcpe.entity_id
LEFT JOIN mage_catalog_product_entity_decimal AS mcped_selling_price ON mcped_selling_price.entity_id = pmd.product_id AND mcped_selling_price.attribute_id = '".PRICE."'";
 */
$table = "price_management_data AS pmd
INNER JOIN price_management_debter_categories AS pmdc ON pmdc.product_ids LIKE CONCAT('%', pmd.product_id, '%')
LEFT JOIN price_management_customer_groups AS pmcg ON pmdc.customer_group=pmcg.magento_id";

for($d=0;$d<=15;$d++) {
  $cust_group = intval(4027100 + $d);
  $debter_columns[] = array( 'db' => "pmd.group_".$cust_group."_debter_selling_price AS group_".$cust_group."_debter_selling_price");
  $debter_columns[] = array( 'db' => "pmd.group_".$cust_group."_margin_on_buying_price AS group_".$cust_group."_margin_on_buying_price");
  $debter_columns[] = array( 'db' => "pmd.group_".$cust_group."_margin_on_selling_price AS group_".$cust_group."_margin_on_selling_price");
  $debter_columns[] = array( 'db' => "pmd.group_".$cust_group."_discount_on_grossprice_b_on_deb_selling_price AS group_".$cust_group."_discount_on_grossprice_b_on_deb_selling_price");
}

$columns = array(
  array( 'db' => 'pmd.product_id AS product_id'),
  array( 'db' => 'pmd.gross_unit_price AS gross_unit_price'),
  array( 'db' => 'pmd.sku AS sku'),
  array( 'db' => 'pmd.buying_price AS buying_price'),
  array( 'db' => 'pmd.selling_price AS selling_price'),
  array( 'db' => 'pmd.profit_percentage_buying_price AS profit_percentage_buying_price'),
  array( 'db' => 'pmd.profit_percentage_selling_price AS profit_percentage_selling_price'),
  array('db' => 'pmdc.customer_group AS m_id'),
  array('db' => 'pmcg.customer_group_name AS debter_number')
);

$columns = array_merge($columns, $debter_columns);

function roundValue($val) {
    global $scale;
    return round($val,$scale);
}
simple($table, $columns);

function simple ($table, $columns)
	{
		global $conn;
		$excel_data = array();
    $all_selected_data = $db_data = $all_updated_data = $debter_sp_condition = array();
    for($d=0;$d<=15;$d++) {
      $cust_group = intval(4027100 + $d);
      $debter_sp_condition[] = "pmd.group_".$cust_group."_debter_selling_price >  selling_price";
    }

    $sql =  "SELECT ".implode(",", pluck($columns, 'db'))."
    FROM $table WHERE ".implode(' OR ', $debter_sp_condition)."
    ORDER BY CAST((SELECT COUNT(*) AS mag_updated_product_cnt FROM price_management_history WHERE product_id = pmd.product_id and is_viewed = 'No' and updated_by = 'Magento' and buying_price_changed = '1') AS UNSIGNED) DESC, pmd.product_id";
    $logfile = fopen("../min_debter_price_getquery.txt", "w");
    fwrite($logfile, $sql."\n");
    $data = $conn->query($sql);
    $db_data =  $all_updated_data = $all_updated_excel = $excel_data = $excel_data_updated = $all_selected_data = array();
      while ($v = $data->fetch_assoc()) {
        if(!isset($db_data[$v["product_id"]])) {
          $db_data[$v["product_id"]]['product_id'] = $v["product_id"];
          $db_data[$v["product_id"]]['sku'] = $v["sku"];
          $excel_data[$v["product_id"]]['sku'] = $v["sku"];
          for($d=0;$d<=15;$d++) {
            $cust_group = intval(4027100 + $d);
            $selected_debter_column_sp = "group_".$cust_group."_debter_selling_price";
            $selected_debter_column_bp = "group_".$cust_group."_margin_on_buying_price";
            $selected_debter_column_msp = "group_".$cust_group."_margin_on_selling_price";
            $selected_debter_column_gp = "group_".$cust_group."_discount_on_grossprice_b_on_deb_selling_price";
            /* $db_data[$v["product_id"]][$selected_debter_column_sp] = is_null($v[$selected_debter_column_sp])?0.00:$v[$selected_debter_column_sp];
            $db_data[$v["product_id"]][$selected_debter_column_bp] = is_null($v[$selected_debter_column_bp])?0.00:$v[$selected_debter_column_bp];;
            $db_data[$v["product_id"]][$selected_debter_column_msp] = is_null($v[$selected_debter_column_msp])?0.00:$v[$selected_debter_column_msp];
            $db_data[$v["product_id"]][$selected_debter_column_gp] = is_null($v[$selected_debter_column_gp])?0.00:$v[$selected_debter_column_gp];
            */ 
            $db_data[$v["product_id"]][$selected_debter_column_sp]  = $v[$selected_debter_column_sp];
            $db_data[$v["product_id"]][$selected_debter_column_bp]  = $v[$selected_debter_column_bp];
            $db_data[$v["product_id"]][$selected_debter_column_msp] = $v[$selected_debter_column_msp];
            $db_data[$v["product_id"]][$selected_debter_column_gp]  = $v[$selected_debter_column_gp];
            $excel_data[$v["product_id"]][$cust_group] = '-';
          }
        }

            $selected_debter_column_sp = "group_".$v['debter_number']."_debter_selling_price";
            if ($v[$selected_debter_column_sp] > $v['selling_price']) {
                $selected_debter_column_bp = "group_".$v['debter_number']."_margin_on_buying_price";
                $selected_debter_column_msp = "group_".$v['debter_number']."_margin_on_selling_price";
                $selected_debter_column_gp = "group_".$v['debter_number']."_discount_on_grossprice_b_on_deb_selling_price";
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
                $excel_data_updated[$v["product_id"]][$v['debter_number']] = $deb_selling_price;
            }
      }//end of product_query

      foreach($db_data as $product_id=>$product_data) {
        if(isset($all_selected_data[$product_id])) {
          $all_updated_data[$product_id] = array_replace($db_data[$product_id], $all_selected_data[$product_id]);
          $all_updated_excel[$product_id] = array_replace($excel_data[$product_id], $excel_data_updated[$product_id]);
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