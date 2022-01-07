<?php

/**
 * Description of SitemapEnhancedPlus
 * @package   CueBlocks_SitemapEnhancedPlus
 * @company    CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhancedPlus_Model_Processor_Catalog_Product extends CueBlocks_SitemapEnhancedPlus_Model_Processor_Abstract
{
    const PRODUCT_LINK_CANONICAL = 'canonical';
    const PRODUCT_LINK_CATEGORY = 'category';
    const PRODUCT_LINK_BOTH = 'both';

    protected $_configKey = 'product';
    protected $_sourceModel = 'sitemapEnhancedPlus/processor_catalog_product';
    protected $_fileName = '_prod';
    protected $_counterLabel = 'Product';

    protected $_imageCounter;
    protected $_prodCatCounter;
    protected $_imgCounterLabel = 'Images';
    protected $_prodCatcounterLabel = 'Cat. Path';

    protected $_isOutOfStock = FALSE;
    protected $_filterInStock = FALSE;
    protected $_filterOutStock = TRUE;
    protected $_isSeparate = FALSE;
    protected $_canProcessCatPath = NULL;
    protected $_linkType = NULL;

    public function _getQueryModelConfig($usePagination = false)
    {
        $config = parent::_getQueryModelConfig($usePagination);

        $extraConfigData = array(
            'filter_out_of_stock' => $this->_filterOutStock,
            'filter_in_stock' => $this->_filterInStock,
            'use_root_catalog_mode' => $this->getSitemap()->getConfig()->getUseRootCatalog(),
            'visibility' => explode(',',$this->getConfig()->getVisibility())
        );
        $config->addData($extraConfigData);
        return $config;
    }

    protected function _getUrl($row)
    {
        $url = '';

        $prodId = $row['entity_id'];
        $url = !empty($row['url']) ? $row['url'] : 'catalog/product/view/id/' . $prodId;

        if (empty($row['url'])) {
            $this->getSitemap()->addWarning("Product (ID:$prodId) has no URL rewrite. Link: $url");
        }

        return $url;
    }

    protected function _getExtraXml($bypass = false)
    {
        if ($bypass)
            return parent::_getExtraXml();

        $extraXml = parent::_getExtraXml();
        $prodId = $this->getCurrentProdId();

        // Generate images for product
        if ($this->getSitemap()->getSitemapType() == CueBlocks_SitemapEnhancedPlus_Model_SitemapEnhancedPlus::TYPE_IMAGE) {
            $processor = Mage::getModel('sitemapEnhancedPlus/processor_catalog_product_image');

            $processor
				->setSitemap($this->getSitemap())
                ->setProdConfig($this->getConfig())
                ->setProdId($prodId)
                ->setCounter($this->_imageCounter)
                ->setIsOutOfStock($this->_isOutOfStock);

            $extraXml .= $processor->process($this->getSitemap());
        }
        // used for category (canonical) url
        $this->setCurrentProdExtraXml($extraXml);
        return $extraXml;
    }

    protected function _processRow($row, $bypass = false)
    {
        // used for extraxml generation
        $this->setCurrentProdId($row['entity_id']);
        $this->setCurrentProdDate($this->_getDate($row));
        $xml = parent::_processRow($row);

        if ($xml) {
            // skip Cat. Path Generation
            if ($bypass) {
                return $xml;
            }

            $link = $this->_getLinkType();
            if ($link == 'canonical' || $link == 'both') {
                $this->_addUrl($xml);
            }
            // if previous row was allowed url
            if ($this->_canProcessCatPath()) {
                if ($xml) {
                    // Generate category path links for product
                    $urlProcessor = $this->_getCategoryPathUrlProcessor();
                    $urlProcessor
                        ->setCounter($this->_prodCatCounter)
                        ->setCatId(null)
                        ->process($this->getSitemap());

                    return false;
                }
            }
            // avoid to add 2 times ( above _addUrl() call do the job)
            return false;
        }
    }

    protected function _getCategoryPathUrlProcessor($catId = null)
    {
        $processor = Mage::getModel('sitemapEnhancedPlus/processor_catalog_product_categoryPath');
        $processor
            ->setConfig($this->getConfig())
            ->setProdId($this->getCurrentProdId())
            ->setCatId($catId)
            ->setDate($this->getCurrentProdDate())
            ->setExtraXml($this->getCurrentProdExtraXml())
            ->setIsOutOfStock($this->_isOutOfStock);
        return $processor;
    }

    protected function _preProcess()
    {
        $this->_imageCounter = new Varien_Object();
        $this->_imageCounter->setCount(0);
        $this->_imageCounter->setLabel($this->_imgCounterLabel);
        $this->_imageCounter->setExclude(true);

        $this->_prodCatCounter = new Varien_Object();
        $this->_prodCatCounter->setCount(0);
        $this->_prodCatCounter->setLabel($this->_prodCatcounterLabel);

        parent::_preProcess();
    }

    protected function _postProcess()
    {
        // for separate category
        // or in case we have chosen only category path
        if (!$this->_counter) {
            $this->_initCounter();
        }

        // in case there are no product
        if ($this->_counter) {
            $extraCounter = new Varien_Data_Collection();
            $extraCounter->addItem($this->_imageCounter);
            $extraCounter->addItem($this->_prodCatCounter);

            $this->_counter->setExtraCounter($extraCounter);

        }
        parent::_postProcess();
    }

    protected function _canProcessCatPath()
    {
        if ($this->_canProcessCatPath === null) {
            // force canonical if category path are disabled and if separate category
            $use_cat_path = Mage::getStoreConfig('catalog/seo/product_use_categories', $this->getSitemap()->getStoreId());
            if ($use_cat_path) {
                $link = $this->_getLinkType();

                $this->_canProcessCatPath = ($link == 'category' || $link == 'both');
            } else {
                $this->_canProcessCatPath = false;
            }
        }
        return $this->_canProcessCatPath;
    }

    protected function _getLinkType()
    {
        if ($this->_linkType === null) {
            $this->_linkType = $this->getSitemap()->getConfig()->getProductLinkType();
        }
        return $this->_linkType;
    }

    protected function _filterRow($row)
    {
        $filterIt = parent::_filterRow($row);
        if (!$filterIt && $this->getSitemap()->getConfig()->getUseCategoryFilter()) {
            $filterHelper = Mage::helper('sitemapEnhancedPlus/categoryFilter');
            $id = $row['entity_id'];
            $filterIt = $filterHelper->filterProduct($id, $this->getSitemap());

            if ($filterIt) {
                $url = 'PROD-URL:' . htmlspecialchars($this->getSitemap()->getBaseUrl() . $this->_getUrl($row));
                $this->getSitemap()->addDisallowed($url);
            }
        }
        return $filterIt;
    }
}
