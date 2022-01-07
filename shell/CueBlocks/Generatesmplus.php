<?php
require_once '../abstract.php';

class Mage_Shell_Generatesmplus extends Mage_Shell_Abstract
{

    protected $_ping;
    protected $_helper;
    protected $_configKey = 'cron';
    /**
     * Apply PHP settings to shell script
     *
     * @return void
     */
    protected function _applyPhpVariables()
    {
        parent::_applyPhpVariables();

        set_time_limit(0);
        error_reporting(E_ALL);
        ini_set( 'memory_limit', '2G' );
        ini_set( 'display_errors', 1 );
    }

    public function run()
    {
        if( isset( $this->_args['generate'] ) ) {
            $arg = $this->_args['generate'];

            //initialize global variables
            $this->_helper = Mage::helper('sitemapEnhancedPlus/email');
            $this->_ping = Mage::getModel('sitemapEnhancedPlus/pings');

            if($arg === 'all') {
                $collection = Mage::getModel('sitemapEnhancedPlus/sitemapEnhancedPlus')->getCollection();
                /* @var $collection CueBlocks_SitemapEnhancedPlus_Model_Mysql4_SitemapEnhancedPlus_Collection */

                foreach ($collection as $sitemap) {
                    /* @var $sitemap Mage_Sitemap_Model_Sitemap */

                    $config = $this->_getConfig($sitemap);
                    $this->processSitemap($sitemap, $config);
                }
                return true;
            } elseif(is_numeric($arg)) {
                $sitemap = Mage::getModel('sitemapEnhancedPlus/sitemapEnhancedPlus')->load($arg);
                $config = $this->_getConfig($sitemap);
                if(isset($sitemap) && $sitemap->getId()) {
                    $this->processSitemap($sitemap, $config);
                } else {
                    echo "Sorry, no sitemap found with this id.\n";
                }
                return true;
            }

        }
        echo "Please specify sitemap id or string 'all' in case you want to regenerate all sitemaps.\n";
        return false;
    }

    /*
     * Generate sitemap + ping if config enabled + send generation summary to
     * configured email addresses
     * */
    public function processSitemap($sitemap, $config) {
        try {
            echo "- Generating sitemap ".$sitemap->getSitemapFilename()." .. \n";
            $startTime = microtime(true);
            $sitemap->removeFiles();
            $sitemap->generateXml();
            if ($this->_ping->getConfig()->getEnabled()) {
                $pingResult = $this->_ping->ping($sitemap);
            } else {
                $pingResult = 'Auto ping is disabled';

            }
            if ($config->getReportEnabled()) {
                $this->_helper->sendReportEmail($sitemap, $pingResult, $config);
            }
            $timeSpent = number_format(microtime(true) - $startTime,2);
            echo "Sitemap ".$sitemap->getSitemapFilename()." has been generated successfully (Time taken $timeSpent seconds).\n\n";
        } catch (Exception $e) {
            echo $e->getMessage()."\n";
        }
    }

    protected function _getConfig($sitemap)
    {
        return Mage::helper('sitemapEnhancedPlus')->getConfig($this->_configKey, $sitemap->getStoreId());
    }


    /**
     * Retrieve Usage Help Message
     *
     * @return void
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php Generatesmplus.php --[options]
  generate        Specify sitemap id or string 'all' in case you want to regenerate all sitemaps.\n
Sample Commands:
  php Generatesmplus.php --generate all [Regenerate all available sitemaps]
  php Generatesmplus.php --generate 2   [Regenerate sitemap with id 2]

USAGE;
    }
}

$shell = new Mage_Shell_Generatesmplus();

$shell->run();