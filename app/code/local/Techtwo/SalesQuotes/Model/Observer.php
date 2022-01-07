<?php

class Techtwo_SalesQuotes_Model_Observer
{
    /**
     * Observes "controller_action_predispatch_checkout_cart_index"
     * Purpose: replace checkout session quote by an empty one when user goes to cart
     */
    public function controller_action_predispatch_checkout_cart_index ($observer) {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        if ($quote) {

            // only quotes with is_cart_auto=1 can be displayed in the cart
            if (!$quote->getIsCartAuto()) {

                // reset cart
                $quote->setIsActive(0)->save();
                Mage::getSingleton('checkout/session')->clear();

            }
        }
    }


    /**
     * Observes controller_action_predispatch_checkout_index_index
     *
     * redirects to quote page if checkout is not allowed
     *
     * @param   Varien_Event_Observer $observer
     */
    public function redirectToQuotePage()
    {
        if(!Mage::helper('techtwo_salesquotes')->isAllowedCheckout()){
            Mage::getSingleton('checkout/session')->addError(Mage::helper('techtwo_salesquotes')->__('Sorry, Checkout is not available, you can only place a quote.'));
            header("Location: ".Mage::getUrl('salesquotes/quote/index'));
            exit;
        }
    }


    /**
     * Observes controller_action_predispatch_adminhtml_sales_order_create_save
     *
     * called when admin presses "Save" in the quote/order editor
     * set status quote to pending if the order marked as edit, no order is created
     *
     * @param   Varien_Event_Observer $observer
     */
    public function changeQuoteStatusToPending($observer)
    {
        $controllerAction = $observer->getControllerAction();

        // if the quote is not from our module, ignore this
        //if(Mage::getSingleton('adminhtml/sales_order_create')->getQuote()->getIsCartAuto()){
        //    die('isCartAuto');
        //    return;
        //}

        // save as quote
        if (($data = $controllerAction->getRequest()->getPost('order')) && $controllerAction->getRequest()->getPost('edit_send_quote')) {

            $quote_status = 'pending';

            Mage::getSingleton('adminhtml/sales_order_create')->importPostData($data);

            Mage::getSingleton('adminhtml/sales_order_create')->getQuote()->setStatus($quote_status)->setIsCartAuto(0);
            Mage::getSingleton('adminhtml/sales_order_create')->saveQuote();

            if (!empty($data['send_confirmation'])) {
                Mage::helper('techtwo_salesquotes')->sendNewQuoteEmail(Mage::getSingleton('adminhtml/sales_order_create')->getQuote());
            }

            if (Mage::getSingleton('adminhtml/session_quote')->getConvertOrderQuote() > 0) {
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('techtwo_salesquotes')->__('The quote has been updated'));
            }
            else {
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('techtwo_salesquotes')->__('The quote has been saved'));
            }
            header("Location: ".Mage::helper('adminhtml')->getUrl('adminhtml/salesquotes/index'));
            exit;

        // save as order
        }elseif($data = $controllerAction->getRequest()->getPost('order') && !$controllerAction->getRequest()->getPost('edit_send_quote')){
            // status must be "confirmed" to convert to order
            $quote = Mage::getSingleton('adminhtml/sales_order_create')->getQuote();
            if($quote && $quote->getIsCartAuto()==0 && $quote->getStatus()!='confirmed') {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('techtwo_salesquotes')->__('The quote must be confirmed in order to be converted to order'));
                header("Location: ".Mage::helper('adminhtml')->getUrl('adminhtml/salesquotes/index'));
                exit;
            }
        }
    }


    /**
     * Observes sales_convert_quote_to_order
     * (used to observe "controller_action_postdispatch_adminhtml_sales_order_create_save",
     * but that only worked for the backend)
     *
     * Purpose: set quote status to converted
     *
     * @param   Varien_Event_Observer $observer
     */
    public function changeQuoteStatusToConverted($observer)
    {
        if ($observer->getQuote() && !$observer->getQuote()->getIsCartAuto()) {
            $quote_status = 'converted';
            $observer->getQuote()->setStatus($quote_status);
            return;
        }
    }


    public function cartProductAddAfter($observer)
    {
        $item = $observer->getQuoteItem();
        if ($item->getParentItem()) {
            $item = $item->getParentItem();
        }

        $product = Mage::getModel('catalog/product')->load($item->getProductId());

        $mandatory = (int) ! (int)$product->getData('afwijkenidealeverpakking');

        /**
         * helios
         */
        $prodCollection = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('*')->addAttributeToFilter('entity_id',$product->getId());
        $priceFactor=$prodCollection->getFirstItem()->getData('idealeverpakking');

        $priceFactor =  str_replace(',','.',$product->getData('idealeverpakking'));
        if ($priceFactor <= 0) $priceFactor = $product->getData('prijsfactor');
        if ($priceFactor <= 0) $priceFactor = 1;
        $actualPrice = Mage::helper('tax')->getPrice($product, $product->getFinalPrice() * $priceFactor);
        if ($actualPrice > 0) {
            $verkoopeenheid = $product->getData('verkoopeenheid');
            if ('' != $verkoopeenheid) {
                $verkoopeenheid[0] = strtolower($verkoopeenheid[0]);
            } else {
                $verkoopeenheid = 'stuk';
            }

            $item->setUnitQty(1);
            if ( $mandatory != 0 ) {
                $item->setCustomPrice($actualPrice);
                $item->setOriginalCustomPrice($actualPrice);
				//$item->setRowTotal($actualPrice);
                $item->getProduct()->setIsSuperMode(true);
                $item->setUnitQty($priceFactor);
            }

            $item->setSalesUnit($verkoopeenheid);
        }
    }
}
