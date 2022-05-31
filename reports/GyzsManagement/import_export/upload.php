<?php
  include "../config/config.php";
include "../define/constants.php";
include "../lib/SimpleXLSX.php";
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);

  error_reporting(E_ALL);
/*
what to do ?
it is reading file

*/

if(isset($_POST['hidden_field']))
{
	$error = '';
	$total_line = '';
	
	if($_FILES['file']['name'] != '')
	{
		$allowed_extension = array('xlsx');
		$file_array = explode(".", $_FILES["file"]["name"]);
		$extension = end($file_array);
		if(in_array($extension, $allowed_extension))
		{
			$xlsx = SimpleXLSX::parse($_FILES['file']['tmp_name']);
			$chunk_xlsx_data = array_chunk($xlsx->rows(), PMCHUNK);
			$progress_status = array();
			$current_rec = 1;
			$progress_status["total_records"] = count($xlsx->rows());
			$valid_count = 0;
			$upload_summary = array();

			$progress_file_path = $document_root_path."/import_export/progress.txt";
			if(count($chunk_xlsx_data)) {
				
				$valid_header = array("Artikelnummer (Artikel)","Inkoopprijs (Inkpr per piece)","Nieuwe Verkoopprijs (Niewe Vkpr per piece)","4027100","4027101","4027102","4027103","4027104","4027105","4027106","4027107","4027108","4027109","4027110");
				$check_header = array_diff($chunk_xlsx_data[0][0],$valid_header);
				if(count($check_header) > 0) {
					$error = 'Header Names Mismatched, please check your xlsx header row names';
				} else {
						$get_all_price_management_data = getAllPriceManagementData();

						foreach($chunk_xlsx_data as $chunked_idx=>$chunked_xlsx_values) {

							$all_col_data = array();
							$updated_product_skus = array();
							$historyArray = array();
							$sql = $last_part_sql = "";
							foreach($chunked_xlsx_values as $c_k=>$chunked_xlsx_value) {
		        				if ($current_rec == 1) { 
									list($sql, $last_part_sql) = makeSqlDependingOnXl($chunk_xlsx_data[0][0]);
									$current_rec++; 
									continue;
								}
		        				$chunked_xlsx_sku = trim($chunked_xlsx_value[0]);
								if(array_key_exists($chunked_xlsx_sku, $get_all_price_management_data)) {
										if( (isset($chunked_xlsx_value[1]) && !is_numeric($chunked_xlsx_value[1])) || (isset($chunked_xlsx_value[2]) && !is_numeric($chunked_xlsx_value[2])) || (isset($chunked_xlsx_value[3]) && !is_numeric($chunked_xlsx_value[3])) || (isset($chunked_xlsx_value[4]) && !is_numeric($chunked_xlsx_value[4])) || (isset($chunked_xlsx_value[5]) && !is_numeric($chunked_xlsx_value[5])) || (isset($chunked_xlsx_value[6]) && !is_numeric($chunked_xlsx_value[6])) || (isset($chunked_xlsx_value[7]) && !is_numeric($chunked_xlsx_value[7])) || (isset($chunked_xlsx_value[8]) && !is_numeric($chunked_xlsx_value[8])) || (isset($chunked_xlsx_value[9]) && !is_numeric($chunked_xlsx_value[9])) || (isset($chunked_xlsx_value[10]) && !is_numeric($chunked_xlsx_value[10])) || (isset($chunked_xlsx_value[11]) && !is_numeric($chunked_xlsx_value[11])) || (isset($chunked_xlsx_value[12]) && !is_numeric($chunked_xlsx_value[12])) || (isset($chunked_xlsx_value[13]) && !is_numeric($chunked_xlsx_value[13])) ) {
											$progress_status['er_imp'][$current_rec] = "<div style='color:red;'><i class='fas fa-exclamation-triangle'></i>&nbsp; Row data not valid. (Row ".($current_rec).")</div>";
											$current_rec++; continue;
										}
		        						$valid_count++;
		        						$afwijkenidealeverpakking = $get_all_price_management_data[$chunked_xlsx_sku]["new_afwijkenidealeverpakking"];
		        						$idealeverpakking = $get_all_price_management_data[$chunked_xlsx_sku]["new_idealeverpakking"];
										
										$debter_buying_price = 0.00;
										$join_cols_names = "(";

										if(in_array("Inkoopprijs (Inkpr per piece)", $chunk_xlsx_data[0][0]) && in_array("Nieuwe Verkoopprijs (Niewe Vkpr per piece)", $chunk_xlsx_data[0][0])) {
											$debter_buying_price = $buying_price = $chunked_xlsx_value[1];
		        							$selling_price = $chunked_xlsx_value[2];
											list($one_rowString, $one_historyString) = getSqlOfColumns($chunked_xlsx_sku, $buying_price, $selling_price);
											$join_cols_names .= $one_rowString;
											$historyArray[] = $one_historyString;
											$updated_product_skus[] = $chunked_xlsx_sku;
										} elseif(in_array("Inkoopprijs (Inkpr per piece)", $chunk_xlsx_data[0][0]) && !in_array("Nieuwe Verkoopprijs (Niewe Vkpr per piece)", $chunk_xlsx_data[0][0])) {
											$debter_buying_price = $buying_price = $chunked_xlsx_value[1];
											$selling_price = $get_all_price_management_data[$chunked_xlsx_sku]["new_selling_price"];

											if($afwijkenidealeverpakking === "0") {
												$selling_price = roundValue((float) $selling_price / $idealeverpakking);
											}
											list($one_rowString, $one_historyString) = getSqlOfColumns($chunked_xlsx_sku, $buying_price, $selling_price);
											$join_cols_names .= $one_rowString;
											$historyArray[] = $one_historyString;
											$updated_product_skus[] = $chunked_xlsx_sku;
										} elseif(!in_array("Inkoopprijs (Inkpr per piece)", $chunk_xlsx_data[0][0]) && in_array("Nieuwe Verkoopprijs (Niewe Vkpr per piece)", $chunk_xlsx_data[0][0])) {
											$buying_price = $get_all_price_management_data[$chunked_xlsx_sku]["new_buying_price"];
		        							$key = array_search ("Nieuwe Verkoopprijs (Niewe Vkpr per piece)", $chunk_xlsx_data[0][0]);
											$selling_price = $chunked_xlsx_value[$key];
											
											if($afwijkenidealeverpakking === "0") {
												$buying_price = roundValue((float) $buying_price / $idealeverpakking);
											}											
											$debter_buying_price = $buying_price;
											list($one_rowString, $one_historyString) = getSqlOfColumns($chunked_xlsx_sku, $buying_price, $selling_price);
											$join_cols_names .= $one_rowString;
											$historyArray[] = $one_historyString;
											$updated_product_skus[] = $chunked_xlsx_sku;
										}
										$allcustomer_groups = getCustomerGroups();
										$check_debter_header = array_diff($allcustomer_groups,$chunk_xlsx_data[0][0]);
										if(count($check_debter_header) < count($allcustomer_groups)) {
											$xls_debter_header_arr = array_diff($allcustomer_groups,$check_debter_header);
											$join_cols_names .= ",";
											$debter_product_arr = getDebterProducts();
											$debter_data_to_insert = $insert_update_group_data = "";
											foreach($xls_debter_header_arr as $head_cust_group_id=>$head_cust_group_name) {
												$key = array_search ($head_cust_group_name, $chunk_xlsx_data[0][0]);
												 	$xlsx_debter_selling_price = $chunked_xlsx_value[$key];
													if($xlsx_debter_selling_price != 0) {
														$d_selling_price = 0.00;
														if($afwijkenidealeverpakking === "0") {
															$d_selling_price = round($xlsx_debter_selling_price * $idealeverpakking,2);
															$debter_buying_price = round($debter_buying_price * $idealeverpakking,2);
														} else {
															$d_selling_price = round($xlsx_debter_selling_price,2);
														}
														$supplier_gross_price = ($get_all_price_management_data[$chunked_xlsx_sku]["new_gross_unit_price"] == 0 ? 1:$get_all_price_management_data[$chunked_xlsx_sku]["new_gross_unit_price"]);
														$d_margin_on_buying_price = round((($d_selling_price - $debter_buying_price) / $debter_buying_price) * 100,2);
														$d_margin_on_selling_price = round((($d_selling_price - $debter_buying_price) / $d_selling_price) * 100,2);
														$d_discount_on_gross = round((1 - ($d_selling_price/$supplier_gross_price)) * 100,2);
														
														// check whether product belongs to debter?
														$xlsx_product_id = $get_all_price_management_data[$chunked_xlsx_sku]["product_id"];
														$given_debter_product_arr = array();
														if (isset($debter_product_arr[$head_cust_group_name])) {
															$given_debter_product_arr = explode(',', $debter_product_arr[$head_cust_group_name]);
														}
														if ($given_debter_product_arr && !in_array($xlsx_product_id, $given_debter_product_arr)) {
															$d_selling_price = $get_all_price_management_data[$chunked_xlsx_sku]["db_group_".$head_cust_group_name."_debter_selling_price"];
															$d_margin_on_buying_price = $get_all_price_management_data[$chunked_xlsx_sku]["db_group_".$head_cust_group_name."_margin_on_buying_price"];
															$d_margin_on_selling_price = $get_all_price_management_data[$chunked_xlsx_sku]["db_group_".$head_cust_group_name."_margin_on_selling_price"];
															$d_discount_on_gross = $get_all_price_management_data[$chunked_xlsx_sku]["db_group_".$head_cust_group_name."_discount_on_grossprice_b_on_deb_selling_price"];
														}
														$debter_data_to_insert .= "'".$head_cust_group_id."','".$d_selling_price."','".$d_margin_on_buying_price."','".$d_margin_on_selling_price."','".$d_discount_on_gross."',";
													} else {
														$debter_data_to_insert .= "'0','0','0','0','0',";
													}
											}
											$debter_data_to_insert = rtrim($debter_data_to_insert,",");
											$join_cols_names .= $debter_data_to_insert;
										}
										$join_cols_names .= ')';
										$all_col_data[] = $join_cols_names;
		        				} else {
			      					$progress_status['er_imp'][$current_rec] = "<div style='color:red;'><i class='fas fa-exclamation-triangle'></i>&nbsp;Sku <b>".$chunked_xlsx_sku."</b> does not exist. (Row ".($current_rec).")</div>";
			      				}
		        				
		        				$progress_status["current_record"] = $current_rec;
		        				$progress_status["percentage"] = intval($current_rec/$progress_status["total_records"] * 100);
		        				$progress_status['er_imp']["er_summary"] = "<div>Imported ".$valid_count." Out Of ".$progress_status["total_records"]."</div>";

		        				file_put_contents($progress_file_path, json_encode($progress_status));
		        				$current_rec++;
		        				usleep(50000);
		        			}

							$sql .= implode(",", $all_col_data) . $last_part_sql;
		        			if($conn->query($sql)) {
					          bulkInsertLog($chunked_idx,"Bulk Update:".count($chunked_xlsx_values));
					          changeUpdateStatus($conn, implode("','", $updated_product_skus));


					           	// Add in history
						          if(count($historyArray) > 0) {
						            addInHistory($conn,$historyArray,$chunked_idx);
						          }
						          // Add in history
					        } else {
					          bulkInsertLog($chunked_idx,"Bulk Update Error:".mysqli_error($conn));
					        } 
		    	}
				}
			}		
		}
		else
		{
			$error = 'Only XLSX file format is allowed';
		}
	}
	else
	{
		$error = 'Please Select File';
	}

	if($error != '')
	{
		$output = array(
			'error'		=>	$error
		);
	}	
	else
	{
		$output = array(
			'success' =>	true
		);
	}

	echo json_encode($output);exit;
}

