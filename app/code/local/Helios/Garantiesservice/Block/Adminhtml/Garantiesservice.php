<?php
class Helios_Garantiesservice_Block_Adminhtml_Garantiesservice extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_garantiesservice';
    $this->_blockGroup = 'garantiesservice';
    $this->_headerText = Mage::helper('garantiesservice')->__('Garantiesservice Manager');
    $this->_addButtonLabel = Mage::helper('garantiesservice')->__('Add Garanties & service');
	
    parent::__construct();
  }
}