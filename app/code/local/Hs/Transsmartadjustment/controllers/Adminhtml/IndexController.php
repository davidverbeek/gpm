<?php
require_once "Transsmart/Shipping/controllers/Adminhtml/Transsmart/Shipping/ShipmentController.php";

/**
 * class        Hs_Transsmartadjustment_Adminhtml_IndexController
 * @category    Transsmart
 * @package     Hs_Transsmartadjustment
 * @author      Hs_SJD
 * @date        06 June, 2018
 *
 */
class Hs_Transsmartadjustment_Adminhtml_IndexController extends Transsmart_Shipping_Adminhtml_Transsmart_Shipping_ShipmentController
{
    /**
     * Mass Create Shipment Action
     */
    public function shipmentCarrierChangeAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        if ($orderId != '') {
            
            $order = Mage::getModel('sales/order')->load($orderId);
            if (!Mage::helper('transsmart_shipping')->isTranssmartOrder($order)) {
                $this->_getSession()->addError(
                    $this->__('This is not a transsmart order.')
                );
                $this->_redirect('adminhtml/sales_order/index');
                return;
            }

            if($order->getShippingAddress()->getCountryId() != 'NL') {

                $this->_getSession()->addError(
                    $this->__('Shipment carrier can be changed only for Netherlands shipping addresses.')
                );
                $this->_redirect('adminhtml/sales_order/index');
                return;
            }


            $qty = array();
            $email = true;
            $includeComment = true;
            $comment = "";
            foreach ($order->getAllItems() as $eachOrderItem) {
                $Itemqty = 0;
                $Itemqty = $eachOrderItem->getQtyOrdered()
                    - $eachOrderItem->getQtyShipped()
                    - $eachOrderItem->getQtyRefunded()
                    - $eachOrderItem->getQtyCanceled();
                $qty[$eachOrderItem->getId()] = $Itemqty;

            }

            if ($order->canShip()) {

                $shipment = $order->prepareShipment($qty);
                if ($shipment) {
                    $shipment->register();
                    $shipment->addComment($comment, $email && $includeComment);
                    $shipment->getOrder()->setIsInProcess(true);
                    $carrierChange = Mage::getStoreConfig('sales/hs_sales/carrier_change');
                    $order->setShippingDescription(Mage::getStoreConfig("transsmart_carrier_profiles/carrierprofile_" . $carrierChange . "/title"));
                    $order->setShippingMethod('transsmartdelivery_carrierprofile_' . $carrierChange);
                    $order->save();
                    $shipment->setTranssmartCarrierprofileId($carrierChange);

                    try {
                        $transactionSave = Mage::getModel('core/resource_transaction')
                            ->addObject($shipment)
                            ->addObject($shipment->getOrder())
                            ->save();
                        //$shipment->sendEmail($email, ($includeComment ? $comment : ''));
                        $this->_getSession()->addSuccess(
                            $this->__('Shipment Carrier is successfully changed for order #' . $order->getIncrementId() . '.')
                        );

                    } catch (Mage_Core_Exception $e) {
                        echo $e->getMessage();
                    }
                }
            } else {
                $this->_getSession()->addError(
                    $this->__('Can not do shipment for this order.')
                );

            }
        }

