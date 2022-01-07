<?php

    class KoekEnPeer_EffectConnect_Model_Order_Shipment extends Mage_Core_Model_Abstract
    {
        private $order;

        public function setOrder($order)
        {
            $this->order = $order;

            return $this;
        }

        protected function _getOrder()
        {
            return $this->order;
        }

        private $shippingMethodsPickupSearch = array(
            'ophalen',
            'afhalen',
            'pick up',
            'pick-up',
            'pickup',
            'abholung',
            'abholen',
            'abzuholen'
        );

        public function getMethod($shippingAddress)
        {
            $shippingAddress
                ->setCollectShippingRates(true)
                ->collectShippingRates()
            ;
            $shipmentMethod = Mage::getStoreConfig('effectconnect_options/order/shipping');
            $shippingRates = $shippingAddress
                ->collectShippingRates()
                ->getGroupedAllShippingRates()
            ;
            $availableShippingRates = array();

            $defaultShipmentMethodLength = strlen(KoekEnPeer_EffectConnect_Model_Order::DEFAULT_SHIPMENT_METHOD);

            foreach ($shippingRates as $carrier)
            {
                foreach ($carrier as $shippingRate)
                {
                    $shippingRateData = $shippingRate->getData();
                    if (substr($shippingRateData['code'],0,$defaultShipmentMethodLength) == KoekEnPeer_EffectConnect_Model_Order::DEFAULT_SHIPMENT_METHOD && $shipmentMethod != $shippingRateData['code'])
                    {
                        continue;
                    }
                    $availableShippingRates[$shippingRateData['code']] = $shippingRateData;
                }
            }

            $this->_getOrder()
                ->setAvailableShippingRates($availableShippingRates)
            ;
            if (!isset($availableShippingRates[$shipmentMethod]) && !empty($availableShippingRates))
            {
                $shippingRatesCount = count($availableShippingRates);
                if ($availableShippingRates > 1)
                {
                    foreach ($availableShippingRates as $shippingRateCode => $shippingRate)
                    {
                        foreach ($this->shippingMethodsPickupSearch as $pickupString)
                        {
                            if (stristr($shippingRate['method_title'], $pickupString))
                            {
                                unset($availableShippingRates[$shippingRateCode]);
                                $shippingRatesCount--;
                                break;
                            }
                        }
                        if ($shippingRatesCount == 1)
                        {
                            break;
                        }
                    }
                }
                reset($availableShippingRates);
                $shipmentMethod = key($availableShippingRates);
            }
            if (!$shipmentMethod)
            {
                $shipmentMethod = KoekEnPeer_EffectConnect_Model_Order::DEFAULT_SHIPMENT_METHOD;;
            }

            return $shipmentMethod;
        }
    }