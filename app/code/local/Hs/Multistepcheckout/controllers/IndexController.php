<?php

class Hs_Multistepcheckout_IndexController extends Mage_Core_Controller_Front_Action
{

    public function preDispatch()
    {
        parent::preDispatch();

        if ($this->getFlag('', 'redirectLogin')) {
            return $this;
        }

        $checkoutSessionQuote = $this->_getCheckoutSession()->getQuote();
        $checkoutSessionQuote->setIsMultiShipping(false);

        return $this;
    }

    protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }

    protected function _getCheckout()
    {
        return Mage::getSingleton('multistepcheckout/type_onepage');
    }

    protected function _getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    protected function _getState()
    {
        return Mage::getSingleton('multistepcheckout/type_state');
    }


    public function updatecartItemAction()
    {

        $params = $this->getRequest()->getParams();

        if(!isset($params) || empty($params) ){
            $this->_redirect('checkout/cart', array('_secure' => true));
            return;
        }
        
        $cart = $this->_getCart();
        $id = (int)$this->getRequest()->getParam('itemId');

        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $quoteItem = $cart->getQuote()->getItemById($id);
            if (!$quoteItem) {
                Mage::throwException($this->__('Quote item is not found.'));
            }

            $item = $cart->updateItem($id, new Varien_Object($params));
            $cart->save();
            $this->_getCheckoutSession()->setCartWasUpdated(true);

            Mage::dispatchEvent('checkout_cart_update_item_complete',
                array('item' => $item, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );

            if (!$cart->getQuote()->getHasError()) {
                $message = $this->__(
                    '%s was updated in your shopping cart.',
                    Mage::helper('core')->htmlEscape($item->getProduct()->getName())
                );
                $response["message"] = $message;
                $response["success"] = 1;
                $response["item_id"] = $item->getId();

                //Get Layout update content
                $layout = $this->getLayout();
                if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                    $layout->getUpdate()
                    ->addHandle('default')
                    ->addHandle('checkout_cart_index')
                    ->addHandle('customer_logged_in')
                    ->load();
                } else {
                    $layout->getUpdate()
                    ->addHandle('default')
                    ->addHandle('checkout_cart_index')
                    ->addHandle('customer_logged_out')
                    ->load();
                }
                $layout->generateXml()->generateBlocks();
                //$total = $layout->getBlock('cart.totals')->toHtml();
                $total = $layout->getBlock('checkout.cart.totals')->toHtml();
                $itemBlock = $layout->getBlock('checkout.cart')->toHtml();

                $response["totalBlock"] = trim($total);
                $response["itemBlock"] = trim($itemBlock);

            }
            //  return $result;
            //                  }
        } catch (Mage_Core_Exception $e) {
            if ($this->_getCheckoutSession()->getUseNotice(true)) {
                $this->_getCheckoutSession()->addNotice($e->getMessage());
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_getCheckoutSession()->addError($message);
                }
            }

        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }

    public function checkEmailAction()
    {
        $bool = 0;
        $email = $this->getRequest()->getParam('email');
        try {

            $customer = Mage::getModel('customer/customer');
            $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
            $customer->loadByEmail($email);

            if ($customer->getId()) {
                $bool = 1;
            }
            $jsonStatus = 200;

            $info = array("status" => $bool);

            $this->getResponse()->setBody(json_encode($info))->setHttpResponseCode($jsonStatus)->setHeader('Content-type', 'application/json', true);
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        return $this;
    }

    public function loginAction()
    {
        if (!$this->_validateStep('address')) {
            return;
        }

        if (!Mage::helper('checkout/cart')->getItemsCount()) {
            $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
            return;
        }


        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            $session = Mage::getSingleton('customer/session');
            $session->setBeforeAuthUrl(Mage::helper('core/url')->getCurrentUrl()); /* set redirecting session  */


        }

        $this->_getState()->setActiveStep(
            Hs_Multistepcheckout_Model_Type_State::STEP_SELECT_ADDRESSES
        );

        $this->_getState()->setCompleteStep(
            Hs_Multistepcheckout_Model_Type_State::STEP_CART_ITEM
        );

        /* Added By ST */

        $this->_getState()->unsCompleteStep(
            Hs_Multistepcheckout_Model_Type_State::STEP_PRODUCT
        );

        $this->_getState()->unsCompleteStep(
            Hs_Multistepcheckout_Model_Type_State::STEP_PAYMENT
        );
        /* Added By ST */

        $this->_getState()->unsCompleteStep(
            Hs_Multistepcheckout_Model_Type_State::STEP_SELECT_ADDRESSES
        );

        // echo "<pre>"; print_r(Mage::getSingleton('checkout/session')->getData()); exit;
        $this->loadLayout();
        $this->_initLayoutMessages('checkout/session');
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

    public function registerAction()
    {
        if (!$this->_validateStep('address')) {
            return;
        }

        if (!Mage::helper('checkout/cart')->getItemsCount()) {
            $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
            return;
        }


        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            $session = Mage::getSingleton('customer/session');
            $session->setBeforeAuthUrl(Mage::helper('core/url')->getCurrentUrl()); /* set redirecting session  */


        }

        $this->_getState()->setActiveStep(
            Hs_Multistepcheckout_Model_Type_State::STEP_SELECT_ADDRESSES
        );

        $this->_getState()->setCompleteStep(
            Hs_Multistepcheckout_Model_Type_State::STEP_CART_ITEM
        );

        /* Added By ST */

        $this->_getState()->unsCompleteStep(
            Hs_Multistepcheckout_Model_Type_State::STEP_PRODUCT
        );

        $this->_getState()->unsCompleteStep(
            Hs_Multistepcheckout_Model_Type_State::STEP_PAYMENT
        );
        /* Added By ST */

        $this->_getState()->unsCompleteStep(
            Hs_Multistepcheckout_Model_Type_State::STEP_SELECT_ADDRESSES
        );

        // echo "<pre>"; print_r(Mage::getSingleton('checkout/session')->getData()); exit;
        $this->loadLayout();
        $this->_initLayoutMessages('checkout/session');
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

    public function guestAction()
    {
        if (!Mage::helper('checkout/cart')->getItemsCount()) {
            $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
            return;
        }

        if (!$this->_validateStep('address')) {
            return;
        }


        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            $session = Mage::getSingleton('customer/session');
            $session->setBeforeAuthUrl(Mage::helper('core/url')->getCurrentUrl()); /* set redirecting session  */
        }

        $this->_getState()->setActiveStep(
            Hs_Multistepcheckout_Model_Type_State::STEP_SELECT_ADDRESSES
        );

        $this->_getState()->setCompleteStep(
            Hs_Multistepcheckout_Model_Type_State::STEP_CART_ITEM
        );

        $this->_getState()->unsCompleteStep(
            Hs_Multistepcheckout_Model_Type_State::STEP_SELECT_ADDRESSES
        );

        $this->loadLayout();
        $this->_initLayoutMessages('checkout/session');
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

    public function saveAddressAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('billing', array());

            // postcode missing retrive from 2 field for billing -- parth
            $post_4 = $this->getRequest()->getPost('billing:postcode_input');
            $post_2 = $this->getRequest()->getPost('billing:postcode_inputdegit');

            if (!$data['postcode']) {
                $data['postcode'] = $post_4 . $post_2;
            }

            if (isset($data['telephonecode'])) {
                $data['telephone'] = $data['telephonecode'] . "-" . $data['telephone'];
            }
            if ($data['country_id'] != 'NL' && isset($data['housenumber'])) {
                $data['street'][1] = $data['housenumber'];
            }
            $data['checkout_method'] = $data['method_post'];

            //save checkout method
            if (!empty($data['checkout_method'])) {
                try {

                    $this->_getCheckout()->saveCheckoutMethod($data['checkout_method']);
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }

            /* Customer create**/
            if (array_key_exists('method_post', $data) && $data['method_post'] == 'register') {
                $websiteId = Mage::app()->getWebsite()->getId();
                $store = Mage::app()->getStore();

                $customerData = Mage::getModel("customer/customer")->setWebsiteId(Mage::app()->getWebsite()->getId())->loadByEmail($data['email']);

                if (count($customerData->getData()) == 0) {
                    $customer = Mage::getModel("customer/customer");
                    $customer->setData($data)
                        ->setWebsiteId($websiteId)
                        ->setStore($store)
                        ->setPassword($data['customer_password']);

                    $customer->save();
                } else {
                    Mage::getModel('core/session')->addError($this->__('Email Id already exists'));
                    $url = Mage::getUrl('multistepcheckout/index/login');
                    Mage::app()->getFrontController()->getResponse()->setRedirect($url)->sendResponse();
                    exit;

                }
                Mage::getSingleton('customer/session')->loginById($customer->getId());

            }/* custome Create End*/

            // NewsLetter Subscription from checkout step - PP 13062018
            if($data['is_subscribed'] && isset($data['is_subscribed'])){
                $result = $this->_getCheckout()->subscribeNewsletter($data);
            }

            $customerAddressId = (isset($data['allshipping']))?$data['allshipping']:'';

            if (isset($data['email'])) {
                $data['email'] = trim($data['email']);
            }

            // // Added by Parth - existing address select for billing
            // Prifix Set
            $data['prefix'] = $this->getRequest()->getPost('billing:Aanhef');
            if ($data['address_id']) {
                Mage::getSingleton('customer/session')->setAddressId($data['address_id']);
            }

            $result = $this->_getCheckout()->saveBilling($data, $customerAddressId);

            /** set the billing address for the next step - Added by P.P - code added on 31-3-2017 **/
            $use_for_shipping = $data['use_for_shipping'];
            if (!$use_for_shipping) {
                $dataShipping = $this->getRequest()->getPost('shipping', array());
                $dataShipping['email'] = $data['email'];
                $customerAddressId = $this->getRequest()->getPost('selected_shipping');
                $dataShipping['address_id'] = $customerAddressId;

                // postcode missing retrive from 2 field for shipping -- parth
                $post_4 = $this->getRequest()->getPost('shipping:postcode_input');
                $post_2 = $this->getRequest()->getPost('shipping:postcode_inputdegit');

                if (isset($dataShipping['postcode']) && empty($dataShipping['postcode'])) {
                    $dataShipping['postcode'] = $post_4 . $post_2;
                }

                // set shipping prefix;
                $dataShipping['prefix'] = $this->getRequest()->getPost('shipping:Aanhef');


                if (isset($dataShipping['telephonecode'])) {
                    $dataShipping['telephone'] = $dataShipping['telephonecode'] . "-" . $dataShipping['telephone'];
                }
                if (isset($dataShipping['housenumber']) && $dataShipping['country_id'] != 'NL') {
                    $dataShipping['street'][1] = $dataShipping['housenumber'];
                }
                $dataShipping['checkout_method'] = $data['method_post'];

                // Added by Parth - set post shipping address
                $resultshipping = $this->_getCheckout()->saveShipping($dataShipping, $customerAddressId);

                // Added by Parth - existing address select for shipping either new or existing
                if ($resultshipping['customerAddressId']) {
                    Mage::getSingleton('customer/session')->setAddressId($resultshipping['customerAddressId']);
                }
            }

            if (count($result) == 0 && !array_key_exists('error', $result)) {
                $this->_redirect('*/index/product', array('_secure' => true));
            } else {
                foreach ($result['message'] as $value) {
                    $this->_getCheckoutSession()->addError($value);
                }
                $refererUrl = $this->_getRefererUrl();
                $this->getResponse()->setRedirect($refererUrl);
                return;
            }

            //SJD++ 10052018 START, To set first available shipping method or throw error if shipping not possible
            $quote = $this->_getCheckoutSession()->getQuote();
            if (!$quote->hasItems()) {
                return;
            }

            $shippingAddress = $quote->getShippingAddress();
            $shippingAddress->setCollectShippingRates(true)->collectShippingRates();
            $shippingRates = $shippingAddress->getGroupedAllShippingRates();

            if(!count($shippingRates)) {
                $errorMessage = $this->__('We can not deliver your order to your delivery address for standard delivery rates.');
                $messageBlock = $this->getLayout()->createBlock('cms/block')->setBlockId('no_shipping_available_message');
                if($messageBlock) {
                    $errorMessage .= ' ' . $messageBlock->toHtml();
                }

                $this->_getCheckoutSession()->addError($errorMessage);
                $this->_redirect('*/index/register', array('_secure' => true));
                return;
            }

            foreach ($shippingRates as $code => $rates) {
                foreach ($rates as $rate) {
                    $quote->setShippingMethod($rate->getCode());
                    break;
                }
            }
            $quote->collectTotals();
            $quote->save();
            //SJD++ 10052018 END, To set first available shipping method or throw error if shipping not possible
        }
        $this->_getState()->setCompleteStep(
            Hs_Multistepcheckout_Model_Type_State::STEP_SELECT_ADDRESSES
        );

    }

    public function productAction()
    {
        if (!$this->_validateStep('product')) {
            return;
        }

        if (!Mage::helper('checkout/cart')->getItemsCount()) {
            $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
            return;
        }


        $this->_getState()->setActiveStep(
            Hs_Multistepcheckout_Model_Type_State::STEP_PRODUCT
        );

        /* Added By ST */
        Mage::getSingleton('multistepcheckout/type_state')->unsCompleteStep(
            Hs_Multistepcheckout_Model_Type_State::STEP_PAYMENT
        );
        /* Added By ST */

        $this->loadLayout();
        $this->_initLayoutMessages('checkout/session');
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

    public function saveDeliveryAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $this->_getCheckoutSession()->addError(
                $this->__('No data provided to set shipping method')
            );
            $this->_redirect('*/index/product', array('_secure' => true));
            return;
        }
        $cart = $this->_getCart();
        $quote = $cart->getQuote();
        $cart->getQuote()
            ->setExpectedDeliveryDate(
                Mage::getSingleton('checkout/session')->getExpectedDeliveryDateTime()
            );
        $quote
            ->setDeliveryType($request->getPost('deliveryMethod'))
            ->getShippingAddress()
            ->setShippingMethod($request->getPost('shipping_method'));
        $quote->collectTotals();

        if (!$quote = $this->_setPickupLocation($quote)) {
            $this->_redirect('*/index/product', array('_secure' => true));
            return;
        }
        
        $quote->collectTotals();
        $quote->save();
        $billingAddress = $quote->getBillingAddress();
        $billingAddress->setFeeAmount(5.95);
        $billingAddress->setBaseFeeAmount(5.95);
        $billingAddress->save();
        $this->_getState()->setCompleteStep(
            Hs_Multistepcheckout_Model_Type_State::STEP_PRODUCT
        );
        $this->_redirect('*/index/payment', array('_secure' => true));
    }

    /**
     * SJD++ 18052018
     * Get current quote object and set it's transsmart pickup location if required.
     *
     * @param $quote
     * @return bool
     */
    protected function _setPickupLocation($quote)
    {
        /** @var Mage_Core_Controller_Request_Http $request */
        $request = $this->getRequest();

        // remove the pickup addresses, if there are any
        Mage::helper('hs_pickuplocation/pickupaddress')
            ->removePickupAddressFromQuote($quote);

        // check if a pickup address is required
        // not a Transsmart shipping method with enabled location selector
        if (Mage::helper('hs_pickuplocation')->isLocationSelectQuote($quote, false)) {
            if (!($pickupAddressData = $request->getPost('qls_pickup_address_data'))) {
                // No location data provided
                $this->_getCheckoutSession()->addError(
                    $this->__('A pickup location has to be selected')
                );
                return false;
            }

            // JSON decode
            $pickupAddressData = Zend_Json_Decoder::decode($pickupAddressData);
            // TODO: verify pickup address data
            Mage::helper('hs_pickuplocation/pickupaddress')
                ->savePickupAddressIntoQuote($quote, $pickupAddressData['servicepoint']);
        }

        return $quote;
    }

    public function paymentAction()
    {
        if (!$this->_validateStep('payment')) {
            return;
        }

        if (!Mage::helper('checkout/cart')->getItemsCount()) {
            $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
            return;
        }
        $this->_getState()->setActiveStep(
            Hs_Multistepcheckout_Model_Type_State::STEP_PAYMENT
        );


        $this->_getState()->unsCompleteStep(
            Hs_Multistepcheckout_Model_Type_State::STEP_PAYMENT
        );
        $this->_getState()->unsCompleteStep(
            Hs_Multistepcheckout_Model_Type_State::STEP_OVERVIEW
        );


        $this->loadLayout();
        $this->_initLayoutMessages('checkout/session');
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

    protected function _validateMinimumAmount()
    {
        if (!$this->_getCheckout()->getQuote()->validateMinimumAmount()) {
            $error = $this->_getCheckout()->getMinimumAmountError();
            $this->_getCheckout()->getCheckoutSession()->addError($error);
            $this->_forward('backToAddresses');
            return false;
        }
        return true;
    }

    public function overviewAction()
    {

        if (!$this->_validateStep('overview')) {
            return;
        }

        try {

            $this->_getCheckout()->getQuote()->collectTotals()->save();

            $this->_getState()->setActiveStep(
                Hs_Multistepcheckout_Model_Type_State::STEP_OVERVIEW
            );
            $this->_getState()->setCompleteStep(
                Hs_Multistepcheckout_Model_Type_State::STEP_PAYMENT
            );

            $this->loadLayout();
            $this->_initLayoutMessages('checkout/session');
            $this->_initLayoutMessages('customer/session');
            $this->renderLayout();
        } catch (Mage_Core_Exception $e) {
            Mage::getModel('core/session')->addError($e->getMessage());
            $this->_redirect('*/*/payment', array('_secure' => true));
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::getModel('core/session')->addException($e, $this->__('Cannot open the overview page'));
            $this->_redirect('*/*/payment', array('_secure' => true));
        }

    }

    /**
     * Update subtotal with shipping rate on country change, ajax action
     * Update current cart subtotals as per new rate and return subtotal block.
     *
     * @return string
     */
    public function updateCountryIDAjaxAction()
    {
        $country = (string)$this->getRequest()->getParam('country_id');
        $shipsameyes = $this->getRequest()->getParam('shipsameyes');
        $billing = $this->getRequest()->getParam('billing');
        $shipping = $this->getRequest()->getParam('shipping');



        if(!$country) {
            return null;
        }
        $quote = $this->_getCart()->getQuote();
        if(!$quote) {
            return null;
        }

        // Billing = Shipping case
        if($shipsameyes){
            if($billing){
                $customerBillingAddress = Mage::getSingleton('customer/address')->load($billing);
                $billingAddress = $quote->getBillingAddress()->addData($customerBillingAddress->getData())->setSaveInAddressBook(0);
                $shippingAddress = $quote->getShippingAddress()->addData($customerBillingAddress->getData())->setSaveInAddressBook(0);

                $shippingAddress = $shippingAddress->setCollectShippingRates(true)
                ->collectTotals();
                $quote->save();
            } else {
                $quote->getShippingAddress()
                    ->setCountryId($country)
                    ->setCollectShippingRates(true)
                    ->collectTotals();
                $quote->save();
            }

        } else {
            // Billing != Shipping
            if($shipping){
                $customerShippingAddress = Mage::getSingleton('customer/address')->load($shipping);
                $shippingAddress = $quote->getShippingAddress()->addData($customerShippingAddress->getData())->setSaveInAddressBook(0);

                /* $shippingAddress = $shippingAddress->setCollectShippingRates(true)
                ->collectTotals();
                $quote->save(); */

                $shippingAddress = $shippingAddress->setCollectShippingRates(true)->collectShippingRates();
                $quote->collectTotals();
                $quote->save();
            } else {
                $quote->getShippingAddress()
                    ->setCountryId($country)
                    ->setCollectShippingRates(true)
                    ->collectTotals();
                $quote->save();
            }
        }

        $shippingRates = $quote->getShippingAddress()->getGroupedAllShippingRates();
        foreach ($shippingRates as $code => $rates) {
            foreach ($rates as $rate) {
                $quote->getShippingAddress()->setShippingMethod($rate->getCode());
                break;
            }
        }
        $quote->collectTotals();
        $quote->save();

        $layout = $this->getLayout();
        $layout->getUpdate()
            ->addHandle('default')
            ->addHandle('checkout_cart_index')
            ->load();
        $layout->generateXml()->generateBlocks();
        $total_html = $layout->getBlock('checkout.cart.totals')->toHtml();

        echo $total_html;
    }

        public function savePaymentAction()
        {
            if (!$this->_validateMinimumAmount()) {
                return $this;
            }

            try {
                $payment = $this->getRequest()->getPost('payment');

            //echo "<pre>";
            //print_r($payment);

                $paymentData['method'] = $payment['method'];

            //print_r($paymentData['method']);
            //exit;
                if (array_key_exists($payment['method'] . '_issuer', $payment)) {
                    $paymentData[$payment['method'] . '_issuer'] = $payment[$payment['method'] . '_issuer'];
                }
                if (array_key_exists($payment['method'] . '_paymentmethod', $payment)) {
                    $paymentData[$payment['method'] . '_paymentmethod'] = $payment[$payment['method'] . '_paymentmethod'];
                }

                /** afterbeatlean payment method code starts here **/


                if (array_key_exists('account_no', $payment)) {
                    $paymentData['account_no'] = $payment['account_no'];
                }

                if (array_key_exists('mobile_no', $payment)) {
                    $paymentData['mobile_no'] = $payment['mobile_no'];
                    if (array_key_exists('telephonecode', $payment)) {
                        $paymentData['mobile_no'] = $payment['telephonecode'] . ' ' . $payment['mobile_no'];
                        $disallowedCharacter = array('-', ' ');
                        $paymentData['mobile_no'] = str_replace($disallowedCharacter, '', trim($payment['mobile_no']));
                    }
                }

                if (array_key_exists('dob', $payment) && DateTime::createFromFormat('d/m/Y', $payment['dob']) !== false) {
                    $date = DateTime::createFromFormat('d/m/Y', $payment['dob']);
                    $paymentData['dob'] = $date->format('Y-m-d');

                }

                if (array_key_exists('gender', $payment)) {
                    $paymentData['gender'] = $payment['gender'];
                }


                /** afterbeatlean payment method code ends here **/


            $issuerName = $payment[$payment['method'] . '_issuer']; //['icepayadv_03_issuer']`

            //;
            Mage::getSingleton('checkout/session')->setSelectedBank($issuerName);
            Mage::getSingleton('checkout/session')->setSelectedPayment($paymentData);
            if (!isset($payment['method'])) {
                Mage::throwException(Mage::helper('checkout')->__('Payment method is not defined'));
            }
            $quote = $this->_getCheckout()->getQuote();
            $quote->getPayment()->importData($paymentData);
            // shipping totals may be affected by payment method
            if (!$quote->isVirtual() && $quote->getShippingAddress()) {
                $quote->getShippingAddress()->setCollectShippingRates(true);
                //$quote->setTotalsCollectedFlag(false)->collectTotals();
                //double total issue for the same day delivery
                $quote->collectTotals();
            }


            $commentData = $this->getRequest()->getPost('ordercomment');
            $comment = $commentData['comment'];

            // If its Pickup Location then Add static comment to order
            if($quote->getShippingAddress()->getShippingMethod() == 'hs_pickuplocation_hs_pickuplocation'){
                $comment = 'Afhaallocatie gekozen';
            }

            if (!empty($comment)) {
                $quote->setCustomerNoteNotify(true);
                $quote->setCustomerNote($comment);
            }

            $quote->save();


            //echo "<pre>"; print_r($paymentData); exit;

//            Mage::getSingleton('checkout/session')->setMethod($paymentData['method']);
//
//            $this->_getCheckout()->getQuote()->getPayment()->addData($paymentData);
//
//            $this->_getCheckout()->getQuote()->collectTotals()->save();

            //echo "<pre>"; print_r($payment); exit;
//            $this->_getCheckout()->savePayment($paymentData);
//            $this->_getCheckout()->getQuote()->save();


            $this->_getState()->setActiveStep(
                Hs_Multistepcheckout_Model_Type_State::STEP_OVERVIEW
            );
            $this->_getState()->setCompleteStep(
                Hs_Multistepcheckout_Model_Type_State::STEP_PAYMENT
            );

            $this->_redirect('*/*/overview', array('_secure' => true));
        } catch (Mage_Core_Exception $e) {
            Mage::getModel('core/session')->addError($e->getMessage());
            $this->_redirect('*/*/payment', array('_secure' => true));
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::getModel('core/session')->addException($e, $this->__('Cannot open the overview page'));
            $this->_redirect('*/*/payment', array('_secure' => true));
        }

    }


    /**
     * Initialize coupon
     */
    public function couponPostAction()
    {
        /**
         * No reason continue with empty shopping cart
         */
        if (!$this->_getCart()->getQuote()->getItemsCount()) {
            $this->_goBack();
            return;
        }

        $couponCode = (string)$this->getRequest()->getParam('coupon_code');
        if ($this->getRequest()->getParam('remove') == 1) {
            $couponCode = '';
        }
        $oldCouponCode = $this->_getCart()->getQuote()->getCouponCode();

        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
            $this->_goBack();
            return;
        }

        try {
            $this->_getCart()->getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->_getCart()->getQuote()->setCouponCode(strlen($couponCode) ? $couponCode : '')
            ->collectTotals()
            ->save();

            if (strlen($couponCode)) {
                if ($couponCode == $this->_getCart()->getQuote()->getCouponCode()) {
                    $this->_getCheckoutSession()->addSuccess(
                        $this->__('Coupon code "%s" was applied.', Mage::helper('core')->htmlEscape($couponCode))
                    );
                } else {
                    $this->_getCheckoutSession()->addError(
                        $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode))
                    );
                }
            } else {
                $this->_getCheckoutSession()->addSuccess($this->__('Coupon code was canceled.'));
            }

        } catch (Mage_Core_Exception $e) {
            $this->_getCheckoutSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getCheckoutSession()->addError($this->__('Cannot apply the coupon code.'));
            Mage::logException($e);
        }

        $this->_redirect('*/*/payment', array('_secure' => true));
    }

    /**
     * Initialize coupon
     */
    public function checkCouponcodeAction()
    {
        /**
         * No reason continue with empty shopping cart
         */
        /* if (!$this->_getCart()->getQuote()->getItemsCount()) {
             $this->_goBack();
             return;
         }*/

         $couponCode = (string)$this->getRequest()->getParam('coupon');
         if ($this->getRequest()->getParam('remove') == 1) {
            $couponCode = '';
        }
        $oldCouponCode = $this->_getCart()->getQuote()->getCouponCode();

        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
            $this->_goBack();
            return;
        }

        try {
            $this->_getCart()->getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->_getCart()->getQuote()->setCouponCode(strlen($couponCode) ? $couponCode : '')
            ->collectTotals()
            ->save();

            if (strlen($couponCode)) {
                if ($couponCode == $this->_getCart()->getQuote()->getCouponCode()) {
                    $result['error'] = 0;
                    $result['message'] = $this->__('Discount Code Applied');

                    $discount = abs($this->_getCart()->getQuote()->getShippingAddress()->getDiscountAmount());
                    $result['discount_amount'] = $discount;

                    if ($discount == 0 && $this->_getCart()->getQuote()->getShippingAddress()->getFreeShipping() == 1) {
                        $result['message'] = $this->__('Free Shipping');
                    }
                } else {
                    $result['error'] = 1;
                    $result['message'] = $this->__('Unfortunately your discount code is not right ...');
                    //$this->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode));
                }
            } else {
                $this->_getCheckoutSession()->addSuccess($this->__('Coupon code was canceled.'));
            }

        } catch (Mage_Core_Exception $e) {
            $this->_getCheckoutSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getCheckoutSession()->addError($this->__('Cannot apply the coupon code.'));
            Mage::logException($e);
        }
        //print_r($result);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        // $this->_redirect('*/*/payment');
    }

    public function getTotalAfterApplyCouponCodeAction()
    {
        $layout = $this->getLayout();
        $layout->getUpdate()
        ->addHandle('default')
        ->addHandle('checkout_cart_index')
        ->load();
        $layout->generateXml()->generateBlocks();
        $total_html = $layout->getBlock('checkout.cart.totals')->toHtml();

        print_r($total_html);
    }

    public function overviewPostAction()
    {
        if (!$this->_validateMinimumAmount()) {
            return;
        }

        if (!$this->_validateStep('overviewPost')) {
            return;
        }


//        die;
        //$allowed_ips = array('83.163.181.69', '27.54.171.46');
        //$ip = $_SERVER['REMOTE_ADDR'];
        //if (!in_array($ip, $allowed_ips)) {
        //$this->_getCheckout()->setShippingItemsInformation($shipToinfo);
        //}else{


//        $this->_getCheckout()->setShippingItemsInformation(Mage::getSingleton('checkout/session')->getShipToinfo());
//        Mage::getSingleton('checkout/session')->setShippingMethods(Mage::getSingleton('checkout/session')->getShippingMethods());


        //}

        try {

//            Mage::throwException("custom Exception");
            if ($requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds()) {
                $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
                if ($diff = array_diff($requiredAgreements, $postedAgreements)) {
                    $this->_getCheckoutSession()->addError($this->__('Please agree to all Terms and Conditions before placing the order.'));
                    $this->_redirect('*/*/payment');
                    return;
                }
            }

            $payment = $this->getRequest()->getPost('payment');
            //echo "<pre>"; print_r($payment); exit;

            $paymentInstance = $this->_getCheckout()->getQuote()->getPayment();
//            if (isset($payment['cc_number'])) {
//                $paymentInstance->setCcNumber($payment['cc_number']);
//            }
//            if (isset($payment['cc_cid'])) {
//                $paymentInstance->setCcCid($payment['cc_cid']);
//            }


            if (count($payment) > 0) {
//                $this->_getCheckout()->getQuote()->getPayment()->importData($payment);

            }

            $this->_getCheckout()->getQuote()->collectTotals()->save();
            $this->_getCheckout()->saveOrder();

            $redirectUrl = $this->_getCheckout()->getCheckout()->getRedirectUrl();


            /*$this->_getState()->setActiveStep(
                Mage_Checkout_Model_Type_Multishipping_State::STEP_SUCCESS
            );*/
            // $this->_getState()->setCompleteStep(
            //  Mage_Checkout_Model_Type_Multishipping_State::STEP_OVERVIEW
            // );
//            $redirectUrl = $this->_getCheckout()->getCheckoutSession()->getRedirectUrl();
            if ($redirectUrl) {
                //Mage::log($redirectUrl,null,"icepay_urls.log");
                $this->_redirectUrl($redirectUrl);
                // Mage::app()->getFrontController()->getResponse()->setRedirect($redirectUrl);

            } else {

                Mage::log(print_r($_SESSION, true), null, "print_sessions.log");

                $this->_getCheckoutSession()->clear();
                $this->_getCheckoutSession()->setDisplaySuccess(true);


                $this->_redirect('*/*/success', array('_secure' => true));
            }

        } catch (Mage_Payment_Model_Info_Exception $e) {
            Mage::logException($e);
            $message = $e->getMessage();
            if (!empty($message)) {
                $this->_getCheckoutSession()->addError($message);
            }
            $this->_redirect('*/*/payment');
        } catch (Mage_Checkout_Exception $e) {

            Mage::logException($e);
            Mage::helper('checkout')
            ->sendPaymentFailedEmail($this->_getCheckout()->getQuote(), $e->getMessage(), 'multi-shipping');
            $this->_getCheckoutSession()->addError($e->getMessage());
            $this->_redirect('checkout/cart');
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')
            ->sendPaymentFailedEmail($this->_getCheckout()->getQuote(), $e->getMessage(), 'multi-shipping');
            $this->_getCheckoutSession()->addError($e->getMessage());
            $this->_redirect('*/*/payment');
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')
            ->sendPaymentFailedEmail($this->_getCheckout()->getQuote(), $e->getMessage(), 'multi-shipping');
            $this->_getCheckoutSession()->addError($this->__('Order place error.'));
            $this->_redirect('*/*/payment');
        }
    }

    public function successAction()
    {

        $this->_getState()->setCompleteStep(
            Hs_Multistepcheckout_Model_Type_State::STEP_OVERVIEW
        );
        $this->_getState()->setActiveStep(
            Hs_Multistepcheckout_Model_Type_State::STEP_SUCCESS
        );


        $session = $this->_getCheckoutSession();

        $lastSuccessQuoteId = $session->getLastSuccessQuoteId();

        if (empty($lastSuccessQuoteId)) {
            $this->_redirect('checkout/cart');
            return;
        }

        $lastQuoteId = $session->getLastQuoteId();
        $lastOrderId = $session->getLastOrderId();
        $lastRecurringProfiles = $session->getLastRecurringProfileIds();
        if (!$lastQuoteId || (!$lastOrderId && empty($lastRecurringProfiles))) {
            $this->_redirect('checkout/cart');
            return;
        }


        /** unset other session data */
        $this->_getCheckout()->getCheckout()->unsetData('ship_toinfo');
        $this->_getCheckout()->getCheckout()->unsetData('shipping_method');
        /** unset other session data */

        $session->clear();

        $this->loadLayout();
        $this->_initLayoutMessages('checkout/session');
        Mage::dispatchEvent('checkout_onepage_controller_success_action', array('order_ids' => array($lastOrderId)));
        $this->renderLayout();

    }

    public function redirectLogin()
    {
        $this->setFlag('', 'no-dispatch', true);
        Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('*/*', array('_secure' => true)));

        $this->getResponse()->setRedirect(
            Mage::helper('core/url')->addRequestParam(
                $this->_getHelper()->getMSLoginUrl(),
                array('context' => 'checkout')
            )
        );

        $this->setFlag('', 'redirectLogin', true);
    }

    public function updatesddAction()
    {
        $updateAction = $this->getRequest()->getParam('samedaydelivery');

        if ($updateAction == 'true') {

            Mage::getSingleton('checkout/session')->setData('sdd_enabled', 1);
        } else {

            Mage::getSingleton('checkout/session')->setData('sdd_enabled', '');
        }

        //update totals by collection totals
        $quote = Mage::getSingleton('checkout/cart')->getQuote();

        $quote->collectTotals()->save();

        $layout = $this->getLayout();
        $layout->getUpdate()
        ->addHandle('default')
        ->addHandle('checkout_cart_index')
        ->load();
        $layout->generateXml()->generateBlocks();
        $total_html = $layout->getBlock('checkout.cart.totals')->toHtml();

        print_r($total_html);


    }

    /**
     * function: This method is used to validate data of each and every step
     * Name: _validateStep
     * @param step -> checkout step name
     * @retrun boolean
     */
    protected function _validateStep($step = 'address')
    {
        $quote = $this->_getCheckoutSession()->getQuote();

        if (!$quote) {
            return false;
        }

        $cartItems = count($quote->getAllItems());

        //if order is being created and in another tab user proccess then redirect to cart PAGE

        if ($cartItems <= 0) {
            Mage::log('Quote Id:' . $quote->getId() . 'NO Quote item  Found', null, 'deleted_addresses_quote.log');
            $this->_redirect('checkout/cart');
            return false;
        }

        $msg = "U bent nu weer in de winkelwagen omdat we uw adres niet goed is opgeslagen. Dat geeft problemen verderop in het proces. Wilt u alsjeblieft nogmaals uw gegevens invullen en het afrekenproces doorlopen?";

        //check if there billing and shipping address exists in quote or not
        if (count($quote->getAllAddresses()) < 2) {

            Mage::getSingleton('core/session')->addError(Mage::helper('multistepcheckout')->__($msg));
            Mage::log('quote Id:' . $quote->getId() . ' Step:' . $step . ' Reason: Customer addresses not found', null, 'deleted_addresses_quote.log');
            $this->_redirect('checkout/cart');
            return false;
        }

        //check if billing address's country id is set or not
        $billingAddress = $quote->getBillingAddress();
        if ($billingAddress->getCountryId() == '' && $step != 'address' && $step != 'login') {
            Mage::getSingleton('core/session')->addError(Mage::helper('multistepcheckout')->__($msg));
            Mage::log('quote Id:' . $quote->getId() . ' Step:' . $step . ' Reason: Billing Address Country has not been set', null, 'deleted_addresses_quote.log');
            $this->_redirect('checkout/cart');
            return false;
        }

        $shippingAddress = $quote->getShippingAddress();

//         SJD-- 10052018, removed this as shipping method would be validated at other step not at login or register step
//        check if shipping method is set or not
//        do not check if shipping method is not set for login and address step
//         if ($step != 'address' && $step != 'login' && empty($shippingMethod)) {
//            Mage::getSingleton('core/session')->addError(Mage::helper('multistepcheckout')->__($msg));
//            Mage::log('quote Id:' . $quote->getId() . ' Step:' . $step . ' Reason: shipping method has not been set', null, 'deleted_addresses_quote.log');
//            $this->_redirect('checkout/cart');
//            return false;
//        }

        //validate Address Data
        if ($step != 'address' && $step != 'login') {

            $billingValidate = $billingAddress->validate();
            $shippingValidate = $shippingAddress->validate();
            //address is not being set or address Validation
            if ($billingValidate !== true && $shippingValidate !== true) {

                Mage::getSingleton('core/session')->addError(Mage::helper('multistepcheckout')->__("Choose an address"));
                Mage::log('quote Id:' . $quote->getId() . ' Step:' . $step . ' Reason: Address Is not being set', null, 'deleted_addresses_quote.log');
                Mage::log(print_r($billingValidate, true), null, 'deleted_addresses_quote.log');
                Mage::log(print_r($shippingValidate, true), null, 'deleted_addresses_quote.log');

                if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
                    $this->_redirect('*/*/register');
                } else {
                    $this->_redirect('*/*/login');
                }

                return false;
            }
        }

        // SJD++ 10052018, Included case for shipping method validation at payment step instead of login/register step
        $shippingMethod = $shippingAddress->getShippingMethod();
        if($step == 'payment' && empty($shippingMethod)) {
            Mage::getSingleton('core/session')->addError(Mage::helper('multistepcheckout')->__("Shipping is not defined"));
            Mage::log('quote Id:' . $quote->getId() . ' Step:' . $step . ' Reason: shipping method has not been set', null, 'deleted_addresses_quote.log');
            $this->_redirect('*/*/product');
            return false;
        }

        //check if payment method is exists or not
        if ($step == 'overviewPost') {

            //check if payment method is set or not
            if ($quote->getPayment()->getMethod() == '') {
                Mage::getSingleton('core/session')->addError(Mage::helper('multistepcheckout')->__("Payment is not defined"));
                Mage::log('quote Id:' . $quote->getId() . ' Step:' . $step . ' Reason: payment method has not been set', null, 'deleted_addresses_quote.log');
                $this->_redirect('*/*/payment');
                return false;
            }


        }

        return true;
    }

    /**
    * This method is used to log message of AdBlocker tracking
    * Need to remove or move to other module.
    *
    */
    public function loggerAction()
    {
        $message = $this->getRequest()->getPost('message');
        Mage::log($message, null, 'google_adblocker.log');
        echo 'Log succeed';
    }
}
