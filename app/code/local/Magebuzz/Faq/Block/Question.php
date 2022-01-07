<?php
class Magebuzz_Faq_Block_Question extends Mage_Core_Block_Template {
	public function _prepareLayout() {
		return parent::_prepareLayout();
  }
	
	public function getPostAction() {
		return Mage::getUrl('faq/index/questionPost');
	}
}