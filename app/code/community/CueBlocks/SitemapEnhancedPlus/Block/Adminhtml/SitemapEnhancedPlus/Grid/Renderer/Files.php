<?php

/**
 * Description of
 * @package   CueBlocks_SitemapEnhancedPlus
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhancedPlus_Block_Adminhtml_SitemapEnhancedPlus_Grid_Renderer_Files extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{

    public function render(Varien_Object $row)
    {
        /* @var $row CueBlocks_SitemapEnhancedPlus_Model_SitemapEnhancedPlus */

        $html = '';
        $collection = $row->getFilesCollection(array(
            CueBlocks_SitemapEnhancedPlus_Model_SitemapEnhancedPlus::TYPE_REGULAR,
            CueBlocks_SitemapEnhancedPlus_Model_SitemapEnhancedPlus::TYPE_IMAGE,
            CueBlocks_SitemapEnhancedPlus_Model_SitemapEnhancedPlus::TYPE_MOBILE
        ));

        foreach ($collection as $item) {
            $fileName = $item->getSitemapFileFilename();
            $url = $row->getFileUrl($fileName);

            if (file_exists($row->getFilePath($fileName))) {
                $html .= sprintf('<div><a target="_blank" href="%1$s">%2$s - L:%3$s</a></div>', $url, $fileName, $item->getLinksCount());
            } else {
                $html .= sprintf('<div>%1$s</div>', $fileName);
            }
        }

        return $html;
    }

}
