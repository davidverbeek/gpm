<?php

class Jaagers_Magmi_Model_Api_V2 extends Mage_Api_Model_Resource_Abstract {

		public function magmiupdate($productsData) {
			$date = date("dmY");
			$magmiJsonLog = 'magmiActualJson_' . $date . '.log';
			$magmiLog = 'magmi_log_' . $date . '.log';
			$magmiOkLog = 'magmi_ok_' . $date . '.log';
			$magmiFailLog = 'magmi_fail_' . $date . '.log';
			$magmiFailJsonLog = 'magmi_fail_json_' . $date . '.log';

			Mage::log($productsData, null, $magmiJsonLog);

			require_once(Mage::getBaseDir() . "/magmi/inc/magmi_defs.php");
			require_once(Mage::getBaseDir() . "/magmi/integration/inc/magmi_datapump.php");
			$mode = ($productsData->mode == 'update') ? 'update' : 'create';

			Mage::log("1. Magmi Mode" . $mode, null, $magmiLog);
			
			$invalidChar = array("\<", "\r\n", "\?");
			$validChar   = array("<", "", "");

			$productsJsonData = str_replace($invalidChar, $validChar, $productsData->products);

			/* get productdata */
			$productsData = json_decode($productsJsonData, true);

			if (count($productsData)) {
				$actiesProducts = $this->getActiesProducts();
				/* Instantiate Magmi */
				$dp = Magmi_DataPumpFactory::getDataPumpInstance("productimport");

				/* Select Magmi profile */
				$dp->beginImportSession("MAVIS Product Import", $mode);

				$productsfailedData = array();
				/* Loop through products and ingest */
				foreach ($productsData as $product) {
					if(array_search($product['sku'],$actiesProducts)){
						$product['category_ids'] = $product['category_ids'] . ';;2,3098';
					}

					/* Fetch storecodes based on storeids */

					if (strlen($product['store'])) {
						if (preg_match('/[0-9]+(,[0-9]+)*/', $product['store'])) {
							// Check whether $product['store'] == numerical, comma-separated string
							$stores = explode(',', $product['store']);
							foreach ($stores as $store) {
								$store_codes[] = Mage::app()->getStore($store)->getCode();
							}
							if (count($store_codes)) {
								$product['store'] = implode(',', $store_codes);
							}
						}
					}

					if (isset($product['sku'])) {

						// PP++ 20180518, Merk Attribute set
						// Basic Details Merk Attribute (attribute code : merk)
						if (isset($product['merk']) && !empty($product['merk'])) {
								if (!isset($product['merk']) || empty($product['merk_'])) {
										$product['merk_'] = $product['merk'];
								}
						}

						// Custom Attribute Merk Attribute (attribute code : merk_)
						if (!isset($product['merk'])) {
								if (isset($product['merk_']) || !empty($product['merk_'])) {
										$product['merk'] = $product['merk_'];
								}
						}

						// Media Gallery Reset option OFF - 0:OFF. 1:ON
						if (!isset($product['media_gallery_reset'])) {
							$product['media_gallery_reset'] = 0;
						}
						
						// unset meta information tags 
						unset($product['internetmemo2']);
						unset($product['meta_description']);
						unset($product['meta_keyword']);
						unset($product['meta_title']);

						Mage::log("2. Ingest will start with Sku : " . $product['sku'], null, $magmiLog);

						/* Ingest */
						$res = $dp->ingest($product);

						Mage::log("3. Result from ingest : " . print_r($res, true), null, $magmiLog);

						$productId = Mage::getModel('catalog/product')->getIdBySku($product['sku']);


						if ($res["ok"] === true) {

							/*************** code to generate Redirect URLs For Product ********/
							$this->reindexProductDataBySku($product['sku'],$productId);
							/*************** code to generate Redirect URLs For Product ********/

							// SJD++ 30072018, flush cache of the product on successful import
							Mage::app()->cleanCache('catalog_product_' . $productId);

							// change response if product update or create send product id, if product delete than send sku with true.
							$pid['success'][$product['sku']] = ($productId) ? $productId : $res["ok"];

							// $pid['success'][$product['sku']] = $res["_product_id"];
							Mage::Log($product['sku'], null, $magmiOkLog);
							Mage::Log("4. Magmi update Ok " . $productId, null, $magmiLog);

						} else {
								$pid['fail'][$product['sku']] = $productId;
								// $pid['fail'][$product['sku']] = $res["_product_id"];
								
								//Retrieve Json Data for failed products
								$productsfailedData[] = $product;
								
								Mage::Log($product['sku'], null, $magmiFailLog);
								Mage::Log("5. Magmi update fail :" . $productId, null, $magmiLog);
						}
					} else {

						$pid['invalid'][] = $product;
						// Mage::Log($product['sku'], null, 'magmi_logcheck.log');
						Mage::Log("6. Invalid SKU : " . print_r($product, true), null, $magmiLog);
					}
				}
				
				Mage::Log(json_encode($productsfailedData, true), null, $magmiFailJsonLog);

				/* End session and return touched product ID's */
				$dp->endImportSession();

				Mage::Log("7. successfully updated " . print_r($pid, true), null, $magmiLog);
				return json_encode($pid);
			} else {
					/* No products found */
					Mage::Log("8. No products found productsData is empty ", null, $magmiLog);
					return false;
			}
		}


