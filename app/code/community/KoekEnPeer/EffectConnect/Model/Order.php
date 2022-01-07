<?php

    class KoekEnPeer_EffectConnect_Model_Order extends Mage_Core_Model_Abstract
    {
        const DEFAULT_SHIPMENT_METHOD = 'effectconnect_shipment';
        const DEFAULT_PAYMENT_METHOD  = 'effectconnect_payment';

        const STATUS_PAID      = 'paid';
        const STATUS_CANCELLED = 'cancelled';
        const STATUS_COMPLETED = 'completed';

        const ERROR_NO_DATA                 = 'no_data';
        const ERROR_DATA_INVALID            = 'data_invalid';
        const ERROR_ORDER_SUBMIT            = 'order_submit_error';
        const ERROR_ORDER_NOT_SET           = 'order_not_set';
        const ERROR_PRODUCT_ADD             = 'product_add_error';
        const ERROR_PRODUCT_NOT_FOUND       = 'product_not_found';
        const ERROR_QUOTE_STORE_LOAD        = 'quote_store_load_error';
        const ERROR_QUOTE_SHIPMENT_PAYMENT  = 'shipment_payment_method_error';
        const ERROR_QUOTE_SAVE              = 'quote_save_error';
        const ERROR_CUSTOMER_CREATE         = 'customer_create_error';

        private $errors = array();
        private $data;
        private $storeId;
        private $customerData = false;
        private $customerGroupId = false;
        private $discountCode;
        /** @var $quote KoekEnPeer_EffectConnect_Model_Order_Quote */
        private $quote;
        private $availableShippingRates = false;
        private $shipmentMethod;
        private $paymentMethod;
        /** @var $order Mage_Sales_Model_Order */
        private $order;
        private $orderId;
        private $orderNumber;
        private $initialEnvironmentInfo;

        public function create($input, $debug = false)
        {
            if ($debug)
            {
                ini_set('display_errors', 1);
                ini_set('display_startup_errors', 1);
                error_reporting(E_ALL);
            }

            if (!$this->_validateData($input))
            {
                return $this->_finish();
            }

            if ($this->_checkForExistingOrder())
            {
                return $this->_finish();
            }

            Mage::register('is_effectconnect', true, true);
            Mage::register('isSecureArea', true, true);

            $this->_getMappingData($this->getChannelId());

            $appEmulation = Mage::getSingleton('core/app_emulation');
            $this->initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($this->getStoreId());

            $orderQuoteModel = Mage::getModel('effectconnect/order_quote')
                ->create($this)
            ;
            $this->quote     = $orderQuoteModel;

            if ($this->_hasErrors())
            {
                return $this->_finish();
            }

            $orderProductModel = Mage::getModel('effectconnect/order_product');
            $orderProductModel->setOrder($this);

            $this->_setCurrencyConversion();

            $orderAddressModel = Mage::getModel('effectconnect/order_address');
            $orderAddressModel->setOrder($this);
            $orderAddressModel->setAddress();

            if ($this->_hasErrors())
            {
                return $this->_finish();
            }

            foreach ($this->getProducts() as $product)
            {
                $orderProductModel->addProduct(
                    $product['option_id'],
                    $product['sku'],
                    $product['ean'],
                    $product['title'],
                    $product['price'],
                    $product['amount']
                );
            }

            if ($this->_hasErrors())
            {
                return $this->_finish();
            }

            Mage::register('effectconnect_order', $this, true);

            $shippingAddress = $orderAddressModel->getShippingAddress();
            $orderShipmentModel = Mage::getModel('effectconnect/order_shipment');
            $orderShipmentModel->setOrder($this);

            $this->shipmentMethod = $orderShipmentModel->getMethod($shippingAddress);
            $this->paymentMethod = Mage::getStoreConfig('effectconnect_options/order/payment');

            if (!$this->paymentMethod)
            {
                $this->paymentMethod = KoekEnPeer_EffectConnect_Model_Order::DEFAULT_PAYMENT_METHOD;
            }

            $orderQuoteModel->setPaymentAndShipmentMethod(
                $this->paymentMethod,
                $this->shipmentMethod
            )
            ;

            if ($this->_hasErrors())
            {
                return $this->_finish();
            }

            if ($this->discountCode)
            {
                $orderQuoteModel->setDiscountCode($this->discountCode);
            }

            $orderQuoteModel->collectTotals();

            if ($this->_hasErrors())
            {
                return $this->_finish();
            }

            $this->_createOrder($orderQuoteModel->getQuote());

            return $this->_finish();
        }

        public function getStoreId()
        {
            return $this->storeId;
        }

        public function getCurrency()
        {
            return $this->data['currency'];
        }

        public function getCurrencyConversionRate()
        {
            return (float)$this->data['currency_conversion_rate'];
        }

        public function getEffectConnectOrderId()
        {
            return $this->data['id'];
        }

        public function getChannelName()
        {
            return $this->data['channel_name'];
        }

        public function getNumberExternal()
        {
            return $this->data['number_external'];
        }

        public function getChannelId()
        {
            return $this->data['channel_id'];
        }

        public function getCustomerData()
        {
            return $this->customerData;
        }

        public function getCustomerGroupId()
        {
            return $this->customerGroupId;
        }

        public function getCustomer()
        {
            return $this->data['_customer'];
        }

        public function getCustomerEmail()
        {
            return $this->data['_customer']['email'];
        }

        public function getProducts()
        {
            return $this->data['_products'];
        }

        public function getPriceShipping()
        {
            return (float)$this->data['price_shipping'];
        }

        public function getTitleShipping()
        {
            return (string)$this->data['title_shipping'];
        }

        public function getTitlePayment()
        {
            return (string)$this->data['title_payment'];
        }

        public function sendEmailsToCustomer()
        {
            return $this->data['send_emails'];
        }

        public function getQuote()
        {
            return $this->quote;
        }

        public function getStatus()
        {
            $defaultStatus = Mage::getStoreConfig('effectconnect_options/order/status');
            if ($defaultStatus)
            {
                return $defaultStatus;
            }

            switch ($this->data['status'])
            {
                case self::STATUS_PAID:
                    return Mage_Sales_Model_Order::STATE_PROCESSING;
                    break;
                case self::STATUS_CANCELLED:
                    return Mage_Sales_Model_Order::STATE_CANCELED;
                    break;
            }

            return false;
        }

        public function getState($status)
        {
            foreach (Mage::getResourceModel('sales/order_status_collection')->joinStates() as $statusRecord)
            {
                if ($statusRecord->getStatus()==$status && $statusRecord->getState())
                {
                    return $statusRecord->getState();
                }
            }

            return Mage_Sales_Model_Order::STATE_PROCESSING;
        }

        public function getOrderData()
        {
            return $this->data;
        }

        public function setAvailableShippingRates($rates)
        {
            $this->availableShippingRates = $rates;

            return true;
        }

        public function addError($code, Exception $exception = null, $data = array())
        {
            if ($exception)
            {
                Mage::log($exception->getMessage(), null, 'koekenpeer_effectconnect.log');
                $data['message'] = $exception->getMessage();
                $data['trace']   = $exception->getTraceAsString();
            }
            $this->errors[] = array(
                'code' => $code,
                'data' => $data
            );
        }

        public function convertStatus($magentoStatus)
        {
            $status = false;
            switch ($magentoStatus)
            {
                case Mage_Sales_Model_Order::STATE_PROCESSING:
                    $status = KoekEnPeer_EffectConnect_Model_Order::STATUS_PAID;
                    break;
                case Mage_Sales_Model_Order::STATE_CANCELED:
                case Mage_Sales_Model_Order::STATE_CLOSED:
                    $status = KoekEnPeer_EffectConnect_Model_Order::STATUS_CANCELLED;
                    break;
                case Mage_Sales_Model_Order::STATE_COMPLETE:
                    $status = KoekEnPeer_EffectConnect_Model_Order::STATUS_COMPLETED;
                    break;
            }

            return $status;
        }

        public function getPriceIncludesTax()
        {
            return Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX,$this->getStoreId());
        }

        private function _hasErrors()
        {
            return count($this->errors) > 0;
        }

        protected function _validateData($input)
        {
            if (!$input)
            {
                $this->addError(self::ERROR_NO_DATA);

                return false;
            }

            $this->data = Mage::helper('core')
                ->jsonDecode($input)
            ;

            if (!$this->data)
            {
                $this->addError(
                    self::ERROR_DATA_INVALID,
                    null,
                    $input
                )
                ;

                return false;
            }

            return true;
        }

        protected function _checkForExistingOrder()
        {
            if(isset($_GET['force'])){
                return false;
            }

            return $this->_loadOrder('effectconnect',$this->getEffectConnectOrderId());
        }

        protected function _getMappingData($channelId)
        {
            $mappingData = Mage::getModel('effectconnect/mapping')
                ->getCollection()
                ->addFieldToFilter('channel_id', $channelId)
                ->getFirstItem()
                ->toArray()
            ;
            if (!$mappingData)
            {
                $this->storeId = Mage::app()
                    ->getWebsite(true)
                    ->getDefaultGroup()
                    ->getDefaultStoreId()
                ;
            } else
            {
                $this->storeId = $mappingData['store_id'];
                if (!empty($mappingData['customer_group_id']))
                {
                    $this->customerGroupId = $mappingData['customer_group_id'];
                }
                if (!empty($mappingData['customer_id']))
                {
                    $this->customerData = Mage::getModel('customer/customer')
                        ->load($mappingData['customer_id'])
                    ;
                }
                $this->discountCode = $mappingData['discount_code'];
            }
        }

        protected function _createOrder($quote)
        {
            $useNumberExternal = Mage::getStoreConfig('effectconnect_options/order/use_number_external') == 1;

            if ($useNumberExternal && $this->getNumberExternal())
            {
                $this->orderNumber = $this->getNumberExternal();

                Mage::register('effectconnect_order_number_external', $this->orderNumber, true);
            } else
            {
                $this->orderNumber = $quote->getReservedOrderId();
            }

            $service = Mage::getModel('sales/service_quote', $quote);
            try
            {
                $service->submitAll();
            } catch (Exception $e)
            {
                $this->addError(
                    self::ERROR_ORDER_SUBMIT,
                    $e,
                    array(
                        'shipmentMethod'         => $this->shipmentMethod,
                        'paymentMethod'          => $this->paymentMethod,
                        'availableShippingRates' => $this->availableShippingRates
                    )
                )
                ;

                return false;
            }

            $this->_loadOrder('increment_id', $this->orderNumber);

            $this->_updateOrderInfo();

            $this->_getReturnData();

            return true;
        }

        protected function _loadOrder($fieldType, $fieldValue)
        {
            $model = Mage::getModel('sales/order');

            switch($fieldType)
            {
                case 'increment_id':
                    $order = $model->loadByIncrementId($fieldValue);
                    break;
                default:
                    $order = Mage::getModel('sales/order')->loadByAttribute($fieldType, $fieldValue);
                    break;
            }

            if ($order->getId())
            {
                $this->order        = $order;
                $this->orderId      = $order->getId();
                $this->orderNumber  = $order->getIncrementId();

                return true;
            }

            return false;
        }

        protected function _updateOrderInfo()
        {
            if ($this->sendEmailsToCustomer() && $this->order->getCanSendNewEmailFlag()) {
                try {
                    $this->order->sendNewOrderEmail();
                } catch (Exception $e){
                    Mage::log($e->getMessage());
                }
            }

            $status = $this->getStatus();
            $state  = $this->getState($status);

            $this->order->setExtOrder($this->getNumberExternal());
            if ($status)
            {
                $this->order->setData('state', $state);
                $this->order->setState($state, $status, '', $this->sendEmailsToCustomer());
            }
            $this->_addOrderComment();
            $this->order->save();

            $this->order->getPayment()->capture(null);

            foreach($this->order->getRelatedObjects() as $object)
            {
                if ($object->getId())
                {
                    continue;
                }

                $object->save();
            }

            /** @var Mage_Sales_Model_Order_Invoice $invoice */
            if ($this->sendEmailsToCustomer())
            {
                foreach ($this->order->getInvoiceCollection() as $invoice)
                {
                    $invoice->sendEmail();
                }
            }

            $this->order->save();
        }

        protected function _addOrderComment()
        {
            $history = $this->order->addStatusHistoryComment(
                $this->_getOrderComment(),
                false
            )
            ;

            $history->setIsCustomerNotified($this->sendEmailsToCustomer());

            // Added by Parth - to set Customer not in Main table(Flat Order Grid)
            try {
                $this->order->setCustomerNote($this->_getOrderComment());
            } catch (Exception $e){
                echo $e->getMessage();
            }
        }

        protected function _getOrderComment()
        {
            // $orderComment = array(
            //     'Order imported from EffectConnect',
            //     'Channel: '.$this->getChannelName(),
            //     'Ordernumber channel: '.$this->getNumberExternal()
            // );
            // if ($this->customerData)
            // {
            //     $orderComment[] = 'E-mail customer: '.$this->getCustomerEmail();
            // }
            // $orderComment[] = '';
            // $orderComment[] = 'View in EffectConnect: '.KoekEnPeer_EffectConnect_Helper_Data::APP_URL.'orders?view='.$this->data['id'];

            // return implode('<br />', $orderComment);

            // Added By Parth - Custom note
            $orderComment = Mage::getStoreConfig('effectconnect_options/order/order_comment', Mage::app()->getStore());
            return $orderComment;
        }

        protected function _getReturnData()
        {
            if ($this->_hasErrors())
            {
                $success = false;

                $returnData = array(
                    'export_error'         => 1,
                    'export_error_message' => $this->errors
                );
            } else
            {
                $success = true;

                $invoiceNumbers = array();
                if ($this->order->hasInvoices())
                {
                    foreach ($this->order->getInvoiceCollection() as $invoice)
                    {
                        $invoiceNumbers[] = $invoice->getIncrementId();
                    }
                }

                $returnData = array(
                    'id_internal'      => $this->orderId,
                    'number_internal'  => $this->orderNumber,
                    'invoice_internal' => implode(' / ', $invoiceNumbers),
                    'export_date'      => strtotime($this->order->getCreatedAt())
                );
            }

            return array($success, $returnData);
        }

        protected function _setCurrencyConversion()
        {
            $baseCurrencyCode = Mage::app()->getStore($this->storeId)->getCurrentCurrencyCode();
            if ($baseCurrencyCode != $this->getCurrency())
            {
                $currencyModel = Mage::getModel('directory/currency');

                $rates = $currencyModel->getCurrencyRates($baseCurrencyCode, array($this->getCurrency()));
                if (isset($rates[$this->getCurrency()]))
                {
                    $newRate = 1 / $this->getCurrencyConversionRate();

                    if ($rates[$this->getCurrency()] != $newRate)
                    {
                        $this->oldCurrencyRate = $rates[$this->getCurrency()];
                        $currencyModel->saveRates(array($baseCurrencyCode => array($this->getCurrency() => $newRate)));
                    }
                }
            } else
            {
                $this->data['currency_conversion_rate'] = 1;
            }
        }

        protected function _finish()
        {
            if ($this->oldCurrencyRate)
            {
                $currencyModel    = Mage::getModel('directory/currency');
                $baseCurrencyCode = Mage::app()->getBaseCurrencyCode();
                $currencyModel->saveRates(array($baseCurrencyCode => array($this->getCurrency() => $this->oldCurrencyRate)));
            }

            Mage::unregister('effectconnect_order');
            Mage::unregister('effectconnect_order_number_external');

            if (!$this->_hasErrors() && !$this->order){
                $this->addError(self::ERROR_ORDER_NOT_SET);
            }

            list($success,$returnData) = $this->_getReturnData();

            Mage::getModel('effectconnect/api')
                ->updateOrder(
                    $this->data['id'],
                    $returnData
                )
            ;
            if (!$returnData)
            {
                $success    = false;
                $returnData = 'Invalid call';
            }

            $return = array(
                'success' => $success,
                'data'    => $returnData
            );

            if ($this->initialEnvironmentInfo)
            {
                $appEmulation = Mage::getSingleton('core/app_emulation');
                $appEmulation->stopEnvironmentEmulation($this->initialEnvironmentInfo);
            }

            return $return;
        }

        public function getTaxRate($taxClassId)
        {
            $calculation = Mage::getSingleton('tax/calculation');
            $rates = $calculation->getRatesForAllProductTaxClasses(
                $calculation->getRateRequest(
                    $this->getQuote()->getShippingAddress(),
                    $this->getQuote()->getBillingAddress(),
                    $this->getQuote()->getQuote()->getCustomerTaxClassId(),
                    Mage::app()->getStore($this->getStoreId())
                )
            );

            if (isset($rates[$taxClassId]))
            {
                return $rates[$taxClassId];
            }

            return false;
        }
    }