<?php
class Magebuzz_Faq_Block_Adminhtml_Faq extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_faq';
    $this->_blockGroup = 'faq';
    $this->_headerText = Mage::helper('faq')->__('Manage FAQ');
    $this->_addButtonLabel = Mage::helper('faq')->__('Add New FAQ');
    parent::__construct();
  }
}