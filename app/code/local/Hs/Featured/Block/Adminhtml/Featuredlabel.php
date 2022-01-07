<?php
/**
 * @version   1.0 12.0.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

class Hs_Featured_Block_Adminhtml_Featuredlabel extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_featuredlabel';
		$this->_blockGroup = 'featured';
		$this->_headerText = Mage::helper('featured')->__('Manage Featured Label');
                
                
                
		parent::__construct();
		
	}
}
