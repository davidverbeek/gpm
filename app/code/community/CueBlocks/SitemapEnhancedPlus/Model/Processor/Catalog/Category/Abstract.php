<?php

/**
 * Description of SitemapEnhancedPlus
 * @package   CueBlocksgetSitemap()EnhancedPlus
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */

abstract class CueBlocks_SitemapEnhancedPlus_Model_Processor_Catalog_Category_Abstract extends CueBlocks_SitemapEnhancedPlus_Model_Processor_Abstract
{
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

    public function getQueryCollection()
    {
        return $this->getQueryModel()->getCollection($this->getSitemap()->getStoreId(), $this->getCatId());
    }

    /**
     * Process link source collection
     */
    public function process($sitemap)
    {
        $this->setSitemap($sitemap);
        // skip the config set to avoid issue
        return $this->_getProcessCollection();
    }
}