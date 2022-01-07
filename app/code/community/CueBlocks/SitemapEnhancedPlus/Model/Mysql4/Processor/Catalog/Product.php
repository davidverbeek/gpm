<?php

/**
 * Description of SitemapEnhancedPlus
 * @package   CueBlocks_SitemapEnhancedPlus
 * @company    CueBlocks - http://www.cueblocks.com/

 */
class CueBlocks_SitemapEnhancedPlus_Model_Mysql4_Processor_Catalog_Product extends CueBlocks_SitemapEnhancedPlus_Model_Mysql4_Processor_Catalog_AbstractProduct
{
    protected $_store;

    protected function _setSql($forCount = false)
    {
        $storeId = $this->_config->getStoreId();
        $visibility = $this->_config->getVisibility();
        $excludeOutOfStock = $this->_config->getFilterOutOfStock();
        $excludeInStock = $this->_config->getFilterInStock();
        $useRootCatalogMode = $this->_config->getUseRootCatalogMode();
        // Special Config ( null on default product processor )
        $filterByCatId = $this->_config->getFilterByCatId();
        $useCatPathInUrl = $this->_config->getUseCatPathInUrl();
        $groupByAttributeCode = $this->_config->getGroupByAttributeCode();

        $store = Mage::app()->getStore($storeId);
        /* @var $store Mage_Core_Model_Store */
        if (!$store) {
            return false;
        }
        // set env. values
        $this->_store = $store;
        $this->_setAlias();

        // base query
        $this->_select = $this->_getBaseSelect();

        if (!$forCount) {
            // url join (works only < 1.13.1)
            $this->_addUrlJoin($filterByCatId, $useCatPathInUrl);
        }

        // filter by stock
        if ($excludeOutOfStock || $excludeInStock) {
            $this->_addStockFilterJoin($excludeOutOfStock, $excludeInStock);
        }
        //filter products catalog by category root
        if ($useRootCatalogMode) {
            $this->_addRootFilterJoin();
        }

        //filter Products by Category ( used in divide by Sub/Top Category )
        if ($filterByCatId) {
            $this->_addCategoryFilterJoin($filterByCatId);
        }
        if ($groupByAttributeCode) {
            $this->_addAttributeFilterJoin($groupByAttributeCode);
        }

        $this->_addFilter($storeId, 'visibility', $visibility, 'in');
        $this->_addFilter($storeId, 'status', Mage::getSingleton('catalog/product_status')->getVisibleStatusIds(), 'in');

//        Mage::log((string)$this->_select, null, 'SQL.sql');
        return $this->_select;
    }

    /**
     * TODO: Fix forEE 1.13.1
     * `core/url_rewrite` is not present
     */
    protected function _addUrlJoin($filterByCatId, $useCatPathInUrl)
    {
        $storeId = (int)$this->_store->getId();

        if (Mage::helper('sitemapEnhancedPlus')->isMageAboveEE12()) {
            // For EE > 1.12
            $this->_joinTableToSelect($this->_select, $storeId);
        } else {
            // For CE & EE < 1.12
            $conditions = array(
                $this->_alias . '.entity_id=ur.product_id',
                'ur.category_id IS NULL',
                $this->_getWriteAdapter()->quoteInto('ur.store_id=?', $storeId),
                $this->_getWriteAdapter()->quoteInto('ur.is_system=?', 1),
            );

            if ($filterByCatId) {
                // use category path only is enabled
                $seo_cat_path = Mage::getStoreConfig('catalog/seo/product_use_categories', $storeId);

                if ($seo_cat_path && $useCatPathInUrl) {
                    $conditions = array(
                        $this->_alias . '.entity_id=ur.product_id',
                        $this->_getWriteAdapter()->quoteInto('ur.category_id=?', $filterByCatId),
                        $this->_getWriteAdapter()->quoteInto('ur.store_id=?', $storeId),
                        $this->_getWriteAdapter()->quoteInto('ur.is_system=?', 1),
                    );
                }
            }

            $this->_select = $this->_select->joinLeft(
                array('ur' => $this->getTable('core/url_rewrite')),
                join(' AND ', $conditions),
                array('url' => 'request_path')
            );
        }
        return $this;
    }

