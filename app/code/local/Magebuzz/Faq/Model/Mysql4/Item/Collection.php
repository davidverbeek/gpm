<?php
/**
 * @copyright   Copyright (c) 2013 AZeBiz Co. LTD
 */
class Magebuzz_Faq_Model_Mysql4_Item_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
	public function _construct() {
		parent::_construct();
		$this->_init('faq/item');
	}
}