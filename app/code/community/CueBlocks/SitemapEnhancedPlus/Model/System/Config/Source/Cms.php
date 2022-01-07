<?php

/**
 * Description of Cms
 * @package   CueBlocks_SitemapEnhancedPlus
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhancedPlus_Model_System_Config_Source_Cms
{
    public function toOptionArray()
    {

        $collection = Mage::getResourceModel('cms/page_collection')
            ->addFieldToFilter('is_active', '1')
            ->load();

        $options = array();

        foreach ($collection as $item) {

            if ($item['identifier'] == Mage_Cms_Model_Page::NOROUTE_PAGE_ID)
                continue;

            $options[] = array(
                'label' => $item->getData('title'),
                'value' => $item->getData('page_id')
            );
        }

        return $options;
    }

}
