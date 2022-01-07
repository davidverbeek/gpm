<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Shopping cart item render block
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * @method Mage_Checkout_Block_Cart_Item_Renderer setProductName(string)
 * @method Mage_Checkout_Block_Cart_Item_Renderer setDeleteUrl(string)
 */
class Hs_Multistepcheckout_Model_Observer
{

    public function setShipping($evt)
    {
        $controller = $evt->getControllerAction();

        Mage::getSingleton('multistepcheckout/type_state')->unsCompleteStep(
            Hs_Multistepcheckout_Model_Type_State::STEP_CART_ITEM
        );

        Mage::getSingleton('multistepcheckout/type_state')->unsCompleteStep(
            Hs_Multistepcheckout_Model_Type_State::STEP_SELECT_ADDRESSES
        );
        Mage::getSingleton('multistepcheckout/type_state')->unsCompleteStep(
            Hs_Multistepcheckout_Model_Type_State::STEP_PRODUCT
        );
        Mage::getSingleton('multistepcheckout/type_state')->unsCompleteStep(
            Hs_Multistepcheckout_Model_Type_State::STEP_PAYMENT
        );
        Mage::getSingleton('multistepcheckout/type_state')->unsCompleteStep(
            Hs_Multistepcheckout_Model_Type_State::STEP_OVERVIEW
        );


        Mage::getSingleton('multistepcheckout/type_state')->setActiveStep(
            Hs_Multistepcheckout_Model_Type_State::STEP_CART_ITEM
        );


        //if(!Mage::getSingleton('checkout/type_onepage')->getQuote()->getShippingAddress()->getCountryId()  && Mage::getSingleton('checkout/type_onepage')->getQuote()->getItemsCount()){
        //if(!Mage::getSingleton('checkout/type_multishipping')->getQuote()->getShippingAddress()->getCountryId()  && Mage::getSingleton('checkout/type_multishipping')->getQuote()->getItemsCount()){
        //if(!Mage::getSingleton('checkout/cart')->getQuote()->getShippingAddress()->getCountryId()  && Mage::getSingleton('checkout/cart')->getQuote()->getItemsCount()){
        if (!Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getCountryId() && Mage::getSingleton('checkout/session')->getQuote()->getItemsCount()) {
            //if(Mage::getSingleton('checkout/cart')->getQuote()->getItemsCount()){

            $country_id = 'NL';
            $region_id = false;
            $country = Mage::getModel('directory/country')->loadByCode($country_id);
            $regions = $country->getRegions();
            if (sizeof($regions) > 0) {
                $region = $regions->getFirstItem();
                $region_id = $region->getId();
            }
            $customerSession = Mage::getSingleton("customer/session");
            if ($customerSession->isLoggedIn()) {
                $customerAddress = $customerSession->getCustomer()->getDefaultShippingAddress();
                if (empty($customerAddress)) {
                    $customerAddress = $customerSession->getCustomer()->getDefaultBillingAddress();
                }
                if (!empty($customerAddress)) {

                    if ($customerAddress->getId()) {
                        $customerCountry = $customerAddress->getCountryId();
                        $region_id = $customerAddress->getRegionId();
                        $region = $customerAddress->getRegion();
                        $quote = Mage::getSingleton('checkout/cart')->getQuote();
                        $shipping = $quote->getShippingAddress();
                        $shipping->setCountryId($customerCountry);
                        if ($region_id) {
                            $shipping->setRegionId($region_id);
                        }
                        if ($region) {
                            $shipping->setRegion($region);
                        }
                        $quote->save();

                        $this->addShippingRate();

                        $controller->getResponse()->setRedirect(Mage::getUrl('*/*/*', array('_current' => true)));
                    } else {


                        $quote = Mage::getSingleton('checkout/cart')->getQuote();
                        $shipping = $quote->getShippingAddress();
                        $shipping->setCountryId($country_id);
                        if ($region_id) {
                            $shipping->setRegionId($region_id);
                        }
                        $quote->save();
                        $this->addShippingRate();

                        $controller->getResponse()->setRedirect(Mage::getUrl('*/*/*', array('_current' => true)));
                    }
                }
            } else {
                $quote = Mage::getSingleton('checkout/session')->getQuote();
                $shippingDetail = $quote->getShippingAddress();
                $shippingDetail->setCountryId($country_id);
                if ($region_id) {
                    $shippingDetail->setRegionId($region_id);
                }


                $this->addShippingRate();
                $quote->save();

                //    $controller->getResponse()->setRedirect(Mage::getUrl('*/*/*',array('_current'=>true)));

            }
        }


    }

