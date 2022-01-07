<?php

    class KoekEnPeer_EffectConnect_Model_Order_Quote extends Mage_Core_Model_Abstract
    {
        /** @var KoekEnPeer_EffectConnect_Model_Order $order */
        protected $order;
        /** @var Mage_Sales_Model_Quote $_quote */
        protected $_quote;

        public function create(KoekEnPeer_EffectConnect_Model_Order $order)
        {
            $this->setOrder($order);
            try
            {
                $quote = Mage::getModel('sales/quote')
                    ->setStoreId($order->getStoreId())
                ;
            } catch (Exception $e)
            {
                $order->addError(
                    KoekEnPeer_EffectConnect_Model_Order::ERROR_QUOTE_STORE_LOAD,
                    $e,
                    array(
                        'storeId' => $order->getStoreId()
                    )
                )
                ;

                return false;
            }
            $quote->setCurrency($order->getCurrency());
            $quote->setIsMultiShipping(false);
            $quote->reserveOrderId();
            $quote->setEffectconnect($order->getEffectConnectOrderId());
            $quote->setExtOrder($order->getNumberExternal());
            $customerData    = $order->getCustomerData();
            $customerGroupId = $order->getCustomerGroupId();
            if ($customerData)
            {
                $quote->setCustomer($customerData);
                $quote->setCheckoutMethod($customerData->getMode());
                if ($customerGroupId)
                {
                    $quote->setCustomerGroupId($customerGroupId);
                }
            } else
            {
                $customer = $order->getCustomer();

                $createCustomer = Mage::getStoreConfig('effectconnect_options/order/create_customer') == 1;
                if ($createCustomer)
                {
                    $customer = $this->_createCustomer($customer);

                    if ($customer)
                    {
                        $quote->setCustomer($customer);
                    }
                    else{
                        return false;
                    }
                } else
                {
                    $quote->setCheckoutMethod('guest');
                    $quote->setCustomerEmail($customer['email']);
                    $quote->setCustomerFirstname($customer['first_name']);
                    $quote->setCustomerLastname($customer['last_name']);
                    $quote->setCustomerId(null);
                    $quote->setCustomerGroupId(
                        $customerGroupId ?
                            $customerGroupId :
                            Mage_Customer_Model_Group::NOT_LOGGED_IN_ID
                    )
                    ;
                }
            }

            $this->_quote = $quote;

            return $this;
        }

        public function setOrder(KoekEnPeer_EffectConnect_Model_Order $order)
        {
            $this->order = $order;

            return $this;
        }

        /**
         * @return KoekEnPeer_EffectConnect_Model_Order
         */
        protected function _getOrder()
        {
            return $this->order;
        }

        public function getQuote()
        {
            return $this->_quote;
        }

        public function getBillingAddress()
        {
            return $this->_quote->getBillingAddress();
        }

        public function getShippingAddress()
        {
            return $this->_quote->getShippingAddress();
        }

        /**
         * @param Mage_Catalog_Model_Product $product
         * @param float $price
         * @param int $quantity
         *
         * @return bool
         * @throws Exception
         */
        public function addProduct($product, $price, $quantity)
        {
            $parameters = array(
                'product' => $product->getId(),
                'qty'     => $quantity
            );

            $productWeight = floatval($product->getWeight());
            $productIds    = array($product->getId());
            $productType   = $product->getTypeId();

            switch ($productType)
            {
                case Mage_Catalog_Model_Product_Type::TYPE_SIMPLE:
                    if ($parentId = Mage::helper('effectconnect/data')->getProductParentId($product))
                    {
                        $productIds[] = $parentId;

                        $configurableProduct     = Mage::getModel('catalog/product')
                            ->load($parentId)
                        ;
                        $productAttributeOptions = $configurableProduct->getTypeInstance(true)
                            ->getConfigurableAttributesAsArray($configurableProduct)
                        ;

                        $superAttributes = array();
                        foreach ($productAttributeOptions as $productAttribute)
                        {
                            $allValues = array();
                            foreach ($productAttribute['values'] as $productAttributeValue)
                            {
                                $allValues[] = $productAttributeValue['value_index'];
                            }

                            $currentProductValue = $product->getData($productAttribute['attribute_code']);
                            if (in_array($currentProductValue, $allValues))
                            {
                                $superAttributes[$productAttribute['attribute_id']] = $currentProductValue;
                            }
                        }

                        $product = $configurableProduct;

                        $parameters['product']         = $product->getId();
                        $parameters['super_attribute'] = $superAttributes;
                    }
                    break;
                case Mage_Catalog_Model_Product_Type::TYPE_BUNDLE:
                    $bundleProduct       = clone $product;
                    $optionCollection    = $bundleProduct->getTypeInstance()->getOptionsCollection();
                    $selectionCollection = $bundleProduct->getTypeInstance()->getSelectionsCollection($bundleProduct->getTypeInstance()->getOptionsIds());
                    $bundleItems         = array();
                    foreach($optionCollection->appendSelections($selectionCollection) as $option) {
                        foreach($option->getSelections() as $selection) {
                            $bundleItems[$option->getOptionId()][] = $selection->getSelectionId();
                        }
                    }

                    $parameters['bundle_option'] = $bundleItems;
                    break;
            }

            $request = new Varien_Object();
            $request->setData($parameters);
            $product->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED);

            $quoteItem = $this->getQuote()
                ->addProduct($product, $request)
            ;

            if (is_string($quoteItem))
            {
                throw new Exception($quoteItem);
            }

            $quoteItem->setWeight($productWeight);

            switch($productType)
            {
                case Mage_Catalog_Model_Product_Type::TYPE_BUNDLE:
                    $bundlePriceSet = false;
                    foreach ($this->getQuote()->getAllVisibleItems() as $item)
                    {
                        if (!in_array($item->getProductId(), $productIds))
                        {
                            continue;
                        }

                        $bundleItemPrice = 0;
                        if (!$bundlePriceSet)
                        {
                            $bundleItemPrice = $price;
                            $bundlePriceSet  = true;
                        }

                        $item->setCustomPrice($bundleItemPrice)
                             ->setOriginalCustomPrice($bundleItemPrice)
                        ;
                    }
                    break;
                case Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE:
                    foreach ($this->getQuote()->getAllVisibleItems() as $item)
                    {
                        if (!in_array($item->getProductId(), $productIds))
                        {
                            continue;
                        }

                        $item->setCustomPrice($price)
                             ->setOriginalCustomPrice($price)
                             ->setWeight($productWeight)
                        ;
                    }
                    break;
                default:
                    $quoteItem->setCustomPrice($price)
                              ->setOriginalCustomPrice($price)
                              ->setQty($quantity)
                    ;
                    break;
            }

            $quoteItem->getProduct()->setIsSuperMode(true);

            return true;
        }

        public function setPaymentAndShipmentMethod($paymentMethod, $shipmentMethod)
        {
            $quote = $this->getQuote();
            $shippingAddress = $this->getShippingAddress();
            try
            {
                $shippingAddress->setShippingMethod($shipmentMethod)
                    ->setCollectShippingRates(true)
                    ->setPaymentMethod($paymentMethod)
                ;
                $quote->getPayment()
                    ->importData(array('method' => $paymentMethod))
                ;
            } catch (Exception $e)
            {
                $this->_getOrder()
                    ->addError(
                        KoekEnPeer_EffectConnect_Model_Order::ERROR_QUOTE_SHIPMENT_PAYMENT,
                        $e,
                        array(
                            'shipmentMethod' => $shipmentMethod,
                            'paymentMethod'  => $paymentMethod
                        )
                    )
                ;
            }

            return true;
        }

        public function setDiscountCode($discountCode)
        {
            $this->getQuote()
                ->setCouponCode($discountCode)
                ->save()
            ;

            return true;
        }

        public function collectTotals()
        {
            try
            {
                $this->getQuote()
                    ->setTotalsCollectedFlag(false)
                    ->collectTotals()
                    ->save()
                ;
            } catch (Exception $e)
            {
                $this->_getOrder()
                    ->addError(
                        KoekEnPeer_EffectConnect_Model_Order::ERROR_QUOTE_SAVE,
                        $e
                    )
                ;
            }
        }

        private function _createCustomer($data)
        {
            try {
                $customer = Mage::getModel('customer/customer');

                $websiteId = Mage::getModel('core/store')->load($this->_getOrder()->getStoreId())->getWebsiteId();
                $customer->setWebsiteId($websiteId);
                $customer->loadByEmail($data['email']);

                $newCustomer = false;
                $address = false;

                if (!$customer->getId())
                {
                    $orderAddressModel = Mage::getModel('effectconnect/order_address');
                    $orderAddressModel->setOrder($this->_getOrder());
                    $address           = $orderAddressModel->convertAddressData(false, $data);

                    $customer->setEmail($data['email']);
                    $customer->addData($address);

                    $password = $customer->generatePassword();
                    $customer->setPassword($password);

                    $newCustomer = true;
                }

                $customerGroupId = $this->_getOrder()->getCustomerGroupId();

                if ($customerGroupId)
                {
                    $customer->setDisableAutoGroupChange(1)
                        ->setGroupId($customerGroupId)
                    ;
                }

                $customer->save();

                if ($newCustomer)
                {
                    $customerAddress = Mage::getModel('customer/address');
                    $customerAddress->setData($address)
                        ->setCustomerId($customer->getId())
                        ->setIsDefaultBilling('1')
                        ->setIsDefaultShipping('1')
                        ->setSaveInAddressBook('1');

                    $customerAddress->save();
                }

                return $customer;
            }catch(Exception $e)
            {
                $this->_getOrder()
                    ->addError(
                        KoekEnPeer_EffectConnect_Model_Order::ERROR_CUSTOMER_CREATE,
                        $e
                    )
                ;
            }

            return false;
        }
    }