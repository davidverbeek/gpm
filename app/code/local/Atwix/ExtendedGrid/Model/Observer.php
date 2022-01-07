<?php
/**
 * Atwix
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
 * @category    Atwix
 * @package     Atwix_ExtendedGrid
 * @author      Atwix Core Team
 * @copyright   Copyright (c) 2014 Atwix (http://www.atwix.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Atwix_ExtendedGrid_Model_Observer
{
  /**
   * Joins extra tables for adding custom columns to Mage_Adminhtml_Block_Sales_Order_Grid
   * @param Varien_Object $observer
   * @return Atwix_Exgrid_Model_Observer
   */
  public function salesOrderGridCollectionLoadBefore($observer)
  {
    $collection = $observer->getOrderGridCollection();
    $select = $collection->getSelect();
    $strSalesFlatOrderItem = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_item');
    
    // To get payment method code for order
    $select->joinLeft(
      array('payment' => $collection->getTable('sales/order_payment')), 
      'payment.parent_id=main_table.entity_id', 
      array('payment_method' => 'method')
    );
//    $select->join(
//        $strSalesFlatOrderItem, 
//        '`'.$strSalesFlatOrderItem.'`.order_id=`main_table`.entity_id', 
//        array('skus' => new Zend_Db_Expr('group_concat(`'.$strSalesFlatOrderItem.'`.sku SEPARATOR ", ")'))
//    );
    
    // Join to get payment method title from its code
    $strCoreConfigData = Mage::getSingleton('core/resource')->getTableName('core_config_data');
    $select->join(
        array('payment_title' => $strCoreConfigData),
        'payment_title.path LIKE concat("payment/", payment.method, "/title")',
        array('value as payment_method_title', 'path as payment_method_path')
    );
    
//    $select->group('main_table.entity_id');
  }

  /**
   * callback function used to filter collection
   * @param $collection
   * @param $column
   * @return $this
   */
  public function filterSkus($collection, $column)
  {
    if (!$value = $column->getFilter()->getValue()) {
      return $this;
    }

    $strSalesFlatOrderItem = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_item');
    $collection->getSelect()->having("group_concat(`$strSalesFlatOrderItem`.sku SEPARATOR ', ') like ?", "%$value%");

    return $this;
  }
    /**
     * Joins extra tables for adding custom columns to Mage_Adminhtml_Block_Sales_Order_Grid
     * @param Varien_Object $observer
     * @return Atwix_Exgrid_Model_Observer
     */
    public function addCompanyNameToSalesOrderGrid(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();
        $this->_grid = $event->getBlock();
        if ($this->_grid instanceof Mage_Adminhtml_Block_Sales_Order_Grid) {
            $this->_collection = $this->_grid->getCollection();
            $this->_collection->getSelect()->join(Mage::getSingleton('core/resource')->getTableName('sales/order_address'), "main_table.entity_id = ".Mage::getSingleton('core/resource')->getTableName('sales/order_address').".parent_id",array('company') )->where(Mage::getSingleton('core/resource')->getTableName('sales/order_address').".address_type =  'billing'");

            $columnData = array(
                'header' => 'Company Name',
                'index'  => 'company',
                'type' => 'text',
				'filter' => false,
				'sortable'  => false,
				
            );

            $this->_grid->addColumnAfter('company', $columnData, 'created_at');
            $this->_grid->sortColumnsByOrder();
            // rebuild the filters
            $filter = $this->_grid->getParam($this->_grid->getVarNameFilter(), null);
            if (is_null($filter)) {
                $this->_collection->load();
            }
            $this->_collection->clear();
            
            // force a reload of the collection
            $this->_collection->load();
        }
    }

    
}