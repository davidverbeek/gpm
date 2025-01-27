<?php

/**
 * 
 * $file_pricechunks_log = "relevance_score_insert_log.txt";
 * */
error_reporting(E_ALL);
include "config/config.php";
include "define/constants.php";

//error_reporting(1);
ini_set("memory_limit", "11G");
ini_set("max_execution_time", 0);


$from_date = mktime(0, 0, 0, date("m")-6, date("d"),   date("Y"));
$created_at = date("Y-m-d",$from_date);

$to_date = mktime(0, 0, 0, date("m"), date("d")-1,   date("Y"));
$created_at_to_date = date("Y-m-d",$to_date);

$order_filter = 'all_orders';

include "get_relevance_revenue.php";

/*print_r($final_data);
print_r('28');*/
//print_r($fetch_response_data);


$file_pricechunks_log = "relevance_score_insert_log.txt";
// Specify the path to your JSON file
$jsonFilePath = 'qtypdata.json';
if (!$jsonFilePath) {
    echo "<p>Unable to open file for reading.\n";
    exit;
}

// Read the file content
$jsonContent = file_get_contents($jsonFilePath);

// Decode the JSON content into a PHP associative array
$dataArray = json_decode($jsonContent, true);
// Check if there was an error during decoding
if (json_last_error() !== JSON_ERROR_NONE) {
    echo 'Error decoding JSON: ' . json_last_error_msg();
    exit;
}

// Now you can work with the $dataArray
//print_r($dataArray);exit;

$roas_SQL ="SELECT roas FROM pm_settings WHERE id = 1";

$setting_resource = $conn->query($roas_SQL);
if (!$setting_resource) {
    die('Invalid query: ' . mysqli_error($conn));
}

$setting_row = $setting_resource->fetch_assoc();
//knowledge this line will print in the page evenif there is not print_r
$setting_row['roas'] = json_decode($setting_row['roas'], true);



// AND pmd.product_id=4831
$product_pmd_currentroas_sql = "SELECT pmd.supplier_type, pmd.artikel_status, rc.roas_target,pmd.product_id, pmd.sku,pmd.profit_percentage_selling_price FROM price_management_data as pmd INNER JOIN roascurrent AS rc ON pmd.product_id = rc.product_id WHERE pmd.supplier_type != 'transferro' AND pmd.supplier_type != 'UITGESLOTEN'";

$result = $conn->query($product_pmd_currentroas_sql);
if (!$result) {
    die('Invalid query: ' . mysql_error());
}

$max_end_target_sql = "SELECT max(rc.roas_target) as max_rt FROM price_management_data as pmd INNER JOIN roascurrent AS rc ON pmd.product_id = rc.product_id WHERE pmd.supplier_type != 'transferro' AND pmd.supplier_type != 'UITGESLOTEN'";
$result_max = $conn->query($max_end_target_sql);
if (!$result_max) {
    die('Invalid query: ' . mysql_error());
}
$max_endtarget = $result_max->fetch_assoc();
$max_endtarget_value = $max_endtarget['max_rt'];

$truncate_resource = $conn->query("TRUNCATE TABLE relevance_score_data");
if (!$truncate_resource) {
    die('Invalid query: ' . mysql_error());
}

if(file_exists($file_pricechunks_log)) {
	unlink($file_pricechunks_log);
}

