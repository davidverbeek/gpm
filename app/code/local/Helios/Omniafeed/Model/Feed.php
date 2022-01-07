<?php

/**
 * Class Helios_Omniafeed_Model_Feed
 *
 * @copyright   Copyright (c) 2017 Helios
 */
class Helios_Omniafeed_Model_Feed extends Mage_Core_Model_Abstract
{
	// global variables
	private $_helper = null;
	private $_notSoldCount = 0;
	private $_lowCount = 0;
	private $_midCount = 0;
	private $_highCount = 0;
	private $_totalSalesYearlyData = null;
	private $_flagIncludeCurrentNodeToFeed = false;
	private $_flagIncludeCurrentNodeToHighFeed = false;
	private $_currentProductAverageSalesQty = 0;

	public function _construct()
	{
		parent::_construct();
		$this->_helper = Mage::helper('omniafeed');
		$this->_totalSalesYearlyData = $this->_helper->getTotalSalesYearlyData();
	}

	/**
	 * Generate xml feed and place on specified folder
	 */
	public function collectFeedData() {
		try {
			$objXmlFeed = new DOMDocument('1.0', 'UTF-8');
			$objXmlFeed->formatOutput = true;
			$root = $objXmlFeed->createElement('root');
			$products = $objXmlFeed->createElement('products');

			// Initialise potential code nodes value
			$this->_notSoldCount = 0;
			$this->_lowCount = 0;
			$this->_midCount = 0;
			$this->_highCount = 0;

			$_totalSalesData = $this->_helper->getTotalSalesData();
			$totalSalesQty = array_sum(array_column($_totalSalesData->getData(), 'sales_since_start'));

			// $totalSalesQty = $_totalSalesData->getFirstItem()->getData('sales_since_start');

			// All products Sales
			$objAttrNode = $objXmlFeed->createElement('sales_since_start');
			$objAttrNode->appendChild(
				$objXmlFeed->createCDATASection(
					round($totalSalesQty, Helios_Omniafeed_Helper_Data::DECIMAL)
                   // Zend_Locale_Format::getNumber((string)$totalSalesQty,
                   //     array(
                   //         'locale' => Helios_Omniafeed_Helper_Data::FEED_LOCALE,
                   //         'precision' => Helios_Omniafeed_Helper_Data::DECIMAL
                   //     ))
				)
			);
			$root->appendChild($objAttrNode);

			// Total Sales Data Yearly
			$this->_totalSalesYearlyData = $this->_helper->getTotalSalesYearlyData();
			$totalSalesQtyYearly = array_sum(array_column($this->_totalSalesYearlyData->getData(), 'actual_sales_year'));
			$totalSalesQtyYearly;

			$soldSkuInYear = $this->_totalSalesYearlyData->count();

			// Average sales per product in QTY
			$averageSales = ($soldSkuInYear) ? ($totalSalesQtyYearly / $soldSkuInYear) : 0;

			$objAttrNode = $objXmlFeed->createElement('total_yearly_sales');
			$objAttrNode->appendChild(
				$objXmlFeed->createCDATASection(
					round($totalSalesQtyYearly, Helios_Omniafeed_Helper_Data::DECIMAL)
                   // Zend_Locale_Format::getNumber((string)$totalSalesQtyYearly,
                   //     array(
                   //         'locale' => Helios_Omniafeed_Helper_Data::FEED_LOCALE,
                   //         'precision' => Helios_Omniafeed_Helper_Data::DECIMAL
                   //     ))
				)
			);
			$root->appendChild($objAttrNode);

			$objAttrNode = $objXmlFeed->createElement('total_average_sales_per_product');
			$objAttrNode->appendChild(
				$objXmlFeed->createCDATASection(
					round($averageSales, Helios_Omniafeed_Helper_Data::DECIMAL)
                   // Zend_Locale_Format::getNumber((string)$averageSales,
                   //     array(
                   //         'locale' => Helios_Omniafeed_Helper_Data::FEED_LOCALE,
                   //         'precision' => Helios_Omniafeed_Helper_Data::DECIMAL
                   //     ))
				)
			);
			$root->appendChild($objAttrNode);

			Mage::log("Root node generated successfully.", null, Helios_Omniafeed_Helper_Data::LOG_FILE_NAME);

			$_productCollection = $this->_helper->getProductCollection();

			foreach ($_productCollection as $_product) {

				// initiliaze loop variables
				$lifetime_sales = $yearlySales = 0;

				// Create xml First Product Node
				$objProductNode = $objXmlFeed->createElement('product');

				// SKU
				$objAttrNode = $objXmlFeed->createElement('sku');
				$objAttrNode->appendChild($objXmlFeed->createCDATASection($_product->getSku()));
				$objProductNode->appendChild($objAttrNode);

				// EAN
				$objAttrNode = $objXmlFeed->createElement('eancode');
				$objAttrNode->appendChild($objXmlFeed->createCDATASection($_product->getEancode()));
				$objProductNode->appendChild($objAttrNode);

				// check Ideal package value and calculate qty
				if ($_product->getAfwijkenidealeverpakking()) {
					$lifetime_sales = $_product->getTotalSoldQty() / $_product->getIdealeverpakking();
					$yearlySales = $_product->getSoldQtyLastYear() / $_product->getIdealeverpakking();
				} else {
					$lifetime_sales = $_product->getTotalSoldQty();
					$yearlySales = $_product->getSoldQtyLastYear();
				}

				// Lifetime Sales Qty
				$objAttrNode = $objXmlFeed->createElement('lifetime_sales');
				$objAttrNode->appendChild($objXmlFeed->createCDATASection($lifetime_sales));
				$objProductNode->appendChild($objAttrNode);

				// Yearly Sales Qty
				//				$yearlySales = $_product->getSoldQtyLastYear();
				$objAttrNode = $objXmlFeed->createElement('yearly_sales');
				$objAttrNode->appendChild($objXmlFeed->createCDATASection($yearlySales));
				$objProductNode->appendChild($objAttrNode);

				// Product Yearly sales vs total average
				$yearlySalesVsAverageYearlySales = $yearlySales - $averageSales;

				$objAttrNode = $objXmlFeed->createElement('yearly_sales_vs_average_yearly_sales');
				$objAttrNode->appendChild(
					$objXmlFeed->createCDATASection(
						round($yearlySalesVsAverageYearlySales, Helios_Omniafeed_Helper_Data::DECIMAL)
                       // Zend_Locale_Format::getNumber((string)$yearlySalesVsAverageYearlySales,
                       //     array(
                       //         'locale' => Helios_Omniafeed_Helper_Data::FEED_LOCALE,
                       //         'precision' => Helios_Omniafeed_Helper_Data::DECIMAL
                       //     ))
					)
				);
				$objProductNode->appendChild($objAttrNode);

				// Product Potential
				$potential = 0;

				if ($yearlySales > 0) {
					$potential = floor(($yearlySales * 100) / $averageSales);
				}
				$potentialCode = $this->getPotentialCode($potential);

				$objAttrNode = $objXmlFeed->createElement('potential_count');
				$objAttrNode->appendChild($objXmlFeed->createCDATASection($potential));
				$objProductNode->appendChild($objAttrNode);

				$objAttrNode = $objXmlFeed->createElement('potential');
				$objAttrNode->appendChild($objXmlFeed->createCDATASection($potentialCode));
				$objProductNode->appendChild($objAttrNode);

				// Apddend current product node to Products node
				$products->appendChild($objProductNode);

			}

			// Apddend current product node to Products node
			$root->appendChild($products);

			// potentialcode wise count nodes add to root node
			// Not Sold
			$objAttrNode = $objXmlFeed->createElement('not_sold');
			$objAttrNode->appendChild($objXmlFeed->createCDATASection($this->_notSoldCount));
			$root->appendChild($objAttrNode);

			// Low
			$objAttrNode = $objXmlFeed->createElement('low');
			$objAttrNode->appendChild($objXmlFeed->createCDATASection($this->_lowCount));
			$root->appendChild($objAttrNode);

			// Mid
			$objAttrNode = $objXmlFeed->createElement('mid');
			$objAttrNode->appendChild($objXmlFeed->createCDATASection($this->_midCount));
			$root->appendChild($objAttrNode);

			// High
			$objAttrNode = $objXmlFeed->createElement('high');
			$objAttrNode->appendChild($objXmlFeed->createCDATASection($this->_highCount));
			$root->appendChild($objAttrNode);

			Mage::log(
				"Total " . $_productCollection->count() . " products node generated successfully.",
				null,
				Helios_Omniafeed_Helper_Data::LOG_FILE_NAME
			);

			$objXmlFeed->appendChild($root);
			$strXmlPath = $this->_helper->_baseDirectory() . Helios_Omniafeed_Helper_Data::OMNIA_FEED_FILE_NAME;
			$objXmlFeed->save($strXmlPath);

			Mage::log("Feed saved at " . $strXmlPath, null, Helios_Omniafeed_Helper_Data::LOG_FILE_NAME);

		} catch (Exception $e) {
			Mage::log($e->getMessage(), null, Helios_Omniafeed_Helper_Data::LOG_FILE_NAME);
		}
	}

