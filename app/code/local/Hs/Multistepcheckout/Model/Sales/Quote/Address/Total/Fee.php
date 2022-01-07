<?php
class Hs_Multistepcheckout_Model_Sales_Quote_Address_Total_Fee extends Mage_Sales_Model_Quote_Address_Total_Abstract{
    protected $_code = 'fee';
 
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);
        if (($address->getAddressType() == 'shipping')) {
            return $this;
        }
        
 
         $quote = $address->getQuote();
         if($quote->getDeliveryType()==2)
         {
            $amount ="5.95";
            
            $address->setFeeAmount($amount);
            $address->setBaseFeeAmount($amount);

            $address->setGrandTotal($address->getGrandTotal() + $address->getFeeAmount());
            $address->setBaseGrandTotal($address->getBaseGrandTotal() + $address->getBaseFeeAmount());
         }
             
        
        /*if ($amount) {
            $this->_addAmount($amount);
            $this->_addBaseAmount($amount);
        }*/
        
 
        //$this->_setAmount(0);
        //$this->_setBaseAmount(0);
 
//        $items = $this->_getAddressItems($address);
        /*if (!count($items)) {
            return $this; //this makes only address type shipping to come through
        }
 
 
        $quote = $address->getQuote();
 
        //if(Excellence_Fee_Model_Fee::canApply($address))
        { //your business logic
            $exist_amount = $quote->getFeeAmount();
            $fee = '5.95';//Excellence_Fee_Model_Fee::getFee();
            $balance = $fee - $exist_amount;
            $address->setFeeAmount($balance);
            $address->setBaseFeeAmount($balance);
                 
            $quote->setFeeAmount($balance);
 
            
        }*/
       
    }
 
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    { 
        if (($address->getAddressType() == 'billing')) {
            $amt = $address->getFeeAmount();
            if($amt){
                $address->addTotal(array(
                    'code'=>$this->getCode(),
                    'title'=>Mage::helper('multistepcheckout')->__('Fee'),
                    'value'=> $amt
                ));
            }
        }
        return $this;
    }
}
