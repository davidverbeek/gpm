<?php

/**
 * Backend Model for Cron
 *
 * @package   CueBlocks_SitemapEnhancedPlus
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhancedPlus_Model_System_SitemapType extends Varien_Object
{

    /**
     * Retrieve store values for form
     *
     * @param bool $empty
     * @param bool $all
     * @return array
     */
    public function getValuesForForm()
    {
        $options = array(
            array(
                'label' => Mage::helper('adminhtml')->__('Regular'),
                'value' => CueBlocks_SitemapEnhancedPlus_Model_SitemapEnhancedPlus::TYPE_REGULAR
            ),
            array(
                'label' => Mage::helper('adminhtml')->__('Regular + Image (Google)'),
                'value' => CueBlocks_SitemapEnhancedPlus_Model_SitemapEnhancedPlus::TYPE_IMAGE
            ),
            array(
                'label' => Mage::helper('adminhtml')->__('Mobile (Google)'),
                'value' => CueBlocks_SitemapEnhancedPlus_Model_SitemapEnhancedPlus::TYPE_MOBILE
            )
        );

        return $options;
    }

}