	/**
	 * gets potential code from potential count
	 *
	 * @param $potential int
	 *
	 * @return string
	 */
	private function getPotentialCode($potential) {
		if ($potential > 0 && $potential <= 50) {
			$this->_lowCount++;
			return Helios_Omniafeed_Helper_Data::PRODUCT_LOW;
		}
		if ($potential > 50 && $potential <= 100) {
			$this->_midCount++;
			return Helios_Omniafeed_Helper_Data::PRODUCT_MID;
		}
		if ($potential > 100) {
			$this->_highCount++;
			return Helios_Omniafeed_Helper_Data::PRODUCT_HIGH;
		}

		$this->_notSoldCount++;
		return Helios_Omniafeed_Helper_Data::PRODUCT_NOT_SOLD;
	}

	/**
	 * Get google feed data and generate google_feed.xml at root
	 *
	 * @return string generated_feed_file_path
	 */
	public function prepareFeed() {
		$productCollection = $this->_helper->getProductCollectionGoogleFeed();
		try { // Save completed google feed

			$objXmlFeed = new DOMDocument('1.0', 'UTF-8');
			$objXmlFeed->formatOutput = true;
			$rootNode = $objXmlFeed->createElement('root');
			$productsNode = $objXmlFeed->createElement('products');

			// High Potential Feed Only - as per requirement no need parent nodes.
			$objXmlHighFeed = new DOMDocument('1.0', 'UTF-8');
			$objXmlHighFeed->formatOutput = true;
			$rootHighNode = $objXmlHighFeed->createElement('root');

			// Adchieve Feed - Minimum data require for Adchieve
			$objXmlAdchieveFeed = new DOMDocument('1.0', 'UTF-8');
			$objXmlAdchieveFeed->formatOutput = true;
			$rootAdchieveNode = $objXmlAdchieveFeed->createElement('root');
			$productsAdchieveNode = $objXmlAdchieveFeed->createElement('products');

			foreach ($productCollection as $product) {
				// SKIP product for category -> Verbandmiddelen (Medicines) -> ID 2020
				if(in_array(2020, $product->getCategoryIds())){
					continue;
				}

                /* Stock Monitoring Starts */
                if (in_array(trim($product->getSku()),Helios_Omniafeed_Helper_Data::SKU_STOCK_ALWAYS_AVAILABLE))	{
                    if($product->getStockLevel() == "0.0000") {
                        $actual_zero_quantity_sku[$product->getSku()] = $product->getStockLevel();
                    }
                }
                /* Stock Monitoring Ends */

				$productNode = $this->prepareProductNode($objXmlFeed, $product);
				$productsNode->appendChild($productNode);
				
				// Adchieve Feed
				$productAdchieveNode = $this->prepareProductNode($objXmlAdchieveFeed, $product, 1); // 1 to exclude SLI and Specs node
				$productsAdchieveNode->appendChild($productAdchieveNode);

				$this->_flagIncludeCurrentNodeToHighFeed;
				if ($this->_flagIncludeCurrentNodeToHighFeed) {
					$productNode = $this->prepareProductNode($objXmlHighFeed, $product);
					$rootHighNode->appendChild($productNode);
				}

			}

			$rootNode->appendChild($productsNode);
			$objXmlFeed->appendChild($rootNode);
			$fileLocation = $this->_helper->_baseDirectory() . Helios_Omniafeed_Helper_Data::GOOGLE_FEED_FILE_NAME;
			$objXmlFeed->save($fileLocation);

            /* Stock Monitoring Starts */
            $this->_helper->MonitorGoogleFeedStocks(Helios_Omniafeed_Helper_Data::SKU_STOCK_ALWAYS_AVAILABLE,$actual_zero_quantity_sku);
            /* Stock Monitoring Ends */


			$objXmlHighFeed->appendChild($rootHighNode);
			$fileLocationForHigh = $this->_helper->_baseDirectory() . Helios_Omniafeed_Helper_Data::GOOGLE_FEED_HIGH_FILE_NAME;
			$objXmlHighFeed->save($fileLocationForHigh);

			// Adchieve Feed
			$rootAdchieveNode->appendChild($productsAdchieveNode);
			$objXmlAdchieveFeed->appendChild($rootAdchieveNode);
			$fileLocationForAdchieve = $this->_helper->_baseDirectory() . Helios_Omniafeed_Helper_Data::ADCHIEVE_FEED_FILE_NAME;
			$objXmlAdchieveFeed->save($fileLocationForAdchieve);

			return $fileLocation;

		} catch (Exception $e) {
			Mage::log($e->getTraceAsString(), null, Helios_Omniafeed_Helper_Data::LOG_FILE_NAME);
		}
	}

