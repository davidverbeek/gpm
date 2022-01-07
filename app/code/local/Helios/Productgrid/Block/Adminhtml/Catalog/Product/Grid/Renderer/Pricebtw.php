<?php
class Helios_Productgrid_Block_Adminhtml_Catalog_Product_Grid_Renderer_Pricebtw extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    /**
     * Calculate Marge Syntax: Price - Cost.
     */
    public function render(Varien_Object $row)
    {
        $_product=Mage::getModel('catalog/product')->load($row->getData('entity_id'));
        return Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol().Mage::helper('tax')->getPrice($_product, $_product->getFinalPrice(),true);

    }
}
?>