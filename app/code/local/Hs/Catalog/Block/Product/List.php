<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Product list
 * This is the Product listing block file. the file is overwrite product collection method for 2 things
 * 1. showing grouped product first in the product listing.
 * 2. when customer having debternumber logged in show special prices instead of normal prices
 */
class Hs_Catalog_Block_Product_List extends Mage_Catalog_Block_Product_Abstract
{
    /**
     * Default toolbar block name
     *
     * @var string
     */
    protected $_defaultToolbarBlock = 'catalog/product_list_toolbar';

    /**
     * Product Collection
     *
     * @var Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected $_productCollection;

    /* CUSTOM JAAGERS */

    function get_prod_count()
    {
    	Mage::getSingleton('catalog/session')->unsLimitPage();
    	return (isset($_REQUEST['limit'])) ? intval($_REQUEST['limit']) : 8;
    }

    function get_cur_page()
    {
    	return (isset($_REQUEST['p'])) ? intval($_REQUEST['p']) : 1;
    }

    /* END CUSTOM JAAGERS */
    /**
     * Retrieve loaded category collection
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected function _getProductCollection($page = null)
    {
        if (is_null($this->_productCollection)) {
            $layer = $this->getLayer();
            /* @var $layer Mage_Catalog_Model_Layer */
            if ($this->getShowRootCategory()) {
                $this->setCategoryId(Mage::app()->getStore()->getRootCategoryId());
            }
            // if this is a product view page
            if (Mage::registry('product')) {
                // get collection of categories this product is associated with
                $categories = Mage::registry('product')->getCategoryCollection()
                    ->setPage(1, 1)
                    ->load();
                // if the product is associated with any category
                if ($categories->count()) {
                    // show products from this category
                    $this->setCategoryId(current($categories->getIterator()));
                }
            }
            $origCategory = null;
            if ($this->getCategoryId()) {
                $category = Mage::getModel('catalog/category')->load($this->getCategoryId());
                if ($category->getId()) {
                    $origCategory = $layer->getCurrentCategory();
                    $layer->setCurrentCategory($category);
                }
            }
            $this->_productCollection = $layer->getProductCollection();
            //echo "<pre>"; print_r($this->_productCollection->getData()); exit;
            /*  code to display group prpduct fist start */
            $this->_productCollection->addAttributeToSelect(array('custom_product_name'));
            $this->_productCollection->clear()->addAttributeToSort('type_id', 'ASC'); // SORT GROUPED PRODUCTS FIRST
            //$filters = Mage::helper('mana_filters')->getParams(true, array('order','price','desc','p', 'dir', 'no_cache', 'limit'));
               $filters= Mage::getBlockSingleton('catalog/layer_state')->getActiveFilters();
            /* Extremely ugly. if length of $filters > 0, then do not filter simple children of grouped products */
//echo "<pre>"; print_r($this->_productCollection->getData()); exit;
            //echo 'filters'.count($filters);
            if(count($filters) == 0) {
                    $this->_tempCollection = clone $this->_productCollection;
                    $itemsToRemove = array();
                    foreach($this->_tempCollection as $item) {
                            $grouped_product_model = Mage::getModel('catalog/product_type_grouped');
                            $groupedParentId = $grouped_product_model->getParentIdsByChild($item->getId());
                            if(isset($groupedParentId[0])) {
                                    if($this->_tempCollection->getItemById($groupedParentId[0])) {
                                            $itemsToRemove[] = $item->getId();
                                    }
                            }
                    }
//echo "<pre>"; print_r($itemsToRemove); exit;
                    if(count($itemsToRemove)) {
                            $this->_productCollection->addFieldToFilter('entity_id', array('nin' => $itemsToRemove));
                    }
            }
            else{
                $this->_tempCollection = clone $this->_productCollection;
                    $itemsToRemove = array();
                    foreach($this->_tempCollection as $item) {
                            if($item->getTypeId()=='grouped') {
                                   // if($this->_tempCollection->getItemById($groupedParentId[0])) {
                                            $itemsToRemove[] = $item->getId();
                                    //}
                            }
                    }
//echo "<pre>"; print_r($itemsToRemove); exit;
                    if(count($itemsToRemove)) {
                            $this->_productCollection->addFieldToFilter('entity_id', array('nin' => $itemsToRemove));
                    }
            }
            /*  code to display group prpduct fist end */
            $this->prepareSortableFieldsByCategory($layer->getCurrentCategory());
            if ($origCategory) {
                $layer->setCurrentCategory($origCategory);
            }
        }
        return $this->_productCollection;
    }



    /**
     * Get catalog layer model
     *
     * @return Mage_Catalog_Model_Layer
     */
    public function getLayer()
    {
        $layer = Mage::registry('current_layer');
        if ($layer) {
            return $layer;
        }
        return Mage::getSingleton('catalog/layer');
    }

    /**
     * Retrieve loaded category collection
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function getLoadedProductCollection($page = null)
    {

        return $this->_getProductCollection();



        // $config = Mage::getConfig()->getNode('default/combipac');
		// $userinfo = Mage::helper('customer')->getCustomer()->getData();
		//var_dump($userinfo);
		//var_dump($config);
        //echo $userinfo['mavis_debiteurnummer'];

		// if(isset($userinfo['mavis_debiteurnummer'])) {
			// $collection = $this->_getProductCollection($page);
		//var_dump($userinfo);
		//var_dump($config);

		// if(isset($userinfo['mavis_debiteurnummer']) || (int)$config->enable_price == 1) {

            //echo (int)$config->enable_price;
				//$cacheable = false; //Mage::helper('caching')->inCacheableRequest();
				// if(isset($userinfo['mavis_debiteurnummer']) && (int)$config->enable_price == 1) {



					// if($collection->count() > 0) {
						/*** Iterate through collection and assign prices received from Combipac ***/
                        //echo 'count:'.$collection->count();
                        // $arrSku = array();
                        // foreach($collection as $c) {
                            // $arrSku[] = $c->getData('sku');
                        // }
                        //print_r($arrSku);

						//$this->_soapCollection = clone $collection;

						// $this->_tempCollection = Mage::helper('price/data')->getDebiteurPriceMore($arrSku);

						//print_r($this->_tempCollection);

						/*** Check if ajax stock is enabled ***/
                        //echo "1"; exit;

						// if(!(int)Mage::getConfig()->getNode('default/combipac/ajax_stock')) {

							// $this->_stockCollection = Mage::helper('price/data')->getVoorraadMore($this->_soapCollection->getData());
						// }

						/*** End check ***/

						// if($this->_tempCollection) {

							// $cnt = 0; /*** Temp fix, until stockcollection object returns SKU identifier ***/

							  // foreach($this->_tempCollection as $c) {



								  // $c = (object)$c;
                                  //echo  $c->Nettpr;

								// $product = $collection->getItemByColumnValue('sku', $c->Artinr);




