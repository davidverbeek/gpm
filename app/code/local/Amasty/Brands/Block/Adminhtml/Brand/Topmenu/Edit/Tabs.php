<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Brands
 */

class Amasty_Brands_Block_Adminhtml_Brand_Topmenu_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /** @var Mage_Core_Block_Abstract  */
    protected $_productsBlock;

    public function __construct()
    {
        parent::__construct();
        $this->setId('brandTopmenuTabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('ambrands')->__('Brands Top Menu'));
    }

    /**
     * @return Mage_Core_Block_Abstract
     * @throws Exception
     */
    protected function _prepareLayout()
    {
        $this->addTab('general', array(
            'label'     => Mage::helper('catalog')->__('General'),
            'content'   => $this->getLayout()->createBlock(
                'ambrands/adminhtml_brand_topmenu_edit_tab_general',
                'ambrands_brand_topmenu_edit_tab_general'
            )->toHtml()
        ));

        return parent::_prepareLayout();
    }
}