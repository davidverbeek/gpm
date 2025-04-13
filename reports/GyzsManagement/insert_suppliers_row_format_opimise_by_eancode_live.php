<?php

include "config/config.php";
include "define/constants.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set("memory_limit", "10G");
ini_set("max_execution_time", 0);

 if($conn->query("TRUNCATE TABLE suppliers_row_format")) {
    bulkInsertLog('null',"Truncated suppliers_row_format:");
}

$roas = array();
if($result_settings=$conn->query("SELECT roas FROM pm_settings")) {
    $row = $result_settings->fetch_assoc();
    $roas = json_decode($row['roas'],true);
}
$jsonFilePath = 'qtypdata.json';

// Read the file content
$jsonContent = file_get_contents($jsonFilePath);

// Decode the JSON content into a PHP associative array
$dataArray = json_decode($jsonContent, true);
// Check if there was an error during decoding
if (json_last_error() !== JSON_ERROR_NONE) {
    echo 'Error decoding JSON: ' . json_last_error_msg();
    exit;
}

$jsonFilePath_nws = 'qtypdata_non_webshop_supplier.json';

// Read the file content
$jsonContent_nws = file_get_contents($jsonFilePath_nws);

// Decode the JSON content into a PHP associative array
$dataArray_nws = json_decode($jsonContent_nws, true);
// Check if there was an error during decoding
if (json_last_error() !== JSON_ERROR_NONE) {
    echo 'Error decoding Supplier-JSON: ' . json_last_error_msg();
    exit;
}

$max_order_qty = 0;

//insertInRowFormat_without_eancode();
insertInRowFormat_with_eancode();

function insertInRowFormat_without_eancode() {
    global $conn;
    $webshop_sql = "SELECT * FROM all_suppliers_data_source WHERE is_present ='webshop' AND (eancode = '' || eancode = '0' || eancode = '?')";
    $result = $conn->query($webshop_sql);
    
    $supplier_row_format_array = array();
    $initiate_non_webshop_supplier_data = array();

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
        $relevance_art = get_required_score($row_webshop, $row_webshop['product_id']);
          
        makeRowFormat('JRS', $initiate_non_webshop_supplier_data, $supplier_row_format_array);
        makeRowFormat('Polvo', $initiate_non_webshop_supplier_data, $supplier_row_format_array);
        makeRowFormat('Nordwest', $initiate_non_webshop_supplier_data, $supplier_row_format_array);
        makeRowFormat('Zevij', $initiate_non_webshop_supplier_data, $supplier_row_format_array);
        makeRowFormat('Dozon', $initiate_non_webshop_supplier_data, $supplier_row_format_array);
        makeRowFormat('Burghouwt', $initiate_non_webshop_supplier_data, $supplier_row_format_array);
          
        if($row_webshop['supplier'] == 'JRS') {
            makeRowFormat('JRS', $row_webshop, $supplier_row_format_array,1);
            $supplier_row_format_array[$row_webshop['product_id']]['jrs_relevance_score'] += $relevance_art;
        } elseif ($row_webshop['supplier'] == 'Polvo') {
            makeRowFormat('Polvo', $row_webshop, $supplier_row_format_array,1);
            $supplier_row_format_array[$row_webshop['product_id']]['polvo_relevance_score'] += $relevance_art;
        } elseif ($row_webshop['supplier'] == 'Nordwest') {
            makeRowFormat('Nordwest', $row_webshop, $supplier_row_format_array,1);
            $supplier_row_format_array[$row_webshop['product_id']]['nordwest_relevance_score'] += $relevance_art;
        } elseif ($row_webshop['supplier'] == 'Zevij') {
            makeRowFormat('Zevij', $row_webshop, $supplier_row_format_array,1);
            $supplier_row_format_array[$row_webshop['product_id']]['zevij_relevance_score'] += $relevance_art;
        } elseif ($row_webshop['supplier'] == 'Dozon') {
            makeRowFormat('Dozon', $row_webshop, $supplier_row_format_array,1);
            $supplier_row_format_array[$row_webshop['product_id']]['dozon_relevance_score'] += $relevance_art;
        } elseif ($row_webshop['supplier'] == 'Burghouwt') {
            makeRowFormat('Burghouwt', $row_webshop, $supplier_row_format_array,1);
            $supplier_row_format_array[$row_webshop['product_id']]['burghouwt_relevance_score'] += $relevance_art;
        }

        unset($supplier_row_format_array[$row_webshop['product_id']]['jrs_min_ord_qty']);
        unset($supplier_row_format_array[$row_webshop['product_id']]['polvo_min_ord_qty']);
        unset($supplier_row_format_array[$row_webshop['product_id']]['nordwest_min_ord_qty']);
        unset($supplier_row_format_array[$row_webshop['product_id']]['zevij_min_ord_qty']);
        unset($supplier_row_format_array[$row_webshop['product_id']]['dozon_min_ord_qty']);
        unset($supplier_row_format_array[$row_webshop['product_id']]['burghouwt_min_ord_qty']);
        
     
        
    }//end loop getting all webshop
 //var_dump($supplier_row_format_array);exit;
    // insert this data into database
    $chunk_format_data = array_chunk($supplier_row_format_array, 1000);

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

                $modified_at = array(date('Y-m-d H:i:s'));
                $col_data = implode("', '", array_merge($row,$modified_at));

                //$one_rowString = getSqlOfColumns($row);


                $one_rowString = "'".$col_data."'";
                $join_cols_names .= $one_rowString;
                $join_cols_names .= ')';

                $all_col_data[] = $join_cols_names;
            }

            //var_dump($all_col_data);exit;

            if(count($all_col_data) > 0) {
                $chunk_sql = $sql_row_format.implode(",", $all_col_data) . $last_part_sql;

                //if($chunked_idx == 0) {
                   /*  if($conn->query("TRUNCATE TABLE suppliers_row_format")) {
                        bulkInsertLog($chunked_idx,"Truncated suppliers_row_format:");
                    } */
               // }

                    if($conn->query($chunk_sql)) {
                        bulkInsertLog($chunked_idx,"Bulk Update:".count($chunked_item));                  
                    } else {
                       // echo mysqli_error($conn)."\n".$chunk_sql;exit;
                        bulkInsertLog($chunked_idx,"Bulk Update Error:".mysqli_error($conn)."\n".$chunk_sql);
                    }
            }
           // unset($all_col_data);
        }

    }

   
}

