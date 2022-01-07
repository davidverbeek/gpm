<?php

/**
 * Description of SitemapEnhancedPlus
 * @package   CueBlocks_SitemapEnhancedPlus
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhancedPlus_Model_Mysql4_Processor_Catalog_Category extends CueBlocks_SitemapEnhancedPlus_Model_Mysql4_Processor_Catalog_AbstractCategory
{
    /**
     * Get category collection array
     *
     * @param unknown_type $storeId
     * @return array
     */
    public function _setSql()
    {
        $storeId = $this->_config->getStoreId();
        $addPathOrder = $this->_config->getAddPathOrder();
        $excludeCatIds = $this->_config->getExcludeCatIds();
        $this->_setAlias();

        $store = Mage::app()->getStore($storeId);
        /* @var $store Mage_Core_Model_Store */

        if (!$store) {
            return false;
        }

        $this->_select = $this->_getWriteAdapter()->select()
            ->from($this->getMainTable())
            ->where($this->getIdFieldName() . '=?', $store->getRootCategoryId());

        $categoryRow = $this->_getWriteAdapter()->fetchRow($this->_select);

        if (!$categoryRow) {
            return false;
        }

        $this->_select = $this->_getWriteAdapter()->select()
            ->from(
                array($this->_alias => $this->getMainTable(), 't1_name.name'),
                array($this->getIdFieldName(), 'DATE(updated_at) as updated_at'))
            ->where($this->_alias . '.path LIKE ?', $categoryRow['path'] . '/%');

        if (Mage::helper('sitemapEnhancedPlus')->isMageAboveEE12()) {
            // For EE > 1.12
            //            $urlRewrite = Mage::helper('sitemapEnhancedPlus')->getFactory()->getCategoryUrlRewriteHelper();
            $this->_joinTableToSelect($this->_select, $storeId);
        } else {
            // For CE & EE < 1.12
            $urConditions = array(
                $this->_alias . '.entity_id=ur.category_id',
                $this->_getWriteAdapter()->quoteInto('ur.store_id=?', $store->getId()),
                'ur.product_id IS NULL',
                $this->_getWriteAdapter()->quoteInto('ur.is_system=?', 1),
            );
            $this->_select = $this->_select->joinLeft(
                array('ur' => $this->getTable('core/url_rewrite')), join(' AND ', $urConditions), array('url' => 'request_path')
            );
        }

        if ($addPathOrder) // necessary for divide by top category
            $this->_select = $this->_select->order($this->_alias . '.path');

        if ($excludeCatIds) {
            $this->_select = $this->_select->where('e.entity_id NOT IN(?)', $excludeCatIds);
        }

        $this->_addFilter($storeId, 'is_active', 1);

        return  $this->_select;
    }

    protected function _joinTableToSelect(Varien_Db_Select $select, $storeId)
    {
        $requestPath = $this->_getWriteAdapter()->getIfNullSql('url_rewrite.request_path', 'default_ur.request_path');
        $urlSuffix  = Mage::helper('catalog/category')->getCategoryUrlSuffix($storeId);
        $urlSuffix = $urlSuffix ? '.' . $urlSuffix : '';

        $select->joinLeft(
            array('url_rewrite_category' => $this->getTable('enterprise_catalog/category')),
            'url_rewrite_category.category_id = main_table.entity_id' .
            ' AND ' . $this->_getWriteAdapter()->quoteInto('url_rewrite_category.store_id = ?', (int)$storeId),
            array('')
        )->joinLeft(
                array('url_rewrite' => $this->getTable('enterprise_urlrewrite/url_rewrite')),
                'url_rewrite_category.url_rewrite_id = url_rewrite.url_rewrite_id AND url_rewrite.is_system = 1',
                array('')
            )->joinLeft(array('default_urc' => $this->getTable('enterprise_catalog/category')),
                'default_urc.category_id = url_rewrite_category.category_id AND default_urc.store_id = 0',
                array('')
            )->joinLeft(array('default_ur' => $this->getTable('enterprise_urlrewrite/url_rewrite')),
                'default_ur.url_rewrite_id = default_urc.url_rewrite_id  AND default_ur.is_system = 1',
                array('url' => 'concat(' . $requestPath . ',"' . $urlSuffix . '")'));

        return $this;
    }

    /**
     * Add attribute to filter
     *
     * @param int $storeId
     * @param string $attributeCode
     * @param mixed $value
     * @param string $type
     * @return Zend_Db_Statement_Interface
     */
