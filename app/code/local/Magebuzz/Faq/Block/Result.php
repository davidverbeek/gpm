<?php
class Magebuzz_Faq_Block_Result extends Mage_Core_Block_Template {
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
			
			$breadcrumbsBlock->addCrumb('faqsearch', array(
				'label'=>Mage::helper('faq')->__('FAQ Search Result'), 
				'title'=>Mage::helper('faq')->__('FAQ Search Result')				
			));
		}
		$this->getLayout()->getBlock('head')->setTitle(Mage::helper('faq')->__('FAQ Search Result'));
		return parent::_prepareLayout();
  }
	
	public function getCategoryResult() {
		$is_loop = array();
		$category_collection_array = array();
		$post = $this->getRequest()->getPost();
		$keywords = preg_split ('/[^a-z0-9]+/i',$post['keyword']);
		$j=0;
		foreach($keywords as $keyword) {
			$keyword=preg_replace('/[^a-z0-9]+/i','',$keyword);
			$category_collection = $this->getCategories();
			$catIds = $this->getAllAvaliableCategories();
			$category_collection = Mage::getSingleton('faq/category')->getCollection();
			$category_collection->addFieldToFilter('is_active', 1);
			$category_collection->addFieldToFilter('category_id',array('in', $catIds));
			$loop = false;
			
			foreach ($category_collection as $category) {
				$j=$j+1;
				$category_id = $category->getData('category_id');
				$is_loop[$j] = $category_id;
			}
			
			$n = count($is_loop);
			
			for ($i=0;$i<$n-1;$i++) {
				if($is_loop[$i+1]==$is_loop[$j]) {
					$loop=true;
				}
			}
			
			$category_check = $category_collection->getData();
			
			if(!$loop and !empty($category_check)) 
				$category_collection_array[$keyword] = $category_collection; 
		}
		return $category_collection_array;
	}
	
	
	public function getFaqs($cat_id) {
		$storeIds = array(Mage::app()->getStore()->getId(), Mage_Core_Model_App::ADMIN_STORE_ID);
		$collection = Mage::getModel('faq/faq')->getCollection();
		$collection->getSelect()
			->join(array('faq_category' => Mage::getModel('core/resource')->getTableName('faq_category_item')), 'main_table.faq_id=faq_category.faq_id')
			->join(array('fstore' => Mage::getModel('core/resource')->getTableName('faq_store')), 'main_table.faq_id=fstore.faq_id')
			->where('faq_category.category_id=?', $cat_id)
			->where('fstore.store_id IN (?)', $storeIds);
		
		return $collection;
	}
	
	public function getQuestionResult() {
		$is_loop = array();
		$question_collection_array = array();
		$post = $this->getRequest()->getPost();
		$keywords = preg_split ('/[^a-z0-9]+/i',$post['keyword']);
		$j = 0;
		
		
		foreach ($keywords as $keyword) {
			$keyword = preg_replace('/[^a-z0-9]+/i','',$keyword);
			$question_collection = $this->getSearchResult();
			$question_collection->addFieldToFilter('is_active',1);
			$question_collection->addFieldToFilter('question',array('like'=>'%'.$keyword.'%'));
			
			$loop = false;
			
			foreach ($question_collection as $question) {
				$j = $j+1;
				$faq_id = $question->getData('faq_id');
				$is_loop[$j] = $faq_id;
				
			}
			
			$n = count($is_loop);
			
			for ($i = 0; $i < $n-1; $i++){
				if($is_loop[$i+1]== $is_loop[$j]) {
					$loop=true;
				}
			}
			
			$question_check = $question_collection->getData();
			if(!$loop and !empty($question_check)) 
				$question_collection_array[$keyword] = $question_collection; 
		}
		
		return $question_collection_array;
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
				$result[] = $data->getFaqId();
			}
		}
		$collection = Mage::getModel('faq/faq')->getCollection()
										->addFieldToFilter('faq_id', array('in', $result));
		return $collection;
	}
	
	public function getQuestion($category) {
		$questions=Mage::getModel('faq/faq')->getResource()->getQuestion($category);
		return $questions;
	}
	
	public function getCategory($question) {
		$category=Mage::getModel('faq/faq')->getResource()->getCategory($question);
		return $category;
	}
	
	public function getQuestionUrl($id) {
		$question = Mage::getModel('faq/faq')->load($id);
		$url_key = $question->getUrlKey();
		
		return $this->getUrl('faq/question/'. $url_key, array());
	}
	
}