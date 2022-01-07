<?php

class Hs_Featured_Block_List extends Mage_Catalog_Block_Product_Abstract
{
	protected $_productCount=4;
	
	public function getProductCollection()
	{
	
		$collection=Mage::getModel('catalog/product')->getCollection()
					->addAttributeToSelect('*')
					->addAttributeToFilter('featured','1');
		
    	
    	Mage::getSingleton('catalog/product_status') -> addVisibleFilterToCollection($collection);
		Mage::getSingleton('cataloginventory/stock') -> addInStockFilterToCollection($collection);
		Mage::getSingleton('catalog/product_visibility') -> addVisibleInSearchFilterToCollection($collection);
		
		$collection->getSelect()->order('RAND()');
		
		$collection->setPageSize($this->_productCount)
               ->setCurPage(1);
					
		return $collection;
	}
}
