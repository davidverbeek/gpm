<?php

class Techtwo_ImportExport_Model_Product_Api_V2 extends Mage_Catalog_Model_Product_Api_V2
{
    /**
     *  Set additional data before product saved
     *
     *  @param    Mage_Catalog_Model_Product $product
     *  @param    object $productData
     *  @return   object
     */
    protected function _prepareDataForSave ($product, $productData)
    {
        foreach ($product->getTypeInstance(true)->getEditableAttributes($product) as $attribute) {
            $_attrCode = $attribute->getAttributeCode();
            if ($this->_isAllowedAttribute($attribute)
                && isset($productData->$_attrCode)
                && $attribute->usesSource()
                && $attribute->getSource() instanceof Mage_Eav_Model_Entity_Attribute_Source_Table) {

                // retrieve id for attribute option value
                $productData->$_attrCode = $attribute->getSource()->getOptionId($productData->$_attrCode);

                $product->setData(
                    $attribute->getAttributeCode(),
                    $productData->$_attrCode
                );
            }
        }

        return parent::_prepareDataForSave($product, $productData);
    }
}