function insertInRowFormat_with_eancode() {
   
    global $conn,$roas,$max_order_qty;

    // AND eancode='4021226301708',8714678114670  and eancode='8714678114670'
    $webshop_sql = "SELECT * FROM all_suppliers_data_source WHERE is_present ='webshop' AND (eancode != ''  && eancode != '0' && eancode != '?')";
    
    $result = $conn->query($webshop_sql);
    
    $supplier_row_format_array = array();
    $initiate_non_webshop_supplier_data = array();
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

    $initiate_non_webshop_supplier_data['manufacture_relevance'] = 0;
    $initiate_non_webshop_supplier_data['manufacture_row_id'] = 0;


    $manufacture_counter=1;
    while($row_webshop = $result->fetch_assoc())
    {

        //echo $row_webshop['sku']."===".$row_webshop['eancode']."\n";
        $supplier_row_format_array[$row_webshop['product_id']]['sku'] = $row_webshop['sku'];
        $supplier_row_format_array[$row_webshop['product_id']]['supplier'] = $row_webshop['supplier'];
        $supplier_row_format_array[$row_webshop['product_id']]['supplier_sku'] = $row_webshop['supplier_sku'];
        $supplier_row_format_array[$row_webshop['product_id']]['eancode'] = $row_webshop['eancode'];
        $supplier_row_format_array[$row_webshop['product_id']]['webshop_bp'] = $row_webshop['net_price'];
        $supplier_row_format_array[$row_webshop['product_id']]['product_id'] = $row_webshop['product_id'];
        $supplier_row_format_array[$row_webshop['product_id']]['actual_supplier'] = $row_webshop['actual_supplier'];

        $initiate_non_webshop_supplier_data['product_id'] = $row_webshop['product_id'];    
        
        makeRowFormat('JRS', $initiate_non_webshop_supplier_data, $supplier_row_format_array);
        makeRowFormat('Polvo', $initiate_non_webshop_supplier_data, $supplier_row_format_array);
        makeRowFormat('Nordwest', $initiate_non_webshop_supplier_data, $supplier_row_format_array);
        makeRowFormat('Zevij', $initiate_non_webshop_supplier_data, $supplier_row_format_array);
        makeRowFormat('Dozon', $initiate_non_webshop_supplier_data, $supplier_row_format_array);
        makeRowFormat('Burghouwt', $initiate_non_webshop_supplier_data, $supplier_row_format_array);
      //  makeRowFormat('manufacture', $initiate_non_webshop_supplier_data, $supplier_row_format_array);



      //160824  if($row_webshop['eancode'] != '' && $row_webshop['eancode'] != '?' && $row_webshop['eancode'] !== '0') { 
        //    echo $row_webshop['eancode'];exit;

            $ean = $row_webshop['eancode'];
            $for_query = "'".$ean."'";
            if(strlen($ean) == 13) {
                $ean_14 = '0'.$ean;
                $for_query = "'".$ean."'".","."'".$ean_14."'";
            }

            //echo $for_query;exit;
            $suppliers_sql = "SELECT * FROM all_suppliers_data_source WHERE eancode IN (".$for_query.") AND (status=1 or status=2) ORDER BY net_price ASC";

            if(!$conn->query($suppliers_sql)) {
                bulkInsertLog(0,"Bulk Update Error:".mysqli_error($conn)."\n".$suppliers_sql."\n".$row_webshop['product_id']);
                exit;
            }

           
           // var_dump($supplier_row_format_array);exit;

            $result_s = $conn->query($suppliers_sql);
            $cheapest_price = 1;
            $max_order_qty = 0;
            $jrs_relevance_afw = $zevij_relevance_afw = $nordwest_relevance_afw = $polvo_relevance_afw = $dozon_relevance_afw = $burghouwt_relevance_afw = 0;
            $ean_suppliers=array();

            while($row_s=$result_s->fetch_assoc()) {

                $product_id_actual = $row_s['product_id'];               
                $relevance_art = get_required_score($row_s, $product_id_actual);

               
                if($row_s['supplier'] == 'JRS' && ($row_s['product_id']==0 || $row_s['product_id']==$row_webshop['product_id'])) {
                    $row_s['product_id'] = $row_webshop['product_id'];
                    makeRowFormat('JRS', $row_s, $supplier_row_format_array,$cheapest_price);
                    $supplier_row_format_array[$row_webshop['product_id']]['jrs_relevance_score'] += $relevance_art;
                    $ean_suppliers[] = 'JRS';
                    //  var_dump($supplier_row_format_array);exit;

                }elseif($row_s['supplier'] == 'Polvo' && ($row_s['product_id']==0 || $row_s['product_id']==$row_webshop['product_id'])) {
                    $row_s['product_id'] = $row_webshop['product_id'];
                    makeRowFormat('Polvo', $row_s, $supplier_row_format_array,$cheapest_price);
                    
                    $supplier_row_format_array[$row_webshop['product_id']]['polvo_relevance_score'] += 
                    $relevance_art;
                    $ean_suppliers[] = 'Polvo';

                }elseif($row_s['supplier'] == 'Nordwest' && ($row_s['product_id']==0 || $row_s['product_id']==$row_webshop['product_id'])) {
                    $row_s['product_id'] = $row_webshop['product_id'];
                     makeRowFormat('Nordwest', $row_s, $supplier_row_format_array,$cheapest_price);
                     $supplier_row_format_array[$row_webshop['product_id']]['nordwest_relevance_score'] += 
                     $relevance_art;
                     $ean_suppliers[] = 'Nordwest';
                }elseif($row_s['supplier'] == 'Zevij' && ($row_s['product_id']==0 || $row_s['product_id']==$row_webshop['product_id'])) { //Zevij is case sensitive
                    $row_s['product_id'] = $row_webshop['product_id'];
                      makeRowFormat('Zevij', $row_s, $supplier_row_format_array,$cheapest_price);
                      $supplier_row_format_array[$row_webshop['product_id']]['zevij_relevance_score'] += 
                      $relevance_art;
                      $ean_suppliers[] = 'Zevij';
                }elseif($row_s['supplier'] == 'Dozon' && ($row_s['product_id']==0 || $row_s['product_id']==$row_webshop['product_id'])) {
                    $row_s['product_id'] = $row_webshop['product_id'];
                      makeRowFormat('Dozon', $row_s, $supplier_row_format_array,$cheapest_price);
                      $supplier_row_format_array[$row_webshop['product_id']]['dozon_relevance_score'] += 
                      $relevance_art;
                      $ean_suppliers[] = 'Dozon';
                }elseif($row_s['supplier'] == 'Burghouwt' && ($row_s['product_id']==0 || $row_s['product_id']==$row_webshop['product_id'])) {
                    $row_s['product_id'] = $row_webshop['product_id'];
                      makeRowFormat('Burghouwt', $row_s, $supplier_row_format_array,$cheapest_price);
                      $supplier_row_format_array[$row_webshop['product_id']]['burghouwt_relevance_score'] += 
                      $relevance_art;
                      $ean_suppliers[] = 'Burghouwt';
                }else {

                   /*  $relevance_chp = $roas['supplier_weights']['weight_bp'] * $cheapest_price;
                    $relevance_dt = $roas['supplier_delivery_time']['gyzs'] * $roas['supplier_weights']['weight_delivery'];

                    if($row_s['afwijkenidealeverpakking'] == 0) {
                    $supp_min_order = $row_s['idealeverpakking'];
                    } else {
                    $supp_min_order = 1;
                    }
                    $supplier_row_format_array[$row_webshop['product_id']]["{$row_s['supplier']}_min_ord_qty"] = $supp_min_order;

                    $supplier_row_format_array[$row_webshop['product_id']]["{$row_s['supplier']}_relevance_score"] = $relevance_chp+ $relevance_dt;
                        
 */
                     /* $supplier_row_format_array[$row_webshop['product_id']]["{$row_s['supplier']}_relevance_score"] += 
                      $relevance_art;*/
                      //$ean_suppliers[] = $row_s['supplier'];
                }
                $cheapest_price -= 0.1;

            }//end loop of all rows of each ean

        //160824}//end if of ean having 13 digits
        
    

            if(in_array('JRS', $ean_suppliers)) {
                $jrs_relevance_afw = ($max_order_qty - $supplier_row_format_array[$row_webshop['product_id']]['jrs_min_ord_qty'])/100;
              
                $supplier_row_format_array[$row_webshop['product_id']]['jrs_relevance_score'] += 
                ($jrs_relevance_afw * $roas['supplier_weights']['weight_qty']);
               
            }

            if(in_array('Polvo', $ean_suppliers)) {
                $polvo_relevance_afw = ($max_order_qty - $supplier_row_format_array[$row_webshop['product_id']]['polvo_min_ord_qty'])/100;
                $supplier_row_format_array[$row_webshop['product_id']]['polvo_relevance_score'] += 
                ($polvo_relevance_afw * $roas['supplier_weights']['weight_qty']);               
            }

           
            if(in_array('Nordwest', $ean_suppliers)) {
                $nordwest_relevance_afw = ($max_order_qty - $supplier_row_format_array[$row_webshop['product_id']]['nordwest_min_ord_qty'])/100;
                $supplier_row_format_array[$row_webshop['product_id']]['nordwest_relevance_score'] += 
                ($nordwest_relevance_afw * $roas['supplier_weights']['weight_qty']);               
            }

            if(in_array('Zevij', $ean_suppliers)) {
                $zevij_relevance_afw = ($max_order_qty - $supplier_row_format_array[$row_webshop['product_id']]['zevij_min_ord_qty'])/100;
                $supplier_row_format_array[$row_webshop['product_id']]['zevij_relevance_score'] += 
                ($zevij_relevance_afw * $roas['supplier_weights']['weight_qty']);               
            }

            if(in_array('Dozon', $ean_suppliers)) {
                $dozon_relevance_afw = ($max_order_qty - $supplier_row_format_array[$row_webshop['product_id']]['dozon_min_ord_qty'])/100;
                $supplier_row_format_array[$row_webshop['product_id']]['dozon_relevance_score'] += 
                ($dozon_relevance_afw * $roas['supplier_weights']['weight_qty']);
            }

            if(in_array('Burghouwt', $ean_suppliers)) {
                $burghouwt_relevance_afw = ($max_order_qty - $supplier_row_format_array[$row_webshop['product_id']]['burghouwt_min_ord_qty'])/100;
                $supplier_row_format_array[$row_webshop['product_id']]['burghouwt_relevance_score'] += 
                ($burghouwt_relevance_afw * $roas['supplier_weights']['weight_qty']);
            }

            unset($supplier_row_format_array[$row_webshop['product_id']]['jrs_min_ord_qty']);
            unset($supplier_row_format_array[$row_webshop['product_id']]['polvo_min_ord_qty']);
            unset($supplier_row_format_array[$row_webshop['product_id']]['nordwest_min_ord_qty']);
            unset($supplier_row_format_array[$row_webshop['product_id']]['zevij_min_ord_qty']);
            unset($supplier_row_format_array[$row_webshop['product_id']]['dozon_min_ord_qty']);
             unset($supplier_row_format_array[$row_webshop['product_id']]['burghouwt_min_ord_qty']);

//var_dump($supplier_row_format_array);exit;

        
    }//end loop getting all webshop

//var_dump(count($supplier_row_format_array));exit;
//var_dump($supplier_row_format_array);exit;



    // insert this data into database
    $chunk_format_data = array_chunk($supplier_row_format_array, 1000);

    if(count($chunk_format_data)) {
        list($sql_row_format, $last_part_sql) = makeSqlForRowFormat();

        foreach($chunk_format_data as $chunked_idx=>$chunked_item) {
            $all_col_data = array();
            $chunk_sql = "";
            foreach($chunked_item as $c_k=>$row) {
                $one_rowString = $col_data ='';
                $join_cols_names = '(';

                //file_put_contents('hhhello.txt', $c_k."----".count($row),FILE_APPEND);
            /* if($chunked_idx==0 && $c_k == 4) {
                    var_dump($row);exit;
                }*/

                $modified_at = array(date('Y-m-d H:i:s'));
                $col_data = implode("', '", array_merge($row,$modified_at));
                //$col_data = implode("', '", $row);

                //$one_rowString = getSqlOfColumns($row);


                $one_rowString = "'".$col_data."'";
                $join_cols_names .= $one_rowString;
                $join_cols_names .= ')';

                $all_col_data[] = $join_cols_names;
            }

            //var_dump($all_col_data);exit;

            if(count($all_col_data) > 0) {
                $chunk_sql = $sql_row_format.implode(",", $all_col_data) . $last_part_sql;

               /*  if($chunked_idx == 0) {
                    if($conn->query("TRUNCATE TABLE suppliers_row_format")) {
                        bulkInsertLog($chunked_idx,"Truncated suppliers_row_format:");
                    }
                } */

                    if($conn->query($chunk_sql)) {
                        bulkInsertLog($chunked_idx,"Bulk Update:".count($chunked_item));                    
                    } else {
                       // echo mysqli_error($conn)."\n".$chunk_sql;exit;
                        bulkInsertLog($chunked_idx,"Bulk Update Error:".mysqli_error($conn)."\n".$chunk_sql);
                    }
            }
            
        }

    }

   
}

