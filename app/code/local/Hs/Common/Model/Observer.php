<?php

class Hs_Common_Model_Observer extends Mage_Core_Model_Observer {

	public function addCategoryToBreadcrumbs(Varien_Event_Observer $observer) {

		$category = Mage::registry('current_category');

		if ($category) {
			if($category->hasChildren()){
				Mage::unregister('current_category');
			}
		}

		if (Mage::registry('current_category')) {
			return;
		}

		$product = $observer->getProduct();
		$product->setDoNotUseCategoryId(false);
		$categoryIds = $product->getCategoryIds();

		if (count($categoryIds)) {
			$categories = Mage::getModel('catalog/category')->getCollection()
				->addAttributeToSelect('*')
				->addAttributeToFilter('entity_id', $categoryIds)
				->addAttributeToFilter('is_active', 1);

			$categories->getSelect()->order('level DESC')->limit(1);

			Mage::register('current_category', $categories->getFirstItem());
		}

	}

}