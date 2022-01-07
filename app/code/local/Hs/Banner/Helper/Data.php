<?php

class Hs_Banner_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getCategoryBanner($category_id = NULL)
	{
		if (!empty($category_id)) {

			$collection = Mage::getModel('banner/banner')->getCollection();
			$collection->getSelect()
				->join(
					array(
						'banner_category' => $collection->getTable('banner/bannercategory')
					),
					'main_table.banner_id = banner_category.banner_id',
					array()
				)
				->where(
					"banner_category.category_id = " . $category_id, "main_table.status = 1"
				);

			return $collection;
		}
	}
}
	 