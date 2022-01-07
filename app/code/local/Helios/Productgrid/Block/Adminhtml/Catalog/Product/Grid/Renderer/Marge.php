<?php
class Helios_Productgrid_Block_Adminhtml_Catalog_Product_Grid_Renderer_Marge extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    /**
     * Calculate Marge Syntax: Price - Cost.
     */
    public function render(Varien_Object $row)
    {
        return Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol().($row->getData('price')-$row->getData('cost'));
    }
}
?>
