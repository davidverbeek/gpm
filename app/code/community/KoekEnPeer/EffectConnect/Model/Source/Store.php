<?php

    class KoekEnPeer_EffectConnect_Model_Source_Store
    {
        public function toOptionArray($type = 'default')
        {
            $stores = Mage::getSingleton('adminhtml/system_store')
                ->getStoreValuesForForm(false, false)
            ;

            if ($type == 'default' || !$type)
            {
                array_unshift(
                    $stores,
                    array(
                        'label' => 'Admin store view',
                        'value' => ''
                    )
                );
            }

            return $stores;
        }
    }