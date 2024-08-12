<?php
include "config/config.php";
include "define/constants.php";
 error_reporting(E_ALL);
//error_reporting(1);
ini_set("memory_limit", "10G");
ini_set("max_execution_time", 0);

insertInRowFormat();

function insertInRowFormat() {
    global $conn;
    $webshop_sql = "SELECT * FROM all_suppliers_data_source WHERE is_present ='webshop' ORDER BY eancode, id";
    $result = $conn->query($webshop_sql);
    
    $supplier_row_format_array = array();
    $initiate_non_webshop_supplier_data = array();
    while($row_webshop = $result->fetch_assoc())
    {

        $supplier_row_format_array[$row_webshop['product_id']]['sku'] = $row_webshop['sku'];
        $supplier_row_format_array[$row_webshop['product_id']]['supplier'] = $row_webshop['supplier'];
        $supplier_row_format_array[$row_webshop['product_id']]['supplier_sku'] = $row_webshop['supplier_sku'];
        $supplier_row_format_array[$row_webshop['product_id']]['eancode'] = $row_webshop['eancode'];
        $supplier_row_format_array[$row_webshop['product_id']]['webshop_bp'] = $row_webshop['net_price'];
        $supplier_row_format_array[$row_webshop['product_id']]['product_id'] = $row_webshop['product_id'];
        $supplier_row_format_array[$row_webshop['product_id']]['actual_supplier'] = $row_webshop['actual_supplier'];

        $initiate_non_webshop_supplier_data['product_id'] = $row_webshop['product_id'];
            $initiate_non_webshop_supplier_data['eancode'] = '';
            $initiate_non_webshop_supplier_data['supplier_sku'] = '';
            $initiate_non_webshop_supplier_data['net_price'] = '';

            $initiate_non_webshop_supplier_data['status'] = '';
            $initiate_non_webshop_supplier_data['idealeverpakking'] = '';
            $initiate_non_webshop_supplier_data['afwijkenidealeverpakking'] = '';

            $initiate_non_webshop_supplier_data['verkoopeenheid'] = '';
            $initiate_non_webshop_supplier_data['delivery_time'] = '';

            $initiate_non_webshop_supplier_data['advise_price'] = '';
            $initiate_non_webshop_supplier_data['currentdate'] = '';
            $initiate_non_webshop_supplier_data['is_present'] = '';
            $initiate_non_webshop_supplier_data['actual_supplier'] = '';

        if(($row_webshop['eancode'] == '' || $row_webshop['eancode'] == '0' || $row_webshop['eancode'] == '?') && $row_webshop['supplier'] == 'JRS') {
            makeRowFormat('JRS', $row_webshop, $supplier_row_format_array);
            makeRowFormat('Polvo', $initiate_non_webshop_supplier_data, $supplier_row_format_array);
            makeRowFormat('Nordwest', $initiate_non_webshop_supplier_data, $supplier_row_format_array);
            makeRowFormat('Zevij', $initiate_non_webshop_supplier_data, $supplier_row_format_array);
            makeRowFormat('Dozon', $initiate_non_webshop_supplier_data, $supplier_row_format_array);

        } elseif (($row_webshop['eancode'] == '' || $row_webshop['eancode'] == '0' || $row_webshop['eancode'] == '?') && $row_webshop['supplier'] == 'Polvo') {
            makeRowFormat('JRS', $initiate_non_webshop_supplier_data, $supplier_row_format_array);

            makeRowFormat('Polvo', $row_webshop, $supplier_row_format_array);

            makeRowFormat('Nordwest', $initiate_non_webshop_supplier_data, $supplier_row_format_array);
            makeRowFormat('Zevij', $initiate_non_webshop_supplier_data, $supplier_row_format_array);
            makeRowFormat('Dozon', $initiate_non_webshop_supplier_data, $supplier_row_format_array);

        } elseif (($row_webshop['eancode'] == '' || $row_webshop['eancode'] == '0' || $row_webshop['eancode'] == '?') && $row_webshop['supplier'] == 'Nordwest') {
            makeRowFormat('JRS', $initiate_non_webshop_supplier_data, $supplier_row_format_array);
            makeRowFormat('Polvo', $initiate_non_webshop_supplier_data, $supplier_row_format_array);

            makeRowFormat('Nordwest', $row_webshop, $supplier_row_format_array);

            makeRowFormat('Zevij', $initiate_non_webshop_supplier_data, $supplier_row_format_array);
            makeRowFormat('Dozon', $initiate_non_webshop_supplier_data, $supplier_row_format_array);
        } elseif (($row_webshop['eancode'] == '' || $row_webshop['eancode'] == '0' || $row_webshop['eancode'] == '?') && $row_webshop['supplier'] == 'Zevij') {
            makeRowFormat('JRS', $initiate_non_webshop_supplier_data, $supplier_row_format_array);
            makeRowFormat('Polvo', $initiate_non_webshop_supplier_data, $supplier_row_format_array);
            makeRowFormat('Nordwest', $initiate_non_webshop_supplier_data, $supplier_row_format_array);

            makeRowFormat('Zevij', $row_webshop, $supplier_row_format_array);

            makeRowFormat('Dozon', $initiate_non_webshop_supplier_data, $supplier_row_format_array);
        } elseif (($row_webshop['eancode'] == '' || $row_webshop['eancode'] == '0' || $row_webshop['eancode'] == '?') && $row_webshop['supplier'] == 'Dozon') {
              makeRowFormat('JRS', $initiate_non_webshop_supplier_data, $supplier_row_format_array);
            makeRowFormat('Polvo', $initiate_non_webshop_supplier_data, $supplier_row_format_array);
            makeRowFormat('Nordwest', $initiate_non_webshop_supplier_data, $supplier_row_format_array);
            makeRowFormat('Zevij', $initiate_non_webshop_supplier_data, $supplier_row_format_array);

            makeRowFormat('Dozon', $row_webshop, $supplier_row_format_array);
        }


        if($row_webshop['eancode'] != '' && $row_webshop['eancode'] != '?' && $row_webshop['eancode'] !== '0') { 
        //    echo $row_webshop['eancode'];exit;

            $ean = $row_webshop['eancode'];
            $for_query = $ean;
            if(strlen($ean) == 13) {
                $ean_14 = '0'.$ean;
                $for_query = "'".$ean."'".","."'".$ean_14."'";
            }

            //echo $for_query;exit;
            $suppliers_sql = "SELECT * FROM all_suppliers_data_source WHERE eancode IN (".$for_query.") ORDER BY is_present DESC";

            if(!$conn->query($suppliers_sql)) {
                bulkInsertLog(0,"Bulk Update Error:".mysqli_error($conn)."\n".$suppliers_sql."\n".$row_webshop['product_id']);
                exit;
            }

            makeRowFormat('JRS', $initiate_non_webshop_supplier_data, $supplier_row_format_array);
            makeRowFormat('Polvo', $initiate_non_webshop_supplier_data, $supplier_row_format_array);
            makeRowFormat('Nordwest', $initiate_non_webshop_supplier_data, $supplier_row_format_array);
            makeRowFormat('Zevij', $initiate_non_webshop_supplier_data, $supplier_row_format_array);
            makeRowFormat('Dozon', $initiate_non_webshop_supplier_data, $supplier_row_format_array);

           // var_dump($supplier_row_format_array);exit;

            $result_s = $conn->query($suppliers_sql);

            while($row_s=$result_s->fetch_assoc()) {

                $row_s['product_id'] = $row_webshop['product_id'];

                if($row_s['supplier'] == 'JRS') {
                    makeRowFormat('JRS', $row_s, $supplier_row_format_array);

                }elseif($row_s['supplier'] == 'Polvo') {

                    makeRowFormat('Polvo', $row_s, $supplier_row_format_array);

                }elseif($row_s['supplier'] == 'Nordwest') {

                     makeRowFormat('Nordwest', $row_s, $supplier_row_format_array);

                }elseif($row_s['supplier'] == 'Zevij') { //Zevij is case sensitive

                      makeRowFormat('Zevij', $row_s, $supplier_row_format_array);

                }elseif($row_s['supplier'] == 'Dozon') {
                      makeRowFormat('Dozon', $row_s, $supplier_row_format_array);
                }

            }//end loop of all rows of each ean

        }//end if of ean having 13 digits
        
      //var_dump($supplier_row_format_array);exit;
        
    }//end loop getting all webshop

    // insert this data into database
    $chunk_format_data = array_chunk($supplier_row_format_array, PMCHUNK);

    if(count($chunk_format_data)) {
        list($sql_row_format, $last_part_sql) = makeSqlForRowFormat();

        foreach($chunk_format_data as $chunked_idx=>$chunked_item) {
            $all_col_data = $updated_product_skus = array();
            $chunk_sql = "";
            foreach($chunked_item as $c_k=>$row) {
                $one_rowString = $col_data ='';
                $join_cols_names = '(';

                //file_put_contents('hhhello.txt', $c_k."----".count($row),FILE_APPEND);
            /* if($chunked_idx==0 && $c_k == 4) {
                    var_dump($row);exit;
                }*/

                $col_data = implode("', '", $row);

                //$one_rowString = getSqlOfColumns($row);


                $one_rowString = "'".$col_data."'";
                $join_cols_names .= $one_rowString;
                $join_cols_names .= ')';

                $all_col_data[] = $join_cols_names;
            }

            //var_dump($all_col_data);exit;

            if(count($all_col_data) > 0) {
                $chunk_sql = $sql_row_format.implode(",", $all_col_data) . $last_part_sql;

                if($chunked_idx == 0) {
                    if($conn->query("TRUNCATE TABLE suppliers_row_format")) {
                        bulkInsertLog($chunked_idx,"Truncated suppliers_row_format:");
                    }
                }

                    if($conn->query($chunk_sql)) {
                        bulkInsertLog($chunked_idx,"Bulk Update:".count($chunked_item));                    
                    } else {
                       // echo mysqli_error($conn)."\n".$chunk_sql;exit;
                        bulkInsertLog($chunked_idx,"Bulk Update Error:".mysqli_error($conn)."\n".$chunk_sql);
                    }
            }
            unset($all_col_data);
        }

    }

   
}

