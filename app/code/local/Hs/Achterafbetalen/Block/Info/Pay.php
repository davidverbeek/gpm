<?php
class Hs_Achterafbetalen_Block_Info_Pay extends Mage_Payment_Block_Info
{
    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }

        $transport = new Varien_Object();
        $transport = parent::_prepareSpecificInformation($transport);

        return $transport;
    }
}