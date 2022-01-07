<?php

/**
 * Description of SitemapEnhancedPlus
 * @package   CueBlocks_SitemapEnhancedPlus
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhancedPlus_Model_Mysql4_SitemapEnhancedPlus extends Mage_Core_Model_Mysql4_Abstract
{

    public function _construct()
    {
        $this->_init('sitemapEnhancedPlus/sitemapEnhancedPlus', 'sitemap_id');
    }

}
