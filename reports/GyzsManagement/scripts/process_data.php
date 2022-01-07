<?php

include "../config/config.php";
include "../define/constants.php";
include "../lib/SimpleXLSX.php";

session_start();

require_once("../../../app/Mage.php");
umask(0);
Mage::app();

error_reporting(0);


/* Get sku carrier level starts */
$collection_transmission = Mage::getModel('catalog/product')->getCollection()
->addAttributeToSelect('tansmission') // select all attributes
->addAttributeToFilter(
    'tansmission',
    array('eq' => 1)
);

$transmission_skus = array();
foreach ($collection_transmission as $product) {
  if($product->getSku() != "") {
    $transmission_skus[$product->getSku()] = "Transmission";
  }
}

$collection_briefpost = Mage::getModel('catalog/product')->getCollection()
->addAttributeToSelect('brievenbuspakket') // select all attributes
->addAttributeToFilter(
    'brievenbuspakket',
    array('eq' => 1)
);

$briefpost_skus = array();
foreach ($collection_briefpost as $product_b) {
  if($product_b->getSku() != "") {
    $briefpost_skus[$product_b->getSku()] = "Briefpost";
  }
}

$get_sku_carrier_level = $transmission_skus + $briefpost_skus;
/* Get sku carrier level ends */


/* Get Google Actual roas starts */
$resource = Mage::getSingleton('core/resource');
$readConnection = $resource->getConnection('core_read');
$sql_google_actual_roas = "SELECT * FROM roas_google";
$google_roas_recs = $readConnection->fetchAll($sql_google_actual_roas);
$google_actual_roas = array();
foreach($google_roas_recs as $google_recs) {
  $google_actual_roas[strtolower($google_recs['sku'])]["kosten"] = $google_recs['kosten'];
  $google_actual_roas[strtolower($google_recs['sku'])]["actual_roas"] = $google_recs['actual_roas'];
}
/* Get Google Actual roas ends */


$type = $_REQUEST['type'];
$response_data = array();

