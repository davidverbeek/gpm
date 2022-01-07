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
 * @category   Mage
 * @package    Mage_Bundle
 * @copyright  Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sales Order Shipment Pdf items renderer
 *
 * @category   Mage
 * @package    Mage_Bundle
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class NMediaSystems_MagentoPDFInvoice_Model_Order_Pdf_Items_Bundle_Shipment extends NMediaSystems_MagentoPDFInvoice_Model_Order_Pdf_Items_Bundle_Abstract 
{
	/**
	 * Draw item line
	 *
	 */
	public function draw() {
		$item = $this->getItem ();
		$pdf = $this->getPdf ();
		$page = $this->getPage ();
		
		$this->_setFontRegular ();
		
		$shipItems = $this->getChilds ( $item );
		$items = array_merge ( array ($item->getOrderItem () ), $item->getOrderItem ()->getChildrenItems () );
		
		$_prevOptionId = '';
		$drawItems = array ();
		
		foreach ( $items as $_item ) {
			$line = array ();
			
			$attributes = $this->getSelectionAttributes ( $_item );
			if (is_array ( $attributes )) {
				$optionId = $attributes ['option_id'];
			} else {
				$optionId = 0;
			}
			
			if (! isset ( $drawItems [$optionId] )) {
				$drawItems [$optionId] = array ('lines' => array (), 'height' => 10 );
			}
			
			if ($_item->getParentItem ()) {
				if ($_prevOptionId != $attributes ['option_id']) {
					$line [0] = array ('font' => 'italic', 'text' => Mage::helper ( 'core/string' )->str_split ( $attributes ['option_label'], 40, true, true ), 'feed' => 90 );
					
					$drawItems [$optionId] = array ('lines' => array ($line ), 'height' => 10 );
					
					$line = array ();
					
					$_prevOptionId = $attributes ['option_id'];
				}
			}
			
			if (($this->isShipmentSeparately () && $_item->getParentItem ()) || (! $this->isShipmentSeparately () && ! $_item->getParentItem ())) {
				if (isset ( $shipItems [$_item->getId ()] )) {
					$qty = $shipItems [$_item->getId ()]->getQty () * 1;
				} else if ($_item->getIsVirtual ()) {
					$qty = Mage::helper ( 'bundle' )->__ ( 'N/A' );
				} else {
					$qty = 0;
				}
			} else {
				$qty = '';
			}
			
			$line [] = array ('text' => $qty, 'feed' => 40 );
			
			// draw Name
			if ($_item->getParentItem ()) {
				$feed = 90;
				$name = $this->getValueHtml ( $_item );
			} else {
				$feed = 90;
				$name = $_item->getName ();
			}
			$text = array ();
			foreach ( Mage::helper ( 'core/string' )->str_split ( $name, 60, true, true ) as $part ) {
				$text [] = $part;
			}
			$line [] = array ('text' => $text, 'feed' => $feed );
			
			// draw SKUs
			$text = array ();
			
			if (strlen ( $item->getSku () ) > 10) {
				$sku = substr ( $item->getSku (), 0, 10 ) . "...";
			} else {
				$sku = $item->getSku ();
			}
			
			foreach ( Mage::helper ( 'core/string' )->str_split ( $sku, 25 ) as $part ) {
				$text [] = $part;
			}
			$line [] = array ('text' => $text, 'feed' => 420 );
			
			$drawItems [$optionId] ['lines'] [] = $line;
		}
		
		// custom options
		$options = $item->getOrderItem ()->getProductOptions ();
		if ($options) {
			if (isset ( $options ['options'] )) {
				foreach ( $options ['options'] as $option ) {
					$lines = array ();
					$lines [] [] = array ('text' => Mage::helper ( 'core/string' )->str_split ( strip_tags ( $option ['label'] ), 70, true, true ), 'font' => 'italic', 'feed' => 90 );
					
					if ($option ['value']) {
						$text = array ();
						$_printValue = isset ( $option ['print_value'] ) ? $option ['print_value'] : strip_tags ( $option ['value'] );
						$values = explode ( ', ', $_printValue );
						foreach ( $values as $value ) {
							foreach ( Mage::helper ( 'core/string' )->str_split ( $value, 50, true, true ) as $_value ) {
								$text [] = $_value;
							}
						}
						
						$lines [] [] = array ('text' => $text, 'feed' => 90 );
					}
					
					$drawItems [] = array ('lines' => $lines, 'height' => 10 );
				}
			}
		}
		
		$page = $pdf->drawLineBlocks ( $page, $drawItems, array ('table_header' => true ) );
		$this->setPage ( $page );
	}
}