function getAllPriceManagementData() {
    global $scale, $conn;
    //$sql = "SELECT sku, buying_price, idealeverpakking, afwijkenidealeverpakking, gross_unit_price, gyzs_selling_price FROM price_management_data";
   
	$allcustomer_groups = getCustomerGroups();
	$group_cols = "";

	foreach($allcustomer_groups as $head_cust_group_id=>$head_cust_group_name) {
		$group_col_debter_selling_price = "pmd.group_".$head_cust_group_name."_debter_selling_price AS group_".$head_cust_group_name."_debter_selling_price";
		$group_col_margin_on_buying_price = "pmd.group_".$head_cust_group_name."_margin_on_buying_price AS group_".$head_cust_group_name."_margin_on_buying_price";
		$group_col_margin_on_selling_price = "pmd.group_".$head_cust_group_name."_margin_on_selling_price AS group_".$head_cust_group_name."_margin_on_selling_price";
		$group_col_discount_on_grossprice_b_on_deb_selling_price = "pmd.group_".$head_cust_group_name."_discount_on_grossprice_b_on_deb_selling_price AS group_".$head_cust_group_name."_discount_on_grossprice_b_on_deb_selling_price";

		$group_cols .= $group_col_debter_selling_price.",".$group_col_margin_on_buying_price.",".$group_col_margin_on_selling_price.",".$group_col_discount_on_grossprice_b_on_deb_selling_price.",";
	 }
	//$group_cols already has comma at its end

    $sql = "SELECT pmd.product_id AS product_id,
     								pmd.sku AS sku, 
  				 					pmd.buying_price AS buying_price,
  				 					pmd.idealeverpakking AS idealeverpakking,
  				 					pmd.afwijkenidealeverpakking AS afwijkenidealeverpakking,
  				 					pmd.gross_unit_price AS gross_unit_price,
  				 					pmd.net_unit_price AS net_unit_price,
  				 					pmd.selling_price AS selling_price,
									".$group_cols."
           CAST(CASE WHEN mcpet.value IS NOT NULL THEN mcpet.value ELSE mcpev_ideal.value END AS UNSIGNED) AS webshop_idealeverpakking,
           CASE WHEN mcpet_af.value IS NOT NULL THEN mcpet_af.value ELSE mcpev_afw.value END AS webshop_afwijkenidealeverpakking,
           REPLACE(mcpev_grossprice.value,',','.') AS webshop_grossprice,
           
           	(CASE WHEN (CASE WHEN mcpet_af.value IS NOT NULL THEN mcpet_af.value = 0 ELSE mcpev_afw.value = 0 END) 
             THEN
                 CAST((mcped_selling_price.value * CASE WHEN mcpet.value IS NOT NULL THEN mcpet.value ELSE mcpev_ideal.value END) AS DECIMAL (10 , ".$scale."))
             ELSE
                 CAST((mcped_selling_price.value) AS DECIMAL (10 , ".$scale."))
             END) AS gyzs_selling_price,

             (CASE WHEN (CASE WHEN mcpet_af.value IS NOT NULL THEN mcpet_af.value = 0 ELSE mcpev_afw.value = 0 END) 
             THEN
                 CAST((mcped.value * CASE WHEN mcpet.value IS NOT NULL THEN mcpet.value ELSE mcpev_ideal.value END) AS DECIMAL (10 , ".$scale."))
             ELSE
                 CAST((mcped.value) AS DECIMAL (10 , ".$scale."))
             END) AS gyzs_buying_price
          	
  		    FROM 
  		  		price_management_data AS pmd
  		    LEFT JOIN mage_catalog_product_entity_text AS mcpev_grossprice ON mcpev_grossprice.entity_id = pmd.product_id AND mcpev_grossprice.attribute_id = '".GROSSUNITPRICE."'
  		    LEFT JOIN mage_catalog_product_entity_text AS mcpet ON mcpet.entity_id = pmd.product_id AND mcpet.attribute_id = '".IDEALEVERPAKKING."'
        	LEFT JOIN mage_catalog_product_entity_text AS mcpet_af ON mcpet_af.entity_id = pmd.product_id AND mcpet_af.attribute_id = '".afwijkenidealeverpakking."'
        	LEFT JOIN mage_catalog_product_entity_varchar AS mcpev_afw ON mcpev_afw.entity_id = pmd.product_id AND mcpev_afw.attribute_id = '".afwijkenidealeverpakking."'
			LEFT JOIN mage_catalog_product_entity_varchar AS mcpev_ideal ON mcpev_ideal.entity_id = pmd.product_id AND mcpev_ideal.attribute_id = '".IDEALEVERPAKKING."'

          LEFT JOIN mage_catalog_product_entity_decimal AS mcped ON mcped.entity_id = pmd.product_id AND mcped.attribute_id = '".COST."'
          LEFT JOIN mage_catalog_product_entity_decimal AS mcped_selling_price ON mcped_selling_price.entity_id = pmd.product_id AND mcped_selling_price.attribute_id = '".PRICE."' ORDER BY pmd.id desc limit 10";


    if ($result = $conn->query($sql)) { 
		$all_pm_data = array();
		while ($row = $result->fetch_assoc()) { 
			//$comp_sku = strtolower($row['sku']);
				$comp_sku = trim($row['sku']);
			$all_pm_data[$comp_sku]["product_id"] = $row['product_id'];
			$all_pm_data[$comp_sku]["sku"] = $row['sku'];

			$all_pm_data[$comp_sku]["old_net_unit_price"] = $row['gyzs_buying_price'];	
			$all_pm_data[$comp_sku]["old_gross_unit_price"] = $row['webshop_grossprice'];
			$all_pm_data[$comp_sku]["old_idealeverpakking"] = $row['webshop_idealeverpakking'];
			$all_pm_data[$comp_sku]["old_afwijkenidealeverpakking"] = $row['webshop_afwijkenidealeverpakking'];
			$all_pm_data[$comp_sku]["old_buying_price"] = $row['gyzs_buying_price'];
			$all_pm_data[$comp_sku]["gyzs_selling_price"] = $row['gyzs_selling_price'];
			$all_pm_data[$comp_sku]["new_net_unit_price"] = $row['net_unit_price'];
			$all_pm_data[$comp_sku]["new_gross_unit_price"] = $row['gross_unit_price'];
			$all_pm_data[$comp_sku]["new_idealeverpakking"] = $row['idealeverpakking'];
			$all_pm_data[$comp_sku]["new_afwijkenidealeverpakking"] = $row['afwijkenidealeverpakking'];
			$all_pm_data[$comp_sku]["new_buying_price"] = $row['buying_price'];
			$all_pm_data[$comp_sku]["new_selling_price"] = $row['selling_price'];
			foreach($allcustomer_groups as $head_cust_group_id=>$head_cust_group_name) {
				$new_group = "db_group_".$head_cust_group_name;
				$all_pm_data[$comp_sku][$new_group."_debter_selling_price"] = $row["group_".$head_cust_group_name."_debter_selling_price"];
				$all_pm_data[$comp_sku][$new_group."_margin_on_buying_price"] = $row["group_".$head_cust_group_name."_margin_on_buying_price"];
				$all_pm_data[$comp_sku][$new_group."_margin_on_selling_price"] = $row["group_".$head_cust_group_name."_margin_on_selling_price"];
				$all_pm_data[$comp_sku][$new_group."_discount_on_grossprice_b_on_deb_selling_price"] = $row["group_".$head_cust_group_name."_discount_on_grossprice_b_on_deb_selling_price"];
			} 
	} 
} else {
	echo $conn->error;
}
    return $all_pm_data;
}

