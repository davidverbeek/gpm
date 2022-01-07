<?php
/**
 * @version   1.0 12.0.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

class Hs_Featured_Adminhtml_FeaturedController extends Mage_Adminhtml_Controller_Action
{


	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('catalog/Featured')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Manage Featured Product'), Mage::helper('adminhtml')->__('Manage Featured Product'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->_addContent($this->getLayout()->createBlock('featured/adminhtml_featured'))
			->renderLayout();
	}
	public function moveToActiesAction() {

        $productIds = $this->getRequest()->getParam('featured');

        if(!is_array($productIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select Item(s)'));
        }else{
            try {
                $categoryId = "3098"; // live acties id changed on 30052018 - PP
                foreach ($productIds as $value) {
                    $_product = Mage::getModel('catalog/product')->load($value);
                    $categories = $_product->getCategoryIds();
                    $categories[] = $categoryId;
                    $_product->setCategoryIds($categories);
                    $_product->save();
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully Updated', count($productIds)
                    )
                );

            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } 
            $this->_redirect('*/*/index');
        }
    }

    public function massUpdateAction() {
        $productIds = $this->getRequest()->getParam('featured');

        if(!is_array($productIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select Item(s)'));
        } else {
            try {


                $postRequest= $this->getRequest()->getParam('pricevalues');
                $priceData=explode(",",$postRequest);

                foreach($priceData as $key=>$_pData){
                    $newPrice=explode('-',$_pData);
                    $price[trim($newPrice[0])]=$newPrice[1];
                }

                $SpecialPriceRequest= $this->getRequest()->getParam('specialpricevalues');
                $spriceData=explode(",",$SpecialPriceRequest);

                foreach($spriceData as $key=>$_spData){
                    $newSPrice=explode('-',$_spData);
                    $sprice[trim($newSPrice[0])]=$newSPrice[1];
                }

                $SpecialFromRequest= $this->getRequest()->getParam('specialfromdatesvalues');
                $spriceFromData=explode(",",$SpecialFromRequest);
                foreach($spriceFromData as $key=>$_spfData){
                    $newSFrom=explode('|',$_spfData);
                    $spriceFromDate[trim(str_replace('from_','',$newSFrom[0]))]=$newSFrom[1];
                }

                $SpecialToRequest= $this->getRequest()->getParam('specialtodatesvalues');
                $spriceToData=explode(",",$SpecialToRequest);

                foreach($spriceToData as $key=>$_sptData){
                    $newSTo=explode('|',$_sptData);
                    $spriceToDate[trim(str_replace('to_','',$newSTo[0]))]=$newSTo[1];
                }


                $labelRequest= $this->getRequest()->getParam('labelvalues');
                $labelData=explode(",",$labelRequest);

                foreach($labelData as $key=>$_lData){
                    $newLabel=explode('-',$_lData);
                    $label[trim($newLabel[0])]=$newLabel[1];
                }
/*echo "<pre>"; print_r($label); 
echo "<pre>"; print_r($sprice);
echo "<pre>"; print_r($spriceFromDate);
echo "<pre>"; print_r($spriceToDate);
exit;*/

                foreach ($productIds as $productId) {
                    $product = Mage::getModel('catalog/product')->load($productId);
                    $product->setPrice($price[$productId]);
                    if($sprice[$productId]){
                        $product->setSpecialPrice($sprice[$productId]);
                    }
                    if($spriceFromDate[$productId]){
                        $product->setSpecialFromDate($spriceFromDate[$productId]);
                    }
                    if($spriceToDate[$productId]) {
                        $product->setSpecialToDate($spriceToDate[$productId]);
                    }
                    if($label[$productId]) {
                        $product->setFeaturedlabel($label[$productId]);
                    }

                    $product->save();

                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully Updated', count($productIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
    public function updateRowAction() {
        $productId = (int) $this->getRequest()->getParam('id');
        $price = $this->getRequest()->getParam('price');
        $sprice = $this->getRequest()->getParam('sprice');
        $datefrom = $this->getRequest()->getParam('fromDate');
        $dateto = $this->getRequest()->getParam('toDate');
        $label = $this->getRequest()->getParam('label');

                    $product = Mage::getModel('catalog/product')->load($productId);
                    $product->setPrice($price);
                    $product->setSpecialPrice($sprice);
                    $product->setSpecialFromDate($datefrom);
                    $product->setSpecialToDate($dateto);
                    $product->setFeaturedlabel($label);

                    $product->save();

    }

    public function updateChangeAction()
    {
        $fieldId = (int) $this->getRequest()->getParam('id');
        $price = $this->getRequest()->getParam('price');

        if ($fieldId) {
            $product = Mage::getModel('catalog/product')->load($fieldId);
            $product->setPrice($price);
            $product->save();
        }
    }
    public function updateSpecialPriceAction()
    {
        $fieldId = (int) $this->getRequest()->getParam('id');
        $price = $this->getRequest()->getParam('price');

        if ($fieldId) {
            $product = Mage::getModel('catalog/product')->load($fieldId);
            $product->setSpecialPrice($price);
            $product->save();
        }
    }

    public function updateSpecialFromDateAction()
    {
        $fieldId = (int) $this->getRequest()->getParam('id');
        $datefrom = $this->getRequest()->getParam('fromDate');

        if ($fieldId) {
            $product = Mage::getModel('catalog/product')->load($fieldId);
            $product->setSpecialFromDate($datefrom);
            $product->save();
        }
    }

    public function updateSpecialToDateAction()
    {
        $fieldId = (int) $this->getRequest()->getParam('id');
        $datefrom = $this->getRequest()->getParam('toDate');
        if ($fieldId) {
            $product = Mage::getModel('catalog/product')->load($fieldId);
            $product->setSpecialToDate($datefrom);
            $product->save();
        }
    }


}
