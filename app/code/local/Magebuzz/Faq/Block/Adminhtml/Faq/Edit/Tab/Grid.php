<?php
/**
 * SilverTouch Technologies Limited.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.silvertouch.com/MagentoExtensions/LICENSE.txt
 *
 * @category   Sttl
 * @package    Sttl_faq
 * @copyright  Copyright (c) 2011 SilverTouch Technologies Limited. (http://www.silvertouch.com/MagentoExtensions)
 * @license    http://www.silvertouch.com/MagentoExtensions/LICENSE.txt
 */ 
class Magebuzz_Faq_Block_Adminhtml_Faq_Edit_Tab_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('productGrid');
	 
	   $this->setUseAjax(true);
		if ($this->_getBlock()->getId()) {
			$this->setDefaultFilter(array('check_products'=>1));
		}
  }

	protected function _getBlock()
    {
    	return Mage::getModel('faq/faq')->load($this->getRequest()->getParam('id'));
    }
	
	
  protected function _prepareCollection()
  {
	  $collection = Mage::getModel('catalog/product_link')
            ->getProductCollection()
            ->addAttributeToSelect('*');
        $this->setCollection($collection);
        return parent::_prepareCollection();
  }

  
  protected function _prepareColumns()
  {
      $this->addColumn('check_products', array(
                'header_css_class' => 'a-center',
                'type'      => 'checkbox',
                'name'      => 'check_products',
                'values'    => $this->_getSelectedProducts(),
                'align'     => 'center',
                'index'     => 'entity_id'
            ));
    
        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('catalog')->__('ID'),
            'sortable'  => true,
            'width'     => '60px',
            'index'     => 'entity_id'
        ));
        $this->addColumn('name', array(
            'header'    => Mage::helper('catalog')->__('Name'),
            'index'     => 'name'
        ));

        $this->addColumn('type',
            array(
                'header'=> Mage::helper('catalog')->__('Type'),
                'width' => '100px',
                'index' => 'type_id',
                'type'  => 'options',
                'options' => Mage::getSingleton('catalog/product_type')->getOptionArray(),
        ));

        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->load()
            ->toOptionHash();

        $this->addColumn('set_name',
            array(
                'header'=> Mage::helper('catalog')->__('Attrib. Set Name'),
                'width' => '130px',
                'index' => 'attribute_set_id',
                'type'  => 'options',
                'options' => $sets,
        ));
/*
        $this->addColumn('status',
            array(
                'header'=> Mage::helper('catalog')->__('Status'),
                'width' => '90px',
                'index' => 'status',
                'type'  => 'options',
                'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
        ));
*/
        $this->addColumn('visibility',
            array(
                'header'=> Mage::helper('catalog')->__('Visibility'),
                'width' => '90px',
                'index' => 'visibility',
                'type'  => 'options',
                'options' => Mage::getSingleton('catalog/product_visibility')->getOptionArray(),
        ));

        $this->addColumn('sku', array(
            'header'    => Mage::helper('catalog')->__('SKU'),
            'width'     => '80px',
            'index'     => 'sku'
        ));
        $this->addColumn('price', array(
            'header'    => Mage::helper('catalog')->__('Price'),
            'type'  => 'currency',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'index'     => 'price'
        ));

        $this->addColumn('position', array(
            'header'    => Mage::helper('catalog')->__('Position'),
            'name'      => 'position',
            'type'      => 'number',
            'width'     => '60px',
            'validate_class' => 'validate-number',
            'index'     => 'position',
        	'value'		=> '0',
            'editable'  => true,
            'edit_only' => false
        ));
	  
      return parent::_prepareColumns();
  }
  
	public function getGridUrl()
    {
        return $this->_getData('grid_url') ? $this->_getData('grid_url') : $this->getUrl('*/*/grid', array('_current'=>true));
    }
  
  
  
  protected function _getSelectedProducts()
    {
        $products = $this->getRequest()->getPost('grid', null);
       
        $id     = $this->getRequest()->getParam('id');
		$block  = Mage::getModel('faq/faq')->load($id);
		
        if (!is_array($products)) {
        	$products = array();
        	
        	if($block->getProducts()!='') {
	        	$arr = explode('&',$block->getProducts());
	        	foreach($arr as $p) {
	        		list($id,$pos) = explode('=',$p);	
	        		$products[] = $id; 
	        	}
        	}
        }
        return $products;
    }

}