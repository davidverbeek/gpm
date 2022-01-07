<?php

class Techtwo_ImportExport_Model_Product_Api extends Mage_Catalog_Model_Product_Api
{
    /**
     *  Set additional data before product saved
     *
     *  @param    Mage_Catalog_Model_Product $product
     *  @param    array $productData
     *  @return   object
     */
    protected function _prepareDataForSave ($product, $productData)
    {
        foreach ($product->getTypeInstance(true)->getEditableAttributes($product) as $attribute) {
            if ($this->_isAllowedAttribute($attribute)
                && isset($productData[$attribute->getAttributeCode()])
                && $attribute->usesSource()
                && $attribute->getSource() instanceof Mage_Eav_Model_Entity_Attribute_Source_Table) {

                // retrieve id for attribute option value
                $productData[$attribute->getAttributeCode()] = $attribute->getSource()->getOptionId($productData[$attribute->getAttributeCode()]);

                $product->setData(
                    $attribute->getAttributeCode(),
                    $productData[$attribute->getAttributeCode()]
                );
            }
        }

        return parent::_prepareDataForSave($product, $productData);
    }
}