function makeSqlForRowFormat()
{
   $sql = "INSERT INTO suppliers_row_format (sku,supplier,supplier_sku,eancode, webshop_bp, product_id, actual_supplier, jrsean, jrssku, jrsnetprice,jrs_status,jrs_idealeverpakking,jrs_afwijkenidealeverpakking,jrs_verkoopeenheid,jrs_delivery_time,jrs_advice_price,jrs_currentdate,jrs_is_present,jrs_actual_supplier,jrs_relevance_score,polvoean,polvosku,polvonetprice,polvo_status,polvo_idealeverpakking,polvo_afwijkenidealeverpakking,polvo_verkoopeenheid,polvo_delivery_time,polvo_advise_price,polvo_currentdate,polvo_is_present,polvo_actual_supplier,polvo_relevance_score,nordwestean,nordwestsku,nordwestnetprice,nordwest_status,nordwest_idealeverpakking,nordwest_afwijkenidealeverpakking,nordwest_verkoopeenheid,nordwest_delivery_time,nordwest_advice_price,nordwest_currentdate,nordwest_is_present,nordwest_actual_supplier,nordwest_relevance_score,zevijean,zevijsku,zevijnetprice,zevij_status,zevij_idealeverpakking,zevij_afwijkenidealeverpakking,zevij_verkoopeenheid,zevij_delivery_time,zevij_advice_price,zevij_currentdate,zevij_is_present,zevij_actual_supplier,zevij_relevance_score,dozonean, dozonsku,dozonnetprice,dozon_status,dozon_idealeverpakking,dozon_afwijkenidealeverpakking,dozon_verkoopeenheid,dozon_delivery_time,dozon_advice_price,dozon_currentdate,dozon_is_present,dozon_actual_supplier,dozon_relevance_score,burghouwtean,burghouwtsku,burghouwtnetprice,burghouwt_status,burghouwt_idealeverpakking,burghouwt_afwijkenidealeverpakking,burghouwt_verkoopeenheid,burghouwt_delivery_time,burghouwt_advice_price,burghouwt_currentdate,burghouwt_is_present,burghouwt_actual_supplier,burghouwt_relevance_score,modified_at";
   $last_part_sql = " ON DUPLICATE KEY UPDATE ";
   
$back_part_cols = "sku = VALUES(sku), supplier_sku = VALUES(supplier_sku), webshop_bp = VALUES(webshop_bp),actual_supplier = VALUES(actual_supplier),jrsean = VALUES(jrsean),jrssku = VALUES(jrssku),jrsnetprice = VALUES(jrsnetprice),jrs_status= VALUES(jrs_status),jrs_idealeverpakking= VALUES(jrs_idealeverpakking),jrs_afwijkenidealeverpakking= VALUES(jrs_afwijkenidealeverpakking),jrs_verkoopeenheid= VALUES(jrs_verkoopeenheid),jrs_delivery_time= VALUES(jrs_delivery_time),jrs_advice_price= VALUES(jrs_advice_price),jrs_currentdate= VALUES(jrs_currentdate),jrs_is_present= VALUES(jrs_is_present),jrs_actual_supplier= VALUES(jrs_actual_supplier),jrs_relevance_score= VALUES(jrs_relevance_score),polvoean = VALUES(polvoean),polvosku = VALUES(polvosku),polvonetprice = VALUES(polvonetprice),polvo_status = VALUES(polvo_status),polvo_idealeverpakking = VALUES(polvo_idealeverpakking),polvo_afwijkenidealeverpakking = VALUES(polvo_afwijkenidealeverpakking),polvo_verkoopeenheid = VALUES(polvo_verkoopeenheid),polvo_delivery_time=VALUES(polvo_delivery_time),polvo_advise_price=VALUES(polvo_advise_price),polvo_currentdate=VALUES(polvo_currentdate),polvo_is_present=VALUES(polvo_is_present),polvo_actual_supplier=VALUES(polvo_actual_supplier),polvo_relevance_score= VALUES(polvo_relevance_score),nordwestean = VALUES(nordwestean),nordwestsku = VALUES(nordwestsku),nordwestnetprice = VALUES(nordwestnetprice),nordwest_status = VALUES(nordwest_status),nordwest_idealeverpakking = VALUES(nordwest_idealeverpakking),nordwest_afwijkenidealeverpakking = VALUES(nordwest_afwijkenidealeverpakking),nordwest_verkoopeenheid = VALUES(nordwest_verkoopeenheid),nordwest_delivery_time=VALUES(nordwest_delivery_time),nordwest_advice_price=VALUES(nordwest_advice_price),nordwest_currentdate=VALUES(nordwest_currentdate),nordwest_is_present=VALUES(nordwest_is_present),nordwest_actual_supplier=VALUES(nordwest_actual_supplier),nordwest_relevance_score= VALUES(nordwest_relevance_score),zevijean = VALUES(zevijean),zevijsku = VALUES(zevijsku),zevijnetprice = VALUES(zevijnetprice),zevij_status = VALUES(zevij_status),zevij_idealeverpakking = VALUES(zevij_idealeverpakking),zevij_afwijkenidealeverpakking = VALUES(zevij_afwijkenidealeverpakking),zevij_verkoopeenheid = VALUES(zevij_verkoopeenheid),zevij_delivery_time=VALUES(zevij_delivery_time),zevij_advice_price=VALUES(zevij_advice_price),zevij_currentdate=VALUES(zevij_currentdate),zevij_is_present=VALUES(zevij_is_present),zevij_actual_supplier=VALUES(zevij_actual_supplier),zevij_relevance_score= VALUES(zevij_relevance_score),dozonean = VALUES(dozonean),dozonsku = VALUES(dozonsku),dozonnetprice = VALUES(dozonnetprice),dozon_status = VALUES(dozon_status),dozon_idealeverpakking = VALUES(dozon_idealeverpakking),dozon_afwijkenidealeverpakking = VALUES(dozon_afwijkenidealeverpakking),dozon_verkoopeenheid = VALUES(dozon_verkoopeenheid),dozon_delivery_time=VALUES(dozon_delivery_time),dozon_advice_price=VALUES(dozon_advice_price),dozon_currentdate=VALUES(dozon_currentdate),dozon_is_present=VALUES(dozon_is_present),dozon_actual_supplier=VALUES(dozon_actual_supplier),dozon_relevance_score= VALUES(dozon_relevance_score),burghouwtean=VALUES(burghouwtean),burghouwtsku=VALUES(burghouwtsku),burghouwtnetprice=VALUES(burghouwtnetprice),burghouwt_status=VALUES(burghouwt_status),burghouwt_idealeverpakking=VALUES(burghouwt_idealeverpakking),burghouwt_afwijkenidealeverpakking=VALUES(burghouwt_afwijkenidealeverpakking),burghouwt_verkoopeenheid=VALUES(burghouwt_verkoopeenheid),burghouwt_delivery_time=VALUES(burghouwt_delivery_time),burghouwt_advice_price=VALUES(burghouwt_advice_price),burghouwt_currentdate=VALUES(burghouwt_currentdate),burghouwt_is_present=VALUES(burghouwt_is_present),burghouwt_actual_supplier=VALUES(burghouwt_actual_supplier),
burghouwt_relevance_score= VALUES(burghouwt_relevance_score),modified_at= VALUES(modified_at)";

        
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

  $result = file_put_contents($file_pricechunks_log,"".date("d-m-Y H:i:s")." Inserted Suppliers-Row-Format Chunk (".$chunk_index."):-".$chunk_msg."\n", FILE_APPEND);

  if ($result === false) {
    $error = error_get_last();
    echo "File put contents error: " . $error['message'];
}

}//end bulkInsertLog()


