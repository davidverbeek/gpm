<?php
class Gyzs_Blog_Block_Adminhtml_Blog    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_blog';
    $this->_blockGroup = 'blog';
    $this->_headerText = Mage::helper('blog')->__('Manage Blog');
   // $this->_addButtonLabel = Mage::helper('blog')->__('Add New FAQ');
    parent::__construct();
	$this->_removeButton('add');
  }
}