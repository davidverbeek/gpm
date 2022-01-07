<?php

    class KoekEnPeer_EffectConnect_Model_Export_Products extends KoekEnPeer_EffectConnect_Model_Export
    {
        private $mediaBasePath;
        private $categoryIds;
        private $attributes;
        private $attributeEan              = false;
        private $attributeBrand            = array();
        private $attributeTitle            = false;
        private $attributeDescription      = array();
        private $attributeSkip             = array();
        private $attributeDescriptionEmpty = false;
        private $attributeCost             = false;
        private $attributeDelivery         = false;
        private $attributesExtra           = array(
            'meta_description',
            'meta_keyword',
            'meta_title',
            'weight'
        );
        private $productDefaults           = array(
            'name'        => '',
            'description' => '',
            'price'       => 0,
            'visibility'  => 1,
            'status'      => 0
        );
        private $structure;
        private $exportBase;
        private $exportFolder;
        private $csvFiles;
        private $csvLocations;
        private $productsExported        = 0;
        /** @var KoekEnPeer_EffectConnect_Model_Export_Products_Log $log */
        private $log;
        private $cron                    = false;
        private $parentDeliveryDateIds   = array();
        private $onlyValidEan            = false;
        private $avoidParentProducts     = false;
        private $validFrontendStoreViews = array();
        private $syncType                = array();
        private $zipFile;

        public function __construct()
        {
            parent::__construct();

            ini_set('memory_limit', '2048M');
            ini_set('max_execution_time', 3600);
            set_time_limit(3600);

            mb_internal_encoding('UTF-8');

            $this->attributeEan = Mage::getStoreConfig('effectconnect_options/attributes/attribute_ean');

            $this->attributeBrand = Mage::getStoreConfig('effectconnect_options/attributes/attribute_brand');

            if ($this->attributeBrand)
            {
                $this->attributeBrand = explode(',', $this->attributeBrand);
            } else
            {
                $this->attributeBrand = array();
            }

            $this->attributeTitle = Mage::getStoreConfig('effectconnect_options/attributes/attribute_title');

            $this->attributeDescription = Mage::getStoreConfig('effectconnect_options/attributes/attribute_description');
            if ($this->attributeDescription)
            {
                if (!is_array($this->attributeDescription))
                {
                    $this->attributeDescription = array($this->attributeDescription);
                }
            }

            $this->attributeSkip = Mage::getStoreConfig('effectconnect_options/attributes/attribute_skip');
            if ($this->attributeSkip)
            {
                if (!is_array($this->attributeSkip))
                {
                    $this->attributeSkip = array($this->attributeSkip);
                }
            }

            $this->attributeCost           = Mage::getStoreConfig('effectconnect_options/attributes/attribute_cost');
            $this->attributeDelivery       = Mage::getStoreConfig('effectconnect_options/attributes/attribute_delivery');
            $this->onlyValidEan            = Mage::getStoreConfig('effectconnect_options/attributes/only_valid_ean');
            $this->avoidParentProducts     = Mage::getStoreConfig('effectconnect_options/custom/avoid_parent_products') == 1;

            $this->validFrontendStoreViews = Mage::getStoreConfig('effectconnect_options/attributes/valid_frontend_store_views');
            if ($this->validFrontendStoreViews)
            {
                $this->validFrontendStoreViews = explode(',', $this->validFrontendStoreViews);
            } else
            {
                $this->validFrontendStoreViews = array();
            }

            $this->syncType                = Mage::getStoreConfig('effectconnect_options/attributes/sync_type');
            if ($this->syncType)
            {
                $this->syncType = explode('_', $this->syncType);
            } else
            {
                $this->syncType = array();
            }

            $this->exportBase              = 'effectconnect'.DS.'export'.DS;
            $this->exportFolder            = Mage::getBaseDir('var').DS.$this->exportBase;

            $csvFolder                     = $this->exportFolder.'csv';
            if (!file_exists($csvFolder))
            {
                if (!mkdir($csvFolder, 0777, true))
                {
                    $exception = 'Failed to create directory ('.$csvFolder.')';
                    $this->_updateLog($exception);
                    throw new Exception($exception);
                }
            }
        }

        public function exportProducts($force = false, $cron = false)
        {
            $this->cron = $cron;

            $storeId = Mage::app()->getStore()->getStoreId();
            $store   = Mage::getModel('core/store')->load($this->getStoreView());

            $success = false;
            $result  = false;

            try {
                $types = array();
                if ($cron)
                {
                    $types[] = 'cron';
                }
                if ($force)
                {
                    $types[] = 'force';
                }

                $this->_createLog(!$force);
                $this->_updateLog('Start');
                $this->_updateLog('> Store view: '.$this->getStoreView());
                $this->_updateLog('> Store name: '.$store->getName());
                $this->_updateLog('> Type: '.implode(' / ',$types));
                $this->_updateLog('> Extension version: '.Mage::helper('effectconnect')->getExtensionVersion(false));
                $this->_updateLog('> EffectConnect shop key: '.Mage::getStoreConfig('effectconnect_options/credentials/shop_key'));

                Mage::app()
                    ->loadAreaPart(
                        Mage_Core_Model_App_Area::AREA_FRONTEND,
                        Mage_Core_Model_App_Area::PART_EVENTS
                    )
                ;

                $this->_getStructure();
                $this->_updateLog('Structure loaded');

                $this->_setStoreView(0);

                $categoriesStoreView = Mage::getStoreConfig('effectconnect_options/attributes/store_view_categories') == 1 ? $this->getStoreView() : 0;
                $this->_getCategories($categoriesStoreView);
                $this->_updateLog('Categories saved');

                $attributesStoreView = Mage::getStoreConfig('effectconnect_options/attributes/store_view_attributes') == 1 ? $this->getStoreView() : 0;
                $this->_getAttributes($attributesStoreView);
                $this->_updateLog('Attributes saved');

                $storeView = $this->getStoreView();
                if ($storeId != $storeView)
                {
                    $this->_setStoreView($storeView);
                }

                $this->_getProducts();
                $this->_updateLog('Products saved ('.$this->productsExported.')');
                $this->getProductMappingPrices();

                $success = true;
            }catch (Exception $e)
            {
                $this->_updateLog('Exception: '.$e->getMessage().PHP_EOL.$e->getTraceAsString());

                return false;
            }

            if ($storeId != $storeView)
            {
                Mage::app()->setCurrentStore($storeId);
            }

            if ($success)
            {
                $zipLocation = $this->createZipFile();
                if ($zipLocation)
                {
                    $result = $this->exportData($zipLocation);
                }
            }

            $this->_updateLog('Export finished', true);
            $this->log->close();

            return $result;
        }

        protected function _createLog($checkForActiveExport = false)
        {
            $this->log = Mage::getModel('effectconnect/export_products_log');
            $this->log->create($checkForActiveExport, $this->exportFolder);

            return true;
        }

        protected function _updateLog($value, $finished = false)
        {
            if ($this->log)
            {
                return $this->log->update($value);
            }

            if ($this->cron)
            {
                Mage::getModel('core/config')->saveConfig(KoekEnPeer_EffectConnect_Model_Export::CONFIG_CRON_EXPORT_ACTIVE_KEY, $finished ? false : time());
                Mage::getModel('core/config')->saveConfig(KoekEnPeer_EffectConnect_Model_Export::CONFIG_CRON_EXPORT_STATUS_KEY, $value);
            }

            return false;
        }

        protected function _getStructure()
        {
            $this->structure = $this->getApi()
                ->getStructure()
            ;
            if (!$this->structure)
            {
                throw new Exception('Unable to load database structure EffectConnect (API can\'t connect).');
            }

            return true;
        }

        protected function _getCategories($storeView)
        {
            $categories = Mage::getModel('catalog/category')
                ->getCollection()
                ->setStoreId(0)
                ->addAttributeToSelect(
                    array(
                        'entity_id',
                        'name',
                        'parent_id',
                        'is_active',
                        'position',
                        'level'
                    ),
                    'left'
                )
            ;

            $categoriesQuery = $categories->getSelect();
            $categoryIds     = array();

            $rootCategoryId = $storeView ? Mage::app()->getStore($storeView)->getRootCategoryId() : false;

            foreach ($this->getData($categoriesQuery) as $category)
            {
                if (empty($category['name']))
                {
                    continue;
                }
                $path = explode('/', $category['path']);
                if ($rootCategoryId && !in_array($rootCategoryId, $path))
                {
                    continue;
                }
                $categoryId = $category['entity_id'];
                if (empty($category['parent_id']))
                {
                    if (count($path) >= 2)
                    {
                        end($path);
                        $category['parent_id'] = prev($path);
                    }
                }
                if ($categoryId == $rootCategoryId)
                {
                    $category['parent_id'] = 0;
                }
                $this->saveData(
                    'categories',
                    array(
                        'id'        => $categoryId,
                        'parent_id' => $category['parent_id'],
                        'visible'   => $category['level'] == '0' ? 1 : $category['is_active'],
                        'sort'      => $category['position']
                    )
                )
                ;
                $this->saveData(
                    'categories_text',
                    array(
                        'id'    => $category['entity_id'],
                        'title' => $category['name']
                    )
                )
                ;
                $categoryIds[] = $categoryId;
            }

            $this->categoryIds = $categoryIds;

            return true;
        }

        protected function _setStoreView($id){
            Mage::app()
                ->setCurrentStore($id)
            ;
        }

        protected function _getAttributes($storeView)
        {
            $attributes = Mage::getSingleton('eav/config')
                ->getEntityType(Mage_Catalog_Model_Product::ENTITY)
                ->getAttributeCollection()
                ->addSetInfo()
            ;
            if (count($attributes))
            {
                foreach ($attributes as $attribute)
                {
                    if ($attribute->getBackendType() == 'static')
                    {
                        continue;
                    }
                    $attributeCode = $attribute->getAttributeCode();
                    if (substr($attributeCode, 0, 6) == 'sherpa')
                    {
                        continue;
                    }
                    $attributeId   = $attribute->getAttributeId();
                    $attributeInfo = array(
                        'id'           => $attributeId,
                        'type'         => $attribute->getFrontendInput(),
                        'data_type'    => $attribute->getBackendType(),
                        'label'        => $attribute->getFrontendLabel(),
                        'user_defined' => $attribute->getIsUserDefined()
                    );
                    if (strstr($attributeInfo['type'], 'select'))
                    {
                        $attributeInfo['data_type'] = 'varchar';
                    }
                    $this->attributes[$attributeCode] = $attributeInfo;

                    $attributesCustom = array();
                    if ($attributeCode == $this->attributeEan)
                    {
                        $attributesCustom[] = 'ean';
                    }
                    if (in_array($attributeCode, $this->attributeBrand))
                    {
                        $attributesCustom[] = 'brand';
                    }
                    if ($attributeCode == $this->getPriceAttribute())
                    {
                        $attributesCustom[] = 'price';
                    }
                    if ($attributeCode == $this->attributeCost)
                    {
                        $attributesCustom[] = 'cost';
                    }
                    if ($attributeCode == $this->attributeDelivery)
                    {
                        $attributesCustom[] = 'delivery';
                    }

                    if (!empty($attributeCustom) || $attributeInfo['user_defined'] == 1 || in_array($attributeCode, $this->attributesExtra))
                    {
                        if (empty($attributesCustom)){
                            $this->saveData(
                                'attributes_categories',
                                array(
                                    'id'       => $attributeId,
                                    'type'     => $attributeInfo['data_type'],
                                    'code'     => $attributeCode,
                                    'frontend' => $attribute->getIsVisibleOnFront()
                                )
                            )
                            ;
                            $this->saveData(
                                'attributes_categories_text',
                                array(
                                    'id'    => $attributeId,
                                    'title' => $attributeInfo['label']
                                )
                            )
                            ;
                        }
                    }
                    if ($attribute->usesSource())
                    {
                        $attributeValues = Mage::getResourceModel('eav/entity_attribute_option_collection')
                            ->setAttributeFilter($attributeId)
                            ->setPositionOrder('asc')
                            ->setStoreFilter($storeView, $storeView > 0)
                            ->load()
                        ;
                        if ($attributeValues)
                        {
                            $options = array();
                            foreach ($attributeValues as $attributeValue)
                            {
                                $attributeValueId           = $attributeValue['option_id'];
                                $attributeValueLabel        = $attributeValue['value'];
                                $options[$attributeValueId] = $attributeValueLabel;
                                if (empty($attributesCustom))
                                {
                                    $this->saveData(
                                        'attributes',
                                        array(
                                            'id'          => $attributeValueId,
                                            'category_id' => $attributeId,
                                            'position'    => $attributeValue['sort_order']
                                        )
                                    )
                                    ;
                                    $this->saveData(
                                        'attributes_data_varchar',
                                        array(
                                            'attribute_id' => $attributeValueId,
                                            'value'        => $attributeValueLabel
                                        )
                                    )
                                    ;
                                } else
                                {
                                    foreach ($attributesCustom as $attributeCustom)
                                    {
                                        switch ($attributeCustom)
                                        {
                                            case 'brand':
                                                $this->saveData(
                                                    'brands',
                                                    array(
                                                        'id'    => $attributeValueId,
                                                        'title' => $attributeValueLabel
                                                    )
                                                )
                                                ;
                                                break;
                                            case 'delivery':
                                                $this->saveData(
                                                    'delivery_dates',
                                                    array(
                                                        'id'   => $attributeValueId,
                                                        'name' => $attributeValueLabel
                                                    )
                                                )
                                                ;
                                                break;
                                        }
                                    }
                                }
                            }
                            $this->attributes[$attributeCode]['options'] = $options;
                        }
                    }
                }
            }
            if (empty($this->attributeDescription))
            {
                $this->attributeDescriptionEmpty = true;
                $this->attributeDescription[]    = 'description';
            }

            return true;
        }

        protected function _getMediaBasePath($internal = false)
        {
            if (!$this->mediaBasePath)
            {
                $this->mediaBasePath = array(
                    'external' => $this->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'catalog/product',
                    'internal' => Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA).DIRECTORY_SEPARATOR.'catalog'.DIRECTORY_SEPARATOR.'product'
                );
            }

            return $this->mediaBasePath[$internal?'internal':'external'];
        }

        protected function _getProducts()
        {
            $appEmulation           = Mage::getSingleton('core/app_emulation');
            $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($this->getStoreView());

            $productCollection = Mage::getModel('catalog/product')
                ->getCollection()
                ->addUrlRewrite()
                ->addStoreFilter()
                ->addAttributeToSelect('sku')
                ->addAttributeToSelect('tax_class_id')
                ->joinTable(
                    array('price_index' => 'catalog/product_index_price'),
                    'entity_id=entity_id',
                    array('price','final_price'),
                    'price_index.website_id='.$this->getWebsiteId(),
                    'left'
                )
            ;

            foreach ($this->syncType as $syncType)
            {
                switch ($syncType)
                {
                    case 'enabled':
                        $productCollection->addAttributeToFilter(
                            'status',
                            array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                        );
                        break;
                    case 'visible':
                        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($productCollection);
                        break;
                }
            }

            if ($this->avoidParentProducts)
            {
                $productCollection->addAttributeToFilter(
                    'type_id',
                    array('eq' => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
                );
            }

            $productCollectionSelect = $productCollection
                ->getSelect()
                ->group('e.entity_id')
            ;

            if ($this->onlyValidEan)
            {
                $this->addFilterOnlyValidEans($productCollectionSelect);
            }

            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

            $this->_updateLog('Start product export ('.$productCollection->getSize().')');
            Mage::getSingleton('core/resource_iterator')
                ->walk(
                    $productCollectionSelect,
                    array(
                        array(
                            $this,
                            'processProduct'
                        )
                    )
                )
            ;

            return true;
        }

        public function processProduct($arguments)
        {
            $this->productsExported++;

            $product     = $arguments['row'];
            $productId   = $product['entity_id'];
            $productType = $product['type_id'];

            /** @var Mage_Catalog_Model_Product $_product */
            $_product  = Mage::getModel('catalog/product');
            $_product->setData($product);

            $_stock = Mage::getModel('cataloginventory/stock_item')
                ->loadByProduct($productId)
            ;

            $taxClassId = $_product->getTaxClassId();

            $product += array(
                'ean'              => '',
                'info'             => array(),
                'attributes'       => array(),
                'brand_id'         => 0,
                'cost'             => 0,
                'delivery_date_id' => null,
                'categories'       => $_product->getCategoryIds(),
                'images'           => array(),
                'stock'            => $_stock->toArray(),
                'description'      => array(),
                'parent'           => false
            );

            if (!empty($product['categories']))
            {
                $categoriesFiltered = array();
                foreach ($product['categories'] as $categoryId)
                {
                    if (in_array($categoryId, $this->categoryIds))
                    {
                        $categoriesFiltered[] = $categoryId;
                    }
                }

                $product['categories'] = $categoriesFiltered;
            }

            $productPrice = false;
            if ($this->attributeEan)
            {
                if (isset($product[$this->attributeEan]))
                {
                    $product['ean'] = trim($product[$this->attributeEan]);
                }
            }

            if ($this->attributeCost)
            {
                if (isset($product[$this->attributeCost]))
                {
                    $product['cost'] = $product[$this->attributeCost];
                }
            }

            if ($this->attributeDelivery)
            {
                if (isset($product[$this->attributeDelivery]))
                {
                    $product['delivery_date_id'] = $product[$this->attributeDelivery];
                }
            }

            if (!$this->avoidParentProducts)
            {
                $parentIds = Mage::getModel('catalog/product_type_configurable')
                    ->getParentIdsByChild($productId)
                ;
                if ($parentIds)
                {
                    $product['parent'] = current($parentIds);
                }
            }

            $product                = array_merge(
                $product,
                $this->getProductAttributes($productId, $product['parent'], $productPrice)
            );

            $_product->addData($product['attributes']);
            $_product->addData($product['info']);

            $productParentId        = $product['parent'] ? $product['parent'] : $productId;
            $product['images']      = $this->getProductMediaGallery($productId, isset($product['info']['image'])?$product['info']['image']:false);
            $product['description'] = $this->getProductDescription($product);

            if (!$taxClassId && isset($product['info']['tax_class_id']))
            {
                $taxClassId = $product['info']['tax_class_id'];
            }

            if ($product['stock'])
            {
                if (
                    $this->useBackorders() &&
                    (
                        ($product['stock']['use_config_backorders'] && $this->getConfigBackordersEnabled()) ||
                        (!$product['stock']['use_config_backorders'] && $product['stock']['backorders'] > 0)
                    )
                )
                {
                    $stock = KoekEnPeer_EffectConnect_Model_Export_Stock::BACKORDER_DEFAULT_STOCK;
                } else
                {
                    $stock  = $product['stock']['is_in_stock'] == 1 ? round($product['stock']['qty']) : 0;
                    $stock -= intval($product['stock']['use_config_min_qty'] == 1 ? $this->getConfigMinQty() : $product['stock']['min_qty']);
                }

                $stock = $this->dispatchStockEvent(
                    $productId,
                    $product['stock']['item_id'],
                    $stock
                );
            } else
            {
                $stock = 0;
            }

            foreach ($this->productDefaults as $defaultKey => $defaultValue)
            {
                if (!isset($product['info'][$defaultKey]))
                {
                    $product['info'][$defaultKey] = $defaultValue;
                }
            }

            $originalPrice = null;
            if (!$this->getPriceAttribute() || (!$productPrice && $this->getPriceAttributeFallback()))
            {
                list($originalPrice, $productPrice) = $this->getProductPrice($_product);
            }

            if ($productType == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE){
                if (!$productPrice)
                {
                    $productPrice = Mage::getModel('bundle/product_price')
                        ->getTotalPrices($_product, 'max', true)
                    ;
                }

                $stock = Mage::getModel('effectconnect/export_stock')->getBundleStock($productId);
            }

            if ($product['surcharge'])
            {
                if ($productPrice)
                {
                    $productPrice += $product['surcharge'];
                }

                if ($originalPrice)
                {
                    $originalPrice += $product['surcharge'];
                }
            }

            $data = array();

            if ($productType != Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)
            {
                if (empty($product['delivery_date_id']) && isset($this->parentDeliveryDateIds[$productParentId]))
                {
                    $product['delivery_date_id'] = $this->parentDeliveryDateIds[$productParentId];
                }

                $data['products_options'] = array(
                    'id'                   => $productId,
                    'parent_id'            => $productParentId,
                    'sku'                  => $product['sku'],
                    'ean'                  => $product['ean'],
                    'price'                => $this->getPriceIncludingTax($productPrice, $taxClassId),
                    'price_original'       => $this->getPriceIncludingTax($originalPrice, $taxClassId),
                    'cost'                 => $product['cost'],
                    'stock'                => $stock,
                    'minimal_order_amount' => $product['stock']['use_config_min_sale_qty']==1 ? $this->getConfigMinSaleQty() : $product['stock']['min_sale_qty'],
                    'weight'               => isset($product['attributes']['weight']) ? $product['attributes']['weight'] : 0,
                    'delivery_date_id'     => $product['delivery_date_id']
                );
            } elseif ($product['delivery_date_id'] > 0)
            {
                $this->parentDeliveryDateIds[$productId] = $product['delivery_date_id'];
            }

            if (!$product['parent'])
            {
                $data += array(
                    'products'      => array(
                        'id'       => $productId,
                        'sku'      => $product['sku'],
                        'price'    => $this->getPriceIncludingTax($productPrice, $taxClassId),
                        'cost'     => $product['cost'],
                        'brand_id' => $product['brand_id'],
                        'visible'  => $product['info']['visibility'] >= 1 && $product['info']['status'] == 1 ? 1 : 0
                    ),
                    'products_text' => array(
                        'id'          => $productId,
                        'title'       => html_entity_decode($product['info']['name'], ENT_QUOTES, 'UTF-8'),
                        'description' => $product['description'],
                        'url'         => $this->_getProductUrl($_product)
                    )
                );
                if (!empty($product['categories']))
                {
                    $data['products_categories'] = array();
                    foreach ($product['categories'] as $categoryId)
                    {
                        $data['products_categories'][] = array(
                            'parent_id'   => $productId,
                            'category_id' => $categoryId
                        );
                    }
                }
            } else
            {
                $data += array(
                    'products_options_text' => array(
                        'id'    => $productId,
                        'title' => $product['info']['name']
                    )
                );
            }

            if (!empty($product['images']))
            {
                $data['product_images'] = array();

                $imageSort = 1;
                foreach ($product['images'] as $image)
                {
                    $data['products_images'][] = array(
                        'parent_id' => $productParentId,
                        'option_id' => $productParentId != $productId ? $productId : 0,
                        'url'       => $image['url'],
                        'size'      => $image['size'],
                        'sort'      => $imageSort
                    );
                    $imageSort++;
                }
            }

            if (!empty($product['attributes']))
            {
                $table = 'products'.($productParentId != $productId ? '_options' : '').'_attributes';
                foreach ($product['attributes'] as $attributeCode => $attributeValues)
                {
                    if (!is_array($attributeValues))
                    {
                        $attributeValues = array($attributeValues);
                    }
                    $attributeInfo = $this->attributes[$attributeCode];
                    foreach ($attributeValues as $attributeValue)
                    {
                        if (strstr($attributeInfo['type'], 'select'))
                        {
                            if (isset($attributeInfo['options'][$attributeValue]))
                            {
                                $this->saveData(
                                    $table,
                                    array(
                                        'parent_id'    => $productId,
                                        'attribute_id' => $attributeValue
                                    )
                                )
                                ;
                            }
                            continue;
                        }
                        if ($attributeValue !== false)
                        {
                            $data['_'.$table][] = array(
                                'parent_id'             => $productId,
                                'attribute_category_id' => $attributeInfo['id'],
                                'attribute_type'        => $attributeInfo['data_type'],
                                'attribute_code'        => $attributeCode,
                                'attribute_value'       => $attributeValue
                            );
                        }
                    }
                }
            }

            foreach ($data as $table => $tableData)
            {
                if (!empty($tableData))
                {
                    if (array_keys($tableData) !== range(0, count($tableData) - 1))
                    {
                        $tableData = array($tableData);
                    }
                    foreach ($tableData as $tableRecord)
                    {
                        $this->saveData($table, $tableRecord);
                    }
                }
            }

            if ($this->productsExported % 100 == 0)
            {
                $this->_updateLog('Exporting products ('.$this->productsExported.')');
            }
        }

        protected function getProductAttributes($productId, $parentId, &$productPrice)
        {
            $attributesFilled = array();
            $product          = array(
                'surcharge' => 0
            );
            if ($attributes = $this->getData($this->getQuery('product_attributes'), array($productId)))
            {
                foreach ($attributes as $attribute)
                {

                    if ($attribute['frontend_input'] == 'multiselect')
                    {
                        $attribute['value'] = explode(',', $attribute['value']);
                    }

                    $attributeCode = $attribute['attribute_code'];
                    if (in_array($attributeCode, $this->attributeSkip))
                    {
                        continue;
                    }

                    if ($this->getStoreView())
                    {
                        if ($attribute['store_id'] == 0)
                        {
                            if (in_array($attributeCode, $attributesFilled))
                            {
                                continue;
                            }
                        } else
                        {
                            if (!in_array($attributeCode, $attributesFilled))
                            {
                                $attributesFilled[] = $attributeCode;
                            }
                        }
                    }
                    $attributeFilled = false;
                    if (empty($product['ean']) && $attributeCode == $this->attributeEan)
                    {
                        $product['ean']  = trim($attribute['value']);
                        $attributeFilled = true;
                    }

                    if (empty($product['brand_id']) && in_array($attributeCode, $this->attributeBrand))
                    {
                        if (is_array($attribute['value']))
                        {
                            $attribute['value'] = current($attribute['value']);
                        }

                        if (strstr($attribute['frontend_input'], 'select'))
                        {
                            $product['brand_id'] = $attribute['value'];
                        } else
                        {
                            $this->saveData(
                                '_brands',
                                array(
                                    'product_id' => $parentId ? $parentId : $productId,
                                    'title'      => $attribute['value']
                                )
                            );
                        }
                    }

                    if (!is_array($attribute['value']))
                    {
                        if (!$productPrice && $attributeCode == $this->getPriceAttribute())
                        {
                            $productPrice    = $attribute['value'];
                            $attributeFilled = true;
                        }

                        if (empty($product['cost']) && $attributeCode == $this->attributeCost)
                        {
                            $product['cost'] = $attribute['value'];
                            $attributeFilled = true;
                        }

                        if (empty($product['delivery_date_id']) && $attributeCode == $this->attributeDelivery)
                        {
                            if (strstr($attribute['frontend_input'], 'select'))
                            {
                                $product['delivery_date_id'] = $attribute['value'];
                            } else
                            {
                                $this->saveData(
                                    '_delivery_dates',
                                    array(
                                        'option_id' => $productId,
                                        'name'      => $attribute['value']
                                    )
                                );
                            }
                            $attributeFilled             = true;
                        }

                        if ($attributeCode == $this->attributeTitle)
                        {
                            $product['info']['name'] = $attribute['value'];
                            $attributeFilled         = true;
                        }

                        if (in_array($attributeCode, $this->attributeDescription))
                        {
                            $product['description'][] = $attribute['value'];
                            $attributeFilled          = true;
                        }

                        if (in_array($attributeCode, $this->getPriceAttributeSurcharge()))
                        {
                            $product['surcharge'] += floatval($attribute['value']);
                        }
                    }

                    if (!$attributeFilled)
                    {
                        $attributeType = $attribute['is_user_defined'] == 1 || in_array($attributeCode, $this->attributesExtra) ? 'attributes' : 'info';

                        if (isset($product[$attributeType][$attributeCode]))
                        {
                            continue;
                        }

                        $product[$attributeType][$attributeCode] = $attribute['value'];
                    }
                }
            }

            return $product;
        }

        protected function getProductMediaGallery($productId, $defaultImage = false)
        {
            $locations = array();

            if($defaultImage && $defaultImage != 'no_selection')
            {
                $locations[] = $defaultImage;
            }

            $gallery = $this->getData($this->getQuery('product_media'), array($productId));
            if ($gallery)
            {
                foreach ($gallery as $galleryItem)
                {
                    $image = $galleryItem['value'];
                    if (!in_array($image, $locations))
                    {
                        $locations[] = $image;
                    }
                }
            }

            $images = array();
            if (!empty($locations))
            {
                foreach ($locations as $image)
                {
                    $imageSize = $this->getProductMediaSize($image);

                    if (!$imageSize)
                    {
                        continue;
                    }

                    $imageUrl  = $this->_getMediaBasePath().$image;

                    $images[] = array(
                        'url'  => $imageUrl,
                        'size' => $imageSize
                    );
                }
            }

            return $images;
        }

        protected function getProductMediaSize($image)
        {
            $internalImageLocation = $this->_getMediaBasePath(true).str_replace('/', DIRECTORY_SEPARATOR, $image);
            if (!file_exists($internalImageLocation))
            {
                return false;
            }

            return filesize($internalImageLocation);
        }

        protected function getProductDescription($product)
        {
            if (in_array('_combined', $this->attributeDescription))
            {
                $product['description'] = array();
                foreach (array(
                    'short_description',
                    'description'
                ) as $descriptionAttribute)
                {
                    if (isset($product['info'][$descriptionAttribute]))
                    {
                        if (!empty($product['info'][$descriptionAttribute]))
                        {
                            $product['description'][] = $product['info'][$descriptionAttribute];
                        }
                    }
                }
            } else
            {
                if ($this->attributeDescriptionEmpty && empty($product['description']))
                {
                    if (isset($product['info']['description']))
                    {
                        if (!empty($product['info']['description']))
                        {
                            $product['description'][] = $product['info']['description'];
                        }
                    }
                    if (isset($product['info']['short_description']))
                    {
                        if (!empty($product['info']['short_description']))
                        {
                            if (isset($product['info']['description']))
                            {
                                if ($product['info']['description'] != $product['info']['short_description'])
                                {
                                    array_unshift($product['description'], $product['info']['short_description']);
                                }
                            } else
                            {
                                $product['description'][] = $product['info']['short_description'];
                            }
                        }
                    }
                }
            }

            $description = implode('<p>&nbsp;</p>', $product['description']);

            return $this->dispatchDescriptionEvent($product['entity_id'],$description);
        }

        /**
         * @param Mage_Catalog_Model_Product $_product
         *
         * @return array
         */
        protected function getProductPrice($_product)
        {
            $originalPrice = null;
            $productPrice  = floatval($_product->getPrice());

            if ($this->useSpecialPrice())
            {
                $finalPrice = floatval($_product->getFinalPrice());
                if ($finalPrice && $finalPrice < $productPrice)
                {
                    $originalPrice = $productPrice;
                    $productPrice  = $finalPrice;
                }
            }

            return array($originalPrice, $productPrice);
        }

        protected function getProductMappingPrices()
        {
            $mappedPriceStores = Mage::getModel('effectconnect/mapping')
                ->getCollection()
            ;
            if (count($mappedPriceStores) > 0)
            {
                $mappedPrices          = array();
                $mappedPricesStatement = $this->getQuery('product_mapping_prices');
                foreach ($mappedPriceStores as $mappedPriceStore)
                {
                    if (!$mappedPriceStore->price_attribute)
                    {
                        continue;
                    }
                    $channelId        = (int)$mappedPriceStore->channel_id;
                    $mappedPricesData = $this->getData(
                        $mappedPricesStatement,
                        array($mappedPriceStore->price_attribute)
                    )
                    ;
                    foreach ($mappedPricesData as $price)
                    {
                        $productId = $price['entity_id'];
                        if (!isset($mappedPrices[$channelId][$productId]))
                        {
                            $mappedPrices[$channelId][$productId] = (float)$price['value'];
                        }
                    }
                    $this->_updateLog(
                        'Mapped prices (channel ID: '.$channelId.') loaded ('.count($mappedPrices[$channelId]).')'
                    )
                    ;
                }
                if (!empty($mappedPrices))
                {
                    foreach ($mappedPrices as $channelId => $channelProducts)
                    {
                        foreach ($channelProducts as $productId => $channelProductPrice)
                        {
                            $this->saveData(
                                '_channels_settings',
                                array(
                                    'channel_id'  => $channelId,
                                    'type'        => 'products_options',
                                    'type_id'     => $productId,
                                    'price_type'  => 'fixed',
                                    'price_fixed' => $channelProductPrice
                                )
                            )
                            ;
                        }
                    }
                    $this->_updateLog('Mapped prices saved');
                }
            }

            return true;
        }

        protected function saveData($table, $record, $header = false)
        {
            if (!$header)
            {
                $tableColumns = isset($this->structure[$table]) ? $this->structure[$table] : false;
                if (!isset($this->csvFiles[$table]))
                {
                    if ($tableColumns || substr($table, 0, 1) == '_')
                    {
                        $csvFolder              = $this->exportFolder.'csv';
                        $csvLocation            = $csvFolder.DS.$table.'.csv';
                        $this->csvLocations[]   = $csvLocation;
                        $this->csvFiles[$table] = fopen($csvLocation, 'w+');
                        fwrite($this->csvFiles[$table], chr(239).chr(187).chr(191));

                        $this->_updateLog('CSV file for '.$table.' created ('.(file_exists($csvLocation)?'success':'error').')');

                        $this->saveData($table, !$tableColumns ? array_keys($record) : $tableColumns, true);
                    } else
                    {
                        return false;
                    }
                }
                if ($tableColumns)
                {
                    $newRecord = array();
                    foreach ($tableColumns as $tableColumn)
                    {
                        if (!isset($record[$tableColumn]))
                        {
                            switch ($tableColumn)
                            {
                                case 'id':
                                    $tableValue = '\N';
                                    break;
                                case 'language':
                                    $tableValue = 'nl';
                                    break;
                                case 'date_created':
                                    $tableValue = time();
                                    break;
                                case 'date_updated':
                                    $tableValue = 0;
                                    break;
                                default:
                                    $tableValue = '';
                                    break;
                            }
                        } else
                        {
                            $tableValue = $record[$tableColumn];
                        }
                        $newRecord[$tableColumn] = $tableValue;
                    }
                    $record = $newRecord;
                }
            } else
            {
                if (!isset($this->csvFiles[$table]))
                {
                    return false;
                }
            }

            return fputcsv($this->csvFiles[$table], $record, ',');
        }

        protected function createZipFile()
        {
            if ($this->csvFiles)
            {
                foreach ($this->csvFiles as $csvFile)
                {
                    fclose($csvFile);
                }

                $zipFolder = Mage::getBaseDir('media').DS.'effectconnect'.DS;
                if (!file_exists($zipFolder))
                {
                    mkdir($zipFolder, 0777, true);
                }

                $this->zipFile = 'data_'.date('YmdHis').'_'.strtolower(Mage::helper('core')->getRandomString(8)).'.zip';
                $zipLocation   = $zipFolder.$this->zipFile;
                if (!file_exists($zipLocation))
                {
                    $zipHandle = fopen($zipLocation, 'w');
                    fclose($zipHandle);
                }

                $this->_updateLog('ZIP file created ('.(file_exists($zipLocation)?'success':'error').')');

                $zip = new ZipArchive();
                $zip->open($zipLocation, ZipArchive::OVERWRITE);
                foreach ($this->csvLocations as $csvLocation)
                {
                    $csvPathInfo = pathinfo($csvLocation);
                    $zip->addFile($csvLocation, $csvPathInfo['basename']);
                }
                $zip->close();

                $this->_updateLog('ZIP created ('.filesize($zipLocation).' bytes)');

                foreach ($this->csvLocations as $csvLocation)
                {
                    unlink($csvLocation);
                }

                $this->_updateLog('CSV files removed');

                return $zipLocation;
            }

            return false;
        }

        protected function exportData($zipLocation)
        {
            $zipFile = $this->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'effectconnect/'.$this->zipFile;

            $this->_updateLog('ZIP location: '.$zipFile);

            try
            {
                $apiResult = $this->getApi()
                    ->submitImport(
                        $zipFile,
                        'magento'
                    );
                ;
            } catch (Exception $e)
            {
                $this->_updateLog('API error: '.$e->getMessage());
            }

            unlink($zipLocation);

            return $apiResult;
        }

        protected function addFilterOnlyValidEans($productCollectionSelect)
        {
            $attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode(Mage_Catalog_Model_Product::ENTITY, $this->attributeEan);
            if ($attributeModel)
            {
                $eanData  = array();
                $resource = Mage::getSingleton('core/resource');
                switch ($attributeModel->getBackendType())
                {
                    case Mage_Eav_Model_Entity_Attribute_Abstract::TYPE_STATIC:
                        switch ($this->attributeEan)
                        {
                            case 'sku':
                                foreach ($this->getData($this->getQuery('ean_by_sku', array('table' => $resource->getTableName('catalog_product_entity')))) as $eanRecord)
                                {
                                    $eanData[$eanRecord['entity_id']] = $eanRecord['sku'];
                                }
                                break;
                            default:
                                return;
                                break;
                        }
                        break;
                    default:
                        foreach (
                            $this->getData(
                                $this->getQuery(
                                    'ean_products',
                                    array(
                                        'table' => $resource->getTableName('catalog_product_entity_'.$attributeModel->getBackendType())
                                    )
                                ),
                                $attributeModel->getAttributeId()
                            ) as
                            $eanRecord
                        )
                        {
                            $eanData[$eanRecord['entity_id']] = $eanRecord['value'];
                        }
                        break;
                }

                $validProductIds = array();
                $helper          = Mage::helper('effectconnect');
                foreach ($eanData as $productId => $ean)
                {
                    if (!$helper->validateEan($ean))
                    {
                         continue;
                    }

                    $validProductIds[] = $eanRecord['entity_id'];
                }

                if (empty($validProductIds))
                {
                    return;
                }

                $validProductIds = array_unique($validProductIds);
                $validProductIds = implode(',', $validProductIds);

                $productCollectionSelect->where('
                    `e`.`entity_id` IN ('.$validProductIds.') OR
                    `e`.`entity_id` IN (
                        SELECT
                            `parent_id`
                        FROM
                            `'.$resource->getTableName('catalog_product_relation').'`
                        WHERE
                            `child_id` IN ('.$validProductIds.')
                    )
                ');
            }
        }

        protected function _getProductUrl($_product)
        {
            if (!empty($this->validFrontendStoreViews))
            {
                if (!in_array($_product->getStoreId, $this->validFrontendStoreViews))
                {
                    $url = null;
                    foreach ($_product->getStoreIds() as $storeId)
                    {
                        if (!in_array($storeId, $this->validFrontendStoreViews))
                        {
                            continue;
                        }

                        $url = $_product->setStoreId($storeId)->getProductUrl();

                        $_product->setStoreId($this->getStoreView());
                        break;
                    }

                    return $url;
                }
            }

            return $_product->getProductUrl();
        }

        public function getLog()
        {
            return $this->log;
        }
    }