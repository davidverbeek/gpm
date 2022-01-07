<?php
class Helios_Deliverytime_Helper_Data extends Mage_Core_Helper_Abstract {

	/**
	 * Global constants
	 */
	const LOG_FILE_NAME = 'stock_deliverytime.log';
	const LOG_SQL_FILE_NAME = 'stock_insert_query.log';
	const PRODUCT_TYPE = 'simple';
	const ATTRIBUTE_CODE = 'deliverytime';
	const MIN_DELIVERYTIME_CONDITION = 2;
	const MIN_DELIVERYTIME = 5;
	const STOCK_API_BREAKPOINT = 4000;

	const REST_STOCK_LOG_FILE_NAME = 'rest_stock_daily.log';
	const REST_STOCK_LOG_FILE_NAME_15 = 'rest_stock_every_15_minutes.log';
	const REST_DELIVERY_TIME_LOG_FILE_NAME = 'rest_delivery_time.log';

	// get connection based on requirment
	protected function _getConnection($type = 'core_read'){
		return Mage::getSingleton('core/resource')->getConnection($type);
	}

	// get magento table name
	protected function _getTableName($tableName){
		return Mage::getSingleton('core/resource')->getTableName($tableName);
	}
	
	// get magento EAV table name for Attribute
	protected function _getEAVTable(){
		$connection = $this->_getConnection('core_read');
		$sql = "SELECT attribute_id, entity_type_id, concat('catalog_product_entity_', backend_type) AS 'table_name' FROM " . $this->_getTableName('eav_attribute') . " WHERE attribute_code = '" . Helios_Deliverytime_Helper_Data::ATTRIBUTE_CODE . "'";
		$tableName = $connection->fetchRow($sql);

		return $tableName;
	}
	
	// get manual product QTY from DB
	protected function _getManualProductQty($productId){
		$connection = $this->_getConnection('core_read');
		
		$sqlManualProduct = "SELECT `e`.entity_id,`e`.sku,`at_manualproduct`.`value` AS `manualproduct`,`at_qty`.qty AS qty
		FROM " . $this->_getTableName('catalog_product_entity') . " AS `e`
		LEFT JOIN " . $this->_getTableName('cataloginventory_stock_item') . " AS `at_qty` ON (at_qty.`product_id`= e.entity_id)
		INNER JOIN " . $this->_getTableName('catalog_product_entity_int') . " AS `at_manualproduct` 
		ON (`at_manualproduct`.`entity_id` = `e`.`entity_id`) AND(`at_manualproduct`.`attribute_id` = '2205') AND (`at_manualproduct`.`value` <> 0) WHERE (`e`.`entity_id` = '".$productId."')";
				
		$allProducts = $connection->fetchAll($sqlManualProduct); 
		
		$isManualProductArray = array();
		if(sizeof($allProducts) > 0) {
			
			foreach($allProducts as $data){
				$isManualProductArray['manualproduct'] = $data['manualproduct'];
				$isManualProductArray['qty'] = $data['qty'];
			}
			return $isManualProductArray;
		}else { return $isManualProductArray; }
		
	}

	public function prepareRequest($forStock = 0, $forNewSku = 0) {
		
		$request = array();
		$skuArray = array();
		$entityArray = array();

		$connection = $this->_getConnection('core_read');
		
		if($forNewSku){
			$sql = "SELECT e.sku, e.entity_id, dt.value as deliverytime FROM " . $this->_getTableName('catalog_product_entity') . " AS e LEFT JOIN " . $this->_getTableName('catalog_product_entity_varchar') . " AS dt ON (dt.entity_id = e.entity_id) AND (dt.attribute_id = (SELECT attribute_id FROM " . $this->_getTableName('eav_attribute') . " AS ea LEFT JOIN " . $this->_getTableName('eav_entity_type') . " AS et ON ea.entity_type_id = et.entity_type_id WHERE ea.attribute_code = '" . Helios_Deliverytime_Helper_Data::ATTRIBUTE_CODE . "' AND et.entity_type_code = 'catalog_product')) AND dt.store_id = 0 WHERE e.sku <> '' AND dt.value = '' ";

		} else {
			$sql = "SELECT entity_id, sku FROM " . $this->_getTableName('catalog_product_entity') . " WHERE type_id = '" . Helios_Deliverytime_Helper_Data::PRODUCT_TYPE . "' and sku <> '' ORDER BY sku";
		}

		try{

			$allProducts = $connection->fetchAll($sql);

			if(!is_array($allProducts)){
				return '';
			}

			foreach ($allProducts as $product) {
				$skuArray [] = $product['sku'];
				$entityArray [] = $product['entity_id'];
			}

			if($forStock == 1){

				$chunkSKU = array_chunk($skuArray, Helios_Deliverytime_Helper_Data::STOCK_API_BREAKPOINT);
				$chunkEntities = array_chunk($entityArray, Helios_Deliverytime_Helper_Data::STOCK_API_BREAKPOINT);
				
				for ($i=0; $i < count($chunkSKU); $i++) { 

					$tempRequest = array();

					$tempRequest ['bedrijfnr'] = '1';
					$tempRequest ['filiaalnr'] = '1';
					$tempRequest ['artikelen'] = $chunkSKU[$i];
					$tempRequest ['entities'] = $chunkEntities[$i];

					array_push($request, $tempRequest);
				}

				Mage::log("Request Prepared.", NULL, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);
				return $request;
			}

			$request['bedrijfnr'] = '1';
			$request['filiaalnr'] = '1';
			$request['artikelen'] = $skuArray;
			$request['entities'] = $entityArray;

			Mage::log("Request Prepared.", NULL, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);
			
			return $request;

		} catch (Exception $e){
			echo $e->getMessage();
			Mage::log("Something wrong with SQL", NULL, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);
			Mage::log($e->getMessage(), NULL, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);
			Mage::log(print_r($sql, true), NULL, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);
		}
	}

