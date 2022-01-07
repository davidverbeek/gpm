<?php

class Magebuzz_Faq_Block_Adminhtml_Faq_Edit_Tab_Categories extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Categories {

	protected $_categoryIds;
  protected $_selectedNodes = null;
	
	public function __construct() {
    parent::__construct();
		$this->setTemplate('faq/categories.phtml');
	}
	
	protected function getCategoryIds()
	{
			return $this->getFaq()->getCategoryIds();
	}
	
	public function isReadonly()
	{
			return false;
	}
  
	
	public function getIdsString()
	{
			return implode(',', $this->getCategoryIds());
	}
		
	public function getFaq() {
		return Mage::registry('faq_data');
	}
}