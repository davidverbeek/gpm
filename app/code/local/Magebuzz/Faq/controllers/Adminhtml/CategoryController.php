<?php
/**
 * @copyright   Copyright (c) 2013 AZeBiz Co. LTD
 */
class Magebuzz_Faq_Adminhtml_CategoryController extends Mage_Adminhtml_Controller_action {
	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('faq/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('faq/category')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('faq_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('faq/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('faq/adminhtml_category_edit'))
				->_addLeft($this->getLayout()->createBlock('faq/adminhtml_category_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('faq')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {
			$model = Mage::getModel('faq/category');					
			
			try {
				if(!isset($data['url_key']) || $data['url_key'] == '') {
					$data['url_key'] = $data['category_name'];
				}
				$data['url_key'] = Mage::helper('faq')->generateUrl($data['url_key']);
				
				$model->setData($data)
					->setId($this->getRequest()->getParam('id'));
				
				if ($model->getCreatedTime() == NULL || $model->getUpdateTime() == NULL) {
					$model->setCreatedTime(now())
						->setUpdateTime(now());
				} else {
					$model->setUpdateTime(now());
				}	
			
				
				$model->save(); 
				
				// Rewrite Url
				$rewriteModel = Mage::getModel('core/url_rewrite');				
				
				//$categoryUrlName = Mage::helper('faq')->generateUrl($model->getCategoryName());
				$request_path = 'faq/category/' . $data['url_key'];
				$id_path = 'faq/category/' . $model->getId();
				$rewriteModel->loadByIdPath($id_path);
				
				$rewriteModel->setData('id_path', $id_path);
				$rewriteModel->setData('request_path', $request_path);
				$rewriteModel->setData('target_path', 'faq/category/view/cid/'.$model->getId());
				$rewriteModel->save();
								
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('faq')->__('Category was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('faq')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				//delete rewrite url
				$model = Mage::getModel('faq/category');
				$categoryId = $this->getRequest()->getParam('id');
				$model->load($categoryId);
				
				$categoryUrlName = Mage::helper('faq')->generateUrl($model->getCategoryName());				
				$rewriteModel = Mage::getModel('core/url_rewrite');
				$request_path = 'faq/category/' . $categoryUrlName;
				$rewriteModel->loadByRequestPath($request_path);
				
				if($rewriteModel->getId()){
					$rewriteModel->delete();
				}		 
			
				$model = Mage::getModel('faq/category');				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Category was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $faqIds = $this->getRequest()->getParam('faq');
        if(!is_array($faqIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($faqIds as $faqId) {
                    $faq = Mage::getModel('faq/category')->load($faqId);
										//delete rewrite url
										$model = Mage::getModel('faq/category')->load($faqId);
										$categoryUrlName = Mage::helper('faq')->generateUrl($model->getCategoryName());
											
										$rewriteModel = Mage::getModel('core/url_rewrite');
										$request_path = 'faq/category/' . $categoryUrlName;
										$rewriteModel->loadByRequestPath($request_path);
										
										if($rewriteModel->getId()){
											$rewriteModel->delete();
										}	
										//delete category url
                    $faq->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($faqIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	
    public function massStatusAction()
    {
        $faqIds = $this->getRequest()->getParam('faq');
        if(!is_array($faqIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($faqIds as $faqId) {
                    $faq = Mage::getSingleton('faq/category')
                        ->load($faqId)
                        ->setIsActive($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($faqIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
}