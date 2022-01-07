<?php

class Hs_Banner_Block_Adminhtml_Banner_Edit_Tab_Category extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Categories
{
	protected $_categoryIds;
	protected $_selectedNodes = null;

	public function __construct()
	{
		parent::__construct();
		$this->setTemplate('banner/category.phtml');
	}

	protected function getCategoryIds()
	{
		return $this->getCategorytab();
	}

	public function isReadonly()
	{
		return false;
	}

	public function getIdsString()
	{
		return implode(',', $this->getCategoryIds());
	}

	public function getCategorytab()
	{
		return Mage::registry('categorytab_data');
	}
}