<?php
class Helios_Customerfaq_Model_Mysql4_Customerfaq extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("customerfaq/customerfaq", "customerfaq_id");
    }
}