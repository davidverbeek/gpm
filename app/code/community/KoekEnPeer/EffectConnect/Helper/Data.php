<?php

    class KoekEnPeer_EffectConnect_Helper_Data extends Mage_Core_Helper_Abstract
    {
        const APP_URL = 'https://go.effectconnect.com/';

        const CACHE_LIFETIME            = 86400;
        const CACHE_TAG                 = 'effectconnect_cache';
        const CACHE_KEY_IP_WHITELISTING = 'effectconnect_ip_whitelisting';
        const CACHE_KEY_CHANNELS        = 'effectconnect_channels';

        const VALIDATION_ERROR_LOADING_WHITELISTING = 'error_loading_whitelisting';
        const VALIDATION_ERROR_INVALID_IP_ADDRESS   = 'error_invalid_ip_address';
        const VALIDATION_ERROR_INVALID_SIGNATURE    = 'error_invalid_signature';

        public function validateCall($controller)
        {
            $api    = Mage::getModel('effectconnect/api');
            $errors = array();

            $cache       = Mage::app()->getCache();
            $ipAddresses = $cache->load(self::CACHE_KEY_IP_WHITELISTING);
            if (!$ipAddresses)
            {
                $ipAddresses = $api->getWhitelisting();
                if (!$ipAddresses)
                {
                    $errors[] = self::VALIDATION_ERROR_LOADING_WHITELISTING;
                } else
                {
                    $cache->save(
                        Mage::helper('core')->jsonEncode($ipAddresses),
                        self::CACHE_KEY_IP_WHITELISTING,
                        array(self::CACHE_TAG),
                        self::CACHE_LIFETIME
                    );
                }
            } else
            {
                $ipAddresses = Mage::helper('core')->jsonDecode($ipAddresses);
            }

            if ($ipAddresses && !in_array($this->getIpAddress(), $ipAddresses))
            {
                $errors[] = self::VALIDATION_ERROR_INVALID_IP_ADDRESS;
            }

            if (empty($errors))
            {
                $request = Mage::app()->getRequest();
                $action  = $request->getControllerName().'/'.$request->getActionName();
                if (!$api->validateSignature($action))
                {
                    $errors[] = self::VALIDATION_ERROR_INVALID_SIGNATURE;
                }
            }

            if (empty($errors))
            {
                return true;
            }

            $this->showResult(
                $controller,
                array(
                    'error_message' => 'There was an error while validating the request.',
                    'error_codes' => $errors
                ),
                false,
                401
            );

            $controller->getResponse()->sendResponse();

            exit;
        }

        public function getIpAddress()
        {
            if (isset($_SERVER['HTTP_CF_CONNECTING_IP']))
            {
                return $_SERVER['HTTP_CF_CONNECTING_IP'];
            }

            return Mage::helper('core/http')->getRemoteAddr(false);
        }

        public function showResult($controller, $data, $success = true, $httpCode = null)
        {
            if ($data instanceof Varien_Object)
            {
                $data = $data->debug();
            }

            if (!$success && is_null($httpCode))
            {
                $httpCode = 500;
            }

            if ($httpCode)
            {
                http_response_code($httpCode);
            }

            if (is_null($success))
            {
                $contentType = 'text/plain';
                $body        = $data;
            } else
            {
                $contentType = 'application/json';
                $body        = Mage::helper('core')->jsonEncode(array(
                    'version' => $this->getExtensionVersion(),
                    'success' => $success,
                    'data'    => $data
                ));
            }

            $controller
                ->getResponse()
                ->clearHeaders()
                ->setHeader('Content-type', $contentType)
                ->setBody($body)
            ;
        }

        public function getChannels()
        {
            $cache       = Mage::app()->getCache();
            $channels = $cache->load(self::CACHE_KEY_CHANNELS);
            if (!$channels)
            {
                $channelData = Mage::getModel('effectconnect/api')->getChannels();
                if (!$channelData)
                {
                    return array();
                }

                $channels = array();
                foreach ($channelData as $channel)
                {
                    $channels[$channel['id']] = $channel['name'];
                }

                $cache->save(
                    Mage::helper('core')->jsonEncode($channels),
                    self::CACHE_KEY_CHANNELS,
                    array(self::CACHE_TAG),
                    self::CACHE_LIFETIME
                );
            } else
            {
                $channels = Mage::helper('core')->jsonDecode($channels);
            }

            return $channels;
        }

        public function getExtensionVersion($config = true)
        {
            if ($config)
            {
                return (string)Mage::getConfig()->getNode()->modules->KoekEnPeer_EffectConnect->version;
            } else
            {
                $configLocation = Mage::getModuleDir('etc', 'KoekEnPeer_EffectConnect').DS.'config.xml';

                $configXml = simplexml_load_file($configLocation);
                return (string)$configXml->modules->KoekEnPeer_EffectConnect->version;
            }
        }

        public function getTaxRates($returnRateId = false, $store = null)
        {
            if (is_numeric($store))
            {
                $store = Mage::app()->getStore($store);
            }

            $taxRates = Mage::helper('core')
                ->jsonDecode(Mage::helper('tax')->getAllRatesByProductClass($store))
            ;

            $rates = array();
            foreach($taxRates as $key=>$percentage)
            {
                $key = explode('_', $key, 2);
                $rateId = end($key);

                if ($returnRateId && $rateId == $returnRateId)
                {
                    return $percentage;
                }

                $rates[$rateId] = $percentage;
            }

            if ($returnRateId !== false)
            {
                return false;
            }

            return $rates;
        }

        public function getBaseUrl($folder = null)
        {
            $customRoot = Mage::getStoreConfig('effectconnect_options/custom/root');

            if (filter_var($customRoot, FILTER_VALIDATE_URL))
            {
                if(substr($customRoot,-1) != '/')
                {
                    $customRoot .= '/';
                }

                if ($folder)
                {
                    $customRoot .= substr(
                       Mage::getConfig()->getOptions()->getData($folder.'_dir'),
                       strlen(Mage::getConfig()->getOptions()->getBaseDir())+1
                   ).'/';
                }

                return $customRoot;
            }

            return Mage::app()
                ->getStore(
                    Mage::app()
                        ->getWebsite(true)
                        ->getDefaultGroup()
                        ->getDefaultStoreId()
                )
                ->getBaseUrl($folder ? $folder : Mage_Core_Model_Store::URL_TYPE_LINK)
            ;
        }

        public function validateEan($input)
        {
            $input = trim((string)$input);
            $input = ltrim($input, '0');

            $inputLength = strlen($input);

            if (empty($input) || !ctype_digit($input) || $inputLength <= 10)
            {
                return false;
            }

            if ($inputLength < 13)
            {
                $input = str_pad(
                    $input,
                    13,
                    '0',
                    STR_PAD_LEFT
                );
            } elseif ($inputLength > 13)
            {
                return false;
            }

            $barcode = substr($input, 0, -1);
            $barcode = strrev($barcode);
            $oddSum  = 0;
            $evenSum = 0;

            for ($i=0; $i < 12; $i++){
                if ($i % 2 === 0)
                {
                    $oddSum  += $barcode[$i] * 3;
                } else
                {
                    $evenSum += $barcode[$i];
                }
            }

            $calculation = ($oddSum + $evenSum) % 10;
            $checksum    = $calculation === 0 ? 0 : 10 - $calculation;

            if ($input[12] != $checksum)
            {
                return false;
            }

            return $input;
        }

        /**
         * @param Mage_Catalog_Model_Product $product
         * @param int $storeId
         *
         * @return bool|int
         */
        public function getProductParentId($product, $storeId = null)
        {
            $parentIds = array();
            foreach (Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild($product->getId()) as $parentId)
            {
                if (!$parentId)
                {
                    continue;
                }

                $parentProduct = Mage::getModel('catalog/product')->load($parentId);
                if ($parentProduct->getTypeId() != Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)
                {
                    continue;
                }

                $parentIds[] = $parentId;
            }

            if (empty($parentIds))
            {
                return false;
            }

            /** @var Mage_Catalog_Model_Resource_Product_Status $productStatusModel */
            $productStatusModel = Mage::getSingleton('catalog/product_status');
            $activeParentIds    = array();
            foreach ($productStatusModel->getProductStatus($parentIds, $storeId) as $parentId => $status)
            {
                if ($status != 1)
                {
                    continue;
                }

                $activeParentIds[] = $parentId;
            }

            $matchingParentSkus = array();
            foreach (Mage::getResourceSingleton('catalog/product')->getProductsSku($parentIds) as $parent)
            {
                if (stripos($product->getSku(), $parent['sku']) !== false)
                {
                    $matchingParentSkus[] = $parent['entity_id'];
                }
            }

            foreach ($activeParentIds as $activeParentId)
            {
                if (in_array($activeParentId, $matchingParentSkus))
                {
                    return $activeParentId;
                }
            }

            if (!empty($activeParentIds))
            {
                return reset($activeParentIds);
            }

            if (!empty($matchingParentSkus))
            {
                return reset($matchingParentSkus);
            }

            return reset($parentIds);
        }
    }