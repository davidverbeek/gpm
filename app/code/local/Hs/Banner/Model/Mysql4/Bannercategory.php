<?php

class Hs_Banner_Model_Mysql4_Bannercategory extends Mage_Core_Model_Mysql4_Abstract
{
	protected function _construct()
	{
		$this->_init("banner/bannercategory", "id");
		//$this->_isPkAutoIncrement = false;
	}
}