function makeSqlForRowFormat()
{
   $sql = "INSERT INTO suppliers_row_format (sku,supplier,supplier_sku,eancode, webshop_bp, product_id, actual_supplier, jrsean, jrssku, jrsnetprice,jrs_status,jrs_idealeverpakking,jrs_afwijkenidealeverpakking,jrs_verkoopeenheid,jrs_delivery_time,jrs_advice_price,jrs_currentdate,jrs_is_present,jrs_actual_supplier,polvoean,polvosku,polvonetprice,polvo_status,polvo_idealeverpakking,polvo_afwijkenidealeverpakking,polvo_verkoopeenheid,polvo_delivery_time,polvo_advise_price,polvo_currentdate,polvo_is_present,polvo_actual_supplier,nordwestean,nordwestsku,nordwestnetprice,nordwest_status,nordwest_idealeverpakking,nordwest_afwijkenidealeverpakking,nordwest_verkoopeenheid,nordwest_delivery_time,nordwest_advice_price,nordwest_currentdate,nordwest_is_present,nordwest_actual_supplier,zevijean,zevijsku,zevijnetprice,zevij_status,zevij_idealeverpakking,zevij_afwijkenidealeverpakking,zevij_verkoopeenheid,zevij_delivery_time,zevij_advice_price,zevij_currentdate,zevij_is_present,zevij_actual_supplier,dozonean, dozonsku,dozonnetprice,dozon_status,dozon_idealeverpakking,dozon_afwijkenidealeverpakking,dozon_verkoopeenheid,dozon_delivery_time,dozon_advice_price,dozon_currentdate,dozon_is_present,dozon_actual_supplier";
    $last_part_sql = " ON DUPLICATE KEY UPDATE ";
   
     
        $back_part_cols = "sku = VALUES(sku), supplier_sku = VALUES(supplier_sku), webshop_bp = VALUES(webshop_bp),product_id = VALUES(product_id),actual_supplier = VALUES(actual_supplier),jrsean = VALUES(jrsean),jrssku = VALUES(jrssku),jrsnetprice = VALUES(jrsnetprice),jrs_status= VALUES(jrs_status),jrs_idealeverpakking= VALUES(jrs_idealeverpakking),jrs_afwijkenidealeverpakking= VALUES(jrs_afwijkenidealeverpakking),jrs_verkoopeenheid= VALUES(jrs_verkoopeenheid),jrs_delivery_time= VALUES(jrs_delivery_time),jrs_advice_price= VALUES(jrs_advice_price),jrs_currentdate= VALUES(jrs_currentdate),jrs_is_present= VALUES(jrs_is_present),jrs_actual_supplier= VALUES(jrs_actual_supplier),polvoean = VALUES(polvoean),polvosku = VALUES(polvosku),polvonetprice = VALUES(polvonetprice),polvo_status = VALUES(polvo_status),polvo_idealeverpakking = VALUES(polvo_idealeverpakking),polvo_afwijkenidealeverpakking = VALUES(polvo_afwijkenidealeverpakking),polvo_verkoopeenheid = VALUES(polvo_verkoopeenheid),polvo_delivery_time=VALUES(polvo_delivery_time),polvo_advise_price=VALUES(polvo_advise_price),polvo_currentdate=VALUES(polvo_currentdate),polvo_is_present=VALUES(polvo_is_present),polvo_actual_supplier=VALUES(polvo_actual_supplier),nordwestean = VALUES(nordwestean),nordwestsku = VALUES(nordwestsku),nordwestnetprice = VALUES(nordwestnetprice),nordwest_status = VALUES(nordwest_status),nordwest_idealeverpakking = VALUES(nordwest_idealeverpakking),nordwest_afwijkenidealeverpakking = VALUES(nordwest_afwijkenidealeverpakking),nordwest_verkoopeenheid = VALUES(nordwest_verkoopeenheid),nordwest_delivery_time=VALUES(nordwest_delivery_time),nordwest_advice_price=VALUES(nordwest_advice_price),nordwest_currentdate=VALUES(nordwest_currentdate),nordwest_is_present=VALUES(nordwest_is_present),nordwest_actual_supplier=VALUES(nordwest_actual_supplier),zevijean = VALUES(zevijean),zevijsku = VALUES(zevijsku),zevijnetprice = VALUES(zevijnetprice),zevij_status = VALUES(zevij_status),zevij_idealeverpakking = VALUES(zevij_idealeverpakking),zevij_afwijkenidealeverpakking = VALUES(zevij_afwijkenidealeverpakking),zevij_verkoopeenheid = VALUES(zevij_verkoopeenheid),zevij_delivery_time=VALUES(zevij_delivery_time),zevij_advice_price=VALUES(zevij_advice_price),zevij_currentdate=VALUES(zevij_currentdate),zevij_is_present=VALUES(zevij_is_present),zevij_actual_supplier=VALUES(zevij_actual_supplier),dozonean = VALUES(dozonean),dozonsku = VALUES(dozonsku),dozonnetprice = VALUES(dozonnetprice),dozon_status = VALUES(dozon_status),dozon_idealeverpakking = VALUES(dozon_idealeverpakking),dozon_afwijkenidealeverpakking = VALUES(dozon_afwijkenidealeverpakking),dozon_verkoopeenheid = VALUES(dozon_verkoopeenheid),dozon_delivery_time=VALUES(dozon_delivery_time),dozon_advice_price=VALUES(dozon_advice_price),dozon_currentdate=VALUES(dozon_currentdate),dozon_is_present=VALUES(dozon_is_present),dozon_actual_supplier=VALUES(dozon_actual_supplier)";


        
        $last_part_sql .= $back_part_cols;
        $sql .= ") VALUES ";
        return array($sql, $last_part_sql);
}


