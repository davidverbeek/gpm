<?php
class Magebuzz_Faq_Block_Adminhtml_Faq_Renderer_Category extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
	public function render(Varien_Object $row){
		$category = Mage::getModel('faq/category_item')->getCollection()
			->addFieldToFilter('faq', $row->getId);
		$category->getSelect()
			->join(array('fcat' => Mage::getSingleton('core/resource')->getTableName('faq_category')), 'main_table.category_id=fcat.category_id', 'fcat.category_name');
		print_r($category);
		die('ss');
	}
}