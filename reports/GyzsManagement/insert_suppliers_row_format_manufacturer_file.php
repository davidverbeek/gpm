<?php


include "config/config.php";
include "define/constants.php";
//header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set("memory_limit", "10G");
ini_set("max_execution_time", 0);

insertInRowFormat_with_eancode();

//echo json_encode(['status' => 'success', 'message' => 'Request received']);



function insertInRowFormat_with_eancode() {
   
    global $conn,$roas,$max_order_qty;

    $webshop_sql = "SELECT * FROM all_suppliers_data_source WHERE is_present ='webshop' AND (eancode != ''  && eancode != '0' && eancode != '?') ";

    $result = $conn->query($webshop_sql);  
    $supplier_row_format_array = array();

    while($row_webshop = $result->fetch_assoc())
    {     

            $ean = $row_webshop['eancode'];
            $for_query = "'".$ean."'";
            if(strlen($ean) == 13) {
                $ean_14 = '0'.$ean;
                $for_query = "'".$ean."'".","."'".$ean_14."'";
            }

             $suppliers_sql_check = "SELECT count(*) as total_manu FROM all_suppliers_data_source AS sd  WHERE sd.eancode IN (".$for_query.") AND is_present='manufacturers file' ";
           //  echo $suppliers_sql_check;

              $result_check = $conn->query($suppliers_sql_check);

            
               $row_check=$result_check->fetch_assoc();
               //  print_r($row_check['total_manu']);


             if($row_check['total_manu']) {
            
                $suppliers_sql = "SELECT min(sd.net_price) AS manufacture_price, sd.manufacture_id, mf.manu_filename, sd.eancode AS ean,sd.id FROM all_suppliers_data_source AS sd INNER JOIN manufacture_file_details AS mf ON sd.manufacture_id=mf.id WHERE sd.eancode IN (".$for_query.") AND is_present='manufacturers file' ";

                // echo $suppliers_sql;exit;

                if(!$conn->query($suppliers_sql)) {
                    bulkInsertLog(0,"Bulk Update Error:".mysqli_error($conn)."\n".$suppliers_sql."\n".$row_webshop['product_id']);
                    exit;
                }


                // var_dump($supplier_row_format_array);exit;

                $result_s = $conn->query($suppliers_sql);


                $row_s=$result_s->fetch_assoc();

                //   print_r($row_s);exit;

                $supplier_row_format_array[$row_webshop['product_id']]['manufacture_price'] = $row_s['manufacture_price'];
                $supplier_row_format_array[$row_webshop['product_id']]['manufacture_file'] = $row_s['manu_filename'];
                $supplier_row_format_array[$row_webshop['product_id']]['product_id'] =  $row_webshop['product_id'];
                $supplier_row_format_array[$row_webshop['product_id']]['cheapest_manufacturer_all_sup_ds_id'] = $row_s['id'];    
            }

        
    }//end loop getting all webshop

//var_dump((count($supplier_row_format_array)));exit;

    // insert this data into database
    $chunk_format_data = array_chunk($supplier_row_format_array, 5);

    //var_dump(($chunk_format_data));exit;
  //  echo count($chunk_format_data);exit;

    if(count($chunk_format_data)) {
          

        foreach($chunk_format_data as $chunked_idx=>$chunked_item) {
            $sql = "UPDATE suppliers_row_format SET ";
            $case_price = "manufacture_price = CASE ";
            $case_quantity = "manufacture_file = CASE ";
            $case_sd_id = "cheapest_manufacturer_all_sup_ds_id = CASE ";  
              $ids = [];  

            foreach ($chunked_item as $data) {
                $product_id = $data['product_id'];
                $case_price .= "WHEN product_id = $product_id THEN {$data['manufacture_price']} ";
                $case_quantity .= "WHEN product_id = $product_id THEN '{$data['manufacture_file']}' ";
                $case_sd_id .= "WHEN product_id = $product_id THEN '{$data['cheapest_manufacturer_all_sup_ds_id']}' ";
                $ids[] = $product_id;
            }

            $case_price .= "ELSE manufacture_price END, ";
            $case_quantity .= "ELSE manufacture_file END, ";
            $case_sd_id .= "ElSE cheapest_manufacturer_all_sup_ds_id END ";

            $sql .= $case_price.$case_quantity.$case_sd_id;
            $sql .= "WHERE product_id IN (" . implode(',', $ids) . ")";

            //file_put_contents('juy.txt',$sql);

           // echo $sql;exit;

            if($conn->query($sql)) {
                bulkInsertLog($chunked_idx,"Bulk Update:".count($chunked_item));                    
            } else {
            // echo mysqli_error($conn)."\n".$chunk_sql;exit;
                bulkInsertLog($chunked_idx,"Bulk Update Error:".mysqli_error($conn)."\n".$chunk_sql);
            } 

          
        }     


     }
 }

   




function bulkInsertLog($chunk_index,$chunk_msg) {
  $file_pricechunks_log = "insert_suppliers_row_format.txt";

  $result = file_put_contents($file_pricechunks_log,"".date("d-m-Y H:i:s")." Updated Manufacture price in Suppliers-Row-Format Chunk (".$chunk_index."):-".$chunk_msg."\n", FILE_APPEND);

  if ($result === false) {
    $error = error_get_last();
    echo "File put contents error: " . $error['message'];
}

}//end bulkInsertLog()