function bulkInsertLog($chunk_index,$chunk_msg) {
  $file_pricechunks_log = "../pm_logs/upload_pm_data.txt";
  file_put_contents($file_pricechunks_log,"".date("d-m-Y H:i:s")." Updated Price Chunk (".$chunk_index."):-".$chunk_msg."\n",FILE_APPEND);
}
function changeUpdateStatus($conn,$product_sku) {
  global $conn;
  $change_status = "UPDATE price_management_data SET is_updated = '1' WHERE sku IN ('".$product_sku."')";
  $conn->query($change_status);
  //file_put_contents("../try.txt",$change_status,FILE_APPEND);
}
function roundValue($val) {
  global $scale;
  return round($val,$scale);
}
function getCustomerGroups($group_number=null) {
  global $conn;
  $sql = "SELECT * FROM price_management_customer_groups ORDER BY magento_id";
  if(!is_null($group_number)) {
	$sql = "SELECT * FROM price_management_customer_groups where customer_group_name='".$group_number."'";
  }
  
  $all_customer_groups = array();
  if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
      $all_customer_groups[$row['magento_id']] = $row['customer_group_name'];
    }
  }
  return $all_customer_groups;
}

function addInHistory($conn,$updateLogs,$chunk_index="") {

  $insertdata = implode(",", $updateLogs);

  $history = "INSERT INTO 
  price_management_history (
  product_id,
  old_net_unit_price,
  old_gross_unit_price,
  old_idealeverpakking,
  old_afwijkenidealeverpakking,
  old_buying_price,
  old_selling_price,
  new_net_unit_price,
  new_gross_unit_price,
  new_idealeverpakking,
  new_afwijkenidealeverpakking,
  new_buying_price,
  new_selling_price,
  updated_date_time,
  updated_by,
  is_viewed,
  fields_changed,
  buying_price_changed
  ) 
  VALUES ".$insertdata."";

  if($conn->query($history)) {
    $chunk_msg = "Processed Chunk (".$chunk_index.")\n";
    $chunk_msg .= "Added in history:-".count($updateLogs)."\n".print_r($updateLogs,true)."\n";
    historyLog($chunk_index,$chunk_msg);
  }
}

