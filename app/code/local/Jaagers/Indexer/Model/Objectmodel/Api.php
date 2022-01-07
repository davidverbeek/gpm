<?php

class Jaagers_Indexer_Model_Objectmodel_Api extends Mage_Api_Model_Resource_Abstract
{
	
	public function startBatch($productIds)
    {
    	 
    	foreach($productIds as $id) {
			
			$product = Mage::getModel('catalog/product')->load($id);
			
			Mage::getSingleton('index/indexer')->processEntityAction(
				$product, self::ENTITY, Mage_Index_Model_Event::TYPE_SAVE
			);
			
			Mage::Log('product with id ' . $id . ' finished indexing',null,'indexing.log');
			
		}
		
		return true;
    
    }
    
}

