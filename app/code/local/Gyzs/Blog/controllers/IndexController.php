<?php
/**
 * @copyright   Copyright (c) 2013 AZeBiz Co. LTD
 */
class Gyzs_Blog_IndexController extends Mage_Core_Controller_Front_Action {

	const XML_PATH_EMAIL_RECIPIENT  = 'faq/suggest/recipient_email';
  const XML_PATH_EMAIL_SENDER     = 'faq/suggest/sender_email_identity';
  const XML_PATH_EMAIL_TEMPLATE   = 'faq/suggest/email_template';

	public function indexAction() {	
	echo "string";
	exit;		
		$this->loadLayout();     
		$this->renderLayout();
	}
	
	public function searchAction() {
		$this->indexAction();
	}
	
	public function questionPostAction() {
		$post = $this->getRequest()->getPost();
		if($post){
			$translate = Mage::getSingleton('core/translate');
			$translate->setTranslateInline(false);
			try{
				// Save data
				Mage::helper('faq')->saveSuggestion($post);
				// Send email to admin
				$postObject = new Varien_Object();
				$postObject->setData($post);

				$error = false;
				
				if (!Zend_Validate::is(trim($post['name']) , 'NotEmpty')) {
						$error = true;
				}
				if (!Zend_Validate::is(trim($post['email']), 'EmailAddress')) {
						$error = true;
				}
				
				if ($error) {
						throw new Exception();
				}
				
				$mailTemplate = Mage::getModel('core/email_template');
				/* @var $mailTemplate Mage_Core_Model_Email_Template */
				$mailTemplate->setDesignConfig(array('area' => 'frontend'))
						->setReplyTo($post['email'])
						->sendTransactional(
								Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE),
								Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER),
								Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT),
								null,
								array('data' => $postObject)
						);

				if (!$mailTemplate->getSentSuccess()) {
						throw new Exception();
				}

				$translate->setTranslateInline(true);
				
				Mage::getSingleton('core/session')->addSuccess(Mage::helper('faq')->__('Thanks for your suggestion.'));
				Mage::app()->getResponse()->setRedirect($post['current_url']);
				return;
			}
			catch(Exception $e){
				Mage::log($e->getMessage,null,'suggest.log');
				Mage::getSingleton('core/session')->addError(Mage::helper('faq')->__('Unable to submit your request. Please, try again later.'));
				Mage::app()->getResponse()->setRedirect($post['current_url']);
				return;
			}
		}
	}
}