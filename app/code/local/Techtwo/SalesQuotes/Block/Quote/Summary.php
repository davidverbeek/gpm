<?php

class Techtwo_SalesQuotes_Block_Quote_Summary extends Mage_Sales_Block_Items_Abstract
{
    public function getItems()
    {
        return Mage::getSingleton('checkout/session')->getQuote()->getAllVisibleItems();
    }

    public function getTotals()
    {
        return Mage::getSingleton('checkout/session')->getQuote()->getTotals();
    }
}
