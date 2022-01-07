<?php

class Hs_Customerbought_Block_List extends Mage_Catalog_Block_Product_Abstract
{
	protected $_productCount=4;
	
	public function getItemCollection()
	{
	
            //$collection = Mage::getResourceModel('sales/order_collection');
            //$collection->getSelect()->join('mage_sales_flat_order_item','main_table.entity_id = mage_sales_flat_order_item.order_id',array('product_id'));
            $collection = Mage::getResourceModel('sales/order_item_collection');
            //$collection->getSelect()->join('mage_sales_flat_order','main_table.order_id = mage_sales_flat_order_item.order_id',array('product_id'));
            $collection->getSelect()->order('item_id Desc');
            $collection->getSelect()->limit(5);
            
            //echo $collection->getSelect(); exit;
            return $collection;
	}
}