switch ($type) {
  
  case "get_roas":
    

    $roasfrom = $_REQUEST['roasfrom'];
    $roasto = $_REQUEST['roasto'];
    $url_path = ''.$roas_document_root_url.'/newroas.php';
    $post_data = array('d' => $roasfrom, 't' => $roasto, 'roas_settings' => $settings_data['roas']);

    $options = array( 
      'http' => array( 
      'method' => 'POST', 
      'content' => http_build_query($post_data)) 
    ); 

    $stream = stream_context_create($options);
    $get_roas = json_decode(file_get_contents($url_path, false, $stream),true);


    $fetch_error = "error";

   if(count($get_roas["ordered_skus"])) {
    $fetch_error = "noerror";
    $chunks_ordered_skus = array_chunk($get_roas["ordered_skus"],ROASINSERTCHUNK,true);
    foreach($chunks_ordered_skus as $c_o=>$cd_o) {
      $rec = insertOrderedChunks($cd_o,$get_roas["all_skus"]);
      $file_insert_orderchunks = "../pm_logs/insertcurrentorderchunks.txt";
      file_put_contents($file_insert_orderchunks,"".date("d-m-Y H:i:s")." Inserted Current Ordered Chunk (".$rec."):-".$c_o."\n",FILE_APPEND);
    } 
  }

  if(count($get_roas["all_skus"])) {
    $fetch_error = "noerror";
    $chunks_all_skus = array_chunk($get_roas["all_skus"],ROASINSERTCHUNK,true);
    foreach($chunks_all_skus as $c=>$cd) {
      $rec_all = insertChunks($cd,$get_roas["ordered_skus"]);
      $file_insert_allchunks = "../pm_logs/insertcurrentallchunks.txt";
      file_put_contents($file_insert_allchunks,"".date("d-m-Y H:i:s")." Inserted Current All Chunk (".$rec_all."):-".$c."\n",FILE_APPEND);
    } 
  }

  $products_category_brand_info = getProductCategoryBrandInfo();
  updateAveragePerCategoryPerBrand($products_category_brand_info);

  $fetch_response_data = array();
  $fetch_response_data["err"] = $fetch_error;
  $fetch_response_data["avg_roas_all"] = $get_roas["roas_avg"];
  $fetch_response_data["roas_avg_with_google_kosten"] = $get_roas["roas_avg_with_google_kosten"];
  


  $response_data['msg'] = $fetch_response_data; 
  break;

  case "get_roas_date":
    $sql = "SELECT * FROM roas_date WHERE id = 1";
    $result = $conn->query($sql);
    $data = $result->fetch_assoc();
    $response_data['live_roas_feed_from_date'] = $data["live_roas_feed_from_date"];
    $response_data['live_roas_feed_to_date'] = $data["live_roas_feed_to_date"];
    $response_data['new_roas_feed_from_date'] = $data["new_roas_feed_from_date"];
    $response_data['new_roas_feed_to_date'] = $data["new_roas_feed_to_date"];
    $response_data['last_cron_run_date'] =  date("F j, Y, g:i a", strtotime($data["last_cron_run_date"]));
  break;

  case "set_roas_date":
    $roasfrom = $_REQUEST['roasfrom'];
    $roasto = $_REQUEST['roasto'];
    $sql = "UPDATE roas_date SET new_roas_feed_from_date = '".$roasfrom."', new_roas_feed_to_date = '".$roasto."' WHERE id = 1";
    $conn->query($sql);
    $response_data['msg'] = "success";
    $response_data['f_date'] = $roasfrom;
    $response_data['t_date'] = $roasto;
  break;

  case "roas_cal":

    $roasfrom = $_REQUEST['roasfrom'];
    $roasto = $_REQUEST['roasto'];
    $sku = $_REQUEST['sku'];
    $aopm = $_REQUEST['aopm'];


    $url_path = ''.$roas_document_root_url.'/newroas_debug.php';
    $post_data = array('txtdate' => $roasfrom, 'txtdate_to' => $roasto, 'txtsku' => $sku, 'roas_settings' => $settings_data['roas']);

    $options = array( 
      'http' => array( 
      'method' => 'POST', 
      'content' => http_build_query($post_data)) 
    ); 

    $stream = stream_context_create($options);
    $get_cal = file_get_contents($url_path, false, $stream);

    $get_roas_information = "";
    if($aopm == 0) {
      $get_roas_information = getRoasInformation($sku);
    }


    $response_data['msg'] = $get_cal.$get_roas_information;
  break;


 case "average_roas":

  $selected_cats = $_POST['selected_cats'];
  $selected_brand = $_POST['selected_brand'];
  $from = $_POST['from'];

  $extra_where = "";
  if(!empty($selected_cats) && !empty($selected_brand)) {
    $extra_where = "WHERE mccp.category_id IN (".$selected_cats.") AND meaov.value = '".$selected_brand."'";
  } else if(!empty($selected_cats) && empty($selected_brand)) {
    $extra_where = "WHERE mccp.category_id IN (".$selected_cats.")";
  } else if(!empty($selected_brand) && empty($selected_cats)) {
    $extra_where = "WHERE meaov.value = '".$selected_brand."'";
  }


  $roas_avg = array();
  $avg_all_roas = "";
  $total_orders_cnt = array();

  $roas_avg_with_google_kosten = array();
  $avg_all_roas_with_google_kosten = "";

  $end_roas_avg = array();
  $avg_all_end_roas = "";

  $end_roas_avg_with_google_kosten = array();
  $avg_all_end_roas_with_google_kosten = "";

  $brands = array(); 
  
if($from == "currentroas") {

  $sql = "SELECT DISTINCT 
  rc.id AS id,
  rc.sku AS sku,
  meaov.value AS brand,
  rc.total_orders AS total_orders,
  rc.roas_target AS roas_target,
  rc.roas_per_cat_per_brand AS roas_per_cat_per_brand,
  rc.end_roas AS end_roas
  FROM
  roascurrent AS rc
  INNER JOIN
  mage_catalog_category_product AS mccp ON mccp.product_id = rc.product_id
  LEFT JOIN
  mage_catalog_product_entity_int AS mcpei ON mcpei.entity_id = rc.product_id
      AND mcpei.attribute_id = '".MERK."'
      LEFT JOIN
  mage_eav_attribute_option_value AS meaov ON meaov.option_id = mcpei.value
  ".$extra_where."";
 

$sql_get_cat_brand_total_orders = "SELECT COUNT(DISTINCT 
  rc.id) AS total_rec,
  rc.sku AS sku
  FROM
  roascurrent AS rc
  INNER JOIN
  mage_catalog_category_product AS mccp ON mccp.product_id = rc.product_id
  LEFT JOIN
  mage_catalog_product_entity_int AS mcpei ON mcpei.entity_id = rc.product_id
      AND mcpei.attribute_id = '".MERK."'
      LEFT JOIN
  mage_eav_attribute_option_value AS meaov ON meaov.option_id = mcpei.value
  ".$extra_where."";

  $total_orders_result = $conn->query($sql_get_cat_brand_total_orders);

  $total_orders_cnt = $total_orders_result->fetch_row();


} else if($from == "feedroas") {

  $sql = "SELECT DISTINCT 
  r.id AS id,
  r.sku AS sku,
  meaov.value AS brand,
  r.total_orders AS total_orders,
  r.roas_target AS roas_target,
  r.roas_per_cat_per_brand AS roas_per_cat_per_brand,
  r.end_roas AS end_roas
  FROM
  roas AS r
  INNER JOIN
  mage_catalog_category_product AS mccp ON mccp.product_id = r.product_id
  LEFT JOIN
  mage_catalog_product_entity_int AS mcpei ON mcpei.entity_id = r.product_id
      AND mcpei.attribute_id = '".MERK."'
      LEFT JOIN
  mage_eav_attribute_option_value AS meaov ON meaov.option_id = mcpei.value
  ".$extra_where."";


  $sql_get_cat_brand_total_orders = "SELECT COUNT(DISTINCT 
  r.id) AS total_rec,
  r.sku AS sku
  FROM
  roas AS r
  INNER JOIN
  mage_catalog_category_product AS mccp ON mccp.product_id = r.product_id
  LEFT JOIN
  mage_catalog_product_entity_int AS mcpei ON mcpei.entity_id = r.product_id
      AND mcpei.attribute_id = '".MERK."'
      LEFT JOIN
  mage_eav_attribute_option_value AS meaov ON meaov.option_id = mcpei.value
  ".$extra_where."";

  $total_orders_result = $conn->query($sql_get_cat_brand_total_orders);

  $total_orders_cnt = $total_orders_result->fetch_row();

}

