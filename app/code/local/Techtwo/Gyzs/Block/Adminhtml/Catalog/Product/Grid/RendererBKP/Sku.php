<?php
class Techtwo_Gyzs_Block_Adminhtml_Catalog_Product_Grid_Renderer_Sku extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
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
        return $row->getData('gyzs_sku');
    	//return Mage::helper('gyzs')->getGyzsSku( $row->getData('sku') );
    }
}
?>