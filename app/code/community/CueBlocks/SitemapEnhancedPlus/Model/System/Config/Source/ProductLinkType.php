<?php

/**
 * Description of Category
 * @package   CueBlocks_SitemapEnhancedPlus
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhancedPlus_Model_System_Config_Source_ProductLinkType
{
    public function toOptionArray()
    {
        $options = array(
            array('value' => 'canonical', 'label' => 'Canonical (recommended)'),
        );
        $helper = Mage::helper('sitemapEnhancedPlus');

        // Disabled for EE 1.13 and Higher
        if (!$helper->isMageAboveEE12()) {
            $options[] = array('value' => 'category', 'label' => 'Category Path');
            $options[] = array('value' => 'both', 'label' => 'Both');
        }

        return $options;
    }
}
