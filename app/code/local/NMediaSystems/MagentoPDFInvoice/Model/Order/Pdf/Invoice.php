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

require_once("NMediaSystems/MagentoPDFInvoice/Model/My_Pdf.php");

/**
 * Sales Order Invoice PDF model
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class NMediaSystems_MagentoPDFInvoice_Model_Order_Pdf_Invoice extends NMediaSystems_MagentoPDFInvoice_Model_Order_Pdf_Abstract 
{

	public function getPdf($invoices = array()) {
		
		$this->_beforeGetPdf ();
		$this->_initRenderer ( 'invoice' );
		
		//$pdf = new Zend_Pdf ();
        $pdf = new My_Pdf();
		$this->_setPdf ( $pdf );
		$style = new Zend_Pdf_Style ();
		$this->_setFontBold ( $style, 10 );
		
		foreach ( $invoices as $invoice ) {
			if ($invoice->getStoreId ()) {
				Mage::app ()->getLocale ()->emulate ( $invoice->getStoreId () );
			}

			// fetch data of payment via
			$order_id = $invoice->getOrderId();
			$order = $invoice->getOrder();

			
			$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
			// $sql = "SELECT `method`,fop.entity_id,main_table.increment_id,its.model,pm_code FROM `mage_sales_flat_order_payment` as `fop`
			// LEFT JOIN `mage_sales_flat_invoice` as `main_table` ON main_table.entity_id = fop.parent_id
			// JOIN mage_icepay_transactions its ON its.order_id = main_table.increment_id
			// JOIN mage_icepay_issuerdata iid ON its.model = iid.magento_code
			// WHERE fop.parent_id = ".$order_id." group by fop.parent_id";

			$sql = "SELECT `method`,fop.entity_id,fo.increment_id,iit.model,pm_code FROM `mage_sales_flat_order_payment` as fop
			join `mage_sales_flat_order` as fo ON fo.entity_id = fop.parent_id
			join `mage_icepay_transactions` as iit ON fo.increment_id = iit.order_id
			join `mage_icepay_issuerdata` as iid ON iit.model = iid.magento_code
			where `parent_id`=".$order_id." group by fop.parent_id";


			$paymentmethod = $connection->fetchAll($sql);
			
			$paidVia = '';
			
			if(!isset($paymentmethod[0]['pm_code'])){
				$paidVia = "NA";
			} else {
				$paidVia = $paymentmethod[0]['pm_code'];
			}

			$paymentCode = strtolower($order->getPayment()->getMethodInstance()->getCode());

			if($paymentCode == 'effectconnect_payment'){
				$paidVia = Mage::helper ( 'sales' )->__( 'Already paid by you' );
			}
			
			$page = $pdf->newPage ( Zend_Pdf_Page::SIZE_A4 );
			$pdf->pages [] = $page;
			
			//$page->setLineColor(new Zend_Pdf_Color_GrayScale(0)); //black
			//$page->setLineWidth(5);
			//$page->drawLine(10, 820, 580, 820); 
			$page->setFillColor(new Zend_Pdf_Color_GrayScale(0))
      			 ->setLineColor(new Zend_Pdf_Color_GrayScale(0))
      			 ->setLineDashingPattern(Zend_Pdf_Page::LINE_DASHING_SOLID)
      			 ->drawRectangle(22, 826, 573, 820); 

			// Add image
			$this->insertLogo ( $page, $invoice->getStore () );
			
			$page->setLineColor(new Zend_Pdf_Color_GrayScale(0.92)); //black
			$page->setLineWidth(1);
			//left,bottom,right,top
			$page->drawLine(22, 764, 573, 765); 

			//Add Contact info
			$this->insertContactInfo ( $page, $order, Mage::getStoreConfigFlag ( self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID, $order->getStoreId () ) );
			// Add customer info
			$this->insertCustomerInfo ( $page, $order, Mage::getStoreConfigFlag ( self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID, $order->getStoreId () ) );
			
			// Add address
			//$this->insertAddress ( $page, $invoice->getStore () );
			
			// Add head 
			$this->insertOrder ( $page, $order, Mage::getStoreConfigFlag ( self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID, $order->getStoreId () ) );
			
			$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 0 ) );
			
			$pdffont = Zend_Pdf_Font::fontWithPath('media/pdf/Source-Sans_Pro/SourceSansPro-Light.ttf');
			$page->setFont($pdffont, 9);

			$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Factuurnummer: ' ), 35, 651.8, 'UTF-8' );

			$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 0 ) ); //changed 0.4 to 0
			$page->drawText ( $invoice->getIncrementId () , 154, 651.8, 'UTF-8' );
			 
			// Add table
			//$page->setFillColor ( new Zend_Pdf_Color_RGB ( 0.93, 0.92, 0.92 ) );
			$page->setLineColor(new Zend_Pdf_Color_RGB ( 217, 217, 217 ));
			$page->setLineWidth ( 0.5 );
			
			//$page->drawRectangle ( 35, $this->y, 560, $this->y - 25 );
			$this->y -= 15;
			
			// Add table head
			$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 0.87 ) );
			$this->_setFontBold ( $page, 10 );
			
			$pdffont = Zend_Pdf_Font::fontWithPath('media/pdf/Source-Sans_Pro/SourceSansPro-Light.ttf');
			$page->setFont($pdffont, 9);
			$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 0 ) ); //changed 0.5 to 0
				
			if (Mage::getStoreConfig ( 'sales/identity/displaytax', $order->getStoreId () )) {
				$page->drawText ( Mage::helper ( 'sales' )->__ ( ' Product ' ), 35, $this->y, 'UTF-8' );
				$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Art.nr' ), 260, $this->y, 'UTF-8' );
				$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Price' ), 330, $this->y, 'UTF-8' );
				$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Eenheid' ), 380, $this->y, 'UTF-8' );
				//$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Tax' ), 445, $this->y, 'UTF-8' );
				$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Subtotal' ), 511, $this->y, 'UTF-8' );
			} else {
				$page->drawText ( Mage::helper ( 'sales' )->__ ( ' Product ' ), 35, $this->y, 'UTF-8' );
				$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Art.nr' ), 342, $this->y, 'UTF-8' );
				$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Price' ), 400, $this->y, 'UTF-8' );
				$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Stuks' ), 434, $this->y, 'UTF-8' );
				$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Eenheid' ), 471, $this->y, 'UTF-8' );
				$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Subtotal' ), 528, $this->y, 'UTF-8' );
			}
			
			$this->y -= 25;
			
			$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 0 ) );
			
			// Add body
			foreach ( $invoice->getAllItems () as $item ) {
				
				if ($item->getOrderItem ()->getParentItem ()) {
					continue;
				}
				if ($this->y < 170) {
					$page = $this->newPage ( array( 
						'table_header' => true,
                        'store'        => $order->getStoreId()
					) );
				}
				// Draw item
				$page = $this->_drawItem ( $item, $page, $order );
			}
			$this->y -= 10;

			// payment by add 
			$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Paid by' )." : ".ucfirst(strtolower($paidVia)), 35, 250, 'UTF-8' );

			$page->setLineColor(new Zend_Pdf_Color_GrayScale(0.8)); //gray
			$page->setLineWidth(1);
			//$page->drawLine(22, 100, 573, 100); 

            // Add comments
            $page = $this->insertComments ( $page, $invoice );

            $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.8)); //gray
			$page->setLineWidth(1.4);

			$page->drawLine(20, 266, 573, 266);

			$page->drawLine(20, 167, 573, 167);

			$page->drawLine(20, 116, 573, 116);

			$page->drawLine(20, 80, 573, 80);

            $this->y=200;
			$this->y -= 15;  
	            
			//$image = Mage::getBaseDir('media') . '/pdf/thankyou.png';
			//$image = Zend_Pdf_Image::imageWithPath ( $image );
			//26 height
			//$page->drawImage ( $image, 436, 129, 557, 155.1);  

			$page->setFont(Zend_Pdf_Font::fontWithPath('media/pdf/Oswald/Oswald-Regular.ttf'), 9.8);
			$page->drawText ( Mage::helper ( 'sales' )->__ ( 'NAMENS HET TEAM VAN GYZS, ' ), 442.2, 146.1, 'UTF-8' );
			$page->drawText ( Mage::helper ( 'sales' )->__ ( 'BEDANKT VOOR JE BESTELLING.' ), 437, 129.1, 'UTF-8' );
			


			// Add totals
			$page = $this->insertTotals ( $page, $invoice );

			//$page->drawLine(22, 198, 573, 198);

			$page = $this->insertStoreInfo ( $page, $invoice, $invoice->getStoreId () );
			
			if ($invoice->getStoreId ()) {
				Mage::app ()->getLocale ()->revert ();
			}
		
		}
		
		$this->_afterGetPdf ();
		
		return $pdf;
	}
	
	/**
	 * Create new page and assign to PDF object
	 *
	 * @param array $settings
	 * @return Zend_Pdf_Page
	 */
	public function newPage(array $settings = array()) 
	{
		/* Add new table head */
		$page = $this->_getPdf ()->newPage ( Zend_Pdf_Page::SIZE_A4 );
		$this->_getPdf ()->pages [] = $page;
		$this->y = 800;
		
		if (! empty ( $settings ['table_header'] )) {
			$this->_setFontRegular ( $page );
			// $page->setFillColor ( new Zend_Pdf_Color_RGB ( 0.93, 0.92, 0.92 ) );
			// $page->setLineColor ( new Zend_Pdf_Color_GrayScale ( 0.5 ) );
			// $page->setLineWidth ( 0.5 );
			// $page->drawRectangle ( 35, $this->y, 560, $this->y - 25 );
			$this->y -= 15;
			
			$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 0 ) );
			$this->_setFontBold ( $page, 12 );
			
			$pdffont = Zend_Pdf_Font::fontWithPath('media/pdf/Source-Sans_Pro/SourceSansPro-Light.ttf');
			$page->setFont($pdffont, 9);
			$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 0 ) ); //changed 0.5 to 0
			
			if (Mage::getStoreConfig ( 'sales/identity/displaytax', $settings['store'] )){
				$page->drawText ( Mage::helper ( 'sales' )->__ ( ' Product ' ), 35, $this->y, 'UTF-8' );
				$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Art.nr' ), 260, $this->y, 'UTF-8' );
				$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Price' ), 330, $this->y, 'UTF-8' );
				$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Eenheid' ), 380, $this->y, 'UTF-8' );
				//$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Tax' ), 445, $this->y, 'UTF-8' );
				$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Subtotal' ), 511, $this->y, 'UTF-8' );
			} else {
				$page->drawText ( Mage::helper ( 'sales' )->__ ( ' Product ' ), 35, $this->y, 'UTF-8' );
				$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Art.nr' ), 342, $this->y, 'UTF-8' );
				$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Price' ), 400, $this->y, 'UTF-8' );
				$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Stuks' ), 434, $this->y, 'UTF-8' );
				$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Eenheid' ), 471, $this->y, 'UTF-8' );
				$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Subtotal' ), 528, $this->y, 'UTF-8' );
			}
			// if (Mage::getStoreConfig ( 'sales/identity/displaytax', $settings['store'] )) {
			// 	$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Product' ), 35, $this->y, 'UTF-8' );
			// 	$page->drawText ( Mage::helper ( 'sales' )->__ ( 'SKU' ), 260, $this->y, 'UTF-8' );
			// 	$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Price' ), 330, $this->y, 'UTF-8' );
			// 	$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Qty' ), 400, $this->y, 'UTF-8' );
			// 	$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Tax' ), 445, $this->y, 'UTF-8' );
			// 	$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Subtotal' ), 502, $this->y, 'UTF-8' );
			// } else {
			// 	$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Product' ), 35, $this->y, 'UTF-8' );
			// 	$page->drawText ( Mage::helper ( 'sales' )->__ ( 'SKU' ), 260, $this->y, 'UTF-8' );
			// 	$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Price' ), 380, $this->y, 'UTF-8' );
			// 	$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Qty' ), 450, $this->y, 'UTF-8' );
			// 	$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Subtotal' ), 502, $this->y, 'UTF-8' );
			// }
			
			$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 0 ) );
			$this->y -= 25;
		}
		
		return $page;
	}
}
	