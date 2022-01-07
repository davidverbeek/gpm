<?php
/**
 * @copyright   Copyright (c) 2013 AZeBiz Co. LTD
 */
class Magebuzz_Faq_Model_Mysql4_Faq extends Mage_Core_Model_Mysql4_Abstract {
	public function _construct() {    
		$this->_init('faq/faq', 'faq_id');
	}
		/**
	 *
	 *
	 * @param Mage_Core_Model_Abstract $object
	 */
	protected function _beforeSave(Mage_Core_Model_Abstract $object)
	{
		if (! $object->getId()) {
			$object->setCreationTime(Mage::getSingleton('core/date')->gmtDate());
		}
		$object->setUpdateTime(Mage::getSingleton('core/date')->gmtDate());
		return $this;
	}
	
	public function lookupCategoryIds($faq_id) {
		$adapter = $this->_getReadAdapter();
		
		$select  = $adapter->select()
				->from($this->getTable('faq/category_item'), 'category_id')
				->where('faq_id = ?',(int)$faq_id);

		return $adapter->fetchCol($select);
	}
	
	protected function _afterSave(Mage_Core_Model_Abstract $object)
	{
		// update faq category
		$newCategoryIds = $object->getData('categories');
		$oldCategoryIds = $this->lookupCategoryIds($object->getId());
		
		$table  = $this->getTable('faq/category_item');
		$insert = array_diff($newCategoryIds, $oldCategoryIds);
		$delete = array_diff($oldCategoryIds, $newCategoryIds);
		if ($delete) {
			$where = array(
				'faq_id = ?'     => (int) $object->getId(),
				'category_id IN (?)' => $delete
			);
			$this->_getWriteAdapter()->delete($table, $where);
		}
		if ($insert) {
			$data = array();
			foreach ($insert as $catId) {
				$data[] = array(
					'faq_id'  => (int) $object->getId(),
					'category_id' => (int) $catId
				);
			}
			$this->_getWriteAdapter()->insertMultiple($table, $data);
		}
		
		//update store
		$condition = $this->_getWriteAdapter()->quoteInto('faq_id = ?', $object->getId());
		$this->_getWriteAdapter()->delete($this->getTable('faq/faq_store'), $condition);

		foreach ((array)$object->getData('stores') as $store) {
				$storeArray = array();
				$storeArray['faq_id'] = $object->getId();
				$storeArray['store_id'] = $store;
				$this->_getWriteAdapter()->insert($this->getTable('faq/faq_store'), $storeArray);
		}

		return parent::_afterSave($object);
	}
	
	public function changeCategory($data){
		$mainTable = $this->getTable('faq/category_item');
		$where = $this->_getWriteAdapter()->quoteInto('faq_id = ?', $data['faq_id']);
		try{
			$query = $this->_getWriteAdapter()->update($mainTable, array('category_id'=>$data['category_id']),$where);
		}catch(Exception $e)	{
			echo $e->getMessage();
		}
	}
	
	public function lookForFaq($data){
		$mainTable = $this->getTable('faq/category_item');
		$where = $this->_getWriteAdapter()->quoteInto('faq_id = ?', $data['faq_id']);
		try{
			$select = $this->_getReadAdapter()->select()->from($mainTable, array('category_id'=>$data['category_id']),$where);
			$result = $this->_getReadAdapter()->fetchRow();
		}catch(Exception $e)	{
			echo $e->getMessage();
		}
		return $result;
	}
	
	public function load(Mage_Core_Model_Abstract $object, $value, $field=null)
	{
		if (!intval($value) && is_string($value)) {
			$field = 'identifier'; // You probably don't have an identifier...
		}
		return parent::load($object, $value, $field);
	}
	
	/**
	 *
	 * @param Mage_Core_Model_Abstract $object
	 */
	protected function _afterLoad(Mage_Core_Model_Abstract $object)	{
		$select = $this->_getReadAdapter()->select()->from(
				$this->getTable('faq/category_item')
		)->where('faq_id = ?', $object->getId());
		
		if ($data = $this->_getReadAdapter()->fetchAll($select)) {
				$categoryArray = array ();
				foreach ($data as $row) {
						$categoryArray[] = $row['category_id'];
				}
				$object->setData('category_id', $categoryArray);
	
		}
	
		$select = $this->_getReadAdapter()->select()
				->from($this->getTable('faq/faq_store'))
				->where('faq_id = ?', $object->getId());

		if ($data = $this->_getReadAdapter()->fetchAll($select)) {
				$storesArray = array();
				foreach ($data as $row) {
						$storesArray[] = $row['store_id'];
				}
				$object->setData('store_id', $storesArray);
		}

		return parent::_afterLoad($object);
	}
	
	/**
	 * Retrieve select object for load object data
	 *
	 * @param string $field
	 * @param mixed $value
	 * @return Zend_Db_Select
	 */
	protected function _getLoadSelect($field, $value, $object)
	{
		$select = parent::_getLoadSelect($field, $value, $object);

		if ($object->getStoreId()) {
				$select->join(array('cbs' => $this->getTable('faq/faq_store')), $this->getMainTable().'.faq_id = cbs.faq_id')
								->where('is_active=1 AND cbs.store_id in (0, ?) ', $object->getStoreId())
								->order('store_id DESC')
								->limit(1);
		}
		return $select;
	}
	