	/**
	 * Get current feed object and productEntity object.
	 * Prepare node with all attributes and return same.
	 *
	 * @param $objXmlFeed object DOMDocument
	 * @param $_product object Mage_Catalog_Product_Entity
	 *
	 * @return object DOMDocument
	 */
	private function prepareProductNode($objXmlFeed, $_product, $adchieve = 0) {

		$productNode = $objXmlFeed->createElement('product');

		// Prepare price related Nodes of the feed
		// if (!$this->_flagIncludeCurrentNodeToFeed) {
		//    return $productNode;
		// }

		if($adchieve){
			$attributeNode = $objXmlFeed->createElement('sku');
			$attributeNode->appendChild($objXmlFeed->createCDATASection($_product->getSku()));
			$productNode->appendChild($attributeNode);
			unset($attributeNode);
			$productNode = $this->getSpecsRelatedNode($objXmlFeed, $productNode, $_product, 1);
			return $productNode;
		}

		$productNode = $this->getPotentialRelatedNode($objXmlFeed, $productNode, $_product);

		$attributeNode = $objXmlFeed->createElement('variant_id');
		$attributeNode->appendChild($objXmlFeed->createCDATASection($_product->getSku()));
		$productNode->appendChild($attributeNode);
		unset($attributeNode);

		$attributeNode = $objXmlFeed->createElement('is_salable');
		$attributeNode->appendChild($objXmlFeed->createCDATASection($_product->getIsSalable()));
		$productNode->appendChild($attributeNode);
		unset($attributeNode);

		$attributeNode = $objXmlFeed->createElement('stock_level');
		$attributeNode->appendChild($objXmlFeed->createCDATASection($_product->getStockLevel()));
		$productNode->appendChild($attributeNode);
		unset($attributeNode);

		$merkValue = $_product->getAttributeText('merk');
		if ($merkValue == 'Onbekend') {
			$merkValue = 'Gyzs.nl';
		}
		$attributeNode = $objXmlFeed->createElement('merk');
		$attributeNode->appendChild($objXmlFeed->createCDATASection($merkValue));
		$productNode->appendChild($attributeNode);
		unset($attributeNode);

		$attributeNode = $objXmlFeed->createElement('merk_id');
		$value = $_product->getMerk();
		if($value  == 'Onbekend') {
			$value = 'Gyzs.nl';
		}
		$attributeNode->appendChild($objXmlFeed->createCDATASection($value));
		$productNode->appendChild($attributeNode);
		unset($attributeNode);

		// if custom product name available then use it first.
		$productName = ($_product->getCustomProductName())?$_product->getCustomProductName():$_product->getName();
		$attributeNode = $objXmlFeed->createElement('name');
		$attributeNode->appendChild($objXmlFeed->createCDATASection($productName));
		$productNode->appendChild($attributeNode);
		unset($attributeNode);


		// if EANCODE not available replace with verpakkingsEAN
		$eanCode = $_product->getEancode()?$_product->getEancode():$_product->getVerpakkingsean_();
		$leverancierartikelnr = (strlen($_product->getLeverancierartikelnr()) <= 2)?$eanCode:$_product->getLeverancierartikelnr();

		$attributeNode = $objXmlFeed->createElement('leverancierartikelnr');
		$attributeNode->appendChild($objXmlFeed->createCDATASection($leverancierartikelnr));
		$productNode->appendChild($attributeNode);
		unset($attributeNode);

		$attributeNode = $objXmlFeed->createElement('eancode');
		if(!$eanCode){
			Mage::log("EAN Missing for : " . $_product->getSku(), null, Helios_Omniafeed_Helper_Data::LOG_FILE_NAME);
		}
		$attributeNode->appendChild($objXmlFeed->createCDATASection($eanCode));
		$productNode->appendChild($attributeNode);
		unset($attributeNode);

		$attributeNode = $objXmlFeed->createElement('sku');
		$attributeNode->appendChild($objXmlFeed->createCDATASection($_product->getSku()));
		$productNode->appendChild($attributeNode);
		unset($attributeNode);

		$attributeNode = $objXmlFeed->createElement('google_category');
		$attributeNode->appendChild($objXmlFeed->createCDATASection(''));
		$productNode->appendChild($attributeNode);
		unset($attributeNode);

		// Expiry Date Set on next month for product
		$attributeNode = $objXmlFeed->createElement('expiration_date');
		$attributeNode->appendChild($objXmlFeed->createCDATASection(date('Y-m-d',strtotime('+1 Month'))));
		$productNode->appendChild($attributeNode);
		unset($attributeNode);

		// Prepare price related Nodes of the feed
		$productNode = $this->getPriceRelatedNodes($objXmlFeed, $productNode, $_product);
		// if (!$this->_flagIncludeCurrentNodeToFeed) {
		//     return $productNode;
		// }

		// Prepare category related Nodes of the feed
		$productNode = $this->getCategoryRelatedNodes($objXmlFeed, $productNode, $_product);

		$productImageUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . "catalog" . DS . "product" . DS . 'placeholder' . DS . 'default' . DS . 'image.jpg';
		if (!empty($_product->getImage()) && $_product->getImage() != 'no_selection') {
			$productImageUrl = Mage::helper('catalog/image')->init($_product, 'image')->resize(500, 500);
			// $productImageUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . "catalog" . DS . "product" . $_product->getImage();
		}
		$attributeNode = $objXmlFeed->createElement('image_url');
		$attributeNode->appendChild($objXmlFeed->createCDATASection($productImageUrl));
		$productNode->appendChild($attributeNode);
		unset($attributeNode);

		$attributeNode = $objXmlFeed->createElement('prijsfactor');
		$attributeNode->appendChild($objXmlFeed->createCDATASection($_product->getPrijsfactor()));
		$productNode->appendChild($attributeNode);
		unset($attributeNode);

		$attributeNode = $objXmlFeed->createElement('verkoopeenheid');
		$attributeNode->appendChild($objXmlFeed->createCDATASection($_product->getVerkoopeenheid()));
		$productNode->appendChild($attributeNode);
		unset($attributeNode);

		$attributeNode = $objXmlFeed->createElement('idealeverpakking');
		$attributeNode->appendChild($objXmlFeed->createCDATASection($_product->getIdealeverpakking()));
		$productNode->appendChild($attributeNode);
		unset($attributeNode);

		$attributeNode = $objXmlFeed->createElement('afwijkenidealeverpakking');
		$attributeNode->appendChild($objXmlFeed->createCDATASection($_product->getAfwijkenidealeverpakking()));
		$productNode->appendChild($attributeNode);
		unset($attributeNode);

		if(!$adchieve){
			$productNode = $this->getSpecsRelatedNode($objXmlFeed, $productNode, $_product);
		}
		return $productNode;
	}

