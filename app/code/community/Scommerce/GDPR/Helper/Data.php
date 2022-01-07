<?php
/**
 * GDPR Default Helper
 *
 * @category   Scommerce
 * @package    Scommerce_GDPR
 * @author     Scommerce Mage <core@scommerce-mage.co.uk>
 */
class Scommerce_GDPR_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_GDPR_ENABLED                 = 'scommerce_gdpr/configuration/enabled';
	const XML_PATH_GDPR_DELETE_FRONTEND         = 'scommerce_gdpr/configuration/delete_enabled_on_frontend';
    const XML_PATH_GDPR_LICENSE_KEY	            = 'scommerce_gdpr/configuration/license_key';
    const XML_PATH_GDPR_SUCCESSMESSAGE          = 'scommerce_gdpr/configuration/successmessage';
	const XML_PATH_GDPR_EMAIL_SENDER			= 'scommerce_gdpr/configuration/sender_email_identity';
	const XML_PATH_GDPR_EMAIL_CONFIRMATION      = 'scommerce_gdpr/configuration/confirmation_email_template';
	const XML_PATH_GDPR_EMAIL_DELETION          = 'scommerce_gdpr/configuration/delete_confirmation_email_template';
    const XML_PATH_GDPR_COOKIE_ENABLED          = 'scommerce_gdpr/configuration/enable_cookie';
    const XML_PATH_GDPR_BLOCKED                 = 'scommerce_gdpr/configuration/blocked';
	const XML_PATH_GDPR_BLOCKED_ID_OR_CLASS    	= 'scommerce_gdpr/configuration/blocked_id_or_class';
    const XML_PATH_GDPR_CMS_PAGE                = 'scommerce_gdpr/configuration/cms_page';
    const XML_PATH_GDPR_COOKIE_MESSAGE          = 'scommerce_gdpr/configuration/cookie_message';
    const XML_PATH_GDPR_COOKIE_MESSAGE_POSITION = 'scommerce_gdpr/configuration/cookie_message_position';
	const XML_PATH_GDPR_COOKIE_LINK_TEXT        = 'scommerce_gdpr/configuration/cookie_link_text';
    const XML_PATH_GDPR_COOKIE_TEXT_COLOR       = 'scommerce_gdpr/configuration/cookie_text_color';
    const XML_PATH_GDPR_COOKIE_BACKGROUND_COLOR = 'scommerce_gdpr/configuration/cookie_background_color';
    const XML_PATH_GDPR_COOKIE_LINK_COLOR       = 'scommerce_gdpr/configuration/cookie_link_color';
    const XML_PATH_GDPR_ATTENTION_MESSAGE       = 'scommerce_gdpr/configuration/attentionmessage';
    const XML_PATH_GDPR_EXPORT_MESSAGE       = 'scommerce_gdpr/configuration/exportmessage';
    const XML_PATH_GDPR_QUOTE_EXPIRE            = 'scommerce_gdpr/configuration/quote_expire';
	const XML_PATH_GDPR_OVERRIDE_ADMIN_MA_BLOCK	= 'scommerce_gdpr/configuration/override_adminhtml_massaction_block';
	

    /**
     * Checks whether GDPR extension is enabled in admin
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return Mage::getStoreConfig( self::XML_PATH_GDPR_ENABLED) && $this->isLicenseValid();
    }

    /**
     * Get success message after deleting account by customer itself
     *
     * @return string
     */
    public function getSuccessMessage()
    {
        $default = $this->__('Your account has been successfully deleted and all your order data has been anonymised.');
        return $this->getValue(Mage::getStoreConfig(self::XML_PATH_GDPR_SUCCESSMESSAGE), $default);
    }
	
	/**
     * @return string
     */
    public function getSenderEmail()
    {
        return Mage::getStoreConfig(self::XML_PATH_GDPR_EMAIL_SENDER);
    }
	
	/**
     * @return string
     */
    public function getOverrideAdminMassActionBlock()
    {
		$default = "Mage_Adminhtml_Block_Widget_Grid_Massaction";
        return $this->getValue(Mage::getStoreConfig(self::XML_PATH_GDPR_OVERRIDE_ADMIN_MA_BLOCK), $default);
    }
	
	/**
     * @return string
     */
    public function getConfirmationEmailTemplate()
    {
        return Mage::getStoreConfig(self::XML_PATH_GDPR_EMAIL_CONFIRMATION);
    }
	
	/**
     * @return string
     */
    public function getSuccessDeletionEmailTemplate()
    {
        return Mage::getStoreConfig(self::XML_PATH_GDPR_EMAIL_DELETION);
    }

    /**
     * Returns license key administration configuration option
     *
     * @return boolean
     */
    public function getLicenseKey()
    {
        return Mage::getStoreConfig(self::XML_PATH_GDPR_LICENSE_KEY);
    }

    /**
     * Returns whether license key is valid or not
     *
     * @return boolean
     */
    public function isLicenseValid()
    {
        $sku = strtolower(str_replace('_Helper_Data','',str_replace('Scommerce_','',get_class($this))));
        return Mage::helper("scommerce_core")->isLicenseValid($this->getLicenseKey(),$sku);
    }

    /**
     * Is deletion enabled on frontend
     *
     * @return bool
     */
    public function isDeletionEnabledOnFrontend()
    {
        return (bool) Mage::getStoreConfig(self::XML_PATH_GDPR_DELETE_FRONTEND) && $this->isEnabled();
    }
	
	 /**
     * Is cookie enabled
     *
     * @return bool
     */
    public function isCookieEnabled()
    {
        return (bool) Mage::getStoreConfig(self::XML_PATH_GDPR_COOKIE_ENABLED) && $this->isEnabled();
    }

    /**
     * Is blocked enable
     *
     * @return bool
     */
    public function isBlocked()
    {
        return (bool) Mage::getStoreConfig(self::XML_PATH_GDPR_BLOCKED);
    }
	
	/**
	 * Return main div wrapper id or class
     * @return string
     */
    public function getBlockIdorClass()
    {
        return Mage::getStoreConfig(self::XML_PATH_GDPR_BLOCKED_ID_OR_CLASS);
    }

    /**
     * @return string
     */
    public function getCmsPageUrl()
    {
        return Mage::getUrl(Mage::getStoreConfig(self::XML_PATH_GDPR_CMS_PAGE));
    }

    /**
     * @return string
     */
    public function getCookieTextMessage()
    {
        $default = $this->__('We use cookies on this website to improve your shopping experience. We use cookies to remember log-in details and provide secure log-in, collect statistics to optimize site functionality, and deliver content tailored to your interests. Click accept to give your consent to accept cookies and go directly to the site or click on more information to see detailed descriptions of the types of cookies we store.');
        return $this->getValue(Mage::getStoreConfig(self::XML_PATH_GDPR_COOKIE_MESSAGE), $default);
    }

    /**
     * @return string
     */
    public function getCookieLinkText()
    {
        $default = $this->__('Click here to learn about cookie settings.');
        return $this->getValue(Mage::getStoreConfig(self::XML_PATH_GDPR_COOKIE_LINK_TEXT), $default);
    }
	
	/**
     * Get cookie key to check accepted cookie policy
     *
     * @return string
     */
    public function getCookieKey()
    {
        return 'cookie_accepted';
    }
	
	/**
     * Get cookie key to check if cookie message was closed
     *
     * @return string
     */
    public function getCookieClosedKey()
    {
        return 'cookie_closed';
    }

    /**
     * @return string
     */
    public function getCookieTextColor()
    {
        $default = 'FFF8C7';
        return $this->getValue(Mage::getStoreConfig(self::XML_PATH_GDPR_COOKIE_TEXT_COLOR), $default);
    }

    /**
     * @return string
     */
    public function getCookieBackgroundColor()
    {
        $default = '333';
        return $this->getValue(Mage::getStoreConfig(self::XML_PATH_GDPR_COOKIE_BACKGROUND_COLOR), $default);
    }

    /**
     * @return string
     */
    public function getCookieLinkColor()
    {
        $default = 'F90';
        return $this->getValue(Mage::getStoreConfig(self::XML_PATH_GDPR_COOKIE_LINK_COLOR), $default);
    }

    /**
     * Get attention message
     *
     * @return string
     */
    public function getAttentionMessage()
    {
        $default = 'If you proceed to delete your account, all data we hold about you will be destroyed or anonymised. This operation cannot be un-done.';
        return $this->getValue(Mage::getStoreConfig(self::XML_PATH_GDPR_ATTENTION_MESSAGE), $default);
    }

    /**
     * Get Export Data message
     *
     * @return string
     */
    public function getExportMessage()
    {
        $default = 'Download here all information of orders and quotations.';
        return $default;
    }

    /**
     * @return string
     */
    public function getCookieQuoteExpire()
    {
        return Mage::getStoreConfig(self::XML_PATH_GDPR_QUOTE_EXPIRE);
    }

	/**
     * @return int
     */
	public function getCookieMessagePosition() {
        return Mage::getStoreConfig(self::XML_PATH_GDPR_COOKIE_MESSAGE_POSITION);
    }	 
    /**
     * @return string
     */
    public function getDeletUrl()
    {
        return Mage::getUrl('scommerce_gdpr/customer/delete');
    }

    /**
     * @return string
     */
    public function getExportUrl()
    {
        return Mage::getUrl('scommerce_gdpr/customer/export');
    }
	
	/**
	 * Sending email to customer
	 * 
	 * @param type $name
	 * @param type $email
	 * @return boolean
	 */
	public function sendEmail($emailId, $customerName, $customerEmailAddress, $data = array())
	{
		$storeId    = Mage::app()->getStore()->getStoreId();
		$emailFrom  = $this->getSenderEmail();
		
		/* Sender Name */ 
		$fromName =Mage::getStoreConfig('trans_email/ident_'.$emailFrom.'/name'); 
		/* Sender Email */ 
		$fromEmail = Mage::getStoreConfig('trans_email/ident_'.$emailFrom.'/email');
		
		try{
				$translate = Mage::getSingleton('core/translate');
				/* @var $translate Mage_Core_Model_Translate */
				$translate->setTranslateInline(false);

				$emailTemplate = Mage::getModel('core/email_template');
				/* @var $emailTemplate Mage_Core_Model_Email_Template */
				$emailTemplate->setDesignConfig(array('area' => 'frontend', 'store' => $storeId));
				
				$emailTemplate->sendTransactional($emailId, 
													array('email' => $fromEmail, 'name' => $fromName), 
													$customerEmailAddress, 
													$customerName, 
													array_merge(array('email' => $customerEmailAddress, 'name' => $customerName),$data));

				$translate->setTranslateInline(true);
		}
		catch (Exception $e){
		}           
		return true;
	}

    /**
     * Helper method for get config value with default value
     * If empty $value then returns $default
     *
     * @param string $value
     * @param string $default
     * @return string
     */
    private function getValue($value, $default)
    {
        if (empty($value)) {
            return $default;
        }
        return $value;
    }
}
