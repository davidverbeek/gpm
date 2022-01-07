<?php

    /**
     * @category    EffectConnect
     * @package     API
     * @copyright   Copyright 2015 Koek&Peer
     * @link        http://www.koekenpeer.nl
     * @version     1 / 2015-04-20
     */
    class KoekEnPeer_EffectConnect_Model_Api
    {
        const CLIENT_VERSION = '1';
        const SERVER_HOST = 'https://importer.effectconnect.com/api/';

        private $shop;
        private $publicKey;
        private $secretKey;
        private $language = 'nl';

        public function __construct($language = 'nl')
        {
            $this->_setShop(Mage::getStoreConfig('effectconnect_options/credentials/shop_key'));
            $this->_setPublicKey(Mage::getStoreConfig('effectconnect_options/credentials/public_key'));
            $this->_setSecretKey(Mage::getStoreConfig('effectconnect_options/credentials/secret_key'));
            $this->setLanguage($language);
        }

        private function _getShop()
        {
            return $this->shop;
        }

        private function _setShop($shop)
        {
            $this->shop = $shop;
        }

        private function _getPublicKey()
        {
            return $this->publicKey;
        }

        private function _setPublicKey($publicKey)
        {
            $this->publicKey = $publicKey;
        }

        private function _getSecretKey()
        {
            return $this->secretKey;
        }

        private function _setSecretKey($secretKey)
        {
            $this->secretKey = $secretKey;
        }

        private function _getLanguage()
        {
            return $this->language;
        }

        public function setLanguage($language)
        {
            if (is_string($language) && !empty($language))
            {
                $this->language = $language;
            }
        }

        private function _getHeaders()
        {
            if (function_exists('apache_request_headers'))
            {
                //return apache_request_headers();
            }

            $headers = array();
            $rxHttp = '/\AHTTP_/';
            foreach ($_SERVER as $key => $value)
            {
                if (!preg_match($rxHttp, $key))
                {
                    continue;
                }

                $headerKey = preg_replace($rxHttp, '', $key);
                $rxMatches = explode('_', $headerKey);
                if (count($rxMatches) > 0 && strlen($headerKey) > 2)
                {
                    foreach ($rxMatches as $matchKey => $matchValue)
                    {
                        $rxMatches[$matchKey] = ucfirst(strtolower($matchValue));
                    }
                    $headerKey = implode('-', $rxMatches);
                }

                $headers[$headerKey] = $value;
            }

            return $headers;
        }

        public function validateSignature($resource)
        {
            $headers      = $this->_getHeaders();
            $signature    = false;
            $signData     = array();
            foreach (array(
                'timestamp',
                'language',
                'token',
                'method',
                'resource',
                'data',
                'signature'
            ) as $headerItem)
            {
                switch ($headerItem)
                {
                    case 'method':
                        $signData[$headerItem] = strtoupper($_SERVER['REQUEST_METHOD']);
                        break;
                    case 'resource':
                        $signData[$headerItem] = $resource;
                        break;
                    case 'data':
                        $signData[$headerItem] = file_get_contents('php://input');
                        break;
                    default:
                        $headerKey = 'X-'.ucfirst($headerItem);
                        if (!isset($headers[$headerKey]) || empty($headers[$headerKey]))
                        {
                            return false;
                        }
                        $headerValue = $headers[$headerKey];
                        if ($headerItem == 'signature')
                        {
                            $signature = $headerValue;
                        } else
                        {
                            $signData[$headerItem] = $headerValue;
                        }
                        break;
                }
            }

            if ($signData['timestamp'] - 5400 >= time() || $signData['timestamp'] + 5400 <= time())
            {
                return false;
            }

            if ($signData['token'] != $this->_getPublicKey())
            {
                return false;
            }

            return $signature == $this->_getSignature($signData);
        }

        private function _getSignature(array $signingParameters)
        {
            // Sort parameters by key
            ksort($signingParameters);

            $signingParametersImploded=implode('|', $signingParameters);

            return hash_hmac('sha256', $signingParametersImploded, $this->_getSecretKey());
        }

        private function _getRequestUrl($resource, $parameters = null)
        {
            return self::SERVER_HOST.'v'.self::CLIENT_VERSION.'/'.$this->_getShop().'/'.$resource.'.json'.
                   (!empty($parameters) ? '?'.http_build_query($parameters) : '');
        }

        private function _sendRequest($resource, $method = 'GET', $data = array())
        {
            if (!$this->shop || !$this->publicKey || !$this->secretKey)
            {
                return false;
            }

            // Process data to submit
            $dataEncoded = !empty($data) && $method != 'GET' ? json_encode($data) : '';

            // Create parameters for HTTP headers
            $headerParameters = array(
                'timestamp' => time(),
                'language'  => $this->_getLanguage(),
                'token'     => $this->_getPublicKey()
            );

            // Create parameters for signature
            $signingParameters = array_merge(
                $headerParameters,
                array(
                    'method'   => $method,
                    'resource' => $resource,
                    'data'     => $dataEncoded
                )
            );

            // Create signature
            $headerParameters['signature'] = $this->_getSignature($signingParameters);

            // Get HTTP headers from parameters
            $httpHeaders = array();
            foreach ($headerParameters as $parameterKey => $parameterValue)
            {
                $httpHeaders[] = 'X-'.ucfirst($parameterKey).': '.$parameterValue;
            }

            // Perform cURL
            $curlHandle = curl_init();
            $curlOptions = array(
                CURLOPT_URL            => $this->_getRequestUrl(
                    $resource,
                    $method == 'GET' && !empty($data) ? $data : null
                ),
                CURLOPT_CUSTOMREQUEST  => $method,
                CURLOPT_HEADER         => false,
                CURLOPT_HTTPHEADER     => $httpHeaders,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT        => $method != 'GET' ? 5 : 10
            );

            if (!empty($dataEncoded))
            {
                $curlOptions[CURLOPT_HTTPHEADER] = array_merge(
                    $curlOptions[CURLOPT_HTTPHEADER],
                    array('Content-Type: application/json')
                );

                $curlOptions[CURLOPT_POSTFIELDS] = $dataEncoded;
            }

            // Set cURL options
            foreach ($curlOptions as $curlOption => $curlValue)
            {
                @curl_setopt($curlHandle, $curlOption, $curlValue);
            }

            $responseBody = curl_exec($curlHandle);
            $responseCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);

            if ($responseBody === false){
                $responseBody = 'ERROR';
            }

            curl_close($curlHandle);

            $apiLog = Mage::getBaseDir('var').DS.'effectconnect'.DS.'api_log.txt';
            $logHandle = fopen($apiLog,'a');
            fwrite($logHandle,
                '['.date('Y-m-d H:i:s').'] '.
                str_pad($resource,50,' ',STR_PAD_RIGHT).
                str_pad($responseCode,10,' ',STR_PAD_RIGHT).
                str_pad($dataEncoded,150,' ',STR_PAD_RIGHT).
                ($responseCode < 200 || $responseCode > 299?$responseBody:'').
                PHP_EOL
            );
            fclose($logHandle);

            if ($responseCode < 200 || $responseCode > 299)
            {
                return false;
            }

            return $this->_processResponse($resource,$responseBody);
        }

        private function _processResponse($resource,$responseBody){
            if ($responseBody == '[]')
            {
                return array();
            }

            $responseBodyJson = json_decode($responseBody, true);
            if (!$responseBodyJson)
            {
                return false;
            }

            $resourceExplode = explode('/', $resource);
            $resourceEnd     = end($resourceExplode);
            if ($resourceEnd == 'count')
            {
                return $responseBodyJson['count'];
            }

            return $responseBodyJson;
        }

        private function _getResourceLocation($resource, $id)
        {
            return $resource.($id ? '/'.$id : '');
        }

        private function _prepareRequest($resource, $resourceId = false, $method = 'GET', $data = array())
        {
            return $this->_sendRequest(
                $this->_getResourceLocation($resource, $resourceId),
                $method,
                $data
            );
        }

        public function testConnection()
        {
            return $this->_prepareRequest('test');
        }

        public function getChannels($channelId = false)
        {
            return $this->_prepareRequest('channels', $channelId);
        }

        public function getOrders($orderId = false)
        {
            return $this->_prepareRequest('orders', $orderId);
        }

        public function getOpenOrders()
        {
            return $this->_prepareRequest('orders', 'open');
        }

        public function updateOrder($orderId, $data)
        {
            return $this->_prepareRequest('orders', $orderId, 'PUT', $data);
        }

        public function updateOrderStatus($orderId, $status, $trackingCode = null)
        {
            return $this->_prepareRequest(
                'orders',
                $orderId,
                'PUT',
                array_filter(
                    array(
                        'status'        => $status,
                        'tracking_code' => $trackingCode
                    )
                )
            );
        }

        public function getProductCount()
        {
            return $this->_prepareRequest('products', 'count', 'GET');
        }

        public function getProducts($page = 1)
        {
            if ((int)$page <= 0)
            {
                $page = 1;
            }

            return $this->_prepareRequest('products', false, 'GET', array('page' => $page));
        }

        public function updateProduct($productId, $data)
        {
            return $this->_prepareRequest('products', $productId, 'PUT', $data);
        }

        public function updateProductOption($optionId, $data)
        {
            return $this->_prepareRequest('products_options', $optionId, 'PUT', $data);
        }

        public function getStructure($table = false, $information = false)
        {
            return $this->_prepareRequest('structure', $table, 'GET', array('information' => $information));
        }

        public function submitImport($zipLocation, $type = false)
        {
            return $this->_prepareRequest('import', $type, 'POST', array('location' => $zipLocation));
        }

        public function getWhitelisting()
        {
            return $this->_prepareRequest('whitelisting');
        }

        public function exportStockData($stockData)
        {
            return $this->_prepareRequest('stock', false, 'PUT', $stockData);
        }

        public function exportPriceData($priceData)
        {
            return $this->_prepareRequest('prices', false, 'PUT', $priceData);
        }
    }