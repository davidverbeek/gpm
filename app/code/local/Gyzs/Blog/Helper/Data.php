<?php
/**
 * @copyright   Copyright (c) 2013 AZeBiz Co. LTD
 */
class Gyzs_Blog_Helper_Data extends Mage_Core_Helper_Abstract {
	const XML_PATH_GET_CONFIG_USING_JAVASCRIPT 	= 'faq/general/using_javascript';
	const XML_PATH_SHOW_SUGGEST_FORM 	= 'faq/suggest/show_suggest_form';
	
	public function getUseJavascript(){
		return (int)Mage::getStoreConfig(self::XML_PATH_GET_CONFIG_USING_JAVASCRIPT);
	}
	
	public function showSuggestForm() {
		return (int)Mage::getStoreConfig(self::XML_PATH_SHOW_SUGGEST_FORM);
	}
	
	public function generateUrl($string) {
		$identifier = preg_replace('#[^0-9a-z]+#i', '-', Mage::helper('catalog/product_url')->format($string));
    $identifier = strtolower($identifier);
    $identifier = trim($identifier, '-');
    return $identifier;	
	}
	
	public function checkUrlRewriteHasExist($tagert_path){
		$oUrlRewriteCollection = Mage::getModel('core/url_rewrite')
    ->getCollection()
    ->addFieldToFilter('target_path', $tagert_path)
		->getFirstItem();
		$id = $oUrlRewriteCollection->getId();
		$model = Mage::getModel('core/url_rewrite');
		if (count($oUrlRewriteCollection) > 0) {
			$data = $model->load($id)->delete();
		}
	}
	
	public function convertArrayToString(){
		$array = array("firstname"=>"John","lastname"=>"doe");
    $json = json_encode($array);
    $phpStringArray = str_replace(array("{","}",":"), array("array(","}","=>"), $json);
    echo $phpStringArray;
	}
	
	public function getCategoryIdByFaqId($faq_id) {
		$object = Mage::getModel('faq/item')->getCollection()
			->addFieldToFilter('faq_id', $faq_id)
			->getFirstItem();
		return $object->getCategoryId();
	}
	
	public function saveFaqProductCategories($id, $categoryIds) {
		$faq_in_categories = $this->_getTableName('faq_in_categories');	
		
		foreach ($categoryIds as $index => $category_id) {
			$query = "INSERT INTO ".$faq_in_categories."(`faq_id`,`category_id`) VALUES ( '".$id."', '".$category_id."')";
			Mage::getSingleton('core/resource')->getConnection('core_write')->query($query);
		}
	}
	
	public function getProductFaqIds($categoryIds) {
		$categoryIds = implode(',', $categoryIds);
		$faq_in_categories = $this->_getTableName('faq_in_categories');
		$query = "SELECT productFaq.faq_id FROM ".$faq_in_categories." AS productFaq WHERE `category_id` IN (".$categoryIds.")";
		$faqIds = $this->_getReadConnection()->fetchCol($query);
		$faqIds = array_unique($faqIds);
		return $faqIds;
	}
	
	public function getProFaqIds($proid) {
		$faq_in_products = $this->_getTableName('faq');
		 $query = "SELECT faq_id FROM ".$faq_in_products." WHERE `products` LIKE '".$proid."%'";
		$faqIds = $this->_getReadConnection()->fetchCol($query);
		 $query1 = "SELECT faq_id FROM ".$faq_in_products." WHERE `products` LIKE '%&".$proid."%'";
		$faqIds1 = $this->_getReadConnection()->fetchCol($query1);
		$finalfaqIds = array_merge($faqIds,$faqIds1);
		$finalfaqIds = array_unique($finalfaqIds);
		return $finalfaqIds;
	}
	
	
	public function saveSuggestion($data) {
		$model = Mage::getModel('faq/suggest');
		if($data) {
			$model->setName($data['name']);
			$model->setEmail($data['email']);
			$model->setPhone($data['telephone']);
			$model->setMessage($data['message']);
			$model->setRequestFreeSample((int)$data['request_free_sample']);
			$model->setStatus(0);
		}
		$model->save();	
	}
	
	public function convertSuggestToFaq($question) {
		$model = Mage::getModel('faq/faq');
		if($question != '') {
			$model->setQuestion($question);
			$model->setIsActive(1);
			$model->setCreatedTime(now());
			$model->setUpdateTime(now());
		}
		$model->save();
	}
	
	protected function _getReadConnection() {
		return Mage::getSingleton('core/resource')->getConnection('core_read');
	}
	
	protected function _getWriteConnection() {
		return Mage::getSingleton('core/resource')->getConnection('core_write');
	}
	
	protected function _getTableName($name) {
		return Mage::getSingleton('core/resource')->getTableName($name);
	}
	public function decodeInput($encoded)
    {
        parse_str($encoded, $data);
        foreach($data as $key=>$value) {
            parse_str(base64_decode($value), $data[$key]);
        }
        return $data;
    }
}