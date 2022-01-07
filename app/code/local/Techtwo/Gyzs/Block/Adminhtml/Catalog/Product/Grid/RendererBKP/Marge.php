<?php
class Techtwo_Gyzs_Block_Adminhtml_Catalog_Product_Grid_Renderer_Marge extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
	/**
	* Removes if 'editable' function (see Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract)
	*/
	public function render(Varien_Object $row)
    {
        return $this->_getValue($row);
    }

    public function _getValue(Varien_Object $row)
    {
    	//return $row->getData('marge')? Mage::getModel('directory/currency')->format($row->getData('marge')): '';
    	
    	return Mage::getModel('directory/currency')->format($row->getData('price')-$row->getData('cost'));
    }
}
?>