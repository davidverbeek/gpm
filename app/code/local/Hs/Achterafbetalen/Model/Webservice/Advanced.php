<?php

/**
 *  ICEPAY Advanced - Webservice
 *
 * @version 1.0.1
 * @author Wouter van Tilburg <wouter@icepay.eu>
 * @copyright ICEPAY <www.icepay.com>
 *
 *  Disclaimer:
 *  The merchant is entitled to change de ICEPAY plug-in code,
 *  any changes will be at merchant's own risk.
 *  Requesting ICEPAY support for a modified plug-in will be
 *  charged in accordance with the standard ICEPAY tariffs.
 *
 */
class Hs_Achterafbetalen_Model_Webservice_Advanced extends Icepay_IceAdvanced_Model_Webservice_Advanced
{

    private $extendedCheckoutMethods = array('ACHTERAFBETALEN');

    /**
     * Retrieve and return merchant's paymentmethods
     *
     * @since 1.0.0
     * @return object
     */
    public function getMyPaymentMethods()
    {
        $obj = new stdClass();

        $obj->MerchantID = $this->getMerchantID();
        $obj->SecretCode = $this->getSecretCode();
        $obj->Timestamp = $this->getTimeStamp();
        $obj->Checksum = $this->generateChecksum($obj);

        return $this->client->GetMyPaymentMethods(array('request' => $obj));
    }

    /**
     * Create transaction and return result
     *
     * @param object $paymentObj
     *
     * @since 1.0.0
     * @return array
     */
    public function doCheckout($paymentObj, $orderObj = null)
    {
        $obj = new StdClass();

        $obj->MerchantID = $this->getMerchantID();
        $obj->Timestamp = $this->getTimeStamp();
        $obj->Amount = $paymentObj->getAmount();
        $obj->Country = $paymentObj->getCountry();
        $obj->Currency = $paymentObj->getCurrency();
        $obj->Description = $paymentObj->getDescription();
        $obj->EndUserIP = $this->getIP();
        $obj->Issuer = $paymentObj->getPaymentMethodIssuer();
        $obj->Language = $paymentObj->getLanguage();
        $obj->OrderID = $paymentObj->getOrderID();
        $obj->PaymentMethod = $paymentObj->getPaymentMethod();
        $obj->Reference = $paymentObj->getReference();
        $obj->URLCompleted = '';
        $obj->URLError = '';

        $obj->XML = $orderObj->getAchterafbetalenXML();
        $obj->Checksum = $this->generateChecksum($obj, $this->getSecretCode());
//
//        header("Content-type: text/xml");
//        print $obj->XML;die;

        Mage::log($obj,null,'ice.log',true);

        //Mage::log('This is working',null,'ice_response.log',true);
        $result = $this->client->CheckoutExtended(array('request' => $obj));

        Mage::log(print_r($result,true),null,'ice_response.log',true);

        return (array)$result;

    }

    /**
     * Checks if paymentmethod requires extended checkout
     *
     * @param object $paymentMethod
     *
     * @since 1.0.0
     * @return bool
     */
    public function isExtendedCheckout($paymentMethod)
    {
        $paymentMethod = strtoupper($paymentMethod);

        return (bool)in_array($paymentMethod, $this->extendedCheckoutMethods);
    }

}
