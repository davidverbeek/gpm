<?php

/**
 * Description of SitemapEnhancedPlusFiles
 * @package   CueBlocks_
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhancedPlus_Model_Mysql4_SitemapEnhancedPlusFiles extends Mage_Core_Model_Mysql4_Abstract
{

    public function _construct()
    {
        $this->_init('sitemapEnhancedPlus/sitemapEnhancedPlusFiles', 'sitemap_file_id');
    }

}
