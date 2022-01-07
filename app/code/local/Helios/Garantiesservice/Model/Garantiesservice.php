<?php

class Helios_Garantiesservice_Model_Garantiesservice extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('garantiesservice/garantiesservice');
    }
}