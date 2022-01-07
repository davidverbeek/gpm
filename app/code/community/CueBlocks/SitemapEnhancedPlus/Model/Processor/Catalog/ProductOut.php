<?php

/**
 * Description of SitemapEnhancedPlus
 * @package   CueBlocks_SitemapEnhancedPlus
 * @company    CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhancedPlus_Model_Processor_Catalog_ProductOut extends CueBlocks_SitemapEnhancedPlus_Model_Processor_Catalog_Product
{
    protected $_configKey = 'prod_out';
    protected $_fileName = '_prod_out';
    protected $_counterLabel = 'Prod. Out';

    protected $_imgCounterLabel = 'Prod. Out Image';
    protected $_prodCatcounterLabel = 'Prod. Out Cat. Path';

    protected $_isOutOfStock = TRUE;
    protected $_filterInStock = TRUE;
    protected $_filterOutStock = FALSE;


    public function _getQueryModelConfig($usePagination = false)
    {
        $config = parent::_getQueryModelConfig($usePagination);
        // product visibility values is from product configuration
        $config->setData('visibility', explode(',', $this->_getMainProductConfig()->getVisibility()));
        return $config;
    }

    /**
     * We need to read visibility from parent class.
     * unfortunately there is not way to access $_configKey parent value
     * so the only way is to access the product config directly
     * -> it will be better to store all sub configuration into the sitemap obj
     */
    protected function _getMainProductConfig()
    {
       return Mage::helper('sitemapEnhancedPlus')->getConfig('product', $this->getSitemap()->getStoreId());
    }

}
