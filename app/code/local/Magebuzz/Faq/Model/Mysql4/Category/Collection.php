<?php
/**
 * @copyright   Copyright (c) 2013 AZeBiz Co. LTD
 */
class Magebuzz_Faq_Model_Mysql4_Category_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
	public function _construct() {
		parent::_construct();
		$this->_init('faq/category');
	}
	
	protected function  _toOptionArray($valueField = 'category_id', $labelField = 'category_name', $additional = array()) {
		return parent:: _toOptionArray($valueField, $labelField, $additional);
	}
		
	public function addStoreFilter($store, $withAdmin = true){
    if ($store instanceof Mage_Core_Model_Store) {
        $store = array($store->getId());
    }

    $this->getSelect()->join(
        array('store_table' => $this->getTable('faq/faq_category_store')),
        'main_table.category_id = store_table.category_id',
        array()
    )
    ->where('store_table.store_id in (?)', ($withAdmin ? array(0, $store) : $store))
    ->group('main_table.category_id');

    return $this;
	} 
}