function historyLog($chunk_index,$chunk_msg) {
  $file_history_log = "../pm_logs/upload_history_log.txt";
  file_put_contents($file_history_log,"".date("d-m-Y H:i:s")."\n".$chunk_msg."\n",FILE_APPEND);
}

function getDebterProducts() {
	global $conn;
	$sql = "SELECT customer_group_name, product_ids FROM price_management_customer_groups JOIN price_management_debter_categories ON price_management_debter_categories.customer_group = price_management_customer_groups.magento_id;
	";
	$result = $conn->query($sql);
	$debter_products = array();
	while($row = $result->fetch_assoc()) {
		$debter_products[$row['customer_group_name']] = $row['product_ids'];
	}
	return $debter_products;
}

function getSqlOfColumns($chunked_xlsx_sku, $buying_price, $selling_price) {
	
	$get_all_price_management_data = getAllPriceManagementData();
	$afwijkenidealeverpakking = $get_all_price_management_data[$chunked_xlsx_sku]["new_afwijkenidealeverpakking"];
	$idealeverpakking = $get_all_price_management_data[$chunked_xlsx_sku]["new_idealeverpakking"];
	$historyArray = "";

	if($afwijkenidealeverpakking === "0") {
		$pmd_buying_price = roundValue((float) $buying_price * $idealeverpakking);
		$new_selling_price = roundValue((float) $selling_price * $idealeverpakking);
	} else  {
		$pmd_buying_price = roundValue((float) $buying_price);
		$new_selling_price = roundValue((float) $selling_price);
	}

/* 	print_r($pmd_buying_price);
	echo $get_all_price_management_data[$chunked_xlsx_sku]["new_buying_price"];
	echo $new_selling_price;
	echo $get_all_price_management_data[$chunked_xlsx_sku]["new_selling_price"];exit; */
	
	$supplier_gross_price = ($get_all_price_management_data[$chunked_xlsx_sku]["new_gross_unit_price"] == 0 ? 1:$get_all_price_management_data[$chunked_xlsx_sku]["new_gross_unit_price"]);
	$webshop_selling_price = $get_all_price_management_data[$chunked_xlsx_sku]["gyzs_selling_price"];

	$profit_margin = roundValue((($new_selling_price - $pmd_buying_price)/$pmd_buying_price) * 100);
	$profit_margin_sp = roundValue((($new_selling_price - $pmd_buying_price)/$new_selling_price) * 100);
	$percentage_increase = roundValue((($new_selling_price - $webshop_selling_price)/$webshop_selling_price) * 100);
	$discount_percentage = roundValue((1 - ($new_selling_price/$supplier_gross_price)) * 100);
	$col_data = "'".$chunked_xlsx_sku."', '".$pmd_buying_price."', '".$pmd_buying_price."', '".$new_selling_price."', '".$profit_margin."', '".$profit_margin_sp."', '".$percentage_increase."', '".$discount_percentage."'";
	
	// Add in history
	$fields_changed = array();
	$buying_price_changed = 0;

	if($get_all_price_management_data[$chunked_xlsx_sku]["new_buying_price"] !=  $pmd_buying_price) {
		  $fields_changed[] = "new_buying_price";
		  $buying_price_changed = 1;
	}
	if($get_all_price_management_data[$chunked_xlsx_sku]["new_selling_price"] !=  $new_selling_price) {
		  $fields_changed[] = "new_selling_price";
	}

	if( ($get_all_price_management_data[$chunked_xlsx_sku]["new_buying_price"] !=  $pmd_buying_price) || ($get_all_price_management_data[$chunked_xlsx_sku]["new_selling_price"] !=  $new_selling_price) ) {
		$historyArray = "('".$get_all_price_management_data[$chunked_xlsx_sku]["product_id"]."',
		'".$get_all_price_management_data[$chunked_xlsx_sku]["old_net_unit_price"]."',
		'".$get_all_price_management_data[$chunked_xlsx_sku]["old_gross_unit_price"]."',
		'".$get_all_price_management_data[$chunked_xlsx_sku]["old_idealeverpakking"]."',
		'".$get_all_price_management_data[$chunked_xlsx_sku]["old_afwijkenidealeverpakking"]."',
		'".$get_all_price_management_data[$chunked_xlsx_sku]["old_buying_price"]."',
		'".$get_all_price_management_data[$chunked_xlsx_sku]["gyzs_selling_price"]."',
		'".roundValue($pmd_buying_price)."',
		'".$get_all_price_management_data[$chunked_xlsx_sku]["new_gross_unit_price"]."',
		'".$get_all_price_management_data[$chunked_xlsx_sku]["new_idealeverpakking"]."',
		'".$get_all_price_management_data[$chunked_xlsx_sku]["new_afwijkenidealeverpakking"]."',
		'".roundValue($pmd_buying_price)."',
		'".roundValue($new_selling_price)."',
		'".date("Y-m-d H:i:s")."',
		'Price Management',
		'No',
		'".json_encode($fields_changed)."',
		'".$buying_price_changed."'
	  )";
	}

	return array($col_data, $historyArray);
}//end getSqlOfColumns()

