<?php

    class KoekEnPeer_EffectConnect_Model_Source_Attribute
    {
        public function toOptionArray($type = false)
        {
            $attributes = Mage::getResourceModel('catalog/product_attribute_collection')
                ->getItems()
            ;

            $attributeTypeDisabled = false;
            $attributeCodes        = array();
            if ($type != 'multiselect')
            {
                $attributeCodes[''] = '';
            }

            if (Mage::getStoreConfig('effectconnect_options/custom/disable_attribute_type') == 1)
            {
                $attributeTypeDisabled = true;
                $type                  = false;
            }elseif ($type == 'multiselect')
            {
                $type = 'select';
            }
            foreach ($attributes as $attribute)
            {
                if ($type)
                {
                    if (!stristr($attribute->getFrontendInput(), $type) &&
                        !stristr($attribute->getBackendType(), $type)
                    )
                    {
                        continue;
                    }
                }
                $attributeCode  = $attribute->getAttributeCode();
                $attributeLabel = $attribute->getFrontendLabel();
                if ($attribute->getBackendType() == 'static' || empty($attributeLabel))
                {
                    if ($attributeCode != 'sku')
                    {
                        continue;
                    }
                }
                $attributeCodes[$attributeCode] = $attributeLabel.' ('.$attributeCode.')'.($attributeTypeDisabled ? ' | '.strtoupper($attribute->getFrontendInput()) : '');
            }
            asort($attributeCodes);
            $return = array();
            foreach ($attributeCodes as $attributeCode => $attributeLabel)
            {
                $return[] = array(
                    'value' => $attributeCode,
                    'label' => $attributeLabel
                );
            }

            return $return;
        }
    }