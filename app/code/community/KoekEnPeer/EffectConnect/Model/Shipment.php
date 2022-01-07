<?php

    class KoekEnPeer_EffectConnect_Model_Shipment extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface
    {
        protected $_code = 'effectconnect';

        public function collectRates(Mage_Shipping_Model_Rate_Request $request)
        {
            $result = Mage::getModel('shipping/rate_result');

            /** @var $order KoekEnPeer_EffectConnect_Model_Order */
            $order  = Mage::registry('effectconnect_order');

            if (Mage::registry('is_effectconnect'))
            {
                $price = $order->getPriceShipping();

                if (!Mage::helper('tax')->shippingPriceIncludesTax())
                {
                    $shippingTaxClassId = Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_SHIPPING_TAX_CLASS);

                    if ($shippingTaxClassId)
                    {
                        $taxRate = $order->getTaxRate($shippingTaxClassId);

                        if ($taxRate)
                        {
                            $price /= (100 + $taxRate) / 100;
                        }
                    }
                }

                $titles = array(
                    'carrier' => Mage::getStoreConfig('carriers/effectconnect_shipment/title'),
                    'method'  => Mage::getStoreConfig('carriers/effectconnect_shipment/subtitle')
                );

                if (!$titles['carrier'])
                {
                    $titles['carrier'] = $this->getConfigData('title');
                }

                if (!$titles['method'])
                {
                    $titles['method'] = $order->getTitleShipping();
                }

                foreach ($titles as &$title)
                {
                    $title = str_replace(
                        array(
                            '[channel]',
                            '[order_number]'
                        ),
                        array(
                            $order->getChannelName(),
                            $order->getNumberExternal()
                        ),
                        $title
                    );
                }

                $method = Mage::getModel('shipping/rate_result_method');
                $method->setCarrier($this->_code);
                $method->setMethod('shipment');
                $method->setCarrierTitle($titles['carrier']);
                $method->setMethodTitle($titles['method']);
                $method->setPrice($price);
                $method->setCost($price);

                $result->append($method);
            }

            return $result;
        }

        public function getAllowedMethods()
        {
            return array('shipment' => $this->getConfigData('title'));
        }
    }