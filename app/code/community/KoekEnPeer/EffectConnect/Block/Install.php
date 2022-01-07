<?php

    class KoekEnPeer_EffectConnect_Block_Install extends Mage_Adminhtml_Block_System_Config_Form_Field
    {
        protected $storeUrl;
        protected $storeName;
        protected $storeCountry;
        protected $storeLanguage;
        protected $storeEmail;
        protected $storePhone;
        protected $storeAddress;

        protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
        {
            $this->setElement($element);

            $defaultStoreId = Mage::app()
                ->getWebsite(true)
                ->getDefaultGroup()
                ->getDefaultStoreId()
            ;

            Mage::app()
                ->setCurrentStore($defaultStoreId)
            ;

            $this->storeUrl      = $this->_getStoreUrl();
            $this->storeName     = $this->_getStoreName();
            $this->storeCountry  = $this->_getStoreCountry();
            $this->storeLanguage = $this->_getStoreLanguage();
            $this->storeEmail    = $this->_getStoreEmail();
            $this->storePhone    = $this->_getStorePhone();
            $this->storeAddress  = $this->_getStoreAddress();

            $timestamp     = time();

            $data          = array(
                'website'   => $this->storeUrl,
                'company'   => $this->storeName,
                'email'     => $this->storeEmail,
                'phone'     => $this->storePhone,
                'country'   => $this->storeCountry,
                'language'  => $this->storeLanguage,
                'timestamp' => $timestamp,
                'return'    => $this->getUrl('adminhtml/effectconnectconnect/index'),
                'hash'      => hash_hmac('sha256', strrev($this->storeUrl.$timestamp), 'effectconnectMagento')
            );

            $addressData = $this->_convertStoreAddress();
            if ($addressData)
            {
               $data+=$addressData;
            }

            $url  = KoekEnPeer_EffectConnect_Helper_Data::APP_URL.'install/magento?'.http_build_query($data);
            $html = $this->getLayout()
                ->createBlock('adminhtml/widget_button')
                ->setType('button')
                ->setClass('scalable')
                ->setLabel('Start installation')
                ->setOnClick('window.open(\''.$url.'\');')
                ->toHtml()
            ;

            return $html;
        }

        protected function _getStoreUrl()
        {
            return Mage::helper('effectconnect')->getBaseUrl();
        }

        protected function _getStoreName()
        {
            $storeName = Mage::getStoreConfig('general/store_information/name');
            if ($storeName)
            {
                return $storeName;
            }
            $storeName = $storeName = Mage::getStoreConfig('trans_email/ident_general/name');
            if ($storeName)
            {
                return $storeName;
            }
            $storeName = Mage::app()
                ->getStore()
                ->getName()
            ;

            return $storeName;
        }

        protected function _getStoreCountry()
        {
            return Mage::getStoreConfig('general/store_information/merchant_country');
        }

        protected function _getStoreLanguage()
        {
            $localeCode = Mage::app()
                ->getLocale()
                ->getLocaleCode()
            ;

            return current(
                explode(
                    '_',
                    $localeCode
                )
            );
        }

        protected function _getStoreEmail(){
            return Mage::getStoreConfig('trans_email/ident_general/email');
        }

        protected function _getStorePhone(){
            return Mage::getStoreConfig('general/store_information/phone');
        }

        protected function _getStoreAddress(){
            return Mage::getStoreConfig('general/store_information/address');
        }

        protected function _convertStoreAddress(){
            if (!$this->storeAddress)
            {
                return false;
            }

            $googleMapsParamaters = array(
                'address'  => urlencode($this->storeAddress),
                'language' => $this->storeLanguage,
                'region '  => $this->storeCountry,
                'sensor'   => 'true'
            );

            $storeAddressData     = json_decode(
                file_get_contents(
                    'http://maps.googleapis.com/maps/api/geocode/json?'.http_build_query($googleMapsParamaters)
                ), true
            );

            if(!$storeAddressData)
            {
                return false;
            }

            if (!isset($storeAddressData['results'][0]['address_components']))
            {
                return false;
            }

            $addressData = array();
            foreach ($storeAddressData['results'][0]['address_components'] as $addressComponent)
            {
                $addressComponentValue = $addressComponent['long_name'];
                $key                   = false;
                switch ($addressComponent['types'][0])
                {
                    case 'route':
                        $key = 'street';
                        break;
                    case 'street_number':
                        $key = 'street_number';
                        break;
                    case 'postal_code':
                        $key = 'zip_code';
                        break;
                    case 'locality':
                        $key = 'city';
                        break;
                    case 'country':
                        $key                   = 'country';
                        $addressComponentValue = $addressComponent['short_name'];
                        break;
                }
                if ($key && !isset($addressData[$key]))
                {
                    $addressData[$key] = $addressComponentValue;
                }
            }

            if (!empty($addressData))
            {
                return $addressData;
            }

            return false;
        }
    }