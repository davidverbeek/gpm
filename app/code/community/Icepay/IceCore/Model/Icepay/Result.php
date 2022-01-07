<?php

/**
 *  ICEPAY Core - Return page processing
 * @version 1.0.0
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
class Icepay_IceCore_Model_Icepay_Result
{

    protected $sqlModel;

    public function __construct()
    {
        $this->sqlModel = Mage::getModel('icecore/mysql4_iceCore');
    }

    public function handle(array $_vars)
    {

        if (count($_vars) == 0)
            die("ICEPAY result page installed correctly.");
        if (!$_vars['OrderID'])
            die("No orderID found");

        $order = Mage::getModel('sales/order');
        $order->loadByIncrementId(($_vars['OrderID'] == "DUMMY") ? $_vars['Reference'] : $_vars['OrderID'])
            ->addStatusHistoryComment(sprintf(Mage::helper("icecore")->__("Customer returned with status: %s"), $_vars['StatusCode']))
            ->save();

        switch (strtoupper($_vars['Status'])) {
            case "ERR":
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
                        // As per suniltalekar.hs,Earlier this line has been added to solved double shipping address related issue. which is now not able to reproduce.
                        // This code causes a double total issue for guest customer.
//                        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
//                            Mage::getSingleton('checkout/session')->getQuote()->unsShippingAddress()->save();
//                        }

                        try {
                            $quoteAddress = $quote->getShippingAddress();
                            foreach ($quoteAddress->getAllItems() as $quoteItem) {
                                $quoteItem->delete();
                            }
                        } catch (Exception $e) {
                            Mage::log("Guest subtotal issue Exception  " . $e->getMessage(), null, 'subtotal_issue.log', true);
                        }
                    }

                }

                $msg = sprintf(Mage::helper("icecore")->__("The payment provider has returned the following error message: %s"), Mage::helper("icecore")->__($_vars['Status'] . ": " . $_vars['StatusCode']));
                $url = 'checkout/cart';
                Mage::getSingleton('checkout/session')->addError($msg);
                break;
            case "OK":
            case "OPEN":
            default:
                Mage::getSingleton('checkout/session')->getQuote()->setIsActive(false)->save();
                //$url = 'checkout/onepage/success';
                $url = 'multistepcheckout/index/success';
        };

        /* Redirect based on store */
        Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl($url));
        $url = Mage::app()->getStore($order->getStoreId())->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, true) . $url;
        Mage::app()->getFrontController()->getResponse()->setRedirect($url);
    }

}