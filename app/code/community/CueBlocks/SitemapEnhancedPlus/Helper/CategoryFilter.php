<?php

/**
 * Description of
 * @package   CueBlocks_
 * @company   CueBlocks - http://www.cueblocks.com/
 * @author    Francesco Magazzu' <magento at cueblocks.com>
 * @support   <magento at cueblocks.com>
 */
class CueBlocks_SitemapEnhancedPlus_Helper_CategoryFilter extends Mage_Core_Helper_Abstract
{
    protected $_configKey = 'general';
    protected $_filterProductsIds = null;

    protected function _getReadAdapter()
    {
        $this->_read = Mage::getSingleton('core/resource')->getConnection('core_read');
        return $this->_read;
    }

    protected function _getFiltersCategoryIds($sitemap)
    {
        $config = Mage::helper('sitemapEnhancedPlus')->getConfig($this->_configKey, $sitemap->getStoreId());
        return explode(',',$config->getCategoryFilter());
    }

    protected function _getFiltersProductsIds($sitemap)
    {
        if ($this->_filterProductsIds !== null) {
            return $this->_filterProductsIds;
        } else {
            if ($cat_ids = $this->_getFiltersCategoryIds($sitemap)) {

                $prod_ids = array();

                if (count($cat_ids) > 0) {

                    $productTable = Mage::getSingleton('core/resource')->getTableName('catalog/category_product');
                    $readAdapter = $this->_getReadAdapter();

                    $inCatIds = '(' . implode(',', $cat_ids) . ')';
                    $joinCondition = array(
                        'main_table.product_id = sub_cat_check.product_id',
                    );
                    $select = $readAdapter->select()
                        ->from(
                            array('main_table' => $productTable),
                            array('main_table.product_id', 'sub_cat_check.checkcount')
                        )
                        // We want to exclude only Product that belong to excluded category BUT not to other category
                        ->joinLeft(
                            array('sub_cat_check' => $this->_subSelectOtherCategoryCheck($inCatIds)),
                            join(' AND ', $joinCondition), array())
                        ->where('sub_cat_check.checkcount IS NULL')
                        // Don't use IN(?)
                        // we avoid '' quote in IN numeric element ( faster )
                        ->where('main_table.category_id IN ' . $inCatIds);

//                    Mage::log((string)$select, null, 'sitemap.log');

                    $prod_ids = $readAdapter->fetchCol($select);
                }
                $this->_filterProductsIds = $prod_ids;
            }
        }
        return $this->_filterProductsIds;
    }

    protected function _subSelectOtherCategoryCheck($inCatIds)
    {
        $readConnection = $this->_getReadAdapter();
        $cat_productTable = Mage::getSingleton('core/resource')->getTableName('catalog/category_product');
        $catStatusTable = Mage::getSingleton('core/resource')->getTableName('catalog_category_entity_int');

        $statusAttribute = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_category', 'is_active');

        $joinConditions = array(
            'cat_check.category_id = status_check.entity_id',
            'status_check.attribute_id = ' . $statusAttribute->getId(),
            'status_check.value = 1'
        );

        $subQuery = new Varien_Db_Select($readConnection);

        $subQuery
            ->from(array('cat_check' => $cat_productTable),
                array('product_id', 'checkcount' => 'count(*)'))
            ->joinRight(array('status_check' => $catStatusTable),
                join(' AND ', $joinConditions), array())
            ->where('cat_check.category_id NOT IN ' . $inCatIds)
            ->group('product_id'); // important for sub query to work

        return $subQuery;
    }
//    protected function _getCategoriesProducts($catIds)
//    {
//        $collection = Mage::getModel('catalog/product')
//            ->getCollection()
//            ->joinField('category_id', 'catalog/category_product', 'category_id', 'product_id = entity_id', null, 'left')
//            ->addAttributeToSelect('*')
//            ->addAttributeToFilter('category_id', array('in' => $catIds));
//        Mage::log((string)$collection->getSelect(), null, 'sitemap.log');
//        return $collection->getAllIds();
//    }


    public function filterProduct($id, $sitemap)
    {
        if (in_array($id, $this->_getFiltersProductsIds($sitemap))) {
            // not allowed
            return true;
        }
        return false;
    }

    public function filterCategory($id, $sitemap)
    {
        if (in_array($id, $this->_getFiltersCategoryIds($sitemap))) {
            // not allowed
            return true;
        }
        return false;
    }
}