function getDebterSql() {
	$debter_data_to_insert = "";
	$xlsx_debter_col_start_index = 3;
	foreach($allcustomer_groups as $g_id=>$g_name) {
		$xlsx_debter_selling_price = $chunked_xlsx_value[$xlsx_debter_col_start_index];
			if(isset($xlsx_debter_selling_price) && $xlsx_debter_selling_price != 0) {
				$d_selling_price = "";
				if($afwijkenidealeverpakking === "0") {
					$d_selling_price = round($xlsx_debter_selling_price * $idealeverpakking,2);
				} else {
					$d_selling_price = round($xlsx_debter_selling_price,2);
				}

				$d_margin_on_buying_price = round((($d_selling_price - $pmd_buying_price) / $pmd_buying_price) * 100,2);
				$d_margin_on_selling_price = round((($d_selling_price - $pmd_buying_price) / $d_selling_price) * 100,2);
				$d_discount_on_gross = round((1 - ($d_selling_price/$supplier_gross_price)) * 100,2);
				
				// check whether product belongs to debter?
				$xlsx_product_id = $get_all_price_management_data[$chunked_xlsx_sku]["product_id"];
				$given_debter_product_arr = array();
				if (isset($debter_product_arr[$g_name])) {
					$given_debter_product_arr = explode(',', $debter_product_arr[$g_name]);
				}
				if ($given_debter_product_arr && !in_array($xlsx_product_id, $given_debter_product_arr)) {
					$d_selling_price = $get_all_price_management_data[$chunked_xlsx_sku]["db_group_".$g_name."_debter_selling_price"];
					$d_margin_on_buying_price = $get_all_price_management_data[$chunked_xlsx_sku]["db_group_".$g_name."_margin_on_buying_price"];
					$d_margin_on_selling_price = $get_all_price_management_data[$chunked_xlsx_sku]["db_group_".$g_name."_margin_on_selling_price"];
					$d_discount_on_gross = $get_all_price_management_data[$chunked_xlsx_sku]["db_group_".$g_name."_discount_on_grossprice_b_on_deb_selling_price"];
				}
				$debter_data_to_insert .= "'".$g_id."','".$d_selling_price."','".$d_margin_on_buying_price."','".$d_margin_on_selling_price."','".$d_discount_on_gross."',";
			} else {
				$debter_data_to_insert .= "'0','0','0','0','0',";
			}
			$xlsx_debter_col_start_index++;
	}
	$debter_data_to_insert = rtrim($debter_data_to_insert,",");
	// Insert debter data ends

	$col_data[] = "('".$chunked_xlsx_sku."', '".$pmd_buying_price."', '".$pmd_buying_price."', '".$new_selling_price."', '".$profit_margin."', '".$profit_margin_sp."', '".$percentage_increase."', '".$discount_percentage."',".$debter_data_to_insert.")";
									
}


