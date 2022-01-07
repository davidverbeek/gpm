<?php

/**
 * Description of Data
 * @package   CueBlocks_SitemapEnhancedPlus
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhancedPlus_Helper_Email extends Mage_Core_Helper_Abstract
{
    /**
     * Send corresponding email template
     *
     * @param int $storeId
     * @param array $templateParams
     * @return Mage_Customer_Model_Customer
     */
    public function sendReportEmail($sitemap, $pingResult, $cronConfig)
    {
        $storeId = $sitemap->getStoreId();
        /** @var $mailer Mage_Core_Model_Email_Template_Mailer */
        $mailer = Mage::getModel('core/email_template_mailer');
        $emailInfo = Mage::getModel('core/email_info');

        $templateParams = $this->getTemplateParams($sitemap, $pingResult, $cronConfig);

        // workarround for 1.4 compatibility
        if (!($mailer && $emailInfo))
            return $this->_sendEmailTemplateOld($storeId, $cronConfig, $templateParams);
        else {
            $template = $cronConfig->getReportEmailTemplate();
            $sender = $cronConfig->getReportEmailIdentity();

            $arrRecipientAddress = $this->_getRecipients($cronConfig, $storeId, true);
            foreach ($arrRecipientAddress as $address) {
                $emailInfo->addTo($address);
            }
            $mailer->addEmailInfo($emailInfo);

// Set all required params and send emails
            $mailer->setSender($sender);
            $mailer->setStoreId($storeId);
            $mailer->setTemplateId($template);
            $mailer->setTemplateParams($templateParams);
            return $mailer->send();
        }
    }

    public function getTemplateParams($sitemap, $pingResult, $cronConfig)
    {
        $freq = '';
        $storeId = $sitemap->getStoreId();
        $sender = $cronConfig->getReportEmailIdentity();

        switch ($cronConfig->getFrequency()) {
            case 'D':
                $freq = 'Daily';
                break;
            case 'W':
                $freq = 'Weekly';
                break;
            case 'M':
                $freq = 'Monthly';
                break;
        }

        $sName = Mage::getStoreConfig('trans_email/ident_' . $sender . '/name', $storeId);

        $warning = ($sitemap->genWarnReport()) ? $sitemap->genWarnReport() : 'No Warning';

        $templateParams = array(
            'domain' => $sitemap->getBaseUrl(),
            'sender' => Mage::getStoreConfig('trans_email/ident_' . $sender . '/email', $storeId),
            'frequency' => $freq,
            'file_report' => $sitemap->genFileReport(),
            'page_report' => $sitemap->getPageReport(),
            'sitemap_link' => $sitemap->getLinkForRobots(true),
            'sitemap_warning' => $warning,
            'ping_report' => $pingResult,
            'recipients' => $this->_getRecipients($cronConfig, $storeId),
        );
        return $templateParams;
    }

    protected function _sendEmailTemplateOld($storeId, $cronConfig, $templateParams)
    {
        $recipientAddress = $this->_getRecipients($cronConfig, $storeId);
        $template = $cronConfig->getReportEmailTemplate();
        $sender = $cronConfig->getReportEmailIdentity();

        return Mage::getModel('core/email_template')
            ->setDesignConfig(array('area' => 'adminhtml', 'store' => $storeId))
            ->sendTransactional(
                $template, $sender, $recipientAddress, null, $templateParams);
    }

    protected function _getRecipients($cronConfig, $storeId, $explode = false)
    {
        $recipientAddress = $cronConfig->getReportEmail();
        // no recipients set default one
        if (!trim($recipientAddress))
            $recipientAddress = Mage::getStoreConfig('trans_email/ident_general/email', $storeId);

        if ($explode) {
            return explode(';', $recipientAddress);
        } else {
            // for template var
            return $recipientAddress;
        }
    }

}
