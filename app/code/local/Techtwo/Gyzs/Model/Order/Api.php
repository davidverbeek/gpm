<?php
/**
 * Techtwo
 *
 * @category    Techtwo
 * @package     Techtwo_Gyzs
 * @copyright   Copyright (c) 2012 Techtwo (http://www.techtwo.nl)
 */

/**
 * Gyzs order API
 *
 * @category   Techtwo
 * @package    Techtwo_Gyzs
 */
class Techtwo_Gyzs_Model_Order_Api extends Mage_Api_Model_Resource_Abstract
{

    /**
     * Update Mavis Order ID for order
     *
     * @param string $orderIncrementId
     * @param string $mavisOrderId
     * @return boolean
     */
    //public function mavisorderupdateUpdate($orderIncrementId, $mavisOrderId)
    public function mavisorderupdateUpdate($mavisOrderId)
    {
    	//$order = $this->_initOrder($orderIncrementId);

        try {
            $order->setMavisOrderId($mavisOrderId)->save();
            //Mage::log("Upate MAVIS Order ID :".$mavisOrderId,null,'orderpinfo.log');

        } catch (Mage_Core_Exception $e) {
            $this->_fault('status_not_changed', $e->getMessage());
        }

        return true;
    }
    
    /**
     * Initialize basic order model
     *
     * @param mixed $orderIncrementId
     * @return Mage_Sales_Model_Order
     */
    protected function _initOrder($orderIncrementId)
    {
        $order = Mage::getModel('sales/order');

        /* @var $order Mage_Sales_Model_Order */

        $order->loadByIncrementId($orderIncrementId);

        if (!$order->getId()) {
            $this->_fault('not_exists');
        }
		//Mage::log("You are Fetching the order ID :",null,'orderpinfo.log');
        return $order;
    }
}
