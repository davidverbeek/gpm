<?php
	require_once('app/Mage.php');
	umask(0);
	Mage::app();
	
	########################## PERFORM REINDEXING START #######################################
	
	$startReindexingTime = time();
	
 	try {

 		/*** added by Rahil *****/
 		//Run other indexing processes

		//run product attribute reindexing process
        $process = Mage::getModel('index/indexer')->getProcessByCode('catalog_product_attribute');
        $process->reindexAll();
        $message = "Catalog Product attribute reindexing done at ".date("D M d, Y G:i a");
        Mage::log($message,null, 'Table-reindexing-report.log');


        //run catlaog flat product issue
        $process = Mage::getModel('index/indexer')->getProcessByCode('catalog_product_flat');
        $process->reindexAll();
        $message = "Catalog Flat Product  reindexing done at ".date("D M d, Y G:i a");
        Mage::log($message,null, 'Table-reindexing-report.log');

        //stock update
        $process = Mage::getModel('index/indexer')->getProcessByCode('cataloginventory_stock');
        $process->reindexAll();
        $message = "Catalog Inventory Stock reindexing done at ".date("D M d, Y G:i a");
        Mage::log($message,null, 'Table-reindexing-report.log');

		//Brand Category Index
//        $process = Mage::getModel('index/indexer')->getProcessByCode('product');
//        $process->reindexAll();
//        $message = "Brand Category reindexing done at ".date("D M d, Y G:i a");
//        Mage::log($message,null, 'Table-reindexing-report.log');

        //Brand URL Reindexing
//        $process = Mage::getModel('index/indexer')->getProcessByCode('url');
//        $process->reindexAll();
//        $message = "Brand URL Reindexing done at ".date("D M d, Y G:i a");
//        Mage::log($message,null, 'Table-reindexing-report.log');

        //tag summary reindexing
        $process = Mage::getModel('index/indexer')->getProcessByCode('tag_summary');
        $process->reindexAll();
        $message = "Tag Aggregation Data Reindexing done at ".date("D M d, Y G:i a");
        Mage::log($message,null, 'Table-reindexing-report.log');

        //Default Values (MANAdev)
        $process = Mage::getModel('index/indexer')->getProcessByCode('mana_db_replicator');
        $process->reindexAll();
        $message = "Default Values (MANAdev) Reindexing done at ".date("D M d, Y G:i a");
        Mage::log($message,null, 'Table-reindexing-report.log');

		/**** added by Rahil ***/



        //Catalog URL Rewrites
		$process = Mage::getModel('index/indexer')->getProcessByCode('catalog_url');
        $process->reindexAll();
        $message = "Catalog URL Rewrites reindexing done at ".date("D M d, Y G:i a");
        Mage::log($message,null, 'Table-reindexing-report.log');
		
		//Products Prices
		$process = Mage::getModel('index/indexer')->getProcessByCode('catalog_product_price');
		$process->reindexAll();
		$message = "Products Prices reindexing done at ".date("D M d, Y G:i a");
		Mage::log($message,null, 'Table-reindexing-report.log');
 		
 		//Category Products
		$process = Mage::getModel('index/indexer')->getProcessByCode('catalog_category_product');
		$process->reindexAll();
		$message = "Category Products reindexing done at ".date("D M d, Y G:i a");
		Mage::log($message,null, 'Table-reindexing-report.log');		
		
		//Catalog Category Flat
		$process = Mage::getModel('index/indexer')->getProcessByCode('catalog_category_flat');
		$process->reindexAll();
		$message = "Catalog Category Flat reindexing done at ".date("D M d, Y G:i a");
		Mage::log($message,null, 'Table-reindexing-report.log');
		
		
		//Catalogsearch Attributes
        $process = Mage::getModel('index/indexer')->getProcessByCode('catalogsearch_fulltext');
		$process->reindexAll();
		$message = "Product catalogsearch reindexing done at ".date("D M d, Y G:i a");
		Mage::log($message,null, 'Table-reindexing-report.log');
		
		// flush cache
		Mage::app()->getCacheInstance()->flush();
		Mage::app()->cleanCache();
		$message = "Cache flushed at ".date("D M d, Y G:i a");
		Mage::log($message,null, 'Table-reindexing-report.log');

    } catch (Exception $e) {
        Mage::log("Error : $e",null, 'Table-reindexing-report.log');
    }
    
    $endReindexingTime = time();
    Mage::log("Differnece Seconds::" . ($endReindexingTime - $startReindexingTime),null, 'Table-reindexing-report.log');
 
   
    ########################## PERFORM REINDEXING END #########################################
    
    ########################## FLUSH VARNISH DATA START #######################################
