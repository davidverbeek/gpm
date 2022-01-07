<?php

    class KoekEnPeer_EffectConnect_ApiController extends Mage_Core_Controller_Front_Action
    {
        public function preDispatch()
        {
            Mage::helper('effectconnect/data')->validateCall($this);
        }

        public function indexAction()
        {
            $success = true;
            $logFile = $this->_getLogFileLocation();
            if (file_exists($logFile))
            {
                $content = file_get_contents($logFile);
            } else
            {
                $content = 'No log file available';
            }

            Mage::helper('effectconnect/data')->showResult(
                $this,
                $content,
                $success
            );

            return true;
        }

        public function testAction()
        {
            $result = Mage::getModel('effectconnect/api')->testConnection();

            Mage::helper('effectconnect/data')->showResult(
                $this,
                $result,
                $result ? true : false
            );

            return true;
        }

        public function resetAction()
        {
            $success = true;
            $logFile = $this->_getLogFileLocation();
            if (file_exists($logFile))
            {
                unlink($logFile);
                $content = 'API log file deleted';
            } else
            {
                $content = 'No log file available';
                $success = false;
            }

            Mage::helper('effectconnect/data')->showResult(
                $this,
                $content,
                $success
            );

            return true;
        }

        private function _getLogFileLocation()
        {
            return Mage::getBaseDir().DS.'var'.DS.'effectconnect'.DS.'api_log.txt';
        }
    }