<?php
    class Jaagers_Price_Model_Mysql4_Price extends Mage_Core_Model_Mysql4_Abstract
    {
        protected function _construct()
        {
            $this->_init("price/price", "id");
        }
    }
	 