	public function importDeliveryTime($allEntities, $deliveryTime){
		
		$connection     = $this->_getConnection('core_write');
		$attributeDetails = $this->_getEAVTable();
		$storeId = 0;

		$sql = "INSERT INTO " . $this->_getTableName($attributeDetails['table_name']) . " (entity_type_id, attribute_id, store_id, entity_id,value) VALUES "; 

		Mage::log("Import to database process started.", NULL, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);

		try{

			for ($i=0; $i < count($allEntities); $i++) { 

				if($deliveryTime[$i] <= Helios_Deliverytime_Helper_Data::MIN_DELIVERYTIME_CONDITION){
					$deliveryTime[$i] = Helios_Deliverytime_Helper_Data::MIN_DELIVERYTIME;
				}

				$updateArray [] = "(". $attributeDetails['entity_type_id'] . "," . $attributeDetails['attribute_id'] . "," . $storeId . "," . $allEntities[$i] . "," . $deliveryTime[$i] . ")";

			}
			
			$sql .= implode(",", $updateArray) . " ON DUPLICATE KEY UPDATE value = VALUES(value)";

			$result = $connection->query($sql);

			Mage::log("Affected Raws : " . $result->rowCount(), NULL, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);

		} catch (Exception $e) {
			Mage::log($e->getMessage(), NULL, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);
			Mage::log(print_r($updateArray, true), NULL, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);
		}

		unset($connection);
		unset($attributeDetails);
		unset($updateArray);
		unset($allEntities);
		unset($deliveryTime);

		Mage::log("Import to database process end.", NULL, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);

		return 1;
	}

	public function importStock($allEntities, $stocks){
		
		$connection     = $this->_getConnection('core_write');
		// In stock Always to allow Backorders.
		$isInStock = $stockStatus = 1;

		$sql = "INSERT INTO " . $this->_getTableName('cataloginventory_stock_mavis') . " (product_id,qty,is_in_stock,stock_status) VALUES ";

		try{

			for ($i=0; $i < count($allEntities); $i++) { 
			
				$isManualProduct = $this->_getManualProductQty($allEntities[$i]);	
				if(sizeof($isManualProduct) > 0){
					$stocks[$i] = $isManualProduct['qty'];
				}
				
				$updateArray [] = "(" . $allEntities[$i] . "," . $stocks[$i] . "," . $isInStock . "," . $stockStatus . ")";

			}
			
			$sql .= implode(",", $updateArray) . " ON DUPLICATE KEY UPDATE qty = VALUES(qty), is_in_stock = VALUES(is_in_stock), stock_status = VALUES(stock_status)";
			
			Mage::log($sql, NULL, Helios_Deliverytime_Helper_Data::LOG_SQL_FILE_NAME);

			$result = $connection->query($sql);
			Mage::log("Affected Raws : " . $result->rowCount(), NULL, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);

		} catch (Exception $e) {
			Mage::log($e->getMessage(), NULL, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);
			Mage::log(print_r($updateArray, true), NULL, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);
		}

		unset($connection);
		unset($updateArray);
		unset($allEntities);
		unset($stocks);

		Mage::log("Import to database process end.", NULL, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);

		return 1;
	}


	public function reindexCatalogInventory(){

		Mage::log("Catalog Inventory Stock reindexing start.", NULL, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);
		$process = Mage::getModel('index/indexer')->getProcessByCode('cataloginventory_stock');
		$process->reindexAll();
		Mage::log("Catalog Inventory Stock reindexing done.", NULL, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);
	}