//    protected function _addFilter($storeId, $attributeCode, $value, $type = '=')
//    {
//        if (!isset($this->_attributesCache[$attributeCode])) {
//            $attribute = Mage::getSingleton('catalog/category')->getResource()->getAttribute($attributeCode);
//
//            $this->_attributesCache[$attributeCode] = array(
//                'entity_type_id' => $attribute->getEntityTypeId(),
//                'attribute_id'   => $attribute->getId(),
//                'table'          => $attribute->getBackend()->getTable(),
//                'is_global'      => $attribute->getIsGlobal(),
//                'backend_type'   => $attribute->getBackendType()
//            );
//        }
//
//        $attribute = $this->_attributesCache[$attributeCode];
//
//        if (!$this->_select instanceof Zend_Db_Select) {
//            return false;
//        }
//
//        switch ($type)
//        {
//            case '=':
//                $conditionRule = '=?';
//                break;
//            case 'in':
//                $conditionRule = ' IN(?)';
//                break;
//            case 'like':
//                $conditionRule = ' LIKE(?)';
//                break;
//            default:
//                return false;
//                break;
//        }
//
//        if ($attribute['backend_type'] == 'static') {
//            $this->_select->where('e.' . $attributeCode . $conditionRule, $value);
//        } else {
//            $this->_select->join(
//                            array('t1_' . $attributeCode => $attribute['table']), 'e.entity_id=t1_' . $attributeCode . '.entity_id AND t1_' . $attributeCode . '.store_id=0', array($attributeCode => 'value')
//                    )
//                    ->where('t1_' . $attributeCode . '.attribute_id=?', $attribute['attribute_id']);
//
//            if ($attribute['is_global']) {
//                $this->_select->where('t1_' . $attributeCode . '.value' . $conditionRule, $value);
//            } else {
//                $ifCase = $this->getCheckSql('t2_' . $attributeCode . '.value_id > 0', 't2_' . $attributeCode . '.value', 't1_' . $attributeCode . '.value');
//                $this->_select->joinLeft(
//                                array('t2_' . $attributeCode => $attribute['table']), $this->_getWriteAdapter()->quoteInto('t1_' . $attributeCode . '.entity_id = t2_' . $attributeCode . '.entity_id AND t1_' . $attributeCode . '.attribute_id = t2_' . $attributeCode . '.attribute_id AND t2_' . $attributeCode . '.store_id=?', $storeId), array()
//                        )
//                        ->where('(' . $ifCase . ')' . $conditionRule, $value);
//            }
//        }
//
//        return $this->_select;
//    }

    /**
     * FOR COMPATIBILITY WITH 1.5 and 1.4
     * Generate fragment of SQL, that check condition and return true or false value
     *
     * @param Zend_Db_Expr|Zend_Db_Select|string $expression
     * @param string $true true value
     * @param string $false false value
     */
//    public function getCheckSql($expression, $true, $false)
//    {
//        if ($expression instanceof Zend_Db_Expr || $expression instanceof Zend_Db_Select) {
//            $expression = sprintf("IF((%s), %s, %s)", $expression, $true, $false);
//        } else {
//            $expression = sprintf("IF(%s, %s, %s)", $expression, $true, $false);
//        }
//
//        return new Zend_Db_Expr($expression);
//    }

}
