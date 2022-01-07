<?php
/**
 * @copyright   Copyright (c) 2013 AZeBiz Co. LTD
 */
class Magebuzz_Faq_Model_Suggest extends Mage_Core_Model_Abstract {
	public function _construct() {
		parent::_construct();
		$this->_init('faq/suggest');
	}
}