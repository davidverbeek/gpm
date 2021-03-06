<?php
/**
 * @copyright   Copyright (c) 2013 AZeBiz Co. LTD
 */
class Gyzs_Blog_Adminhtml_BlogController extends Mage_Adminhtml_Controller_action {
	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('wordpress/items')
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

		$model  = Mage::getModel('wordpress/post')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}
            //echo "<pre>"; print_r($model->getData()); die;             
			Mage::register('blog_data', $model);
			// Mage::register('current_faq', $model);

			$this->loadLayout();
			$this->_setActiveMenu('wprdpress/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('blog/adminhtml_blog_edit'))
				->_addLeft($this->getLayout()->createBlock('blog/adminhtml_blog_edit_tabs'));

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
            try {
                
                
                $blogId=$this->getRequest()->getParam('id');
                
                $stores=$data['stores'];
//exit;
                $connection = Mage::getSingleton('core/resource')->getConnection('core_write');

                $connection->beginTransaction();

                $links = $this->getRequest()->getPost('links');
                if (isset($links['products']) ) {
                    $tableName=Mage::getSingleton('core/resource')->getTableName('wordpress_association');
                    $blogProduct=Mage::helper('adminhtml/js')->decodeGridSerializedInput($links['products']);

                    if($blogProduct){
                        foreach($blogProduct as $key=>$val){
                            
                            foreach($stores as $_store){

                                $select = $connection->select()
                                ->from($tableName, array('*')) 
                                ->where('wordpress_object_id=?',$blogId)              
                                ->where('object_id=?',$key)
                                ->where('store_id=?', $_store)
                                ->where('type_id=?','1');
                                //echo $select; exit;

                                $rowArray =$connection->fetchRow($select);
                                if(!$rowArray){
                                    $__fields = array();
                                    $__fields['wordpress_object_id'] = $blogId;
                                    $__fields['object_id'] = $key;
                                    $__fields['type_id'] = '1';
                                    $__fields['store_id'] = $_store;
                                    $__fields['position']=$val['position'];
                                    $connection->insert($tableName, $__fields);
                                }
                            }
                        }
                    }
                }
                
                /* for category post wise*/
                $categoryIds=  array_unique(explode(',',$data['category_ids']));
				/* to fetch existing data */
				$tableName=Mage::getSingleton('core/resource')->getTableName('wordpress_association');
				$select = $connection->select()
							->from($tableName, array('object_id')) 
							->where('wordpress_object_id=?',$blogId)              
							->where('type_id=?','3');
				$rowArray =$connection->fetchAll($select);  
				$existing_category = array();
				if($rowArray) {
					foreach($rowArray as $_row){
					   $existing_category[]=$_row['object_id'];
							   
					}
					array_unique($existing_category);
				}
				
				//echo "<pre>"; print_r($categoryIds);
                if (count($categoryIds)>0 ) {
					foreach($categoryIds as $key=>$val){
                        if($val!=""){
							if(!in_array($val,$existing_category)) {
								foreach($stores as $_store){

									$select = $connection->select()
									->from($tableName, array('*')) 
									->where('wordpress_object_id=?',$blogId)              
									->where('object_id=?',$val)
									->where('store_id=?', $_store)
									->where('type_id=?','3');
									//echo $select; exit;

									$rowArray =$connection->fetchRow($select);
									if(!$rowArray){
										$__fields = array();
										$__fields['wordpress_object_id'] = $blogId;
										$__fields['object_id'] = $val;
										$__fields['type_id'] = '3';
										$__fields['store_id'] = $_store;
										$__fields['position']='4444';
										$connection->insert($tableName, $__fields);
									}
								}
							}
                        }
						if(($key_val = array_search($val, $existing_category)) !== false) {
							unset($existing_category[$key_val]);
						}
                    }
				}
				
				if(!empty($existing_category)) {
					foreach($existing_category as $cat_id) {
						$query = "DELETE FROM {$tableName}  WHERE object_id = ". (int)$cat_id." AND wordpress_object_id = ".(int)$blogId;
						$connection->query($query);
					}	
				}
                
                
                $connection->commit();
				

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('blog')->__('Blog was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                        $this->_redirect('*/*/edit', array('id' => $blogId));
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
			
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('blog')->__('Unable to find FAQ to save'));
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
            $this->getLayout()->createBlock('blog/adminhtml_blog_edit_tab_categories')
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
	public function productAction()
    {
            $id     = $this->getRequest()->getParam('id');

		$model  = Mage::getModel('wordpress/post')->load($id);

		if ($model->getId()) {
                    Mage::register('blog_data', $model);
                }
            
       // $this->_initAction();
        $this->loadLayout();
        $this->getLayout()->getBlock('catalog.product.edit.tab.blog')
            ->setBlogProducts($this->getRequest()->getPost('blog_products', null));
        $this->renderLayout();
        
        /*$gridBlock = $this->getLayout()->createBlock('blog/adminhtml_faq_edit_tab_grid')
            ->setGridUrl($this->getUrl('//gridOnly', array('_current' => true, 'gridOnlyBlock' => 'grid')));
        
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
	    $this->_outputBlocks($gridBlock, $serializerBlock);*/
    }
    
    public function productGridAction()
    {
        $id     = $this->getRequest()->getParam('id');

		$model  = Mage::getModel('wordpress/post')->load($id);

		if ($model->getId()) {
                    Mage::register('blog_data', $model);
                }
        $this->loadLayout();
        $this->getLayout()->getBlock('catalog.product.edit.tab.blog')
            ->setBlogProducts($this->getRequest()->getPost('blog_products', null));
        $this->renderLayout();
    }

	
	
}