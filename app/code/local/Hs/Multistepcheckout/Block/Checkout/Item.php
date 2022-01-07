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
 * @package     Mage_Checkout
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * One page checkout status
 *
 * @category   Mage
 * @category   Mage
 * @package    Mage_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */ 
class Hs_Multistepcheckout_Block_Checkout_Item extends Mage_Sales_Block_Items_Abstract {
	protected $_address;
	protected $_customer;
	protected $_quote;
	protected $_checkout;
	
	public function __construct() {		
		if(Mage::registry('cart_stock_data')=="") {
			$this->getItems();
		}
	}

	public function getItems() {

		$item= Mage::getSingleton('checkout/session')->getQuote()->getAllVisibleItems();
		
		foreach ($item as $_item){
			$instock=0;
			
			$artikel = new stdClass();
			$skuList[] = $_item->getSku();
			$artikel->sku = $_item->getSku();
			$artikel->type = '';
			$artikel->qty = $_item->getQty();
			$artikel->id = $_item->getId();
			$artikelen[] = $artikel;
		}    
		 
		// $result = Mage::helper('price/data')->getVoorraadMore($artikelen);
		$resultStock = Mage::helper('price/data')->getVoorraadMore($artikelen);
		// $resultDeliveryTime = Mage::helper('price/data')->getLevertijdMore($artikelen);

		if(isset($resultStock->decimal) && !is_array($resultStock->decimal)) {

			/*** Combipac returned single result ***/
			$result = $resultStock->decimal;
			$resultData = $this->buildResult($result, $artikel);
			$stockResult = $this->checkStock($resultData,$artikel->qty,$artikel->id);
			$finalresult[$artikel->sku] =$stockResult;
 
		} elseif(isset($resultStock->decimal)) {

			/*** Combipac returned multiresult ***/

			$cnt = 0;

			foreach($resultStock->decimal as $r) {

				$resultData = $this->buildResult($r, $artikelen[$cnt]);
				$stockResult = $this->checkStock($resultData,$artikelen[$cnt]->qty,$artikelen[$cnt]->id);
				$finalresult[$artikelen[$cnt]->sku] =$stockResult;

				$cnt++;
			}
		} else {
			$finalresult = array();
		}

		// Mage::log($finalresult,null,'productLog.log');

		if(Mage::registry('cart_stock_data')==''){    
			Mage::register('cart_stock_data', $finalresult);
		}
	}