	/**
	 * Get productNode object prepare potential related nodes, append to productNode and return same
	 *
	 * @param $objXmlFeed object DOMDocument
	 * @param $productNode object DOMDocument
	 * @param $_product object Mage_Catalog_Product_Model
	 *
	 * @return $productNode object DOMDocument
	 */
	private function getSpecsRelatedNode($objXmlFeed, $productNode, $_product, $adchieve = 0) {
		$categoryIds = $_product->getCategoryIds();
		Mage::log("Resource Cat Fetch for : " . $_product->getSku(), null, Helios_Omniafeed_Helper_Data::LOG_FILE_NAME);
		$sliAttributes = trim(Mage::getResourceModel('catalog/category')
			->getAttributeRawValue($categoryIds[count($categoryIds)-1], "custom_filters", 0));
		if(!empty($sliAttributes )){
			$sliAttributes =explode(',',$sliAttributes);
			if(count($sliAttributes) > 0 ){
				// $_product->load($_product->getId());
				$sliSpecsNode = $objXmlFeed->createElement('sli_specs');
				$specsNode = $objXmlFeed->createElement('specs');
				foreach ($sliAttributes as $sliAttribute){
					
					// Below code checks the product attribute exist in webshop or not, script throws error if attribute not exist
					$attr = Mage::getResourceModel('catalog/eav_attribute')->loadByCode('catalog_product',$sliAttribute);
					if(null===$attr->getId()) 
						continue;

					$data = $_product->getAttributeText($sliAttribute);
					if(!empty($data)){
						$new = $sliSpecsNode->appendChild($objXmlFeed->createElement($sliAttribute));
						//create the name attribute and set name
						$name = $new->appendChild($objXmlFeed->createElement("name"));
						$name->appendChild($objXmlFeed->createCDATASection(trim($sliAttribute)));

						//create the value attribute and set value
						$value = $new->appendChild($objXmlFeed->createElement("value"));
						$value->appendChild($objXmlFeed->createCDATASection($data));

						$specsInternalNode = $objXmlFeed->createElement($sliAttribute);
						$specsInternalNode->appendChild($objXmlFeed->createCDATASection($data));

						$specsNode->appendChild($specsInternalNode);
						$sliSpecsNode->appendChild($new);
					}
				}
				if($adchieve){
					$productNode->appendChild($sliSpecsNode);
				} else {
					$productNode->appendChild($specsNode);
				}
			}
		}

		return $productNode;
	}



