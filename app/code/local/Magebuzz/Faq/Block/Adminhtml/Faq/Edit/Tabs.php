<?php

class Magebuzz_Faq_Block_Adminhtml_Faq_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('faq_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('faq')->__('FAQ Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('faq')->__('General Information'),
          'title'     => Mage::helper('faq')->__('General Information'),
          'content'   => $this->getLayout()->createBlock('faq/adminhtml_faq_edit_tab_form')->toHtml(),
      ));
	  
	  //$this->addTab('product', array(
          //'label'     => Mage::helper('faq')->__('Skus'),
          //'class'     => 'ajax',
          //'url'       => $this->getUrl('*/*/product', array('_current' => true)),
		//  'content'   => $this->getLayout()->createBlock('faq/adminhtml_faq_edit_tab_product')->toHtml(),
      //));
	  $this->addTab('grid_section', array(
			'label'     => Mage::helper('faq')->__('Products'),
			'class'     => 'ajax',
			'url'       => $this->getUrl('*/*/grid', array('_current' => true)),
	  )); 
	  
		//	$this->addTab('categories', array(
    //            'label'     => Mage::helper('catalog')->__('Categories'),
    //            'url'       => $this->getUrl('*/*/categories', array('_current' => true)),
    //            'class'     => 'ajax',
    //        ));				
		
			$this->addTab('categories', array(
          'label'     => Mage::helper('faq')->__('Categories'),
          'title'     => Mage::helper('faq')->__('Categories'),
          'content'   => $this->getLayout()->createBlock('faq/adminhtml_faq_edit_tab_categories')->toHtml(),
      ));			
      return parent::_beforeToHtml();
  }
}