	// Prepare Rest Stock Request Starts //
	public function prepareRestRequest() {
		$connection = $this->_getConnection('core_read');
		
		$sql = "SELECT entity_id, sku FROM " . $this->_getTableName('catalog_product_entity') . " WHERE type_id = '" . Helios_Deliverytime_Helper_Data::PRODUCT_TYPE . "' and sku <> '' ORDER BY sku";
		
		$allProducts = $connection->fetchAll($sql);

		$chunkSKU = array_chunk($allProducts, 50);
		
		$all_chunk_products = array();
		foreach($chunkSKU as $chunks=>$chunkdata) {
			$product_params = "";
			$quantity_params = "";
			$chunked_product = array();
			foreach($chunkdata as $k=>$v) {
				$product_params .= "ProductIds=".$v['sku']."&";
        		$quantity_params .= "Quantities=1&";
        		$chunked_product[$v['sku']]['entity_id'] =  $v['entity_id'];
			}


			$all_chunk_products[$chunks]['mavis_request_url'] = $product_params.rtrim($quantity_params,"&");
			$all_chunk_products[$chunks]['chunked_products_data'] = $chunked_product;			

		}
		return $all_chunk_products;
	}
	// Prepare Rest Stock Request Ends //


	// Import stocks from mavis rest api starts
	public function importRestStocks($mavis_stock_data,$chunked_products_data) {
		
		$connection = $this->_getConnection('core_write');
		
		$isInStock = $stockStatus = 1;

		$sql = "INSERT INTO " . $this->_getTableName('cataloginventory_stock_mavis_rest') . " (product_id,product_sku,qty,is_in_stock,stock_status,type) VALUES ";
		
		try{
			$updateArray = array();

			foreach($chunked_products_data as $chunked_product_sku=>$chunked_product_id) {

				$chunked_product_id = $chunked_product_id["entity_id"];
				
				$get_mavis_stock_data_array_key = array_search($chunked_product_sku, array_column($mavis_stock_data, 'productId'));

				//If product sku exist in mavis rest response
				if($get_mavis_stock_data_array_key !== false ) {
					$response_product_sku = $mavis_stock_data[$get_mavis_stock_data_array_key]->productId;
					$response_product_stock = $mavis_stock_data[$get_mavis_stock_data_array_key]->stock;
			$updateArray [] = "(" . $chunked_product_id . ",'" . $response_product_sku . "'," . $response_product_stock . "," . $isInStock . "," . $stockStatus . ",'mavis')";
				} else {
					// If product is not in mavis response so it is manual
					$isManualProduct = $this->_getManualProductQty($chunked_product_id);
					if(sizeof($isManualProduct) > 0){		
						$updateArray [] = "(" . $chunked_product_id . ",'".$chunked_product_sku."'," . $isManualProduct['qty'] . "," . $isInStock . "," . $stockStatus . ",'manual')";
					} else {
						// All other products which are not mavis and manual
						$updateArray [] = "(" . $chunked_product_id . ",'".$chunked_product_sku."',0," . $isInStock . "," . $stockStatus . ",'other')";
					}
				}
				
			}

			$sql .= implode(",", $updateArray) . " ON DUPLICATE KEY UPDATE qty = VALUES(qty), is_in_stock = VALUES(is_in_stock), stock_status = VALUES(stock_status)";

			$result = $connection->query($sql);

			//Mage::log("Affected Raws : " . $result->rowCount(), NULL, Helios_Deliverytime_Helper_Data::REST_STOCK_LOG_FILE_NAME);

			unset($connection);
		
		} catch (Exception $e) {
			
			Mage::log($e->getMessage(), NULL, Helios_Deliverytime_Helper_Data::REST_STOCK_LOG_FILE_NAME);
			
			Mage::log(print_r($updateArray, true), NULL, Helios_Deliverytime_Helper_Data::REST_STOCK_LOG_FILE_NAME);
		}

	}
	// Import stocks from mavis rest api ends

