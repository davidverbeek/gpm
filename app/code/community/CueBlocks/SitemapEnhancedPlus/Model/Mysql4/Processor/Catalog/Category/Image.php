<?php

/**
 * Description of SitemapEnhancedPlus
 * @package   CueBlocks_SitemapEnhancedPlus
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */

// To mantain compatibility with Ce 1.4 it is better to extend Mage_Core_Model_Mysql4_Abstract
class CueBlocks_SitemapEnhancedPlus_Model_Mysql4_Processor_Catalog_Category_Image extends CueBlocks_SitemapEnhancedPlus_Model_Mysql4_Processor_Abstract
{
    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('catalog_category_entity_varchar', 'value_id');
    }

    /**
     * Get Image for products
     *
     * @TODO: NEED TO BE COMPLETED
     *
     * @param string $storeId
     * @param string $prodId
     *
     * @return Zend_Db_Statement_Interface
     */
    public function _setSql()
    {
        $storeId = $this->_config->getStoreId();
        $entityId = $this->_config->getEntityId();
        $image_attr_id = $this->_config->getImageAttrId();
        $thumb_attr_id = $this->_config->getThumbAttrId();

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
            ->from(array('e' => $this->getMainTable()), array('path' => 'e.value'))
            ->where('e.entity_id =?', $entityId);

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
}