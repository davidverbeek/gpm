<?php

require 'Mage/CatalogSearch/Model/Mysql4/Fulltext.php';

class Techtwo_Gyzs_Model_Mysql4_Fulltext extends Mage_CatalogSearch_Model_Mysql4_Fulltext
{
	protected function _prepareProductIndex($indexData, $productData, $storeId)
	{
		$index = array();

        $index['gyzs_sku'] = Mage::helper('common')->getGyzsSku($productData['sku']); // TECHTWO CHANGE HERE, ONLY THIS LINE

        foreach ($this->_getSearchableAttributes('static') as $attribute) {
            if (isset($productData[$attribute->getAttributeCode()])) {
                if ($value = $this->_getAttributeValue($attribute->getId(), $productData[$attribute->getAttributeCode()], $storeId)) {

                    //For grouped products
                    if (isset($index[$attribute->getAttributeCode()])) {
                        if (!is_array($index[$attribute->getAttributeCode()])) {
                            $index[$attribute->getAttributeCode()] = array($index[$attribute->getAttributeCode()]);
                        }
                        $index[$attribute->getAttributeCode()][] = $value;
                    }
                    //For other types of products
                    else {
                        $index[$attribute->getAttributeCode()] = $value;
                    }
                }
            }
        }

        foreach ($indexData as $attributeData) {
            foreach ($attributeData as $attributeId => $attributeValue) {
                $value = $this->_getAttributeValue($attributeId, $attributeValue, $storeId);
                if (!is_null($value) && $value !== false) {
                    $code = $this->_getSearchableAttribute($attributeId)->getAttributeCode();
                    //For grouped products
                    if (isset($index[$code])) {
                        if (!is_array($index[$code])) {
                            $index[$code] = array($index[$code]);
                        }
                        $index[$code][] = $value;
                    }
                    //For other types of products
                    else {
                        $index[$code] = $value;
                    }
                }
            }
        }

        $product = $this->_getProductEmulator()
            ->setId($productData['entity_id'])
            ->setTypeId($productData['type_id'])
            ->setStoreId($storeId);
        $typeInstance = $this->_getProductTypeInstance($productData['type_id']);
        if ($data = $typeInstance->getSearchableData($product)) {
            $index['options'] = $data;
        }

        if (isset($productData['in_stock'])) {
            $index['in_stock'] = $productData['in_stock'];
        }

        if ($this->_engine) {
            if ($this->_engine->allowAdvancedIndex()) {
                $index += $this->_engine->addAllowedAdvancedIndexField($productData);
            }

            return $this->_engine->prepareEntityIndex($index, $this->_separator);
        }

        return Mage::helper('catalogsearch')->prepareIndexdata($index, $this->_separator);
	}
}