<?php
/**
 * @copyright   Copyright (c) 2013 AZeBiz Co. LTD
 */
class Magebuzz_Faq_Model_Mysql4_Suggest extends Mage_Core_Model_Mysql4_Abstract {
	protected function _construct() {
		$this->_init('faq/suggest', 'id');
	}
}