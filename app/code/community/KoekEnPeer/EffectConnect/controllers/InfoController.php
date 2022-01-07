<?php

    class KoekEnPeer_EffectConnect_InfoController extends Mage_Core_Controller_Front_Action
    {
        public function preDispatch()
        {
            Mage::helper('effectconnect/data')->validateCall($this);
        }

        public function indexAction()
        {
            try {
                Mage::app()->getConfig()->reinit();

                $helper = Mage::helper('effectconnect');

                $info = array(
                    'version'       => $helper->getExtensionVersion(),
                    'configVerison' => $helper->getExtensionVersion(false),
                    'adminLocation' => Mage::helper('adminhtml')->getUrl('adminhtml'),
                    'zipExtension'  => extension_loaded('zip')
                );

                $folders = array(
                    'media' => 'effectconnect',
                    'var'   => 'effectconnect' . DS . 'export'
                );

                foreach ($folders as $baseDir => $subDirectory) {
                    $folder = Mage::getBaseDir($baseDir) . DS . $subDirectory;
                    $folderExists = file_exists($folder);

                    $info[$baseDir . 'Folder'] = array(
                        'location'  => $folder,
                        'existing'  => $folderExists,
                        'writeable' => $folderExists ? is_writable($folder) : false
                    );
                }

                $success = true;
                $content = array_merge(
                    $info,
                    array(
                        'settings' => Mage::getStoreConfig('effectconnect_options'),
                        'mapping' => Mage::getModel('effectconnect/mapping')->getCollection()->toArray()
                    )
                );
            } catch (Exception $e) {
                $success = false;
                $content = $e->getMessage();
            }

            Mage::helper('effectconnect/data')->showResult(
                $this,
                $content,
                $success
            );

            return true;
        }
    }