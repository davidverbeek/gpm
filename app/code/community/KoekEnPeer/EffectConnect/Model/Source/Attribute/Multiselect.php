<?php

    class KoekEnPeer_EffectConnect_Model_Source_Attribute_Multiselect extends KoekEnPeer_EffectConnect_Model_Source_Attribute
    {
        public function toOptionArray($type = 'multiselect')
        {
            return parent::toOptionArray('multiselect');
        }
    }