<?php

/**
 * Description of Category
 * @package   CueBlocks_SitemapEnhancedPlus
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhancedPlus_Model_System_Config_Source_DivideMode
{
    public function toOptionArray()
    {
        $helper = Mage::helper('sitemapEnhancedPlus');

        $options = array(
            array('value' => 0, 'label' => 'No'),
            array('value' => 3, 'label' => 'Manufacturer'),
        );

        // Disabled for EE 1.13 and Higher
        if (!$helper->isMageEE13()) {
            $options[] = array('value' => 1, 'label' => 'Top Category');
            $options[] = array('value' => 2, 'label' => 'Top & Sub Category');
        }

        return $options;
    }
}
