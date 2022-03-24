<?php
/*ini_set('max_execution_time', 0);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); */


$created_at = $_REQUEST['from'];
$created_at_to_date = $_REQUEST['to'];

//$created_at = "2018-01-01";
//$created_at_to_date = "2019-12-31";

//$created_at = "2020-01-01";
if(empty($created_at) || empty($created_at_to_date)) {
  die("Please mention date");
}


if(!empty($created_at) && !empty($created_at_to_date)) {

  include 'revenue_data.php';

  if(count($final_data)) {  
     //echo json_encode($all_ordered_skus);
    echo json_encode($final_data);
  }
}

?>
