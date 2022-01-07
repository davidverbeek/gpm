<?php

/**
 * Description of SitemapEnhancedPlus
 * @package   CueBlocks_SitemapEnhancedPlus
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhancedPlus_Model_Processor_Catalog_Review extends CueBlocks_SitemapEnhancedPlus_Model_Processor_Abstract
{
    protected $_configKey = 'prod_review';
    protected $_sourceModel = 'sitemapEnhancedPlus/processor_catalog_review';
    protected $_fileName = '_review';
    protected $_counterLabel = 'Review';

    protected function _getUrl($row)
    {
        $url = '';

        $prodId = $row['prod_id'];
        $url = 'review/product/list/id/' . $prodId . '/';

        return $url;
    }

    public function _getQueryModelConfig($usePagination = false)
    {
        $config = parent::_getQueryModelConfig($usePagination);

        $extraConfigData = array(
            'visibility' => explode(',', Mage::helper('sitemapEnhancedPlus')->getConfig('product', $this->getSitemap()->getStoreId())->getVisibility())
        );
        $config->addData($extraConfigData);
        return $config;
    }
}
