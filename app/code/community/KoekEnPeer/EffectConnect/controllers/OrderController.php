<?php

    class KoekEnPeer_EffectConnect_OrderController extends Mage_Core_Controller_Front_Action
    {
        public function preDispatch()
        {
            Mage::helper('effectconnect/data')->validateCall($this);
        }

        public function indexAction()
        {
            $result = Mage::getModel('effectconnect/order')
                ->create(
                    file_get_contents('php://input'),
                    !is_null($this->getRequest()->getParam('debug'))
                )
            ;

            Mage::helper('effectconnect/data')->showResult(
                $this,
                $result['data'],
                $result['success']
            );

            return true;
        }
    }