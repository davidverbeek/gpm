<?php

class Techtwo_SalesQuotes_Helper_Data extends Mage_Core_Helper_Abstract
{

    const XML_PATH_QUOTE_CUSTOMER_GROUP = 'techtwo_salesquotes/quotes_settings/customer_group';
    const XML_PATH_QUOTE_NOTIFICATION_EMAIL = 'techtwo_salesquotes/quotes_settings/notification_email';
    const XML_PATH_NEW_QUOTE_EMAIL_TEMPLATE = 'sales_email/quote/template';
    const XML_PATH_NEW_QUOTE_THANKYOU_EMAIL_TEMPLATE = 'sales_email/quote_thankyou/template';
    const XML_PATH_NEW_QUOTE_NOTIFICATION_EMAIL_TEMPLATE = 'sales_email/quote_notification/template';


    /**
     * Customers from groups with quotes only are not allowed to checkout
     */
    public function isAllowedCheckout()
    {
        //var_dump(Mage::getSingleton('checkout/session')->getQuote()->getData('customer_group_id'));exit;
        if(Mage::getSingleton('checkout/cart')->getQuote() && 
            ($customer_group_id = Mage::getSingleton('checkout/cart')->getQuote()->getData('customer_group_id'))!==FALSE){
            if (!is_null($customer_group_id)) {
                $customer_groups = explode(',', Mage::getStoreConfig(self::XML_PATH_QUOTE_CUSTOMER_GROUP));
                if(in_array($customer_group_id,$customer_groups)) return false;
            }
        }
        return true;
    }

    public function getQuoteUrl()
    {
        return Mage::getUrl('salesquotes/quote/index');
    }

    public function getConfirmQuoteUrl($quote_id, $store = null, $nosid = false)
    {
        $params = array('_secure' => true,'quote_id' => $quote_id, '_store' => (int)$store);
        if ($nosid) $params['_nosid'] = true;
        return Mage::getUrl('salesquotes/quote/confirmQuote/', $params);
    }

    public function getCheckoutQuoteUrl($quote_id)
    {
        return Mage::getUrl('salesquotes/quote/checkoutQuote/',array('_secure' => true,'quote_id' => $quote_id));
    }

    public function sendNewQuoteEmail($quote)
    {
        //$store_id = Mage::app()->getStore()->getId();
        //$store_id = 1;
        $store_id = $quote->getStoreId();
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

       
        $mailTemplate = Mage::getModel('core/email_template');
       
        $template = 9;//Mage::getStoreConfig(self::XML_PATH_NEW_QUOTE_EMAIL_TEMPLATE, $store_id);

        $sendTo = array(
            array(
                'email' => $quote->getCustomerEmail(),
                'name'  => $quote->getCustomerFirstname().' '.$quote->getCustomerLastname()
            )
        );
        
        $orderItemsBlock = Mage::getModel('core/layout')->createBlock('sales/order_email_items','sales_order_email_items')
                                ->setTemplate('salesquotes/email/quote/items.phtml')
                                ->setQuote($quote);
        
		$orderTotalsBlock = Mage::getModel('core/layout')->createBlock('sales/order_email_items','sales_order_email_items')
                        ->setTemplate('salesquotes/email/quote/totals.phtml')
                        ->setQuote($quote);
        //echo "<pre>";  print_r($orderTotalsBlock->toHtml()); 
        //exit;

		
        foreach ($sendTo as $recipient) {
            $mailTemplate->setDesignConfig(array('area'=>'frontend', 'store'=>$store_id))
                ->sendTransactional(
                    $template,
                    'sales',
                    $recipient['email'],
                    $recipient['name'],
                    array(
                        'quote'             => $quote,
                        'quote_confirm_url' => $this->getConfirmQuoteUrl($quote->getId(), $store_id, true),
                        'quote_items_html'  => $orderItemsBlock->toHtml(),
						'quote_totals_html'   => $orderTotalsBlock->toHtml(),
                    )
                );
        }
	
		//echo "<pre>";
		//print_r($mailTemplate);
		//exit;
		
        $translate->setTranslateInline(true);

        return;
    }


    public function sendNewQuoteThankyouEmail($quote)
    {
        $store_id = $quote->getStoreId();
        //$store_id = Mage::app()->getStore()->getId();
        
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

       
        $mailTemplate = Mage::getModel('core/email_template');
       
        $template = 12;//Mage::getStoreConfig(self::XML_PATH_NEW_QUOTE_THANKYOU_EMAIL_TEMPLATE, $store_id);

        $sendTo = array(
            array(
                'email' => $quote->getCustomerEmail(),
                'name'  => $quote->getCustomerFirstname().' '.$quote->getCustomerLastname()
            )
        );

        $orderItemsBlock = Mage::getModel('core/layout')->createBlock('sales/order_email_items','sales_order_email_items')
                ->setTemplate('salesquotes/email/quote/items.phtml')
                ->setQuote($quote);

        $orderTotalsBlock = Mage::getModel('core/layout')->createBlock('sales/order_email_items','sales_order_email_items')
                        ->setTemplate('salesquotes/email/quote/totals.phtml')
                        ->setQuote($quote);

        foreach ($sendTo as $recipient) {
            $mailTemplate->setDesignConfig(array('area'=>'frontend', 'store'=>$store_id))
                ->sendTransactional(
                    $template,
                    'sales',
                    $recipient['email'],
                    $recipient['name'],
                    array(
                        'quote'               => $quote,
                        'quote_checkout_url'  => $this->getCheckoutQuoteUrl($quote->getId()),
                        'quote_confirm_url'   => $this->getConfirmQuoteUrl($quote->getId()),
                        'quote_items_html'    => $orderItemsBlock->toHtml(),
                        'quote_totals_html'   => $orderTotalsBlock->toHtml(),
                        'checkout_img'        => Mage::getDesign()->getSkinUrl('images/buttons/email-button-checkout.gif')
                    )
                );
        }

        $translate->setTranslateInline(true);
        return;
    }

    
    public function sendQuoteNotificationEmail($quote)
    {
        $store_id = Mage::app()->getStore()->getId();
        
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

       
        $mailTemplate = Mage::getModel('core/email_template');
       
        $template = 10;//Mage::getStoreConfig(self::XML_PATH_NEW_QUOTE_NOTIFICATION_EMAIL_TEMPLATE, $store_id);

        $sendTo = array(
            array(
                'email' => Mage::getStoreConfig(self::XML_PATH_QUOTE_NOTIFICATION_EMAIL),
                'name'  => 'Admin'
            )
        );

        //var_dump($quote->getStore()->getName());

        foreach ($sendTo as $recipient) {
            $mailTemplate->setDesignConfig(array('area'=>'frontend', 'store'=>$store_id))
                ->sendTransactional(
                    $template,
                    'sales',
                    $recipient['email'],
                    $recipient['name'],
                    array(
                        'quote' => $quote
                    )
                );
        }

        $translate->setTranslateInline(true);

        return;
    }

}
