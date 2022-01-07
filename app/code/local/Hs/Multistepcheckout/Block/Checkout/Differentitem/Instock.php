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
class Hs_Multistepcheckout_Block_Checkout_Differentitem_Instock extends Mage_Sales_Block_Items_Abstract
{
    protected $_address;
    protected $_customer;
    protected $_quote;
    protected $_checkout;
    
    public function getItems()
    {
        return  Mage::getSingleton('checkout/session')->getQuote()->getAllVisibleItems(); 
        /*foreach ($item as $_item){
            $instock=false;
            
            $_product=Mage::getModel('catalog/product')->load($_item->getProductId());
            $stockItem=$_product->getStockItem();
            //$qty=$stockItem->getQty();
            $stockstatus = $_product->getArtikelstatus();
            $idealeverpakking=  str_replace(",", ".",$_product->getIdealeverpakking());
            $leverancier = $_product->getLeverancier();
            $verkoopeenheid = strtolower($_product->getVerkoopeenheid());
            $afwijkenidealeverpakking = $_product->getAfwijkenidealeverpakking();
            
            
            $artikel->sku = $_product->getSku();
            $artikel->type = '';
            $artikelen[] = $artikel;

            $result = Mage::helper('price/data')->getVoorraadMore($artikelen);
            
           
            if(isset($result->VoorraadInfo) && !is_array($result->VoorraadInfo)) {

                

                $result = $result->VoorraadInfo;
                $qty= $_item->getQty();    
                $truestock=$result->Voorraad;
                
                
                 if ($qty > $truestock) {
                    $backorder = $qty - $truestock; 
                    if ($truestock == 0) {
                        if ($stockstatus == 6) {
                            $instock=false;

                        } else {
                                if($leverancier==3797){
                                    $instock=false;
                                }else{
                                    //var levertijd = $j('#' + id + ' .now-order').html();
                                    $instock=false;
                                }
                        }
                    } else { 
                            if ($stockstatus == 6) {
                                $instock=false;
                            }
                            else {
                               
                                if ($afwijkenidealeverpakking != 0) {
                                    //$hstock = ' ' . $verkoopeenheid;
                                    $instock=false;
                                }
                                else {
                                    $instock=false;
                                    //$hstock = ' x ' . $idealeverpakking . ' ' . $verkoopeenheid;
                                }
                                if ( $_product->getTypeId()=='grouped') {
                                    $instock=false;
                                }
                                else {
                                    if($leverancier==3797  && $truestock <= 0){
                                        $finalContent='';
                                    }else { 
                                        $instock=false;
                                    }

                                }

                            }
                    }
                } else if ($truestock == 0) {
                    if (!$smalldisplay) {
                        $instock=false;
                    } else {
                        //$j('#' + id).html('<span class="stock yellow"><img src="/skin/frontend/gyzs/default/images/stock_yellow.png" /></span>');
                        $instock=false;
                    }
                } else { 
                        {
                            if($leverancier==3797 && $truestock <= 0){
                                $finalContent='';
                            }else{
                                
                                if ($afwijkenidealeverpakking != 0) {
                                    //$hstock = ' ' + $verkoopeenheid;
                                    $instock=true;
                                }
                                else { 
                                    //$hstock = ' x ' + $idealeverpakking + ' ' + $verkoopeenheid;
                                    $instock=true;
                                }
                                //$finalContent='<span class="stock">' . $this->calculateQty($truestock, $idealeverpakking, $verkoopeenheid, $afwijkenidealeverpakking) . ' op voorraad</span><span class="now-order">'. $qty .' '.$hstock.' direct leverbaar</span>';
                                $instock=true;
                            }
                        }
                }       
                
                 
            }
            
            if($instock)
            {
                $items[]=$_item;
            }
        }*/
        return $items;
    }
    
