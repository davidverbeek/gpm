<?php
/*ini_set('max_execution_time', 0);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); */
ini_set('memory_limit', '1024M');

$created_at = $_REQUEST['from'];
$created_at_to_date = $_REQUEST['to'];//echo json_encode($_REQUEST);exit;
$categories = $_REQUEST['categories'];//echo (json_encode($categories));exit;
$filter_name = $_REQUEST['filter_name'];

if(empty($created_at) || empty($created_at_to_date)) {
  die("Please mention date");
}


if(!empty($created_at) && !empty($created_at_to_date) && !empty($categories)) {

  include 'revenue_data_by_category.php';

  if(count($final_data)) {  
    echo json_encode($final_data);
  }
}