<?php

/**
 * Description of Collection
 * @package   CueBlocks_SitemapEnhancedPlus
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhancedPlus_Model_Mysql4_SitemapEnhancedPlusFiles_Collection extends Mage_Sitemap_Model_Mysql4_Sitemap_Collection
{

    public function _construct()
    {
        $this->_init('sitemapEnhancedPlus/sitemapEnhancedPlusFiles');
    }

    /**
     * Files Collections
     *
     * @return CueBlocks_SitemapEnhancedPlus_Model_Mysql4_SitemapEnhancedPlusFiles_Collection
     */
    public function setSitemapFilter($sitemapId)
    {
//        if (is_numeric($sitemap))
//            $sitemapId = $sitemap;
//        else
//            $sitemapId = $sitemap->getId();

        /* @var $collection CueBlocks_SitemapEnhancedPlus_Model_Mysql4_SitemapEnhancedPlusFiles_Collection */
        $this->addFieldToFilter('sitemap_id', $sitemapId)
            ->addOrder('sitemap_file_id', 'ASC');

        return $this;
    }

    public function setTypeFilter($type = null)
    {
        if ($type != null)
            $this->addFieldToFilter('sitemap_file_type', $type);

        return $this;
    }

}
