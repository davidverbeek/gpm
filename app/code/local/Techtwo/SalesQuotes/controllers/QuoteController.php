<?php

class Techtwo_SalesQuotes_QuoteController extends Mage_Core_Controller_Front_Action
{
    public function historyAction()
    {
        $loginUrl = Mage::helper('customer')->getLoginUrl();

        if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
            return;
        }


        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');

        $this->getLayout()->getBlock('head')->setTitle($this->__('My Quotes'));

        if ($block = $this->getLayout()->getBlock('customer.account.link.back')) {
            $block->setRefererUrl($this->_getRefererUrl());
        }
        $this->renderLayout();
    }

    protected function _viewAction()
    {
        if (!$this->_loadValidOrder()) {
            return;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');

        if ($navigationBlock = $this->getLayout()->getBlock('customer_account_navigation')) {
            $navigationBlock->setActive('sales/order/history');
        }
        $this->renderLayout();
    }

    /**
     * Try to load valid order by order_id and register it
     *
     * @param int $orderId
     * @return bool
     */
    protected function _loadValidOrder($orderId = null)
    {
        if (null === $orderId) {

        }
        if (!$orderId) {
            $this->_forward('noRoute');
            return false;
        }

        $order = Mage::getModel('sales/order')->load($orderId);

        if ($this->_canViewOrder($order)) {
            Mage::register('current_order', $order);
            return true;
        }
        else {
            $this->_redirect('*/*/history');
        }
        return false;
    }

    public function viewQuoteAction()
    {
        $loginUrl = Mage::helper('customer')->getLoginUrl();

        if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
            return;
        }

        $customerId = Mage::getSingleton('customer/session')->getCustomerId();

        $quoteId = (int) $this->getRequest()->getParam('quote_id');
        $quote = Mage::getModel('sales/quote')->load($quoteId);

        //var_dump($quote->getId());

        // is not customer's quote
        if (!$quote->getId() || !$quote->getCustomerId() || ($quote->getCustomerId() != $customerId)){
            $this->_forward('noRoute');
            return false;
        }

        Mage::register('current_quote', $quote);

        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');

        $this->getLayout()->getBlock('head')->setTitle($this->__('View Quote'));

        if ($navigationBlock = $this->getLayout()->getBlock('customer_account_navigation')) {
            $navigationBlock->setActive('salesquotes/quote/history');
        }
        $this->renderLayout();

    }


    public function checkoutQuoteAction() {
        $loginUrl = Mage::helper('customer')->getLoginUrl();

        if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
            return;
        }

        $customerId = Mage::getSingleton('customer/session')->getCustomerId();

        $quoteId = (int) $this->getRequest()->getParam('quote_id'); 
        $quote = Mage::getModel('sales/quote')->load($quoteId);

        //var_dump($quote->getId());

        // is not customer's quote
        if (!$quote->getId() || !$quote->getCustomerId() || ($quote->getCustomerId() != $customerId)){
            $this->_forward('noRoute');
            return false;
        }

        // set checkout quote
        $quote->setIsActive(1)
                ->save();

        Mage::getSingleton('checkout/session')->replaceQuote($quote);

        //exit;
        // redirect to checkout
        $this->_redirect('multistepcheckout/index/login');
    }


    public function confirmQuoteAction()
    {
        $loginUrl = Mage::helper('customer')->getLoginUrl();

        if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
            return;
        }


        $customerId = Mage::getSingleton('customer/session')->getCustomerId();

        $quoteId = (int) $this->getRequest()->getParam('quote_id');
        $quote = Mage::getModel('sales/quote')->load($quoteId);


        // is not customer's quote
        if (!$quote->getId() || !$quote->getCustomerId() || ($quote->getCustomerId() != $customerId)){
            $this->_forward('noRoute');
            return false;
        }



        if(!$this->getRequest()->getPost()){
            $this->_redirect('salesquotes/quote/viewQuote/',array('quote_id'=>$quoteId));
            return;
        }

        $quote->setStatus('confirmed');
        $quote->save();

        Mage::getSingleton('customer/session')->addSuccess(Mage::helper('techtwo_salesquotes')->__('The quote status has been changed to confirmed'));
        $this->_redirect('salesquotes/quote/history');
        return;

    }


    public function indexAction()
    {
    	//echo "11"; exit;

        //$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
        //var_dump($setup->getTable('sales/quote'));
        //eh,
        $this->loadLayout();
        $this->_initLayoutMessages('techtwo_salesquotes/session');
        $this->_initLayoutMessages('checkout/session');
        $this->_initLayoutMessages('catalog/session');
        $this->_initLayoutMessages('customer/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Quote'));
        $this->renderLayout();
        //echo "11"; exit;
    }

    public function loginCustomerAction()
    {
        $loginUrl = Mage::helper('customer')->getLoginUrl();
            if ( $this->_request->getParam('backurl') )
                $loginUrl .= '?backurl='.$this->_request->getParam('backurl');

        if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
            return;
        }
        $this->_forward('index');
    }


    /*
    * Change by Parth in function saveAction()
    * $this->updateCustomerAccountInfo($billingdata); this line commented because no need to 
    * update data while placing quote of loggedin user
    *
    * $cart_quote->assignCustomer($customer); -- this is the cause which adding new shipping 
    * row while saving quote so commented this line.
    * 
    * 
    */

    public function saveAction()
    {

        if ($this->getRequest()->isPost()){
     		try{

                $customer_id = '';
                $created_customer_account = false;
                // logged in customer
                if(Mage::getSingleton('customer/session')->getCustomerId()){
                    $customer_id = Mage::getSingleton('customer/session')->getCustomerId();


                        // whatever the input could be, we set the email since you are logged in
                        $billingdata = $this->getRequest()->getPost('billing');
                        $billingdata['country_id'] = $this->getRequest()->getPost('country_id');
                        $billingdata['email'] = Mage::getSingleton('customer/session')->getCustomer()->getEmail();
                        

                    

                    // update customer account info
                    // $this->updateCustomerAccountInfo($billingdata);

                    // update customer default address
                    $this->updateCustomerAddressInfo($billingdata);

                }else{
                    // create new customer account
                    $customer_id = $this->createCustomerAccount($this->getRequest()->getPost('billing'));

                    $created_customer_account = true;

                    if(!$customer_id) Mage::throwException(Mage::helper('techtwo_salesquotes')->__('Cannot save contact information'));
                }


                //create new quote (we don't want to save the existent checkout quote because it could be an old cart->existent id);
                // $customer = Mage::getModel('customer/customer')->load($customer_id);

                $cart_quote = Mage::getSingleton('checkout/session')->getQuote();

                $cart_quote->setIsActive('false');
                $cart_quote->setIsCartAuto(0);
                $cart_quote->setStatus('open');
                // $cart_quote->assignCustomer($customer);
                $cart_quote->save();

                $quoteId = $cart_quote->getId();
                /*$newQuote = Mage::getModel('sales/quote')
                     ->setData($cart_quote->getData())
                     ->setIsCartAuto(0)
                     ->setId(null)
                     ->assignCustomer($customer);

                 foreach ($cart_quote->getAllVisibleItems() as $item) {
                    $newItem = clone $item;
                    $newQuote->addItem($newItem);
                    if ($item->getHasChildren()) {
                        foreach ($item->getChildren() as $child) {
                            $newChild = clone $child;
                            $newChild->setParentItem($newItem);
                            $newQuote->addItem($newChild);
                        }
                    }
                 }

                $newQuote->setIsActive('false');
                $newQuote->setStatus('open');
                $newQuote->save();*/

                //
                Mage::helper('techtwo_salesquotes')->sendNewQuoteThankyouEmail($cart_quote);
                Mage::helper('techtwo_salesquotes')->sendQuoteNotificationEmail($cart_quote);

                // send notifications
                // Mage::helper('techtwo_salesquotes')->sendNewQuoteThankyouEmail($newQuote);
                // Mage::helper('techtwo_salesquotes')->sendQuoteNotificationEmail($newQuote);

                // clear cart
                Mage::getSingleton('checkout/session')->clear();
                Mage::getSingleton('checkout/session')->unsetAll();

                Mage::getSingleton('techtwo_salesquotes/session')->addSuccess($this->__('Your Contact Information and your Quote (#%s) have been successfully saved',$quoteId));

                $this->_redirect('*/*/quoteSuccess');

            }catch(Exception $e){
                Mage::getSingleton('techtwo_salesquotes/session')->addError($e->getMessage());
                $this->_redirect('*/*/index');
            }

         }
    }


     public function quoteSuccessAction()
     {
     	/*if (!$this->_getOnepage()->getCheckout()->getLastSuccessQuoteId()) {
            $this->_redirect('checkout/cart');
            return;
        }*/

     	Mage::getSingleton('checkout/session')->clear();

     	$this->loadLayout();
        $this->_initLayoutMessages('techtwo_salesquotes/session');
        $this->_initLayoutMessages('checkout/session');
        $this->_initLayoutMessages('catalog/session');
        $this->_initLayoutMessages('customer/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Quote'));
        $this->renderLayout();
     }


    private function _getOnepage()
    {
        return Mage::getSingleton('checkout/session');
    }


    //------------------------------------------------------------------------
    //						CUSTOMER ACCOUNT
    //------------------------------------------------------------------------
    private function createCustomerAccount($customer_data)
    {
        $customer = Mage::getModel('customer/customer')->setId(null);
        $customer->setData('firstname', $customer_data['firstname']);
        $customer->setData('lastname', $customer_data['lastname']);
        $customer->setData('email', $customer_data['email']);
        $customer->setData('password', $customer_data['customer_password']);
        $customer->setData('confirmation', $customer_data['confirm_password']);

        $customer->getGroupId();

        // set address
        $address = Mage::getModel('customer/address')
                    ->setData($customer_data)
                    ->setIsDefaultBilling(1)
                    ->setIsDefaultShipping(1)
                    ->setId(null);
        $customer->addAddress($address);

        $errors = $address->validate();
        if (!is_array($errors)) {
            $errors = array();
        }


        $validationCustomer = $customer->validate();
        if (is_array($validationCustomer)) {
            $errors = array_merge($validationCustomer, $errors);
        }
        $validationResult = count($errors) == 0;

        if (true === $validationResult) {
            $customer->save();
            Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);

        } else {
            if (is_array($errors)) {
                foreach ($errors as $errorMessage) {
                    Mage::getSingleton('techtwo_salesquotes/session')->addError($errorMessage);
                }
            }
            else {
                Mage::getSingleton('techtwo_salesquotes/session')->addError($this->__('Invalid customer data'));
            }
            return false;
        }


        Mage::getSingleton('techtwo_salesquotes/session')->setEscapeMessages(true);
        return $customer->getId();
    }


    private function updateCustomerAccountInfo($customer_data)
    {
        $customer = Mage::getModel('customer/customer')
            ->setId(Mage::getSingleton('customer/session')->getCustomerId())
            ->setWebsiteId(Mage::getSingleton('customer/session')->getCustomer()->getWebsiteId());

        $customer->setData('firstname', $customer_data['firstname']);
        $customer->setData('lastname', $customer_data['lastname']);
        $customer->setData('email', $customer_data['email']);

        $errors = $customer->validate();
        if (!is_array($errors)) {
            $errors = array();
        }

        // we would like to preserve the existing group id
        if (Mage::getSingleton('customer/session')->getCustomerGroupId()) {
            $customer->setGroupId(Mage::getSingleton('customer/session')->getCustomerGroupId());
        }

        if (!empty($errors)) {
            foreach ($errors as $message) {
                Mage::getSingleton('techtwo_salesquotes/session')->addError($message);
            }
            Mage::throwException(Mage::helper('techtwo_salesquotes')->__('Cannot save contact information'));
        }else{
            $customer->save();
        }

    }

    private function updateCustomerAddressInfo($customer_data)
    {
        $address = Mage::getModel('customer/address')
                    ->setData($customer_data)
                    ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                    ->setIsDefaultBilling(1)
                    ->setIsDefaultShipping(1);
        /*
        * Change by Parth
        * Address Id if posted data then set that instead setting default.
        */
        if(isset($customer_data['address_id']) && !is_null($customer_data['address_id']) ){
            $addressId = $customer_data['address_id'];
        } else {
            $addressId = Mage::getSingleton('customer/session')->getCustomer()->getDefaultBilling();
        }

        if ($addressId) {
            $customerAddress = Mage::getSingleton('customer/session')->getCustomer()->getAddressById($addressId);
            if ($customerAddress->getId() && $customerAddress->getCustomerId() == Mage::getSingleton('customer/session')->getCustomerId()) {
                $address->setId($addressId);
            }
            else {
                $address->setId(null);
            }
        }
        else {
            $address->setId(null);
        }

        $addressValidation = $address->validate();
        if (true === $addressValidation) {
            $address->save();
        }
        else {
            if (is_array($addressValidation)) {
                foreach ($addressValidation as $errorMessage) {
                    Mage::getSingleton('techtwo_salesquotes/session')->addError($message);
                    Mage::throwException(Mage::helper('techtwo_salesquotes')->__('Cannot save contact address information'));
                }
            } else {
                Mage::throwException(Mage::helper('techtwo_salesquotes')->__('Cannot save contact address information'));
            }
        }
    }


//    public function testAction()
//    {
//        echo 'this is the quote page!';
//
//        //mail('ada80ro@gmail.com', 'My Subject', 'just testing');
//
//        $quoteId = 45;
//        $quote = Mage::getModel('sales/quote')->load($quoteId);
//       // echo '<pre>';
//       // print_r($quote);
//       // echo '</pre>';
//        //Mage::helper('techtwo_salesquotes')->sendNewQuoteEmail($quote);
//
//        //Mage::helper('techtwo_salesquotes')->sendNewQuoteThankyouEmail($quote);
//
//        $customerId = 2;
//        /*$collection = Mage::getResourceModel('sales/quote_collection')
//            ->addAttributeToSelect('entity_id')
//            ->addAttributeToFilter('customer_id', $customerId)
//            ->addAttributeToFilter('is_cart_auto',1)
//            ->addAttributeToFilter('is_active', 1);
//
//        echo $collection->getSelect()->__toString();*/
//        $customerQuote = Mage::getModel('sales/quote')
//            ->setStoreId(Mage::app()->getStore()->getId())
//            ->loadByCustomer($customerId);
//    }

}