    public function calculateQty($qty, $idealeverpakking, $verkoopeenheid, $afwijkenidealeverpakking) {
        if ($afwijkenidealeverpakking != 0 || $idealeverpakking == 1) {
            if ($qty > 0) {
                if ($qty <= 2) {
                    return true;
                } else {
                    return true;;
                }
            } else {
                return false;
            }

            return "";

        }
        else {

            if ($qty > 0) {
                
                if ($qty <= 2) {
                    return true;;
                } else {
                    return true;
                }
            } else {
                return false;
            }
            return "";

        }


} 
    
    
    
    
    public function getCustomer()
    {
        if (empty($this->_customer)) {
            $this->_customer = Mage::getSingleton('customer/session')->getCustomer();
        }
        return $this->_customer;
    }
    
    public function getCheckout()
    {
        if (empty($this->_checkout)) {
            $this->_checkout = Mage::getSingleton('checkout/session');
        }
        return $this->_checkout;
    }
    
    public function customerHasAddresses()
    {
        return count($this->getCustomer()->getAddresses());
    }
    
    public function isCustomerLoggedIn()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }
    
    public function getQuote()
    {
        if (empty($this->_quote)) {
            $this->_quote = $this->getCheckout()->getQuote();
        }
        return $this->_quote;
    }
    
    public function getAddress()
    {
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
    
    public function getAddressesHtmlSelect($type)
    {
        $stockData=Mage::registry('cart_stock_data');
        foreach($stockData as $sku=>$stock){ 
            if($stock->instock){
                $stockValue[]=$sku;
            }
        }
        $itemSku=  implode(",", $stockValue);
        
        if ($this->isCustomerLoggedIn()) {
            $options = array();
            foreach ($this->getCustomer()->getAddresses() as $address) {
                $options[] = array(
                    'value' => $address->getId(),
                    'label' => $address->format('oneline')
                );
            }
            
            $quoteId=$this->getQuote()->getId();
            
            $addressCollection=Mage::getModel('sales/quote_address')->getCollection()
                                ->addFieldToFilter('address_type','shipping')
                                ->addFieldToFilter('quote_id',$quoteId);
            
            
            if($addressCollection->getSize()>1){
            
                foreach ($addressCollection as $_address)
                {
                    $addressCollectionItem=Mage::getModel('sales/quote_address_item')->getCollection()
                                            ->addFieldToFilter('quote_address_id',$_address->getId())
                                            ->addFieldToFilter('sku',array('in'=>$itemSku));
                                           
                     
                     if($addressCollectionItem->getSize()>0){
                        $addressId=$_address->getCustomerAddressId();
                        if(!Mage::registry('instock_address'))
                        Mage::register('instock_address', $_address);
                        
                        break;
                     }
                     
                }
                
            }
            else{
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
            }
            
            $select = $this->getLayout()->createBlock('core/html_select')
                ->setName($type.'_address_id')
                ->setId($type.'-address-select')
                ->setClass('address-select')
               // ->setExtraParams('onchange="'.$type.'.newAddress(!this.value)"')
                ->setValue($addressId)
                ->setOptions($options);

           // $select->addOption('', Mage::helper('checkout')->__('New Address'));

            return $select->getHtml();
        }
        return '';
    }
    
    public function getAddressId()
    {
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
        return $addressId;
    }
    
    public function getActiveShippingMethods()
    {
        $methods = array(array('value'=>'','label'=>Mage::helper('adminhtml')->__('--Please Select--')));

        $activeCarriers = Mage::getSingleton('shipping/config')->getActiveCarriers();
        foreach($activeCarriers as $carrierCode => $carrierModel)
        {
           $options = array();
           if( $carrierMethods = $carrierModel->getAllowedMethods() )
           {
               foreach ($carrierMethods as $methodCode => $method)
               {
                    $code= $carrierCode.'_'.$methodCode;
                    //$options[]=array('value'=>$code,'label'=>$method);
                    $options=$code;

               }
               $carrierTitle = Mage::getStoreConfig('carriers/'.$carrierCode.'/title');
               $methods[]=array('value'=>$options,'label'=>$carrierTitle);
           }
            
        }
        return $methods;
    }
}