$google_kosten_greater_than_zero_cnt = 0;

 if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {

      $sku = $row['sku'];

      $roas_avg[] = $row['roas_per_cat_per_brand'];
      $end_roas_avg[] = $row['end_roas'];

      if($google_actual_roas[strtolower($sku)]['kosten'] > 0) {
        $roas_avg_with_google_kosten[] = $row['roas_per_cat_per_brand'];
        $end_roas_avg_with_google_kosten[] = $row['end_roas']; 
        $google_kosten_greater_than_zero_cnt++; 
      }

      $brand = trim(mb_convert_encoding($row['brand'], 'UTF-8', 'UTF-8'));
      if($brand !== NULL && $brand != "") {
         $brands[$brand] = $brand;
      }


    }

   // echo array_sum($roas_avg_with_google_kosten)."===".array_sum($end_roas_avg_with_google_kosten)."<br>";
   // echo $total_orders_cnt[0]."====".$google_kosten_greater_than_zero_cnt;

    
    //$avg_all_roas = array_sum($roas_avg);
    $avg_all_roas = array_sum($roas_avg) / $total_orders_cnt[0];
    $avg_all_roas = "Avg. ROAS (ALL) : ".(is_nan($avg_all_roas) ? "" : round($avg_all_roas,2));

    //$avg_all_roas_with_google_kosten = array_sum($roas_avg_with_google_kosten);
    $avg_all_roas_with_google_kosten = array_sum($roas_avg_with_google_kosten) / $google_kosten_greater_than_zero_cnt;
    $avg_all_roas_with_google_kosten = "Avg. ROAS (ADWORDS) : ".(is_nan($avg_all_roas_with_google_kosten) ? "" : round($avg_all_roas_with_google_kosten,2));


    //$avg_all_end_roas = array_sum($end_roas_avg);
    $avg_all_end_roas = array_sum($end_roas_avg) / $total_orders_cnt[0];
    $avg_all_end_roas = "Avg. ROAS (ALL:End ROAS) : ".(is_nan($avg_all_end_roas) ? "" : round($avg_all_end_roas,2));

    //$avg_all_end_roas_with_google_kosten = array_sum($end_roas_avg_with_google_kosten);
    $avg_all_end_roas_with_google_kosten = array_sum($end_roas_avg_with_google_kosten) / $google_kosten_greater_than_zero_cnt;
    $avg_all_end_roas_with_google_kosten = "Avg. ROAS (ADWORDS-End ROAS) : ".(is_nan($avg_all_end_roas_with_google_kosten) ? "" : round($avg_all_end_roas_with_google_kosten,2));

  }


  $response_data['msg']["avg_all_roas"] = $avg_all_roas;
  $response_data['msg']["avg_all_roas_help"] = array_sum($roas_avg)." / ".$total_orders_cnt[0];

  $response_data['msg']["avg_all_roas_with_google_kosten"] = $avg_all_roas_with_google_kosten;
  $response_data['msg']["avg_all_roas_with_google_kosten_help"] = array_sum($roas_avg_with_google_kosten)." / ".$google_kosten_greater_than_zero_cnt;;

  $response_data['msg']["brands"] = $brands;


  $google_avg_roas_sql = "SELECT ROUND(SUM(conv_waarde)/SUM(kosten),2) AS avg_google_roas FROM roas_google";
  $res = $conn->query($google_avg_roas_sql);
  $google_average_roas = $res->fetch_assoc();
  $response_data['msg']["avg_google_roas"] = "Avg. ROAS (Google) : ".$google_average_roas["avg_google_roas"];

  $response_data['msg']["avg_all_end_roas"] = $avg_all_end_roas;
  $response_data['msg']["avg_all_end_roas_help"] = array_sum($end_roas_avg)." / ".$total_orders_cnt[0];

  $response_data['msg']["avg_all_end_roas_with_google_kosten"] = $avg_all_end_roas_with_google_kosten;
  $response_data['msg']["avg_all_end_roas_with_google_kosten_help"] = array_sum($end_roas_avg_with_google_kosten)." / ".$google_kosten_greater_than_zero_cnt;

  $response_data['msg']["avg_roas_debug"] = "Roas Avg Sum:-".array_sum($roas_avg)."==="."Total:-".$total_orders_cnt[0]."|||End Roas Avg Sum:-".array_sum($end_roas_avg);

  $response_data['msg']["avg_roas_kosten_debug"] = "Roas Avg Sum:-".array_sum($roas_avg_with_google_kosten)."==="."Total:-".$google_kosten_greater_than_zero_cnt."|||End Roas Avg Sum:-".array_sum($end_roas_avg_with_google_kosten);

 break;





  case "google_roas_xlsx":

    $file_array = explode(".", $_FILES["file_google_roas_xlsx"]["name"]);
    $extension = end($file_array);
    if($extension == "zip") {
      $response_data['msg']["error"] = "Invalid file extension (".$extension.") . Please upload xlsx file";
    } else {

    $finfo = finfo_open( FILEINFO_MIME_TYPE );
    $mtype = finfo_file( $finfo, $_FILES['file_google_roas_xlsx']['tmp_name']);
    if(empty($mtype)) {
      $fileuploaderrormsg = FileUploadException($_FILES['file_google_roas_xlsx']['error']);
      $response_data['msg']["error"] = $fileuploaderrormsg;
    } else {
      if($mtype == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" || $mtype == "application/zip") {

        $xlsx = SimpleXLSX::parse($_FILES['file_google_roas_xlsx']['tmp_name']);
        $xlsx_uploaded_columns = $xlsx->rows()[0];
        $xlsx_valid_columns = array("Item-ID","Valuta","Vertoningen","Klikken","CTR","Gem. CPC","Kosten","Conversies","Kosten/conv.","Conv. perc.","Conv.waarde/kosten","Conv.waarde");
        $diff_cols = array_diff($xlsx_uploaded_columns,$xlsx_valid_columns);
        $valid_column_headers = implode("<br>",$xlsx_valid_columns);

        if(count($diff_cols)) {
          $response_data['msg']["error"] = "<b>Invalid Column Headers.</b><br><br> Valid Column Headers Should Be As Below.<br>".$valid_column_headers."";
        } else {
          $new_file_name = time()."_".$_FILES['file_google_roas_xlsx']['name'];
          $file_path = $document_root_path."/pm_logs/google_roas/".$new_file_name;
          move_uploaded_file($_FILES['file_google_roas_xlsx']['tmp_name'],$file_path);
          $sql = "INSERT INTO google_actual_roas SET from_date = '".$_REQUEST['from']."', to_date = '".$_REQUEST['to']."', file_name = '".$new_file_name."'";
          $result = $conn->query($sql);
          $response_data['msg']["success"] = "Success";
        }
      } else {
        $response_data['msg']["error"] = "Invalid file type (".$mtype."). Please upload xlsx file";
      }
    }
  }

  break;

  case "activate_roas_xlsx":
      
      $inserted_file = array();

      $inserted_file = AddGoogleXlsxDataIntoDb($_REQUEST['id']);

      if($inserted_file["stat"] == 1) {
        $sql = "UPDATE google_actual_roas SET active = '0'";
        $conn->query($sql);
      
        $sql = "UPDATE google_actual_roas SET active = '1' WHERE id = '".$_REQUEST['id']."'";
        $conn->query($sql);
      
        $response_data['msg'] = "Activated Successfully. ".$inserted_file["mes"];
        $response_data['stat'] = 1;
      } else {
        $response_data['msg'] = $inserted_file["mes"];
        $response_data['stat'] = 0;
      }

  break;


}