function getSqlOfColumns($chunked_row) {

    $col_data = implode("', '", $chunked_row);
    $col_data = "'".$col_data.",";exit;


   
   /* $col_data = "'".$chunked_row['sku']."', '".$chunked_row['supplier']."', '".$chunked_row['supplier_sku']."', '".$chunked_row['eancode']."', '".$chunked_row['webshop_bp']."', '".$chunked_row['product_id']."', '".$chunked_row['actual_supplier']."', '".$chunked_row['jrsean']."', '".$chunked_row['jrssku']."', '".$chunked_row['jrsnetprice']."', '".$chunked_row['polvoean']."', '".$chunked_row['polvosku']."', '".$chunked_row['polvonetprice']."', '".$chunked_row['nordwestean']."', '".$chunked_row['nordwestsku']."', '".$chunked_row['nordwestnetprice']."', '".$chunked_row['zevijean']."', '".$chunked_row['zevijsku']."', '".$chunked_row['zevijnetprice']."', '".$chunked_row['dozonean']."', '".$chunked_row['dozonsku']."', '".$chunked_row['dozonnetprice']."'";*/
    
  
    return $col_data;
}//end getSqlOfColumns()

function bulkInsertLog($chunk_index,$chunk_msg) {
  $file_pricechunks_log = "insert_suppliers_row_format.txt";

  $result = file_put_contents($file_pricechunks_log,"".date("d-m-Y H:i:s")." Updated Price Chunk (".$chunk_index."):-".$chunk_msg."\n", FILE_APPEND);

  if ($result === false) {
    $error = error_get_last();
    echo "File put contents error: " . $error['message'];
}

}//end bulkInsertLog()


