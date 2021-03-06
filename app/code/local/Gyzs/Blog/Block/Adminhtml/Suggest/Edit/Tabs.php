<?php

class Magebuzz_Faq_Block_Adminhtml_Suggest_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('faq_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('faq')->__('Suggestion Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('faq')->__('General Information'),
          'title'     => Mage::helper('faq')->__('General Information'),
          'content'   => $this->getLayout()->createBlock('faq/adminhtml_suggest_edit_tab_form')->toHtml(),
      ));
      return parent::_beforeToHtml();
  }
}