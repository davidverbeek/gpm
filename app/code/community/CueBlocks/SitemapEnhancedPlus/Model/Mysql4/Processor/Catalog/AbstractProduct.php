<?php

/**
 * Description of SitemapEnhancedPlus
 * @package   CueBlocksgetSitemap()EnhancedPlus
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
abstract class CueBlocks_SitemapEnhancedPlus_Model_Mysql4_Processor_Catalog_AbstractProduct extends Mage_Sitemap_Model_Mysql4_Catalog_Product
{
    /**
     * Collection Zend Db select
     *
     * @var Zend_Db_Select
     */
    protected $_select;
    protected $_alias;
    protected $_config = null;
    protected $_attributesCache = array();

    abstract protected function _setSql();

    public function init($config)
    {
        $this->_config = $config;
        return $this;
    }

    protected function _setAlias()
    {
        $alias = 'e';

        if (Mage::helper('sitemapEnhancedPlus')->isMageAbove18()) {
            $alias = 'main_table';
        }
        $this->_alias = $alias;

        return $this->_alias;
    }

    protected function _getBaseSelect()
    {
        $select = $this->_getWriteAdapter()->select()
            ->from(array($this->_alias => $this->getMainTable()), array($this->getIdFieldName(), 'DATE(updated_at) as updated_at'))
            ->join(
                array('w' => $this->getTable('catalog/product_website')),
                $this->_alias . '.entity_id=w.product_id',
                array()
            )
            ->where('w.website_id=?', $this->_store->getWebsiteId());

        return $select;
    }

    /**
     * @param REQUIRED FOR METHOD SIGNATURE COMPATIBILITY $forCompatibility
     * @return array|Zend_Db_Statement_Interface
     */
    public function getCollection($forCompatibility = null)
    {
        $this->_select = $this->_setSql();

        if ($this->_config->getMysqlUsePagination()) {
            $pageSize = $this->_config->getMysqlPageSize();
            $pageCurrent = $this->_config->getMysqlPageCurrent();
            $this->_select->limitPage($pageCurrent, $pageSize);
//            mage::log((string)($this->_select), null, 'log.log');
        }
        $query = $this->_getWriteAdapter()->query($this->_select);

        return $query;
    }

    /**
     * @return Count SQL for pagination
     * @throws Zend_Db_Select_Exception
     */
    public function getCount()
    {
        // populate select
        $this->_setSql(true);

        $countSelect = clone $this->_select;
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(Zend_Db_Select::COLUMNS);

        $countSelect->columns('COUNT(*)');

//        MAGE::log((string)$countSelect);
        $count = $this->_getWriteAdapter()->fetchOne($countSelect);
        return $count;
    }

    /**
     * Tools For divideBy Attribute
     * @param $storeId
     * @param $attributeCode
     * @param bool $orderBy
     * @return Zend_Db_Select
     * @throws Zend_Db_Select_Exception
     */
    protected function _addAttribute($storeId, $attributeCode, $orderBy = false)
    {
        if (!isset($this->_attributesCache[$attributeCode])) {
            $this->_loadAttribute($attributeCode);
        }

        $attribute = $this->_attributesCache[$attributeCode];

        if (!$this->_select instanceof Zend_Db_Select) {
            return false;
        }

        if ($attribute['backend_type'] == 'static') {
            $this->_select->columns($this->_alias . '.' . $attributeCode);
        } else {
            $this->_select->joinLeft(
                array('t1_' . $attributeCode => $attribute['table']),
                $this->_alias . '.entity_id=t1_' . $attributeCode . '.entity_id AND t1_' . $attributeCode . '.store_id=0' .
                ' AND t1_' . $attributeCode . '.attribute_id=' . $attribute['attribute_id'],
                array()
            );

            if ($attribute['is_global']) {
                $this->_select->columns(array($attributeCode => 't1_' . $attributeCode . '.value'));
            } else {
                $ifCase = $this->_select->getAdapter()->getCheckSql('t2_' . $attributeCode . '.value_id > 0',
                    't2_' . $attributeCode . '.value', 't1_' . $attributeCode . '.value'
                );
                $this->_select->joinLeft(
                    array('t2_' . $attributeCode => $attribute['table']),
                    $this->_getWriteAdapter()->quoteInto(
                        't1_' . $attributeCode . '.entity_id = t2_' . $attributeCode . '.entity_id AND t1_'
                        . $attributeCode . '.attribute_id = t2_' . $attributeCode . '.attribute_id AND t2_'
                        . $attributeCode . '.store_id = ?', $storeId
                    ),
                    array($attributeCode => $ifCase)
                );
            }
        }
        $this->_select->order($attributeCode);
        return $this->_select;
    }

    /**
     * for compatibility with < 1.8
     */
    protected function _loadAttribute($attributeCode)
    {
        $attribute = Mage::getSingleton('catalog/product')->getResource()->getAttribute($attributeCode);

        $this->_attributesCache[$attributeCode] = array(
            'entity_type_id' => $attribute->getEntityTypeId(),
            'attribute_id' => $attribute->getId(),
            'table' => $attribute->getBackend()->getTable(),
            'is_global' => $attribute->getIsGlobal() == Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
            'backend_type' => $attribute->getBackendType()
        );
        return $this;
    }

    /**
     * === DEBUG TOOLS ===
     */

    protected function _initDebug($storeId)
    {
        $data = array(
            'mysql_use_pagination' => false,
            'store_id' => $storeId,
            'filter_out_of_stock' => false,
            'filter_in_stock' => false,
            'use_root_catalog_mode' => false,
            'visibility' => explode(',', Mage::helper('sitemapEnhancedPlus')->getConfig('product', $storeId)->getVisibility())
        );
        $config = new Varien_Object($data);
        $this->init($config);
    }

    /**
     * Look for any product that are not in the sitemap
     * @param int $storeId
     * @return array
     */
    public function getAllExcluded($storeId = 0)
    {
        $all_ids = array();
        $sitemap_ids = array();

        $this->_initDebug($storeId);
        $this->_setAlias();
        /* @var $result Zend_Db_Statement_Interface */
        $result = $this->getCollection();
        while ($id = $result->fetchColumn(0)) {
            $sitemap_ids[] = $id;
        }
        unset($result);

        $result = $this->_getAllStoreProd($storeId);
        while ($id = $result->fetchColumn()) {
            $all_ids[] = $id;
        }
        unset($result);

        $excluded = array_diff($all_ids, $sitemap_ids);
        return $excluded;
    }

    /**
     * It looks that this
     */
    protected function _getAllStoreProd($storeId)
    {
        $this->_setAlias();
        $this->_initDebug($storeId);

        $store = Mage::app()->getStore($storeId);
        /* @var $store Mage_Core_Model_Store */
        $this->_store = $store;
        if (!$store) {
            return false;
        }

        $this->_select = $this->_getConnection('write')->select()
            ->from(array($this->_alias => $this->getMainTable()), array($this->getIdFieldName(), 'updated_at'))
            ->joinLeft(
                array('w' => $this->getTable('catalog/product_website')),
                $this->_alias . '.entity_id=w.product_id',
                array()
            )
            ->where('w.website_id='.$store->getWebsiteId().' OR w.website_id is null');;

        //$this->_addFilter($storeId, 'visibility', $visibility, 'in');
        //$this->_addFilter($storeId, 'status', Mage::getSingleton('catalog/product_status')->getVisibleStatusIds(), 'in');

//        die((string)($this->_select));

        $query = $this->_getWriteAdapter()->query($this->_select);

        return $query;
    }

    /**
     * Look for product not associated with any category or to any configurable product
     * @param int $storeId
     * @return array
     */
    public function getOrphan($storeId = 0)
    {
        $this->_initDebug($storeId);
        $this->_setAlias();
        $store = Mage::app()->getStore($storeId);

        if (!$store) {
            return false;
        }
        $this->_store = $store;
        $visibility = $this->_config->getVisibility();

        $this->_select = $this->_getBaseSelect()
            ->columns('sku');

        $this->_select = $this->_select
            ->joinLeft(
                array('cat' => $this->getTable('catalog/category_product')),
                $this->_alias . '.entity_id=cat.product_id',
                array())
            ->where('cat.product_id is null');

        $this->_select = $this->_select
            ->joinLeft(
                array('rel' => $this->getTable('catalog/product_relation')),
                $this->_alias . '.entity_id=rel.child_id',
                array('rel.parent_id'))
            ->where('rel.parent_id is null');

        $this->_addFilter($storeId, 'visibility', $visibility, 'in');
        $this->_addFilter($storeId, 'status', Mage::getSingleton('catalog/product_status')->getVisibleStatusIds(), 'in');

//        die((string)($this->_select));

        $query = $this->_getWriteAdapter()->query($this->_select);

        return $query;
    }

    /** Look for orphan product that are not in the sitemap
     * @param int $storeId
     * @return array
     */
    public function getExcludedOrphan($storeId = 0)
    {
        $this->_initDebug($storeId);
        $this->_setAlias();
//        $all_ids = array();
        $sitemap_ids = array();
        $orphan_ids = array();

        $result = $this->getOrphan($storeId);
        while ($id = $result->fetchColumn(0)) {
            $orphan_ids[] = $id;
        }
        unset($result);

        /* @var $result Zend_Db_Statement_Interface */
        $result = $this->getCollection();
        while ($id = $result->fetchColumn(0)) {
            $sitemap_ids[] = $id;
        }
        unset($result);
        $excluded = array_diff($orphan_ids, $sitemap_ids);
        return $excluded;
    }

}