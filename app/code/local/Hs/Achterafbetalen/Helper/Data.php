<?php

/**
 * Class Hs_Achterafbetalen_Helper_Data
 */
Class Hs_Achterafbetalen_Helper_Data extends Mage_Core_Helper_Abstract
{


    public function getLogoUrl()
    {

        return Mage::getBaseUrl('media') . 'achterafbetalen/' . Mage::getStoreConfig(Hs_Achterafbetalen_Model_Constant::PATH_LOGO);

    }


    /**
     * Function: This method is used to cancel the order based on the data return by the payment provider
     * @param $result - error string
     */
    public function cancelOrder($result = '')
    {

        /*** get last order and cancel the order **/

        $session = Mage::getSingleton('checkout/session');

        // Retrieve icepay order
        if ($session->getLastRealOrderId()) {
            $orderId = $session->getLastRealOrderId();
        } else {
            $coresession = Mage::getSingleton('core/session');
            $orderIds = $coresession->getOrderIds();
            foreach ($orderIds as $order) {
                $orderId = $order;
            }
        }

        if (!empty($orderId)) {

            try {
                $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
                $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, sprintf($this->__("The payment provider has returned the following error message: %s"), $result));
                $order->setStatus("canceled");
                $order->save();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        /*** get last order and cancel the order ends here **/

    }


}