$relevance_row_insert = array();
while($relevance_inputs = $result->fetch_assoc())
{
	//print_r($final_data['revenue_data'][ $relevance_inputs['sku']]);exit;
    $product_id = $relevance_inputs['product_id'];
	$relevance_row_insert[$product_id]['product_id'] =  $relevance_inputs['product_id'];
	$relevance_row_insert[$product_id]['sku'] =  $relevance_inputs['sku'];

	if((isset($relevance_inputs['product_id']) && !is_numeric($relevance_inputs['product_id']))) {
		//file_put_contents('wedspe.txt', $product_id);
		continue;
	}

	$product_lead = $product_lead_score = get_required_score($relevance_inputs['supplier_type'],'relevance_lead_time');
	$product_lead_score = $product_lead_score * $setting_row['roas']['weights']['manual_product_label'];

	$product_quantity = 0;
	if (isset($dataArray[$relevance_inputs['product_id']])) {
		$product_quantity = $dataArray[$relevance_inputs['product_id']]['qty'];
	}

	$stock_level = 0;
	if($product_quantity > 0) {
		$product_stock_level = 1 * $setting_row['roas']['weights']['stock_level'];
		$stock_level = 1;
	} else {
		$product_stock_level = 0;
	}



	//$roas_score_only = $roas_score = get_required_score($relevance_inputs['roas_target'],'relevance_roas_percentage');
    if($relevance_inputs['roas_target'] > 0) {
        $roas_score_only = $relevance_inputs['roas_target'];
        $roas_in_calculations = $relevance_inputs['roas_target']/$max_endtarget_value;
        $roas_score = $roas_in_calculations * $setting_row['roas']['weights']['weight_roas'];
    } else {
        $roas_score = 0;
        $roas_score_only = 'NEG';
        $roas_in_calculations = 0;
    }
    
 
    $total_absoulte_m = $fetch_response_data["tot_abs_margin"];
    if(isset($final_data['revenue_data'][$relevance_inputs['sku']]) && $final_data['revenue_data'][$relevance_inputs['sku']]['sku_abs_margin']>0) {
        $sku_abs_margin = $final_data['revenue_data'][$relevance_inputs['sku']]['sku_abs_margin'];        
        //$abs_margin_calculations = $sku_abs_margin/$total_absoulte_m;
        $abs_margin_calculations = log10(1 + $sku_abs_margin);
        $absoulte_score = $abs_margin_calculations * $setting_row['roas']['weights']['weight_absolute'];
    } else {
        $absoulte_score = 0;
        $abs_margin_calculations = 0;
        $sku_abs_margin = 'NA OR NEG';
    }

   
  $sku_sp_margin = $relevance_inputs['profit_percentage_selling_price'];
    $sp_margin_calculation =$sku_sp_margin /100;
    $sp_margin_score =$sp_margin_calculation * $setting_row['roas']['weights']['weight_spm'];


	$article_status = get_required_score($relevance_inputs['artikel_status'],'relevance_article');
	if($article_status == '1' && $product_quantity > 0) {
		$product_article_status = $setting_row['roas']['relevance_article']['status_1_in'];
	} else if($article_status == '1' && $product_quantity < 0) {
		$product_article_status = $setting_row['roas']['relevance_article']['status_1_out'];
	}  else if($article_status == '2') {
		$product_article_status = $setting_row['roas']['relevance_article']['status_2'];
	} else {
		$product_article_status = $setting_row['roas']['relevance_article']['status_other'];
	}
	$product_article_score = (int)$product_article_status;
	$product_article_status = (int)$product_article_status * $setting_row['roas']['weights']['article_status'];

	
	$relevance_calculations = $roas_score + $absoulte_score + $sp_margin_score + $product_lead_score + $product_stock_level + $product_article_status;



	$relevance_row_insert[$product_id]['relevance_score'] = $relevance_calculations;

	$calculation_txt = '<div class="box box-primary">
                              <div class="box-header with-border">
                                  <h3 class="box-title">Product data</h3>
                              </div>
                              <div class="box-body">
                                  <dl class="dl-horizontal">
                                      <dt>SKU</dt>
                                      <dd>'.$relevance_inputs['sku'].'</dd>
                                      <dt>Roas Target </dt>
                                      <dd>'.$roas_in_calculations.' ( '.$roas_score_only.'/'.$max_endtarget_value.') ( Roas Target / Max Roas Target)</dd>
                                      <dt>Absolute Margin</dt>
                                      <dd>'.$abs_margin_calculations.' ( '.$sku_abs_margin.'/'.$total_absoulte_m.')'.' ( Absolute Margin / Total Absolute Margin)'.'</dd>
                                      <dt>Selling Price Margin</dt>
                                       <dd>'.$sp_margin_calculation.' ( '.$sku_sp_margin.'/100)'.' ( Marge Vkpr / 100)'.'</dd>
                                      <dt>Supplier</dt>
                                      <dd>'.$relevance_inputs['supplier_type'].'</dd>                                     
                                      <dt>Product Quantity</dt>
                                      <dd>'.$product_quantity.'</dd>
                                      <dt>Stock Level</dt>
                                      <dd>'.$stock_level.' (Quantity = '.$product_quantity.')</dd>
                                      <dt>Article Status</dt>
                                      <dd>'.(int)$relevance_inputs['artikel_status'].'</dd>                                
                                  </dl>
                              </div>
                          </div>

  

                          <div class="box box-success">
                              <div class="box-header with-border">
                                  <h3 class="box-title">Weights</h3>
                              </div>
                              <div class="box-body">
                                  <dl class="dl-horizontal">
                                      <dt>Roas </dt>
                                      <dd>'. $setting_row['roas']['weights']['weight_roas'].'</dd>
                                      <dt>Absolute Margin </dt>
                                      <dd>'.$setting_row['roas']['weights']['weight_absolute'].'</dd>
                                      <dt>Selling Price Margin </dt>
                                      <dd>'.$setting_row['roas']['weights']['weight_spm'].'</dd>
                                      <dt>Supplier </dt>
                                      <dd>'.$setting_row['roas']['weights']['manual_product_label'].'</dd>
                                      <dt>Stock Level </dt>
                                      <dd>'.$setting_row['roas']['weights']['stock_level'].'</dd>
                                      <dt>Article Status </dt>
                                      <dd>'.$setting_row['roas']['weights']['article_status'].'</dd>
                                  </dl>
                              </div>
                          </div>
                          <div class="box box-danger">
                              <div class="box-header with-border">
                                  <h3 class="box-title">Relevance Score</h3>
                              </div>
                              <div class="box-body">
                                  <dl class="dl-horizontal">
                                      <dt>Roas Score = </dt>
                                      <dd>'.$roas_score.' ( Roas Target * Roas Weight)</dd>
                                      <dt>Absolute Margin Score = </dt>
                                      <dd>'.$absoulte_score.' ( Absolute Margin * Weight of Absolute Margin)</dd>
                                      <dt>Selling Price Margin Score = </dt>
                                      <dd>'.$sp_margin_score.' ( Selling Price Margin * Weight of Selling Price Margin )</dd>
                                      <dt>Supplier Score = </dt>
                                      <dd>'.$product_lead.'&nbsp'.'( Check Supplier Score ('.$relevance_inputs['supplier_type'].') in settings page  )</dd>
                                      <dt>Stock Level Score = </dt>
                                      <dd>'.$stock_level.'&nbsp'.'(Product Data Stock Level)</dd>
                                      <dt>Article Status Score = </dt>
                                      <dd>'.$product_article_score.'&nbsp'.'( Check Article Status Score ('.$product_article_score.') in settings page )'.'</dd>
                                      <dt>Relevance Score = </dt>
                                      <dd> ('.$roas_in_calculations.'*'.$setting_row['roas']['weights']['weight_roas'].')[Roas Target * Roas Weight] + ('.$abs_margin_calculations.'*'.$setting_row['roas']['weights']['weight_absolute'].')[Absolute Margin * Weight of Absolute Margin] +('.$sp_margin_calculation.'*'.$setting_row['roas']['weights']['weight_spm'].')[Selling Price Margin * Weight of Selling Price Margin] + ('.$product_lead.'*'.$setting_row['roas']['weights']['manual_product_label'].')[Supplier Score * Supplier Weight]
                                        + ('.$stock_level.'*'.$setting_row['roas']['weights']['stock_level'].')[Stock Level Score * Stock Level Weight] + ('.$product_article_score.'*'.$setting_row['roas']['weights']['article_status'].')[Article Status Score * Article Status Weight ] = '.round($relevance_calculations,2).'
                                      </dd>                                  
                                  </dl>
                              </div>
                          </div>
                          ';

                          $relevance_row_insert[$product_id]['explaination'] = $calculation_txt;

}

