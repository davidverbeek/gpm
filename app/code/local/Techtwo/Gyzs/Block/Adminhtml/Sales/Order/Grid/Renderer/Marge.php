<?php

class Techtwo_Gyzs_Block_Adminhtml_Sales_Order_Grid_Renderer_Marge extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
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

        // return Mage::getModel('directory/currency')->format($row->getData('marge'));

        $tablename = Mage::getSingleton('core/resource')->getTableName('mage_sales_flat_order_item'); 
        $query = '
            SELECT sum( base_row_total - ( base_cost * qty_ordered ) ) as marge
            FROM `mage_sales_flat_order_item`
            WHERE order_id = '.((int) $row->getId()).'
        ';


        $dbRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        // return Mage::getModel('directory/currency')->format($dbRead->fetchOne($query));
        
        //Added By Parth
        return Mage::helper('core')->currency($dbRead->fetchOne($query),true,false);
    }
}
?>