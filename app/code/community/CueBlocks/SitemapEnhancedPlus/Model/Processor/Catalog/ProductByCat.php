<?php

/**
 * Description of SitemapEnhancedPlus
 * @package   CueBlocks_SitemapEnhancedPlus
 * @company    CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhancedPlus_Model_Processor_Catalog_ProductByCat extends CueBlocks_SitemapEnhancedPlus_Model_Processor_Catalog_Product
{
    protected $_configKey_out = 'prod_out';
    protected $_counterLabel = 'Prod. Cat. Paths';
    protected $_isSepProcess = true;

    protected $_firstFile = FALSE;

    public function isEnabled()
    {
        // Always active if we are generating separate category
        return TRUE;
    }

    public function setCounter($counter)
    {
        $counter->setLabel($this->_counterLabel);
//        $this->_prodCatCounter = $counter;
        $this->_counter = $counter;

        return $this;
    }

    public function setImageCounter($counter)
    {
        $this->_imageCounter = $counter;
        $this->_imageCounter->setLabel('Prod. Images');
        $this->_imageCounter->setExclude(true);

        return $this;
    }

    /**
     * Add filter for attribute code
     * @param bool $usePagination
     * @return Varien_Object
     */
    public function _getQueryModelConfig($usePagination = false)
    {
        $config = parent::_getQueryModelConfig($usePagination);
        $filter_out = !Mage::helper('sitemapEnhancedPlus')->getConfig($this->_configKey_out, $this->getSitemap()->getStoreId())
            ->getEnabled();

        $extraConfigData = array(
            'filter_out_of_stock' => $filter_out,
            'group_by_attribute_code' => $this->_attributeCode,
            'filter_by_cat_id' => $this->getCatId(),
            'use_cat_path_in_url' => true,
        );
        // add data override the previous values
        $config->addData($extraConfigData);
        return $config;
    }

    public function getQueryCollection()
    {
//        $in_enabled = Mage::getStoreConfig(self::CONFIG_BASE_PATH . $this->_configKey.'/enabled', $this->getSitemap()->getStoreId());

        return $this->getQueryModel()->getCollection($this->getSitemap()->getStoreId(), false, $filter_out, $this->getSitemap()->getConfig()->getUseRootCatalog(), $this->getCatId(), true);
    }

    protected function _getUrl($row)
    {
        $url = '';

        $prodId = $row['entity_id'];
        $url = !empty($row['url']) ? $row['url'] : 'catalog/product/view/id/' . $this->getProdId() . '/category/' . $row['category_id'];

        return $url;
    }

    protected function _processRow($row, $bypass = true)
    {
        $this->setCurrentProdId($row['entity_id']);
        $this->setCurrentProdDate($this->_getDate($row));
        $this->_getExtraXml();

        // check if I have to use Category Path
        if ($this->_canProcessCatPath()) {
            $urlProcessor = $this->_getCategoryPathUrlProcessor($this->getCatId());
            $urlProcessor->setCounter($this->_counter);
            $urlProcessor->process($this->getSitemap());
        } else {

            $xml = parent::_processRow($row, $bypass);
            if ($xml) {
                $this->_addUrl($xml);
            }
        }
        return false;
    }

    protected function _postProcess()
    {
        return;
    }

    /**
     * Separate Category Mode ( check if category path are enabled )
     */
    protected function _canProcessCatPath()
    {
        if ($this->_canProcessCatPath === null) {
            $use_cat_path = Mage::getStoreConfig('catalog/seo/product_use_categories', $this->getSitemap()->getStoreId());
            $this->_canProcessCatPath = $use_cat_path;
        }
        return $this->_canProcessCatPath;
    }
}
