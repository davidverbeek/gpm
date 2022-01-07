<?php

class Helios_Garantiesservice_Model_Mysql4_Garantiesservice_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('garantiesservice/garantiesservice');
    }

    public function addStoreFilter($store) {
        if ($store instanceof Mage_Core_Model_Store) {
            $store = array($store->getId());
        }

        $this->getSelect()->join(
                        array('store_table' => $this->getTable('garantiesservice_store')), 'main_table.garantiesservice_id = store_table.garantiesservice_id', array()
                )
                ->where('store_table.store_id in (?)', array(0, $store));

        return $this;
    }

}