function makeRowFormat($supplier_name, $its_data, &$supplier_row_format_array,$cheapest_price_score=0,$manufacture_counter=1) {
     global $roas, $max_order_qty;
     if($its_data['afwijkenidealeverpakking'] == 0 && $max_order_qty < $its_data['idealeverpakking']) {
        $max_order_qty = $its_data['idealeverpakking'];
    }
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
         $supplier_row_format_array[$its_data['product_id']]['jrs_relevance_score'] = 0;

        if($cheapest_price_score > 0) {
            $relevance_chp = $roas['supplier_weights']['weight_bp'] * $cheapest_price_score;
            $relevance_dt = $roas['supplier_delivery_time']['jrs'] * $roas['supplier_weights']['weight_delivery'];


            if($its_data['afwijkenidealeverpakking'] == 0) {
                $supp_min_order = $its_data['idealeverpakking'];
            } else {
                $supp_min_order = 1;
            }

            $supplier_row_format_array[$its_data['product_id']]['jrs_min_ord_qty'] = $supp_min_order;
            $supplier_row_format_array[$its_data['product_id']]['jrs_relevance_score'] = $relevance_chp + $relevance_dt;
        }


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
         $supplier_row_format_array[$its_data['product_id']]['polvo_relevance_score'] = 0;

        if($cheapest_price_score>0){
            $relevance_chp = $roas['supplier_weights']['weight_bp'] * $cheapest_price_score;
            $relevance_dt = $roas['supplier_delivery_time']['polvo'] * $roas['supplier_weights']['weight_delivery'];

            if($its_data['afwijkenidealeverpakking'] == 0) {
                $supp_min_order = $its_data['idealeverpakking'];
            } else {
                $supp_min_order = 1;
            }
            $supplier_row_format_array[$its_data['product_id']]['polvo_min_ord_qty'] = $supp_min_order;

            $supplier_row_format_array[$its_data['product_id']]['polvo_relevance_score'] = $relevance_chp+ $relevance_dt;
        }


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
        $supplier_row_format_array[$its_data['product_id']]['nordwest_relevance_score'] = 0;


         if($cheapest_price_score>0){
            $relevance_chp = $roas['supplier_weights']['weight_bp'] * $cheapest_price_score;
            $relevance_dt = $roas['supplier_delivery_time']['nordwest'] * $roas['supplier_weights']['weight_delivery'];            

            if($its_data['afwijkenidealeverpakking'] == 0){
            $supp_min_order = $its_data['idealeverpakking'];
            } else {
            $supp_min_order = 1;
            }
            $supplier_row_format_array[$its_data['product_id']]['nordwest_min_ord_qty'] = $supp_min_order;

            $supplier_row_format_array[$its_data['product_id']]['nordwest_relevance_score'] = $relevance_chp+ $relevance_dt;
        }



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
        $supplier_row_format_array[$its_data['product_id']]['zevij_relevance_score'] = 0;

         if($cheapest_price_score>0) {
            $relevance_chp = $roas['supplier_weights']['weight_bp'] * $cheapest_price_score;
            $relevance_dt = $roas['supplier_delivery_time']['zevij'] * $roas['supplier_weights']['weight_delivery'];

            if($its_data['afwijkenidealeverpakking'] == 0){
            $supp_min_order = $its_data['idealeverpakking'];
            } else {
            $supp_min_order = 1;
            }
            $supplier_row_format_array[$its_data['product_id']]['zevij_min_ord_qty'] = $supp_min_order;
            $supplier_row_format_array[$its_data['product_id']]['zevij_relevance_score'] = $relevance_chp+ $relevance_dt;

        }




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
         $supplier_row_format_array[$its_data['product_id']]['dozon_relevance_score'] = 0;

        if($cheapest_price_score>0){
            $relevance_chp = $roas['supplier_weights']['weight_bp'] * $cheapest_price_score;
            $relevance_dt = $roas['supplier_delivery_time']['dozon'] * $roas['supplier_weights']['weight_delivery'];

            if($its_data['afwijkenidealeverpakking'] == 0) {
                $supp_min_order = $its_data['idealeverpakking'];
            } else {
                $supp_min_order = 1;
            }
            $supplier_row_format_array[$its_data['product_id']]['dozon_min_ord_qty'] = $supp_min_order;
            $supplier_row_format_array[$its_data['product_id']]['dozon_relevance_score'] = $relevance_chp+ $relevance_dt;
        }

    } elseif($supplier_name == 'Burghouwt') {
        $supplier_row_format_array[$its_data['product_id']]['burghouwtean'] = $its_data['eancode'];
        $supplier_row_format_array[$its_data['product_id']]['burghouwtsku'] = $its_data['supplier_sku'];
        $supplier_row_format_array[$its_data['product_id']]['burghouwtnetprice'] = $its_data['net_price'];

         $supplier_row_format_array[$its_data['product_id']]['burghouwt_status'] = $its_data['status'];
        $supplier_row_format_array[$its_data['product_id']]['burghouwt_idealeverpakking'] = $its_data['idealeverpakking'];
        $supplier_row_format_array[$its_data['product_id']]['burghouwt_afwijkenidealeverpakking'] = $its_data['afwijkenidealeverpakking'];

        $supplier_row_format_array[$its_data['product_id']]['burghouwt_verkoopeenheid'] = $its_data['verkoopeenheid'];
        $supplier_row_format_array[$its_data['product_id']]['burghouwt_delivery_time'] = $its_data['delivery_time'];

        $supplier_row_format_array[$its_data['product_id']]['burghouwt_advice_price'] = $its_data['advise_price'];
        $supplier_row_format_array[$its_data['product_id']]['burghouwt_currentdate'] = $its_data['currentdate'];
        $supplier_row_format_array[$its_data['product_id']]['burghouwt_is_present'] = $its_data['is_present'];
        $supplier_row_format_array[$its_data['product_id']]['burghouwt_actual_supplier'] = $its_data['actual_supplier'];
        $supplier_row_format_array[$its_data['product_id']]['burghouwt_relevance_score'] = 0;


        if($cheapest_price_score>0){
            $relevance_chp = $roas['supplier_weights']['weight_bp'] * $cheapest_price_score;
            $relevance_dt = $roas['supplier_delivery_time']['burghouwt'] * $roas['supplier_weights']['weight_delivery'];

            if($its_data['afwijkenidealeverpakking'] == 0) {
                $supp_min_order = $its_data['idealeverpakking'];
            } else {
                $supp_min_order = 1;
            }
            $supplier_row_format_array[$its_data['product_id']]['burghouwt_min_ord_qty'] = $supp_min_order;
            $supplier_row_format_array[$its_data['product_id']]['burghouwt_relevance_score'] = $relevance_chp+ $relevance_dt;
        }

    } else {
        /*$supplier_row_format_array[$its_data['product_id']]['polvoean'] =$its_data['eancode'];
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
        $supplier_row_format_array[$its_data['product_id']]['polvo_actual_supplier'] = $its_data['actual_supplier'];*/
         $supplier_row_format_array[$its_data['product_id']]["{$manufacture_counter}_relevance_score"] = 0;

        if($cheapest_price_score > 0) {
            $supplier_row_format_array[$its_data['product_id']]["{$manufacture_counter}_file"] = $its_data['manufacture_row_id'];
            $relevance_chp = $roas['supplier_weights']['weight_bp'] * $cheapest_price_score;
            $relevance_dt = $roas['supplier_delivery_time']['gyzs'] * $roas['supplier_weights']['weight_delivery'];

            if($its_data['afwijkenidealeverpakking'] == 0) {
                $supp_min_order = $its_data['idealeverpakking'];
            } else {
                $supp_min_order = 1;
            }
            $supplier_row_format_array[$its_data['product_id']]["{$manufacture_counter}_min_ord_qty"] = $supp_min_order;

            $supplier_row_format_array[$its_data['product_id']]["{$manufacture_counter}_relevance_score"] = $relevance_chp+ $relevance_dt;
        }


    }
}//end makeRowFormat()


function get_required_score($its_data, $is_zero_product_id) {
    global $dataArray,$dataArray_nws,$roas;

        if ($is_zero_product_id != 0) {
            $product_quantity = isset($dataArray[$its_data['product_id']]['qty'])?$dataArray[$its_data['product_id']]['qty']:0;
        } else {
           $product_quantity = isset($dataArray_nws[$its_data['supplier_sku']])?$dataArray_nws[$its_data['supplier_sku']]:0;
        }

        

        if($its_data['status'] == '1' && $product_quantity > 0) {
            $sup_article_setting = $roas['supplier_article']['article_in'];
        } else if($its_data['status'] == '1' && $product_quantity < 0) {
            $sup_article_setting = $roas['supplier_article']['article_not'];
        }  else if($its_data['status'] == '2') {
            $sup_article_setting = $roas['supplier_article']['article_2'];
        } else {
            $sup_article_setting = $roas['supplier_article']['article_other'];
        }
        $sup_article_setting = $sup_article_setting;
        $sup_article_score = $sup_article_setting * $roas['supplier_weights']['weight_article'];
        //echo $sup_article_score.'<br>';
        return $sup_article_score;
}//get_required_score()