function AddGoogleXlsxDataIntoDb($id) {
    global $conn, $document_root_path;
    $added_message = array();
    $sql = "SELECT * FROM google_actual_roas WHERE id = '".$id."'";
    $result = $conn->query($sql);
    $data = $result->fetch_assoc();
    $uploaded_file = $document_root_path."/pm_logs/google_roas/".$data["file_name"];
    if(file_exists($uploaded_file)) {
      $xlsx = SimpleXLSX::parse($uploaded_file);
        if($xlsx) {
          if(count($xlsx->rows()) > 1) {
            $xlsx_uploaded_columns = $xlsx->rows()[0];
            $xlsx_valid_columns = array("Item-ID","Valuta","Vertoningen","Klikken","CTR","Gem. CPC","Kosten","Conversies","Kosten/conv.","Conv. perc.","Conv.waarde/kosten","Conv.waarde");
            $diff_cols = array_diff($xlsx_uploaded_columns,$xlsx_valid_columns);
            if(count($diff_cols)) {
               $added_message["stat"] = 0;
               $added_message["mes"] = "Invalid Column Headers";
            } else {
                      $insert_sql = "INSERT INTO roas_google (sku,
                      valuta,
                      vertoningen,
                      klikken,
                      ctr,
                      gem_cpc,
                      kosten,
                      conversies,
                      kosten_conversies,
                      conv_percentage,
                      actual_roas,
                      conv_waarde) VALUES ";

                      $all_col_data = array();
                      
                      foreach($xlsx->rows() as $xl_row=>$xl_rowdata) {
                        
                        if($xl_row == 0) {
                          continue;
                        }

                        if($xl_rowdata[4]) { //CTR
                          $xl_rowdata[4] = $xl_rowdata[4] * 100;
                        }

                        if($xl_rowdata[9]) { //Conv. perc.
                          $xl_rowdata[9] = $xl_rowdata[9] * 100;
                        }

                        $all_col_data[] = "('".$xl_rowdata[0]."','".$xl_rowdata[1]."','".$xl_rowdata[2]."','".$xl_rowdata[3]."','".$xl_rowdata[4]."','".$xl_rowdata[5]."','".$xl_rowdata[6]."','".$xl_rowdata[7]."','".$xl_rowdata[8]."','".$xl_rowdata[9]."','".$xl_rowdata[10]."','".$xl_rowdata[11]."')";
                      }
                
                      $insert_sql .= implode(",", $all_col_data);
                      $truncate_sql = "TRUNCATE TABLE roas_google";

                      $conn->query($truncate_sql);
                      if($conn->query($insert_sql)) {
                        $added_message["stat"] = 1;
                        $added_message["mes"] = "Processed ".count($xlsx->rows()). " rows"; 
                      } else {
                        $added_message["stat"] = 0;
                        $added_message["mes"] = mysqli_error($conn);
                      }


                    }
        } else {
          $added_message["stat"] = 0;
          $added_message["mes"] = "Xlsx File Is Empty";  
        }
      } else {
        $added_message["stat"] = 0;
        $added_message["mes"] = "Xlsx File Is Corrupted";  
      }
    } else {
      $added_message["stat"] = 0;
      $added_message["mes"] = "Xlsx File Does Not Exist";
    }

    return $added_message;
}

function endRoas($roas_settings,$roas_target) {
  
  if(is_array($roas_settings['roas_lower_bound'])) {
    foreach($roas_settings['roas_lower_bound'] as $l_k=>$l_v) {
      if($roas_target < $l_k) {
        return $l_v;
      } 
    }
  }

  if(is_array($roas_settings['roas_range'])) {
    foreach($roas_settings['roas_range'] as $r_k=>$r_v) {
      $r_k_min_max = explode("-", $r_k);
      if($roas_target >= $r_k_min_max[0] && $roas_target < $r_k_min_max[1]) {
        if($r_v["r_type"] == "fixed") {
          return $r_v["r_val"];
        } else {
          return $roas_target + $r_v["r_val"];
        }
      } 
    }
  }


  if(is_array($roas_settings['roas_upper_bound'])) {
    foreach($roas_settings['roas_upper_bound'] as $u_k=>$u_v) {
      if($roas_target >= $u_k) {
        return $u_v;
      } 
    }
  }

  return $roas_target;

}

