<?php
/**
 * GDPR cookie notice block
 *
 * @category   Scommerce
 * @package    Scommerce_GDPR
 * @author     Scommerce Mage <core@scommerce-mage.co.uk>
 */
class Scommerce_GDPR_Block_Cookienotice extends Mage_Core_Block_Template
{
    /**
     * "Disable" block if module disabled or module has wrong license
     *
     * @return string
     */
    protected function _toHtml()
    {
        return ($this->h()->isEnabled() && $this->isCookieEnabled() && ! $this->hasCookie()) ?
            parent::_toHtml() : '';
    }

    /**
     * Is cookie enabled
     *
     * @return bool
     */
    protected function isCookieEnabled()
    {
        return $this->h()->isCookieEnabled();
    }

    /**
     * Is blocked enable
     *
     * @return bool
     */
    protected function isBlocked()
    {
        return $this->h()->isBlocked();
    }

    /**
     * @return string
     */
    protected function getCmsPageUrl()
    {
        return $this->h()->getCmsPageUrl();
    }

    /**
     * @return string
     */
    protected function getCookieTextMessage()
    {
        return $this->h()->getCookieTextMessage();
    }

    /**
     * @return string
     */
    protected function getCookieLinkText()
    {
        return $this->h()->getCookieLinkText();
    }

    /**
     * @return string
     */
    protected function getCookieTextColor()
    {
        return $this->h()->getCookieTextColor();
    }

    /**
     * @return string
     */
    protected function getCookieBackgroundColor()
    {
        return $this->h()->getCookieBackgroundColor();
    }

    /**
     * @return string
     */
    protected function getCookieLinkColor()
    {
        return $this->h()->getCookieLinkColor();
    }

    /**
     * Get cookie key to check accepted cookie policy
     *
     * @return string
     */
    protected function getCookieKey()
    {
        return $this->h()->getCookieKey();
    }
	
	/**
	 * Return main div wrapper id or class
     * @return string
     */
    protected function getBlockIdorClass()
    {
        return $this->h()->getBlockIdorClass();
    }
	
	/**
     * Get cookie key to check if cookie message was closed
     *
     * @return string
     */
    protected function getCookieClosedKey()
    {
        return $this->h()->getCookieClosedKey();
    }

    /**
     * Check if has cookie with accepted cookie policy
     *
     * @return bool
     */
    protected function hasCookie()
    {
        return Mage::getModel('core/cookie')->get($this->getCookieKey());
    }

    /**
     * Helper method for getting helper instance
     *
     * @return Scommerce_GDPR_Helper_Data
     */
    private function h()
    {
        return Mage::helper("scommerce_gdpr");
    }
}
