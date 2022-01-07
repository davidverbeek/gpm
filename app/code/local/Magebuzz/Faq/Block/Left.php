<?php
/**
 * @copyright   Copyright (c) 2013 AZeBiz Co. LTD
 */
 
class Magebuzz_Faq_Block_Left extends Mage_Core_Block_Template {
	public function _prepareLayout() {
		return parent::_prepareLayout();
  }
	
	public function getFaqCategories() {
		$storeIds = array(Mage::app()->getStore()->getId(), Mage_Core_Model_App::ADMIN_STORE_ID);
		$collection = Mage::getModel('faq/category')->getCollection();
		$collection->getSelect()
			->join(array('fstore' => Mage::getModel('core/resource')->getTableName('faq_category_store')), 'main_table.category_id=fstore.category_id')
			->where('fstore.store_id IN (?)', $storeIds);
		return $collection;
	}

	public function getCategoryUrl($cat) {
		$url_key = $cat->getUrlKey();
		return $this->getUrl('faq/category/'. $url_key, array());
	}
	
	
}