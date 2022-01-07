<?php

class Hs_Multistepcheckout_Model_Sales_Quote_Address_Total_Samedaydeliveryfee extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    protected $_code = 'sdd';

    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);

        $amount = Mage::helper('multistepcheckout')->getSddFromConfig();

        $isSddEnabled = Mage::getSingleton('checkout/session')->getData('sdd_enabled');

        if (empty($isSddEnabled) || $this->canApply($address) == false) {
            $amount = 0;
            Mage::getSingleton('checkout/session')->setData('sdd_enabled', '');
        }

        //$items = $this->_getAddressItems($address);


        if ($address->getAddressType() == 'billing') {
            return $this; //this makes only address type shipping to come through
        }


        $this->_setAmount($amount);
        $this->_setBaseAmount($amount);


        $address->setSddAmount($amount);
        $address->setBaseSddAmount($amount);

        $address->setGrandTotal($address->getGrandTotal() + $address->getSddAmount());
        $address->setBaseGrandTotal($address->getBaseGrandTotal() + $address->getBaseSddAmount());

        return $this;
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $amt = $address->getSddAmount();
        if ($amt > 0) {
            $address->addTotal(array(
                'code' => $this->getCode(),
                'title' => Mage::helper('multistepcheckout')->__('Extra shipmentcost'),
                'value' => $amt
            ));
        }

        return $address;
    }

    public function canApply($shippingAddress)
    {
        //logic for the same day delivery validation

        return Mage::helper('multistepcheckout')->checkSdd($shippingAddress->getQuote());

    }

}
