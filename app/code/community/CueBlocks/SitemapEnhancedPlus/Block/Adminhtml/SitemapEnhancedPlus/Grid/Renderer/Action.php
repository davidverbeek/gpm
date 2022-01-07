<?php

/**
 * Description of 
 * @package   CueBlocks_SitemapEnhancedPlus
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhancedPlus_Block_Adminhtml_SitemapEnhancedPlus_Grid_Renderer_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{

    public function render(Varien_Object $row)
    {
        /* @var $row CueBlocks_SitemapEnhancedPlus_Model_SitemapEnhancedPlus */
        
        $this->getColumn()->setActions(
                array(
                    array(
                        'url' => $this->getUrl("*/sitemapEnhancedPlus/ping", array("sitemap_id" => $row->getSitemapId())),
                        'caption'    => Mage::helper('adminhtml')->__('Ping Sitemap'),
                    ), array(
                        'url' => $this->getUrl('*/sitemapEnhancedPlus/generate', array('sitemap_id' => $row->getSitemapId())),
                        'caption'    => Mage::helper('sitemapEnhancedPlus')->__('Generate'),
                        'confirm'    => Mage::helper('adminhtml')->__('Are you sure you want to update/generate this XML Sitemap?'),
                    )
//                    , array(
//                        'url' => $this->getUrl('*/sitemapEnhancedPlus/generatepopup', array('sitemap_id' => $row->getSitemapId())),
//                        'caption'    => Mage::helper('sitemapEnhancedPlus')->__('Generate Pop Up'),
//                        'confirm'    => Mage::helper('adminhtml')->__('Are you sure you want to update/generate this XML Sitemap?'),
//                        'popup'      => true
//                    )
                    , array(
                        'url' => $this->getUrl('*/sitemapEnhancedPlus/delete', array('sitemap_id' => $row->getSitemapId())),
                        'caption'    => Mage::helper('sitemapEnhancedPlus')->__('Delete'),
                        'confirm'    => Mage::helper('adminhtml')->__('Are you sure you want to delete this XML Sitemap?'),
                    )
//                    , array(
//                        'url' => $this->getUrl('*/sitemapEnhancedPlus/addRobots', array('sitemap_id' => $row->getSitemapId())),
//                        'caption'    => Mage::helper('sitemapEnhancedPlus')->__('Add to Robots.txt'),
//                    )
                )
        );
        return parent::render($row);
    }

}
