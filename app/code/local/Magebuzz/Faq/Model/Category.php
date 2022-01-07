<?php
/**
 * @copyright   Copyright (c) 2013 AZeBiz Co. LTD
 */
class Magebuzz_Faq_Model_Category extends Mage_Core_Model_Abstract
{
	protected function _construct() {
		$this->_init('faq/category');
	}  
	
	public function getTitle($cat_id){
		$categoryInfo = $this->getCollection()
			->addFieldToFilter('category_id', $cat_id)
			->getFirstItem()
			->getData();
		if($categoryInfo == null)
			return null;
		return $categoryInfo['category_name'];
	}
	
}
