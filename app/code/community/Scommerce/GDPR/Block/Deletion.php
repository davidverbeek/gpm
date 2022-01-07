<?php
/**
 * @category   Scommerce
 * @package    Scommerce_GDPR
 * @author     Scommerce Mage <core@scommerce-mage.co.uk>
 */
class Scommerce_GDPR_Block_Deletion extends Mage_Core_Block_Template
{
    /**
     * "Disable" block if module disabled or module has wrong license
     *
     * @return string
     */
    protected function _toHtml()
    {
        return $this->h()->isDeletionEnabledOnFrontend() ? parent::_toHtml() : '';
    }

    /**
     * Get attention message
     *
     * @return string
     */
    protected function getAttentionMessage()
    {
        return $this->h()->getAttentionMessage();
    }

    /**
     * Get url for delete customer from front
     *
     * @return string
     */
    protected function getDeleteUrl()
    {
        return $this->h()->getDeletUrl();
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
