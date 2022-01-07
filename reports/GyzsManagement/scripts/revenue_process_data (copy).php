<?php

include "../config/config.php";
include "../define/constants.php";
include "../lib/SimpleXLSX.php";

session_start();

require_once("../../../app/Mage.php");
umask(0);
Mage::app();

error_reporting(0);

$type = $_REQUEST['type'];
$response_data = array();

function getRange($created_at,$created_at_to_date) {
  $start    = new DateTime($created_at);
  $end      = new DateTime($created_at_to_date);
  $interval = DateInterval::createFromDateString('1 week');
  $period   = new DatePeriod($start, $interval, $end);

  $create_months_weeks = array();

  foreach ($period as $dt) {
      //echo $dt->format("Y-m-d").PHP_EOL;
      $create_months_weeks[] = $dt->format("Y-m-d");   
  }


  $create_months_weeks_sets = array();
  $temp_month_week = 0;
  foreach($create_months_weeks as $key=>$month_week) {
    $create_months_weeks_sets[$temp_month_week][0] = $create_months_weeks[$temp_month_week];
    $create_months_weeks_sets[$temp_month_week][1] = ($create_months_weeks[$temp_month_week + 1] != "" ? $create_months_weeks[$temp_month_week + 1]:$created_at_to_date);
    $temp_month_week++;
  }
  return $create_months_weeks_sets;
}

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
   if(count($get_revenue_data)) {

    $range = getRange($from,$to);
    $fetch_error = "noerror";


    
      $table_cols["id"] = "`id` int NOT NULL AUTO_INCREMENT";
      foreach($get_revenue_data["Columns"] as $column) {
        $dt = "decimal(10,2) DEFAULT NULL";
        if($column == "sku") {
          $dt = "varchar(20) DEFAULT NULL";
        } else if($column == "pid") {
          $dt = "int(11) DEFAULT NULL";
        }

        $table_cols[$column] = "`".$column."` ".$dt;  
      }
  
    // Drop Table
    $drop_sql = "DROP TABLE gyzsrevenuedata1";
    $conn->query($drop_sql);
    // Drop Table

    // Create Table
    $table_cols = implode(",", $table_cols);
    $create_sql = "CREATE TABLE gyzsrevenuedata1 (".$table_cols.", PRIMARY KEY (id))";
    $conn->query($create_sql);
    unset($get_revenue_data["Columns"]);
    // Create Table


    $chunks_revenue_data = array_chunk($get_revenue_data,ROASINSERTCHUNK,true);
    foreach($chunks_revenue_data as $c_k=>$data) {
      //echo "<pre>";
      //print_r($data);
      //exit;
      $rec = insertrevenueChunks($data,$range);  
    }

    // Get cols of new table
    $sql_revenue_cols = "Show columns from gyzsrevenuedata1";
    $allcols = $conn->query($sql_revenue_cols);
    $allRevenue_cols = $allcols->fetch_all(MYSQLI_ASSOC);

    $col_header = "<tr>";
    foreach($allRevenue_cols as $col) {
        $col_header .= "<th>".$col["Field"]."</th>";
    }
    $col_header .= "</tr>";
    // Get cols of new table

  }
  
  $fetch_response_data["err"] = $fetch_error;
  $fetch_response_data["new_table_cols"] = $col_header; 
  $response_data['msg'] = $fetch_response_data; 
  
  break;


  case "get_revenue_brand";

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


}

function insertrevenueChunks($revenue_data,$range) {
    
    global $conn;

    $all_cols = array();
    $all_col_data = array();
    
    $sql = "INSERT INTO gyzsrevenuedata1 VALUES ";



    foreach($revenue_data as $sku=>$r_data) {
      
      if(count($range)) {
        $range_data = "";
        foreach($range as $k=>$date_range) {
         $range_data .= "'".$r_data[$date_range[0]."|||".$date_range[1]."|||sku_total_quantity_sold"]."',";
         $range_data .= "'".$r_data[$date_range[0]."|||".$date_range[1]."|||sku_total_price_excl_tax"]."',";
        }
      }

      

      $all_col_data[] = "(NULL,
                          '" . $r_data['sku'] . "',
                          '" . $r_data['product_id'] . "',
                          " . $r_data['sku_total_quantity_sold'] . ",
                          " . $r_data['sku_total_price_excl_tax'] . ",
                          ".rtrim($range_data,",")."
                        )";

    }


  $sql .= implode(",", $all_col_data);

  

  
    if($conn->query($sql)) {
      return count($revenue_data);
    } else {
      return "Error in chunk insertion:- ".mysqli_error($conn);
    }

}

echo json_encode($response_data); 
?>
