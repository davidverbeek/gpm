<?php

    class KoekEnPeer_EffectConnect_Adminhtml_EffectconnectconnectController extends Mage_Adminhtml_Controller_Action
    {
        protected $keys = array(
            'shop_key',
            'public_key',
            'secret_key'
        );

        public function indexAction()
        {
            if (isset($_GET['root']) && isset($_GET['timestamp']) && isset($_GET['hash']))
            {
                if ($_GET['hash'] == hash_hmac('sha256', strrev($_GET['root'].$_GET['timestamp']), 'effectconnectMagento'))
                {
                    foreach ($this->keys as $key)
                    {
                        if (isset($_GET[$key]))
                        {
                            Mage::getModel('core/config')
                                ->saveConfig('effectconnect_options/credentials/'.$key, $_GET[$key])
                            ;
                        }
                    }
                }
            }
            Mage::getSingleton('adminhtml/session')
                ->addSuccess(
                    Mage::helper('adminhtml')
                        ->__('EffectConnect was set up successfully.')
                )
            ;
            $this->_redirect('adminhtml/system_config/edit/section/effectconnect_options');
        }
    }