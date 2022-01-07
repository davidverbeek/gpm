<?php

class Helios_Customerfaq_Adminhtml_CustomerfaqController extends Mage_Adminhtml_Controller_Action
{
		protected function _initAction()
		{
				$this->loadLayout()->_setActiveMenu("customerfaq/customerfaq")->_addBreadcrumb(Mage::helper("adminhtml")->__("Customerfaq  Manager"),Mage::helper("adminhtml")->__("Customerfaq Manager"));
				return $this;
		}
		public function indexAction() 
		{
			    $this->_title($this->__("Customerfaq"));
			    $this->_title($this->__("Manager Customerfaq"));

				$this->_initAction();
				$this->renderLayout();
		}
		public function editAction()
		{			    
			    $this->_title($this->__("Customerfaq"));
				$this->_title($this->__("Customerfaq"));
			    $this->_title($this->__("Edit Item"));
				
				$id = $this->getRequest()->getParam("id");
				$model = Mage::getModel("customerfaq/customerfaq")->load($id);
				if ($model->getId()) {
					Mage::register("customerfaq_data", $model);
					$this->loadLayout();
					$this->_setActiveMenu("customerfaq/customerfaq");
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Customerfaq Manager"), Mage::helper("adminhtml")->__("Customerfaq Manager"));
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Customerfaq Description"), Mage::helper("adminhtml")->__("Customerfaq Description"));
					$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
					$this->_addContent($this->getLayout()->createBlock("customerfaq/adminhtml_customerfaq_edit"))->_addLeft($this->getLayout()->createBlock("customerfaq/adminhtml_customerfaq_edit_tabs"));
					$this->renderLayout();
				} 
				else {
					Mage::getSingleton("adminhtml/session")->addError(Mage::helper("customerfaq")->__("Item does not exist."));
					$this->_redirect("*/*/");
				}
		}

		public function newAction()
		{

		$this->_title($this->__("Customerfaq"));
		$this->_title($this->__("Customerfaq"));
		$this->_title($this->__("New Item"));

        $id   = $this->getRequest()->getParam("id");
		$model  = Mage::getModel("customerfaq/customerfaq")->load($id);

		$data = Mage::getSingleton("adminhtml/session")->getFormData(true);
		if (!empty($data)) {
			$model->setData($data);
		}

		Mage::register("customerfaq_data", $model);

		$this->loadLayout();
		$this->_setActiveMenu("customerfaq/customerfaq");

		$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);

		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Customerfaq Manager"), Mage::helper("adminhtml")->__("Customerfaq Manager"));
		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Customerfaq Description"), Mage::helper("adminhtml")->__("Customerfaq Description"));


		$this->_addContent($this->getLayout()->createBlock("customerfaq/adminhtml_customerfaq_edit"))->_addLeft($this->getLayout()->createBlock("customerfaq/adminhtml_customerfaq_edit_tabs"));

		$this->renderLayout();

		}
		public function saveAction()
		{

			$post_data=$this->getRequest()->getPost();

			

				if ($post_data) {

					try {

						

						$model = Mage::getModel("customerfaq/customerfaq")
						->addData($post_data)
						->setId($this->getRequest()->getParam("id"))
						->save();

						$queid = Mage::getModel("customerfaq/customerfaq")->load($this->getRequest()->getParam("id"));
						$customerData = Mage::getModel('customer/customer')->load($queid->getCustomerId());
						
						  $postdata = array();
						  $postdata['email'] = $customerData->getEmail();
						  $postdata['firstname'] = $customerData->getFirstname();
						  $postdata['reply'] = $post_data['answer'];
						$translate = Mage::getSingleton('core/translate');  			
						$translate->setTranslateInline(false);
				
						$postObject = new Varien_Object();
						$postObject->setData($postdata);
						$emailTemplate = Mage::getModel('core/email_template')->loadDefault('custom_faq');	
						$emailTemplate->setSenderName($customerData->getFirstname());
            			$emailTemplate->setSenderEmail($customerData->getEmail());
            														
						
						$emailTemplate->send(Mage::getStoreConfig('tab2/general/email'),'',array('data' => $postObject));
						
						$model = Mage::getModel("customerfaq/customerfaq")
							->setMailSent(1)
							->setId($this->getRequest()->getParam("id"))
							->save();
							
						
						Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Mail was successfully sent'));
						
						

					
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						Mage::getSingleton("adminhtml/session")->setCustomerfaqData($this->getRequest()->getPost());
						$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
					return;
					}

				}
				$this->_redirect("*/*/");
		}



		public function deleteAction()
		{
				if( $this->getRequest()->getParam("id") > 0 ) {
					try {
						$model = Mage::getModel("customerfaq/customerfaq");
						$model->setId($this->getRequest()->getParam("id"))->delete();
						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item was successfully deleted"));
						$this->_redirect("*/*/");
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
					}
				}
				$this->_redirect("*/*/");
		}

		
		public function massRemoveAction()
		{
			try {
				$ids = $this->getRequest()->getPost('customerfaq_ids', array());
				foreach ($ids as $id) {
                      $model = Mage::getModel("customerfaq/customerfaq");
					  $model->setId($id)->delete();
				}
				Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item(s) was successfully removed"));
			}
			catch (Exception $e) {
				Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
			}
			$this->_redirect('*/*/');
		}
		
		
		
		public function massMoveAction()
		{
			
			try {
				$ids = $this->getRequest()->getPost('customerfaq_ids', array());
				$i=0;
				foreach ($ids as $id) {
                      $queid = Mage::getModel("customerfaq/customerfaq")->load($id);
					  if($queid->getIsMove()==0)
					  {
						  $data['question'] = $queid->getQuestion();
						  $data['answer'] = $queid->getAnswer();
						  $data['stores'] = 0;						   
						  $model = Mage::getModel('faq/faq');		
						  $model->setData($data);
						  if($queid->getProductId()!='')
						  {
							$model->setProducts($queid->getProductId().'=cG9zaXRpb249');
						  }
						  $model->save();
						  $queid->setIsMove(1);
						  $queid->save();
						  $i++;
					  }
					//  $model->setId($id)->delete();
				}
				if($i>0){
					Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item(s) was successfully moved"));
				}
				else
				{
					Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item(s) was successfully moved"));
				}
				
			}
			catch (Exception $e) {
				Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
			}
			$this->_redirect('*/*/');
		}
		
			
		/**
		 * Export order grid to CSV format
		 */
		public function exportCsvAction()
		{
			$fileName   = 'customerfaq.csv';
			$grid       = $this->getLayout()->createBlock('customerfaq/adminhtml_customerfaq_grid');
			$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
		} 
		/**
		 *  Export order grid to Excel XML format
		 */
		public function exportExcelAction()
		{
			$fileName   = 'customerfaq.xml';
			$grid       = $this->getLayout()->createBlock('customerfaq/adminhtml_customerfaq_grid');
			$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
		}
}
