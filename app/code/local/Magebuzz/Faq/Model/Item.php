<?php
/**
 * @copyright   Copyright (c) 2013 AZeBiz Co. LTD
 */
class Magebuzz_Faq_Model_Item extends Mage_Core_Model_Abstract {
	public function _construct() {
			parent::_construct();
			$this->_init('faq/item');
	}
	
	public function getAllFaqIds($id){
		$collection = $this->getCollection()
			->addFieldToFilter('category_id',$id)
			->getData();
		$faqIds = array();
		foreach ($collection as $item){
			$faqIds[] = $item['faq_id'];
		}
		
		return $faqIds;
	}
	
}