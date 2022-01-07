<?php

class Helios_Garantiesservice_Block_Adminhtml_Garantiesservice_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('garantiesservice_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('garantiesservice')->__('Garantiesservice Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('garantiesservice')->__('Garantiesservice Information'),
          'title'     => Mage::helper('garantiesservice')->__('Garantiesservice Information'),
          'content'   => $this->getLayout()->createBlock('garantiesservice/adminhtml_garantiesservice_edit_tab_form')->toHtml(),
		  'content'   => $this->getLayout()->createBlock('garantiesservice/adminhtml_garantiesservice_edit_tab_form')->toHtml(),
      ));
	  
	  
	  
     
      return parent::_beforeToHtml();
  }
}