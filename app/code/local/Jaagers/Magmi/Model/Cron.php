<?php

class Jaagers_Magmi_Model_Cron{	
	
	public function processIndex(){
		
		if(file_exists(Mage::getBaseDir() . '/toggle_index.flag') && !file_exists(Mage::getBaseDir() . '/toggle_index.running')) {

			unlink(Mage::getBaseDir() . '/toggle_index.flag');

			file_put_contents(Mage::getBaseDir() . '/toggle_index.running', now());

			// Define indexes which should be run in array below.

			$indexers = array(
							'catalog_product_attribute',
			 				'catalog_product_price', 
							'catalog_product_flat', 
							/*'catalog_category_flat',*/ 
							'catalog_category_product',
							/*'cataloginventory_stock', 
							'catalogsearch_fulltext',
							'catalog_url',*/
						);

			foreach($indexers as $index) {
				$time_start = microtime(true);
				$result[$index]['result'] = $this->index($index);
				$time_spent = microtime(true) - $time_start;
				$result[$index]['time'] = round($time_spent,2);
			}

			unlink(Mage::getBaseDir() . '/toggle_index.running');
			
			Mage::Log($result, true, 'custom-index.log');

		}

	}

	/* Start reindex */

	function index($index) {
		
		$process = Mage::getSingleton('index/indexer')->getProcessByCode($index);
		
		try {
			$process->reindexEverything();
		} catch(Exception $e) {
			unlink(Mage::getBaseDir() . '/toggle_index.running');
			Mage::Log($e->getMessage(), true, 'custom-index-fail.log');
			return $e->getMessage();
		}

		return true;

	}

}