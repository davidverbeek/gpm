<?php

/**
 * Description of Observer
 * @package   CueBlocks_SitemapEnhancedPlus
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */

/**
 * SitemapEnhancedPlus module observer
 *
 * @category   Mage
 * @package    Mage_Sitemap
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class CueBlocks_SitemapEnhancedPlus_Model_Observer extends Varien_Object
{
    /**
     * Cronjob expression configuration
     */

    const XML_PATH_CRON_EXPR = 'crontab/jobs/generate_sitemapEnhancedPlus/schedule/cron_expr';
    protected $_configKey = 'cron';

    /**
     * Generate sitemaps
     *
     * @param Mage_Cron_Model_Schedule $schedule
     */
    public function cronGenerateSitemap($schedule = null, $debug = false)
    {
        $errors = array();

        $collection = Mage::getModel('sitemapEnhancedPlus/sitemapEnhancedPlus')->getCollection();
        /* @var $collection CueBlocks_SitemapEnhancedPlus_Model_Mysql4_SitemapEnhancedPlus_Collection */

        foreach ($collection as $sitemap) {
            /* @var $sitemap Mage_Sitemap_Model_Sitemap */

            $config = $this->_getConfig($sitemap);
            // check if scheduled generation enabled
            if (!$config->getEnabled()) {
                continue;
            }

            try {
                $sitemap->removeFiles();
                $sitemap->generateXml();
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
                Mage::log('Sitemap Cron Generation error:' . $e->getMessage());
            }

            try {
                $ping = Mage::getModel('sitemapEnhancedPlus/pings');
                if ($ping->getConfig()->getEnabled()) {
                    $pingResult = $ping->ping($sitemap);
                } else {
                    $pingResult = 'Auto ping is disabled';

                }
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
                Mage::log('Sitemap Cron Generation error (ping):' . $e->getMessage());
            }

            // Send Report Email
            if ($config->getReportEnabled()) {
                $helper = Mage::helper('sitemapEnhancedPlus/email');
                $send = $helper->sendReportEmail($sitemap, $pingResult, $config);
            }
        }

        if ($debug) {
            var_dump($send);
            return 'here';
        }
    }

    protected function _getConfig($sitemap)
    {
        $config = Mage::helper('sitemapEnhancedPlus')->getConfig($this->_configKey, $sitemap->getStoreId());
        return $config;
    }
}
