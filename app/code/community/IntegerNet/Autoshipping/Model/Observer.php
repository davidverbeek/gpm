<?php
/**
 * integer_net Autoshipping Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Autoshipping
 * @copyright  Copyright (c) 2013 integer_net GmbH (http://www.integer-net.de/)
 * @license    http://opensource.org/licenses/gpl-3.0 GNU General Public License, version 3 (GPLv3)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 * @author     PRWD (http://www.prwd.co.uk)
 * */

class IntegerNet_Autoshipping_Model_Observer
{
    /**
     * @param Varien_Event_Observer $observer
     * @event controller_action_predispatch_checkout_cart_index
     */
    public function addShipping(Varien_Event_Observer $observer)
    { 
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
        
        if (! Mage::getStoreConfigFlag('autoshipping/settings/enabled')) {
            return;
        } 
        if (!($country = $this->_getCoreSession()->getAutoShippingCountry())) {
            $country = Mage::getStoreConfig('autoshipping/settings/country_id');
            $this->_getCoreSession()->setAutoShippingCountry($country);
        }

        $quote = $this->_getCheckoutSession()->getQuote();
        if (! $quote->hasItems()) {
            return;
        }

        $billingAddress = $quote->getBillingAddress();
        if (!$billingAddress->getCountryId()) {
            $billingAddress->setCountryId($country);
        }

        $shippingAddress = $quote->getShippingAddress(); 
        
        if(!$shippingAddress->getCountryId()){
            $shippingAddress->setCountryId($country);
        }
        $shippingAddress->setCollectShippingRates(true);
        //if($shippingAddress->getShippingMethod()){// && $shippingAddress->getCountryId() == $this->_getCoreSession()->getAutoShippingCountry()){
          //  return; //don't override a method that was previously set
        //}
        
        if (!$shippingAddress->getFreeMethodWeight()) {
            $shippingAddress->setFreeMethodWeight($shippingAddress->getWeight());
        }

        $shippingAddress->collectShippingRates();

        $rates = $shippingAddress->getGroupedAllShippingRates();

        if (count($rates)) {

            $topRates = reset($rates);
            foreach($topRates as $topRate) {

                /** @var Mage_Sales_Model_Quote_Address_Rate $topRate */
                $code = $topRate->code;

                if (in_array($topRate->getCarrier(), explode(',', Mage::getStoreConfig('autoshipping/settings/ignore_shipping_methods')))) {
                    continue;
                }

                try {
                    $shippingAddress->setShippingMethod($code);

                    $quote->save();

                    $this->_getCheckoutSession()->resetCheckout();

                } catch (Mage_Core_Exception $e) {
                    $this->_getCheckoutSession()->addError($e->getMessage());
                }
                catch (Exception $e) {
                    $this->_getCheckoutSession()->addException(
                        $e, Mage::helper('checkout')->__('Load customer quote error')
                    );
                }

                return;
            }
        }

        $errorMessage = Mage::helper('checkout')->__('We can not deliver your order to your delivery address for standard delivery rates.');
        $messageBlock = Mage::getModel('cms/block')->load('no_shipping_available_message');
        if($messageBlock->getIsActive()) {
            $errorMessage .= ' ' . $messageBlock->getContent();
        }

        $this->_getCheckoutSession()->addError($errorMessage);
    }

    /**
     * Show dropdown for country selection in cart before shipping cost
     *
     * @param Varien_Event_Observer $observer
     * @event core_block_abstract_to_html_before
     */
    public function beforeBlockToHtml($observer)
    {
        $block = $observer->getBlock();

        if (! Mage::getStoreConfigFlag('autoshipping/settings/show_country_selection_in_cart')) {
            return;
        }

        if ($block instanceof Mage_Tax_Block_Checkout_Shipping) {

            // show only on cart
            /*if (Mage::app()->getRequest()->getControllerName() != 'cart') {
                return;
            }*/

            // don't display if only 1 country allowed
            if (sizeof(explode(',', Mage::getStoreConfig('general/country/allow'))) <= 1) {
                return;
            }

            // replace total title
            $block->getTotal()->setTitle(
                $block->getLayout()->createBlock('autoshipping/country', 'checkout_cart_country_select')->toHtml()
            );
        }
    }

    /**
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * @return Mage_Core_Model_Session
     */
    protected function _getCoreSession()
    {
        return Mage::getSingleton('core/session');
    }
}
