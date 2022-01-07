<?php

    class KoekEnPeer_EffectConnect_Model_Source_Attribute_Select extends KoekEnPeer_EffectConnect_Model_Source_Attribute
    {
        public function toOptionArray($type = 'select')
        {
            return parent::toOptionArray('select');
        }
    }