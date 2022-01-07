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
 * One page checkout processing model
 */
class Hs_Multistepcheckout_Model_Type_Onepage extends Mage_Checkout_Model_Type_Onepage
{

    private $_customerEmailExistsMessage = '';


    /**
     * Class constructor
     * Set customer already exists message
     */
    public function __construct()
    {
        $this->_helper = Mage::helper('checkout');
        $this->_customerEmailExistsMessage = Mage::helper('checkout')->__('There is already a customer registered using this email address. Please login using this email address or enter a different email address to register your account.');
        $this->_checkoutSession = Mage::getSingleton('checkout/session');
        $this->_customerSession = Mage::getSingleton('customer/session');
    }

    /**
     * Save billing address information to quote
     * This method is called by One Page Checkout JS (AJAX) while saving the billing information.
     *
     * @param   array $data
     * @param   int $customerAddressId
     * @return  Mage_Checkout_Model_Type_Onepage
     */
    public function saveBilling($data, $customerAddressId)
    {
        if (empty($data)) {
            return array('error' => -1, 'message' => Mage::helper('checkout')->__('Invalid data.'));
        }

        $address = $this->getQuote()->getBillingAddress();
        /* @var $addressForm Mage_Customer_Model_Form */
        $addressForm = Mage::getModel('customer/form');
        $addressForm->setFormCode('customer_address_edit')
            ->setEntityType('customer_address');
            //->setIsAjaxRequest(Mage::app()->getRequest()->isAjax());

        if (!empty($customerAddressId)) {
            $customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
            if ($customerAddress->getId()) {
                if ($customerAddress->getCustomerId() != $this->getQuote()->getCustomerId()) {
                    return array('error' => 1,
                        'message' => Mage::helper('checkout')->__('Customer Address is not valid.')
                    );
                }

                $address->importCustomerAddress($customerAddress)->setSaveInAddressBook(0);
                $addressForm->setEntity($address);
                $addressErrors = $addressForm->validateData($address->getData());
                if ($addressErrors !== true) {
                    return array('error' => 1, 'message' => $addressErrors);
                }
            }
        } else {
            $addressForm->setEntity($address);
            // emulate request object
            $addressData = $addressForm->extractData($addressForm->prepareRequest($data));
            //echo "<pre>"; print_r($addressData); exit;
            /*$addressErrors  = $addressForm->validateData($addressData);
            if ($addressErrors !== true) {
                return array('error' => 1, 'message' => array_values($addressErrors));
            }*/
            $addressForm->compactData($addressData);
            //unset billing address attributes which were not shown in form
            foreach ($addressForm->getAttributes() as $attribute) {
                if (!isset($data[$attribute->getAttributeCode()])) {
                    $address->setData($attribute->getAttributeCode(), NULL);
                }
            }
            $address->setCustomerAddressId(null);
            $this->getQuote()->getBillingAddress()->setEmail($data['email']);
            // Additional form data, not fetched by extractData (as it fetches only attributes)
            //$address->setSaveInAddressBook(empty($data['save_in_address_book']) ? 0 : 1);
            if ($data['checkout_method'] != 'guest') {
                $customerId = $this->getQuote()->getCustomerId();
                if ($data['save_in_address_book']) {
                    $customerAddressInfo = Mage::getModel("customer/address");
                    $customerAddressInfo->setCustomerId($customerId)
                        ->addData($data)
                        ->setIsDefaultBilling('1')
                        ->setIsDefaultShipping('0')
                        ->setSaveInAddressBook('1');


                    $customerAddressInfo->save();
                    $this->getQuote()->getBillingAddress()->setCustomerAddressId($customerAddressInfo->getId())->setSaveInAddressBook('0');


                    // Added by Parth - get new added address
                    $customerAddressId = $customerAddressInfo->getId();
                    Mage::getSingleton('customer/session')->setAddressId($customerAddressId);
                }
            }
        }

        // validate billing address
        if (($validateRes = $address->validate()) !== true) {
            return array('error' => 1, 'message' => $validateRes);
        }

        $address->implodeStreetAddress();
        //$address->save();

        if (true !== ($result = $this->_validateCustomerData($data))) {
            return $result;
        }

        if (!$this->getQuote()->getCustomerId() && self::METHOD_REGISTER == $this->getQuote()->getCheckoutMethod()) {
            if ($this->_customerEmailExists($address->getEmail(), Mage::app()->getWebsite()->getId())) {
                return array('error' => 1, 'message' => $this->_customerEmailExistsMessage);
            }
        }

        if (!$this->getQuote()->isVirtual()) {
            /**
             * Billing address using otions
             */
            $usingCase = isset($data['use_for_shipping']) ? (int)$data['use_for_shipping'] : 0;

            switch ($usingCase) {
                case 0:
                    $shipping = $this->getQuote()->getShippingAddress();
                    $shipping->setSameAsBilling(0);
                    break;
                case 1:
                    $billing = clone $address;
                    $billing->unsAddressId()->unsAddressType();
                    $shipping = $this->getQuote()->getShippingAddress();
                    $shippingMethod = $shipping->getShippingMethod();

                    // Billing address properties that must be always copied to shipping address
                    $requiredBillingAttributes = array('customer_address_id');

                    // don't reset original shipping data, if it was not changed by customer
                    foreach ($shipping->getData() as $shippingKey => $shippingValue) {
                        if (!is_null($shippingValue) && !is_null($billing->getData($shippingKey))
                            && !isset($data[$shippingKey]) && !in_array($shippingKey, $requiredBillingAttributes)
                        ) {
                            $billing->unsetData($shippingKey);
                        }
                    }
                    $shipping->addData($billing->getData())
                        ->setSameAsBilling(1)
                        ->setSaveInAddressBook(0)
                        ->setShippingMethod($shippingMethod)
                        ->setCollectShippingRates(true);
                    //$this->getCheckout()->setStepData('shipping', 'complete', true);
                    break;
            }
        }

        $this->getQuote()->collectTotals();
        $this->getQuote()->save();

        if (!$this->getQuote()->isVirtual() && $this->getCheckout()->getStepData('shipping', 'complete') == true) {
            //Recollect Shipping rates for shipping methods
            $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
        }

        /** checkout method is not geeting save */
        if (isset($data['checkout_method'])) {
            $checkoutMethod = $data['checkout_method'];
            $this->saveCheckoutMethod($checkoutMethod);
        }
        /*** checkout method saving mechnism end */

        $this->getCheckout()
            ->setStepData('billing', 'allow', true)
            ->setStepData('billing', 'complete', true)
            ->setStepData('shipping', 'allow', true);

        return array();
    }


