<?php
/**
 * Magento
 *
 * NOTICE OF LICENSEfo
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
 * Sales PDF abstract model
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */

abstract class NMediaSystems_MagentoPDFInvoice_Model_Order_Pdf_Abstract extends Mage_Sales_Model_Order_Pdf_Abstract {
	public $y;
	/**
	 * Item renderers with render type key
	 *
	 * model    => the model name
	 * renderer => the renderer model
	 *
	 * @var array
	 */
	protected $_renderers = array ();
	
	const XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID = 'sales_pdf/invoice/put_order_id';
	const XML_PATH_SALES_PDF_SHIPMENT_PUT_ORDER_ID = 'sales_pdf/shipment/put_order_id';
	const XML_PATH_SALES_PDF_CREDITMEMO_PUT_ORDER_ID = 'sales_pdf/creditmemo/put_order_id';
	
	/**
	 * Zend PDF object
	 *
	 * @var Zend_Pdf
	 */
	protected $_pdf;
	
	protected $_defaultTotalModel = 'NMediaSystems_MagentoPDFInvoice_Model_Order_Pdf_Total_Default';
	//protected $_defaultTotalModel = 'Mage_Sales_Model_Order_Pdf_Total_Default';
	

	/**
	 * Retrieve PDF
	 *
	 * @return Zend_Pdf
	 */
	public function getPdf() {
	}
	
	/**
	 * Returns the total width in points of the string using the specified font and
	 * size.
	 *
	 * This is not the most efficient way to perform this calculation. I'm
	 * concentrating optimization efforts on the upcoming layout manager class.
	 * Similar calculations exist inside the layout manager class, but widths are
	 * generally calculated only after determining line fragments.
	 *
	 * @param string $string
	 * @param Zend_Pdf_Resource_Font $font
	 * @param float $fontSize Font size in points
	 * @return float
	 */
	public function widthForStringUsingFontSize($string, $font, $fontSize) {
		$drawingString = '"libiconv"' == ICONV_IMPL ? iconv ( 'UTF-8', 'UTF-16BE//IGNORE', $string ) : @iconv ( 'UTF-8', 'UTF-16BE', $string );
		
		$characters = array ();
		for($i = 0; $i < strlen ( $drawingString ); $i ++) {
			$characters [] = (ord ( $drawingString [$i ++] ) << 8) | ord ( $drawingString [$i] );
		}
		$glyphs = $font->glyphNumbersForCharacters ( $characters );
		$widths = $font->widthsForGlyphs ( $glyphs );
		$stringWidth = (array_sum ( $widths ) / $font->getUnitsPerEm ()) * $fontSize;
		return $stringWidth;	
	}
	
	/**
	 * Calculate coordinates to draw something in a column aligned to the right
	 *
	 * @param string $string
	 * @param int $x
	 * @param int $columnWidth
	 * @param Zend_Pdf_Resource_Font $font
	 * @param int $fontSize
	 * @param int $padding
	 * @return int
	 */
	public function getAlignRight($string, $x, $columnWidth, Zend_Pdf_Resource_Font $font, $fontSize, $padding = 5) {
		$width = $this->widthForStringUsingFontSize ( $string, $font, $fontSize );
		return $x + $columnWidth - $width - $padding;
	}
	
	/**
	 * Calculate coordinates to draw something in a column aligned to the center
	 *
	 * @param string $string
	 * @param int $x
	 * @param int $columnWidth
	 * @param Zend_Pdf_Resource_Font $font
	 * @param int $fontSize
	 * @return int
	 */
	public function getAlignCenter($string, $x, $columnWidth, Zend_Pdf_Resource_Font $font, $fontSize) {
		$width = $this->widthForStringUsingFontSize ( $string, $font, $fontSize );
		return $x + round ( ($columnWidth - $width) / 2 );
	}
	
	protected function insertLogo(&$page, $store = null) {
			$image = Mage::getStoreConfig ( 'sales/identity/logo', $store );
			if ($image) {
				$image = Mage::getBaseDir('media') . '/sales/store/logo/' . $image;
				if (is_file ( $image )) {
					$image = Zend_Pdf_Image::imageWithPath ( $image );
					//$page->drawImage ( $image, 35, 720, 35 + 124, 720 + 90 );
					//$page->drawImage ( $image, 35, 730, 35 + 200, 730 + 50 0;
					//$page->drawImage ( $image, 35, 760, 25 + 150, 760 + 40 );
											//left,bottom,right,top
					$page->drawImage ( $image, 32, 773, 157, 813.5 );
				}
			}
		//return $page;
	}
	
