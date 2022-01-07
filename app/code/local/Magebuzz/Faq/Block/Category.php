<?php
/**
 * @copyright   Copyright (c) 2013 AZeBiz Co. LTD
 */
 
class Magebuzz_Faq_Block_Category extends Mage_Core_Block_Template {
	protected $_faqCollection = null;
	
	public function __construct() {
		parent::__construct();
		$cat_id = $this->getRequest()->getParam('cid');
		if ($cat_id) {
			$cat = Mage::getModel('faq/category')->load($cat_id);
			Mage::register('current_faqcategory', $cat);
			if ($this->_faqCollection == null) {
				$storeIds = array(Mage::app()->getStore()->getId(), Mage_Core_Model_App::ADMIN_STORE_ID);
				$collection = Mage::getModel('faq/faq')->getCollection();
				$collection->getSelect()
					->join(array('faq_category' => Mage::getModel('core/resource')->getTableName('faq_category_item')), 'main_table.faq_id=faq_category.faq_id')
					->join(array('fstore' => Mage::getModel('core/resource')->getTableName('faq_store')), 'main_table.faq_id=fstore.faq_id')
					->where('faq_category.category_id=?', $cat_id)
					->where('fstore.store_id IN (?)', $storeIds);
				$this->_faqCollection = $collection;
			}
		}
		$this->setCollection($this->_faqCollection);
  }
	
	public function _prepareLayout() {
		if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
			$breadcrumbsBlock->addCrumb('home', array(
				'label'=>Mage::helper('catalog')->__('Home'),
				'title'=>Mage::helper('catalog')->__('Go to Home Page'),
				'link'=>Mage::getBaseUrl()
			));
			
			$breadcrumbsBlock->addCrumb('faq', array(
				'label' => Mage::helper('faq')->__('FAQ'), 
				'title' => Mage::helper('faq')->__('FAQ'),
				'link' => $this->getUrl('faq')
			));
			
			$breadcrumbsBlock->addCrumb('faqcat', array(
				'label'=> Mage::helper('faq')->__('FAQ Category'), 
				'title'=> Mage::helper('faq')->__('FAQ Category')				
			));
		}
		$this->getLayout()->getBlock('head')->setTitle(Mage::helper('faq')->__('FAQ Category'));
		return parent::_prepareLayout();
  }

	public function getFaqUrl($faq) {
		$url_key = $faq->getUrlKey();
		return $this->getUrl('faq/question/'. $url_key, array());
	}
	
	public function getFaqCategory() {
		return Mage::registry('current_faqcategory');
	}	
}