<?php
/**
 * @copyright   Copyright (c) 2013 AZeBiz Co. LTD
 */
class Magebuzz_Faq_Model_Faq extends Mage_Core_Model_Abstract
{
	
	protected $_eventPrefix = 'faq_item';

	public function _construct() {
		parent::_construct();
		$this->_init('faq/faq');
	}
	
	public function getQuestionId($object) {
		$condition = $this->getResource()->_getWriteAdapter()->quoteInto('faq_id = ?', $object->getData('category_id'));
		$this->_getWriteAdapter()->load($this->getTable('faq/category_item'), $condition);
		return $this;
	} 
	
	public function getFaqsByIds($faqIds){
		$faqCollection = $this->getCollection()
						->addFieldToFilter('is_active', 1)
						->addFieldToFilter('faq_id', array('in' => $faqIds));
		return $faqCollection;				
	}
	
	public function getCategoryIds() {
		$product_category_table = Mage::getSingleton('core/resource')->getTableName('faq_in_categories');
		$_readCollection = Mage::getSingleton('core/resource')->getConnection('core_read');
		$query = "SELECT faqCategories.category_id FROM ".$product_category_table." AS faqCategories WHERE `faq_id`='".$this->getId()."'";
		$categoryIDs = $_readCollection->fetchCol($query);
		return $categoryIDs;
		
	}
}