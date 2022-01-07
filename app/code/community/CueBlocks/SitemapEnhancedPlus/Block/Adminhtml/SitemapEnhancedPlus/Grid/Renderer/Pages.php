<?php

/**
 * Description of
 * @package   CueBlocks_SitemapEnhancedPlus
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhancedPlus_Block_Adminhtml_SitemapEnhancedPlus_Grid_Renderer_Pages extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $html = '';

        $html .= sprintf('<table style="border:0;"><tbody>');
        /* @var $row CueBlocks_SitemapEnhancedPlus_Model_SitemapEnhancedPlus */
        $html .= $row->getPageReport();
        $html .= sprintf('</tbody></table>');

        return $html;
    }
}
