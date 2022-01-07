<?php

/**
 * Description of SitemapEnhancedPlus
 * @package   CueBlocksgetSitemap()EnhancedPlus
 * @company   CueBlocks - http://www.cueblocks.com/
 */
abstract class CueBlocks_SitemapEnhancedPlus_Model_Mysql4_Processor_Abstract extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Collection Zend Db select
     *
     * @var Zend_Db_Select
     */
    protected $_select;
    protected $_config = null;

    abstract protected function _setSql();

    public function init($config)
    {
        $this->_config = $config;
        return $this;
    }

    public function getCollection()
    {
        $this->_select = $this->_setSql();

        if ($this->_config->getMysqlUsePagination()) {
            $pageSize = $this->_config->getMysqlPageSize();
            $pageCurrent = $this->_config->getMysqlPageCurrent();
            $this->_select->limitPage($pageCurrent, $pageSize);
        }
        //MAGE::log((string)$this->_select);

        $query = $this->_getWriteAdapter()->query($this->_select);

        return $query;
    }

    public function getCount()
    {
        // populate select
        $this->_setSql();

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