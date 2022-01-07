<?php

/**
 *  ICEPAY Advanced - Process payment
 *  @version 1.0.0
 *  @author Olaf Abbenhuis
 *  @copyright ICEPAY <www.icepay.com>
 *  
 *  Disclaimer:
 *  The merchant is entitled to change de ICEPAY plug-in code,
 *  any changes will be at merchant's own risk.
 *  Requesting ICEPAY support for a modified plug-in will be
 *  charged in accordance with the standard ICEPAY tariffs.
 * 
 */
class Icepay_IceAdvanced_ProcessingController extends Mage_Core_Controller_Front_Action {

    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function payAction()
    {
        $pay = Mage::getModel('iceadvanced/pay');
        $result = $pay->getCheckoutResult();
        //echo "<pre>"; print_r($result); exit;
		Mage::log(print_r($result,true),null,'icepay_urls.log');
        if (!is_array($result)) {
            Mage::log($result,null,'error_payment_redirect.log',true);
            Mage::getSingleton('checkout/session')->setErrorMessage(sprintf($this->__("The payment provider has returned the following error message: %s"), $result));
            Mage::getSingleton('checkout/session')->addError(sprintf($this->__("The payment provider has returned the following error message: %s"), $result));
            parent::_redirect('checkout/cart');
        } else {
			
            $checkoutResult = (isset($result['CheckoutExtendedResult'])) ? $result['CheckoutExtendedResult'] : $result['CheckoutResult'];
			
            $this->getResponse()->setBody($this->__("Redirecting"))->setRedirect($checkoutResult->PaymentScreenURL);
        }
    }

}
