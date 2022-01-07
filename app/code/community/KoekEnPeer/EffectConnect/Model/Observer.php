<?php

    class KoekEnPeer_EffectConnect_Model_Observer extends Mage_Core_Model_Abstract
    {
        public function priceUpdate(Varien_Event_Observer $observer)
        {
            $exportModel = Mage::getModel('effectconnect/export_price');

            return $exportModel->exportPrice($observer);
        }

        public function stockUpdate($productId, $newQty = false)
        {
            $exportModel = Mage::getModel('effectconnect/export_stock');

            return $exportModel->exportStock($productId, $newQty);
        }

        public function orderUpdate(Varien_Event_Observer $observer)
        {
            $exportModel = Mage::getModel('effectconnect/export_order');

            return $exportModel->updateOrder($observer);
        }

        public function catalogInventorySave(Varien_Event_Observer $observer)
        {
            $event = $observer->getEvent();
            $_item = $event->getItem();
            if ((int)$_item->getData('qty') != (int)$_item->getOrigData('qty'))
            {
                $this->stockUpdate(
                    $_item->getProductId(),
                    $_item->getQty()
                )
                ;
            }
        }

        public function beforeOrderCreated(Varien_Event_Observer $observer)
        {
            $quote = $observer->getEvent()
                ->getQuote()
            ;
            foreach ($quote->getAllItems() as $item)
            {
                $this->stockUpdate(
                    $item->getProductId()
                )
                ;
            }

            if (Mage::registry('is_effectconnect'))
            {
                $useNumberExternal = Mage::getStoreConfig('effectconnect_options/order/use_number_external') == 1;
                if ($useNumberExternal)
                {
                    $order       = $observer->getEvent()->getOrder();
                    $orderNumber = (string)Mage::registry('effectconnect_order_number_external');

                    if (!empty($orderNumber))
                    {
                        $order->setIncrementId($orderNumber);
                    }
                }
            }
        }

        public function revertQuoteInventory(Varien_Event_Observer $observer)
        {
            $quote = $observer->getEvent()
                ->getQuote()
            ;
            foreach ($quote->getAllItems() as $item)
            {
                $this->stockUpdate(
                    $item->getProductId()
                );
            }
        }

        public function refundOrderInventory(Varien_Event_Observer $observer)
        {
            if (!Mage::getStoreConfig('cataloginventory/options/can_subtract'))
            {
                return;
            } else
            {
                $creditMemo = $observer->getEvent()
                    ->getCreditmemo()
                ;
                foreach ($creditMemo->getAllItems() as $item)
                {
                    $this->stockUpdate(
                        $item->getProductId()
                    );
                }
            }
        }

        public function salesOrderGridCollectionLoadBefore($observer)
        {
            $collection = $observer->getOrderGridCollection();
            $collection->addFilterToMap('ext_order', 'main_table.ext_order');
        }
    }