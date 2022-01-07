<?php

/**
 * Class Hs_Transsmartadjustment_Helper_Data
 * @category    Transsmart
 * @package     Hs_Transsmartadjustment
 * @author      Hs_SJD
 * @date        05 June, 2018
 *
 */
class Hs_Transsmartadjustment_Helper_Data extends Mage_Core_Helper_Abstract
{
    // Adjustent second user configurations value
    const XML_PATH_CONNECTION_USERNAME_2 = 'transsmart_shipping/connection_2/username_2';
    const XML_PATH_CONNECTION_PASSWORD_2 = 'transsmart_shipping/connection_2/password_2';

    /**
     * @var Transsmart_Shipping_Model_Client
     */
    protected $_apiClient;

    /**
     * Get the Transsmart API client, and initialize it with the configured authentication details. The same instance
     * will be returned for subsequent calls. Configuration settings are always read from the global scope, as there
     * can be only one Transsmart account per Magento installation.
     *
     * @return Transsmart_Shipping_Model_Client
     */
    public function getApiClientUser2()
    {
        if (is_null($this->_apiClient)) {
            // collect configuration settings for second user
            $username = Mage::getStoreConfig(self::XML_PATH_CONNECTION_USERNAME_2, 0);
            $password = Mage::getStoreConfig(self::XML_PATH_CONNECTION_PASSWORD_2, 0);
            $environment = Mage::getStoreConfig(Transsmart_Shipping_Helper_Data::XML_PATH_CONNECTION_ENVIRONMENT, 0);

            // check if username and password are specified
            if (empty($username)) {
                Mage::throwException('No Transsmart connection username is configured');
            }
            if (empty($password)) {
                Mage::throwException('No Transsmart connection password is configured');
            }

            // use environment setting to determine url
            if ($environment == Transsmart_Shipping_Model_Adminhtml_System_Config_Source_Environment::STAGING) {
                $url = 'https://connect.test.api.transwise.eu';
            } elseif ($environment == Transsmart_Shipping_Model_Adminhtml_System_Config_Source_Environment::PRODUCTION) {
                $url = 'https://connect.api.transwise.eu';
            } else {
                Mage::throwException('An invalid Transsmart connection environment is configured');
            }

            // instantiate API client model
            $this->_apiClient = Mage::getModel('transsmart_shipping/client', array(
                'url' => $url,
                'username' => $username,
                'password' => $password
            ));
        }

        return $this->_apiClient;
    }
}