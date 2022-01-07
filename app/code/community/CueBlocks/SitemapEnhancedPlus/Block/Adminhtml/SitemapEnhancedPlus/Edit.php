<?php

/**
 * Description of 
 * @package   CueBlocks_SitemapEnhancedPlus
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhancedPlus_Block_Adminhtml_SitemapEnhancedPlus_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    /**
     * Init container
     */
    public function __construct()
    {
        $this->_objectId = 'sitemap_id';
        $this->_controller = 'adminhtml_sitemapEnhancedPlus';
        $this->_blockGroup = 'sitemapEnhancedPlus';

        parent::__construct();

//        $this->_addButton('ping', array(
//            'label'   => Mage::helper('adminhtml')->__('Save & Generate & Ping'),
//            'onclick' => "$('generate').value=2; editForm.submit();",
//            'class'   => 'add',
//        ));

        $this->_addButton('generate', array(
            'label'   => Mage::helper('adminhtml')->__('Save & Generate'),
            'onclick' => "$('generate').value=1; editForm.submit();",
            'class'   => 'add'
        ));
    }

    /**
     * Get edit form container header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        if (Mage::registry('sitemapEnhancedPlus_sitemap')->getId()) {
            return Mage::helper('sitemapEnhancedPlus')->__('Edit Sitemap');
        } else {
            return Mage::helper('sitemapEnhancedPlus')->__('Generate New Sitemap');
        }
    }

    /**
     * Return save url for edit form
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array('_current' => true, 'back'     => null));
    }

}