	// Update stocks from mavis rest api starts
	public function updateRestStocks($products_updated) {
		

	  $connection = $this->_getConnection('core_write');
		
	  $isInStock = $stockStatus = 1;

	  $sql = "INSERT INTO " . $this->_getTableName('cataloginventory_stock_mavis_rest') . " (product_id,product_sku,qty,is_in_stock,stock_status,type,updated_at) VALUES ";


		if(count($products_updated)) {
			
			try {

			$updateArray = array();

			$updated_product_skus = array();

			foreach($products_updated as $product) {
				$product_sku_from_mavis_response = $product->productId;
				$magentoproductId = Mage::getModel('catalog/product')->getIdBySku($product_sku_from_mavis_response);
				if(!empty($magentoproductId)) { 
				$updated_product_skus[] = $product_sku_from_mavis_response;
				$updateArray [] = "(" . $magentoproductId . ",'".$product_sku_from_mavis_response."',".$product->availability."," . $isInStock . "," . $stockStatus . ",'mavis','".date("Y-m-d H:i:s")."')";
				}
			}
			
			$sql .= implode(",", $updateArray) . " ON DUPLICATE KEY UPDATE qty = VALUES(qty), is_in_stock = VALUES(is_in_stock), stock_status = VALUES(stock_status), updated_at = '".date("Y-m-d H:i:s")."'";

			//echo $sql;	

			$result = $connection->query($sql);

			//Mage::log("Affected Rows : " . $connection->exec($sql), NULL, Helios_Deliverytime_Helper_Data::REST_STOCK_LOG_FILE_NAME_15);
			
			unset($connection);

			return $updated_product_skus;

			} catch (Exception $e) {
			
				Mage::log($e->getMessage(), NULL, Helios_Deliverytime_Helper_Data::REST_STOCK_LOG_FILE_NAME_15);
			
				Mage::log(print_r($updateArray, true), NULL, Helios_Deliverytime_Helper_Data::REST_STOCK_LOG_FILE_NAME_15);
			}	

		}
	}
	// Update stocks from mavis rest api ends


	// Import Rest Delivery Time Starts
	public function importRestDeliveryTime($product_delivery_data){
		
		$connection     = $this->_getConnection('core_write');
		$attributeDetails = $this->_getEAVTable();
		$storeId = 0;

		$sql = "INSERT INTO " . $this->_getTableName($attributeDetails['table_name']) . " (entity_type_id, attribute_id, store_id, entity_id,value) VALUES ";


		if(count($product_delivery_data)) {
			
			try {

			$updateArray = array();

			$updated_product_skus = array();

			foreach($product_delivery_data as $product) {
				$product_sku_from_mavis_response = $product->productId;
				$magentoproductId = Mage::getModel('catalog/product')->getIdBySku($product_sku_from_mavis_response);
				if(!empty($magentoproductId)) { 


				$updated_product_skus[] = $product_sku_from_mavis_response;
				
				$product_deliverydays = $product->deliveryDays;

				if($product_deliverydays <= Helios_Deliverytime_Helper_Data::MIN_DELIVERYTIME_CONDITION){
					$product_deliverydays = Helios_Deliverytime_Helper_Data::MIN_DELIVERYTIME;
				}
				
	$updateArray [] = "(". $attributeDetails['entity_type_id'] . "," . $attributeDetails['attribute_id'] . "," . $storeId . "," . $magentoproductId . "," . $product_deliverydays . ")";

				}
			}
			
			$sql .= implode(",", $updateArray) . " ON DUPLICATE KEY UPDATE value = VALUES(value)";

			//echo $sql;	

			$result = $connection->query($sql);

			//Mage::log("Affected Rows : " . $connection->exec($sql), NULL, Helios_Deliverytime_Helper_Data::REST_STOCK_LOG_FILE_NAME_15);
			
			unset($connection);

			return $updated_product_skus;

			} catch (Exception $e) {
			
				Mage::log($e->getMessage(), NULL, Helios_Deliverytime_Helper_Data::REST_DELIVERY_TIME_LOG_FILE_NAME);
			
				Mage::log(print_r($updateArray, true), NULL, Helios_Deliverytime_Helper_Data::REST_DELIVERY_TIME_LOG_FILE_NAME);
			}	

		}

		/*

		Mage::log("Import to database process started.", NULL, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);

		try{

			for ($i=0; $i < count($allEntities); $i++) { 

				if($deliveryTime[$i] <= Helios_Deliverytime_Helper_Data::MIN_DELIVERYTIME_CONDITION){
					$deliveryTime[$i] = Helios_Deliverytime_Helper_Data::MIN_DELIVERYTIME;
				}

				$updateArray [] = "(". $attributeDetails['entity_type_id'] . "," . $attributeDetails['attribute_id'] . "," . $storeId . "," . $allEntities[$i] . "," . $deliveryTime[$i] . ")";

			}
			
			$sql .= implode(",", $updateArray) . " ON DUPLICATE KEY UPDATE value = VALUES(value)";

			$result = $connection->query($sql);

			Mage::log("Affected Raws : " . $result->rowCount(), NULL, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);

		} catch (Exception $e) {
			Mage::log($e->getMessage(), NULL, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);
			Mage::log(print_r($updateArray, true), NULL, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);
		}

		unset($connection);
		unset($attributeDetails);
		unset($updateArray);
		unset($allEntities);
		unset($deliveryTime);

		Mage::log("Import to database process end.", NULL, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);

		return 1; */
	}
	// Import Rest Delivery Time Ends




}
	 