<?php

/**
 * Description of SitemapEnhancedPlus
 * @package   CueBlocks_SitemapEnhancedPlus
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
// To mantain compatibility with Ce 1.4 it is better to extend Mage_Core_Model_Mysql4_Abstract
class CueBlocks_SitemapEnhancedPlus_Model_Mysql4_Processor_Catalog_Product_Image extends CueBlocks_SitemapEnhancedPlus_Model_Mysql4_Processor_Abstract
{
    const GALLERY_VALUE_TABLE = 'catalog/product_attribute_media_gallery_value';

    /**
     * Collection Zend Db select
     *
     * @var Zend_Db_Select
     */
    protected $_alias = 'gallery';

    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('catalog/product_attribute_media_gallery', 'value_id');
    }

    /**
     * Get Image for products
     *
     * TODO: improve the join query
     *
     * @param string $storeId
     * @param string $prodId
     *
     * @return Zend_Db_Statement_Interface
     */
    public function _setSql()
    {
        $storeId = $this->_config->getStoreId();
        $prodId = $this->_config->getProdId();

        $adapter = $this->_getReadAdapter();

        $conditions_store = array(
            'e.value_id=value.value_id',
            $adapter->quoteInto('value.store_id=?', $storeId)
        );

        $conditions_default = array(
            'e.value_id=default_value.value_id',
            $adapter->quoteInto('default_value.store_id=?', 0)
        );

        $this->_select = $adapter->select()
            ->from(array($this->_alias => $this->getMainTable()), array('path' => $this->_alias . '.value'))
            ->where($this->_alias . '.entity_id =?', $prodId);

        $this->_addImageValueJoin($storeId,'label');
        $this->_select = Mage::helper('sitemapEnhancedPlus')->addProductAttributeToSelect('short_description', $this->_select,$this->_alias, $storeId);
        /**
         * EXCLUDE VALUE SHOULD NOT BE USED:
         * ALSO IF AN IMAGE IS EXCLUDED IT IS SHOWN
         * IF ASSIGNED TO SOMETHING
         */
        // Adding info to check if the image has been disabled
//        $this->_select = $this->_select
//            ->joinLeft( // Joining default values
//                array('default_value' => $this->getTable(self::GALLERY_VALUE_TABLE)),
//                join(' AND ', $conditions_default), array('disabled_default' => 'disabled'))
//            ->joinLeft(
//                array('value' => $this->getTable(self::GALLERY_VALUE_TABLE)),
//                join(' AND ', $conditions_store), array('disabled'));

//        die((string)($this->_select));

        return $this->_select;
    }

    protected function _addImageValueJoin($storeId, $column)
    {
        $jointable = $this->getTable(self::GALLERY_VALUE_TABLE);
        $tableAlias = $column.'_TB';

        if (!$this->_select instanceof Zend_Db_Select) {
            return false;
        }

        //Decide if excluded images need to be added to sitemap
        if($this->_config->getAddExcludedImages() == "1") {
            $this->_select->joinInner(
                array('t1_' . $tableAlias => $jointable),
                $this->_alias . '.value_id=t1_' . $tableAlias . '.value_id AND ' .
                't1_' . $tableAlias . '.store_id=0',
                array()
            );
        } else {
            $this->_select->joinInner(
                array('t1_' . $tableAlias => $jointable),
                $this->_alias . '.value_id=t1_' . $tableAlias . '.value_id AND ' .
                't1_' . $tableAlias . '.disabled=0 AND ' .
                't1_' . $tableAlias . '.store_id=0',
                array()
            );
        }

        $ifCase = $this->_select->getAdapter()->getCheckSql('t2_' . $tableAlias . '.value_id > 0',
            't2_' . $tableAlias . '.'.$column, 't1_' . $tableAlias . '.'.$column
        );
        $this->_select->joinLeft(
            array('t2_' . $tableAlias => $jointable),
            $this->_getWriteAdapter()->quoteInto(
                't1_' . $tableAlias . '.value_id = t2_' . $tableAlias . '.value_id AND ' .
                't2_' . $tableAlias . '.store_id = ?', $storeId
            ),
            array($column => $ifCase)
        );

        return $this->_select;
    }



//    public function getAllImagesCollection($storeId, $includeOutOfStock = true, $catId = null)
//    {
//
//        $store = Mage::app()->getStore($storeId);
//        /* @var $store Mage_Core_Model_Store */
//
//        if (!$store) {
//            return false;
//        }
//
//// filter for category
//        if ($catId) {
//            $catConditions = array(
//                'e.entity_id=cat_index.product_id',
//                $adapter->quoteInto('cat_index.store_id=?', $store->getId()),
//                $adapter->quoteInto('cat_index.category_id=?', $catId),
//                $adapter->quoteInto('cat_index.is_parent=?', 1),
//            );
//        } else {
//
//            $rootId = $store->getRootCategoryId();
//
//            $_category = Mage::getModel('catalog/category')->load($rootId); //get category model
//            $child_categories = $_category->getResource()->getChildren($_category, false); //array consisting of all child categories id
//            $child_categories = array_merge(array($rootId), $child_categories);
//
//// filter product that doesn't belongs to the store root category childs
//            $catConditions = array(
//                'e.entity_id=cat_index.product_id',
//                $adapter->quoteInto('cat_index.store_id=?', $store->getId()),
////                $adapter->quoteInto('cat_index.category_id=?', $rootId),
////                $adapter->quoteInto('cat_index.category_id in (?)', $child_categories),
//                $adapter->quoteInto('cat_index.position!=?', 0),
//            );
//        }
//
//        $this->_select = $adapter->select()
//            ->from(array('e' => $this->getMainTable()), array($this->getIdFieldName(), 'e.entity_id', 'path' => 'e.value'))
//            ->join(
//                array('cat_index' => $this->getTable('catalog/category_product_index')), join(' AND ', $catConditions), array());
//
//
//// filter Out of Stock
//        if (!$includeOutOfStock) {
//            $stkConditions = array(
//                'e.entity_id=stk.product_id',
//                $adapter->quoteInto('stk.is_in_stock=?', 1)
//            );
//            $this->_select = $this->_select->join(
//                array('stk' => $this->getTable('cataloginventory/stock_item')), join(' AND ', $stkConditions), array('is_in_stock' => 'is_in_stock'));
//        }
//
////        $valueConditions = array(
////            'e.value_id=w.value_id',
////                // $adapter->quoteInto('w.disabled=?', 0)
////        );
////
////        $this->_select = $this->_select->join(
////                array('w' => $this->getTable('catalog/product_attribute_media_gallery_value')), join(' AND ', $valueConditions), array('w.disabled')
////        );
//
//        $query = $adapter->query($this->_select);
//
////        die((string) ($this->_select));
//
//        return $query;
//    }

}