function insertOrderedChunks($chunk_ordered_data,$all_skus) {
    
    global $conn;

    $all_cols = array();
    $all_col_data = array();
    
    $sql = "INSERT INTO roascurrent (sku,
                                    carrier_level,
                                    total_quantity,
                                    total_orders,

                                    total_orders_bol,
                                    total_quantity_bol,

                                    return_general,
                                    return_bol,
                                    return_nobol,
                                    return_help,
                                    return_order_general,
                                    return_order_bol,
                                    return_order_nobol,
                                    return_order_help,
                                    parent_product_factor,
                                    parent_absolute_margin,
                                    parent_return_margin,
                                    total_parent_margin,
                                    average_order_per_month,
                                    other_absolute_margin,
                                    total_absolute_margin,
                                    shipment_revenue,
                                    shipment_cost,
                                    shipment_diff,
                                    employee_cost,
                                    margin_after_deductions,
                                    total_selling_price,
                                    payment_other_company_cost,
                                    burning_margin,
                                    roas_target,
                                    product_id
                                  ) VALUES ";

    foreach($chunk_ordered_data as $key=>$data) {
      if($key == "avg_qty_of_all_orders" || $key == "avg_order_lines_per_order" || $key == "header_row") {
        continue;
      }

      $get_product_id = $all_skus[$data['sku']]['pid'];
      


      $all_col_data[] = "('" . $data['sku'] . "',
                          '" . $data['carrier_level'] . "',
                          " . $data['total_quantity'] . ",
                          " . $data['total_orders'] . ",
                          " . $data['total_orders_bol'] . ",
                          " . $data['total_quantity_bol'] . ",
                          " . $data['return_general'] . ",
                          " . $data['return_bol'] . ", 
                          " . $data['return_nobol'] . ", 
                          '" . addslashes($data['return_help']) . "',
                          " . $data['return_order_general'] . ",
                          " . $data['return_order_bol'] . ",
                          " . $data['return_order_nobol'] . ",
                          '" . addslashes($data['return_order_help']) . "',
                          " . $data['parent_product_factor'] . ",
                          " . $data['parent_absolute_margin'] . ",
                          " . $data['parent_return_margin'] . ",
                          " . $data['total_parent_margin'] . ",
                          " . $data['average_order_per_month'] . ",
                          " . $data['other_absolute_margin'] . ",
                          " . $data['total_absolute_margin'] . ",
                          " . $data['shipment_revenue'] . ",
                          " . $data['shipment_cost'] . ",
                          " . $data['shipment_diff'] . ",
                          " . $data['employee_cost'] . ",
                          " . $data['margin_after_deductions'] . ",
                          " . $data['total_selling_price'] . ",
                          " . $data['payment_other_company_cost'] . ",
                          " . $data['burning_margin'] . ",
                          " . $data['roas_target'] . ",
                          '".$get_product_id."'
                        )";

    }

  $sql .= implode(",", $all_col_data) . " ON DUPLICATE KEY UPDATE carrier_level = VALUES(carrier_level),total_quantity = VALUES(total_quantity),total_orders = VALUES(total_orders), total_orders_bol = VALUES(total_orders_bol), total_quantity_bol = VALUES(total_quantity_bol), return_general = VALUES(return_general), return_bol = VALUES(return_bol), return_nobol = VALUES(return_nobol), return_help = VALUES(return_help), return_order_general = VALUES(return_order_general), return_order_bol = VALUES(return_order_bol), return_order_nobol = VALUES(return_order_nobol), return_order_help = VALUES(return_order_help), parent_product_factor = VALUES(parent_product_factor),parent_absolute_margin = VALUES(parent_absolute_margin),parent_return_margin = VALUES(parent_return_margin),total_parent_margin = VALUES(total_parent_margin),average_order_per_month = VALUES(average_order_per_month),other_absolute_margin = VALUES(other_absolute_margin),total_absolute_margin = VALUES(total_absolute_margin),shipment_revenue = VALUES(shipment_revenue),shipment_cost = VALUES(shipment_cost),shipment_diff = VALUES(shipment_diff),employee_cost = VALUES(employee_cost),margin_after_deductions = VALUES(margin_after_deductions),total_selling_price = VALUES(total_selling_price),payment_other_company_cost = VALUES(payment_other_company_cost),burning_margin = VALUES(burning_margin),roas_target = VALUES(roas_target),product_id = VALUES(product_id)";

  
    if($conn->query($sql)) {
      return count($chunk_ordered_data);
    } else {
      return "Error in chunk insertion:- ".mysqli_error($conn);
    }

}