/*
    $startFlushTime = time();
    
    try {
		$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
		$query      = "Select * from `mage_mavis_magmi`";
		$rows       = $connection->fetchAll($query);
		
		foreach ($rows as $values) {
		    $id 	= $values['sku_id'];
		    $sku 	= $values['sku'];

			$_catalog = Mage::getModel('catalog/product');
			$_productId = $_catalog->getIdBySku($values['sku']);
			//$_productId = $_catalog->getIdBySku('1327128');

			if(isset($_productId)) {
				Mage::helper('performance')->purgeProductContentById($_productId);
				Mage::log('Flushed Product ID ::'.$_productId,null,'varnish_flushed_id.log');
			}else{
				Mage::log('Product ID not exist ::'.$_productId,null,'varnish_flushed_id.log');
			}
		}

		$tableName = $connection->getTableName('mage_mavis_magmi');
		$connection->query("truncate table " . $tableName); 
		echo $tableName . " has been truncated";

		Mage::log('Table has been truncated',null,'varnish_flushed_id.log');
		
	} catch (Exception $e) {
		Mage::log("Error :".$e,null, 'varnish_flushed_id.log');
        
    }
    
    $endFlushTime = time();
    Mage::log("Differnece Seconds::" . ($endFlushTime - $startFlushTime),null, 'varnish_flushed_id.log');
*/
    ########################## FLUSH VARNISH DATA END #######################################
    
    ########################## COMBIPACK CACHE FLUSH START ##################################

    /*$startCombipackFlushTime = time();
    
    try {
		$resource = Mage::getSingleton('core/resource')->getConnection('core_read'); 
		$tableName = $resource->getTableName('mage_combipac_cache');
		$resource->query("truncate table " . $tableName); 
		$message = "$tableName cleared at " .date("D M d, Y G:i a");
		//Mage::log($message,null, 'TableCombipacFlush.log');
	} catch (Exception $e) {
		Mage::log("Error :".$e,null, 'TableCombipacFlush.log');
        
    }
    $endCombipackFlushTime = time();
    Mage::log("Differnece Seconds::" . ($endCombipackFlushTime - $startCombipackFlushTime),null, 'TableCombipacFlush.log');*/

	########################## COMBIPACK CACHE FLUSH END ####################################
	
	########################## UPDATE STOCK START ##################################
	
	$startUpdateStockTime = time();
	
	$resource = Mage::getSingleton('core/resource');

	$writeConnection = $resource->getConnection('core_write');
	$writeConnection->query("SET SESSION group_concat_max_len=102400000;");

	$readConnection = $resource->getConnection('core_read');

	$strSkuQuery = "
	  SELECT GROUP_CONCAT(DISTINCT sku) AS sku
	  FROM `mage_catalog_product_entity` AS `e` 
	  WHERE sku <> '';";

	$resultSet = $readConnection->fetchRow($strSkuQuery);
	unset($resource);
	unset($readConnection);

	$arrSku = explode(',', $resultSet['sku']);  
	$request = array(
	'bedrijfnr' =>  1, 
	'filiaalnr' =>  1, 
	'artikelen' =>  $arrSku
	);

	ini_set('default_socket_timeout', 1200);
	//  WS for GYZS get STOCK DATA
	$client = new SoapClient('http://62.12.1.67:3335/WebshopService.asmx?wsdl');
	$stockResponse = $client->GetVoorraadMore($request);
	$arrStock = $stockResponse->GetVoorraadMoreResult->decimal;  
	$skuStock = array_combine($arrSku, $arrStock);

	$resource = Mage::getSingleton('core/resource');
	$writeConnection = $resource->getConnection('core_write');
	
	$createTable = ""
		  . "TRUNCATE TABLE `stock_information`;"
		  . "CREATE TABLE IF NOT EXISTS `stock_information` ("
			. "`sku` varchar(64) NOT NULL COMMENT 'SKU',"
			. "`stock` varchar(5) NOT NULL,"
			. "PRIMARY KEY  (sku)"
		  . ");";
  $writeConnection->query($createTable);
  
	$insertQuery = "INSERT INTO `stock_information` (sku, stock) VALUES ";
	foreach($skuStock as $sku => $stock) {

	$insertQuery .= "('$sku', '$stock'),";
	}
	$insertQuery = rtrim($insertQuery, ",");
	
	$writeConnection->query($insertQuery);
	
	$endUpdateStockTime = time();
    Mage::log("Differnece Seconds::" . ($endUpdateStockTime - $startUpdateStockTime),null, 'TableCombipacFlush.log');
	
	########################## UPDATE STOCK END ####################################

    
?>
