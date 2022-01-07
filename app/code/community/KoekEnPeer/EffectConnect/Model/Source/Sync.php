<?php

    class KoekEnPeer_EffectConnect_Model_Source_Sync
    {
        public function toOptionArray()
        {
            return array(
                array(
                    'value' => '',
                    'label' => 'All products'
                ),
                array(
                    'value' => 'enabled',
                    'label' => 'Only enabled products'
                ),
                array(
                    'value' => 'visible',
                    'label' => 'Only visible products'
                ),
                array(
                    'value' => 'enabled_visible',
                    'label' => 'Only enabled & visible products'
                )
            );
        }
    }