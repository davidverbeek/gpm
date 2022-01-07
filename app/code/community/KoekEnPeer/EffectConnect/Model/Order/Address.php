<?php

    class KoekEnPeer_EffectConnect_Model_Order_Address extends Mage_Core_Model_Abstract
    {
        protected $order;
        private $streetLines = false;
        private $billingAddress;
        private $shippingAddress;
        private $obligatedAdressFields = array(
            'first_name',
            'last_name',
            'state',
            'phone'
        );

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

        /**
         * @return KoekEnPeer_EffectConnect_Model_Order_Quote
         */
        protected function _getQuote()
        {
            return $this->order->getQuote();
        }

        public function setAddress()
        {
            $quote = $this->_getQuote();
            $billingAddress = $quote->getBillingAddress();
            $order = $this->_getOrder();
            $customerData = $order->getCustomerData();
            $data = $order->getOrderData();
            if (!$customerData)
            {
                $billingAddressData = $this->convertAddressData('billing', $data);
            } else
            {
                $customerAddressId  = $customerData->getDefaultBilling();
                $customerAddress    = Mage::getModel('customer/address')
                    ->load($customerAddressId)
                ;
                $billingAddressData = $customerAddress->getData();
                if (!empty($data['billing_phone']))
                {
                    $billingAddressData['telephone'] = $data['billing_phone'];
                }
            }
            $billingAddress->addData($billingAddressData);
            $this->billingAddress = $billingAddress;
            $shippingAddressData = $this->convertAddressData('shipping', $data);
            $shippingAddress     = $quote->getShippingAddress()
                ->addData($shippingAddressData)
            ;

            $shippingAddress->setWeight(0);
            $shippingAddress->setShippingAmount(0);
            $shippingAddress->setBaseShippingAmount(0);
            $shippingAddress->setFreeMethodWeight(0);

            $this->shippingAddress = $shippingAddress;

            return $this;
        }

        public function convertAddressData($addressType, $data)
        {
            if ($this->streetLines === false)
            {
                $this->streetLines = Mage::helper('customer/address')
                    ->getStreetLines(
                        $this->_getOrder()
                            ->getStoreId()
                    )
                ;
            }

            $prefix = $addressType ? $addressType.'_' : '';

            $region   = Mage::getModel('directory/region_api')
                ->items($data[$prefix.'country'])
            ;
            $regionId = null;
            foreach ($region as $regionData)
            {
                if ($regionData['name'] == $data[$prefix.'state'])
                {
                    $regionId = $regionData['region_id'];
                    break;
                }
            }
            foreach ($this->obligatedAdressFields as $addressField)
            {
                $addressFieldKey = $prefix.$addressField;
                if (empty($data[$addressFieldKey]))
                {
                    $data[$addressFieldKey] = '-';
                }
            }

            $order = $this->_getOrder();
            $addressData = array(
                'email'      => $order?$order->getCustomerEmail():null,
                'prefix'     => $this->_convertPrefix($data[$prefix.'salutation']),
                'firstname'  => $data[$prefix.'first_name'],
                'lastname'   => $data[$prefix.'last_name'],
                'company'    => $data[$prefix.'company'],
                'postcode'   => $data[$prefix.'zip_code'],
                'city'       => $data[$prefix.'city'],
                'country_id' => $data[$prefix.'country'],
                'region'     => $data[$prefix.'state'],
                'region_id'  => $regionId ? $regionId : $data[$prefix.'state'],
                'telephone'  => $data[$prefix.'phone']
            );
            $addressStreet               = $data[$prefix.'street'];
            $addressHouseNumber          = $data[$prefix.'house_number'];
            $addressHouseNumberExtension = $data[$prefix.'house_number_extension'];
            $addressHouseNumberCombined  = implode(
                ' ', array_filter(
                    array(
                        $data[$prefix.'house_number'],
                        $data[$prefix.'house_number_extension']
                    )
                )
            );
            $addressNote                 = !empty($data[$prefix.'address_note']) ? '('.$data[$prefix.'address_note'].')' : '';
            switch ($this->streetLines)
            {
                case 1:
                    $addressData['street'] = implode(
                        ' ',
                        array_filter(
                            array(
                                $addressStreet,
                                $addressHouseNumberCombined,
                                $addressNote
                            )
                        )
                    );
                    break;
                case 2:
                    $addressData['street'] = array(
                        $addressStreet,
                        implode(
                            ' ',
                            array_filter(
                                array(
                                    $addressHouseNumberCombined,
                                    $addressNote
                                )
                            )
                        )
                    );
                    break;
                default:
                    $addressData['street'] = array_filter(
                        $addressNote ?
                            array(
                                $addressStreet,
                                $addressHouseNumberCombined,
                                $addressNote
                            )
                            :
                            array(
                                $addressStreet,
                                $addressHouseNumber,
                                $addressHouseNumberExtension
                            )
                    );
                    break;
            }

            return $addressData;
        }

        public function getBillingAddress()
        {
            return $this->billingAddress;
        }

        public function getShippingAddress()
        {
            return $this->shippingAddress;
        }

        protected function _convertPrefix($prefix)
        {
            if (!$prefix)
            {
                return '';
            }

            $convertedPrefix = Mage::getStoreConfig('effectconnect_options/order/convert_prefix_'.$prefix);

            if (!$convertedPrefix)
            {
                return '';
            }

            return $convertedPrefix;
        }
    }