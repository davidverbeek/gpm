<?php
/**
 * @copyright   Copyright (c) 2013 AZeBiz Co. LTD
 */
class Magebuzz_Faq_Block_Detail extends Mage_Core_Block_Template {
	protected $_faq = null;
	public function __construct() {
		parent::__construct();
		$faq_id = $this->getRequest()->getParam('id', false);
		if ($faq_id && $this->_faq == null) {
			$faq = Mage::getModel('faq/faq')->load($faq_id);
			Mage::register('current_faq', $faq);
		}
  }
	
	public function _prepareLayout() {
		if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
			$breadcrumbsBlock->addCrumb('home', array(
				'label'=>Mage::helper('catalog')->__('Home'),
				'title'=>Mage::helper('catalog')->__('Go to Home Page'),
				'link'=>Mage::getBaseUrl()
			));
			
			$breadcrumbsBlock->addCrumb('faq', array(
				'label'=>Mage::helper('faq')->__('FAQ'), 
				'title'=>Mage::helper('faq')->__('FAQ'),
				'link'=> $this->getUrl('faq')
			));
			
			$breadcrumbsBlock->addCrumb('faqdetail', array(
				'label'=>Mage::helper('faq')->__('FAQ Question'), 
				'title'=>Mage::helper('faq')->__('FAQ Question')				
			));
		}
		
		$this->getLayout()->getBlock('head')->setTitle(Mage::helper('faq')->__('FAQ'));
		return parent::_prepareLayout();
  }
	
	public function getFaq() {
		return Mage::registry('current_faq');
	}

	public function getOtherQuestions() {
		$cat_id = Mage::helper('faq')->getCategoryIdByFaqId($this->getFaq()->getId());
		$storeIds = array(Mage::app()->getStore()->getId(), Mage_Core_Model_App::ADMIN_STORE_ID);
		$collection = Mage::getModel('faq/faq')->getCollection();
		$collection->getSelect()
			->join(array('faq_category' => Mage::getModel('core/resource')->getTableName('faq_category_item')), 'main_table.faq_id=faq_category.faq_id')
			->join(array('fstore' => Mage::getModel('core/resource')->getTableName('faq_store')), 'main_table.faq_id=fstore.faq_id')
			->where('faq_category.category_id=?', $cat_id)
			->where('fstore.store_id IN (?)', $storeIds);
		return $collection;
	}
	
	public function getFaqUrl($faq) {
		$url_key = $faq->getUrlKey();
		return $this->getUrl('faq/question/'. $url_key, array());
	}
}