<?php

include "../config/config.php";
include "../define/constants.php";

session_start();

require_once("../../../app/Mage.php");
umask(0);
Mage::app();
error_reporting(1);

$type = $_REQUEST['type'];
$response_data = array();

switch ($type) {
  case "get_revenue_data":
    
    $from = $_REQUEST['from'];
    $to = $_REQUEST['to'];
    $url_path = ''.$roas_document_root_url.'/fetch_revenue_data.php';
    $post_data = array('from' => $from, 'to' => $to, 'roas_settings' => $settings_data['roas']);

    $options = array( 
      'http' => array( 
      'method' => 'POST', 
      'content' => http_build_query($post_data)) 
    ); 

    $stream = stream_context_create($options);
    $get_revenue_data = json_decode(file_get_contents($url_path, false, $stream),true);
    

    $fetch_error = "error";
   if(count($get_revenue_data['revenue_data'])) {

    $revenue_log = "../pm_logs/revenue_log.txt";
    file_put_contents($revenue_log,"Revenue Log:-".date("d-m-Y H:i:s")."\n"); 

    $truncate_sql = "TRUNCATE TABLE gyzsrevenuedata";
    $conn->query($truncate_sql);

    $fetch_error = "noerror";
    $chunks_revenue_data = array_chunk($get_revenue_data['revenue_data'],ROASINSERTCHUNK,true);
    foreach($chunks_revenue_data as $c_k=>$data) {
      $rec = insertrevenueChunks($data,$from,$to);
      file_put_contents($revenue_log,"Chunk ".$c_k." :-".$rec,FILE_APPEND); 
    }
  }
  
  $fetch_response_data["err"] = $fetch_error;
  
  $fetch_response_data["date_selected"] = $from." To ".$to;
  $fetch_response_data["total_revenue"] = number_format($get_revenue_data['total_revenue'], 2, ',', '.');
  $fetch_response_data["total_bp"] = number_format($get_revenue_data['total_bp'], 2, ',', '.');

  $getsum_data = getSUM();
  
  $fetch_response_data["tot_refund_amount"] = number_format($getsum_data[0]['tot_refund_amount'], 2, ',', '.');
  $fetch_response_data["tot_abs_margin"] = number_format($getsum_data[0]['tot_abs_margin'], 2, ',', '.');
  
  $tot_pm_sp = $getsum_data[0]['tot_abs_margin'] / $get_revenue_data['total_revenue'];

  $fetch_response_data["tot_pm_sp"] = number_format($tot_pm_sp, 2, ',', '.');

  $response_data['msg'] = $fetch_response_data; 
  
  break;


  case "get_revenue_brand":

  $sql = "SELECT DISTINCT 
  rd.id AS id,
  rd.sku AS sku,
  meaov.value AS brand 
  FROM gyzsrevenuedata AS rd
  LEFT JOIN
  mage_catalog_product_entity_int AS mcpei ON mcpei.entity_id = rd.product_id
  AND mcpei.attribute_id = '".MERK."'
  LEFT JOIN
  mage_eav_attribute_option_value AS meaov ON meaov.option_id = mcpei.value";


  if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
      $brand = trim(mb_convert_encoding($row['brand'], 'UTF-8', 'UTF-8'));
      if($brand !== NULL && $brand != "") {
         $brands[$brand] = $brand;
      }
    }

    $response_data['msg']["brands"] = $brands;
  }
  break;

  case "update_mag_order":
    $update_type = $_REQUEST['update_type'];
    $update_val = $_REQUEST['update_val'];
    $sku = $_REQUEST['sku'];
    $orderid = $_REQUEST['orderid'];

    $field_query = "";
    if($update_type == "qty_ordered") {
      $field_query = "qty_ordered = '".$update_val."'";
    } else if($update_type == "base_cost") {
      $field_query = "base_cost = '".$update_val."'";
    } else if($update_type == "base_price") {
      $field_query = "base_price = '".$update_val."' AND price = '".$update_val."'";
    }

    if($sku == "" || $orderid == "") {
      $response_data['msg']['error'] = "Something is wrong!!!";
    } else {
      $sql = "UPDATE mage_sales_flat_order_item SET ".$field_query." WHERE sku = '".$sku."' AND order_id = '".$orderid."'";
      $conn->query($sql);
      $response_data['msg']['success'] = "Ordered data updated successfully";
    }
  break;

  case "set_magento_sort_order":
    
    $bulk_update_errors = array();
    $bulk_update_error_msg = "";
    $products_with_sort_order = array();

    $sql_all_data = "SELECT id, product_id FROM gyzsrevenuedata";
    $file_revenue_products = "../pm_logs/export_sort_order_revenue.txt";
    file_put_contents($file_revenue_products,"Export Revenue Products Sort Log :- ".date('d-m-Y H:i:s')."\n");

    $result_all_data = $conn->query($sql_all_data);
     $allData = $result_all_data->fetch_all(MYSQLI_ASSOC);
      $chunk_all_data = array_chunk($allData, PMCHUNK);
      if(count($chunk_all_data)) {
       foreach($chunk_all_data as $chunked_all_idx=>$chunked_all_datas) {
          
          $create_case_statement = "CASE product_id ";
          $all_product_ids = array();

          foreach($chunked_all_datas as $chunked_all_data) {
            $create_case_statement .= " WHEN '".$chunked_all_data["product_id"]."' THEN '".$chunked_all_data["id"]."'"; 
            $all_product_ids[] = $chunked_all_data["product_id"];
            $products_with_sort_order[$chunked_all_data["product_id"]] = $chunked_all_data["id"];
          }

          $create_case_statement .= " END";
          $get_all_products_to_update = implode(",", $all_product_ids);
          $bulk_update_sql = "UPDATE mage_catalog_category_product SET position = (".$create_case_statement.") WHERE product_id IN (".$get_all_products_to_update.")";

          if($conn->query($bulk_update_sql)) {
            file_put_contents($file_revenue_products,"".date("d-m-Y H:i:s")."\nExported Chunk (".$chunked_all_idx."):-".$create_case_statement."\n",FILE_APPEND);
          } else {
            file_put_contents($file_revenue_products,"".date("d-m-Y H:i:s")."\nExported Chunk Error (".$chunked_all_idx."):-".mysqli_error($conn).$bulk_update_sql."\n\n".$create_case_statement."\n",FILE_APPEND);
            $bulk_update_errors[] = mysqli_error($conn); 
          }  
       }
      }


      // Group Products
        $group_products = Mage::getModel('catalog/product')->getCollection()->addAttributeToFilter('type_id', array('eq' => 'grouped'));
        $ass_group_simple = array();
        foreach ($group_products as $group_product) {
          $ass_simple_products = $group_product->getTypeInstance(true)->getAssociatedProducts($group_product);
          $group_product_id =  $group_product->getId();
          foreach($ass_simple_products as $ass_simple_product) {
              $ass_group_simple[$group_product_id][$ass_simple_product->getId()] = $products_with_sort_order[$ass_simple_product->getId()];    
          }
        }
        file_put_contents("../pm_logs/simple_grouped.txt",print_r($ass_group_simple,true));

       if(count($ass_group_simple) > 0) {
          $create_grouped_case_statement = "CASE product_id ";
          $all_grp_product_ids = array();
         foreach($ass_group_simple as $grouped_id=>$grouped_childs) {
            $min_sort_order_id = min(array_filter($grouped_childs));
            $create_grouped_case_statement .= " WHEN '".$grouped_id."' THEN '".$min_sort_order_id."'"; 
            $all_grp_product_ids[] = $grouped_id;
          }
          $create_grouped_case_statement .= " END";

          $get_all_grp_products_to_update = implode(",", $all_grp_product_ids);
          $bulk_update_grp_sql = "UPDATE mage_catalog_category_product SET position = (".$create_grouped_case_statement.") WHERE product_id IN (".$get_all_grp_products_to_update.")";


          $file_sort_grouped_products = "../pm_logs/export_sort_order_grouped.txt";

          if($conn->query($bulk_update_grp_sql)) {
            file_put_contents($file_sort_grouped_products,"".date("d-m-Y H:i:s")."\nExported :-".$bulk_update_grp_sql."\n",FILE_APPEND);
          } else {
            file_put_contents($file_sort_grouped_products,"".date("d-m-Y H:i:s")."\nExported Error :-".mysqli_error($conn).$bulk_update_grp_sql."\n",FILE_APPEND);
          }
               
      }
      // Group Products


   /* if ($result_all_data = $conn->query($sql_all_data)) {  
      $create_case_statement = "CASE product_id ";
      while ($row = $result_all_data->fetch_assoc()) {
        //echo $row["id"]."===".$row["product_id"]."<br>";
        $create_case_statement .= " WHEN '".$row["product_id"]."' THEN '".$row["id"]."'"; 
        $all_product_ids[$row["id"]] = $row["product_id"];  
      }
      $create_case_statement .= " END";
    }
    $bulk_update_error_msg = "";
    if(count($all_product_ids) > 0) {
      $get_vals = array_values($all_product_ids);
      $get_all_products_to_update = implode(",", $get_vals);
      $bulk_update_sql = "UPDATE mage_catalog_category_product SET position = (".$create_case_statement.") WHERE product_id IN (".$get_all_products_to_update.")";
      
      if($conn->query($bulk_update_sql)) {
        $bulk_update_error_msg = "success";
      } else {
        $bulk_update_error_msg = mysqli_error($conn);
      }
    }
    */


    // Update other products to 9999
     $file_other_products = "../pm_logs/export_sort_order_other.txt";
     file_put_contents($file_other_products,"Export Other Products Sort Log :- ".date('d-m-Y H:i:s')."\n");
     $sql_other_prouducts = "SELECT entity_id FROM mage_catalog_product_entity WHERE type_id = 'simple' AND entity_id NOT IN (SELECT product_id FROM gyzsrevenuedata)";
     $result_other_data = $conn->query($sql_other_prouducts);
     $allOtherData = $result_other_data->fetch_all(MYSQLI_ASSOC);
      $chunk_other_data = array_chunk($allOtherData, PMCHUNK);
      if(count($chunk_other_data)) {
       foreach($chunk_other_data as $chunked_other_idx=>$chunked_other_datas) {
          
          $create_other_case_statement = "CASE product_id ";
          $all_other_product_ids = array();

          foreach($chunked_other_datas as $chunked_other_data) {
            $create_other_case_statement .= " WHEN '".$chunked_other_data["entity_id"]."' THEN '99999'"; 
            $all_other_product_ids[] = $chunked_other_data["entity_id"];           
          }

          $create_other_case_statement .= " END";
          $get_other_products_to_update = implode(",", $all_other_product_ids);
          $bulk_update_other_sql = "UPDATE mage_catalog_category_product SET position = (".$create_other_case_statement.") WHERE product_id IN (".$get_other_products_to_update.")";

          if($conn->query($bulk_update_other_sql)) {
            file_put_contents($file_other_products,"".date("d-m-Y H:i:s")."\nExported Chunk (".$chunked_other_idx."):-".$create_other_case_statement."\n",FILE_APPEND);
          } else {
            file_put_contents($file_other_products,"".date("d-m-Y H:i:s")."\nExported Chunk Error (".$chunked_other_idx."):-".mysqli_error($conn).$bulk_update_other_sql."\n\n".$create_other_case_statement."\n",FILE_APPEND);
            $bulk_update_errors[] = mysqli_error($conn); 
          }  
       }
      } 
    // Update other products to 9999

    if(count($bulk_update_errors) > 0) {
      $bulk_update_error_msg = json_encode($bulk_update_errors);
    } else {
      $bulk_update_error_msg = "success";
    }

    $response_data['msg'] = $bulk_update_error_msg;
  break;

}