    public function addShippingRate()
    {
        /// get shipping rate
        // $quote = Mage::getSingleton('checkout/session')->getQuote();

        // $quote->getShippingAddress()->setCollectShippingRates(true);

        // $quote->getShippingAddress()->collectShippingRates();

        // $shippingGrouprates = $quote->getShippingAddress()->getGroupedAllShippingRates();
        //echo "<pre>"; print_r($shippingGrouprates); exit;

        // $rates = $quote->getShippingAddress()->getAllShippingRates();

        // $allowed_rates = array();

        // foreach ($shippingGrouprates as $code => $rates) {
        //     foreach ($rates as $rate) {
        //         array_push($allowed_rates, $rate->getCode());
        //     }
        // }


        // if (!in_array($this->_shippingCode, $allowed_rates) && count($allowed_rates) > 0) {
        //     $shippingCode = $allowed_rates[0];
        // }

        /*if (!empty($shippingCode))
        {
            $address = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress();
            if ($address->getCountryId() == '')
            $address->setCountryId($this->_country);
            if ($address->getCity() == '')
               $address->setCity('');
            if ($address->getPostcode() == '')
               $address->setPostcode('');
            if ($address->getRegionId() == '')
               $address->setRegionId('');
            if ($address->getRegion() == '')
               $address->setRegion('');

            $address->setShippingMethod($shippingCode)->setCollectShippingRates(true);

            Mage::getSingleton('checkout/session')->getQuote()->save();
        } */

    }

    public function salesConvertQuoteToOrder(Varien_Event_Observer $observer)
    {
		$quoteAddresses = Mage::getSingleton('checkout/session')->getQuote()->getAddressesCollection();
        foreach ($quoteAddresses as $data) {
            $address_type = $data->getAddressType();
            $qlsAddressData = array();
            if($address_type == 'qls_pickup') {
                $qlsAddressData['company'] = $data->getCompany();
                $qlsAddressData['street'] = $data->getStreet();
                $qlsAddressData['city'] = trim($data->getCity());
                $qlsAddressData['postcode'] = $data->getPostcode();
                $qlsAddressData['qls_servicepoint_code'] = $data->getQlsServicepointCode();
                break;
            }
        }
        if(sizeof($qlsAddressData) > 0) {
            $shippingAddress = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress();
            $shippingAddress->setStreet(array($qlsAddressData['street'][0],$qlsAddressData['street'][1]))
                ->setCompany($qlsAddressData['company'])
                ->setCity($qlsAddressData['city'])
                ->setPostcode($qlsAddressData['postcode'])
                ->setQlsServicepointCode($qlsAddressData['qls_servicepoint_code'])
                ->save();
        }
		
        //$expectedDate=$observer->getEvent()->getQuote()->getExpectedDeliveryDate();
        $expectedDate = Mage::getSingleton('checkout/session')->getExpectedDeliveryDateTime();
        $observer->getEvent()->getOrder()->setExpectedDeliveryDate($expectedDate);
        //echo $observer->getEvent()->getOrder()->getId();*/

        /*$order=$observer->getOrder();
        $quoteId=$order->getQuoteId();
        $quote=Mage::getModel('sales/quote')->load($quoteId);
        
        $expectedDate=$quote->getExpectedDeliveryDate();
        
        $order->setExpectedDeliveryDate($expectedDate);
        $order->save();*/
        //echo "<pre>"; print_r($observer->getEvent()->getOrder()->getData()); 
        //exit;

        /*
        
		$billingAddress = $observer->getEvent()->getQuote()->getBillingAddress();
			$shippingAddress = $observer->getEvent()->getQuote()->getShippingAddress();
			$basedOnAddress = Mage::helper('euvatgrouper')->getVatBasedOnAddress($billingAddress, $shippingAddress);
			if($this->_debug) Mage::log("[EUVAT] Using basedOnAddress: ".var_export($basedOnAddress->debug(),true), null, 'euvatenhanced.log');
			// Set the customer group for the specific order according to VAT status
			$orderGroupId = Mage::helper('euvatgrouper/customer')->getCustomerGroupForOrder($basedOnAddress);
			if(is_int($orderGroupId)) {
				$groupName = Mage::getModel('customer/group')->load($orderGroupId)->getCustomerGroupCode();
				if($this->_debug) Mage::log("[EUVAT] SETTING CUSTOMER GROUP ON ORDER: ".$orderGroupId." ($groupName)", null, 'euvatenhanced.log');
				$observer->getEvent()->getOrder()->setCustomerGroupId($orderGroupId);
                if(!Mage::registry('euvat_checkout_group_id'))
				    Mage::register('euvat_checkout_group_id', $orderGroupId);
			}

			if($this->_debug) Mage::log("[EUVAT] EVENT END: salesConvertQuoteToOrder--------------------", null, 'euvatenhanced.log');
	*/

        /**** same delivery logic start here **/


        $quote = $observer->getEvent()->getQuote();
        $sddAmount = $quote->getShippingAddress()->getSddAmount();
        //$quote->getSddAmount();

        if ($sddAmount > 0) {
            //set is_sdd flag in the system
            $observer->getEvent()->getOrder()->setExtraVerzendkosten('VANDAAG OPGEHAALD OM 12.00 (REDJEPAKKETJE)');
        }

        /**** same delivery logic ends here **/

    }

