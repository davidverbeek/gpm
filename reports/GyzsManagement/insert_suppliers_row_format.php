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



    $webshop_sql = "SELECT * FROM all_suppliers_data_source WHERE is_present ='webshop' AND eancode != '' ORDER BY eancode";
    $result = $conn->query($webshop_sql);
    
    $supplier_row_format_array = array();
    while($row_webshop = $result->fetch_assoc()) {

        $supplier_row_format_array[$row_webshop['eancode']]['sku'] = $row_webshop['sku'];
        $supplier_row_format_array[$row_webshop['eancode']]['supplier'] = $row_webshop['supplier'];
        $supplier_row_format_array[$row_webshop['eancode']]['supplier_sku'] = $row_webshop['supplier_sku'];
        $supplier_row_format_array[$row_webshop['eancode']]['eancode'] = $row_webshop['eancode'];
        $supplier_row_format_array[$row_webshop['eancode']]['webshop_bp'] = $row_webshop['net_price'];
        $supplier_row_format_array[$row_webshop['eancode']]['product_id'] = $row_webshop['product_id'];
        $supplier_row_format_array[$row_webshop['eancode']]['actual_supplier'] = $row_webshop['actual_supplier'];

        $supplier_row_format_array[$row_webshop['eancode']]['jrsean'] = '';
        $supplier_row_format_array[$row_webshop['eancode']]['jrssku'] = '';
        $supplier_row_format_array[$row_webshop['eancode']]['jrsnetprice'] = '';

        $supplier_row_format_array[$row_webshop['eancode']]['polvoean'] = '';
        $supplier_row_format_array[$row_webshop['eancode']]['polvosku'] = '';
        $supplier_row_format_array[$row_webshop['eancode']]['polvonetprice'] = '';

        $supplier_row_format_array[$row_webshop['eancode']]['nordwestean'] = '';
        $supplier_row_format_array[$row_webshop['eancode']]['nordwestsku'] = '';
        $supplier_row_format_array[$row_webshop['eancode']]['nordwestnetprice'] = '';

        $supplier_row_format_array[$row_webshop['eancode']]['zevijean'] = '';
        $supplier_row_format_array[$row_webshop['eancode']]['zevijsku'] = '';
        $supplier_row_format_array[$row_webshop['eancode']]['zevijnetprice'] = '';

        $supplier_row_format_array[$row_webshop['eancode']]['dozonean'] = '';
        $supplier_row_format_array[$row_webshop['eancode']]['dozonsku'] = '';
        $supplier_row_format_array[$row_webshop['eancode']]['dozonnetprice'] = '';


 
        if(strlen($row_webshop['eancode']) == 13) {
            //$i = 0;
            $eancode_14 = '0'+$row_webshop['eancode'];      
            $suppliers_sql = "SELECT * FROM all_suppliers_data_source WHERE eancode = '".$row_webshop['eancode']."' || eancode = '".$eancode_14."'";
             $result_s = $conn->query($suppliers_sql);
            while($row_s=$result_s->fetch_assoc()) {
               // $make_cols = $row_s['suppliers_count'];
              //  if($make_cols > 1) {
                    //echo $row_s;
                    file_put_contents('thursday_2507.txt',$row_s['eancode'].'/n', FILE_APPEND);
             //   }

              
                if($row_s['supplier'] == 'JRS') {

                    $supplier_row_format_array[$row_webshop['eancode']]['jrsean'] = $row_webshop['eancode'];
                    $supplier_row_format_array[$row_webshop['eancode']]['jrssku'] = $row_webshop['sku'];
                    $supplier_row_format_array[$row_webshop['eancode']]['jrsnetprice'] = $row_webshop['net_price'];

                }

                if($row_s['supplier'] == 'Polvo') {
                    $supplier_row_format_array[$row_webshop['eancode']]['polvoean'] =$row_webshop['eancode'];
                    $supplier_row_format_array[$row_webshop['eancode']]['polvosku'] = $row_webshop['sku'];
                    $supplier_row_format_array[$row_webshop['eancode']]['polvonetprice'] = $row_webshop['net_price'];

                }

                if($row_s['supplier'] == 'Nordwest') {
                    $supplier_row_format_array[$row_webshop['eancode']]['nordwestean'] = $row_webshop['eancode'];
                    $supplier_row_format_array[$row_webshop['eancode']]['nordwestsku'] = $row_webshop['sku'];
                    $supplier_row_format_array[$row_webshop['eancode']]['nordwestnetprice'] = $row_webshop['net_price'];

                }

                if($row_s['supplier'] == 'zevij') {
                    $supplier_row_format_array[$row_webshop['eancode']]['zevijean'] = $row_webshop['eancode'];
                    $supplier_row_format_array[$row_webshop['eancode']]['zevijsku'] = $row_webshop['sku'];
                    $supplier_row_format_array[$row_webshop['eancode']]['zevijnetprice'] = $row_webshop['net_price'];

                }
                if($row_s['supplier'] == 'dozon') {
                    $supplier_row_format_array[$row_webshop['eancode']]['dozonean'] = $row_webshop['eancode'];
                    $supplier_row_format_array[$row_webshop['eancode']]['dozonsku'] = $row_webshop['sku'];
                    $supplier_row_format_array[$row_webshop['eancode']]['dozonnetprice'] = $row_webshop['net_price'];
                }       
       

                //$i++;

            }//end loop of all rows of each ean

        }//end if of ean having 13 digits
        
       
        
    }//end loop getting all eancode

    //file_put_contents('friday.json',json_encode($supplier_row_format_array));exit;

    // insert this data into database
    $chunk_format_data = array_chunk($supplier_row_format_array, PMCHUNK);

