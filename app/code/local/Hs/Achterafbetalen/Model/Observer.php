<?php

/**
 *  ICEPAY Advanced - Observer to save admin paymentmethods and save checkout payment
 * @version 1.2.0
 * @author Wouter van Tilburg
 * @author Olaf Abbenhuis
 * @copyright ICEPAY <www.icepay.com>
 *
 *  Disclaimer:
 *  The merchant is entitled to change de ICEPAY plug-in code,
 *  any changes will be at merchant's own risk.
 *  Requesting ICEPAY support for a modified plug-in will be
 *  charged in accordance with the standard ICEPAY tariffs.
 *
 */
class Hs_Achterafbetalen_Model_Observer extends Icepay_IceAdvanced_Model_Observer
{

    public $_currencyArr = array();
    public $_countryArr = array();
    public $_minimumAmountArr = array();
    public $_maximumAmountArr = array();
    private $_setting = array();
    private $_issuers = array();
    private $_value;
    private $_advancedSQL = null;
    private $_coreSQL = null;

    /**
     * Checks if an Icepay quote id is set, if so make the checkout session active
     * Note: This is done so cancelled orders no longer have an empty card upon returning
     *
     * @param Varien_Event_Observer $observer
     *
     * @since 1.2.0
     * @author Wouter van Tilburg
     * @return \Varien_Event_Observer
     */
    public function custom_quote_process(Varien_Event_Observer $observer)
    {
        $session = Mage::getSingleton('core/session');
        $quoteID = $session->getData('ic_quoteid');

        if (!is_null($quoteID)) {
            $quoteDate = $session->getData('ic_quotedate');

            $diff = abs(strtotime(date("Y-m-d H:i:s")) - strtotime($quoteDate));

            if ($diff < 360) {
                $observer['checkout_session']->setQuoteId($quoteID);
                $observer['checkout_session']->setLoadInactive(true);
            }
        }

        return $observer;
    }

    /**
     * sales_order_place_before
     *
     * @param Varien_Event_Observer $observer
     *
     * @since 1.2.0
     * @author Wouter van Tilburg
     * @return \Icepay_IceAdvanced_Model_Observer
     */
    public function sales_order_place_before(Varien_Event_Observer $observer)
    {
        $quote = $observer->getEvent()->getOrder()->getQuote();

        if (!$quote->getId()) {
            $quote = Mage::getSingleton('checkout/session')->getQuote();
        }

        $paymentMethodCode = $quote->getPayment()->getMethodInstance()->getCode();

        if ($paymentMethodCode !== 'achterafbetalen')
            return;

        $this->initAfterpayValidation($quote);
        return $this;
    }

    /**
     * Validate additional information (Only for Afterpay)
     *
     * @param Object $quote
     *
     * @since 1.2.0
     * @author Wouter van Tilburg
     * @throws Mage_Payment_Model_Info_Exception
     */
    public function initAfterpayValidation($observer)
    {
        $billingAddress = $observer->getBillingAddress();
        $shippingAddress = $observer->getShippingAddress();

        $billingCountry = $billingAddress->getCountry();

        $errorMessage = false;

        // Validate postcode
        if (!Mage::Helper('iceadvanced')->validatePostCode($billingCountry, $billingAddress->getPostcode()))
            $errorMessage = Mage::helper('iceadvanced')->__('It seems your billing address is incorrect, please confirm the postal code.');

        // Validate phonenumber
//        if (!Mage::Helper('iceadvanced')->validatePhonenumber($billingCountry, $billingAddress->getTelephone()))
//            $errorMessage = Mage::helper('iceadvanced')->__('It seems your billing address is incorrect, please confirm the phonenumber.');

        // Validate billing streetaddress
        if (!Mage::helper('iceadvanced')->validateStreetAddress($billingAddress->getStreet()))
            $errorMessage = Mage::helper('iceadvanced')->__('It seems your billing address is incorrect, please confirm the street and housenumber.');

        // Validate shipping streetaddress
        if (!Mage::helper('iceadvanced')->validateStreetAddress($shippingAddress->getStreet()))
            $errorMessage = Mage::helper('iceadvanced')->__('It seems your shipping address is incorrect, please confirm the street and housenumber.');

        if ($errorMessage) {
            Mage::getSingleton('checkout/session')->addError($errorMessage);
            Mage::app()->getResponse()->setRedirect(Mage::getUrl('checkout/cart'));
            session_write_close();
            Mage::app()->getResponse()->sendResponse();
            exit();
        }
    }

    /* Save order */

    public function sales_order_payment_place_end(Varien_Event_Observer $observer)
    {
        $payment = $observer->getPayment();
        $paymentMethodCode = $payment->getMethodInstance()->getCode();

        if ($paymentMethodCode !== 'achterafbetalen')
            return;

        if ($this->coreSQL()->isActive("iceadvanced"))
            $this->coreSQL()->savePayment($observer);

        return;
    }


    public function custom_order_status(Varien_Event_Observer $observer)
    {

        //$order = $observer->getEvent()->getInvoice()->getOrder();

        $order = $observer->getEvent()->getOrder();

//        echo 'order id is '.$order->getId();
//        print '<pre>';
//        var_dump($order->getData());
//        die;

        if (($order->getState() != Mage_Sales_Model_Order::STATE_PROCESSING) || ($order->getStatus() != "processing")) {
            return;
        }

        $payment = $order->getPayment();
        $paymentMethodCode = $payment->getMethodInstance()->getCode();

        if ($paymentMethodCode !== 'achterafbetalen') {
            return;
        }

        $order->setStatus('icecore_ok')
            ->addStatusHistoryComment(sprintf(Mage::helper("icecore")->__("Invoice Created is Created. Status has been changed: %s"), 'icecore_ok'))
            ->save();

        $order->setStatus('icecore_ok')->save();


    }


}