    public function addStoreIdToOrderItem(Varien_Event_Observer $observer)
    {

        /*
            Windsor circle was not able to get the store id in sales_flat_order_item table, this function is created to set the current store id in that table
        */
        $orderId = $observer->getEvent()->getOrder()->getId();
        $storeId = Mage::app()->getStore()->getStoreId();
        //Mage::log("Order id::".$orderId, null, 'storeid_save.log');
        if (isset($orderId)) {
            try {
                $order = Mage::getModel('sales/order')->load($orderId);
                $orderItems = $order->getAllItems();

                foreach ($orderItems as $orderItem) {
                    //Mage::log("Item ::".$orderItem->getItemId(), null, 'storeid_save.log');
                    $item = Mage::getModel('sales/order_item')->load($orderItem->getItemId());
                    $item->setStoreId($storeId);
                    $item->save();
                }
            } catch (Exception $e) {
                print_r($e);
                echo $e->getMessage();
            }
        }


        /**** same delivery logic start here **/
//        $order = $observer->getEvent()->getOrder();
//        $sddDescription = $order->getData('extra_verzendkosten');
//        if(!empty($sddDescription)) {
//            $order->addStatusHistoryComment($sddDescription, false);
//        }

        // unset sdd flag once order is place
        Mage::getSingleton('checkout/session')->setData('sdd_enabled', '');
        /**** same delivery logic start here **/
    }

    /*
        To Trigger an email if any manual products are in order.
    */

    public function sendEmailForManualProduct(Varien_Event_Observer $observer){
        $order = $observer->getEvent()->getOrder();
        $orderItems = $order->getAllItems();

        $hasManualProduct = 0;

        foreach ($orderItems as $orderItem) {
            if($orderItem->getManualproduct()){
                $hasManualProduct = 1;
            }
        }

        if($hasManualProduct){
            Mage::helper('common')->sendEmailForManualProduct($order);
        }

    }

    /*
        Added by Parth
        To set BaseCost in OrderItem to calculate Marge on Sales ordergrid.
    */
    public function addBaseCostToOrderItem(Varien_Event_Observer $observer)
    {

        $orderItem = $observer->getOrderItem();
        $orderItem->setBaseCost($orderItem->getProduct()->getCost());
    }

}
