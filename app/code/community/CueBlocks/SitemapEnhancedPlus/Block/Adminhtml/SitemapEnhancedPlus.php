<?php

/**
 * Description of 
 * @package   CueBlocks_SitemapEnhancedPlus
 * @company   CueBlocks - http://www.cueblocks.com/
 
 * 
 */
class CueBlocks_SitemapEnhancedPlus_Block_Adminhtml_SitemapEnhancedPlus extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    /**
     * Block constructor
     */
    public function __construct()
    {
        // Used to Generate Grid file/class name
        // $this->_blockGroup/$this->_controller_ . 'grid'
        $this->_controller = 'adminhtml_sitemapEnhancedPlus';
        $this->_blockGroup = 'sitemapEnhancedPlus';
        $this->_headerText = Mage::helper('sitemapEnhancedPlus')->__('Manage Sitemaps');
        $this->_addButtonLabel = Mage::helper('sitemap')->__('Add Sitemap');

        parent::__construct();
    }

}
