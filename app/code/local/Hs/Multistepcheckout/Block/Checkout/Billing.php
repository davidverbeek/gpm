<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * One page checkout status
 *
 * @category   Mage
 * @category   Mage
 * @package    Mage_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Hs_Multistepcheckout_Block_Checkout_Billing extends Mage_Checkout_Block_Onepage_Billing
{
    /**
     * Sales Qoute Billing Address instance
     *
     * @var Mage_Sales_Model_Quote_Address
     */
    protected $_address;

    /**
     * Customer Taxvat Widget block
     *
     * @var Mage_Customer_Block_Widget_Taxvat
     */
    protected $_taxvat;

    /**
     * Initialize billing address step
     *
     */
    protected function _construct()
    {
        $this->getCheckout()->setStepData('billing', array(
            'label'     => Mage::helper('checkout')->__('Billing Information'),
            'is_show'   => $this->isShow()
        ));

        if ($this->isCustomerLoggedIn()) {
            $this->getCheckout()->setStepData('billing', 'allow', true);
        }
        parent::_construct();
    }
    
    public function getAddressesHtmlSelect($type)
    {
        if ($this->isCustomerLoggedIn()) {
            $options = array();
            foreach ($this->getCustomer()->getAddresses() as $address) {
                $options[] = array(
                    'value' => $address->getId(),
                    'label' => $address->format('oneline')
                );
            }

            $addressId = $this->getAddress()->getCustomerAddressId();
            if (empty($addressId)) {
                if ($type=='billing') {
                    $address = $this->getCustomer()->getPrimaryBillingAddress();
                } else {
                    $address = $this->getCustomer()->getPrimaryShippingAddress();
                }
                if ($address) {
                    $addressId = $address->getId();
                }
            }

            $select = $this->getLayout()->createBlock('core/html_select')
                ->setName($type.'_address_id')
                ->setId($type.'-address-select')
                ->setClass('address-select')
                ->setExtraParams('onchange="newAddress(!this.value); "')
                ->setValue($addressId)
                ->setOptions($options);

            $select->addOption('', Mage::helper('checkout')->__('New Address'));

            return $select->getHtml();
        }
        return '';
    }
    public function getAddressId($type='')
    {
         $addressId = $this->getAddress()->getCustomerAddressId();
            if (empty($addressId)) {
                if ($type=='billing') {
                    $address = $this->getCustomer()->getPrimaryBillingAddress();
                } else {
                    $address = $this->getCustomer()->getPrimaryShippingAddress();
                }
                if ($address) {
                    $addressId = $address->getId();
                }
            }
        return $addressId;
    }
    
    public function isUseBillingAddressForShipping()
    {
        if (($this->getQuote()->getIsVirtual())
            || !$this->getQuote()->getShippingAddress()->getSameAsBilling()) {
            return false;
        }
        return true;
    }

    /**
     * Return country collection
     *
     * @return Mage_Directory_Model_Mysql4_Country_Collection
     */
    public function getCountries()
    {
        return Mage::getResourceModel('directory/country_collection')->loadByStore();
    }

    /**
     * Return checkout method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->getQuote()->getCheckoutMethod();
    }

    /**
     * Return Sales Quote Address model
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getAddress()
    {
        if (is_null($this->_address)) {
            if ($this->isCustomerLoggedIn()) {
                $this->_address = $this->getQuote()->getBillingAddress();
                if(!$this->_address->getFirstname()) {
                    $this->_address->setFirstname($this->getQuote()->getCustomer()->getFirstname());
                }
                if(!$this->_address->getLastname()) {
                    $this->_address->setLastname($this->getQuote()->getCustomer()->getLastname());
                }
            } else {
                $this->_address = Mage::getModel('sales/quote_address');
            }
        }

        return $this->_address;
    }

    /**
     * Return Customer Address First Name
     * If Sales Quote Address First Name is not defined - return Customer First Name
     *
     * @return string
     */
    public function getFirstname()
    {
        $firstname = $this->getAddress()->getFirstname();
        if (empty($firstname) && $this->getQuote()->getCustomer()) {
            return $this->getQuote()->getCustomer()->getFirstname();
        }
        return $firstname;
    }

    /**
     * Return Customer Address Last Name
     * If Sales Quote Address Last Name is not defined - return Customer Last Name
     *
     * @return string
     */
    public function getLastname()
    {
        $lastname = $this->getAddress()->getLastname();
        if (empty($lastname) && $this->getQuote()->getCustomer()) {
            return $this->getQuote()->getCustomer()->getLastname();
        }
        return $lastname;
    }

    /**
     * Check is Quote items can ship to
     *
     * @return boolean
     */
    public function canShip()
    {
        return !$this->getQuote()->isVirtual();
    }

    public function getSaveUrl()
    {
    }

    /**
     * Get Customer Taxvat Widget block
     *
     * @return Mage_Customer_Block_Widget_Taxvat
     */
    protected function _getTaxvat()
    {
        if (!$this->_taxvat) {
            $this->_taxvat = $this->getLayout()->createBlock('customer/widget_taxvat');
        }

        return $this->_taxvat;
    }

    /**
     * Check whether taxvat is enabled
     *
     * @return bool
     */
    public function isTaxvatEnabled()
    {
        return $this->_getTaxvat()->isEnabled();
    }

    public function getTaxvatHtml()
    {
        return $this->_getTaxvat()
            ->setTaxvat($this->getQuote()->getCustomerTaxvat())
            ->setFieldIdFormat('billing:%s')
            ->setFieldNameFormat('billing[%s]')
            ->toHtml();
    }
    
    public function getCallingCode(){
        $timeout = 3000;
        $countryList = Mage::getModel('directory/country')->getResourceCollection()
                            ->loadByStore()
                            ->toOptionArray(true);
        
        foreach($countryList as $_country){
            if($_country['value'])
                $countryCode[]=$_country['value'];
        }
        $newCountryCode=  implode($countryCode, ";");
        
        $url = "https://restcountries.eu/rest/v1/alpha?codes=".$newCountryCode;
                
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1" );
        curl_setopt( $ch, CURLOPT_URL, $url );
        // curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookie );
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt( $ch, CURLOPT_ENCODING, "" );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
        curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout );
        curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
        $content = curl_exec( $ch );
        curl_close ( $ch );
        
        return Mage::helper('core')->jsonEncode($content);
    }
    
    public function getCountryHtmlSelect($type)
    {
        $countryId = $this->getAddress()->getCountryId();
        if (is_null($countryId)) {
            $countryId = Mage::helper('core')->getDefaultCountry();
        }
        $select = $this->getLayout()->createBlock('core/html_select')
            ->setName($type.'[country_id]')
            ->setId($type.':country_id')
            ->setTitle(Mage::helper('checkout')->__('Country'))
            ->setClass('validate-select')
            ->setValue($countryId)
            ->setOptions($this->getCountryOptions());
        if ($type === 'shipping') {
            $select->setExtraParams('onchange="if(window.shipping)shipping.setSameAsBilling(false);"');
        }
        $select->setExtraParams('onchange="changeCallingCode(this.value)"');
        
        
        return $select->getHtml();
    }
    
}    
