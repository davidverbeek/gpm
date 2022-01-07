<?php

    class KoekEnPeer_EffectConnect_Model_Export
    {
        const CONFIG_CRON_EXPORT_QUEUE_KEY  = 'effectconnect_options/cron/export/queue';
        const CONFIG_CRON_EXPORT_ACTIVE_KEY = 'effectconnect_options/cron/export/active';
        const CONFIG_CRON_EXPORT_STATUS_KEY = 'effectconnect_options/cron/export/status';
        const CONFIG_CRON_EXPORT_LAST_KEY   = 'effectconnect_options/cron/export/last';

        protected $api;
        protected $liveSynchronisation;
        protected $useSpecialPrice;
        protected $useBackorders;
        protected $configBackordersEnabled;
        protected $configMinQty;
        protected $configMinSaleQty;
        protected $attributePrice;
        protected $attributePriceFallback;
        protected $attributePriceSurcharge;
        protected $storeView;
        protected $websiteId;
        protected $queries = array();
        protected $roundPrice;
        protected $pricesIncludesTax;
        protected $taxRates;

        public function __construct()
        {
            $this->api                     = Mage::getModel('effectconnect/api');
            $this->liveSynchronisation     = (bool)Mage::getStoreConfig('effectconnect_options/synchronisation/live');
            if (!$this->storeView = Mage::getStoreConfig('effectconnect_options/attributes/store_view'))
            {
                $this->storeView = 0;
            }
            $this->useSpecialPrice         = Mage::getStoreConfig('effectconnect_options/attributes/attribute_special_price') == 1;
            $this->useBackorders           = Mage::getStoreConfig('effectconnect_options/stock/stock_backorders') == 1;
            $this->configBackordersEnabled = Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_BACKORDERS, $this->getStoreView()) > 0;
            $this->configMinQty            = (float)Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MIN_QTY, $this->getStoreView());
            $this->configMinSaleQty        = (float)Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MIN_SALE_QTY, $this->getStoreView());
            $this->attributePrice          = Mage::getStoreConfig('effectconnect_options/attributes/attribute_price');
            $this->attributePriceFallback  = Mage::getStoreConfig('effectconnect_options/attributes/attribute_price_use_fallback') == 1;

            $attributePriceSurcharge       = explode(',', Mage::getStoreConfig('effectconnect_options/attributes/attribute_price_surcharge'));
            $this->attributePriceSurcharge = array_filter($attributePriceSurcharge);
            $this->roundPrice              = Mage::getStoreConfig('effectconnect_options/custom/round_price') == 1;
            $this->pricesIncludesTax       = Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX, $this->storeView) == 1;
        }

        public function getPriceIncludingTax($price, $taxRateId)
        {
            if (!$price || $this->pricesIncludesTax)
            {
                return $price;
            }

            if (!$this->taxRates)
            {
                $this->taxRates = Mage::helper('effectconnect')->getTaxRates(false, $this->storeView);
            }

            if (!isset($this->taxRates[$taxRateId]))
            {
                return $price;
            }

            $priceIncludingTax = $price * ((100 + $this->taxRates[$taxRateId]) / 100);

            if ($this->roundPrice)
            {
                $priceIncludingTax = round ($priceIncludingTax, 2);
            }

            return $priceIncludingTax;
        }

        public function getApi()
        {
            return $this->api;
        }

        public function hasLiveSynchronisation()
        {
            return $this->liveSynchronisation;
        }

        public function useSpecialPrice()
        {
            return $this->useSpecialPrice;
        }

        public function useBackorders()
        {
            return $this->useBackorders;
        }

        public function getConfigBackordersEnabled()
        {
            return $this->configBackordersEnabled;
        }

        public function getConfigMinQty()
        {
            return $this->configMinQty;
        }

        public function getConfigMinSaleQty()
        {
            return $this->configMinSaleQty;
        }

        public function getPriceAttribute()
        {
            return $this->attributePrice;
        }

        public function getPriceAttributeFallback()
        {
            return $this->attributePriceFallback;
        }

        public function getPriceAttributeSurcharge()
        {
            return $this->attributePriceSurcharge;
        }

        public function getStoreView()
        {
            return $this->storeView;
        }

        public function getWebsiteId()
        {
            if (is_null($this->websiteId))
            {
                $storeView = $this->getStoreView();

                if (!$storeView)
                {
                    $storeView = Mage::app()
                        ->getWebsite()
                        ->getDefaultGroup()
                        ->getDefaultStoreId()
                    ;
                }

                $this->websiteId = Mage::app()->getStore($storeView)->getWebsiteId();
            }

            return $this->websiteId;
        }

        public function processProductUpdate($productId, $field, $value)
        {
            $this->getApi()
                ->updateProductOption(
                    $productId,
                    array($field => $value)
                )
            ;
        }

        public function getQuery($type,$defaultReplaces=false)
        {
            if (!isset($this->queries[$type]))
            {
                $querySqlLocation = Mage::getModuleDir('','KoekEnPeer_EffectConnect').DS.'Model'.DS.'Sql'.DS.$type.'.query';
                if (!file_exists($querySqlLocation))
                {
                    throw new Exception('Unknown query: \''.$type.'\' ('.$querySqlLocation.')');
                } else
                {
                    $query = file_get_contents($querySqlLocation);

                    if (preg_match_all('/\[\[(\w+)\]\]/m', $query, $results))
                    {
                        $resource = Mage::getSingleton('core/resource');
                        $searches = array();
                        $replaces = array();
                        foreach ($results[1] as $search)
                        {
                            switch ($search)
                            {
                                case 'store_view':
                                    $replace = $this->getStoreView();
                                    break;
                                case 'website_id':
                                    $replace = $this->getWebsiteId();
                                    break;
                                default:
                                    $replace = $resource->getTableName($search);
                                    break;
                            }
                            $searches[] = '[['.$search.']]';
                            $replaces[] = $replace;
                        }
                        $query = str_replace($searches, $replaces, $query);
                    }
                    $this->queries[$type] = $query;
                }
            }

            $query = $this->queries[$type];

            if ($defaultReplaces)
            {
                foreach ($defaultReplaces as $search => $replace)
                {
                    $query = str_replace('[[@'.$search.']]', $replace, $query);
                }
            }

            return $query;
        }

        public function getData($query, $parameters = null)
        {
            $resource       = Mage::getSingleton('core/resource');
            $readConnection = $resource->getConnection('core_read');

            return $readConnection->fetchAll($query, $parameters);
        }

        public function getBaseUrl($folder = null)
        {
            return Mage::helper('effectconnect')->getBaseUrl($folder);
        }

        public function dispatchStockEvent($productId,$stockId,$stock){
            $eventData = new Varien_Object(
                array(
                    'id'       => $productId,
                    'stock_id' => $stockId,
                    'qty'      => $stock
                )
            );

            Mage::dispatchEvent(
                'effectconnect_export_get_product_stock',
                array(
                    'product' => $eventData
                )
            )
            ;

            return $eventData->getQty();
        }

        public function dispatchDescriptionEvent($productId,$description)
        {
            $eventData = new Varien_Object(
                array(
                    'id'          => $productId,
                    'description' => $description
                )
            );

            Mage::dispatchEvent(
                'effectconnect_export_get_product_description',
                array(
                    'product' => $eventData
                )
            )
            ;

            return $eventData->getDescription();
        }

        public function isExportActive()
        {
            $exportActive = Mage::getStoreConfig(KoekEnPeer_EffectConnect_Model_Export::CONFIG_CRON_EXPORT_ACTIVE_KEY);

            return $exportActive && $exportActive >= time() - 300;
        }

        public function checkExport()
        {
            Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

            if (Mage::getSingleton('core/session')->getEffectConnectExportFinished())
            {
                return true;
            }

            $exportQueue = Mage::getStoreConfig(KoekEnPeer_EffectConnect_Model_Export::CONFIG_CRON_EXPORT_QUEUE_KEY);

            if (!$exportQueue || $this->isExportActive())
            {
                return false;
            }

            $exportModel = Mage::getModel('effectconnect/export_products');
            try
            {
                $exportModel->exportProducts(false, true);
            } catch (Exception $e)
            {
                $exportModel->getLog()->update('Exception: '.$e->getMessage().PHP_EOL.$e->getTraceAsString());
                $exportModel->getLog()->close();
                return false;
            }

            Mage::getSingleton('core/session')->setEffectConnectExportFinished(true);
            Mage::getModel('core/config')->deleteConfig(KoekEnPeer_EffectConnect_Model_Export::CONFIG_CRON_EXPORT_QUEUE_KEY);

            Mage::app()->getCacheInstance()->cleanType('config');

            return true;
        }

        public function hourlyExport()
        {
            $api = $this->getApi();
            $priceData = Mage::getModel('effectconnect/export_price')->getPrice();
            if ($priceData)
            {
                $api->exportPriceData();
            }

            $stockData = Mage::getModel('effectconnect/export_stock')->getStock();
            if ($stockData)
            {
                $api->exportStockData($stockData);
            }
        }
    }