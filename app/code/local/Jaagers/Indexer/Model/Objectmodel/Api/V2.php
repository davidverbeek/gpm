<?php

class Jaagers_Indexer_Model_Objectmodel_Api_V2 extends Mage_Api_Model_Resource_Abstract
{

    public function startBatch($productIds)
    {		
		
		Mage::App('admin');
		
		foreach($productIds->productIds as $id) {
			
			if(strlen($id) > 0) {

				$product = Mage::getModel('catalog/product');
				$product->setStoreId('2');
				
				
				if (is_string($id)) {
					$idBySku = $product->getIdBySku($id);
					if ($idBySku) {
						$productId = $idBySku;
					}
				}
				
				$product->load($productId);
				$product->setData('store_id', 2);
				Mage::log($product,null,'jaggers_indexer.log');
				
				try {
					Mage::getSingleton('index/indexer')->processEntityAction( $product, Mage_Catalog_Model_Product::ENTITY, Mage_Index_Model_Event::TYPE_SAVE );
				} catch (Exception $e) {
					return $e;
				}
				
			}
				
		}
    
    }
    
}

