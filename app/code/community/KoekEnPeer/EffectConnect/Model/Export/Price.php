<?php

    class KoekEnPeer_EffectConnect_Model_Export_Price extends KoekEnPeer_EffectConnect_Model_Export
    {
        public function exportPrice($observer)
        {
            if (!$this->hasLiveSynchronisation() || $this->getPriceAttribute() || $this->getPriceAttributeSurcharge())
            {
                return false;
            }
            Mage::app()
                ->loadAreaPart(Mage_Core_Model_App_Area::AREA_FRONTEND, Mage_Core_Model_App_Area::PART_EVENTS)
            ;
            /** @var Mage_Catalog_Model_Product $productNew */
            $productNew      = $observer->getProduct();
            $productOldPrice = $productNew->getOrigData('price');
            if ($this->useSpecialPrice)
            {
                $productNewPrice = Mage::getResourceModel('catalogrule/rule')
                    ->getRulePrice(
                        Mage::app()
                            ->getLocale()
                            ->storeTimeStamp($this->storeView),
                        Mage::app()
                            ->getStore($this->storeView)
                            ->getWebsiteId(),
                        null,
                        $productNew->getId()
                    )
                ;
                if (!$productNewPrice)
                {
                    $productNewPrice = $productNew->getData('price');
                }
            } else
            {
                $productNewPrice = $productNew->getData('price');
            }
            if ($productOldPrice != $productNewPrice)
            {
                $this->processProductUpdate(
                    $productNew->getId(),
                    'price',
                    $this->getPriceIncludingTax($productNewPrice, $productNew->getTaxClassId())
                )
                ;
            }

            return true;
        }

        public function getPrice($productId = false)
        {
            if ($this->getPriceAttribute() || $this->getPriceAttributeSurcharge())
            {
                return false;
            }

            $resource       = Mage::getSingleton('core/resource');
            $readConnection = $resource->getConnection('core_read');

            $statement = $this->getQuery(
                'product_price',
                array(
                    'price_field' => ($this->useSpecialPrice() ? 'final_' : '').'price'
                )
            )
            ;
            if ($productId)
            {
                $statement .= ' AND `entity_id`='.(int)$productId;
            }

            $query = $readConnection->query($statement);
            $items = array();
            while ($record = $query->fetch())
            {
                $items[(int)$record['entity_id']] = $this->getPriceIncludingTax((float)$record['price'], $record['tax_class_id']);
            }

            if ($productId)
            {
                if (!isset($items[$productId]))
                {
                    return false;
                }

                return $items[$productId];
            } else
            {
                return $items;
            }
        }
    }