        $this->_redirect('adminhtml/sales_order/index');
    }

    /**
     * Book and print selected Transsmart shipments.
     */
    public function massBookAndPrintUser1Action()
    {
        if(!$this->_createShipmentPackage()) {
            $this->_redirect('adminhtml/sales_order/index');
            return;
        }
        parent::massBookAndPrintAction();

        $this->_redirect('adminhtml/sales_order/index');
    }

    /**
     * Book and print selected Transsmart shipments.
     */
    public function massBookAndPrintUser2Action()
    {
        if(!$this->_createShipmentPackage()) {
            $this->_redirect('adminhtml/sales_order/index');
            return;
        }

        $shipmentCollection = $this->_getMassActionShipmentCollection();
        $shipmentHelper = Mage::helper('transsmartadjustment/shipment');

        $totalCount = 0;
        $successCount = 0;
        if ($shipmentCollection) {
            try {
                $shipmentHelper->doMassBookAndPrint($shipmentCollection);
            }
            catch (Mage_Core_Exception $_exception) {
                $this->_getSession()->addError(
                    $this->__(
                        'One or more shipments could not be booked and printed: %s',
                        $_exception->getMessage()
                    )
                );
            }

            /** @var Mage_Sales_Model_Order_Shipment $_shipment */
            foreach ($shipmentCollection as $_shipment) {
                // check if Transsmart shipping labels have been printed
                if ($_shipment->getTranssmartDocumentId()) {
                    $totalCount++;

                    if ($_shipment->getTranssmartStatus() == 'LABL') {
                        $successCount++;
                    }
                    else {
                        $_shipmentError = $_shipment->getTranssmartShipmentError();
                        if (empty($_shipmentError)) {
                            $_shipmentError = $this->__('Unknown error');
                        }

                        $this->_getSession()->addError(
                            $this->__(
                                'Shipment #%s for order #%s could not be booked and printed: %s',
                                $_shipment->getIncrementId(),
                                $_shipment->getOrder()->getIncrementId(),
                                $_shipmentError
                            )
                        );
                    }
                }
            }
        }

        if ($totalCount == 0) {
            if ($this->_isMassActionFromOrders()) {
                $this->_getSession()->addError(
                    $this->__('There are no Transsmart documents related to selected order(s).')
                );
            }
            else {
                $this->_getSession()->addError(
                    $this->__('There are no Transsmart documents related to selected shipment(s).')
                );
            }
        }
        elseif ($successCount) {
            $this->_getSession()->addSuccess(
                $this->__('Successfully booked and printed %s Transsmart shipments.', $successCount)
            );
        }

        if ($this->_isMassActionFromOrders()) {
            $this->_redirect('*/sales_order/index');
        }
        else {
            $this->_redirect('*/sales_order_shipment/index');
        }

        $this->_redirect('adminhtml/sales_order/index');
    }

    /**
     * SJD++ 17052018,
     * Create shipment if not created for an order.
     * Return false if error occured during creating shipment package.
     *
     * @return bool
     */
    protected function _createShipmentPackage()
    {
        $orderId = $this->getRequest()->getParam('order_ids');
        if (!$orderId) {
            $this->_getSession()->addError(
                $this->__('No order selected.')
            );
            $this->_redirect('adminhtml/sales_order/index');
            return false;
        }

        $order = Mage::getModel('sales/order')->load($orderId);
        if (!Mage::helper('transsmart_shipping')->isTranssmartOrder($order)) {
            $this->_getSession()->addError(
                $this->__('This is not a transsmart order.')
            );
            $this->_redirect('adminhtml/sales_order/index');
            return false;
        }

        $qty = array();
        $email = true;
        $includeComment = true;
        $comment = "";
        foreach ($order->getAllItems() as $eachOrderItem) {
            $itemQty = $eachOrderItem->getQtyOrdered()
                - $eachOrderItem->getQtyShipped()
                - $eachOrderItem->getQtyRefunded()
                - $eachOrderItem->getQtyCanceled();
            $qty[$eachOrderItem->getId()] = $itemQty;
            unset($itemQty);
        }

        if ($order->canShip()) {
            $shipment = $order->prepareShipment($qty);
            if ($shipment) {
                $shipment->register();
                $shipment->addComment($comment, $email && $includeComment);
                $shipment->getOrder()->setIsInProcess(true);
                $order->save();

                try {
                    Mage::getModel('core/resource_transaction')
                        ->addObject($shipment)
                        ->addObject($shipment->getOrder())
                        ->save();
                } catch (Mage_Core_Exception $e) {
                    Mage::log($e->getMessage(), null, 'transsmart_errors.log');
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * SJD++ 06062018
     * Copied from Transsmart_Shipping_Adminhtml_Transsmart_Shipping_ShipmentController
     * Get the shipments to which the mass action should be applied, or NULL if none are selected.
     *
     * @return Mage_Sales_Model_Resource_Order_Shipment_Collection|null
     */
    protected function _getMassActionShipmentCollection()
    {
        $request = $this->getRequest();
        $shipmentCollection = null;
        if ($request->has('shipment_ids')) {
            $ids = $request->getParam('shipment_ids');
            array_filter($ids, 'intval');
            if (!empty($ids)) {
                $shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection')
                    ->addFieldToFilter('entity_id', array('in' => $ids));
            }
        }
        elseif ($request->has('order_ids')) {
            $ids = $request->getParam('order_ids');
            array_filter($ids, 'intval');
            if (!empty($ids)) {
                $shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection')
                    ->setOrderFilter(array('in' => $ids));
            }
        }

        return $shipmentCollection;
    }
}