function makeRowFormat($supplier_name, $its_data, &$supplier_row_format_array) {
    if($supplier_name == 'JRS') {

        $supplier_row_format_array[$its_data['product_id']]['jrsean'] = $its_data['eancode'];
        $supplier_row_format_array[$its_data['product_id']]['jrssku'] = $its_data['supplier_sku'];
        $supplier_row_format_array[$its_data['product_id']]['jrsnetprice'] = $its_data['net_price'];

        $supplier_row_format_array[$its_data['product_id']]['jrs_status'] = $its_data['status'];
        $supplier_row_format_array[$its_data['product_id']]['jrs_idealeverpakking'] = $its_data['idealeverpakking'];
        $supplier_row_format_array[$its_data['product_id']]['jrs_afwijkenidealeverpakking'] = $its_data['afwijkenidealeverpakking'];

        $supplier_row_format_array[$its_data['product_id']]['jrs_verkoopeenheid'] = $its_data['verkoopeenheid'];
        $supplier_row_format_array[$its_data['product_id']]['jrs_delivery_time'] = $its_data['delivery_time'];

        $supplier_row_format_array[$its_data['product_id']]['jrs_advice_price'] = $its_data['advise_price'];
        $supplier_row_format_array[$its_data['product_id']]['jrs_currentdate'] = $its_data['currentdate'];
        $supplier_row_format_array[$its_data['product_id']]['jrs_is_present'] = $its_data['is_present'];
        $supplier_row_format_array[$its_data['product_id']]['jrs_actual_supplier'] = $its_data['actual_supplier'];
    } elseif($supplier_name == 'Polvo') {
        $supplier_row_format_array[$its_data['product_id']]['polvoean'] =$its_data['eancode'];
        $supplier_row_format_array[$its_data['product_id']]['polvosku'] = $its_data['supplier_sku'];
        $supplier_row_format_array[$its_data['product_id']]['polvonetprice'] = $its_data['net_price'];

        $supplier_row_format_array[$its_data['product_id']]['polvo_status'] = $its_data['status'];
        $supplier_row_format_array[$its_data['product_id']]['polvo_idealeverpakking'] = $its_data['idealeverpakking'];
        $supplier_row_format_array[$its_data['product_id']]['polvo_afwijkenidealeverpakking'] = $its_data['afwijkenidealeverpakking'];

        $supplier_row_format_array[$its_data['product_id']]['polvo_verkoopeenheid'] = $its_data['verkoopeenheid'];
        $supplier_row_format_array[$its_data['product_id']]['polvo_delivery_time'] = $its_data['delivery_time'];

        $supplier_row_format_array[$its_data['product_id']]['polvo_advice_price'] = $its_data['advise_price'];
        $supplier_row_format_array[$its_data['product_id']]['polvo_currentdate'] = $its_data['currentdate'];
        $supplier_row_format_array[$its_data['product_id']]['polvo_is_present'] = $its_data['is_present'];
        $supplier_row_format_array[$its_data['product_id']]['polvo_actual_supplier'] = $its_data['actual_supplier'];


    } elseif($supplier_name == 'Nordwest') {
        $supplier_row_format_array[$its_data['product_id']]['nordwestean'] =$its_data['eancode'];
        $supplier_row_format_array[$its_data['product_id']]['nordwestsku']  = $its_data['supplier_sku'];
        $supplier_row_format_array[$its_data['product_id']]['nordwestnetprice'] = $its_data['net_price'];

         $supplier_row_format_array[$its_data['product_id']]['nordwest_status'] = $its_data['status'];
        $supplier_row_format_array[$its_data['product_id']]['nordwest_idealeverpakking'] = $its_data['idealeverpakking'];
        $supplier_row_format_array[$its_data['product_id']]['nordwest_afwijkenidealeverpakking'] = $its_data['afwijkenidealeverpakking'];

        $supplier_row_format_array[$its_data['product_id']]['nordwest_verkoopeenheid'] = $its_data['verkoopeenheid'];
        $supplier_row_format_array[$its_data['product_id']]['nordwest_delivery_time'] = $its_data['delivery_time'];

        $supplier_row_format_array[$its_data['product_id']]['nordwest_advice_price'] = $its_data['advise_price'];
        $supplier_row_format_array[$its_data['product_id']]['nordwest_currentdate'] = $its_data['currentdate'];
        $supplier_row_format_array[$its_data['product_id']]['nordwest_is_present'] = $its_data['is_present'];
        $supplier_row_format_array[$its_data['product_id']]['nordwest_actual_supplier'] = $its_data['actual_supplier'];



    } elseif($supplier_name == 'Zevij') {
        $supplier_row_format_array[$its_data['product_id']]['zevijean'] = $its_data['eancode'];
        $supplier_row_format_array[$its_data['product_id']]['zevijsku'] = $its_data['supplier_sku'];
        $supplier_row_format_array[$its_data['product_id']]['zevijnetprice'] = $its_data['net_price'];

         $supplier_row_format_array[$its_data['product_id']]['zevij_status'] = $its_data['status'];
        $supplier_row_format_array[$its_data['product_id']]['zevij_idealeverpakking'] = $its_data['idealeverpakking'];
        $supplier_row_format_array[$its_data['product_id']]['zevij_afwijkenidealeverpakking'] = $its_data['afwijkenidealeverpakking'];

        $supplier_row_format_array[$its_data['product_id']]['zevij_verkoopeenheid'] = $its_data['verkoopeenheid'];
        $supplier_row_format_array[$its_data['product_id']]['zevij_delivery_time'] = $its_data['delivery_time'];

        $supplier_row_format_array[$its_data['product_id']]['zevij_advice_price'] = $its_data['advise_price'];
        $supplier_row_format_array[$its_data['product_id']]['zevij_currentdate'] = $its_data['currentdate'];
        $supplier_row_format_array[$its_data['product_id']]['zevij_is_present'] = $its_data['is_present'];
        $supplier_row_format_array[$its_data['product_id']]['zevij_actual_supplier'] = $its_data['actual_supplier'];



    } elseif($supplier_name == 'Dozon') {
        $supplier_row_format_array[$its_data['product_id']]['dozonean'] = $its_data['eancode'];
        $supplier_row_format_array[$its_data['product_id']]['dozonsku'] = $its_data['supplier_sku'];
        $supplier_row_format_array[$its_data['product_id']]['dozonnetprice'] = $its_data['net_price'];

         $supplier_row_format_array[$its_data['product_id']]['dozon_status'] = $its_data['status'];
        $supplier_row_format_array[$its_data['product_id']]['dozon_idealeverpakking'] = $its_data['idealeverpakking'];
        $supplier_row_format_array[$its_data['product_id']]['dozon_afwijkenidealeverpakking'] = $its_data['afwijkenidealeverpakking'];

        $supplier_row_format_array[$its_data['product_id']]['dozon_verkoopeenheid'] = $its_data['verkoopeenheid'];
        $supplier_row_format_array[$its_data['product_id']]['dozon_delivery_time'] = $its_data['delivery_time'];

        $supplier_row_format_array[$its_data['product_id']]['dozon_advice_price'] = $its_data['advise_price'];
        $supplier_row_format_array[$its_data['product_id']]['dozon_currentdate'] = $its_data['currentdate'];
        $supplier_row_format_array[$its_data['product_id']]['dozon_is_present'] = $its_data['is_present'];
        $supplier_row_format_array[$its_data['product_id']]['dozon_actual_supplier'] = $its_data['actual_supplier'];

    }
}//end makeRowFormat()