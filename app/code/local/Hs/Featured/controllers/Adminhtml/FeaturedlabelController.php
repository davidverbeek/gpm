<?php
/**
 * @version   1.0 12.0.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

class Hs_Featured_Adminhtml_FeaturedlabelController extends Mage_Adminhtml_Controller_Action
{


	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('catalog/Featured')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Manage Featured Label'), Mage::helper('adminhtml')->__('Manage Featured Label'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->_addContent($this->getLayout()->createBlock('featured/adminhtml_featuredlabel'))
			->renderLayout();
	}

    public function editAction()
    {
        $featuredlabelId     = $this->getRequest()->getParam('id');
        $featuredlabelModel  = Mage::getModel('featured/featuredlabel')->load($featuredlabelId);
 
        if ($featuredlabelModel->getId() || $featuredlabelId == 0) {
 
            Mage::register('featuredlabel_data', $featuredlabelModel);
 
            $this->loadLayout();
            $this->_setActiveMenu('catalog/geatured');
           
            $this->_addBreadcrumb(Mage::helper('featured')->__('Featured Label'), Mage::helper('featured')->__('Featured Label'));
            
           
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
           
            $this->_addContent($this->getLayout()->createBlock('featured/adminhtml_featuredlabel_edit'))
                 ->_addLeft($this->getLayout()->createBlock('featured/adminhtml_featuredlabel_edit_tabs'));
               
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('featured')->__('Featured label does not exist'));
            $this->_redirect('*/*/');
        }
    }
   
    public function newAction()
    {
        $this->_forward('edit');
    }
    
    public function saveAction()
    {
        if ( $this->getRequest()->getPost() ) {
            $featuredId=$this->getRequest()->getParam('id');
            
            try {
                $postData = $this->getRequest()->getPost();
                $featuredModel = Mage::getModel('featured/featuredlabel')->load($featuredId);
                
              // echo "<pre>".$featuredModel->getOptionId(); print_r($postData); exit;
                if(!$featuredId && $featuredModel->getOptionId()==$postData['option_id']){
                    Mage::getSingleton('adminhtml/session')->addError($this->__('Label entry is already exist'));
                    Mage::getSingleton('adminhtml/session')->setfeaturedlabelData($this->getRequest()->getPost());
                
                    $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                    return; 
                }
                
                
                $featuredModel
                   // ->setTitle($postData['title'])
                    //->setContent($postData['content'])
                    //->setStatus($postData['status'])
                    ->setData($postData)
                    ->setId($featuredId)
                    ->save();
               //echo "<pre>"; print_r($featuredModel->getData()); exit;
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Featured label was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setfeaturedlabelData(false);
 
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setfeaturedlabelData($this->getRequest()->getPost());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }
    public function deleteAction()
    {
        if( $this->getRequest()->getParam('id') > 0 ) {
            try {
                $featuredModel = Mage::getModel('featured/featuredlabel');

                $featuredModel->setId($this->getRequest()->getParam('id'))
                    ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Featured Label was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }
    /**
     * Product grid for AJAX request.
     * Sort and filter result for example.
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('featured/adminhtml_featuredlabel_grid')->toHtml()
        );
    }

    public function massDeleteAction()
    {
        $featuredIds = $this->getRequest()->getParam('featured');
        if (!is_array($featuredIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('featured')->__('Please select featured label(s)'));
        }
        else {
            try {
                foreach ($featuredIds as $featureId) {
                    $feature= Mage::getModel('featured/featuredlabel')->load($featureId);
                    $feature->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) were deleted', count($featuredIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }


}