/**
 * array $chunk_xlsx_heading_row
 */
function makeSqlDependingOnXl($chunk_xlsx_heading_row) {
	$sql = "INSERT INTO price_management_data (sku,";
	$last_part_sql = " ON DUPLICATE KEY UPDATE";
	$back_part_cols = "";

	if(in_array("Inkoopprijs (Inkpr per piece)", $chunk_xlsx_heading_row) || in_array("Nieuwe Verkoopprijs (Niewe Vkpr per piece)", $chunk_xlsx_heading_row)) {
		$sql .= "net_unit_price, buying_price, selling_price, profit_percentage_buying_price, profit_percentage_selling_price, percentage_increase, discount_on_gross";
		$back_part_cols = " net_unit_price = VALUES(net_unit_price), buying_price = VALUES(buying_price), selling_price = VALUES(selling_price),profit_percentage_buying_price = VALUES(profit_percentage_buying_price),profit_percentage_selling_price = VALUES(profit_percentage_selling_price),percentage_increase = VALUES(percentage_increase),discount_on_gross = VALUES(discount_on_gross)";
	}
	
	$allcustomer_groups = getCustomerGroups();
	$check_debter_header = array_diff($allcustomer_groups, $chunk_xlsx_heading_row);
	
	$xls_debter_header_arr = array();
	if(count($check_debter_header) < count($allcustomer_groups)) {
		$xls_debter_header_arr = array_diff($allcustomer_groups, $check_debter_header);
	}
	$group_cols = $insert_update_group_data = "";
	foreach($xls_debter_header_arr as $head_cust_group_id=>$head_cust_group_name) {
		$group_col_magento_id = "group_".$head_cust_group_name."_magento_id";
		$group_col_debter_selling_price = "group_".$head_cust_group_name."_debter_selling_price";
		$group_col_margin_on_buying_price = "group_".$head_cust_group_name."_margin_on_buying_price";
		$group_col_margin_on_selling_price = "group_".$head_cust_group_name."_margin_on_selling_price";
		$group_col_discount_on_grossprice_b_on_deb_selling_price = "group_".$head_cust_group_name."_discount_on_grossprice_b_on_deb_selling_price";

		$group_cols .= $group_col_magento_id.",".$group_col_debter_selling_price.",".$group_col_margin_on_buying_price.",".$group_col_margin_on_selling_price.",".$group_col_discount_on_grossprice_b_on_deb_selling_price.",";
		$insert_update_group_data .= $group_col_magento_id." = VALUES(".$group_col_magento_id."),".$group_col_debter_selling_price." = VALUES(".$group_col_debter_selling_price."),".$group_col_margin_on_buying_price." = VALUES(".$group_col_margin_on_buying_price."),".$group_col_margin_on_selling_price." = VALUES(".$group_col_margin_on_selling_price."),".$group_col_discount_on_grossprice_b_on_deb_selling_price." = VALUES(".$group_col_discount_on_grossprice_b_on_deb_selling_price."),";
	}
	if($group_cols) {
		$group_cols = rtrim($group_cols,",");
		$sql .= ', '.$group_cols.") VALUES ";
		$insert_update_group_data = rtrim($insert_update_group_data, ',');
		$last_part_sql .= $back_part_cols.', ';
		$last_part_sql .= $insert_update_group_data;
	} else {
		$last_part_sql .= $back_part_cols;
		$sql .= ") VALUES ";
	}
	return array($sql, $last_part_sql);

}//end makeSqlDependingOnXl();