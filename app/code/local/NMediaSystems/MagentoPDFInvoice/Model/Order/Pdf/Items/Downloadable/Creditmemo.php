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
 * @package     Mage_Downloadable
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Order Creditmemo Downloadable Pdf Items renderer
 *
 * @category   Mage
 * @package    Mage_Downloadable
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class NMediaSystems_MagentoPDFInvoice_Model_Order_Pdf_Items_Downloadable_Creditmemo extends NMediaSystems_MagentoPDFInvoice_Model_Order_Pdf_Items_Downloadable_Abstract {
	/**
	 * Draw item line
	 *
	 */
	public function draw() {
		$order = $this->getOrder ();
		$item = $this->getItem ();
		$pdf = $this->getPdf ();
		$page = $this->getPage ();
		$lines = array ();
		
		$leftBound = 40;
		$rightBound = 550;
		
		$x = $leftBound;
		// draw Product name
		$lines [0] = array (array ('text' => Mage::helper ( 'core/string' )->str_split ( $item->getName (), 40, true, true ), 'feed' => $x ) );
		
		$x = 260;
		// draw SKU
		

		$text = array ();
		if (strlen ( $item->getSku () ) > 7) {
			$sku = substr ( $item->getSku (), 0, 7 ) . "...";
		} else {
			$sku = $item->getSku ();
		}
		
		$lines [0] [] = array ('text' => Mage::helper ( 'core/string' )->str_split ( $sku, 10 ), 'feed' => $x );
		
		$x = 360;
		// draw Total (ex)
		$lines [0] [] = array ('text' => $order->formatPriceTxt ( $item->getRowTotal () ), 'feed' => $x, 'font' => 'bold', 'align' => 'right' );
		
		$x = 400;
		// draw QTY
		$lines [0] [] = array ('text' => $item->getQty () * 1, 'feed' => $x, 'font' => 'bold', 'align' => 'center' );
		
		$x = 470;
		// draw Tax
		$lines [0] [] = array ('text' => $order->formatPriceTxt ( $item->getTaxAmount () ), 'feed' => $x, 'font' => 'bold', 'align' => 'right' );
		
		$x = 550;
		// draw Subtotal
		$lines [0] [] = array ('text' => $order->formatPriceTxt ( $item->getRowTotal () + $item->getTaxAmount () - $item->getDiscountAmount () ), 'feed' => $rightBound, 'font' => 'bold', 'align' => 'right' );
		
		// draw options
		$options = $this->getItemOptions ();
		if ($options) {
			foreach ( $options as $option ) {
				// draw options label
				$lines [] [] = array ('text' => Mage::helper ( 'core/string' )->str_split ( strip_tags ( $option ['label'] ), 40, true, true ), 'font' => 'italic', 'feed' => $leftBound );
				
				// draw options value
				$_printValue = isset ( $option ['print_value'] ) ? $option ['print_value'] : strip_tags ( $option ['value'] );
				$lines [] [] = array ('text' => Mage::helper ( 'core/string' )->str_split ( $_printValue, 40, true, true ), 'feed' => $leftBound + 5 );
			}
		}
		
		// downloadable Items
		$_purchasedItems = $this->getLinks ()->getPurchasedItems ();
		
		// draw Links title
		$lines [] [] = array ('text' => Mage::helper ( 'core/string' )->str_split ( $this->getLinksTitle (), 40, true, true ), 'font' => 'italic', 'feed' => 40 );
		
		// draw Links
		foreach ( $_purchasedItems as $_link ) {
			$lines [] [] = array ('text' => Mage::helper ( 'core/string' )->str_split ( $_link->getLinkTitle (), 40, true, true ), 'feed' => 40 );
		}
		
		$lineBlock = array ('lines' => $lines, 'height' => 10 );
		
		$page = $pdf->drawLineBlocks ( $page, array ($lineBlock ), array ('table_header' => true ) );
		$this->setPage ( $page );
	}
}
