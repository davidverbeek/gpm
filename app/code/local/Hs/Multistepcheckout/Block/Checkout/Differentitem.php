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
class Hs_Multistepcheckout_Block_Checkout_Differentitem extends Mage_Sales_Block_Items_Abstract
{
    protected $_address;
    protected $_customer;
    protected $_quote;
    protected $_checkout;
    
    public function getItems()
    {
        return Mage::getSingleton('checkout/session')->getQuote()->getAllVisibleItems();
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
                ->setExtraParams('onchange="'.$type.'.newAddress(!this.value)"')
                ->setValue($addressId)
                ->setOptions($options);

            $select->addOption('', Mage::helper('checkout')->__('New Address'));

            return $select->getHtml();
        }
        return '';
    }
    
}