	/**
	 * Get productNode object prepare potential related nodes, append to productNode and return same
	 *
	 * @param $objXmlFeed object DOMDocument
	 * @param $productNode object DOMDocument
	 * @param $_product object Mage_Catalog_Product_Model
	 *
	 * @return $productNode object DOMDocument
	 */
	private function getPotentialRelatedNode($objXmlFeed, $productNode, $_product) {
		
		// Product Potential Calculation Starts
		// $potential = 0;
		$this->_flagIncludeCurrentNodeToFeed = true;
		// $this->_currentProductAverageSalesQty = 0;

		// Total Sales Data Yearly
		// if (empty($this->_totalSalesYearlyData)) {
		//     $this->_totalSalesYearlyData = $this->_helper->getTotalSalesYearlyData();
		// }
		// $totalSalesQtyYearly = array_sum(array_column($this->_totalSalesYearlyData->getData(), 'actual_sales_year'));
		// $soldSkuInYear = $this->_totalSalesYearlyData->count();

		// Average sales per product in QTY
		// $this->_currentProductAverageSalesQty = ($soldSkuInYear) ? ($totalSalesQtyYearly / $soldSkuInYear) : 0;

		// check Ideal package value and calculate qty
		// $idealeverpakking = !empty($_product->getIdealeverpakking()) ? $_product->getIdealeverpakking() : 1;
		// $lifetimeSales = $_product->getTotalSoldQty() ? $_product->getTotalSoldQty() : 0;
		// $yearlySales = $_product->getSoldQtyLastYear() ? $_product->getSoldQtyLastYear() : 0;
		// if ($_product->getAfwijkenidealeverpakking()) {
			// $lifetimeSales = $_product->getTotalSoldQty() / $idealeverpakking;
			// $yearlySales = $_product->getSoldQtyLastYear() / $idealeverpakking;
		// }

		// if ($yearlySales > 0) {
			// $potential = floor(($yearlySales * 100) / $this->_currentProductAverageSalesQty);
		// }
		// $potentialCode = $this->getPotentialCode($potential);

		// don't include LOW potential products to feed, set flag to false
       // if ($potentialCode == Helios_Omniafeed_Helper_Data::PRODUCT_LOW) {
       //     $this->_flagIncludeCurrentNodeToFeed = false;
       //     return $productNode;
       // }

		// don't include NOT_SOLD potential products to feed, set flag to false
       // if ($potentialCode == Helios_Omniafeed_Helper_Data::PRODUCT_NOT_SOLD) {
       //     $this->_flagIncludeCurrentNodeToFeed = false;
       //     return $productNode;
       // }

		// potential products to separate HIGH feed, set flag to true
		if ($_product->getPotential() == Helios_Omniafeed_Helper_Data::PRODUCT_HIGH) {
		  $this->_flagIncludeCurrentNodeToHighFeed = true;
		} else {
		  $this->_flagIncludeCurrentNodeToHighFeed = false;            
		}

		$lifetimeSales = round($_product->getStockSold(), Helios_Omniafeed_Helper_Data::DECIMAL);
		$attributeNode = $objXmlFeed->createElement('stock_sold');
		$attributeNode->appendChild($objXmlFeed->createCDATASection($lifetimeSales));
		$productNode->appendChild($attributeNode);
		unset($attributeNode);

		$yearlySales = round($_product->getYearlySales(), Helios_Omniafeed_Helper_Data::DECIMAL);
		$attributeNode = $objXmlFeed->createElement('yearly_sales');
		$attributeNode->appendChild($objXmlFeed->createCDATASection($yearlySales));
		$productNode->appendChild($attributeNode);
		unset($attributeNode);

		$attributeNode = $objXmlFeed->createElement('potential');
		$attributeNode->appendChild($objXmlFeed->createCDATASection($_product->getPotential()));
		$productNode->appendChild($attributeNode);
		unset($attributeNode);

		return $productNode;
	}