$make_chunks = array_chunk($relevance_row_insert,PMCHUNK);

if(count($make_chunks)) {
        list($sql_row_format, $last_part_sql) = makeSqlForRowFormat();
try {
        foreach($make_chunks as $chunked_idx=>$chunked_item) {
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


                    if($conn->query($chunk_sql)) {
                        bulkInsertLog($chunked_idx,"Bulk Insert:".count($chunked_item));                    
                    } else {
                       // echo mysqli_error($conn)."\n".$chunk_sql;exit;
                        bulkInsertLog($chunked_idx,"Bulk Insert Error:".mysqli_error($conn)."\n".$chunk_sql);
                    }
            }
        }
         } catch(Exception $e) {
         	bulkInsertLog($chunked_idx,"Bulk Update Error:".$e."\n".$chunk_sql);
         }

    }


function insertBatch_2($sql_row_format, $last_part_sql,$batchData, $chunked_idx) {

    $all_col_data = array();
    $chunk_sql = "";
    global $conn;
    foreach($batchData as $c_k=>$row) {
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

    $total_updates = count($all_col_data);

    if($total_updates > 0) {
    	
			//throw new Exception("Some error message");
			$chunk_sql = $sql_row_format.implode(",", $all_col_data) . $last_part_sql;

			/* if($chunked_idx == 0) {
			if($conn->query("TRUNCATE TABLE suppliers_row_format")) {
			bulkInsertLog($chunked_idx,"Truncated suppliers_row_format:");
			}
			}*/

			if($conn->query($chunk_sql)) {
			bulkInsertLog($chunked_idx,"Bulk Update:".$total_updates);                    
			} else {
			// echo mysqli_error($conn)."\n".$chunk_sql;exit;
			bulkInsertLog($chunked_idx,"Bulk Update Error:".mysqli_error($conn)."\n".$chunk_sql);
			}
       
			//echo $e;
			bulkInsertLog($chunked_idx,"Bulk Update Error:".$e."\n".$chunk_sql);
		
    }
    unset($all_col_data);


}

function makeSqlForRowFormat()
{
	$sql = "INSERT INTO relevance_score_data (product_id,sku, relevance_score, explaination";

    $last_part_sql = " ON DUPLICATE KEY UPDATE ";

    $back_part_cols = "sku = VALUES(sku), product_id = VALUES(product_id),relevance_score=VALUES(relevance_score),explaination=VALUES(explaination)";
    $last_part_sql .= $back_part_cols;
        $sql .= ") VALUES ";
        return array($sql, $last_part_sql);

}


   




function get_required_score($supplier_or_number_status, $relevance_type) {
	global $conn, $setting_row;
	
	$score = $setting_row['roas'][$relevance_type];
	$required_score = 0;
	if($relevance_type == 'relevance_lead_time') {
		$required_score = isset($score[strtolower($supplier_or_number_status)]) ?$score[strtolower($supplier_or_number_status)]: 0;
	}else if($relevance_type == 'relevance_roas_percentage') {
        if(isset($setting_row['roas']["endroas_percent_lower_bound"]) && $supplier_or_number_status < array_keys($setting_row['roas']["endroas_percent_lower_bound"])[0]) { 
            $required_score = (int)array_values($setting_row['roas']["endroas_percent_lower_bound"])[0];
        } elseif($required_score === 0) { 
            foreach ($score as $range => $value) {
                $score = $setting_row['roas'][$relevance_type];
                list($min, $max) = explode('-', $range);
                if ($supplier_or_number_status >= $min && $supplier_or_number_status < $max) {
                    $required_score = (int)$value;
                    break;
                }
            }
        }

        if(isset($setting_row['roas']["endroas_percent_upper_bound"]) && $required_score === 0 && $supplier_or_number_status >=  array_keys($setting_row['roas']["endroas_percent_upper_bound"])[0]){
            $required_score = (int)array_values($setting_row['roas']["endroas_percent_upper_bound"])[0];
        }
        

	}else if($relevance_type == 'relevance_article') {
		if ($supplier_or_number_status == '1') {
			$required_score = (int)$supplier_or_number_status;
		} else if($supplier_or_number_status == '2') {
			$required_score = (int)$supplier_or_number_status;
		} else {
			$required_score = 0;
		}

	}
	return $required_score;

}//end get_required_score()

function bulkInsertLog($chunk_index,$chunk_msg) {
  $file_pricechunks_log = "relevance_score_insert_log.txt";

  //$result = file_put_contents($file_pricechunks_log,"".date("d-m-Y H:i:s")." Inserted Relevance Score Chunk (".$chunk_index."):-".$chunk_msg."\n", FILE_APPEND);
   $result = file_put_contents($file_pricechunks_log,"".date("d-m-Y H:i:s")." Inserted Relevance Score Chunk (".$chunk_index."):-".$chunk_msg."\n", FILE_APPEND);

  if ($result === false) {
    $error = error_get_last();
    echo "File put contents error: " . $error['message'];
}

}//end bulkInsertLog()