<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Sitemap
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Sitemap cms page collection model
 *
 * @category    Mage
 * @package     Mage_Sitemap
 * @author      Magento Core Team <core@magentocommerce.com>
 */

// To mantain compatibility with Ce 1.4 it is better to extend Mage_Core_Model_Mysql4_Abstract
class CueBlocks_SitemapEnhancedPlus_Model_Mysql4_Processor_Cms_Page extends CueBlocks_SitemapEnhancedPlus_Model_Mysql4_Processor_Abstract
{
    /**
     * Init resource model (catalog/category)
     *
     */
    protected function _construct()
    {
        $this->_init('cms/page', 'page_id');
    }

    /**
     * Retrieve cms page collection array
     *
     * @param unknown_type $storeId
     * @return Zend_Db_Statement_Interface
     */
    public function _setSql($includeEEHierarchy = false)
    {
        $storeId = $this->_config->getStoreId();
        $this->_select = $this->_getWriteAdapter()->select();

        $this->_select
            ->from(array('main_table' => $this->getMainTable()), array($this->getIdFieldName(), 'DATE(main_table.update_time) as updated_at'))
            ->join(
                array('store_table' => $this->getTable('cms/page_store')),
                'main_table.page_id=store_table.page_id',
                array()
            )
            ->where('main_table.is_active=1')
            ->where('store_table.store_id IN(?)', array(0, $storeId))
            ->where('main_table.identifier NOT IN(?)',array('no-route','enable-cookies'))
            ->group('main_table.page_id');

        if ($includeEEHierarchy) {
            $this->_addHierarchy();
        }
        else{
            $this->_select->columns( 'main_table.identifier as url');
        }
        return  $this->_select;
    }

    protected function _addHierarchy()
    {
        $requestPath = $this->_getWriteAdapter()->getIfNullSql('h.request_url', 'main_table.identifier');
        // Add Hierarchy
        $this->_select
            ->join(
                array('h' => $this->getTable('enterprise_cms/hierarchy_node')),
                'main_table.page_id=h.page_id',
                array($requestPath . ' as url')
            );

        return $this;
    }
}
