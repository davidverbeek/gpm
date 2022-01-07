<?php

/**
 * Description of Category
 * @package   CueBlocks_SitemapEnhancedPlus
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhancedPlus_Model_System_Config_Source_ProductLinkTypeBoth extends CueBlocks_SitemapEnhancedPlus_Model_System_Config_Source_ProductLinkType
{
    /*
     * Deprecated
     */
    public function toOptionArray()
    {
        $options = parent::toOptionArray();
        $options[] =
            array('value' => 'both', 'label' => 'Both');

        return $options;
    }

}
