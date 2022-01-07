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
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sales Order Creditmemo Pdf default items renderer
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */

class NMediaSystems_MagentoPDFInvoice_Model_Order_Pdf_Items_Default_Creditmemo extends NMediaSystems_MagentoPDFInvoice_Model_Order_Pdf_Items_Abstract {

    /*public function getItemOptions () {
        $options = parent::getItemOptions();

        $filteredOptions = array();
        foreach ($options as $option) {
            switch ($option['option_id']) {
                case 439: // rollen 4m
                case 438: // rollen 2m
                    $filteredOptions[] = $option;
                    break;
            }
        }

        return $filteredOptions;
    }*/

    public function getItemOption ($label) {
        $options = parent::getItemOptions();

        foreach ($options as $option) {
            if ($option['label'] == $label) {
                return isset ( $option ['print_value'] ) ? $option ['print_value'] : strip_tags ( $option ['value'] );
            }
        }
    }

    /**
     * Draw item line
     *
     */
    public function draw() {
        $order = $this->getOrder ();
        $item = $this->getItem ();
        $pdf = $this->getPdf ();
        $page = $this->getPage ();
        $lines = array();

        // draw Product name
        $pakket = 0; //$this->getItemOption("Pakket");
        $lines [0] = array();
        $feed      = 35;
        if (!empty($pakket)) {
            $lines [0][] = array(
                'text' => $pakket . ".",
                'feed' => $feed,
                'font' => "bold"
            );
            //$feed += 20;
        }
        $lines [0][] = array(
            'text' => Mage::helper ( 'core/string' )->str_split ( $item->getName (), 68, true, true ),
            'feed' => $feed
        );

        if (Mage::getStoreConfig ( 'sales/identity/displaytax', $order->getStoreId() )) {
            // draw SKU
            if (strlen ( $this->getSku($item) ) > 7) {
                $sku = substr ( $this->getSku($item), 0, 7 ) . "...";
            } else {
                $sku = $this->getSku($item);
            }

            $lines [0] [] = array(
                'text' => Mage::helper ( 'core/string' )->str_split ( $sku, 10 ),
                'feed' => 320
            );

            // draw QTY
            $lines [0] [] = array(
                'text' => $item->getQty () * 1,
                'feed' => 420,
                'align' => 'right'
            );

            // draw Price
            $lines [0] [] = array(
                'text' => $order->formatPriceTxt ( $item->getPrice () ),
                'feed' => 427,
                'align' => 'right'
            );

            // draw Tax
            $lines [0] [] = array(
                'text' => $order->formatPriceTxt ( $item->getTaxAmount () ),
                'feed' => 470,
                'font' => 'bold',
                'align' => 'right'
            );

            // draw Subtotal
            $lines [0] [] = array(
                'text' => $order->formatPriceTxt ( $item->getRowTotal () ),
                'feed' => 570,
                'align' => 'right'
            );

            // custom options
            $options = $this->getItemOptions ();
            if ($options) {
                foreach ( $options as $option ) {
                    // draw options label
                    $_printValue = strip_tags ( $option ['label'] ) . ": ";
                    /*$lines [] [] = array(
                        'text' => Mage::helper ( 'core/string' )->str_split ( $_printValue, 40, true, true ),
                        'font' => 'italic',
                        'feed' => 50
                    );*/

                    if ($option ['value']) {
                        $_printValue .= isset ( $option ['print_value'] ) ? $option ['print_value'] : strip_tags ( $option ['value'] );
                        //$values = explode ( ', ', $_printValue );
                        //foreach ( $values as $value ) {
                        $lines [] [] = array(
                            'text' => Mage::helper ( 'core/string' )->str_split ( $_printValue, 40, true, true ),
                            'font' => 'italic',
                            'feed' => 75
                        );
                        //}
                    }
                }
            }
        } else {
            // draw SKU
            if (strlen ( $this->getSku($item) ) > 15 ) {
                $sku = substr ( $this->getSku($item), 0, 15 ) . "...";
            } else {
                $sku = $this->getSku($item);
            }

            $lines [0] [] = array(
                'text' => Mage::helper ( 'core/string' )->str_split ( $sku, 18 ),
                'feed' => 320
            );

            // draw QTY
            if($item->getQty () < 100){
                $lines [0] [] = array(
                    'text' => $item->getQty () * 1,
                    'feed' => 455,
                    'align' => 'right'
                );
            }
            else if($item->getQty () < 1000){
                $lines [0] [] = array(
                    'text' => $item->getQty () * 1,
                    'feed' => 457,
                    'align' => 'right'
                );
            }else if($item->getQty () > 10000){
                $lines [0] [] = array(
                    'text' => $item->getQty () * 1,
                    'feed' => 460,
                    'align' => 'right'
                );
            }else{
                $lines [0] [] = array(
                    'text' => $item->getQty () * 1,
                    'feed' => 459,
                    'align' => 'right'
                );
            }

            // draw Price
            $lines [0] [] = array(
                'text' => $order->formatPriceTxt ( $item->getPrice () ),
                'feed' => 427,
                'align' => 'right'
            );
            $_product=Mage::getModel('catalog/product')->load($item->getProductId());
            $unit = Mage::helper('featured')->getProductUnit($_product->getData('verkoopeenheid'));
            $prijsfactor = 1;
            $verkoopeenheid = $_product->getData('verkoopeenheid');
            //if (!$_product->getData('afwijkenidealeverpakking'))
            //$prijsfactor = $_product->getData('prijsfactor');
			$prijsfactor = Mage::helper('featured')->getPrijsfactorValue($_product);
            //if ($prijsfactor == 1)
            //    $prijsfactor = '';

            if($prijsfactor == 1){
                $prijsfactor = "";
                $stuks= Mage::helper('featured')->__('per') . " " . $prijsfactor . " " . Mage::helper('featured')->getStockUnit($prijsfactor, $unit);

                $lines [0] [] = array(
                    'text' => $stuks,
                    'feed' => 472
                );
            }else{
                $stuks= Mage::helper('featured')->__('per') . " " . $prijsfactor . " " . Mage::helper('featured')->getStockUnit($prijsfactor, $unit);

                $lines [0] [] = array(
                    'text' => $stuks,
                    'feed' => 535,
                    'font' => 'bold',
                    'align' => 'right'
                );
            }


            // draw Tax
            //$lines [0] [] = array(
            //	'text' => $order->formatPriceTxt ( $item->getTaxAmount () ),
            //	'feed' => 470,
            //	'font' => 'bold',
            //	'align' => 'right'
            //);

            // draw Subtotal
            $lines [0] [] = array(
                'text' => $order->formatPriceTxt ( $item->getRowTotal () ),
                'feed' => 575,
                'align' => 'right'
            );

            // custom options
            $options = $this->getItemOptions ();
            if ($options) {
                foreach ( $options as $option ) {
                    // draw options label
                    $_printValue = strip_tags ( $option ['label'] ) . ": ";
                    /*$lines [] [] = array(
                        'text' => Mage::helper ( 'core/string' )->str_split ( $_printValue, 40, true, true ),
                        'font' => 'italic',
                        'feed' => 50
                    );*/

                    if ($option ['value']) {
                        $_printValue .= isset ( $option ['print_value'] ) ? $option ['print_value'] : strip_tags ( $option ['value'] );
                        //$values = explode ( ', ', $_printValue );
                        //foreach ( $values as $value ) {
                        $lines [] [] = array(
                            'text' => Mage::helper ( 'core/string' )->str_split ( $_printValue, 40, true, true ),
                            'font' => 'italic',
                            'feed' => 75
                        );
                        //}
                    }
                }
            }
        }

        $lineBlock = array(
            'lines' => $lines,
            'height' => 14
        );

        $this->_setFontRegular ();

        $page = $pdf->drawLineBlocks ( $page, array(
            $lineBlock
        ), array(
            'table_header' => true
        ) );
        $this->setPage ( $page );
    }
}
