<?php

class Techtwo_SalesQuotes_Adminhtml_SalesquotesController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {   
        /*$collection=Mage::getModel('pws_productregistration/productregistration')
            ->getCollection()->joinCustomers()->joinProducts();
        print($collection->getSelect()->__toString());  
          */
          
          
          //$quoteId = 53;
        //$quote = Mage::getModel('sales/quote')->setStoreId(1)->load($quoteId);
       
        //Mage::helper('techtwo_salesquotes')->sendNewQuoteEmail($quote);
          
        $this->loadLayout();

        $this->_setActiveMenu('sales/techtwo_salesquotes');
        $this->_addBreadcrumb(Mage::helper('techtwo_salesquotes')->__('Quotes'), Mage::helper('techtwo_salesquotes')->__('Quotes'));
        $this->_addContent($this->getLayout()->createBlock('techtwo_salesquotes/adminhtml_salesQuotes_list'));

        $this->renderLayout();
    }
    
    
    public function newAction()
    {
        // init quote edit session
        Mage::getSingleton('adminhtml/session_quote')
                ->clear()
                ->setConvertOrderQuote(-1);

        $this->_redirect('*/sales_order_create/');
    }


    public function editAction()
    {
        if(!(int)$this->getRequest()->getParam('quote_id')) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('techtwo_salesquotes')->__('Please select a quote'));
            $this->_redirect('*/*/');
            return;
        }

        //because I need the store id to load the quote directly, I didnt' figure out how to pass it in grid column
        $quoteCollection = Mage::getModel('sales/quote')->getCollection()
                ->addFieldToFilter('is_cart_auto',0)
                ->addFieldToFilter('entity_id',$this->getRequest()->getParam('quote_id'));
        if(!$quoteCollection->getSize()) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('techtwo_salesquotes')->__('Quote with id %s was not found',$this->getRequest()->getParam('quote_id'))
                );
            $this->_redirect('*/*/');
            return;
        };
        $quote = $quoteCollection->getFirstItem();

        // check status
        if ($quote->getStatus() == 'converted') {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('techtwo_salesquotes')->__('Quote %s is already converted and cannot be edited',$this->getRequest()->getParam('quote_id'))
            );
            $this->_redirect('*/*/');
            return;
        }

        // init quote edit session
        Mage::getSingleton('adminhtml/session_quote')
                ->setQuoteId($quote->getId())
                ->setCustomerId($quote->getCustomerId())
                ->setStoreId($quote->getStoreId())
                ->setCurrencyId((string) $quote->getQuoteCurrencyCode())
                ->setConvertOrderQuote($quote->getId());

        $this->_redirect('*/sales_order_create/');
    }

    
    public function updateStatusAction()
    {
        $status = $this->getRequest()->getParam('status');
        if (!in_array($status, array('open', 'pending', 'confirmed', 'converted'))) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('techtwo_salesquotes')->__('Please select a valid quote status'));
            $this->_redirect('*/*/');
            return;
        }

        if(!($quoteIds = $this->getRequest()->getPost('quote_id'))){
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('techtwo_salesquotes')->__('Please select at least one quote'));
            $this->_redirect('*/*/');
            return;
        }

        $quoteCollection = Mage::getModel('sales/quote')->getCollection()->addFieldToFilter('entity_id',array('in'=>$quoteIds));

        if(!$quoteCollection->getSize()) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('techtwo_salesquotes')->__('Quote with id %s was not found',implode(',',$quoteIds))
                );
            $this->_redirect('*/*/');
            return;
        };

        foreach($quoteCollection as $quote){
            //if ($quote->getStatus() != $status) {
                $quote->setStatus($status);
                $quote->save();

                if ($status == 'pending') {
                    Mage::helper('techtwo_salesquotes')->sendNewQuoteThankyouEmail($quote);
                }
            //}
        }

        if ($status == 'confirmed') {
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('techtwo_salesquotes')->__('%s quote(s) confirmed',$quoteCollection->getSize()));
        }
        else {
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('techtwo_salesquotes')->__('Status updated for %s quote(s)',$quoteCollection->getSize()));
        }
        $this->_redirect('*/*/');
    }
    

//    public function testAction()
//    {
//          echo 'this is the quote page!';
//
//        //mail('ada80ro@gmail.com', 'My Subject', 'just testing');
//
//        $quoteId = 53;
//        $quote = Mage::getModel('sales/quote')->setStoreId(1)->load($quoteId);
//       
//        //Mage::helper('techtwo_salesquotes')->sendNewQuoteEmail($quote);
//
//        $orderItemsBlock = $this->getLayout()->createBlock('sales/order_email_items','sales_order_email_items')
//                            ->setTemplate('salesquotes/email/items.phtml')
//                            ->setQuote($quote);
//        var_dump($orderItemsBlock);
//    }

}
