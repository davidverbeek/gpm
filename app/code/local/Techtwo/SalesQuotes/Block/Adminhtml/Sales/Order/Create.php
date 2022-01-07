<?php

class Techtwo_SalesQuotes_Block_Adminhtml_Sales_Order_Create extends Mage_Adminhtml_Block_Sales_Order_Create
{
    public function __construct()
    {
        parent::__construct();

        // update save-button label for quotes
        if ($this->_getSession()->getQuote()->getIsCartAuto() != 1) {
            $this->_updateButton('save', 'label', Mage::helper('techtwo_salesquotes')->__('Save quote/order'));
        }
    }
}
