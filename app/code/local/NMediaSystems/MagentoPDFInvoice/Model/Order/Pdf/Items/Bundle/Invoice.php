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
 * @package    Mage_Sales
 * @copyright  Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sales Order Invoice Pdf default items renderer
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class NMediaSystems_MagentoPDFInvoice_Model_Order_Pdf_Items_Bundle_Invoice extends NMediaSystems_MagentoPDFInvoice_Model_Order_Pdf_Items_Bundle_Abstract {
	/**
	 * Draw item line
	 *
	 */
	public function draw() {
		$order = $this->getOrder ();
		$item = $this->getItem ();
		$pdf = $this->getPdf ();
		$page = $this->getPage ();
		
		$this->_setFontRegular ();
		$items = $this->getChilds ( $item );
		
		if (Mage::getStoreConfig ( 'sales/identity/displaytax', $store )) {
			
			$leftBound = 40;
			$rightBound = 550;
			
			$_prevOptionId = '';
			$drawItems = array();
			
			foreach ( $items as $_item ) {
				$line = array();
				
				$attributes = $this->getSelectionAttributes ( $_item );
				if (is_array ( $attributes )) {
					$optionId = $attributes ['option_id'];
				} else {
					$optionId = 0;
				}
				
				if (! isset ( $drawItems [$optionId] )) {
					$drawItems [$optionId] = array( 
						'lines' => array(), 
						'height' => 10 
					);
				}
				
				if ($_item->getOrderItem ()->getParentItem ()) {
					if ($_prevOptionId != $attributes ['option_id']) {
						$line [0] = array( 
							'font' => 'italic', 
							'text' => Mage::helper ( 'core/string' )->str_split ( $attributes ['option_label'], 40, true, true ), 
							'feed' => 40 
						);
						
						$drawItems [$optionId] = array( 
							'lines' => array( 
								$line 
							), 
							'height' => 10 
						);
						
						$line = array();
						
						$_prevOptionId = $attributes ['option_id'];
					}
				}
				
				/* in case Product name is longer than 80 chars - it is written in a few lines */
				if ($_item->getOrderItem ()->getParentItem ()) {
					$feed = 40;
					$name = $this->getValueHtml ( $_item );
				} else {
					$feed = 40;
					$name = $_item->getName ();
				}
				$line [] = array( 
					'text' => Mage::helper ( 'core/string' )->str_split ( $name, 40, true, true ), 
					'feed' => $feed 
				);
				
				// draw SKUs
				if (! $_item->getOrderItem ()->getParentItem ()) {
					$text = array();
					
					if (strlen ( $item->getSku () ) > 7) {
						$sku = substr ( $item->getSku (), 0, 7 ) . "...";
					} else {
						$sku = $item->getSku ();
					}
					
					foreach ( Mage::helper ( 'core/string' )->str_split ( $sku, 10 ) as $part ) {
						$text [] = $part;
					}
					$line [] = array( 
						'text' => $text, 
						'feed' => 260,
						'align' => 'left'
					);
				}
				
				$x = 360;
				
				// draw prices
				if ($this->canShowPriceInfo ( $_item )) {
					// draw Total(ex)
					$text = $order->formatPriceTxt ( $_item->getRowTotal () );
					$line [] = array( 
						'text' => $text, 
						'feed' => $x, 
						'font' => 'bold', 
						'align' => 'right' 
					);
					
					$x = 400;
					
					// draw QTY
					$text = $_item->getQty () * 1;
					$line [] = array( 
						'text' => $_item->getQty () * 1, 
						'feed' => $x, 
						'font' => 'bold', 
						'align' => 'center' 
					);
					$x = 470;
					
					// draw Tax
					$text = $order->formatPriceTxt ( $_item->getTaxAmount () );
					$line [] = array( 
						'text' => $text, 
						'feed' => $x, 
						'font' => 'bold', 
						'align' => 'right' 
					);
					$x = 550;
					
					// draw Total(inc)
					$text = $order->formatPriceTxt ( $_item->getRowTotal () + $_item->getTaxAmount () - $_item->getDiscountAmount () );
					$line [] = array( 
						'text' => $text, 
						'feed' => $rightBound, 
						'font' => 'bold', 
						'align' => 'right' 
					);
				}
				
				$drawItems [$optionId] ['lines'] [] = $line;
			}
			
			// custom options
			$options = $item->getOrderItem ()->getProductOptions ();
					if ($options) {
				if (isset ( $options ['options'] )) {
					foreach ( $options ['options'] as $option ) {
						$lines = array();
						$lines [] [] = array( 
							'text' => Mage::helper ( 'core/string' )->str_split ( strip_tags ( $option ['label'] ), 40, true, true ), 
							'font' => 'italic', 
							'feed' => $leftBound 
						);
						
						if ($option ['value']) {
							$text = array();
							$_printValue = isset ( $option ['print_value'] ) ? $option ['print_value'] : strip_tags ( $option ['value'] );
							$values = explode ( ', ', $_printValue );
							foreach ( $values as $value ) {
								foreach ( Mage::helper ( 'core/string' )->str_split ( $value, 40, true, true ) as $_value ) {
									$text [] = $_value;
								}
							}
							
							$lines [] [] = array( 
								'text' => $text, 
								'feed' => $leftBound + 5 
							);
						}
						
						$drawItems [] = array( 
							'lines' => $lines, 
							'height' => 10 
						);
					}
				}
			}
		} else {
			$leftBound = 40;
			$rightBound = 550;
			
			$_prevOptionId = '';
			$drawItems = array();
			
			foreach ( $items as $_item ) {
				$line = array();
				
				$attributes = $this->getSelectionAttributes ( $_item );
				if (is_array ( $attributes )) {
					$optionId = $attributes ['option_id'];
				} else {
					$optionId = 0;
				}
				
				if (! isset ( $drawItems [$optionId] )) {
					$drawItems [$optionId] = array( 
						'lines' => array(), 
						'height' => 10 
					);
				}
				
				if ($_item->getOrderItem ()->getParentItem ()) {
					if ($_prevOptionId != $attributes ['option_id']) {
						$line [0] = array( 
							'font' => 'italic', 
							'text' => Mage::helper ( 'core/string' )->str_split ( $attributes ['option_label'], 40, true, true ), 
							'feed' => 40 
						);
						
						$drawItems [$optionId] = array( 
							'lines' => array( 
								$line 
							), 
							'height' => 10 
						);
						
						$line = array();
						
						$_prevOptionId = $attributes ['option_id'];
					}
				}
				
				/* in case Product name is longer than 80 chars - it is written in a few lines */
				if ($_item->getOrderItem ()->getParentItem ()) {
					$feed = 40;
					$name = $this->getValueHtml ( $_item );
				} else {
					$feed = 40;
					$name = $_item->getName ();
				}
				$line [] = array( 
					'text' => Mage::helper ( 'core/string' )->str_split ( $name, 40, true, true ), 
					'feed' => $feed 
				);
				
				// draw SKUs
				if (! $_item->getOrderItem ()->getParentItem ()) {
					$text = array();
					
					if (strlen ( $item->getSku () ) > 15) {
						$sku = substr ( $item->getSku (), 0, 15 ) . "...";
					} else {
						$sku = $item->getSku ();
					}
					
					foreach ( Mage::helper ( 'core/string' )->str_split ( $sku, 18 ) as $part ) {
						$text [] = $part;
					}
					$line [] = array( 
						'text' => $text, 
						'feed' => 260,
						'align' => 'left',
					);
				}
				
				$x = 410;
				
				// draw prices
				if ($this->canShowPriceInfo ( $_item )) {
					// draw Total(ex)
					$text = $order->formatPriceTxt ( $_item->getRowTotal () );
					$line [] = array( 
						'text' => $text, 
						'feed' => $x, 
						'font' => 'bold', 
						'align' => 'right' 
					);
					
					$x = 450;
					
					// draw QTY
					$text = $_item->getQty () * 1;
					$line [] = array( 
						'text' => $_item->getQty () * 1, 
						'feed' => $x, 
						'font' => 'bold', 
						'align' => 'center' 
					);
					$x = 500;
					
					// draw Total(inc)
					$text = $order->formatPriceTxt ( $_item->getRowTotal () + $_item->getTaxAmount () - $_item->getDiscountAmount () );
					$line [] = array( 
						'text' => $text, 
						'feed' => $rightBound, 
						'font' => 'bold', 
						'align' => 'right' 
					);
				}
				
				$drawItems [$optionId] ['lines'] [] = $line;
			}
			
			// custom options
			$options = $item->getOrderItem ()->getProductOptions ();
					if ($options) {
				if (isset ( $options ['options'] )) {
					foreach ( $options ['options'] as $option ) {
						$lines = array();
						$lines [] [] = array( 
							'text' => Mage::helper ( 'core/string' )->str_split ( strip_tags ( $option ['label'] ), 40, true, true ), 
							'font' => 'italic', 
							'feed' => $leftBound 
						);
						
						if ($option ['value']) {
							$text = array();
							$_printValue = isset ( $option ['print_value'] ) ? $option ['print_value'] : strip_tags ( $option ['value'] );
							$values = explode ( ', ', $_printValue );
							foreach ( $values as $value ) {
								foreach ( Mage::helper ( 'core/string' )->str_split ( $value, 40, true, true ) as $_value ) {
									$text [] = $_value;
								}
							}
							
							$lines [] [] = array( 
								'text' => $text, 
								'feed' => $leftBound + 5 
							);
						}
						
						$drawItems [] = array( 
							'lines' => $lines, 
							'height' => 10 
						);
					}
				}
			}
		}
		
		$page = $pdf->drawLineBlocks ( $page, $drawItems, array( 
			'table_header' => true 
		) );
		
		$this->setPage ( $page );
	}
}
