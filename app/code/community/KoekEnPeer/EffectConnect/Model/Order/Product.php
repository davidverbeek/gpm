<?php

    class KoekEnPeer_EffectConnect_Model_Order_Product extends Mage_Core_Model_Abstract
    {
        /** @var KoekEnPeer_EffectConnect_Model_Order */
        protected $order;

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

        public function addProduct($productId, $sku, $ean, $title, $price, $quantity)
        {
            $product = Mage::getModel('catalog/product');
            if (!$productId)
            {
                if (substr($sku, 0, 3) == 'EC_')
                {
                    $productId = (int)substr($sku['sku'], 3);
                }
                if (!$productId)
                {
                    $productId = $product->getIdBySku($sku);
                }
            }
            if ($productId)
            {
                $product = $product->load($productId);
                $product->setSkipCheckRequiredOption(true);
            } else
            {
                $product = false;
            }
            $productInfo = array(
                'sku'   => $sku,
                'ean'   => $ean,
                'title' => $title
            );
            $order = $this->_getOrder();
            if ($product)
            {
                if ($order->getCurrencyConversionRate())
                {
                    $price /= $order->getCurrencyConversionRate();
                }

                if (!$order->getPriceIncludesTax())
                {
                    $taxClassId = $product->getData('tax_class_id');
                    if (!$taxClassId)
                    {
                        $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());

                        if (!empty($parentIds))
                        {
                            $parentProduct = Mage::getModel('catalog/product');
                            foreach ($parentIds as $parentId)
                            {
                                $parentProduct = $parentProduct->load($parentId);

                                if (!$parentProduct)
                                {
                                    continue;
                                }

                                $taxClassId = $parentProduct->getData('tax_class_id');

                                if ($taxClassId)
                                {
                                    break;
                                }
                            }
                        }
                    }

                    $taxRate = $this->_getOrder()->getTaxRate($taxClassId);

                    if ($taxRate)
                    {
                        $price /= (100 + $taxRate) / 100;
                    }
                }

                try
                {
                    $this->_getQuote()
                        ->addProduct($product, $price, $quantity)
                    ;
                    $product->unsSkipCheckRequiredOption();
                } catch (Exception $e)
                {
                    $order->addError(
                        KoekEnPeer_EffectConnect_Model_Order::ERROR_PRODUCT_ADD,
                        $e,
                        $productInfo
                    )
                    ;
                }
            } else
            {
                $order->addError(
                    KoekEnPeer_EffectConnect_Model_Order::ERROR_PRODUCT_NOT_FOUND,
                    null,
                    $productInfo
                )
                ;
            }
        }
    }