function updateAveragePerCategoryPerBrand($products_category_brand_info) {
  global $conn;
  $_SESSION["avg_per_cat"] = array();
  $_SESSION["avg_per_cat_per_brand"] = array();
  

  $sql = "SELECT sku, product_id,roas_target,total_orders FROM roascurrent";
  $result = $conn->query($sql);
  $all_skus = $result->fetch_all();

  
  $get_chunks = array_chunk($all_skus,ROASINSERTCHUNK,true);
  foreach($get_chunks as $c_k=>$c_d) {
    $rec = insertAveragesChunks($c_k,$c_d,$products_category_brand_info);
    $file_insert_averages = "../pm_logs/insertcurrentaverages.txt";
    file_put_contents($file_insert_averages,"".date("d-m-Y H:i:s")." Inserted Average Chunk (".$rec."):-".$c_k."\n",FILE_APPEND);
  }
  
}

 function insertAveragesChunks($c_k,$c_d,$products_category_brand_info) {

  global $conn, $individual_sku_percentage, $category_brand_percentage, $google_actual_roas, $settings_data;

  $all_col_avg_data = array();
  

  $sql = "INSERT INTO roascurrent (sku,
                                   product_id,
                                   end_roas,
                                   google_kosten,
                                   google_roas,
                                   performance,
                                   cat_id,
                                   cat_name,
                                   brand_name,
                                   avg_per_cat,
                                   avg_per_cat_per_brand,
                                   roas_per_cat_per_brand,
                                   roas_per_cat_per_brand_help) VALUES ";

  foreach($c_d as $key_avg=>$data_avg) {

      $cat_id = "";
      $cat_name = "";
      $brand_name = "";
      $get_product_sku = $data_avg[0];
      $get_product_id = $data_avg[1];
      $roas_target = $data_avg[2];
      $total_orders = $data_avg[3];
      $averages = array();
      $averages_per_cat = array();
      $roas_per_cat_per_brand_help = "";

      if(isset($products_category_brand_info[$get_product_id]["4"])) {
        $cat_id = $products_category_brand_info[$get_product_id]["4"]["cat_id"];
        $cat_name = $products_category_brand_info[$get_product_id]["4"]["cat_name"];
        $brand_name = $products_category_brand_info[$get_product_id]["4"]["brand_name"];
      } 
      else if (isset($products_category_brand_info[$get_product_id]["3"])) {
        $cat_id = $products_category_brand_info[$get_product_id]["3"]["cat_id"];
        $cat_name = $products_category_brand_info[$get_product_id]["3"]["cat_name"];
        $brand_name = $products_category_brand_info[$get_product_id]["3"]["brand_name"];
      } 
      else if (isset($products_category_brand_info[$get_product_id]["2"])) {
        $cat_id = $products_category_brand_info[$get_product_id]["2"]["cat_id"];
        $cat_name = $products_category_brand_info[$get_product_id]["2"]["cat_name"];
        $brand_name = $products_category_brand_info[$get_product_id]["2"]["brand_name"];
      }
      else if (isset($products_category_brand_info[$get_product_id]["1"])) {
        $cat_id = $products_category_brand_info[$get_product_id]["1"]["cat_id"];
        $cat_name = $products_category_brand_info[$get_product_id]["1"]["cat_name"];
        $brand_name = $products_category_brand_info[$get_product_id]["1"]["brand_name"];
      }

      if(isset($_SESSION["avg_per_cat"][$cat_id])) {
        $averages_per_cat = $_SESSION["avg_per_cat"][$cat_id];
      } else {
        $averages_per_cat = getPerCategoryPerBrandAverage($cat_id,"");
        $_SESSION["avg_per_cat"][$cat_id] = $averages_per_cat;
        $file_show_averages = "../pm_logs/showaverages.txt";
        file_put_contents($file_show_averages, print_r($_SESSION["avg_per_cat"],true));
      }

      if(isset($_SESSION["avg_per_cat_per_brand"][$cat_id][$brand_name])) {
        $averages = $_SESSION["avg_per_cat_per_brand"][$cat_id][$brand_name];
      } else {
        $averages = getPerCategoryPerBrandAverage($cat_id,$brand_name);
        $_SESSION["avg_per_cat_per_brand"][$cat_id][$brand_name] = $averages;
        $file_show_averages_cat_brand = "../pm_logs/showaverages_cat_brand.txt";
        file_put_contents($file_show_averages_cat_brand, print_r($_SESSION["avg_per_cat_per_brand"],true));
      }

      
      if($averages_per_cat['avg_all_roas'] != 0 && $averages['avg_all_roas'] == 0) {
        $averages['avg_all_roas'] = $averages_per_cat['avg_all_roas'];
        $roas_per_cat_per_brand_help = "Debug1|||Avg per cat per brand is zero, so avg per cat per brand becomes avg per cat";
      }

      $roas_per_cat_per_brand = round(($roas_target * ($individual_sku_percentage/100)) + ($averages['avg_all_roas'] * ($category_brand_percentage/100)),2);
      
      if($total_orders == 0 && $roas_target < 0) {
        $roas_per_cat_per_brand = $averages['avg_all_roas'] + 2;
        $roas_per_cat_per_brand_help = "Debug2|||Sku total order is 0 and roas target < 0, So consider Avg per cat per brand + 2";
      } 
      
      if($averages_per_cat['avg_all_roas'] == 0 && $averages['avg_all_roas'] == 0) {
        $roas_per_cat_per_brand = $roas_target;
        $roas_per_cat_per_brand_help = "Debug3|||Avg per cat per brand and avg per cat both are 0, so consider roas_target";
      } 



      $end_roas = endRoas($settings_data['roas'],$roas_per_cat_per_brand);
      $lower_case_sku = strtolower($get_product_sku);
      $google_kosten = $google_actual_roas[$lower_case_sku]['kosten'];
      $google_roas = $google_actual_roas[$lower_case_sku]['actual_roas'];

      $performance = "";
      if($google_kosten > 0) {
        if($google_roas > $end_roas) {
          $performance = "Over Performance";
        } else if($google_roas < $end_roas) {
          $performance = "Under Performance";
        } else if($end_roas == $google_roas) {
          $performance = "Same as Google";
        }
      }


      $all_col_avg_data[] = "('".$get_product_sku."',
                            '".$get_product_id."',
                            '".$end_roas."',
                            '".$google_kosten."',
                            '".$google_roas."',
                            '".$performance."',
                            '".$cat_id."',
                            '".addslashes($cat_name)."',
                            '".addslashes($brand_name)."',
                            '".$averages_per_cat['avg_all_roas']."',
                            '".$averages['avg_all_roas']."',
                            '".$roas_per_cat_per_brand."',
                            '".$roas_per_cat_per_brand_help."'
                        )";
  }


  $sql .= implode(",", $all_col_avg_data) . " ON DUPLICATE KEY UPDATE end_roas = VALUES(end_roas), google_kosten = VALUES(google_kosten), google_roas = VALUES(google_roas), performance = VALUES(performance), cat_id = VALUES(cat_id), cat_name = VALUES(cat_name), brand_name = VALUES(brand_name), avg_per_cat = VALUES(avg_per_cat), avg_per_cat_per_brand = VALUES(avg_per_cat_per_brand), roas_per_cat_per_brand = VALUES(roas_per_cat_per_brand), roas_per_cat_per_brand_help = VALUES(roas_per_cat_per_brand_help)";


  if($conn->query($sql)) {
      return count($c_d);
    } else {
      return "Error in average chunk  insertion:- ".mysqli_error($conn);
    }
  
}




function getPerCategoryPerBrandAverage($selected_cats,$selected_brand) {

  global $conn;
  $allavgs = array();
  $total_orders_cnt = array();

  $extra_where = "";
  if(!empty($selected_cats) && !empty($selected_brand)) {
    $extra_where = "WHERE mccp.category_id IN (".$selected_cats.") AND meaov.value = '".$selected_brand."'";
  } else if(!empty($selected_cats) && empty($selected_brand)) {
    $extra_where = "WHERE mccp.category_id IN (".$selected_cats.")";
  } else if(!empty($selected_brand) && empty($selected_cats)) {
    $extra_where = "WHERE meaov.value = '".$selected_brand."'";
  }
  

  $roas_avg = array();
  $avg_all_roas = "";

          $sql_get_cat_brand_total_orders = "SELECT DISTINCT 
          rc.id AS id,
          rc.sku AS sku,
          sum(rc.total_orders) AS cat_brand_total_orders
          FROM
          roascurrent AS rc
          INNER JOIN
          mage_catalog_category_product AS mccp ON mccp.product_id = rc.product_id
          LEFT JOIN
          mage_catalog_product_entity_int AS mcpei ON mcpei.entity_id = rc.product_id
              AND mcpei.attribute_id = '".MERK."'
              LEFT JOIN
          mage_eav_attribute_option_value AS meaov ON meaov.option_id = mcpei.value
          ".$extra_where."";

          $total_orders_result = $conn->query($sql_get_cat_brand_total_orders);

          $total_orders_cnt = $total_orders_result->fetch_row();
    
  
   $sql = "SELECT DISTINCT 
          rc.id AS id,
          rc.sku AS sku,
          meaov.value AS brand,
          rc.total_orders AS total_orders,
          rc.roas_target AS roas_target
          FROM
          roascurrent AS rc
          INNER JOIN
          mage_catalog_category_product AS mccp ON mccp.product_id = rc.product_id
          LEFT JOIN
          mage_catalog_product_entity_int AS mcpei ON mcpei.entity_id = rc.product_id
              AND mcpei.attribute_id = '".MERK."'
              LEFT JOIN
          mage_eav_attribute_option_value AS meaov ON meaov.option_id = mcpei.value
          ".$extra_where."";
         
         if ($result = $conn->query($sql)) {
            while ($row = $result->fetch_assoc()) {
              if($row['total_orders'] > 0) {

                $get_roas_target = $row['roas_target'];
                if($get_roas_target < 1) {
                  $get_roas_target = 10;
                }

                $roas_avg[] = (($row['total_orders'] / $total_orders_cnt[2]) * $get_roas_target);
               
              }
            }
            $avg_all_roas = array_sum($roas_avg);
            $avg_all_roas = (is_nan($avg_all_roas) ? "" : round($avg_all_roas,2));
            $allavgs["avg_all_roas"] = $avg_all_roas;
            $allavgs["total_order_cnt"] = $total_orders_cnt[2];
          }
          return $allavgs;
}




