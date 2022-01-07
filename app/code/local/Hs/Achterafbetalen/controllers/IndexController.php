<?php

class Hs_Achterafbetalen_IndexController extends Mage_Core_Controller_Front_Action
{

    public function payAction()
    {

        //logic for accepting the payment
        $pay = Mage::getModel('achterafbetalen/pay');
        $result = $pay->getCheckoutResult();
        Mage::log($result, null, 'result.log', true);
        if (!is_array($result)) {

            /*** get last order and cancel the order **/
            Mage::helper('achterafbetalen')->cancelOrder($result);
            /*** get last order and cancel the order ends here **/

            //at this point i think we need to remove all the addresses associated with the quote in order to
            // solve the problem of double total.

            /*** remove cart address patch starts here ***/

            $quoteID = Mage::getSingleton('checkout/session')->getQuoteId();
            Mage::getSingleton('core/session')->setData('ic_quoteid', $quoteID);
            Mage::getSingleton('core/session')->setData('ic_quotedate', date("Y-m-d H:i:s"));


            $quote = Mage::getSingleton('checkout/session')->getQuote();
            if ($quote->getIsMultiShipping() == 1) {

                /* Unset the multi Fees data start */
                $multifeesession = Mage::getSingleton('checkout/session');
                $multifeesession->setMultifees();
                $multifeesession->setDetailsMultifees();
                $multifeesession->setStoreMultifees();
                $multifeesession->setOnlyOneAutoadd(false);
                /* Unset the multi Fees data end */


                if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
                    Mage::getSingleton('checkout/session')->getQuote()->unsShippingAddress()->save();
                } else {
                    Mage::getSingleton('checkout/session')->getQuote()->unsShippingAddress()->save();
                }

                Mage::getSingleton('checkout/session')->getQuote()->removeAllAddresses();

                //Mage::getSingleton('checkout/session')->getQuote()->save();
                //unset selected payment method data
                Mage::getSingleton('core/session')->unsSelectedPayment();

            }


            /*** remove cart address patch ends here ***/

            Mage::log($result, null, 'error_payment_redirect.log', true);
            Mage::getSingleton('checkout/session')->setErrorMessage(sprintf($this->__("The payment provider has returned the following error message: %s"), $result));
            Mage::getSingleton('checkout/session')->addError(sprintf($this->__("The payment provider has returned the following error message: %s"), $result));
            parent::_redirect('checkout/cart');
        } else {

            $quote = Mage::getSingleton('checkout/session')->getQuote();

            $quote->setIsActive(0)->save();

            Mage::log($quote, null, 'sucess_payment_redirect.log', true);
            Mage::log($result, null, 'sucess_payment_redirect.log', true);
            $checkoutResult = (isset($result['CheckoutExtendedResult'])) ? $result['CheckoutExtendedResult'] : $result['CheckoutResult'];

            $this->_result($checkoutResult);

            //$this->getResponse()->setBody($this->__("Redirecting"))->setRedirect($checkoutResult->PaymentScreenURL);
        }

    }

    protected function _result($checkoutResult)
    {

        $responseData = json_decode(json_encode($checkoutResult), true);

        Mage::log($responseData, null, 'sucess_payment_redirect.log', true);

        //Mage::getModel('achterafbetalen/icepay_postback')->handle($responseData);
        Mage::getModel('achterafbetalen/icepay_result')->handle($responseData);
    }

}
