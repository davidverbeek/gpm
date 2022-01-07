<?php
class Hs_Reports_Model_Observer
{
	public function salesQuoteItemSetPrices(Varien_Event_Observer $observer)
	{
        $quoteItem = $observer->getQuoteItem();
        $product = $observer->getProduct();
        $quoteItem->setData('afwijkenidealeverpakking',$product->getAfwijkenidealeverpakking());
        $quoteItem->setData('idealeverpakking',$product->getIdealeverpakking());
        $quoteItem->setData('prijsfactor',$product->getPrijsfactor());
        $quoteItem->setData('manualproduct',$product->getManualproduct());
        return $quoteItem;
	}
}