function insertChunks($chunk_data,$ordered_skus) {
 
    global $conn,$shipping_packing_costs,$shpment_reveenue_wo,$employee_cost_wo,$payment_cost,$other_company_cost,$get_sku_carrier_level;

    $all_col_data = array();
    $sql = "";

    $insert_new_data = 0;

    $sql = "INSERT INTO roascurrent (sku,
                                    carrier_level,
                                    total_quantity,
                                    total_orders,

                                    total_orders_bol,
                                    total_quantity_bol,

                                    return_general,
                                    return_bol,
                                    return_nobol,
                                    return_help,
                                    return_order_general,
                                    return_order_bol,
                                    return_order_nobol,
                                    return_order_help,
                                    parent_product_factor,
                                    parent_absolute_margin,
                                    parent_return_margin,
                                    total_parent_margin,
                                    average_order_per_month,
                                    other_absolute_margin,
                                    total_absolute_margin,
                                    shipment_revenue,
                                    shipment_cost,
                                    shipment_diff,
                                    employee_cost,
                                    margin_after_deductions,
                                    total_selling_price,
                                    payment_other_company_cost,
                                    burning_margin,
                                    roas_target,
                                    product_id
                                    ) VALUES ";
    foreach($chunk_data as $sku=>$sku_data) {
      if(!(array_key_exists(trim($sku), $ordered_skus))) {

        // echo $sku."<br>";


        
        $absolute_margin = $sku_data["absolute"];
        $selling_price = $sku_data["price"];

        if(empty($selling_price) || $selling_price == 0) {
          continue;
        }

        $insert_new_data++;

        if($get_sku_carrier_level[$sku] == "") {
          $carrier_level_wo = "Pakketpost";
        } else {
          $carrier_level_wo = $get_sku_carrier_level[$sku]; 
        }

         if($carrier_level_wo == "Transmission") {
          $shipment_revenue =  $shpment_reveenue_wo[$carrier_level_wo];
        } else {
          $shipment_revenue =  $shpment_reveenue_wo["Not_Transmission"];
        }

        $shipment_cost = $shipping_packing_costs[$carrier_level_wo];
        $shipment_diff = round(($shipment_revenue - $shipment_cost),4);
        $employee_cost = $employee_cost_wo;
        $margin_after_deductions =  round((($absolute_margin + $shipment_diff) - $employee_cost),4);
        
        $parent_selling_price = round(($selling_price * 1),4);

        $payment_other_company_cost = $payment_cost + $other_company_cost;
        
        $burning_margin = round(((((($absolute_margin + $shipment_diff) - $employee_cost)/$parent_selling_price)*100) - $payment_other_company_cost),4);

        $roas_target = round((1 / $burning_margin) * 100,4);

        $get_product_id = $sku_data['pid']; 
        
        $all_col_data[] = "('" . trim($sku) . "',
                            '".$carrier_level_wo."',
                            0,
                            0,
                            0,
                            0,
                            0,
                            0,
                            0,
                            '',
                            0,
                            0,
                            0,
                            '',
                            0,
                            " . $absolute_margin . ",
                            0,
                            " . $absolute_margin . ",
                            0,
                            0,
                            " . $absolute_margin . ",
                            ".$shipment_revenue.",
                            ".$shipment_cost.",
                            ".$shipment_diff.",
                            ".$employee_cost.",
                            " . $margin_after_deductions . ",
                            " .$parent_selling_price . ",
                            ".$payment_other_company_cost." ,
                            " . $burning_margin . ",
                            " . $roas_target . ",
                            '".$get_product_id."'
                            )";
      }
    }

     $sql .= implode(",", $all_col_data) . " ON DUPLICATE KEY UPDATE carrier_level = VALUES(carrier_level),total_quantity = VALUES(total_quantity),total_orders = VALUES(total_orders), total_orders_bol = VALUES(total_orders_bol), total_quantity_bol = VALUES(total_quantity_bol), return_general = VALUES(return_general), return_bol = VALUES(return_bol), return_nobol = VALUES(return_nobol), return_help = VALUES(return_help), return_order_general = VALUES(return_order_general), return_order_bol = VALUES(return_order_bol), return_order_nobol = VALUES(return_order_nobol), return_order_help = VALUES(return_order_help), parent_product_factor = VALUES(parent_product_factor),parent_absolute_margin = VALUES(parent_absolute_margin),parent_return_margin = VALUES(parent_return_margin),total_parent_margin = VALUES(total_parent_margin),average_order_per_month = VALUES(average_order_per_month),other_absolute_margin = VALUES(other_absolute_margin),total_absolute_margin = VALUES(total_absolute_margin),shipment_revenue = VALUES(shipment_revenue),shipment_cost = VALUES(shipment_cost),shipment_diff = VALUES(shipment_diff),employee_cost = VALUES(employee_cost),margin_after_deductions = VALUES(margin_after_deductions),total_selling_price = VALUES(total_selling_price),payment_other_company_cost = VALUES(payment_other_company_cost),burning_margin = VALUES(burning_margin),roas_target = VALUES(roas_target),product_id = VALUES(product_id)";

    if($conn->query($sql)) {
      return $insert_new_data;
    } else {
      return "Error in chunk insertion:- ".mysqli_error($conn);
    }
}