    /**
     * Necessary for EE > 1.12
     *
     * @param Varien_Db_Select $select
     * @param $storeId
     * @return $this
     */
    protected function _joinTableToSelect(Varien_Db_Select $select, $storeId)
    {
        $requestPath = $this->_getWriteAdapter()->getIfNullSql('url_rewrite.request_path', 'default_ur.request_path');
        $urlSuffix = Mage::helper('catalog/product')->getProductUrlSuffix($storeId);
        $urlSuffix = ($urlSuffix) ? '.' . $urlSuffix : '';

        $select->joinLeft(
            array('url_rewrite_product' => $this->getTable('enterprise_catalog/product')),
            'url_rewrite_product.product_id = main_table.entity_id ' .
            'AND url_rewrite_product.store_id = ' . (int)$storeId,
            array('url_rewrite_product.product_id' => 'url_rewrite_product.product_id'))
            ->joinLeft(array('url_rewrite' => $this->getTable('enterprise_urlrewrite/url_rewrite')),
                'url_rewrite_product.url_rewrite_id = url_rewrite.url_rewrite_id AND url_rewrite.is_system = 1',
                array(''))
            ->joinLeft(array('default_urp' => $this->getTable('enterprise_catalog/product')),
                'default_urp.product_id = main_table.entity_id AND default_urp.store_id = 0',
                array(''))
            ->joinLeft(array('default_ur' => $this->getTable('enterprise_urlrewrite/url_rewrite')),
                'default_ur.url_rewrite_id = default_urp.url_rewrite_id',
                array('url' => 'concat( ' . $requestPath . ',"' . $urlSuffix . '")'));
        return $this;
    }

    protected function _addStockFilterJoin($excludeOutOfStock, $excludeInStock)
    {
        $storeId = (int)$this->_store->getId();
        $conditions = null;

        // filter in/out of stock
        $manageStockConfig = Mage::getStoreConfig('cataloginventory/item_options/manage_stock', $storeId);

        if ($excludeOutOfStock) {
            $sql = $this->_alias . '.entity_id=stk.product_id AND stk.stock_id = 1 ';

            if ($manageStockConfig) {
                $sql .= ' AND IF (stk.use_config_manage_stock = 1, stk.is_in_stock = 1';
            } else {
                $sql .= ' AND IF (stk.use_config_manage_stock = 1, TRUE';
            }
            $sql .= ' ,(stk.manage_stock = 0 OR (stk.manage_stock = 1 AND stk.is_in_stock = 1)) )';
            $conditions = $sql;

        } elseif ($excludeInStock) {
            $sql = $this->_alias . '.entity_id=stk.product_id AND stk.stock_id = 1 ';

            if ($manageStockConfig) {
                $sql .= ' AND IF (stk.use_config_manage_stock = 1, stk.is_in_stock = 0';
            } else {
                $sql .= ' AND IF (stk.use_config_manage_stock = 1, FALSE';
            }
            $sql .= ' ,stk.manage_stock = 1 AND stk.is_in_stock = 0)';
            $conditions = $sql;
        }

        if ($conditions) {
            $this->_select = $this->_select->join(
                array('stk' => $this->getTable('cataloginventory/stock_item')),
                $conditions,
                array());
// add for debug
//                array('is_in_stock', 'manage_stock', 'use_config_manage_stock'));
        }

        return $this;
    }

    /**
     * Divide by Top/Sub Category
     * @param $filterByCatId
     * @return $this
     */
    protected function _addCategoryFilterJoin($filterByCatId)
    {
        $conditions = array(
            $this->_alias . '.entity_id=cat_product.product_id',
            $this->_getWriteAdapter()->quoteInto('cat_product.category_id=?', $filterByCatId),
        );
        $this->_select = $this->_select->join(
            array('cat_product' => $this->getTable('catalog/category_product')),
            join(' AND ', $conditions),
            array()
        );

        return $this;
    }

    protected function _addRootFilterJoin()
    {
        $rootId = $this->_store->getRootCategoryId();
        $allSubCat = Mage::getModel('catalog/category')->load($rootId)->getAllChildren();

        $subCountSelect = $this->_getReadAdapter()->select();

        // All Products that belongs to the Root Category and sub
        $subCountSelect->from($this->getTable('catalog/category_product'),
            array('product_id'))
            ->group('product_id')
            ->where('category_id IN (' . $allSubCat . ')');

        $this->_select
            ->join(array('sub_count' => $subCountSelect),
                $this->_alias . '.entity_id=sub_count.product_id', array());

        return $this;
    }

    protected function _addAttributeFilterJoin($attributeCode)
    {
        $storeId = (int)$this->_store->getId();
        $this->_addAttribute($storeId, $attributeCode, true);
        return $this;
    }
}
