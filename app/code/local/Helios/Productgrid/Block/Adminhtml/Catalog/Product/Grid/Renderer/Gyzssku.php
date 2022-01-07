<?php
class Helios_Productgrid_Block_Adminhtml_Catalog_Product_Grid_Renderer_Gyzssku extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    /**
     * Calculate Marge Syntax: Price - Cost.
     */
    public function render(Varien_Object $row)
    {
        return Mage::helper('common')->getGYZSSku($row->getSku());
    }
}
?>