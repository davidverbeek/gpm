<?php

    class KoekEnPeer_EffectConnect_ExportController extends Mage_Core_Controller_Front_Action
    {
        public function preDispatch()
        {
            Mage::helper('effectconnect/data')->validateCall($this);
        }

        public function indexAction()
        {
            $this->productsAction();

            return true;
        }

        public function productsAction()
        {
            Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

            $exportModel = Mage::getModel('effectconnect/export_products');
            $force = !is_null($this->getRequest()->getParam('force'));

            if ($force)
            {
                if ($force !== 'hard' && $exportModel->isExportActive())
                {
                    Mage::helper('effectconnect/data')->showResult(
                        $this,
                        'Export is still active (last update: '.date('d-m-Y H:i:s', Mage::getStoreConfig(KoekEnPeer_EffectConnect_Model_Export::CONFIG_CRON_EXPORT_ACTIVE_KEY)).')',
                        false
                    );

                    return true;
                }

                Mage::getModel('core/config')->deleteConfig(KoekEnPeer_EffectConnect_Model_Export::CONFIG_CRON_EXPORT_QUEUE_KEY);
                $exportResult = $exportModel->exportProducts($force);

                Mage::helper('effectconnect/data')->showResult(
                    $this,
                    $exportResult ? $exportResult : 'There was a problem exporting the products',
                    $exportResult ? true : false
                );

                return true;
            }

            $exportQueue = Mage::getStoreConfig(KoekEnPeer_EffectConnect_Model_Export::CONFIG_CRON_EXPORT_QUEUE_KEY);

            if ($exportQueue && is_null($this->getRequest()->getParam('reset')))
            {
                $exportActive = Mage::getStoreConfig(KoekEnPeer_EffectConnect_Model_Export::CONFIG_CRON_EXPORT_ACTIVE_KEY);
                $exportStatus = Mage::getStoreConfig(KoekEnPeer_EffectConnect_Model_Export::CONFIG_CRON_EXPORT_STATUS_KEY);

                Mage::helper('effectconnect/data')->showResult(
                    $this,
                    'Product export queue already set: '.date('d-m-Y H:i:s',$exportQueue).' ('.(time()-$exportQueue).' second(s) ago).'.PHP_EOL.
                    'Export active: '.($exportActive ? 'yes, last update: '.(time()-$exportActive).' second(s) ago, status: '.$exportStatus : 'no'),
                    false
                );

                return true;
            }

            $timestamp = time();
            Mage::getModel('core/config')->saveConfig(KoekEnPeer_EffectConnect_Model_Export::CONFIG_CRON_EXPORT_QUEUE_KEY, $timestamp);

            Mage::helper('effectconnect/data')->showResult(
                $this,
                'Product export queue successfully set ('.date('d-m-Y H:i:s', $timestamp).').'
            );

            Mage::app()->getCacheInstance()->cleanType('config');

            return true;
        }

        public function logAction()
        {
            $logFile = Mage::getBaseDir().DS.'var'.DS.'effectconnect'.DS.'export'.DS.'log.txt';
            if (file_exists($logFile))
            {
                if (!is_null($this->getRequest()->getParam('clear')))
                {
                    unlink($logFile);
                    $content = 'Log cleared';
                } else
                {
                    $content = file_get_contents($logFile);
                }
            } else
            {
                $content = 'No log file available';
            }

            Mage::helper('effectconnect/data')->showResult(
                $this,
                $content,
                null
            );

            return true;
        }

        public function stockAction()
        {
            $id = $this->getRequest()
                ->getParam('id')
            ;

            Mage::helper('effectconnect/data')->showResult(
                $this,
                Mage::getModel('effectconnect/export_stock')->getStock($id)
            );

            return true;
        }

        public function priceAction()
        {
            $id = $this->getRequest()
                ->getParam('id')
            ;

            Mage::helper('effectconnect/data')->showResult(
                $this,
                Mage::getModel('effectconnect/export_price')->getPrice($id)
            );

            return true;
        }
    }