    /**
     * Save checkout shipping address
     *
     * @param   array $data
     * @param   int $customerAddressId
     * @return  Mage_Checkout_Model_Type_Onepage
     */
    public function saveShipping($data, $customerAddressId)
    {

        //echo $customerAddressId;

        if (empty($data)) {
            return array('error' => -1, 'message' => Mage::helper('checkout')->__('Invalid data.'));
        } //echo "<pre>"; print_r($data); exit;
        $address = $this->getQuote()->getShippingAddress();

        /* @var $addressForm Mage_Customer_Model_Form */
        $addressForm = Mage::getModel('customer/form');
        $addressForm->setFormCode('customer_address_edit')
            ->setEntityType('customer_address')
            ->setIsAjaxRequest(Mage::app()->getRequest()->isAjax());

        if (!empty($customerAddressId)) {
            $customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
            if ($customerAddress->getId()) {
                if ($customerAddress->getCustomerId() != $this->getQuote()->getCustomerId()) {
                    return array('error' => 1,
                        'message' => Mage::helper('checkout')->__('Customer Address is not valid.')
                    );
                }

                $address->importCustomerAddress($customerAddress)->setSaveInAddressBook(0);
                $addressForm->setEntity($address);
                $addressErrors = $addressForm->validateData($address->getData());
                if ($addressErrors !== true) {
                    return array('error' => 1, 'message' => $addressErrors);
                }
            }
        } else {
            $addressForm->setEntity($address);
            // emulate request object
            $addressData = $addressForm->extractData($addressForm->prepareRequest($data));
            $addressErrors = $addressForm->validateData($addressData);
            if ($addressErrors !== true) {
                return array('error' => 1, 'message' => $addressErrors);
            }
            $addressForm->compactData($addressData);
            // unset shipping address attributes which were not shown in form
            foreach ($addressForm->getAttributes() as $attribute) {
                if (!isset($data[$attribute->getAttributeCode()])) {
                    $address->setData($attribute->getAttributeCode(), NULL);
                }
            }
            $this->getQuote()->getShippingAddress()->setEmail($data['email']);

            /*$address->setCustomerAddressId(null);
            // Additional form data, not fetched by extractData (as it fetches only attributes)
            $address->setIsDefaultBilling('0');
			$address->setIsDefaultShipping('1');
            $address->setSaveInAddressBook('1');*/
            $postBillingData = Mage::app()->getRequest()->getParam('billing');
            if ($data['checkout_method'] != 'guest') {
                $customerId = $this->getQuote()->getCustomerId();
                if ($postBillingData['save_in_address_book']) {
                    $customerAddressInfo = Mage::getModel("customer/address");
                    $customerAddressInfo->setCustomerId($customerId)
                        ->addData($data)
                        ->setIsDefaultBilling('0')
                        ->setIsDefaultShipping('1')
                        ->setSaveInAddressBook('1');


                    $customerAddressInfo->save();
                    $this->getQuote()->getShippingAddress()->setCustomerAddressId($customerAddressInfo->getId())->setSaveInAddressBook('1');

                    // Added by Parth - get new added address
                    $customerAddressId = $customerAddressInfo->getId();
                }
            }

            // $address->setSameAsBilling(empty($data['same_as_billing']) ? 0 : 1);
        }

        $address->implodeStreetAddress();
        $address->setCollectShippingRates(true);

        if (($validateRes = $address->validate()) !== true) {
            return array('error' => 1, 'message' => $validateRes);
        }

        $this->getQuote()->collectTotals()->save();

        $this->getCheckout()
            ->setStepData('shipping', 'complete', true)
            ->setStepData('shipping_method', 'allow', true);

        // Added by Parth - new address id return
        return array("customerAddressId" => $customerAddressId);
    }


    /**
     * Subscribe Newsletter - PP 13062018
     *
     * @param   array $data
     * @return  Mage_Checkout_Model_Type_Onepage
     */
    public function subscribeNewsletter($data){
        $status = 1;
        $newsLetterModel = Mage::getModel('newsletter/subscriber');

        $customerData = Mage::getModel("customer/customer")->setWebsiteId(Mage::app()->getWebsite()->getId())->loadByEmail($data['email']);

        $subscriber = $newsLetterModel->loadByEmail($data['email']);

        if(!$subscriber->getId()){   
            $status = $newsLetterModel->subscribe($data['email']);
        }
        return $status;
    }
}
