<?php
class Helios_Creports_Block_Adminhtml_Wiretransfer extends Mage_Adminhtml_Block_Widget_Grid_Container {

	public function __construct() {
		$this->_controller = 'adminhtml_wiretransfer';
		$this->_blockGroup = 'creports';
		$this->_headerText = Mage::helper('creports')->__('Wire Transfer Orders Report');
		parent::__construct();
		$this->_removeButton('add');
	}
}