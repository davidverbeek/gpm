<?php

    class KoekEnPeer_EffectConnect_Model_Source_Attribute_Description
    {
        public function toOptionArray($type = false)
        {
            $attributes = Mage::getResourceModel('catalog/product_attribute_collection')
                ->getItems()
            ;
            $return = array();
            $return[] = array(
                'value' => '_combined',
                'label' => 'Combine short description & description'
            );
            foreach ($attributes as $attribute)
            {
                if ($attribute->getFrontendInput() != 'textarea')
                {
                    continue;
                }
                $attributeCode  = $attribute->getAttributeCode();
                $attributeLabel = $attribute->getFrontendLabel();
                $return[] = array(
                    'value' => $attributeCode,
                    'label' => $attributeLabel.' ('.$attributeCode.')'
                );
            }

            return $return;
        }
    }