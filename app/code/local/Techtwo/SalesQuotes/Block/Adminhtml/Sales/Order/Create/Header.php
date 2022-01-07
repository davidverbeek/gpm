<?php

class Techtwo_SalesQuotes_Block_Adminhtml_Sales_Order_Create_Header extends Mage_Adminhtml_Block_Sales_Order_Create_Header
{
    protected function _toHtml()
    {
        // update header for quotes
        if (Mage::getSingleton('adminhtml/session_quote')->getConvertOrderQuote()) {
            if ($this->_getSession()->getQuote()->getId() && $this->_getSession()->getQuote()->getIsCartAuto() != 1 && Mage::getSingleton('adminhtml/session_quote')->getConvertOrderQuote() != -1) {
                $out = '<h3 class="icon-head head-sales-order">'.Mage::helper('techtwo_salesquotes')->__('Edit Quote #%s', $this->_getSession()->getQuote()->getEntityId()).'</h3>';
            }
            else {
                $customerId = $this->getCustomerId();
                $storeId    = $this->getStoreId();
                $out = '';
                if ($customerId && $storeId) {
                    $out.= Mage::helper('sales')->__('Create New Quote for %s in %s', $this->getCustomer()->getName(), $this->getStore()->getName());
                }
                elseif (!is_null($customerId) && $storeId){
                    $out.= Mage::helper('sales')->__('Create New Quote for New Customer in %s', $this->getStore()->getName());
                }
                elseif ($customerId) {
                    $out.= Mage::helper('sales')->__('Create New Quote for %s', $this->getCustomer()->getName());
                }
                elseif (!is_null($customerId)){
                    $out.= Mage::helper('sales')->__('Create New Quote for New Customer');
                }
                else {
                    $out.= Mage::helper('sales')->__('Create New Quote');
                }
                $out = $this->htmlEscape($out);
                $out = '<h3 class="icon-head head-sales-order">' . $out . '</h3>';
            }
            return $out;
        }

        return parent::_toHtml();
    }
}
