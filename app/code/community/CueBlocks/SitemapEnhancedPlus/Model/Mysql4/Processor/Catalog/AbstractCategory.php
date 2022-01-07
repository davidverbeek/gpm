<?php

/**
 * Description of SitemapEnhancedPlus
 * @package   CueBlocksgetSitemap()EnhancedPlus
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
abstract class CueBlocks_SitemapEnhancedPlus_Model_Mysql4_Processor_Catalog_AbstractCategory extends Mage_Sitemap_Model_Mysql4_Catalog_Category
{
    /**
     * Collection Zend Db select
     *
     * @var Zend_Db_Select
     */
    protected $_select;
    protected $_alias;
    protected $_config = null;

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

        $count = $this->_getWriteAdapter()->fetchOne($countSelect);
        return $count;
    }


}