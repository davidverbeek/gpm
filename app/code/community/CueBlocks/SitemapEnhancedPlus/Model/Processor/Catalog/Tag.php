<?php

/**
 * Description of SitemapEnhancedPlus
 * @package   CueBlocks_SitemapEnhancedPlus
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhancedPlus_Model_Processor_Catalog_Tag extends CueBlocks_SitemapEnhancedPlus_Model_Processor_Abstract
{
    protected $_configKey = 'prod_tag';
    protected $_sourceModel = 'sitemapEnhancedPlus/processor_catalog_tag';
    protected $_fileName = '_tag';
    protected $_counterLabel= 'Tag';

    protected function _getUrl($row)
    {
        $url = '';

        $tagId = $row['tag_id'];
        $url = 'tag/product/list/tagId/' . $tagId . '/';

        return $url;
    }
}