function FileUploadException($code) {
  switch ($code) {
    case UPLOAD_ERR_INI_SIZE:
      $message = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
      break;
    case UPLOAD_ERR_FORM_SIZE:
      $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
      break;
    case UPLOAD_ERR_PARTIAL:
      $message = "The uploaded file was only partially uploaded";
      break;
    case UPLOAD_ERR_NO_FILE:
      $message = "No file was uploaded";
      break;
    case UPLOAD_ERR_NO_TMP_DIR:
      $message = "Missing a temporary folder";
      break;
    case UPLOAD_ERR_CANT_WRITE:
      $message = "Failed to write file to disk";
      break;
    case UPLOAD_ERR_EXTENSION:
      $message = "File upload stopped by extension";
      break;
    default:
      $message = "Unknown upload error";
      break;
  }
  return $message;
}

function getRoasInformation($sku) {

   global $conn;

   $euro_sign = "\xE2\x82\xAc";

   $cal_html_roas_info = "";

   $sql = "SELECT * FROM roascurrent WHERE sku = '".$sku."'";

   if ($result = $conn->query($sql)) {
      $row = $result->fetch_assoc();

      $cal_html_roas_info = '<div style="margin:20px;"></div>';
      $cal_html_roas_info .= '<table class="table table-bordered table-striped table-hover display nowrap" style="width: 100%;">';
      $cal_html_roas_info .= '<thead><th colspan="14">Roas Information</th></thead>';
      $cal_html_roas_info .= '<tr>
                    <td><b>A</b></td>
                    <td><b>B</b></td>
                    <td><b>C<br>(A - B)</b></td>
                    <td><b>D</b></td>
                    <td><b>E<br>(C + D)</b></td>
                    <td><b>F</b></td>
                    <td><b>G</b></td>
                    <td><b>H<br>(F - G)</b></td>
                    <td><b>I</b></td>
                    <td><b>J<br>((E + H) - I)</b></td>
                    <td><b>K</b></td>
                    <td><b>L</b></td>
                    <td><b>M<br>(((J / K) * 100) - L)</b></td>
                    <td><b>N<br>((1/M) * 100)</b></td>
                  </tr>
                  <tr>
                    <td>
                      Absolute Margin<br>(Absolute Margin PP * Product factor PP)<br>'.$row["parent_absolute_margin"].' '.$euro_sign.' ( '.$row["parent_absolute_margin"].' * 1)
                    </td> 
                      <td>Return Margin (PP)<br>0 '.$euro_sign.'</td>
                      <td>Total Margin (PP)<br>'.$row["total_parent_margin"].' '.$euro_sign.'</td>
                      <td>Child Margin<br>'.$row["other_absolute_margin"].' '.$euro_sign.'</td>
                      <td>Total Absolute Margin<br>'.$row["total_absolute_margin"].' '.$euro_sign.'</td>
                      <td>Shipment Revenue<br>'.$row["shipment_revenue"].' '.$euro_sign.'</td>
                      <td>Shipment Cost<br>'.$row["shipment_cost"].' '.$euro_sign.'</td>
                      <td>Shipment Difference<br>'.$row["shipment_diff"].' '.$euro_sign.'</td>
                      <td>Employee Cost<br>'.$row["employee_cost"].' '.$euro_sign.'</td>
                      <td>Margin After Deductions<br>'.$row["margin_after_deductions"].' '.$euro_sign.'</td>
                      <td>Total Selling Price<br>(Selling Price PP * Product Factor PP)<br>'.$row["total_selling_price"].' '.$euro_sign.' ('.$row["total_selling_price"].' * 1)</td>
                      <td>Payment and Other<br>Company Cost<br>'.$row["payment_other_company_cost"].' %</td>
                      <td>Burning Margin<br>'.$row["burning_margin"].' %</td>
                      <td>Roas Target<br>'.$row["roas_target"].' %</td>
                    </td>
                  </tr>';
      $cal_html_roas_info .= '</table>';
   }
            
  return $cal_html_roas_info;
}

function getProductCategoryBrandInfo() {
  global $conn;
  $sql = "SELECT 
            mccp.category_id,
            mccp.product_id,
            mccev.value AS cat_name,
            mcce.level,
            meaov.value AS brand_name
          FROM 
            mage_catalog_category_product AS mccp
          LEFT JOIN
          mage_catalog_product_entity_int AS mcpei ON mcpei.entity_id = mccp.product_id
          AND mcpei.attribute_id = '".MERK."'
          LEFT JOIN
          mage_eav_attribute_option_value AS meaov ON meaov.option_id = mcpei.value
          LEFT JOIN
            mage_catalog_category_entity_varchar AS mccev ON mccev.entity_id = mccp.category_id AND mccev.attribute_id = '".CATEGORYNAME_ATTRIBUTE_ID."' AND mccev.store_id = '0'
          LEFT JOIN
            mage_catalog_category_entity AS mcce ON mcce.entity_id = mccp.category_id";

  $products_with_cat_brand_info = array();
  if ($result = $conn->query($sql)) {
     while ($row = $result->fetch_assoc()) {
       $products_with_cat_brand_info[$row["product_id"]][$row["level"]]['cat_id'] = $row["category_id"];
       $products_with_cat_brand_info[$row["product_id"]][$row["level"]]['cat_name'] = $row["cat_name"];
       $products_with_cat_brand_info[$row["product_id"]][$row["level"]]['brand_name'] = $row["brand_name"];
     } 
  }
  return $products_with_cat_brand_info;  
}



echo json_encode($response_data); 
?>