function insertrevenueChunks($revenue_data,$from,$to) {
    
    global $conn;

    $all_cols = array();
    $all_col_data = array();
    
    $sql = "INSERT INTO gyzsrevenuedata VALUES ";

    foreach($revenue_data as $sku=>$r_data) {
      
      $all_col_data[] = "(NULL,
                          '" . $r_data['sku'] . "',
                          '" . $r_data['product_id'] . "',
                          " . $r_data['sku_total_quantity_sold'] . ",
                          " . $r_data['sku_total_price_excl_tax'] . ",
                          " . $r_data['sku_bp_excl_tax'] . ",
                          " . $r_data['sku_sp_excl_tax'] . ",
                          " . $r_data['sku_abs_margin'] . ",
                          " . $r_data['sku_margin_bp'] . ",
                          " . $r_data['sku_margin_sp'] . ",
                          " . $r_data['vericale_som_percentage'] . ",
                          " . $r_data['sku_vericale_som'] . ",
                          " . $r_data['sku_vericale_som_bp'] . ",
                          " . $r_data['sku_vericale_som_bp_percentage'] . ",
                          " . $r_data['sku_refund_qty'] . ",
                          " . $r_data['sku_refund_revenue_amount'] . ",
                          " . $r_data['sku_refund_bp_amount'] . ", 
                          '".$from." To ".$to."',
                          " . $r_data['sku_vericale_som_abs'] . ",
                          " . $r_data['sku_vericale_som_abs_percentage'] . ",
                          ". $r_data['sku_total_quantity_sold_365']."
                        )";

    }

  $sql .= implode(",", $all_col_data);
  //echo $sql;
  //exit;

    if($conn->query($sql)) {
      return "Inserted:-".count($revenue_data)."\n";
    } else {
      return "Error in chunk insertion:- ".mysqli_error($conn)."\n";
    }

}

function getSUM() {
  global $conn;

  $sql_sum = "SELECT SUM(sku_refund_revenue_amount) AS tot_refund_amount, SUM(sku_abs_margin) AS tot_abs_margin FROM gyzsrevenuedata";
  $res_sum = $conn->query($sql_sum);
  $res_sum_data = $res_sum->fetch_all(MYSQLI_ASSOC);
  return $res_sum_data;     
}

echo json_encode($response_data); 
?>