	/**
	 * Get productNode object prepare price related nodes, append to productNode and return same
	 *
	 * @param $objXmlFeed object DOMDocument
	 * @param $productNode object DOMDocument
	 * @param $_product object Mage_Catalog_Product_Model
	 *
	 * @return $productNode object DOMDocument
	 */
	private function getPriceRelatedNodes($objXmlFeed, $productNode, $_product)
	{

		$prijsfactor = Mage::helper('featured')->getPrijsfactorValue($_product);
		$dblEndPriceExclVat = $_product->getPrice() * $prijsfactor;
		$attributeNode = $objXmlFeed->createElement('end_price_excl_vat');
		$attributeNode->appendChild($objXmlFeed->createCDATASection(round($dblEndPriceExclVat, 2)));
		$productNode->appendChild($attributeNode);
		unset($attributeNode);

		// TODO VAT choosen 21% fixed as per general feed, need to make it dynamic
		$priceInclVat = $_product->getPrice() + (($_product->getPrice() * Helios_Omniafeed_Helper_Data::VAT_AMMOUNT) / 100);
		$priceInclVat = $priceInclVat * (float)$prijsfactor;
		$priceInclVat = round($priceInclVat, Helios_Omniafeed_Helper_Data::DECIMAL);
//        $priceInclVat = Zend_Locale_Format::getNumber((string)$priceInclVat,
//            array(
//                'locale' => Helios_Omniafeed_Helper_Data::FEED_LOCALE,
//                'precision' => Helios_Omniafeed_Helper_Data::DECIMAL
//            )
//        );
		$attributeNode = $objXmlFeed->createElement('price_incl');
		$attributeNode->appendChild($objXmlFeed->createCDATASection($priceInclVat));
		$productNode->appendChild($attributeNode);
		unset($attributeNode);

		// TODO VAT choosen 21% fixed as per general feed, need to make it dynamic
		$costInclVat = $_product->getCost() + (($_product->getCost() * Helios_Omniafeed_Helper_Data::VAT_AMMOUNT) / 100);
		$costInclVat = $costInclVat * (float)$prijsfactor;
		$costInclVat = round($costInclVat, Helios_Omniafeed_Helper_Data::DECIMAL);
//        $costInclVat = Zend_Locale_Format::getNumber((string)$costInclVat,
//            array(
//                'locale' => Helios_Omniafeed_Helper_Data::FEED_LOCALE,
//                'precision' => Helios_Omniafeed_Helper_Data::DECIMAL
//            )
//        );
		$attributeNode = $objXmlFeed->createElement('cost_price_incl');
		$attributeNode->appendChild($objXmlFeed->createCDATASection($costInclVat));
		$productNode->appendChild($attributeNode);
		unset($attributeNode);

		$marginIncl = $priceInclVat - $costInclVat;

		// don't include negative margin products to feed, set flag to false
//        if ($marginIncl < 0) {
//            $this->_flagIncludeCurrentNodeToFeed = false;
//            return $productNode;
//        }

		// don't include average_qty * margin less than € 5 products to feed, set flag to false
//        if ($marginIncl * $this->_currentProductAverageSalesQty < 5) {
//            $this->_flagIncludeCurrentNodeToFeed = false;
//            return $productNode;
//        }

		$marginIncl = round($marginIncl, Helios_Omniafeed_Helper_Data::DECIMAL);
//        $marginIncl = Zend_Locale_Format::getNumber((string)$marginIncl,
//            array(
//                'locale' => Helios_Omniafeed_Helper_Data::FEED_LOCALE,
//                'precision' => Helios_Omniafeed_Helper_Data::DECIMAL
//            )
//        );

		$marginIncl = $this->_getMarginInclRound($marginIncl);
//        if (!$this->_flagIncludeCurrentNodeToFeed) {
//            return $productNode;
//        }
		$attributeNode = $objXmlFeed->createElement('margin_incl');
		$attributeNode->appendChild($objXmlFeed->createCDATASection($marginIncl));
		$productNode->appendChild($attributeNode);
		unset($attributeNode);

		$roasTargetPercent = $priceInclVat / $costInclVat;
		if(empty($roasTargetPercent)) {
			$roasTargetPercent = 0;
		}
		$roasTargetPercent = round($roasTargetPercent, Helios_Omniafeed_Helper_Data::DECIMAL);
//        $roasTargetPercent = Zend_Locale_Format::getNumber((string)$roasTargetPercent,
//            array(
//                'locale' => Helios_Omniafeed_Helper_Data::FEED_LOCALE,
//                'precision' => Helios_Omniafeed_Helper_Data::DECIMAL
//            )
//        );
		$roasTargetPercent = $this->_getRoasTargetPercentRound($roasTargetPercent);
//        if (!$this->_flagIncludeCurrentNodeToFeed) {
//            return $productNode;
//        }
		$attributeNode = $objXmlFeed->createElement('roas_target_percent');
		$attributeNode->appendChild($objXmlFeed->createCDATASection($roasTargetPercent));
		$productNode->appendChild($attributeNode);
		unset($attributeNode);

		// Same as porto calculation of general feed
		$shippingCostsIncl = 0;
		if ($priceInclVat < 100) {
			$shippingCostsIncl = 6.75;
		}
		$shippingCostsIncl = round($shippingCostsIncl, Helios_Omniafeed_Helper_Data::DECIMAL);
//        $shippingCostsIncl = Zend_Locale_Format::getNumber((string)$shippingCostsIncl,
//            array(
//                'locale' => Helios_Omniafeed_Helper_Data::FEED_LOCALE,
//                'precision' => Helios_Omniafeed_Helper_Data::DECIMAL
//            )
//        );
		$attributeNode = $objXmlFeed->createElement('shipping_costs_incl');
		$attributeNode->appendChild($objXmlFeed->createCDATASection($shippingCostsIncl));
		$productNode->appendChild($attributeNode);
		unset($attributeNode);

		return $productNode;
	}

