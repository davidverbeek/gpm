<?php
class Magebuzz_Faq_Block_Faq extends Mage_Core_Block_Template {
	public function __construct() {
		parent::__construct();
  }
	
	public function _prepareLayout() {
		if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
			$breadcrumbsBlock->addCrumb('home', array(
				'label'=>Mage::helper('catalog')->__('Home'),
				'title'=>Mage::helper('catalog')->__('Go to Home Page'),
				'link'=>Mage::getBaseUrl()
			));
			
			
		}
		//$this->getLayout()->getBlock('head')->setTitle(Mage::helper('faq')->__('FAQ'));
		return parent::_prepareLayout();
  }
   
   
	public function getQuestion($category) {
		$questions=Mage::getModel('faq/faq')->getResource()->getQuestion($category);
		return $questions;
	}
	
	public function getAllQuestion() {
		$allQuestion=Mage::getModel('faq/faq')->getResource()->getAllQuestion();
		return $allQuestion;
	}
	
	public function getCategories(){
		$storeIds = array(Mage::app()->getStore()->getId(), Mage_Core_Model_App::ADMIN_STORE_ID);
		$collection = Mage::getModel('faq/category')->getCollection();
		$collection->getSelect()
			->join(array('fstore' => Mage::getModel('core/resource')->getTableName('faq_category_store')), 'main_table.category_id=fstore.category_id')
			->where('fstore.store_id IN (?)', $storeIds);
		return $collection;
	}
	
	public function getAllAvaliableCategories(){
		$collection = $this->getCategories();
		$categoriesIds = array();
		foreach($collection as $item){
			$categoriesIds[] = $item->getCategoryId();
		}
		return $categoriesIds;
	}
	
	public function getFaqsData($cat_id) {
		$storeIds = array(Mage::app()->getStore()->getId(), Mage_Core_Model_App::ADMIN_STORE_ID);
		$collection = Mage::getModel('faq/faq')->getCollection();
		$collection->getSelect()
			->join(array('faq_category' => Mage::getModel('core/resource')->getTableName('faq_category_item')), 'main_table.faq_id=faq_category.faq_id')
			->join(array('fstore' => Mage::getModel('core/resource')->getTableName('faq_store')), 'main_table.faq_id=fstore.faq_id')
			->where('faq_category.category_id=?', $cat_id)
			->where('fstore.store_id IN (?)', $storeIds);
		
		return $collection;
	}
	
	public function getSearchResult(){
		$categoriesIds = $this->getAllAvaliableCategories();
		$result = array();
		foreach ($categoriesIds as $catId){
			$catData = $this->getFaqsData($catId);
			foreach ($catData as $data){
				$result[] = $data;
			}
		}
		return $result;
	}
	
	public function getCategoryTitle(){
		$cat_id = $this->getRequest()->getParam('cid');
		$title = Mage::getModel('faq/category')->getTitle($cat_id);
		if($title == null || $cat_id == null)
			return null;
		return $title;	
	}
	
	public function getCategoryUrl($id) {
		$category = Mage::getModel('faq/category')->load($id);
		$url_key = $category->getUrlKey();
		return $this->getUrl('faq/category/'. $url_key, array());
	}
	
	public function getQuestionUrl($id) {
		$question = Mage::getModel('faq/faq')->load($id);
		$url_key = $question->getUrlKey();
		return $this->getUrl('faq/question/'. $url_key, array());
	}
}