	public function checkStock($resultData, $qty, $id) {

		$sku = $resultData->Artinr;

		$_product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
		$stockstatus = $_product->getArtikelstatus();
		$idealeverpakking = str_replace(",", ".", $_product->getIdealeverpakking());
		$leverancier = $_product->getLeverancier();
		$afwijkenidealeverpakking = $_product->getAfwijkenidealeverpakking();


		$_item = Mage::getSingleton('checkout/cart')->getQuote()->setStoreId(Mage::app()->getStore()->getId())->getItemByProduct($_product);

		$_incl = Mage::helper('checkout')->getPriceInclTax($_item);
		
		$truestock = $resultData->VoorHH;

		$unit = Mage::helper('featured')->getProductUnit($_product->getData('verkoopeenheid'));
		$verkoopeenheid = Mage::helper('featured')->getStockUnit($qty, $unit);

		if ($qty > $truestock) {
			$backorder = $qty - $truestock;
			if ($truestock == 0) {
				if ($stockstatus == 6) {
					$finalContent = '<span class="stock"> (U kunt niet meer dan ' . $truestock . ' ' . $verkoopeenheid . ' <span id="besteld-term-' . $id . '" class="besteld-info">bestellen</span>)</span>';

				} else {
					if ($leverancier == 3797) {
						$finalContent = '';
					} else {
						//var levertijd = $j('#' + id + ' .now-order').html();
						$finalContent = '<span class="yellow"></span><span class="stock">' . $backorder . ' ' . $verkoopeenheid . ' worden besteld </span><span class="now-order">' . $levertijd . '</span>';
					}
				}
			} else {
				if ($stockstatus == 6) {
					$finalContent = '<span class="yellow"></span><span class="stock">slechts ' . $truestock . ' ' . $verkoopeenheid . ' leverbaar</span><span class="now-order">Artikel verdwijnt uit assortiment</span>';
				} else {

					if ($afwijkenidealeverpakking != 0) {
						$hstock = ' ' . $verkoopeenheid;
					} else {
						$hstock = ' x ' . $idealeverpakking . ' ' . $verkoopeenheid;
					}
					if ($_product->getTypeId() == 'grouped') {
						$finalContent = '<span class="stock">' . $this->calculateQty($truestock, $idealeverpakking, $verkoopeenheid, $afwijkenidealeverpakking) . ' </span><span class="stock yellow"> (' . $backorder . $hstock . ' worden <span id="backorder-term-' . $id . '" class="backorder-info">besteld </span>)</span>';
						$finalContent .= "<span class='cart-unit-price-excl'> x " . Mage::helper('checkout')->formatPrice($_item->getCalculationPrice()) . "</span>";
						$finalContent .= "<span class='cart-unit-price-incl'> ( " . Mage::helper('checkout')->formatPrice($_incl - $_item->getWeeeTaxDisposition()) . " " . $this->__('incl.') . ' ' . $this->__('per') . ' ' . $verkoopeenheid . " )</span>";
					} else {
						if ($leverancier == 3797 && $truestock <= 0) {
							$finalContent = '';
						} else {
							//$finalContent= '<div class="backorder stock"><span class="yellow"></span><span class="stock"><div class="stockstatus"> ' . $qty . ' worden <span id="backorder-term-' . $id . '" class="backorder-info">besteld</span></div><span class="now-order">in +/- ' . $instockDeleveryDays . ' werkdagen geleverd</span></div>';
							$finalContent = '<div class="backorder stock"><span class="yellow"></span><span class="stock"><div class="stockstatus"> ' . $qty . ' ' . $unitLabel . '</div> <span class="now-order">' . $qty . ' ' . $unitLabel . ' worden nageleverd</span></div>';
							$finalContent .= "<span class='cart-unit-price-excl'> x " . Mage::helper('checkout')->formatPrice($_item->getCalculationPrice()) . "</span>";
							$finalContent .= "<span class='cart-unit-price-incl'> ( " . Mage::helper('checkout')->formatPrice($_incl - $_item->getWeeeTaxDisposition()) . " " . $this->__('incl.') . ' ' . $this->__('per') . ' ' . $verkoopeenheid . " )</span>";
						}

					}

				}
			}
			$instock = 0;
		} else {
			
			if ($leverancier == 3797 && $qty <= 0) {
				$finalContent = $resultData->text; // Based on class
				$instock = 1;
			} else {
				$finalContent = $this->calculateQty($qty, $idealeverpakking, $verkoopeenheid, $afwijkenidealeverpakking);
				$finalContent .= "<span class='cart-unit-price-excl'> x " . Mage::helper('checkout')->formatPrice($_item->getCalculationPrice()) . "</span>";
				$finalContent .= "<span class='cart-unit-price-incl'> ( " . Mage::helper('checkout')->formatPrice($_incl - $_item->getWeeeTaxDisposition()) . " " . $this->__('incl.') . ' ' . $this->__('per') . ' ' . $verkoopeenheid . " )</span>";
				$finalContent .= $resultData->text;
				$instock = 1;
			}
		}

		$resultData->finaltext = $finalContent;
		$resultData->instock = $instock;
		return $resultData;
	}

	public function calculateQty($qty, $idealeverpakking, $verkoopeenheid, $afwijkenidealeverpakking) {
		if ($afwijkenidealeverpakking != 0 || $idealeverpakking == 1) { 
			if ($qty > 0) {
				return '<span class="green"></span><span class="stock">' . $qty .  ' </span>';
			} else {
				return '<span class="yellow"></span>';
			}

			return "";

		}	else {
			if ($qty > 0) {
				return '<span class="green"></span><span class="stock">' . $qty .  ' </span>';
			} else {
				return '<span class="yellow"></span>';
			}
			return "";
		}
	} 
	
