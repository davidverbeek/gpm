<?php

/**
 * Description of SitemapEnhancedPlus
 * @package   CueBlocks_SitemapEnhancedPlus
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhancedPlus_Model_Processor_Cms_Page extends CueBlocks_SitemapEnhancedPlus_Model_Processor_Abstract
{
    protected $_configKey = 'page';
    protected $_sourceModel = 'sitemapEnhancedPlus/processor_cms_page';
    protected $_fileName = '_cms';
    protected $_counterLabel= 'CMS';

    protected function _filterRow($row)
    {
        $exPagesId = explode(',', $this->getConfig()->getExcludedPages());

        if ($row['url'] == Mage_Cms_Model_Page::NOROUTE_PAGE_ID
            || in_array($row['page_id'], $exPagesId)
        ) {
            return TRUE;
        }

        return FALSE;
    }

    protected function _getUrl($row)
    {
        $url = '';
        $url = $row['url'];
        return $url;
    }
}