<?php

/**
 * Description of SitemapEnhancedPlus
 * @package   CueBlocks_SitemapEnhancedPlus
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhancedPlus_Model_Mysql4_Processor_Catalog_Tag extends CueBlocks_SitemapEnhancedPlus_Model_Mysql4_Processor_Abstract
{
    protected function _construct()
    {
        $this->_init('tag/summary', 'tag_id');
    }

    protected function _setSql()
    {
        $storeId = $this->_config->getStoreId();
        if (trim($storeId) == '') {
            return false;
        }

        $relConditions = array(
            $this->_getWriteAdapter()->quoteInto('r.active=?', 1),
            'r.tag_id=t.tag_id'
        );

        $this->_select = $this->_getWriteAdapter()->select()
            ->from(array('t' => $this->getMainTable()), array('t.tag_id'))
            ->where('t.store_id =?', $storeId)
            ->join(
                array('r' => $this->getTable('tag/relation')), join(' AND ', $relConditions), array());

        return $this->_select;
    }
}