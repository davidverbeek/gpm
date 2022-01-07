<?php

    class KoekEnPeer_EffectConnect_Model_Payment extends Mage_Payment_Model_Method_Abstract
    {
        protected $_code = 'effectconnect_payment';

        public function isAvailable($quote = null)
        {
            return Mage::registry('is_effectconnect');
        }
    }