<?php


class Helios_Customerfaq_Block_Adminhtml_Customerfaq extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{

	$this->_controller = "adminhtml_customerfaq";
	$this->_blockGroup = "customerfaq";
	$this->_headerText = Mage::helper("customerfaq")->__("Customerfaq Manager");
	$this->_addButtonLabel = Mage::helper("customerfaq")->__("Add New Item");
	parent::__construct();
	
	}

}