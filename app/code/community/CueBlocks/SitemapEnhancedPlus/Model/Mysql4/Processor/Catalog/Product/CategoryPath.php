<?php

/**
 * Description of SitemapEnhancedPlus
 * @package   CueBlocks_SitemapEnhancedPlus
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhancedPlus_Model_Mysql4_Processor_Catalog_Product_CategoryPath extends CueBlocks_SitemapEnhancedPlus_Model_Mysql4_Processor_Abstract
{
    /**
     * TODO: IMPLEMENT FIX FOR EE 1.13.1
     *       core/url_rewrite is deprecated on 1.13.1
     *
     */

    /**
     * Init resource model (catalog/category)
     *
     */
    protected function _construct()
    {
        if (Mage::helper('sitemapEnhancedPlus')->isMageAboveEE12()) {
            // For EE > 1.12
            $this->_init('enterprise_urlrewrite/url_rewrite', 'url_rewrite_id');
        } else {
            // For CE & EE < 1.12
            $this->_init('catalog/category_product_index', 'value_id');
        }
    }

    public function _setSql()
    {
        $storeId = $this->_config->getStoreId();
        $prodId = $this->_config->getProdId();
        $catId = $this->_config->getCatId();

        if (Mage::helper('sitemapEnhancedPlus')->isMageAboveEE12()) {
            // For EE > 1.12
            return $this->_setSqlEE($storeId, $prodId, $catId);
        } else {
            // For CE & EE < 1.12
            return $this->_setSqlCE($storeId, $prodId, $catId);
        }
    }

    /**
     * Get Canonical Collection ( all different link for that product )
     *
     * @param string $storeId
     * @param string $prodId
     *
     * @return Zend_Db_Statement_Interface
     */
    public function _setSqlCE($storeId, $prodId, $catId = null)
    {
        $adapter = $this->_getReadAdapter();

        $urlConditions = array(
            'e.product_id=ur.product_id',
            'e.category_id=ur.category_id',
            $adapter->quoteInto('ur.store_id=?', $storeId),
            'ur.is_system="1"',
        );

        $this->_select = $adapter->select()
            ->from(array('e' => $this->getMainTable()), array('category_id'))
            ->where('e.product_id =?', $prodId)
            ->where('e.store_id =?', $storeId)
            ->where('e.is_parent= "1"');

        if ($catId) {
            $this->_select->where('e.category_id =?', $catId);
        }

        $this->_select = $this->_select
            ->join(
                array('ur' => $this->getTable('core/url_rewrite')),
                join(' AND ', $urlConditions),
                array('url' => 'request_path')
            );

//        die((string)($this->_select));

        return $this->_select;
    }

    /**
     * @TODO:
     * There is not a safe way on EE 1.13.1 to find these URLS
     * It is not possible to filter by store ID and trust the result
     */
    public function _setSqlEE($storeId, $prodId)
    {
        $adapter = $this->_getReadAdapter();

        $this->_select = $adapter->select()
            ->from(array('e' => $this->getMainTable()), array('url' => 'request_path'))
            ->where('e.store_id =?', $storeId)
            ->where('e.target_path =?', 'catalog/product/view/id/' . $prodId)
            ->where('e.is_system =?', '0');

//        die((string)($this->_select));

        return $this->_select;
    }
}