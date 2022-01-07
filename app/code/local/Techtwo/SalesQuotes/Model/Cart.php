<?php
require_once("Mage/Checkout/Model/Cart.php");

class Techtwo_SalesQuotes_Model_Cart extends Mage_Checkout_Model_Cart
{

    /**
     * Get quote object associated with cart. By default it is current customer session quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if (!$this->hasData('quote')) {
            $quote = $this->getCheckoutSession()->getQuote();
            if ($quote && $quote->hasData('is_cart_auto') && ($quote->getIsCartAuto() == 0)) {
                // quote mode, replace cart quote by empty one
                $quote->setStoreId(Mage::app()->getStore()->getId());
				//$quote = Mage::getModel('sales/quote')
                 //   ->setStoreId(Mage::app()->getStore()->getId());
                // attach to customer if needed
                $customerSession = Mage::getSingleton('customer/session');
                if ($customerSession->isLoggedIn()) {
                    $quote->setCustomer($customerSession->getCustomer());
                }
                else {
                    $quote->setIsCheckoutCart(true);
                    Mage::dispatchEvent('checkout_quote_init', array('quote'=>$quote));
                }

            }
            $this->setData('quote', $quote);
        }
        return $this->_getData('quote');
    }

}
