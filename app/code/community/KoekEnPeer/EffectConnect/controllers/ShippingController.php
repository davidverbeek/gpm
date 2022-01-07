<?php

    class KoekEnPeer_EffectConnect_ShippingController extends Mage_Core_Controller_Front_Action
    {
        public function preDispatch()
        {
            Mage::helper('effectconnect/data')->validateCall($this);
        }

        public function indexAction()
        {
            $orderIds = false;
            $input    = $this->_getInput();
            if ($input)
            {
                $orderIds = Mage::helper('core')->jsonDecode($input);
            }

            if (!$orderIds)
            {
                $orderIds = explode(',',$this->getRequest()->getParam('id'));
            }

            if (!$orderIds)
            {
                $this->_setContent('No order ID found.');

                return false;
            }

            $orderTracking = Mage::getModel('effectconnect/export_order')->getTracking($orderIds);

            $this->_setContent($orderTracking !== false ? $orderTracking : 'No orders found.');

            return true;
        }

        private function _getInput()
        {
            return file_get_contents('php://input');
        }

        private function _setContent($content)
        {
            if (is_array($content))
            {
                $contentType = 'application/json';
                $content     = Mage::helper('core')->jsonEncode($content);
            }
            else {
                $contentType = 'text/plain';
            }

            $this->getResponse()
                ->clearHeaders()
                ->setHeader('Content-Type', $contentType)
                ->setBody($content)
            ;
        }
    }