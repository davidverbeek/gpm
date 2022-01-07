<?php

class Hs_Achterafbetalen_Block_Form_Pay extends Mage_Payment_Block_Form
{

    protected $_methodCode = 'achterafbetalen';

    public $_code;
    public $_issuer;
    public $_model;
    public $_countryArr = null;
    public $_country;

    protected function _construct()
    {
//        parent::_construct();

        $this->setTemplate('achterafbetalen/form/pay.phtml');
        $this->model = Mage::getModel('achterafbetalen/standard');
        parent::_construct();
    }

    /**
     * Payment method code getter
     * @return string
     */
    public function getMethodCode()
    {
        return $this->_methodCode;
    }



}