//								if($product) {
//									$product->load($product->getData('entity_id'));
//								} else {
//									continue;
//								}
                                  //print_r($product->getData());

								/*** if isset stockcollection, check for stock value of current product by SKU ***/

								//echo 'stock'.sizeof($this->_stockCollection);

								// if($this->_stockCollection) {
								// 	if($this->_stockCollection->Voorraad[$cnt]) {

								// 		** Temp fix, until stockcollection object returns SKU identifier **
								// 		$product->setData('combipac_stock', $this->_stockCollection->Voorraad[$cnt]->VoorHH);

								// 	}
								// }

								/*** Below example code for assigning dynamic attributes to product object. Useful for price display ***/

								//if($product->isGrouped()) {
								//	$product->setData('combipac_result', $this->_tempCollection);
								//}

								// if($c->Nettpr > 0) {
								// 	$now=date('Y-m-d', strtotime('now'));
								// 	$special_from_date= date('Y-m-d',strtotime($product->getData('special_from_date')));
								// 	$special_to_date= date('Y-m-d',strtotime($product->getData('special_to_date')));
								// 	//if($eid == '31125') {

								// 	//}
								// 	if($product->getData('price')>=$product->getData('special_price'))
								// 	{
								// 		if ((($now >= $special_from_date) && ($now <= $special_to_date)) || (($now >= $special_from_date) && $special_to_date=="1970-01-01"))
								// 		{
								// 		  $specialPrice=$product->getData('special_price');
								// 		}
								// 	}
								// 	$eid = $product->getData('entity_id');
									//if($eid == '31125') {
