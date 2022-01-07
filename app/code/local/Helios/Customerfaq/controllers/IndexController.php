<?php
class Helios_Customerfaq_IndexController extends Mage_Core_Controller_Front_Action{
    public function IndexAction() {
      
	  $this->loadLayout();   
	  $this->getLayout()->getBlock("head")->setTitle($this->__("Titlename"));
	        $breadcrumbs = $this->getLayout()->getBlock("breadcrumbs");
      $breadcrumbs->addCrumb("home", array(
                "label" => $this->__("Home Page"),
                "title" => $this->__("Home Page"),
                "link"  => Mage::getBaseUrl()
		   ));

      $breadcrumbs->addCrumb("titlename", array(
                "label" => $this->__("Titlename"),
                "title" => $this->__("Titlename")
		   ));

      $this->renderLayout(); 
	  
    }
	
	
	  public function SaveAction() {
	  
		$data = $this->getRequest()->getPost();
		$value = array_key_exists('product_url', $data) ? $data['product_url'] : '';

		try {
				$model = Mage::getModel('customerfaq/customerfaq');
				$model->setData($data);
				$model->setCreatedAt(date('Y-m-d H:i:s'));
		    	$model->save();
		    	
		    	Mage::getSingleton('core/session')->addSuccess($this->__('FAQ was send successfully'));
				if(!empty($value))
				{
					$this->_redirectUrl($value);
				}
				else
				{
					$this->_redirectUrl(Mage::getBaseUrl());					
				}		    	
				return;
		} catch (Exception $e) {
              
                Mage::getSingleton('customer/session')->addError($this->__('Unable to submit your request. Please, try again later'));
                $this->_redirectUrl($value);
                return;
            }
	    		
		
		
	  }
}