	/**
	 * Get productNode object prepare category related nodes, append to productNode and return same
	 *
	 * @param $objXmlFeed object DOMDocument
	 * @param $productNode object DOMDocument
	 * @param $_product object Mage_Catalog_Product_Model
	 *
	 * @return $productNode object DOMDocument
	 */
	private function getCategoryRelatedNodes($objXmlFeed, $productNode, $_product)
	{
		$categories = $_product->getCategoryCollection()
			->addAttributeToSelect(array('name', 'custom_filters', 'url_path'));

		$listCategoryNames = array();
		$listCategoryUrls = array();
		$customFilters = null;
		$endCategoryUrlPath = null;
		$endCategory = null;

		foreach ($categories as $category) {

			// TODO need to implement method to get root_category_id
			// if (Mage::app()->getStore()->getRootCategoryId() == $category->getId()) {
			if ($category->getId() == '2') { // Default Category Id
				continue; // It's a root category no need to include it in feed.
			}

			if ($category->getCustomFilters()) {
				$customFilters = $category->getCustomFilters();
			}
			$listCategoryNames[$category->getLevel()] = $category->getName();
			$listCategoryUrls[$category->getLevel()] = $category->getUrlPath();

			$level = (int)$category->getLevel() - 1;

			$attributeNode = $objXmlFeed->createElement('category' . $level);
			$attributeNode->appendChild($objXmlFeed->createCDATASection($category->getName()));
			$productNode->appendChild($attributeNode);
			unset($attributeNode);
		}

		// sort categories by level
		ksort($listCategoryNames);
		$attributeNode = $objXmlFeed->createElement('listCategory');
		$attributeNode->appendChild($objXmlFeed->createCDATASection(join(' > ', $listCategoryNames)));
		$productNode->appendChild($attributeNode);
		unset($attributeNode);

		// fillup category3 if empty category2 if empty category1
		$endCategory = $listCategoryNames[max(array_keys($listCategoryNames))];
		$attributeNode = $objXmlFeed->createElement('end_category');
		$attributeNode->appendChild($objXmlFeed->createCDATASection($endCategory));
		$productNode->appendChild($attributeNode);
		unset($attributeNode);

		// get last category url_path and append product_url path to get full category path of the product
		// $endCategoryUrlPath = $listCategoryUrls[max(array_keys($listCategoryUrls))];
		$endCategoryUrlPath = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . $_product->getUrlPath();
		$attributeNode = $objXmlFeed->createElement('url_path');
		$attributeNode->appendChild($objXmlFeed->createCDATASection($endCategoryUrlPath));
		$productNode->appendChild($attributeNode);
		unset($attributeNode);

		$customFilters = explode(',', $customFilters);
		$strBeslistOmschrijving = $this->getBeslistOmschrijving($_product, $customFilters);
		$attributeNode = $objXmlFeed->createElement('beslist_omschrijving');
		$attributeNode->appendChild($objXmlFeed->createCDATASection($strBeslistOmschrijving));
		$productNode->appendChild($attributeNode);
		unset($attributeNode);

		return $productNode;
	}

