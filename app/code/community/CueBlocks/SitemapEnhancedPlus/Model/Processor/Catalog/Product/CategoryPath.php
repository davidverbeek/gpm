<?php

/**
 * Description of SitemapEnhancedPlus
 * @package   CueBlocks_SitemapEnhancedPlus
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhancedPlus_Model_Processor_Catalog_Product_CategoryPath extends CueBlocks_SitemapEnhancedPlus_Model_Processor_Catalog_Product_Abstract
{
    /**
     * first File ( we don't need a separate file here )
     *
     * @var bool
     */
    protected $_firstFile = FALSE;
    protected $_sourceModel = 'sitemapEnhancedPlus/processor_catalog_product_categoryPath';

    public function _getQueryModelConfig($usePagination = false)
    {
        $config = parent::_getQueryModelConfig($usePagination);

        $extraConfigData = array(
            'prod_id' => $this->getProdId(),
            'cat_id' => $this->getCatId(),
        );
        $config->addData($extraConfigData);
        return $config;
    }

    protected function _getUrl($row)
    {
        $url = '';
        $url = !empty($row['url']) ? $row['url'] : 'catalog/product/view/id/' . $this->getProdId() . '/category/' . $row['category_id'];

        return $url;
    }

    protected function _getDate($row)
    {
        // we force the date from the prod.
        return $this->getDate();
    }

    protected function _getExtraXml()
    {
        // we force the extraXml from the prod.
        return $this->getExtraXml();
    }
}