//										echo "<br>";
//										echo "price".$product->getData('price');
//										echo "</br>";
//                                        echo "specialprice".$product->getData('special_price');
//										echo "<br>";
//										echo "Nettpr :: ".$c->Nettpr;
//										echo "<br>";
//										echo "prijsfactor".$product->getData('prijsfactor');
//										echo "<br>";
										//echo "debternumber". $userinfo['combipac_debiteurnummer'];
										// if(isset($userinfo['mavis_debiteurnummer'])) {
										// 	$nettoprice = round(($c->Nettpr / $product->getData('prijsfactor')),5);
										// 	$brutoprice	= round(($c->Brvkpr / $product->getData('prijsfactor')),5);
										// }else{
										// 	$nettoprice = round(($c->Nettpr / $product->getData('prijsfactor')),4);
										// 	$brutoprice	= round(($c->Brvkpr / $product->getData('prijsfactor')),4);
										// }

										//echo "<br>";
										//echo "debter price :: ".$nettoprice;
										//echo "<br>";

									//}
									// $product->setData('combipac_result', $c);
									/*
									* Helios:
									* Date :8-sep-2014
									* Reason: Listing Page and detail page price not displaying Properly then we have to do this change.
									*/
								/*	if($c->Pryskd == 'Actieprijs') {
										$product->setData('discount_percentage', $c->Krt1pc);
										$product->setData('special_price', $nettoprice);
										$product->setData('price', $brutoprice);
										$product->setData('final_price', $brutoprice);
										$product->setData('minimal_price', $brutoprice);
										$product->setData('max_price', $brutoprice);
										$product->setData('min_price', $brutoprice);
									} else {*/
										// if($c->Brvkpr > 0 && $c->Nettpr > 0 && $c->Verkpr > 0) {


										// 	$product->setData('default_price', $brutoprice);
										// 	$product->setData('price', $nettoprice);
										// 	//echo 'specialPrice'.$specialPrice;
										// 	if(isset($specialPrice)){
										// 		//echo "$specialPrice".$specialPrice;
										// 		$product->setData('final_price', $specialPrice);
										// 	}else{
										// 		//echo "$nettoprice".$nettoprice;
										// 		$product->setData('final_price', $nettoprice);
										// 	}
										// 	$product->setData('minimal_price', $nettoprice);
										// 	$product->setData('max_price', $nettoprice);
										// 	$product->setData('min_price', $nettoprice);

											//if($eid == '31125') {

											//echo "<br>";
											//echo "DB Price :: ".$product->getDefaultPrice();
											//echo "<br>";
											//echo "getPrice :: ".$product->getPrice();
											//echo "<br>";
											//echo "getFinalPrice :: ".$product->getFinalPrice();
											//echo "<br>";
											//echo "=====================================";
											//echo "<br>";
											//}

										// } else {
											//echo "getMaxPrice".$product->getData('price');
											//echo "<br>";
											// $product->setData('default_price', $product->getData('price'));
										// }
									//}
								// }

								// $cnt++; /*** Temp fix, until stockcollection object returns SKU identifier ***/

							// }

						// }

						/*** End iteration ***/
					// }

				// }

		    	/*** Read Ahead Cache ***/

		    	/*if((int)Mage::getConfig()->getNode('default/combipac/enable_read_ahead_cache') && !$page) {

		    		for($i=0;$i < $readAheadCount;$i++) {
		    			$this->getLoadedProductCollection($i, ($readAheadCount-1));
		    			echo $i;exit;
		    		}

		    	}*/

    	// return $collection;

		// }else{
			//echo "U R HERE";
			// return $this->_getProductCollection();
		// }
    }

    /**
     * Retrieve current view mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->getChild('toolbar')->getCurrentMode();
    }

    /**
     * Need use as _prepareLayout - but problem in declaring collection from
     * another block (was problem with search result)
     */
    protected function _beforeToHtml()
    {
        $toolbar = $this->getToolbarBlock();

        // called prepare sortable parameters
        $collection = $this->_getProductCollection();

        // use sortable parameters
        if ($orders = $this->getAvailableOrders()) {
            $toolbar->setAvailableOrders($orders);
        }
        if ($sort = $this->getSortBy()) {
            $toolbar->setDefaultOrder($sort);
        }
        if ($dir = $this->getDefaultDirection()) {
            $toolbar->setDefaultDirection($dir);
        }
        if ($modes = $this->getModes()) {
            $toolbar->setModes($modes);
        }

        // set collection to toolbar and apply sort
        $toolbar->setCollection($collection);

        $this->setChild('toolbar', $toolbar);
        Mage::dispatchEvent('catalog_block_product_list_collection', array(
            'collection' => $this->_getProductCollection()
        ));

        $this->_getProductCollection()->load();
        Mage::getModel('review/review')->appendSummary($this->_getProductCollection());
        return parent::_beforeToHtml();
    }

    /**
     * Retrieve Toolbar block
     *
     * @return Mage_Catalog_Block_Product_List_Toolbar
     */
    public function getToolbarBlock()
    {
        if ($blockName = $this->getToolbarBlockName()) {
            if ($block = $this->getLayout()->getBlock($blockName)) {
                return $block;
            }
        }
        $block = $this->getLayout()->createBlock($this->_defaultToolbarBlock, microtime());
        return $block;
    }

    /**
     * Retrieve additional blocks html
     *
     * @return string
     */
    public function getAdditionalHtml()
    {
        return $this->getChildHtml('additional');
    }

    /**
     * Retrieve list toolbar HTML
     *
     * @return string
     */
    public function getToolbarHtml()
    {
        return $this->getChildHtml('toolbar');
    }

    public function setCollection($collection)
    {
        $this->_productCollection = $collection;
        return $this;
    }

    public function addAttribute($code)
    {
        $this->_getProductCollection()->addAttributeToSelect($code);
        return $this;
    }

    public function getPriceBlockTemplate()
    {
        return $this->_getData('price_block_template');
    }

    /**
     * Retrieve Catalog Config object
     *
     * @return Mage_Catalog_Model_Config
     */
    protected function _getConfig()
    {
        return Mage::getSingleton('catalog/config');
    }

    /**
     * Prepare Sort By fields from Category Data
     *
     * @param Mage_Catalog_Model_Category $category
     * @return Mage_Catalog_Block_Product_List
     */
    public function prepareSortableFieldsByCategory($category) {
        if (!$this->getAvailableOrders()) {
            $this->setAvailableOrders($category->getAvailableSortByOptions());
        }
        $availableOrders = $this->getAvailableOrders();
        if (!$this->getSortBy()) {
            if ($categorySortBy = $category->getDefaultSortBy()) {
                if (!$availableOrders) {
                    $availableOrders = $this->_getConfig()->getAttributeUsedForSortByArray();
                }
                if (isset($availableOrders[$categorySortBy])) {
                    $this->setSortBy($categorySortBy);
                }
            }
        }

        return $this;
    }

    /**
     * Retrieve block cache tags based on product collection
     *
     * @return array
     */
    public function getCacheTags()
    {
        return array_merge(
            parent::getCacheTags(),
            $this->getItemsTags($this->_getProductCollection())
        );
    }
}
