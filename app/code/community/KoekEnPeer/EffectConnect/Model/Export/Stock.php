<?php

    class KoekEnPeer_EffectConnect_Model_Export_Stock extends KoekEnPeer_EffectConnect_Model_Export
    {
        const BACKORDER_DEFAULT_STOCK = 25;

        public function exportStock($productId, $newQty = false)
        {
            if (!$this->hasLiveSynchronisation())
            {
                return false;
            }
            $_product = Mage::getModel('catalog/product')
                ->load($productId)
            ;
            $_stock   = Mage::getModel('cataloginventory/stock_item')
                ->loadByProduct($_product)
            ;
            if (
                $this->useBackorders() &&
                ($_stock->getUseConfigBackorders() && $this->getConfigBackordersEnabled()) ||
                (!$_stock->getUseConfigBackorders() && $_stock->getBackorders() > 0)
            )
            {
                $stock = self::BACKORDER_DEFAULT_STOCK;
            } else
            {
                if ($newQty === false)
                {
                    $newQty = $_stock->getQty();
                }
                $stock = $_stock->getIsInStock() ? $newQty : 0;
            }
            $this->processProductUpdate(
                $productId,
                'stock',
                $stock
            )
            ;

            return true;
        }

        public function getStock($productId = false)
        {
            $resource       = Mage::getSingleton('core/resource');
            $readConnection = $resource->getConnection('core_read');
            $tableStock     = $resource->getTableName('cataloginventory_stock_item');
            $tableProduct   = $resource->getTableName('catalog_product_entity');

            $statement = '
                SELECT
                    `'.$tableStock.'`.`item_id`,
                    `'.$tableStock.'`.`product_id`,
                    `'.$tableStock.'`.`backorders`,
                    `'.$tableStock.'`.`use_config_backorders`,
                    `'.$tableStock.'`.`is_in_stock`,
                    `'.$tableStock.'`.`qty`,
                    `'.$tableStock.'`.`min_qty`,
                    `'.$tableStock.'`.`use_config_min_qty`,
                    `'.$tableProduct.'`.`type_id`
                FROM
                    `'.$tableStock.'`
                INNER JOIN
                    `'.$tableProduct.'` ON
                    `'.$tableStock.'`.`product_id`=`'.$tableProduct.'`.`entity_id`
                '.($productId ? '
                WHERE
                    `'.$tableStock.'`.`product_id`='.(int)$productId.'
                ' : '').'
            ';
            $query = $readConnection->query($statement);
            $items = array();
            while ($record = $query->fetch())
            {
                switch($record['type_id'])
                {
                    case 'bundle':
                        $stock = $this->getBundleStock($record['product_id']);
                        break;
                    default:
                        if (
                            $this->useBackorders() &&
                            (
                                ($record['use_config_backorders'] && $this->getConfigBackordersEnabled())
                                ||
                                (!$record['use_config_backorders'] && $record['backorders'] > 0)
                            )
                        )
                        {
                            $stock = self::BACKORDER_DEFAULT_STOCK;
                        } else
                        {
                            if ($record['is_in_stock'])
                            {
                                $stock  = intval($record['qty']);
                                $stock -= intval($record['use_config_min_qty'] == 1 ? $this->getConfigMinQty() : $record['min_qty']);
                            }
                            else
                            {
                                $stock = 0;
                            }
                        }
                        break;
                }

                $stock = $this->dispatchStockEvent(
                    (int)$record['item_id'],
                    (int)$record['product_id'],
                    $stock
                );

                $items[(int)$record['product_id']] = $stock;
            }
            if ($productId)
            {
                return isset($items[$productId]) ? $items[$productId] : false;
            } else
            {
                return $items;
            }
        }

        public function getBundleStock($productId)
        {
            $product = Mage::getModel('catalog/product')
                ->load($productId)
            ;

            $options = Mage::getModel('bundle/option')
                ->getResourceCollection()
                ->setProductIdFilter($productId)
                ->setPositionOrder()
            ;

            $selections = $product->getTypeInstance(true)
                ->getSelectionsCollection(
                    $product->getTypeInstance(true)->getOptionsIds($product),
                    $product
                )
            ;

            $bundleStock = false;
            foreach ($options->getItems() as $option)
            {
                if ($option->getType() != 'checkbox' || $option->getRequired() != 1)
                {
                    $bundleStock = 0;
                    break;
                }

                foreach ($selections as $selection)
                {
                    if ($option->getId() != $selection->getOptionId())
                    {
                        continue;
                    }

                    $selectionQty = floatval($selection->getSelectionQty());
                    $bundleProductStock = floor($this->getStock($selection->getEntityId()) / $selectionQty);

                    if ($bundleStock === false || $bundleProductStock < $bundleStock)
                    {
                        $bundleStock = $bundleProductStock;
                    }
                }
            }

            return (int)$bundleStock;
        }
    }