	protected function insertAddress(&$page, $store = null) {
		$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 0 ) );
		$this->_setFontRegular ( $page, 10 );
		
		$page->setLineWidth ( 0 ); //changed 0.5 to 0
		//$page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
		

		$page->setLineWidth ( 0 );
		$this->y = 750;
		foreach ( explode ( "\n", Mage::getStoreConfig ( 'sales/identity/address', $store ) ) as $value ) {
			if ($value !== '') {
				$page->drawText ( trim ( strip_tags ( $value ) ), 35, $this->y, 'UTF-8' );
				$this->y -= 14;
			}
		}
	
	}
	
	protected function insertCustomerInfo(&$page, $order, $putOrderId = true) {
		//$page->setLineWidth(0.5);
		//$page->setFillColor(new Zend_Pdf_Color_GrayScale(0.5));
		//$page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
		//$page->drawRectangle(300, 640, 560, 810);
		

		$this->_setFontRegular ( $page, 12 );
		$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 0 ) ); // changed 0.5 to 0

        // if there is no shipping address for this order, virtual product or downloadable, etc
        // billing address is used instead
        $address = is_object ( $order->getShippingAddress () ) ? $order->getShippingAddress () : $order->getBillingAddress ();
		
        
        $shippingAddressBlock = $address->getName () . "|";
        //$shippingAddressBlock = ltrim($address->getCompany () . "|", "|");
        $shippingAddressBlock .= $address->getStreet1 () . " ";
        $shippingAddressBlock .= $address->getStreet2 () . " ";

        $shippingAddressBlock .= $address->getStreet3 () . "|";
        $shippingAddressBlock .= $address->getStreet4 () . "|";

        $shippingAddressBlock .= $address->getPostcode () . "  ";

        $shippingAddressBlock .= $address->getCity ();

        if (strlen ( $address->getRegion () ) > 0 && $address->getRegion () != '-') {
            $shippingAddressBlock .= ", " . $address->getRegion () . "|";
        } else {
            $shippingAddressBlock .= "|";
        }

        $shippingAddressBlock .= Mage::getModel ( 'directory/country' )->load ( $address->getCountry () )->getName ();

		$shippingAddress = $this->_formatAddress ( $shippingAddressBlock );

        if (trim($address->getTelephone(),'-') != '' || trim($address->getFax(), '-') != '') {
            $shippingAddress[] = ' ';
            if (trim($address->getTelephone(), '-') != '') {
                $shippingAddress[] = 'T: ' . $address->getTelephone();
            }
            if (trim($address->getFax(), '-') != '') {
                $shippingAddress[] = 'M: ' . $address->getFax();
            }
        }

		$this->y = 638;//742; 
		$i = 0;
		foreach ( $shippingAddress as $value ) {
			/*if ($i == 0) {
				$this->_setFontBold ( $page, 16 );
			} else */{
			$pdffont = Zend_Pdf_Font::fontWithPath('media/pdf/Source-Sans_Pro/SourceSansPro-Light.ttf');
			$page->setFont($pdffont, 9);
			$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 0 ) ); // changed 0.5 to 0
			}
			if ($value !== '') {
				//shipping address
				$page->drawText ( strip_tags ( ltrim ( $value ) ), 373, $this->y, 'UTF-8' );
				$this->y -= 13;
			}
			$i ++;
		}
		

		$image = Mage::getBaseDir('media') . '/pdf/invoice.png';
				if (is_file ( $image )) {
					$image = Zend_Pdf_Image::imageWithPath ( $image );
					//$page->drawImage ( $image, 35, 720, 35 + 124, 720 + 90 );
					//$page->drawImage ( $image, 35, 730, 35 + 200, 730 + 50 0;
					//$page->drawImage ( $image, 35, 760, 25 + 150, 760 + 40 );
					//left,bottom,right,top
					$page->drawImage ( $image, 400, 696, 527, 721);  
				}
	//$this->y = 700;
	

	/*
		 $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
		 $this->_setFontRegular($page, 5);

		 $page->setLineWidth(0.5);
		 $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));

		 $page->setLineWidth(0);
		 foreach (explode("\n", Mage::getStoreConfig('sales/identity/address', $store)) as $value){
		 if ($value!=='') {
		 $page->drawText(trim(strip_tags($value)), 330, $this->y, 'UTF-8');
		 $this->y -=7;
		 }
		 }
		 //return $page;
		 */
	}
    protected function insertCreditmemoCustomerInfo(&$page, $order, $putOrderId = true) {

        $this->_setFontRegular ( $page, 12 );
        $page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 0 ) ); // changed 0.5 to 0

        // if there is no shipping address for this order, virtual product or downloadable, etc
        // billing address is used instead
        $address = is_object ( $order->getShippingAddress () ) ? $order->getShippingAddress () : $order->getBillingAddress ();


        $shippingAddressBlock = $address->getName () . "|";
        //$shippingAddressBlock = ltrim($address->getCompany () . "|", "|");
        $shippingAddressBlock .= $address->getStreet1 () . " ";
        $shippingAddressBlock .= $address->getStreet2 () . " ";

        $shippingAddressBlock .= $address->getStreet3 () . "|";
        $shippingAddressBlock .= $address->getStreet4 () . "|";

        $shippingAddressBlock .= $address->getPostcode () . "  ";

        $shippingAddressBlock .= $address->getCity ();

        if (strlen ( $address->getRegion () ) > 0 && $address->getRegion () != '-') {
            $shippingAddressBlock .= ", " . $address->getRegion () . "|";
        } else {
            $shippingAddressBlock .= "|";
        }

        $shippingAddressBlock .= Mage::getModel ( 'directory/country' )->load ( $address->getCountry () )->getName ();

        $shippingAddress = $this->_formatAddress ( $shippingAddressBlock );

        if (trim($address->getTelephone(),'-') != '' || trim($address->getFax(), '-') != '') {
            $shippingAddress[] = ' ';
            if (trim($address->getTelephone(), '-') != '') {
                $shippingAddress[] = 'T: ' . $address->getTelephone();
            }
            if (trim($address->getFax(), '-') != '') {
                $shippingAddress[] = 'M: ' . $address->getFax();
            }
        }

        $this->y = 638;//742;
        $i = 0;
        foreach ( $shippingAddress as $value ) {
            /*if ($i == 0) {
                $this->_setFontBold ( $page, 16 );
            } else */{
                $pdffont = Zend_Pdf_Font::fontWithPath('media/pdf/Source-Sans_Pro/SourceSansPro-Light.ttf');
                $page->setFont($pdffont, 9);
                $page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 0 ) ); // changed 0.5 to 0
            }
            if ($value !== '') {
                //shipping address
                $page->drawText ( strip_tags ( ltrim ( $value ) ), 373, $this->y, 'UTF-8' );
                $this->y -= 13;
            }
            $i ++;
        }


        $image = Mage::getBaseDir('media') . '/pdf/creditfactuur.jpg';
        if (is_file ( $image )) {
            $image = Zend_Pdf_Image::imageWithPath ( $image );
            $page->drawImage ( $image, 400, 696, 527, 721);
        }

    }
	protected function insertShipmentCustomerInfo(&$page, $order, $putOrderId = true) {

        $this->_setFontRegular ( $page, 12 );
        $page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 0 ) ); // changed 0.5 to 0

        // if there is no shipping address for this order, virtual product or downloadable, etc
        // billing address is used instead
        $address = is_object ( $order->getShippingAddress () ) ? $order->getShippingAddress () : $order->getBillingAddress ();


        $shippingAddressBlock = $address->getName () . "|";
        //$shippingAddressBlock = ltrim($address->getCompany () . "|", "|");
        $shippingAddressBlock .= $address->getStreet1 () . " ";
        $shippingAddressBlock .= $address->getStreet2 () . " ";

        $shippingAddressBlock .= $address->getStreet3 () . "|";
        $shippingAddressBlock .= $address->getStreet4 () . "|";

        $shippingAddressBlock .= $address->getPostcode () . "  ";

        $shippingAddressBlock .= $address->getCity ();

        if (strlen ( $address->getRegion () ) > 0 && $address->getRegion () != '-') {
            $shippingAddressBlock .= ", " . $address->getRegion () . "|";
        } else {
            $shippingAddressBlock .= "|";
        }

        $shippingAddressBlock .= Mage::getModel ( 'directory/country' )->load ( $address->getCountry () )->getName ();

        $shippingAddress = $this->_formatAddress ( $shippingAddressBlock );

        if (trim($address->getTelephone(),'-') != '' || trim($address->getFax(), '-') != '') {
            $shippingAddress[] = ' ';
            if (trim($address->getTelephone(), '-') != '') {
                $shippingAddress[] = 'T: ' . $address->getTelephone();
            }
            if (trim($address->getFax(), '-') != '') {
                $shippingAddress[] = 'M: ' . $address->getFax();
            }
        }

        $this->y = 638;//742;
        $i = 0;
        foreach ( $shippingAddress as $value ) {
            /*if ($i == 0) {
                $this->_setFontBold ( $page, 16 );
            } else */{
                $pdffont = Zend_Pdf_Font::fontWithPath('media/pdf/Source-Sans_Pro/SourceSansPro-Light.ttf');
                $page->setFont($pdffont, 9);
                $page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 0 ) ); // changed 0.5 to 0
            }
            if ($value !== '') {
                //shipping address
                $page->drawText ( strip_tags ( ltrim ( $value ) ), 373, $this->y, 'UTF-8' );
                $this->y -= 13;
            }
            $i ++;
        }


        $image = Mage::getBaseDir('media') . '/pdf/pakbon.png';
        if (is_file ( $image )) {
            $image = Zend_Pdf_Image::imageWithPath ( $image );
            $page->drawImage ( $image, 400, 696, 527, 721);
        }

    }
	protected function insertContactInfo(&$page, $order, $putOrderId = true) {
		
		$this->y = 795;
		$i = 0; 
		$this->_setFontRegular ( $page, 10 );
		$pdffont = Zend_Pdf_Font::fontWithPath('media/pdf/Source-Sans_Pro/SourceSansPro-Light.ttf');
		$page->setFont($pdffont, 9.5);
		$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 0 ) ); // changed 0.5 to 0
		

		$stringText=Mage::helper ( 'sales' )->__('Klantenservice');//.": ";//.Mage::getStoreConfig('general/store_information/phone');
		$page->drawText ($stringText, 398, $this->y, 'UTF-8' );

		$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 0 ) );
		$telephoneNumber=Mage::getStoreConfig('general/store_information/phone');
		$page->drawText ( $telephoneNumber, 467, $this->y, 'UTF-8' );

		$this->y = 795-14;
		$stringTextEmail=Mage::helper ( 'sales' )->__('Email');//.": ";//.Mage::getStoreConfig('trans_email/ident_custom1/email');
		$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 0 ) ); // changed 0.5 to 0
		$page->drawText ( $stringTextEmail, 398, $this->y, 'UTF-8' );
	 
		$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 0 ) );
		$email=Mage::getStoreConfig('trans_email/ident_custom1/email');
		$page->drawText ( $email, 467, $this->y, 'UTF-8' );		
		 
	
	}
	
	/**
	 * Format address
	 *
	 * @param string $address
	 * @return array
	 */
	protected function _formatAddress($address) {
		$return = array ();
		foreach ( explode ( '|', $address ) as $str ) {
			foreach ( Mage::helper ( 'core/string' )->str_split ( $str, 33, true, true ) as $part ) {
				if (empty ( $part )) {
					continue;
				}
				$return [] = $part;
			}
		}
		
		return $return;
	}
	
	/**
	 * Format address
	 *
	 * @param string $address
	 * @return array
	 */
	protected function _formatShippingMethod($shippingMethod) {
		$return = array ();
		foreach ( explode ( '|', $shippingMethod ) as $str ) {
			foreach ( Mage::helper ( 'core/string' )->str_split ( $str, 45, true, true ) as $part ) {
				if (empty ( $part )) {
					continue;
				}
				$return [] = $part;
			}
		}
		return $return;
	}
	
	protected function insertOrder(&$page, $order, $putOrderId = true) {
		
		$page->setFillColor ( new Zend_Pdf_Color_Rgb ( 0.93, 0.92, 0.92 ) );
		$page->setLineColor ( new Zend_Pdf_Color_GrayScale ( 0.81) );
		$page->setLineWidth ( 1.5 );
		//$page->drawRectangle ( 35, 600, 560, 575 );
		$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 1 ) );
		/* Calculate blocks info */
		
		/* Billing Address */

        $address = $order->getBillingAddress ();
		
		$billingAddressBlock = ltrim($address->getCompany () . "|", "|");
		$billingAddressBlock .= $address->getName () . "|";
		$billingAddressBlock .= $address->getStreet1 () . " ";
		$billingAddressBlock .= $address->getStreet2 () . " ";
		$billingAddressBlock .= $address->getStreet3 () . "|";
		$billingAddressBlock .= $address->getStreet4 () . "|";
		$billingAddressBlock .= $address->getPostcode ()  . ",  ";
		$billingAddressBlock .= $address->getCity (). ",  ";
		
		/*if (strlen ( $address->getRegion () ) > 0 && $address->getRegion () != '-') {
			$billingAddressBlock .= ", " . $address->getRegion () . "|";
		} else {
			$billingAddressBlock .= "|";
		}*/
		
		$billingAddressBlock .= Mage::getModel ( 'directory/country' )->load ( $address->getCountry () )->getName ();

		$billingAddress = $this->_formatAddress ( $billingAddressBlock );
		
		if($address->getVatId ()){
			$billingAddress[] = "BTW-nummer : " . $address->getVatId ();
		}
        /*if ($order->getCustomerTaxvat()) {
            $billingAddress[] = ' ';
            $billingAddress[] = Mage::helper ( 'sales' )->__ ( 'VAT Number: ' ) . $order->getCustomerTaxvat();
        }*/

        if (trim($address->getTelephone(),'-') != '' || trim($address->getFax(), '-') != '') {
            //$billingAddress[] = ' ';
            if (trim($address->getTelephone(), '-') != '') {
                $billingAddress[] = 'T: ' . $address->getTelephone();
            }
            if (trim($address->getFax(), '-') != '') {
                $billingAddress[] = 'M: ' . $address->getFax();
            }
        }

		/* Payment */
        
		//$paymentInfo = Mage::helper ( 'payment' )->getInfoBlock ( $order->getPayment () )->setIsSecureMode ( true )->toPdf ();


        // Start store emulation process
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($order->getStoreId());

        try {
            // Retrieve specified view block from appropriate design package (depends on emulated store)
            $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
                ->setIsSecureMode(true);
            $paymentBlock->getMethod()->setStore($order->getStoreId());
            $paymentInfo = $paymentBlock->toPdf();

            if (empty($paymentInfo)) {
                $paymentInfo = $paymentBlock->getMethod()->getTitle();
            }

        } catch (Exception $exception) {
            // Stop store emulation process
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $exception;
        }

        // Stop store emulation process
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
        
        
		$payment = explode ( '{{pdf_row_separator}}',strip_tags (  $paymentInfo ));
		foreach ( $payment as $key => $value ) {
			if (trim ( $value )  == '') {
				unset ( $payment [$key] );
			}
		}
		reset ( $payment );
		
		/* Shipping Address and Method */
		if (! $order->getIsVirtual ()) {

			/* Shipping Address */
			$shippingAddress = $this->_formatAddress ( $order->getShippingAddress ()->format ( 'pdf' ) );
			
			$shippingMethod = $order->getShippingDescription ();
		}
		
		$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 0 ) );
		$this->_setFontBold ( $page, 10 );
		/*if (get_class ( $this ) == "NMediaSystems_MagentoPDFInvoice_Model_Order_Pdf_Creditmemo") {
			$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Credimemo Details:' ), 40, 585, 'UTF-8' );
		} else if (get_class ( $this ) == "NMediaSystems_MagentoPDFInvoice_Model_Order_Pdf_Invoice") {
			$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Invoice Details:' ), 40, 585, 'UTF-8' );
		} else if (get_class ( $this ) == "NMediaSystems_MagentoPDFInvoice_Model_Order_Pdf_Shipment") {
			$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Shipment Details:' ), 40, 585, 'UTF-8' );
		}*/
		$pdffont = Zend_Pdf_Font::fontWithPath('media/pdf/Source-Sans_Pro/SourceSansPro-Light.ttf');
		$page->setFont($pdffont, 9);

		if (! $order->getIsVirtual ()) { 
			$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Bezorgadres:' ), 373, 651.8, 'UTF-8' );
		} else {
			$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Bezorgadres:' ), 373, 651.8, 'UTF-8' );
		}
		
		if (! $order->getIsVirtual ()) {
			$y = 730 - (max ( count ( $billingAddress ), count ( $shippingAddress ) ) * 10 + 5);
		} else {
			$y = 730 - (count ( $billingAddress ) * 10 + 5);
		}
		
		$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 1 ) );
		//$page->drawRectangle(35, 530, 550, $y);
		$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 0 ) );
		$this->_setFontRegular ( $page );
		
		$i = 0;
		$counter = 0;
		$linecount = count($billingAddress);
		$this->y = ($linecount>=5) ? 746 : 742;
		$yVal = ($linecount>=5) ? 12 : 15;
		$fontVal = ($linecount>=5) ? 8.5 : 9.5;
		$last_line = "";
		foreach ( $billingAddress as $value ) {
			if ($i == 0) {
				$this->_setFontBold ( $page );
			} else {
				$this->_setFontRegular ( $page );
			}
			if ($value !== '') {
				$pdffont = Zend_Pdf_Font::fontWithPath('media/pdf/Source-Sans_Pro/SourceSansPro-Light.ttf');
				$page->setFont($pdffont, $fontVal);
				$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 0 ) ); // changed 0.4 to 0 
				if($counter >= 5){
					$last_line .= strip_tags ( ltrim ( $value ) ) ." ";
				}else{
					//Billing Address
					$page->drawText ( strip_tags ( ltrim ( $value ) ), 25, $this->y, 'UTF-8' );

					$this->y -= $yVal;
					$last_y = $this->y;
				}
				$counter++;
			}
			$i ++;
		}
		$page->drawText ( strip_tags ( ltrim ($last_line) ), 25, $last_y , 'UTF-8' );

		$page->setLineColor(new Zend_Pdf_Color_GrayScale(0.81)); //black
		$page->setLineWidth(1.5);
		//left,bottom,right,top
		//Rectangle Top Line
		$page->drawLine(22, 674, 573, 674);
		//Rectangle Bottom Line
		$page->drawLine(22, 570, 573, 570);
		//Rectangle Left Line
		$page->drawLine(22, 674, 22, 570);
		//Rectangle Middle Line
		$page->drawLine(309, 674, 309, 570);
		//Rectangle Right Line
		$page->drawLine(573, 674, 573, 570);

		$pdffont = Zend_Pdf_Font::fontWithPath('media/pdf/Source-Sans_Pro/SourceSansPro-Light.ttf');
		$page->setFont($pdffont, 9);

		$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 0 ) );
		$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Ordernummer:' ), 35, 639, 'UTF-8' ); 
		
		$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 0 ) ); // changed 0.4 to 0
		$page->drawText ( $order->getRealOrderId (), 154, 639, 'UTF-8' ); 
		
		$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 0 ) );
		$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Order Date: ' ), 35, 624, 'UTF-8' );

		$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 0 ) ); // changed 0.4 to 0
		$page->drawText (date ( 'j-m-Y', strtotime ( $order->getCreatedAt () ) ), 154, 624, 'UTF-8' );

		$customerAddress=Mage::getModel('customer/address')->load($order->getBillingAddress()->getCustomerAddressId());
		$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 0 ) );
		$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Referentie:' ), 35, 611, 'UTF-8' );

		$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 0 ) ); // changed 0.4 to 0
		$page->drawText ($customerAddress->getReferentie(), 154, 611, 'UTF-8' );


		if (strlen ( Mage::getStoreConfig ( 'sales/identity/vatnumber' ) )) {
			$page->drawText ( Mage::helper ( 'sales' )->__ ( 'VAT Number: ' ) . Mage::getStoreConfig ( 'sales/identity/vatnumber' ), 40, 600, 'UTF-8' );
		}
		//$page->drawText(Mage::helper('sales')->__('Order Date: ') . Mage::helper('core')->formatDate($order->getCreatedAtStoreDate(), 'medium', false), 40, 590, 'UTF-8');
		
		$this->y = 475;
		
		//if (! $order->getIsVirtual ()) {
		

		/*
			 $this->y = 520;
			 foreach ($shippingAddress as $value){
			 if ($value!=='') {
			 $page->drawText(strip_tags(ltrim($value)), 290, $this->y, 'UTF-8');
			 $this->y -=12;
			 }

			 }

			 */
		
		/*$page->setFillColor ( new Zend_Pdf_Color_Rgb ( 0.93, 0.92, 0.92 ) );
		$page->setLineWidth ( 0.5 );
		$page->drawRectangle ( 35, $this->y, 560, $this->y - 25 );
		
		$this->y -= 15;
		$this->_setFontBold ( $page, 12 );
		$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 0 ) );
		$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Payment Method:' ), 40, $this->y, 'UTF-8' );
		$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Delivery Method:' ), 290, $this->y, 'UTF-8' );
		
		$this->y -= 10;
		$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 1 ) );
		
		$this->_setFontRegular ( $page );
		$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 0 ) );
		
		$paymentLeft = 40;
		$yPayments = $this->y - 15;
		//} else {
		//	$yPayments = 520;
		//	$paymentLeft = 285;
		//}
		

		$paymentMethod = implode ( "|", $payment );
		$paymentMethod = $this->_formatShippingMethod ( $paymentMethod );
		
		$i = 0;
		$extraBoxHeight = 0;
		foreach ( $paymentMethod as $value ) {
			if ($value !== '') {
				$page->drawText ( strip_tags ( ltrim ( $value ) ), $paymentLeft, $yPayments, 'UTF-8' );
				$yPayments -= 12;
				$extraBoxHeight += 12;
			}
			
			$i ++;
		}
		*/
		/*
		foreach ( $payment as $value ) {
			if (trim ( $value ) !== '') {
				//Mage::helper ( 'core/string' )->str_split ( $str, 50, true, true );
				$page->drawText ( strip_tags ( trim ( $value ) ), $paymentLeft, $yPayments, 'UTF-8' );
				$yPayments -= 12;
			}
		}
		*/
		/*
		//if (! $order->getIsVirtual ()) {
		$this->y -= 15;
		
		// is this online delivery?
		if (! isset ( $shippingMethod )) {
			$shippingMethod = Mage::helper ( 'sales' )->__ ( 'Online Delivery' );
		}
		
		$shippingMethod = $this->_formatShippingMethod ( $shippingMethod );
		//$this->y = 760;
		$i = 0;
		$extraBoxHeight = 0;
		foreach ( $shippingMethod as $value ) {
			if ($value !== '') {
				$page->drawText ( strip_tags ( ltrim ( $value ) ), 290, $this->y, 'UTF-8' );
				$this->y -= 12;
				$extraBoxHeight += 12;
			}
			
			$i ++;
		}
		
		//$page->drawText($shippingMethod, 290, $this->y, 'UTF-8');
		

		$yShipments = $this->y;
		
		//shipping costs hier niet laten zien, #11039
		//$totalShippingChargesText = "(" . Mage::helper ( 'sales' )->__ ( 'Total delivery cost' ) . " " . $order->formatPriceTxt ( $order->getShippingAmount () ) . ")";
		//$page->drawText ( $totalShippingChargesText, 290, $yShipments - 12, 'UTF-8' );
		$yShipments -= 25;
		$tracks = $order->getTracksCollection ();
		if (count ( $tracks )) {
			$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 1 ) );
			$page->setLineWidth ( 0.5 );
			$page->drawRectangle ( 290, $yShipments, 560, $yShipments - 10 );
			$page->drawLine ( 380, $yShipments, 380, $yShipments - 10 );
			//$page->drawLine(510, $yShipments, 510, $yShipments - 10);
			

			$this->_setFontRegular ( $page, 8 );
			$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 0 ) );
			//$page->drawText(Mage::helper('sales')->__('Carrier'), 290, $yShipments - 7 , 'UTF-8');
			$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Title' ), 295, $yShipments - 7, 'UTF-8' );
			$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Number' ), 385, $yShipments - 7, 'UTF-8' );
			
			$yShipments -= 17;
			$this->_setFontRegular ( $page, 6 );
			foreach ( $order->getTracksCollection () as $track ) {
				
				$CarrierCode = $track->getCarrierCode ();
				if ($CarrierCode != 'custom') {
					$carrier = Mage::getSingleton ( 'shipping/config' )->getCarrierInstance ( $CarrierCode );
					$carrierTitle = $carrier->getConfigData ( 'title' );
				} else {
					$carrierTitle = Mage::helper ( 'sales' )->__ ( 'Custom Value' );
				}
				
				//$truncatedCarrierTitle = substr($carrierTitle, 0, 35) . (strlen($carrierTitle) > 35 ? '...' : '');
				$truncatedTitle = substr ( $track->getTitle (), 0, 45 ) . (strlen ( $track->getTitle () ) > 45 ? '...' : '');
				//$page->drawText($truncatedCarrierTitle, 285, $yShipments , 'UTF-8');
				$page->drawText ( $truncatedTitle, 295, $yShipments, 'UTF-8' );
				$page->drawText ( $track->getNumber (), 385, $yShipments, 'UTF-8' );
				$yShipments -= 7;
			}
		} else {
			$yShipments -= 7;
		}
		
		/*$currentY = min ( $yPayments, $yShipments );
		
		// replacement of Shipments-Payments rectangle block
		$page->drawLine ( 35, $this->y + 15 + $extraBoxHeight, 35, $currentY );
		$page->drawLine ( 35, $currentY, 560, $currentY );
		$page->drawLine ( 560, $currentY, 560, $this->y + 15 + $extraBoxHeight );
		*/
		$this->y = 580;
		$this->y -= 15;
	}
	
	protected function _sortTotalsList($a, $b) {
		if (! isset ( $a ['sort_order'] ) || ! isset ( $b ['sort_order'] )) {
			return 0;
		}
		
		if ($a ['sort_order'] == $b ['sort_order']) {
			return 0;
		}
		
		return ($a ['sort_order'] > $b ['sort_order']) ? 1 : - 1;
	}
	
	protected function _getTotalsList($source) {
		$totals = Mage::getConfig ()->getNode ( 'global/pdf/totals' )->asArray ();
		
		usort ( $totals, array ($this, '_sortTotalsList' ) );
		
		$totalModels = array ();
		
		foreach ( $totals as $index => $totalInfo ) {
			
			if (! empty ( $totalInfo ['model'] )) {
				
				$totalModel = Mage::getModel ( $totalInfo ['model'] );
				
				//if ($totalModel instanceof NMediaSystems_MagentoPDFInvoice_Model_Order_Pdf_Total_Default) {
				$totalInfo ['model'] = $totalModel;
				//} else {
			//	Mage::throwException ( Mage::helper ( 'sales' )->__ ( 'Pdf total model should extend Mage_Sales_Model_Order_Pdf_Total_Default' ) );
			//	Mage::throwException ( Mage::helper ( 'sales' )->__ ( 'Something is wrong here.' ) );
			//}
			} else {
				$totalModel = Mage::getModel ( $this->_defaultTotalModel );
				//echo "zz";
			}
			
			$totalModel->setData ( $totalInfo );
			$totalModels [] = $totalModel;
		}
		return $totalModels;
	}

	protected function insertComments($page, $source) {
		$order = $source->getOrder ();

        $comment = trim(strip_tags($order->getOnestepcheckoutCustomercomment()));

        if ($comment != "") {
            $comment = Mage::helper ( 'sales' )->__ ( 'Comments' ) . ": " . $comment;

            $this->_setFontItalic ( $page, 9 );
            $this->y = $this->_pdf->drawTextBox($page, $comment, 35, $this->y, $page->getWidth() - 35, 'left');
        }

        return $page;
    }
	
	protected function insertTotals($page, $source) {
		$order = $source->getOrder ();
		$totals = $this->_getTotalsList ( $source );
		$lineBlock = array ('lines' => array (), 'height' => 15 );
		
		foreach ( $totals as $total ) {
			$total->setOrder ( $order )->setSource ( $source );
			
			if ($total->canDisplay ()) {
				foreach ( $total->getTotalsForDisplay () as $totalData ) {
					//echo $totalData ['label'].strpos($totalData ['label'], "Belasting"). "---";
					$conditionTotal = strpos($totalData ['label'], "Totaal");

					if ($totalData ['label'] == "Subtotaal:") {
						$lineBlock ['lines'] [] = array (
							array ('text' => $totalData ['label'], 'feed' => 506, 'align' => 'right', 'font_size' => 10, 'font' => 'bold' ),
							array ('text' => $totalData ['amount'], 'feed' => 575, 'align' => 'right', 'font_size' => 10, 'font' => 'bold')
						);
					}
					else if ($totalData ['label'] == "Verzending & Transport:") {
						//echo "1st IF<br>";
						$lineBlock ['lines'] [] = array (
							array ('text' => 'Verzendkosten:', 'feed' => 515, 'align' => 'right', 'font_size' => 10, 'font' => 'bold' ),
							array ('text' => $totalData ['amount'], 'feed' => 574, 'align' => 'right', 'font_size' => 10, 'font' => 'bold')
						);
					}
					else if ($totalData ['label'] == "Additional Fees:") {
						//echo "1st IF<br>";
						$lineBlock ['lines'] [] = array (
							array ('text' => $totalData ['label'], 'feed' => 501, 'align' => 'right', 'font_size' => 10, 'font' => 'bold' ),
							array ('text' => $totalData ['amount'], 'feed' => 574, 'align' => 'right', 'font_size' => 10, 'font' => 'bold')
						);
					}
					else if ($totalData ['label'] == "Totaal (Ex. Belasting):") {
						//echo "1st IF<br>";
						$lineBlock ['lines'] [] = array (
							array ('text' => 'Totaal excl.:', 'feed' => 510, 'align' => 'right', 'font_size' => 10, 'font' => 'bold' ),
							array ('text' => $totalData ['amount'], 'feed' => 575, 'align' => 'right', 'font_size' => 10, 'font' => 'bold')
						);
					}
					else if ($totalData ['label'] == "Belasting:") {
						//echo "1st IF<br>";
						$lineBlock ['lines'] [] = array (
							array ('text' => 'BTW:', 'feed' => 502, 'align' => 'right', 'font_size' => 10, 'font' => 'bold' ),
							array ('text' => $totalData ['amount'], 'feed' => 574, 'align' => 'right', 'font_size' => 10, 'font' => 'bold')
						);
					}
					else if ($totalData ['label'] == "Totaal (Incl. Belasting):") {
						//echo "1st IF<br>";
						$lineBlock ['lines'] [] = array (
							array ('text' => 'Totaal incl.:', 'feed' => 508.5, 'align' => 'right', 'font_size' => 10, 'font' => 'bold' ),
							array ('text' => $totalData ['amount'], 'feed' => 575, 'align' => 'right', 'font_size' => 10, 'font' => 'bold')
						);
					}
					else{
						//echo "5th IF<br>";
						$lineBlock ['lines'] [] = array (
							array ('text' => $totalData ['label'], 'feed' => 491, 'align' => 'right', 'font_size' => 10, 'font' => 'bold' ),
							array ('text' => $totalData ['amount'], 'feed' => 577, 'align' => 'right', 'font_size' => 10, 'font' => 'bold')
						);
					}
				}
			}
		}
		//echo "<pre>";
		//print_r($lineBlock);
		//exit();
		$page->setLineColor(new Zend_Pdf_Color_GrayScale(0.8));
		$page->setLineWidth(0.8); 
		$page->drawLine(390, 230, 573, 230 );

		$page = $this->drawLineBlocks ( $page, array ($lineBlock ));
		return $page;
	}
	
	protected function insertStoreInfo($page, $source, $store = null) {
		
		//BOTTOM RACTANGLE
		$pdffont = Zend_Pdf_Font::fontWithPath('media/pdf/Source-Sans_Pro/SourceSansPro-Light.ttf');
		$page->setFont($pdffont, 7.5);

		$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 1 ) ); 
		$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Adres' ), 35, 150, 'UTF-8' );

		$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 0 ) );
		$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Witte Paal 300A' ), 98, 150, 'UTF-8' );

		$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 0 ) ); // changed 0.4 to 0
		$page->drawText ( Mage::helper ( 'sales' )->__ ( 'KVK' ), 230, 150, 'UTF-8' );
		
		$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 0 ) );
		$page->drawText ( Mage::helper ( 'sales' )->__ ( '52365417' ), 309, 150, 'UTF-8' );
		
		$page->drawText ( Mage::helper ( 'sales' )->__ ( '1742LD Schagen' ), 98, 138, 'UTF-8' );

		$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 0 ) ); // changed 0.4 to 0
		$page->drawText ( Mage::helper ( 'sales' )->__ ( 'ABN Amro' ), 230, 138, 'UTF-8' );

		$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 0 ) );
		$page->drawText ( Mage::helper ( 'sales' )->__ ( 'NL 85 ABNA 0567 277 437' ), 309, 138, 'UTF-8' );

		$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 0 ) ); // changed 0.4 to 0
		$page->drawText ( Mage::helper ( 'sales' )->__ ( 'BTW' ), 35, 126, 'UTF-8' );

		$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 0 ) );
		$page->drawText ( Mage::helper ( 'sales' )->__ ( 'NL 850411932 B01' ), 98, 126, 'UTF-8' );

		$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 0 ) ); // changed 0.4 to 0
		$page->drawText ( Mage::helper ( 'sales' )->__ ( 'Rabobank' ), 230, 126, 'UTF-8' );

		$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 0 ) );
		$page->drawText ( Mage::helper ( 'sales' )->__ ( 'NL 63 RABO 0181 079 976' ), 309, 126, 'UTF-8' );

		
		
		////////$page->drawLine(22, 140, 573, 140);

		$this->_setFontRegular ( $page, 8 );
		$page->setFillColor ( new Zend_Pdf_Color_GrayScale ( 0 ) ); // changed 0.4 to 0

		$this->y = 100;

        $footer = Mage::getStoreConfig ( 'sales/identity/footer', $store );
		$pdffont = Zend_Pdf_Font::fontWithPath('media/pdf/Source-Sans_Pro/SourceSansPro-Light.ttf');
		
		$page->setFont($pdffont, 6.5);
		$footer = str_split($footer,195);
		$page->drawText ( trim ( strip_tags ( $footer[0] ) ), 35, $this->y, 'UTF-8' );
		$page->drawText ( trim ( strip_tags ( $footer[1] ) ), 55, $this->y-10, 'UTF-8' );
		//$page->drawLine(22, $this->y-20, 573, $this->y-20); 
        //$this->_pdf->drawTextBox($page, $footer, 35, $this->y, floor(($page->getWidth() - 40) ), 'left');
       
