<?php

/**
 * Description of
 * @package   CueBlocks_SitemapEnhancedPlus
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhancedPlus_Block_Adminhtml_SitemapEnhancedPlus_Grid_Renderer_Type extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    /**
     * Prepare link to display in grid
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        /* @var $row CueBlocks_SitemapEnhancedPlus_Model_SitemapEnhancedPlus */

        $html = '';

        switch ($row->getSitemapType()) {
            case CueBlocks_SitemapEnhancedPlus_Model_SitemapEnhancedPlus::TYPE_REGULAR:
                $html = 'Regular';
                break;
            case CueBlocks_SitemapEnhancedPlus_Model_SitemapEnhancedPlus::TYPE_IMAGE:
                $html = 'Regular + Image';
                break;
            case CueBlocks_SitemapEnhancedPlus_Model_SitemapEnhancedPlus::TYPE_MOBILE:
                $html = 'Mobile';
                break;
        }

        return $html;
    }

}
