<?php

class Helios_Gtm_Helper_Data extends Mage_Core_Helper_Abstract
{
    const NEW_RELIC_BROWSER_SNIPPET = 'config/new_relic/browser_snippet';

    /**
     * To get gtm settings enable or not.
     */
    public function getEnabled()
    {
        return Mage::getStoreConfig('config/settings/enabled', Mage::app()->getStore());
    }

    /**
     * To get gtm code.
     */
    public function getGtmCode()
    {
        return Mage::getStoreConfig('config/settings/gtm_code', Mage::app()->getStore());
    }

    /**
     * @return string containing javascirpt for including new relic browser snippet
     */
    public function getNewRelicBrowserSnippet()
    {

        return Mage::getStoreConfig(self::NEW_RELIC_BROWSER_SNIPPET, Mage::app()->getStore());
    }

    /**
     * To get Google Ad settings enable or not.
     */
    public function getGoogleAdsEnabled()
    {
        return Mage::getStoreConfig('config/google_ads/enabled_google_ads', Mage::app()->getStore());
    }

    /**
     * To get Google Ads code.
     */
    public function getGoogleAdsCode()
    {
        return Mage::getStoreConfig('config/google_ads/google_ads_code', Mage::app()->getStore());
    }

    /**
     * To get Google Ads code 2.
     */
    public function getGoogleAdsCode2()
    {
        return Mage::getStoreConfig('config/google_ads/google_ads_code_2', Mage::app()->getStore());
    }

    /**
     * To get Google Ads label.
     */
    public function getGoogleAdsLabel()
    {
        return Mage::getStoreConfig('config/google_ads/google_ads_label', Mage::app()->getStore());
    }

    /**
     * To get Google Ads label 2.
     */
    public function getGoogleAdsLabel2()
    {
        return Mage::getStoreConfig('config/google_ads/google_ads_label_2', Mage::app()->getStore());
    }

    /**
     * To get Google Maps API Key.
     */
    public function getGMapApiKey()
    {
        return Mage::getStoreConfig('config/google_maps/google_maps_key', Mage::app()->getStore());
    }

    /**
     * To get Google reCAPTCHA Site Key.
     */
    public function getreCAPTCHAKey()
    {
        return Mage::getStoreConfig('config/google_recaptcha/google_recaptcha_key', Mage::app()->getStore());
    }
    /**
     * To get Google reCAPTCHA Site Secret Key.
     */
    public function getreCAPTCHASecretKey()
    {
        return Mage::getStoreConfig('config/google_recaptcha/google_recaptcha_secret_key', Mage::app()->getStore());
    }
}