	/**
	 * Get store ids to which specified item is assigned
	 *
	 * @param int $id
	 * @return array
	 */
	public function lookupStoreIds($id)
	{
		return $this->_getReadAdapter()->fetchCol($this->_getReadAdapter()->select()
				->from($this->getTable('faq/faq_store'), 'store_id')
				->where("{$this->getIdFieldName()} = ?", $id)
		);
	}
	
	
	public function getQuestion ($category) {
		$store_id = Mage::app()->getStore()->getId();
		$faq_table = $this->getTable('faq/faq');
		$faq_category_table = $this->getTable('faq/category_item');
		$store_table = $this->getTable('faq/faq_store');
		$where = $this->_getReadAdapter()->quoteInto('category_id = ? AND ', $category->getData('category_id')).$this->_getReadAdapter()->quoteInto('is_active =?',1);
		$where2 = $this->_getReadAdapter()->quoteInto('category_id = ? ', $store_id);
		$condition = $this->_getReadAdapter()->quoteInto('faq_category.faq_id = faq.faq_id', $category);
		$condition2 = $this->_getReadAdapter()->quoteInto('faq_category.faq_id = store_table.faq_id', $store_id);
		$select = $this->_getReadAdapter()->select()->from(array('faq_category' => $faq_category_table))->join(array('faq'=>$faq_table),$condition)->where($where);
		$select = $this->_getReadAdapter()->select()->from(array('faq_category' => $faq_category_table))->join(array('store_table'=>$store_table),$condition2)->where($where2);
		$question_collection = $this->_getReadAdapter()->fetchAll($select);
		return $question_collection;
	}
	
	public function getAllQuestion () {
		$faq_table=$this->getTable('faq/faq');
		$faq_category_table=$this->getTable('faq/category_item');
		$faq_category_store_table=$this->getTable('faq/faq_category_store');
		$where = $this->_getReadAdapter()->quoteInto('is_active =?',1);
		$condition=$this->_getReadAdapter()->quoteInto('faq_category.faq_id =faq.faq_id', '');
		$condition2 = $this->_getReadAdapter()->quoteInto('category_store.category_id =faq.category_id', '');
		$select=$this->_getReadAdapter()->select()->from(array('faq_category'=>$faq_category_table))->join(array('faq'=>$faq_table),$condition)->where($where);
		$allQuestion=$this->_getReadAdapter()->fetchAll($select);
		return $allQuestion;
	}
	
	public function getAllFaqs($categories) {
		$storeIds = array(Mage::app()->getStore()->getId(), Mage_Core_Model_App::ADMIN_STORE_ID);
		$collection = Mage::getModel('faq/faq')->getCollection();
		$collection->getSelect()
			->join(array('faq_category' => Mage::getModel('core/resource')->getTableName('faq_category_item')), 'main_table.faq_id=faq_category.faq_id')
			->join(array('fstore' => Mage::getModel('core/resource')->getTableName('faq_store')), 'main_table.faq_id=fstore.faq_id')
			->where('faq_category.category_id IN (?)', $categories)
			->where('fstore.store_id IN (?)', $storeIds);
		
		return $collection;
	}
	
	public function getCategory ($faq) {
		$category_table=$this->getTable('faq/category');
		$faq_category_table=$this->getTable('faq/category_item');
		$where = $this->_getReadAdapter()->quoteInto('faq_id = ? AND ', $faq->getData('faq_id')).$this->_getReadAdapter()->quoteInto('is_active =?',1);
		$condition=$this->_getReadAdapter()->quoteInto('faq_category.category_id =category.category_id', '');
		$select=$this->_getReadAdapter()->select()->from(array('faq_category'=>$faq_category_table))->join(array('category'=>$category_table),$condition)->where($where)->order('category.category_id DESC');
		$category_collection=$this->_getReadAdapter()->fetchAll($select);
		return $category_collection;
	}
	
	public function getCategories(){
		$storeIds = Mage::app()->getStore()->getId();
		$collection = Mage::getModel('faq/category')->getCollection();
		$collection->getSelect()
			->join(array('fstore' => Mage::getModel('core/resource')->getTableName('faq_category_store')), 'main_table.category_id=fstore.category_id')
			->where('fstore.store_id = ?', $storeIds);
		return $collection;
	}
	
	public function getAllAvaliableCategories(){
		$collection = $this->getCategories();
		$categoriesIds = array();
		foreach($collection as $item){
			$categoriesIds[] = $item->getCategoryId();
		}
		return $categoriesIds;
	}
	
	public function getFAQIds(){
		$storeIds = array(Mage::app()->getStore()->getId(), Mage_Core_Model_App::ADMIN_STORE_ID);
		$collection = Mage::getModel('faq/faq')->getCollection();
		$collection->getSelect()
			->join(array('fstore' => Mage::getModel('core/resource')->getTableName('faq_store')), 'main_table.faq_id=fstore.faq_id')
			->where('fstore.store_id IN (?)', $storeIds);		
		
		$questionIds = array();
		foreach($collection as $item){
			$questionIds[] = $item->getFaqId();
		}
		
		return $questionIds;	
	}
	
	public function getAllQuestionFaq() {
		$faqIds = $this->getFAQIds();
		$categoryIds = $this->getAllAvaliableCategories();
		$table = $this->getTable('faq/category_item');
		$where = $this->_getReadAdapter()->quoteInto('category_id IN(?) ', $categoryIds);
		$select = $this->_getReadAdapter()->select()->from($table, array('faq_id'))->where($where);
		$faq = $this->_getReadAdapter()->fetchAll($select);
		
		$collection = Mage::getModel('faq/faq')->getCollection();
		
		if($faqIds){
			$collection->addFieldToFilter('faq_id', array('in', $faqIds));
		}
		
		return $collection;
	}
}