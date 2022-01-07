<?php
/**
 * Techtwo
 *
 * @category    Techtwo
 * @package     Techtwo_Orderstatus
 * @copyright   Copyright (c) 2010 Techtwo (http://www.techtwo.nl)
 */

/**
 * Orderstatus API
 *
 * @category   Techtwo
 * @package    Techtwo_Orderstatus
 */
class Techtwo_Orderstatus_Model_Statusmodel_Api extends Mage_Api_Model_Resource_Abstract
{

    /**
     * Add comment to order and change status
     *
     * @param string $orderIncrementId
     * @param string $status
     * @param string $comment
     * @param boolean $notify (sends an notification to customer)
     * @return boolean
     */
    public function changeStatus($orderIncrementId, $status, $comment = null, $notify = false)
    {
        $order = $this->_initOrder($orderIncrementId);

        $order->addStatusToHistory($status, $comment, $notify);
        
        //Mage::log('Current Status : '.$status." for order number: ".$orderIncrementId, null, 'orderstatusfromm.log');
        try {
            if ($notify && $comment) {
                $oldStore = Mage::getDesign()->getStore();
                $oldArea = Mage::getDesign()->getArea();
                Mage::getDesign()->setStore($order->getStoreId());
                Mage::getDesign()->setArea('frontend');
            }

            $order->save();
            $order->sendOrderUpdateEmail($notify, $comment);
            if ($notify && $comment) {
                Mage::getDesign()->setStore($oldStore);
                Mage::getDesign()->setArea($oldArea);
            }
            
            /* Add the logic to complet order From Ankita Pancholi start 28/12/2015 */
            if($status=="processing_processed"){
                 $qty=array();
                    foreach($order->getAllItems() as $eachOrderItem){

                            $Itemqty=0;
                             $Itemqty = $eachOrderItem->getQtyOrdered()
                                - $eachOrderItem->getQtyShipped()
                                - $eachOrderItem->getQtyRefunded()
                                - $eachOrderItem->getQtyCanceled();
                            $qty[$eachOrderItem->getId()]=$Itemqty;
//Mage::log('Ordered Qty : '.$Itemqty." for order number: ".$orderIncrementId, null, 'orderstatusfromm.log');
                    }
                    
                    /* check order shipment is prossiable or not */

                    $email=true;
                    $includeComment=true;

                    if ($order->canShip()) {
                             /* @var $shipment Mage_Sales_Model_Order_Shipment */
                                    /* prepare to create shipment */
                            /* $shipment = $order->prepareShipment($qty);
                              if ($shipment) {
                                      $shipment->register();
                                      $shipment->addComment($comment, $email && $includeComment);
                                      $shipment->getOrder()->setIsInProcess(true);
                                try {
                                    $transactionSave = Mage::getModel('core/resource_transaction')
                                        ->addObject($shipment)
                                        ->addObject($shipment->getOrder())
                                        ->save();
                                    //$shipment->sendEmail($email, ($includeComment ? $comment : ''));
                                } catch (Mage_Core_Exception $e) {
                                                    var_dump($e);
                                }

                              }*/
                             $order = $this->_initOrder($orderIncrementId);
                             //$order->setState(Mage_Sales_Model_Order::STATE_COMPLETE,true);//->save();
                             $order->setData('state', "complete");
                             $order->setStatus("complete");
                             $history = $order->addStatusHistoryComment('', false);
                             $history->setIsCustomerNotified(false);
                             $order->save();
                    } 
            }
            
            /* Add the logic to complet order From Ankita Pancholi end 28/12/2015 */
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

        return $order;
    }
}