	/**
	 * Get attribute data and format string for node beslist_omschrijving
	 *
	 * @param $product object
	 * @param $customFilters array
	 *
	 * @return string
	 */
	private function getBeslistOmschrijving($product, $customFilters)
	{
		$strBeslistOmschrijving = null;
		if (!empty($customFilters)) {
			try {
				// load additional custom attributes
				$_product = Mage::getModel('catalog/product')
					->getCollection()
					->addAttributeToSelect($customFilters)
					->addFieldToFilter('sku', $product->getSku())
					->getFirstItem();

				// Get custom attributes frontend labels
				$_attributes = Mage::getModel('eav/entity_attribute')->getCollection()
					->addFieldToSelect(array('frontend_label', 'attribute_code'))
					->addFieldToFilter('attribute_code', array('in', $customFilters));

				$_attributeValue = null;
				foreach ($_attributes as $_attribute) {
					$_attributeValue = $_product->getAttributeText($_attribute->getAttributeCode());
					if ($_attributeValue) {
						$strBeslistOmschrijving .= '| ' . $_attribute->getFrontendLabel()
							. ': ' . $_attributeValue . ' ';
					}
				}
			} catch (Exception $e) {
				Mage::log('Google feed error occured on custom_attributes fetch', null, Helios_Omniafeed_Helper_Data::LOG_FILE_NAME);
				Mage::log($e->getMessage(), null, Helios_Omniafeed_Helper_Data::LOG_FILE_NAME);
			}
		}

		$strBeslistOmschrijving = $product->getName() . ' bij GYZS.nl '
			. '| Afname Per: ' . $product->getIdealeverpakking() . ' ' . $product->getVerkoopeenheid()
			. $strBeslistOmschrijving . ' | Korte beschrijving: '
			. Mage::helper('core')->stripTags($product->getShortDescription());

		return $strBeslistOmschrijving;
	}

	/**
	 * Round up float values as per below rules
	 * Range <= 0 => not in feed
	 * Range 0.1 - 2.5 => in 0.1 steps ( 0.1 , 0.2 etc.)
	 * 2.41 = 2.40
	 * 2.42 = 2.40
	 * 2.46 = 2.50
	 * Range 2.5 - 25 => in 0.5 steps (2.5 , 3.0 etc.)
	 * 2.85 = 3.00
	 * 3.22 = 3.00
	 * 3.65 = 3.50
	 * 3.30 = 3.50
	 * Range 25 - 250 => in 1 steps (25 , 26 etc.)
	 * 25.2 = 25
	 * 25.5 = 26
	 * 26 = 26
	 * 35 = 35
	 * Range > 250 => in 5 steps (255, 260 etc.)
	 * 251 = 250
	 * 253 = 255
	 * 256 = 255
	 * 258 = 260
	 *
	 * @param $value float
	 *
	 * @return float
	 */
	public function _getMarginInclRound($value)
	{
		// Range <= 0 => not in feed
		if ($value <= 0) {
//            $this->_flagIncludeCurrentNodeToFeed = false;
			return $value;
		}

		/**
		 * Range 0.1 - 2.5 => in 0.1 steps ( 0.1 , 0.2 etc.)
		 * 2.41 = 2.40
		 * 2.42 = 2.40
		 * 2.46 = 2.50
		 */
		if ($value > 0.1 && $value < 2.5) {
			return round($value, 1, PHP_ROUND_HALF_ODD);
		}

		/**
		 * Range 2.5 - 25 => in 0.5 steps (2.5 , 3.0 etc.)
		 * 2.85 = 3.00
		 * 3.22 = 3.00
		 * 3.65 = 3.50
		 * 3.30 = 3.50
		 */
		if ($value > 2.5 && $value < 25) {
			return round($value * 2) / 2;
		}

		/**
		 * Range 25 - 250 => in 1 steps (25 , 26 etc.)
		 * 25.2 = 25
		 * 25.5 = 26
		 * 26 = 26
		 * 35 = 35
		 */
		if ($value > 25 && $value < 250) {
			return round($value, 0, PHP_ROUND_HALF_ODD);
		}

		/**
		 * Range > 250 => in 5 steps (255, 260 etc.)
		 * 251 = 250
		 * 253 = 255
		 * 256 = 255
		 * 258 = 260
		 */
		if ($value > 250) {
			return round($value / 5) * 5;
		}

		return $value;
	}

	/**
	 * Round up float values as per below rules
	 * Range < 1.0 = not in feed
	 * Range 1.0 - 25 = in 0.1 steps (0.1 , 0.2 etc.)
	 * Range > 25 = in 1 steps (25 , 26 etc.)
	 * Limit is 900.0
	 *
	 * @param $value float
	 *
	 * @return float
	 */
	public function _getRoasTargetPercentRound($value)
	{
		// Range < 1.0 = not in feed
		if ($value < 1) {
//            $this->_flagIncludeCurrentNodeToFeed = false;
			return $value;
		}

		/**
		 * Range 1.0 - 25 = in 0.1 steps (0.1 , 0.2 etc.)
		 * 2.41 = 2.40
		 * 2.42 = 2.40
		 * 2.46 = 2.50
		 */
		if ($value > 1 && $value < 25) {
			return round($value, 1, PHP_ROUND_HALF_ODD);
		}

		/**
		 * Range > 25 = in 1 steps (25 , 26 etc.)
		 * 25.2 = 25
		 * 25.5 = 26
		 * 26 = 26
		 * 35 = 35
		 */
		if ($value > 25) {
			return round($value, 0, PHP_ROUND_HALF_ODD);
		}

		return $value;
	}
}
