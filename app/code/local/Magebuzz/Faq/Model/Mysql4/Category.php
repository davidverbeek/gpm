<?php
/**
 * @copyright   Copyright (c) 2013 AZeBiz Co. LTD
 */
class Magebuzz_Faq_Model_Mysql4_Category extends Mage_Core_Model_Mysql4_Abstract {
	protected function _construct() {
		$this->_init('faq/category', 'category_id');
	}
	
	protected function _beforeSave(Mage_Core_Model_Abstract $object) {
    if (! $object->getId()) {
      $object->setCreationTime(Mage::getSingleton('core/date')->gmtDate());
    }
    $object->setUpdateTime(Mage::getSingleton('core/date')->gmtDate());
    return $this;
	}
	
	protected function _afterSave(Mage_Core_Model_Abstract $object) {
		$condition = $this->_getWriteAdapter()->quoteInto('category_id = ?', $object->getId());
		$this->_getWriteAdapter()->delete($this->getTable('faq/faq_category_store'), $condition);

		foreach ((array)$object->getData('stores') as $store) {
			$storeArray = array();
			$storeArray['category_id'] = $object->getId();
			$storeArray['store_id'] = $store;
			$this->_getWriteAdapter()->insert($this->getTable('faq/faq_category_store'), $storeArray);
		}
		return parent::_afterSave($object);
	}
	
	public function load(Mage_Core_Model_Abstract $object, $value, $field=null) {
    if (!intval($value) && is_string($value)) {
      $field = 'identifier'; // You probably don't have an identifier...
    }
    return parent::load($object, $value, $field);
	}
	
	
	protected function _afterLoad(Mage_Core_Model_Abstract $object) {
		$select = $this->_getReadAdapter()->select()
			->from($this->getTable('faq/faq_category_store'))
			->where('category_id = ?', $object->getId());

		if ($data = $this->_getReadAdapter()->fetchAll($select)) {
			$storesArray = array();
			foreach ($data as $row) {
				$storesArray[] = $row['store_id'];
			}
			$object->setData('store_id', $storesArray);
		}

		return parent::_afterLoad($object);
	}

	protected function _getLoadSelect($field, $value, $object) {
			$select = parent::_getLoadSelect($field, $value, $object);
			if ($object->getStoreId()) {
					$select->join(array('cbs' => $this->getTable('faq/faq_category_store')), $this->getMainTable().'.category_id = cbs.category_id')
									->where('is_active=1 AND cbs.store_id in (0, ?) ', $object->getStoreId())
									->order('store_id DESC')
									->limit(1);
			}
			return $select;
	}

	public function lookupStoreIds($id) {
		return $this->_getReadAdapter()->fetchCol($this->_getReadAdapter()->select()
			->from($this->getTable('faq/faq_category_store'), 'store_id')
			->where("{$this->getIdFieldName()} = ?", $id)
		);
	}

}