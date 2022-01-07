<?php

class Techtwo_SalesQuotes_Block_Customer_Quotes extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('salesquotes/customer/quotes.phtml');
        
        $customer_id = Mage::getSingleton('customer/session')->getCustomer()->getId();
        $quotesCollection = Mage::getModel('sales/quote')->getCollection()->addFieldToFilter('customer_id',$customer_id)
                                ->addFieldToFilter('is_cart_auto',0)
                                ->setOrder('updated_at','DESC')
                                ->setOrder('entity_id','DESC');
            

        $this->setQuotes($quotesCollection);
        Mage::app()->getFrontController()->getAction()->getLayout()->getBlock('root')->setHeaderTitle(Mage::helper('techtwo_salesquotes')->__('My Quotes'));

    }

    protected function _prepareLayout()
    { 
        parent::_prepareLayout();

        $pager = $this->getLayout()->createBlock('page/html_pager', 'salesquotes.customer.quotes.pager')
            ->setCollection($this->getQuotes());
        $this->setChild('pager', $pager);
        $this->getQuotes()->load();        
        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }   

    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }
}
