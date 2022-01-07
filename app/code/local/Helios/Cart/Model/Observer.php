<?php

class Helios_Cart_Model_Observer
{

	public function checkoutCartProductAddBefore(Varien_Event_Observer $observer)
    {
        $cart = $observer->getCart();


        $items = $cart->getItems();
        foreach ($items as $item) {


            $product = Mage::getModel('catalog/product')->load($item->getProductId());

            $afwijkenidealeverpakking = $product->getData('afwijkenidealeverpakking');


            $prijsfactor = $product->getData('prijsfactor');
            $idealeverpakking = (float)str_replace(',', '.', $product->getData('idealeverpakking'));
			$itemQty = $item->getQty();

            if ($afwijkenidealeverpakking == 1) {
                //echo "in if";
                $priceFactor = (float)1;
            } else {
                //echo "in else";
                $priceFactor = (isset($idealeverpakking) ? $idealeverpakking : 1);
				//$itemQty = ($item->getQty() * $priceFactor)/$prijsfactor;
				$itemQty = ($item->getQty() * $priceFactor);
            }
			
            $actualPrice = 0;
            $actualPrice = Mage::helper('tax')->getPrice($product, $product->getFinalPrice($itemQty) * $priceFactor);


            $unit = Mage::helper('featured')->getProductUnit($product->getData('verkoopeenheid'));
			
            if ($actualPrice > 0) {

                $salesUnit = Mage::helper('featured')->getStockUnit($item->getQty(), $unit);


                $item->setCustomPrice($actualPrice);
                $item->setOriginalCustomPrice($actualPrice);

                $item->getProduct()->setIsSuperMode(true);

                $item->setSalesUnit($salesUnit);
            }
        }

    }
	
    public function checkoutCartProductAddAfter(Varien_Event_Observer $observer)
    {
        //Mage::dispatchEvent('admin_session_user_login_success', array('user'=>$user));
        //$user = $observer->getEvent()->getUser();
        //$user->doSomething();
        $item = $observer->getQuoteItem();
        //Mage::log(print_r(get_class_methods($item),true),null,"cart.log");

        if ($item->getParentItem()) {
            $item = $item->getParentItem();
        }

        $product = Mage::getModel('catalog/product')->load($item->getProductId());

        $mandatory = (int)!(int)$product->getData('afwijkenidealeverpakking');

        /**
         * helios
         *
         * $prodCollection = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('*')->addAttributeToFilter('entity_id',$product->getId());
         * $priceFactor=(int) $prodCollection->getFirstItem()->getData('idealeverpakking');
         */

        /* Commmented By Ankita to fix cart price issue
         *
         * $priceFactor =  str_replace(',','.',$product->getData('idealeverpakking'));

         if ($priceFactor <= 0) $priceFactor = (int) $product->getData('prijsfactor');
         if ($priceFactor <= 0) $priceFactor = 1;
         */
        $prijsfactor = $product->getData('prijsfactor');
        $priceFactor = (isset($prijsfactor) ? (int)$product->getData('prijsfactor') : 1);
        $actualPrice = 0;
        $actualPrice = Mage::helper('tax')->getPrice($product, $product->getFinalPrice() * $priceFactor);

        $unit = Mage::helper('featured')->getProductUnit($product->getData('verkoopeenheid'));
        if ($actualPrice > 0) {

            $salesUnit = Mage::helper('featured')->getStockUnit($item->getQty(), $unit);


            $item->setUnitQty(1);
            if ($mandatory != 0) {
                $item->setCustomPrice($actualPrice);
                $item->setOriginalCustomPrice($actualPrice);
                //$item->setRowTotal($actualPrice);
                $item->getProduct()->setIsSuperMode(true);
                $item->setUnitQty($priceFactor);
            }
            $item->setSalesUnit($salesUnit);
        }
    }


    /**
     * Name: CustomerLogoutEvent
     * Function: this method is used to disable quote/remove item from cart once customer get logged out
     * @param Varien_Event_Observer $observer
     */
    public function CustomerLogoutEvent(Varien_Event_Observer $observer)
    {
        /* Need to execute the Observer before Segment_Analytics observer execute */

        try {
            /** @var  Mage_Sales_Model_Quote $customerQuote */
            $customerQuote = Mage::getSingleton('checkout/session')->getQuote();
//			$customerQuote->setReservedOrderId(null)->save();
            $customerQuote->setIsActive(0);
            $customerQuote->save();
        } catch (Exception $e) {
            Mage::logException($e);
        }

    }


    /**
     * Name: CustomerLogInEvent
     * Function: this method is used to remove guest customer address from session to avoid multiple address specification
     * @param Varien_Event_Observer $observer
     */
    public function CustomerLogInEvent(Varien_Event_Observer $observer)
    {
        $customerQuote = Mage::getSingleton('checkout/session')->getQuote();

        $addresses = $customerQuote->getAllShippingAddresses();
        if (count($addresses) >= 2) {
            foreach ($addresses as $address) {
                $customerAdd = $address->getCustomerAddressId();
                if (is_null($customerAdd)) {
                    $customerQuote->removeAddress($address->getAddressId());
                }
            }
        }
        $customerQuote->save();
        $customerQuote->setTotalsCollectedFlag(false)->collectTotals();

    }

}
