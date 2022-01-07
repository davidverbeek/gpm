<?php
/**
 * @version   1.0 12.0.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

class Hs_Featured_Block_Adminhtml_Featured extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_featured';
		$this->_blockGroup = 'featured';
		$this->_headerText = Mage::helper('featured')->__('Manage Featured Product');
                
                //$this->_addButtonLabel = Mage::helper('featured')->__('Add Featured Label');
                
		parent::__construct();
		$this->_removeButton('add');
	}
        protected function _prepareLayout()
        {
            $this->_addButton('manage_label', array(
                'label'   => Mage::helper('featured')->__('Manage Label'),
                'onclick' => "setLocation('{$this->getUrl('*/adminhtml_featuredlabel/index')}')"//,
             //   'class'   => 'add'
            ));
                
            $this->_addButton('add_label', array(
                'label'   => Mage::helper('featured')->__('Add Label'),
                'onclick' => "setLocation('{$this->getUrl('*/adminhtml_featuredlabel/new')}')",
                'class'   => 'add'
            ));    

            $this->setChild('grid', $this->getLayout()->createBlock('adminhtml/catalog_product_grid', 'product.grid'));
            return parent::_prepareLayout();
        }
}
