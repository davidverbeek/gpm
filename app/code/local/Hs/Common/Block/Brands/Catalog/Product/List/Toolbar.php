<?php

/**
 * @author Parth Pabari
 * @copyright Copyright (c) 2018 Helios
 * @package Hs_Common
 */

class Hs_Common_Block_Brands_Catalog_Product_List_Toolbar extends Amasty_Brands_Block_Catalog_Product_List_Toolbar_Pure {

	/**
	 * Return current URL with rewrites and additional parameters
	 *
	 * @param array $params Query parameters
	 * @return string
	 */

	public function getPagerUrl($params=array()) {
		$brandId = $this->getRequest()->getParam('ambrand_id', null);
		$brand = Mage::getModel('ambrands/brand')->load($brandId);
		$res = parent::getPagerUrl($params);

		if (!$brand->getId()) {
			return $res;
		}

		$suffix = Mage::getStoreConfig('catalog/seo/category_url_suffix', Mage::app()->getStore());
		$url = ($suffix) ? $brand->getUrlKey() . $suffix : $brand->getUrlKey();

		return Mage::helper('ambrands')->rebuildUrl($res, $url);
	}


	public function setDefaultOrder($ignoredField) {
		$field = Mage::getStoreConfig('ambrands/brand_page/sort');
		if (isset($this->_availableOrder[$field])) {
			$this->_orderField = $field;
		}
		return $this;
	}
}
			