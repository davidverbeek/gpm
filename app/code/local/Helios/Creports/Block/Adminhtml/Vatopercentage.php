<?php
class Helios_Creports_Block_Adminhtml_Vatopercentage extends Mage_Adminhtml_Block_Widget_Grid_Container {

	public function __construct() {
		$this->_controller = 'adminhtml_vatopercentage';
		$this->_blockGroup = 'creports';
		$this->_headerText = Mage::helper('creports')->__('VAT 0 Percentage Report');
		parent::__construct();
		$this->_removeButton('add');
	}
}