//file_put_contents($chunk_format_data);exit;

    if(count($chunk_format_data)) {
        list($sql_row_format, $last_part_sql) = makeSqlForRowFormat();

        foreach($chunk_format_data as $chunked_idx=>$chunked_item) {
            $all_col_data = $updated_product_skus = $historyArray = array();
            $chunk_sql = "";
            foreach($chunked_item as $c_k=>$row) {
                $one_rowString = '';
                $join_cols_names = "(";
                $one_rowString = getSqlOfColumns($row);
                $join_cols_names .= $one_rowString;
                $join_cols_names .= ')';
                $all_col_data[] = $join_cols_names;
            }

            if(count($all_col_data) > 0) {
                $chunk_sql = $sql_row_format.implode(",", $all_col_data) . $last_part_sql;
                    if($conn->query($chunk_sql)) {
                        bulkInsertLog($chunked_idx,"Bulk Update:".count($chunked_item));                    
                } else {
                    bulkInsertLog($chunked_idx,"Bulk Update Error:".mysqli_error($conn)."\n".$chunk_sql);
                }
            }

            unset($all_col_data);

        }

    }

   
}

function makeSqlForRowFormat()
{
   $sql = "INSERT INTO suppliers_row_format (sku,supplier,supplier_sku,eancode, webshop_bp, product_id, actual_supplier, jrsean, jrssku, jrsnetprice,polvoean,polvosku,polvonetprice,nordwestean,nordwestsku,nordwestnetprice,zevijean,zevijsku,zevijnetprice, dozonean, dozonsku,dozonnetprice";
    $last_part_sql = " ON DUPLICATE KEY UPDATE ";
   
     
        $back_part_cols = "sku = VALUES(sku), supplier_sku = VALUES(supplier_sku), webshop_bp = VALUES(webshop_bp),product_id = VALUES(product_id),actual_supplier = VALUES(actual_supplier),jrsean = VALUES(jrsean),jrssku = VALUES(jrssku),jrsnetprice = VALUES(jrsnetprice),polvoean = VALUES(polvoean),polvosku = VALUES(polvosku),polvonetprice = VALUES(polvonetprice),nordwestean = VALUES(nordwestean),nordwestsku = VALUES(nordwestsku),nordwestnetprice = VALUES(nordwestnetprice),zevijean = VALUES(zevijean),zevijsku = VALUES(zevijsku),zevijnetprice = VALUES(zevijnetprice),nordwestean = VALUES(nordwestean),nordwestsku = VALUES(nordwestsku),nordwestnetprice = VALUES(nordwestnetprice),dozonean = VALUES(dozonean),dozonsku = VALUES(dozonsku),dozonnetprice = VALUES(dozonnetprice)";
        
        $last_part_sql .= $back_part_cols;
        $sql .= ") VALUES ";
        return array($sql, $last_part_sql);
}


function getSqlOfColumns($chunked_row) {
   
    $col_data = "'".$chunked_row['sku']."', '".$chunked_row['supplier']."', '".$chunked_row['supplier_sku']."', '".$chunked_row['eancode']."', '".$chunked_row['webshop_bp']."', '".$chunked_row['product_id']."', '".$chunked_row['actual_supplier']."', '".$chunked_row['jrsean']."', '".$chunked_row['jrssku']."', '".$chunked_row['jrsnetprice']."', '".$chunked_row['polvoean']."', '".$chunked_row['polvosku']."', '".$chunked_row['polvonetprice']."', '".$chunked_row['nordwestean']."', '".$chunked_row['nordwestsku']."', '".$chunked_row['nordwestnetprice']."', '".$chunked_row['zevijean']."', '".$chunked_row['zevijsku']."', '".$chunked_row['zevijnetprice']."', '".$chunked_row['dozonean']."', '".$chunked_row['dozonsku']."', '".$chunked_row['dozonnetprice']."'";
    
  
    return $col_data;
}//end getSqlOfColumns()

function bulkInsertLog($chunk_index,$chunk_msg) {
  $file_pricechunks_log = "insert_suppliers_row_format.txt";
  file_put_contents($file_pricechunks_log,"".date("d-m-Y H:i:s")." Updated Price Chunk (".$chunk_index."):-".$chunk_msg."\n",FILE_APPEND);
}//end bulkInsertLog()

