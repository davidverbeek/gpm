<?php
// get API details
$startTime = time();
/**
 * @author		MagePsycho <info@magepsycho.com>
 * @website		https://www.magepsycho.com
 * @category	Export / Import
 */
$mageFilename = '../../app/Mage.php';
require_once $mageFilename;
Mage::setIsDeveloperMode(true);
//ini_set('display_errors', 1);
umask(0);
Mage::app('admin');
Mage::register('isSecureArea', 1);
Mage::app()->setCurrentStore(3);

set_time_limit(0);
ini_set('memory_limit','1024M');


//echo "<pre>";
/***************** UTILITY FUNCTIONS ********************/
function _getConnection($type = 'core_read'){
	return Mage::getSingleton('core/resource')->getConnection($type);
}

function _getTableName($tableName){
	return Mage::getSingleton('core/resource')->getTableName($tableName);
}

function _getTransferroSKUs(){
	
	$connection = _getConnection('core_read');
	$sql		= "SELECT 
					`e`.`entity_id` AS `id`,
					`e`.`sku` AS `sku`
				FROM
					`mage_catalog_product_entity` AS `e`	
						LEFT JOIN
					`mage_catalog_product_entity_int` AS `cpei` ON e.entity_id = cpei.entity_id
						AND cpei.attribute_id = 2205
				WHERE
					cpei.value = 2";
					
	$productCollection = $connection->fetchAll($sql);
	
	$skus = array();
	foreach ($productCollection as $sku) {
		$skus[$sku['id']] = $sku['sku'];
	}
		 
	return $skus;
}
try {
	
	$host = "https://pxapi.transferro.com/Tradeitem_info";
	$username = "gyzsonline";
	$password = "Loco8121";
	$headers = array(
    'Content-Type:application/json',
    'Authorization: Basic '. base64_encode($username.":".$password) // <---
	);
	
	$chunkSKU_data = ['647.122.0422', '299.372.2820'];
		$post_data = $products = array();
		$post_data["GLN"] = "8719333015354";
		$k = 0;
	  foreach($chunkSKU_data as $chunked_data) {  
			$post_data["Tradeitems"][$k]["TradeitemNumber"] = $chunked_data;
			$k++;
        }
       
	  $ch = curl_init($host);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$return_tf_data = json_decode(curl_exec($ch),true);
		if(curl_errno($ch))
		echo 'Curl error: '.curl_error($ch);
		curl_close($ch);

		if(isset($return_tf_data["TradeItems"]) && count($return_tf_data["TradeItems"]) > 0) {
			foreach($return_tf_data["TradeItems"] as $tfk=>$tfd) {
				
				$products[$tfk]['sku'] = $tfd['TradeitemNumber'];
				$products[$tfk]['bp_min_order_qty'] = $tfd['TradeitemOrderAmount'] * $tfd['NetUnitPrice'];
				$products[$tfk]['afwijkenidealeverpakking'] = 0;
				$products[$tfk]['min_order_qty'] = (int)$tfd['TradeitemOrderAmount'];
				$products[$tfk]['bp_per_piece'] = $tfd['NetUnitPrice'];
				$products[$tfk]['ean'] = $tfd['Gtin'];
				$products[$tfk]['supplier_id'] = '3';
			}
		} else {
			Mage::log("Error in chunk:-"."$chunkSKU_key".null, 'update_transferro_product_stock_rest.log');
		}
	$sql_2 = "INSERT INTO all_supplier_products(sku,ean,supplier_id,bp_per_piece,bp_min_order_qty, min_order_qty, afwijkenidealeverpakking, created_at, modified_at) VALUES ";
	$all_col_data = array();
	foreach ($products as $key=>$data) {
		$all_col_data[] = "('".$data['sku']."','".$data['ean']."' ,'".$data['supplier_id']."','".$data['bp_per_piece']."','".$data['bp_min_order_qty']."', '".$data['min_order_qty']."','".$data['afwijkenidealeverpakking']."', '".NOW()."', '".NOW()."')";
	}

	$sql_2 .= implode(',', $all_col_data);
	

	$modified_at = date('Y-m-d h:i:s');
	$sql_2 .= " ON DUPLICATE KEY UPDATE sku = VALUES(sku), ean=VALUES(ean),supplier_id = VALUES(supplier_id),bp_per_piece = VALUES(bp_per_piece),bp_min_order_qty = VALUES(bp_min_order_qty), min_order_qty = VALUES(min_order_qty), afwijkenidealeverpakking = VALUES(afwijkenidealeverpakking), modified_at = VALUES(modified_at)";

	$write_connection		= _getConnection('core_write');
	if ($write_connection->query($sql_2)) {
		echo $response_data['msg'] = "Excel Data is saved successfully.";
	} else {
		echo mysqli_error();
	}

} catch (Exception $e){
		Mage::log($e->getMessage(),null, 'update_transferro_product_stock_rest.log');
} 