<?php

class Jaagers_Price_Model_Observer
{


    /**
     * Function: This function is used to recalculate special price once customer is logged in
     * @param Varien_Event_Observer $observer
     */
    public function customerLogin(Varien_Event_Observer $observer)
    {


        $customer = $observer->getCustomer();
        //check if customer have debterNumber
        $customerDebterNumber = $customer->getData('mavis_debiteurnummer');

        //if no debter number assigend then do not update the cart otherwise update the cart
        if (empty(trim($customerDebterNumber))) {
            return;
        }

        //set that customer is eligable for disacounter price
        Mage::getSingleton('customer/session')->setData('special_price_available', true);


        /* @param Mage_Checkout_Model_Cart */
        $cart = Mage::getSingleton('checkout/cart');
        $quote = $cart->getQuote();

        $items = $quote->getAllItems();
        //do not proceed further if quote item price is not valid
        if (count($items) <= 0) {
            return;
        }

        try {

            foreach ($items as $item) {

                $itemId = $item->getId();
                $buyRequest = $item->getBuyRequest();

                //remove item from cart and add item again so that discount price can be apply to the product
                $cart->removeItem($itemId);
                $cart->addProduct($item->getProduct(), $buyRequest);
            }

            /** (Mini cart)Patch to calculate price of the added item in the cart otherwise item price will be considered or set to 0 **/
            $url = Mage::helper('core/http')->getHttpReferer();
            $checkoutUrl = Mage::getUrl('multistepcheckout/index/login/');

            if ($checkoutUrl != $url) {
                $cart->init();

            }
            /** Patch end  **/

            $cart->save();

            Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
        } catch (Exception $e) {
            Mage::logException($e->getMessage());
        }

    }


    /**
     * Name: quoteMerge
     * Function: this function is used to re assign special price when customer login so that base price can be replace by debutoerprice.
     *  In this case we have first removed the product and again adding product into the cart to solve this problem.
     * @param Varien_Event_Observer $observer
     */
    public function quoteMerge(Varien_Event_Observer $observer)
    {


        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            return;
        }

        $isEligable = Mage::getSingleton('customer/session')->getData('special_price_available');
        if ($isEligable != true) {
            return;
        }

        Mage::getSingleton('customer/session')->unsetData('special_price_available');
        $customer = Mage::getSingleton('customer/session')->getCustomer();

        //check if customer have debterNumber
        $customerDebterNumber = $customer->getData('mavis_debiteurnummer');

        //if no debter number assigend then do not update the cart otherwise update the cart
        if (empty(trim($customerDebterNumber))) {
            return;
        }

        /* @param Mage_Checkout_Model_Cart */
        $quote = $observer->getEvent()->getQuote();
        $items = $quote->getAllVisibleItems();

        //do not proceed further if quote item price is not valid
        if (count($items) <= 0) {
            return;
        }
        try {

            foreach ($items as $item) {

                $itemId = $item->getItemId();

                if (empty($itemId)) {
                    continue;
                }

                $buyRequest = $item->getBuyRequest();
                //remove item from cart and add item again so that discount price can be apply to the product
                $quote->removeItem($itemId);
                $quote->addProduct($item->getProduct(), $buyRequest);
            }

            $quote->getShippingAddress()->collectShippingRates();
            $observer->getSource()->getShippingAddress()->collectShippingRates();

        } catch (Exception $e) {
            Mage::logException($e->getMessage());
        }

    }

}