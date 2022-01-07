<?php

    class KoekEnPeer_EffectConnect_Adminhtml_EffectconnectexportController extends Mage_Adminhtml_Controller_Action
    {
        public function indexAction()
        {
            Mage::getModel('effectconnect/export_products')->exportProducts();

            Mage::getSingleton('adminhtml/session')
                ->addSuccess(
                    Mage::helper('adminhtml')
                        ->__('Product export was created succesfully.')
                )
            ;

            $this->_redirect('adminhtml/system_config/edit/section/effectconnect_options');
        }
    }