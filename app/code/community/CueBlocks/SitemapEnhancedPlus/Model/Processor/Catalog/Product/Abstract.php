<?php

/**
 * Description of SitemapEnhancedPlus
 * @package   CueBlocksgetSitemap()EnhancedPlus
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */

abstract class CueBlocks_SitemapEnhancedPlus_Model_Processor_Catalog_Product_Abstract extends CueBlocks_SitemapEnhancedPlus_Model_Processor_Abstract
{

    /**
     * Process link source collection
     * NO PAGINATION NO POSTPROCESS
     */
    public function process($sitemap)
    {
        $this->setSitemap($sitemap);
        // skip the config set to avoid issue
        return $this->_getProcessCollection();
    }

    protected function _increaseLinkCounter()
    {
        if ($this->_counter)
            $this->_counter->setCount((int)$this->_counter->getCount() + 1);
    }

    public function setCounter($counter)
    {
        $this->_counter = $counter;

        return $this;
    }

    public function _getQueryModelConfig($usePagination = false)
    {
        $config = parent::_getQueryModelConfig($usePagination);

        $extraConfigData = array(
            'prod_id' => $this->getProdId(),
        );
        $config->addData($extraConfigData);
        return $config;
    }
}