		/**
		 * Name: reindexProductDataBySku
		 * Function: This function is used to generate and reindex product url based on product sku.
		 * @param $sku
		 */
		public function reindexProductDataBySku($sku, $productId, $storeIds = array(0, 3)) {

			$date = date("dmY");
			$reindex_success_log = 'magmi_reindex_success_' . $date . '.log';
			$index_error_log = 'magmi_reindex_error_' . $date . '.log';

			/* @var $urlModel Mage_Catalog_Model_Url */
			$urlModel = Mage::getSingleton('catalog/url');
			//set permanant redirect for the old URLS
			$urlModel->setShouldSaveRewritesHistory(1);

			if (empty($sku)) {
				Mage::log("sku is not set...", null, $index_error_log);
				return;
			}
			if (empty($productId)) {
				Mage::log("Product not found for sku " . $sku, null, $index_error_log);
				return;
			}

			try {

				$needToProceed = false;
				$logArray = array();

				//save url key for eac store
				foreach ($storeIds as $storeId) {

					/** @var  $_product Mage_Catalog_Model_Product */
					$_product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($productId);

					$urlKey = $_product->getUrlKey();

					$urlKeyByName = $_product->formatUrlKey($_product->getName());

					$logArray[] = "oldUrl :: " . $urlKey;
					$logArray[] = "NewURL :: " . $urlKeyByName;
					$logArray[] = "Product ID :: " . $productId;
					$logArray[] = "Sku :: " . $sku;

					if ($urlKey != $urlKeyByName) {
							$_product->setUrlKey($urlKeyByName);
							$_product->setData('url_key_create_redirect', true);
							$_product->setForceReindexRequired(true);

							// added this line to maping url rewrite and force reindex product
							$_product->save();
							$needToProceed = true;

							$logArray[] = "Changed :: " . date("Y-m-d H:i:s");

					} else {
							$logArray[] = "Not- Changed :: " . date("Y-m-d H:i:s");
					}
				}

				if ($needToProceed) {
					$urlModel->refreshProductRewrite($productId);

					$logArray[] = ":::Product Name is changed for Product";
					$logArray[] = "refreshProductRewrite";

					$indexer = Mage::getSingleton('index/indexer');

					if ($indexer->processEntityAction($_product, 'catalog_url', Mage_Index_Model_Event::TYPE_SAVE)) {
						$logArray[] = "IndexDone Sku :: " . $sku;
					} else {
						$logArray[] = "IndexFail Sku :: " . $sku;
					}
				}

				Mage::log($logArray, null, $reindex_success_log, true);

			} catch (Exception $e) {
				Mage::log("Product Id is-->" . $productId, null, $index_error_log);
				Mage::log("Product Sku is-->" . $sku, null, $index_error_log);
				Mage::log($e->getMessage(), null, $index_error_log);
			}
		}


		/**
		 * Name: flushMavisImportedSku
		 * Function: This function is used for Partial flushing
		 * @param $sku
		 */
		public function flushMavisImportedSku($sku)
		{
				$write = Mage::getSingleton("core/resource")->getConnection("core_write");
				$sql = "INSERT INTO mage_mavis_magmi (sku)VALUES (" . $sku . ")";

				try {
						$write->query($sql);
				} catch (Exception $e) {
						return $e->getMessage();
						Mage::log($e->getMessage(), null, 'mavissku.log');
				}
		}

		/*public function callindex( $indexesData ) {

				$validindexes = array('1', '2');

				$indexes = explode(',', $indexesData->indexes);

				foreach($indexes as $index) {

						if(in_array($index, $indexes)) {

								try {
										$process = Mage::getModel('index/process')->load($index);
										$process->reindexAll();
								} catch (Exception $e) {
										Mage::Log($e, null, 'magmi_index.log');
								}

						} else {

								return 'Invalid index specified';

						}

				}

				return true;

		}*/

		public function callindex()
		{

				try {
						if (!file_exists(Mage::getBaseDir() . '/toggle_index.flag')) {
								@unlink(Mage::getBaseDir() . '/toggle_index.done');
								file_put_contents(Mage::getBaseDir() . '/toggle_index.flag', now());
								return true;
						}
				} catch (Exception $e) {
						return $e->getMessage();
				}

				return false;

		}
		public function getActiesProducts()
		{
			$categoryModel = Mage::getModel('catalog/category')->load('3098');
			$productIds = $categoryModel->getProductCollection()->getAllIds();
			$collection = Mage::getModel('catalog/product')->getCollection()
							->addAttributeToSelect('sku')
            				->addAttributeToFilter('entity_id', array('in' => $productIds));
			$productsSku = array();
			foreach ($collection as $product) {
				$productsSku[] =$product->getSku();
			}
			return $productsSku;
		}
}