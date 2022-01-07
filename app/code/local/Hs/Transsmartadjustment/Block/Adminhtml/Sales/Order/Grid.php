<?php

/**
 * class        Hs_Transsmartadjustment_Block_Adminhtml_Sales_Order_Grid
 * @category    Transsmart
 * @package     Hs_Transsmartadjustment
 * @author      Hs_SJD
 * @date        05 June, 2018
 *
 */
class Hs_Transsmartadjustment_Block_Adminhtml_Sales_Order_Grid extends Transsmart_Shipping_Block_Adminhtml_Sales_Order_Grid
{
    /**
     * Prepare and add columns to grid
     *
     * @return $this
     */
    protected function _prepareColumns()
    {

        $this->addColumnAfter('transsmart_book_and_print_user1', [
            'header' => $this->__('B&P 1'),
            'width' => '60px',
            'index' => 'transsmart_book_and_print',
            'type' => 'action',
            'sortable' => false,
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => $this->__('B&P 1'),
                    'url' => array('base' => 'admin_sales/adminhtml_index/massBookAndPrintUser1'),
                    'field' => 'order_ids'
                )
            ),
            'filter' => false
        ], 'massaction');

        $this->addColumnAfter('transsmart_book_and_print_user2', [
            'header' => $this->__('B&P 2'),
            'width' => '60px',
            'index' => 'transsmart_book_and_print',
            'type' => 'action',
            'sortable' => false,
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => $this->__('B&P 2'),
                    'url' => array('base' => 'admin_sales/adminhtml_index/massBookAndPrintUser2'),
                    'field' => 'order_ids'
                )
            ),
            'filter' => false
        ], 'massaction');

        $this->addColumnAfter('post_shipment_carrier', [
            'header' => $this->__('Bpost'),
            'width' => '60px',
            'index' => 'post_shipment_carrier',
            'type' => 'action',
            'sortable' => false,
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::getStoreConfig("transsmart_shipping/transsmartadjustment/custom_name", Mage::app()->getStore()->getId()),
                    'url' => array('base' => 'admin_sales/adminhtml_index/shipmentCarrierChange'),
                    'field' => 'order_id'
                )
            ),
            'filter' => false
        ], 'massaction');

        return parent::_prepareColumns();
    }
}