//		foreach ( explode ( "\n", $footer ) as $value ) {
//			//foreach (explode("\n", $message) as $value){
//			if ($value !== '') {
//				$page->drawText ( trim ( strip_tags ( $value ) ), 35, $this->y, 'UTF-8' );
//				$this->y -= 10;
//			}
//		}
		
	//$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
	//$page->setLineWidth(0);
	

	//$this->_setFontBold($page);
	//$this->y = 80;
	//$page->drawText($bold_message, 40, $this->y, 'UTF-8');
	

	//$this->_setFontRegular($page);
	//$this->y -= 10;
	//$page->drawText($message1, 40, $this->y, 'UTF-8');
	//$this->y -= 10;
	//$page->drawText($message2, 40, $this->y, 'UTF-8');
	//$this->y -= 10;
	//$page->drawText($message3, 40, $this->y, 'UTF-8')
	

	}
	
	protected function _parseItemDescription($item) {
		$matches = array ();
		$description = $item->getDescription ();
		if (preg_match_all ( '/<li.*?>(.*?)<\/li>/i', $description, $matches )) {
			return $matches [1];
		}
		
		return array ($description );
	}
	
	/**
	 * Before getPdf processing
	 *
	 */
	protected function _beforeGetPdf() {
		$translate = Mage::getSingleton ( 'core/translate' );
		/* @var $translate Mage_Core_Model_Translate */
		$translate->setTranslateInline ( false );
	}
	
	/**
	 * After getPdf processing
	 *
	 */
	protected function _afterGetPdf() {
		$translate = Mage::getSingleton ( 'core/translate' );
		/* @var $translate Mage_Core_Model_Translate */
		$translate->setTranslateInline ( true );
	}
	
	protected function _formatOptionValue($value, $order) {
		$resultValue = '';
		if (is_array ( $value )) {
			if (isset ( $value ['qty'] )) {
				$resultValue .= sprintf ( '%d', $value ['qty'] ) . ' x ';
			}
			
			$resultValue .= $value ['title'];
			
			if (isset ( $value ['price'] )) {
				$resultValue .= " " . $order->formatPrice ( $value ['price'] );
			}
			return $resultValue;
		} else {
			return $value;
		}
	}
	
	protected function _initRenderer($type) {
		$node = Mage::getConfig ()->getNode ( 'global/pdf/' . $type );
		foreach ( $node->children () as $renderer ) {
			$this->_renderers [$renderer->getName ()] = array ('model' => ( string ) $renderer, 'renderer' => null );
		}
	}
	
	/**
	 * Retrieve renderer model
	 *
	 * @throws Mage_Core_Exception
	 * @return Mage_Sales_Model_Order_Pdf_Items_Abstract
	 */
	protected function _getRenderer($type) {
		if (! isset ( $this->_renderers [$type] )) {
			$type = 'default';
		}
		
		if (! isset ( $this->_renderers [$type] )) {
			Mage::throwException ( Mage::helper ( 'sales' )->__ ( 'Invalid renderer model' ) );
		}
		
		if (is_null ( $this->_renderers [$type] ['renderer'] )) {
			$this->_renderers [$type] ['renderer'] = Mage::getSingleton ( $this->_renderers [$type] ['model'] );
		}
		
		return $this->_renderers [$type] ['renderer'];
	}
	
	/**
	 * Public method of protected @see _getRenderer()
	 *
	 * Retrieve renderer model
	 *
	 * @param string $type
	 * @return Mage_Sales_Model_Order_Pdf_Items_Abstract
	 */
	public function getRenderer($type) {
		return $this->_getRenderer ( $type );
	}
	
	/**
	 * Draw Item process
	 *
	 * @param Varien_Object $item
	 * @param Zend_Pdf_Page $page
	 * @param Mage_Sales_Model_Order $order
	 * @return Zend_Pdf_Page
	 */
	protected function _drawItem(Varien_Object $item, Zend_Pdf_Page $page, Mage_Sales_Model_Order $order) {
		$type = $item->getOrderItem ()->getProductType ();
		$renderer = $this->_getRenderer ( $type );
					
		//$renderer = $this->_getRenderer ( "default" );
		$renderer->setOrder ( $order );
		$renderer->setItem ( $item );
		$renderer->setPdf ( $this );
		$renderer->setPage ( $page );
		$renderer->setRenderedModel ( $this );
		
		$renderer->draw ();
		
		return $renderer->getPage ();
	}
	
	protected function _setFontRegular($object, $size = 10) {
		$font = Zend_Pdf_Font::fontWithName ( Zend_Pdf_Font::FONT_HELVETICA );
		$object->setFont ( $font, $size );
		return $font;
	}
	
	protected function _setFontBold($object, $size = 10) {
		$font = Zend_Pdf_Font::fontWithName ( Zend_Pdf_Font::FONT_HELVETICA_BOLD );
		$object->setFont ( $font, $size );
		return $font;
	}
	
	protected function _setFontItalic($object, $size = 10) {
		$font = Zend_Pdf_Font::fontWithName ( Zend_Pdf_Font::FONT_HELVETICA_ITALIC );
		$object->setFont ( $font, $size );
		return $font;
	}
	
	/**
	 * Set PDF object
	 *
	 * @param Zend_Pdf $pdf
	 * @return Mage_Sales_Model_Order_Pdf_Abstract
	 */
	protected function _setPdf(Zend_Pdf $pdf) {
		$this->_pdf = $pdf;
		return $this;
	}
	
	/**
	 * Retrieve PDF object
	 *
	 * @throws Mage_Core_Exception
	 * @return Zend_Pdf
	 */
	protected function _getPdf() {
		if (! $this->_pdf instanceof Zend_Pdf) {
			Mage::throwException ( Mage::helper ( 'sales' )->__ ( 'Please define PDF object before using' ) );
		}
		
		return $this->_pdf;
	}
	
	/**
	 * Create new page and assign to PDF object
	 *
	 * @param array $settings
	 * @return Zend_Pdf_Page
	 */
	public function newPage(array $settings = array()) {
		$pageSize = ! empty ( $settings ['page_size'] ) ? $settings ['page_size'] : Zend_Pdf_Page::SIZE_A4;
		$page = $this->_getPdf ()->newPage ( $pageSize );
		$this->_getPdf ()->pages [] = $page;
		$this->y = 800;
		
		return $page;
	}
	
	/**
	 * Draw lines
	 *
	 * draw items array format:
	 * lines        array;array of line blocks (required)
	 * shift        int; full line height (optional)
	 * height       int;line spacing (default 10)
	 *
	 * line block has line columns array
	 *
	 * column array format
	 * text         string|array; draw text (required)
	 * feed         int; x position (required)
	 * font         string; font style, optional: bold, italic, regular
	 * font_file    string; path to font file (optional for use your custom font)
	 * font_size    int; font size (default 7)
	 * align        string; text align (also see feed parametr), optional left, right
	 * height       int;line spacing (default 10)
	 *
	 * @param Zend_Pdf_Page $page
	 * @param array $draw
	 * @param array $pageSettings
	 * @throws Mage_Core_Exception
	 * @return Zend_Pdf_Page
	 */
	public static $count = 1;
	public function drawLineBlocks(Zend_Pdf_Page $page, array $draw, array $pageSettings = array()) {
		
		// echo "<pre>";
		// print_r($draw);
		// echo "</pre>";
		// echo "<br>----------------<br>";
		// echo "Y: " . $this->y;
		if($this->y == 185){
			$this->y = 250;
		}
		foreach ( $draw as $itemsProp ) {
			if (! isset ( $itemsProp ['lines'] ) || ! is_array ( $itemsProp ['lines'] )) {
				Mage::throwException ( Mage::helper ( 'sales' )->__ ( 'Invalid draw line data. Please define "lines" array' ) );
			}
			$lines = $itemsProp ['lines'];
			$height = isset ( $itemsProp ['height'] ) ? $itemsProp ['height'] : 10;
			

			
			if (empty ( $itemsProp ['shift'] )) {
				$shift = 0;
				foreach ( $lines as $line ) {
					$maxHeight = 0;
					foreach ( $line as $column ) {
						$lineSpacing = ! empty ( $column ['height'] ) ? $column ['height'] : $height;
						if (! is_array ( $column ['text'] )) {
							$column ['text'] = array ($column ['text'] );
						}
						$top = 0;
						foreach ( $column ['text'] as $part ) {
							$top += $lineSpacing;
						}
						
						$maxHeight = $top > $maxHeight ? $top : $maxHeight;
					}
					$shift += $maxHeight;
				}
				$itemsProp ['shift'] = $shift;
			}
			
			if ($this->y - $itemsProp ['shift'] < 25) {
				$page = $this->newPage ( $pageSettings );
			}
			
			foreach ( $lines as $line ) {
				$maxHeight = 0;
				foreach ( $line as $column ) {
					$fontSize = empty ( $column ['font_size'] ) ? 10 : $column ['font_size'];
					if (! empty ( $column ['font_file'] )) {
						$font = Zend_Pdf_Font::fontWithPath ( $column ['font_file'] );
						$page->setFont ( $font );
					} else {
						$fontStyle = empty ( $column ['font'] ) ? 'regular' : $column ['font'];
						switch ($fontStyle) {
							case 'bold' :
								$font = $this->_setFontBold ( $page, $fontSize );
								break;
							case 'italic' :
								$font = $this->_setFontItalic ( $page, $fontSize );
								break;
							default :
								$font = $this->_setFontRegular ( $page, $fontSize );
								break;
						}
					}
					
					if (! is_array ( $column ['text'] )) {
						$column ['text'] = array ($column ['text'] );
					}
					
					$lineSpacing = ! empty ( $column ['height'] ) ? $column ['height'] : $height;
					$top = 0;
					foreach ( $column ['text'] as $part ) {
						$feed = $column ['feed'];
						$textAlign = empty ( $column ['align'] ) ? 'left' : $column ['align'];
						$width = empty ( $column ['width'] ) ? 0 : $column ['width'];
						switch ($textAlign) {
							case 'right' :
								if ($width) {
									$feed = $this->getAlignRight ( $part, $feed, $width, $font, $fontSize );
								} else {
									$feed = $feed - $this->widthForStringUsingFontSize ( $part, $font, $fontSize );
								}
								break;
							case 'center' :
								if ($width) {
									$feed = $this->getAlignCenter ( $part, $feed, $width, $font, $fontSize );
								}
								break;
						}
						
						$pdffont = Zend_Pdf_Font::fontWithPath('media/pdf/Source-Sans_Pro/SourceSansPro-Light.ttf');
						$page->setFont($pdffont, 8.75);
						
						if($part == "Totaal incl.:"){
							$page->setLineColor(new Zend_Pdf_Color_GrayScale(0));
							$page->setLineWidth(1); 
							$page->drawLine(390, $this->y + 10, 573, $this->y + 10);
						}

						// if($part == "Verzendkosten:"){
							
						// }
						$page->drawText ( $part, $feed, $this->y - $top, 'UTF-8' );
						$top += $lineSpacing;
					}
					
					$maxHeight = $top > $maxHeight ? $top : $maxHeight;
				}
				$this->y -= $maxHeight;
			}
		}
		// if($msg == "total"){
		// 	echo "fibne";
		// 	exit();	
		// }
		return $page;
	}
}