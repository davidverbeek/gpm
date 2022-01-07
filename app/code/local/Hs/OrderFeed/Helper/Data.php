<?php

class Hs_OrderFeed_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * 
     * @return array of Orders
     */
    public function getOrders(){
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $strSkuQuery = "SELECT sku,price_incl_tax,base_price,created_at,SUM(qty_ordered) AS qty_ordered
                    FROM ". $resource->getTableName('sales/order_item') ."
                    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND created_at < CURDATE()
                    GROUP BY created_at,sku HAVING ( COUNT(sku) >= 1 )";
        $resultSet = $readConnection->fetchAll($strSkuQuery);
        unset($resource);
        unset($readConnection);
        return $resultSet;
    }
    /**
     * 
     * @return array of specific date Orders
     */
    public function getDateOrders($date){
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $strSkuQuery = "SELECT sku,price_incl_tax,base_price,created_at,SUM(qty_ordered) AS qty_ordered
                    FROM ". $resource->getTableName('sales/order_item') ."
                    WHERE DATE(created_at) = DATE('$date')
                    GROUP BY created_at,sku HAVING ( COUNT(sku) >= 1 )";
        $resultSet = $readConnection->fetchAll($strSkuQuery);
        unset($resource);
        unset($readConnection);
        return $resultSet;
    }
}