	public function buildResult($result, $artikel) {
		$newresult = new stdClass();
		$newresult->Artinr = $artikel->sku;
		$newresult->VoorHH = $result;

		/*
		* Name: Helios Solutions
		* Date: 13-Augest-2014
		* Definition: Create Logic For Showing Actual Stock on FroentEnd Side.
		*/
		$productId = Mage::getModel('catalog/product')->getIdBySku($artikel->sku);
		$product = Mage::getModel('catalog/product')->load($productId);
		$newresult->Levertijd = $levertijd = $product->getData('deliverytime');
		if($product->getData('afwijkenidealeverpakking')!=0){
			$result=$result;
		}else{
			$idealeverpakking=$product->getData('idealeverpakking');
			$result=(int)($result / $idealeverpakking);
			$newresult->VoorHH = $result;
		}
		/*out of stock product*/
		if($result <= 0) { 
			if($newresult->Levertijd == 1) {
				$levertijd = 'verzending: binnen ' . $newresult->Levertijd . ' werkdagen';
			} else {
				if($newresult->Levertijd > 14) {
					$levertijd = 'verzending: binnen 14 werkdagen';
				} else {
					$levertijd = 'verzending: binnen ' . $newresult->Levertijd . ' werkdagen';
				}
			}

			if($product->getData('leverancier')==3797){
				$newresult->text = '<span class="green"></span><span class="stock">Op voorraad bij leverancier</span><span class="now-order">Levertijd +/- 3 werkdagen</span>';
			}
			else {
				$newresult->text = '<span class="stock">' . $levertijd . '</span>';
			}

		} else {
				/*in of stock product*/
				$leverdatum=date_create($product->getData('leverdatum'));
				$curentDate=date_create(date("Y-m-d"));
				$diff=date_diff($curentDate,$leverdatum);
				if($diff->format("%R")==='+'){
					$levertijd= $diff->format("%a");
				}elseif($diff->format("%R")==='-'){
					if($newresult->Levertijd == 1){
						$levertijd= 5;
					}else {
						$levertijd= $newresult->Levertijd;
					}
				}
				if($product->getArtikelstatus() == 3){
					//$newresult->text ='<span class="stock">op voorraad</span><span class="now-order">u kunt niet meer dan '.$result->Voorraad.' ' .$product->getVerkoopeenheid().' bestellen</span><input type="hidden" name="levertijd" class="levertijd" value="'.$levertijd.'">';
					$newresult->text ='<span class="now-order">u kunt niet meer dan '.$result.' ' .$product->getVerkoopeenheid().' bestellen</span><input type="hidden" name="levertijd" class="levertijd" value="'.$levertijd.'">';
				}else{
					//$newresult->text ='<span class="stock">op voorraad</span><span class="now-order">vandaag besteld, morgen in huis</span><input type="hidden" name="levertijd" class="levertijd" value="'.$levertijd.'">';
					$instockDeliveryText = Mage::helper('featured')->getInstockDeliveryText();
					$newresult->text ='<span class="now-order">'.$instockDeliveryText.'</span><input type="hidden" name="levertijd" class="levertijd" value="'.$levertijd.'">';
				}
				
		}
		return $newresult;
	}
	
	public function getCustomer(){
		if (empty($this->_customer)) {
			$this->_customer = Mage::getSingleton('customer/session')->getCustomer();
		}
		return $this->_customer;
	}
	
	public function getCheckout(){
		if (empty($this->_checkout)) {
			$this->_checkout = Mage::getSingleton('checkout/session');
		}
		return $this->_checkout;
	}
	
	public function customerHasAddresses(){
		return count($this->getCustomer()->getAddresses());
	}
	
	public function isCustomerLoggedIn(){
		return Mage::getSingleton('customer/session')->isLoggedIn();
	}
	
	public function getQuote(){
		if (empty($this->_quote)) {
			$this->_quote = $this->getCheckout()->getQuote();
		}
		return $this->_quote;
	}
	
	public function getAddress(){
		if (is_null($this->_address)) {
			if ($this->isCustomerLoggedIn()) {
				$this->_address = $this->getQuote()->getShippingAddress();
				if(!$this->_address->getFirstname()) {
					$this->_address->setFirstname($this->getQuote()->getCustomer()->getFirstname());
				}
				if(!$this->_address->getLastname()) {
					$this->_address->setLastname($this->getQuote()->getCustomer()->getLastname());
				}
			} else {
				$this->_address = Mage::getModel('sales/quote_address');
			}
		}

		return $this->_address;
	}
	
	public function getAddressesHtmlSelect($type){
		if ($this->isCustomerLoggedIn()) {
			$options = array();
			foreach ($this->getCustomer()->getAddresses() as $address) {
				$options[] = array(
					'value' => $address->getId(),
					'label' => $address->format('oneline')
				);
			}

			$addressId = $this->getAddress()->getCustomerAddressId();
			if (empty($addressId)) {
				if ($type=='billing') {
					$address = $this->getCustomer()->getPrimaryBillingAddress();
				} else {
					$address = $this->getCustomer()->getPrimaryShippingAddress();
				}
				if ($address) {
					$addressId = $address->getId();
				}
			}

			$select = $this->getLayout()->createBlock('core/html_select')
				->setName($type.'_address_id')
				->setId($type.'-address-select')
				->setClass('address-select')
				//->setExtraParams('onchange="'.$type.'.newAddress(!this.value)"')
				->setValue($addressId)
				->setOptions($options);

		  //  $select->addOption('', Mage::helper('checkout')->__('New Address'));

			return $select->getHtml();
		}
		return '';
	}
	
}
