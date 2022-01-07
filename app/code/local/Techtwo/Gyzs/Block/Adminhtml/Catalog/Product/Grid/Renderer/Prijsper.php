<?php
class Techtwo_Gyzs_Block_Adminhtml_Catalog_Product_Grid_Renderer_Prijsper extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
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
		
		$unit = Mage::helper('featured')->getProductUnit($row->getData('verkoopeenheid'));
		
		if (!(int) $row->getData('afwijkenidealeverpakking')){
			$prijsfactor = ($row->getData('idealeverpakking') > 1) ? $row->getData('idealeverpakking') : 1;
		} else {
			$prijsfactor = 1;
		}
		$sales_unit = Mage::helper('featured')->getStockUnit($prijsfactor, $unit);
		if ($prijsfactor == 1)
			$prijsfactor = '';
		
		$_prejis = "Per ".$prijsfactor." ".$sales_unit;
                
        return $_prejis;    

    }
}
?>