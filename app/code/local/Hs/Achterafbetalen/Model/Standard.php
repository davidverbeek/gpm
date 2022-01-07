<?php

class Hs_Achterafbetalen_Model_Standard extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 'achterafbetalen';
    protected $_canOrder = true;
    protected $_isInitializeNeeded = true;
    protected $_canUseInternal = true;
    protected $_canUseForMultishipping = true;
    protected $_canUseCheckout = true;


    protected $_infoBlockType = 'achterafbetalen/info_pay';

    //protected $_formBlockType = 'achterafbetalen/form';

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->_code;
    }

    /**
     * Return Order place redirect url
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        //when you click on place order you will be redirected on this url, if you don't want this action remove this method
        return Mage::getUrl('achterafbetalen/index/pay', array('_secure' => true));
    }

    public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }

        return $this;
    }


    public function validate()
    {
        parent::validate();

        //$info = $this->getInfoInstance();

        $coreSession = Mage::getSingleton('checkout/session');

        $selectedPayment = $coreSession->getSelectedPayment();

        $acNo = $selectedPayment['account_no'];
        $phoneNo = $selectedPayment['mobile_no'];
        $birthDate = $selectedPayment['dob'];
        $gender = $selectedPayment['gender'];


        if (empty($acNo) || empty($phoneNo) || empty($birthDate) || empty($gender)) {
            //$errorCode = 'invalid_data';
            $errorMsg = $this->_getHelper()->__('Check Gender,Account No,Phone No and Birth Date are required fields');
        }


        //gender validation logic start here
        if (strtoupper($gender) != 'M' && strtoupper($gender) != 'F') {

            $errorMsg = $this->_getHelper()->__('Invalid Gender Selection');
        }

        //validation pattern for the Account number
        $ibanPattern = '/^[A-Z]{2,2}[0-9]{2,2}[a-zA-Z0-9]{1,30}$/';

        if (!preg_match($ibanPattern, $acNo)) {
            $errorMsg = $this->_getHelper()->__('Invalid Account Number!');
        }

        //birth date should be in YYYY-MM-DD format
        if (!$this->validateDate($birthDate)) {
            $errorMsg = $this->_getHelper()->__('Invalid BirthDate. Birthdate must be in YYYY-MM-DD format!');

        }

        //phone no validation
        if (!Mage::Helper('iceadvanced')->validatePhonenumber('nl', $phoneNo))
            $errorMsg = Mage::helper('iceadvanced')->__('It seems your billing address is incorrect, please confirm the phonenumber.');


        if ($errorMsg) {
            Mage::throwException($errorMsg);
        }
        return $this;
    }

    /*
     * function: this function is used to validate birthdate
     * Name: validateDate
     * @return: boolean
     */
    public function validateDate($date, $format = 'Y-m-d')
    {

        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

}