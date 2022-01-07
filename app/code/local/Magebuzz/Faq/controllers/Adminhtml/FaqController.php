<?php
/**
 * @copyright   Copyright (c) 2013 AZeBiz Co. LTD
 */
class Magebuzz_Faq_Adminhtml_FaqController extends Mage_Adminhtml_Controller_action {
	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('faq/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
			$this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
		}
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');

		$model  = Mage::getModel('faq/faq')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('faq_data', $model);
			// Mage::register('current_faq', $model);

			$this->loadLayout();
			$this->_setActiveMenu('faq/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('faq/adminhtml_faq_edit'))
				->_addLeft($this->getLayout()->createBlock('faq/adminhtml_faq_edit_tabs'));

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
			$data = $this->getRequest()->getPost();

			//Get all category id
			$categoryIds = $data['category_ids'];
			if($categoryIds) {
				if (is_string($categoryIds)) {
						$categoryIds = explode(',', $categoryIds);
				} elseif (!is_array($categoryIds)) {
					Mage::throwException(Mage::helper('catalog')->__('Invalid category IDs.'));
				}
				foreach ($categoryIds as $i => $v) {
						if (empty($v)) {
								unset($categoryIds[$i]);
						}
				}
				$categoryIds =	array_unique($categoryIds);
			}	
			
			$model = Mage::getModel('faq/faq');		
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));
			
			try {
				if(!isset($data['url_key']) || $data['url_key'] == '') {
					$data['url_key'] = $data['question'];
				}
				$data['url_key'] = Mage::helper('faq')->generateUrl($data['url_key']);
				
				$model->setData($data)
					->setId($this->getRequest()->getParam('id'));
				
				if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
					$model->setCreatedTime(now())
						->setUpdateTime(now());
				} else {
					$model->setUpdateTime(now());
				}				
				$model->save();
				if($categoryIds) {
					Mage::helper('faq')->saveFaqProductCategories($model->getId(), $categoryIds);
				}	
				/* rewrite url*/
				$rewriteModel = Mage::getModel('core/url_rewrite');
				
				//$url_key = Mage::helper('faq')->generateUrl($model->getQuestion());
				$id_path = 'faq/question/' . $model->getId();
				$rewriteModel->loadByIdPath($id_path);
				
				$rewriteModel->setData('id_path', 'faq/question/' . $model->getId());
				$rewriteModel->setData('request_path', 'faq/question/' . $data['url_key']);
				$rewriteModel->setData('target_path', 'faq/category/detail/id/'.$model->getId());
				$rewriteModel->save();
					
				//end rewrite url
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('faq')->__('FAQ was successfully saved'));
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
			
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('faq')->__('Unable to find FAQ to save'));
      $this->_redirect('*/*/');
  }
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('faq/faq');		
				//delete rewrite url
				$faqId = $this->getRequest()->getParam('id');
				$model->load($faqId);

				$question = Mage::helper('faq')->generateUrl($model->getQuestion());
				$rewriteModel = Mage::getModel('core/url_rewrite');
				$request_path = 'faq/question/' . $question;
				$rewriteModel->loadByRequestPath($request_path);
				if($rewriteModel->getId()){
					$rewriteModel->delete();
				}	 
				//delete faq
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
				
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('FAQ was successfully deleted'));
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
									$model = Mage::getModel('faq/faq')->load($faqId);	
									$question = Mage::helper('faq')->generateUrl($model->getQuestion());									
									$rewriteModel = Mage::getModel('core/url_rewrite');
									$request_path = 'faq/question/' . $question;
									$rewriteModel->loadByRequestPath($request_path);
									if($rewriteModel->getId()){
										$rewriteModel->delete();
									}	 
									$faq = Mage::getModel('faq/faq')->load($faqId);										
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
                    $faq = Mage::getSingleton('faq/faq')
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
		public function categoriesAction() {	
			$faqId = $this->getRequest()->getParam('id');
			$faq = Mage::getModel('faq/faq')->load($faqId);
			Mage::register('faq_data', $faq);
			$this->loadLayout();
			$this->renderLayout();
    }
		
		
		public function categoriesJsonAction()
    {
        $faqId = $this->getRequest()->getParam('id');
				$faq = Mage::getModel('faq/faq')->load($faqId);
				Mage::register('faq_data', $faq);

        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('faq/adminhtml_faq_edit_tab_categories')
                ->getCategoryChildrenJson($this->getRequest()->getParam('category'))
        );
    }
	
	
	
	protected function _createSerializerBlock($inputName, Mage_Adminhtml_Block_Widget_Grid $gridBlock, $productsArray)
    {
    	return $this->getLayout()->createBlock('faq/adminhtml_faq_edit_tab_ajax_serializer')
            ->setGridBlock($gridBlock)
            ->setProducts($productsArray)
            ->setInputElementName($inputName);
    }
    
	/**
     * Output specified blocks as a text list
     */
    protected function _outputBlocks()
    {
        $blocks = func_get_args();
        $output = $this->getLayout()->createBlock('adminhtml/text_list');
        foreach ($blocks as $block) {
            $output->insert($block, '', true);
        }
        
        $this->getResponse()->setBody($output->toHtml());
    }
    
    
	/**
     * Product grid for AJAX request
	 */
	 
	/*
	public function gridAction()
    {
		$this->getResponse()->setBody(
            $this->getLayout()->createBlock('faq/adminhtml_faq_edit_tab_grid')->toHtml()
        );
		//adminhtml/catalog_product_grid
        //$this->getLayout()->createBlock('faq/adminhtml_faq_edit_tab_grid');
    }
    */   
	
    /**
     * Get specified tab grid
     */
	 
    public function gridOnlyAction()
    {
        $this->_initAction();
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('faq/adminhtml_faq_edit_tab_' . $this->getRequest()->getParam('gridOnlyBlock'))
                ->toHtml()
        );
    }
    
	/**
	 * Products grid on block edit form
	 *
	 */
	public function gridAction()
    {
        $gridBlock = $this->getLayout()->createBlock('faq/adminhtml_faq_edit_tab_grid')
            ->setGridUrl($this->getUrl('*/*/gridOnly', array('_current' => true, 'gridOnlyBlock' => 'grid')));
        
        $pline = Mage::getModel('faq/faq')->load($this->getRequest()->getParam('id'))->getProducts(); 
		
		$products = array();
        
        if($pline!='')
        { 
        	$decoded = Mage::helper('faq')->decodeInput($pline);
        	$products_arr = explode('&',$pline);
        	foreach($products_arr as $p) { 
        		list($id,$pos) = explode('=',$p);
        		 $product = Mage::getModel('catalog/product')->load($id);
        		 $product->setPosition($decoded[$id]['position']);
        	     $products[] = $product;
        		 
        	}
        }       
		
        $serializerBlock = $this->_createSerializerBlock('products', $gridBlock, $products);
	    $this->_outputBlocks($gridBlock, $serializerBlock);
    }

	
	
}