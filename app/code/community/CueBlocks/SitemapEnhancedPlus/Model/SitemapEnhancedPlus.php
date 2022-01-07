<?php

/**
 * Description of SitemapEnhancedPlus
 * @package   CueBlocks_SitemapEnhancedPlus
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhancedPlus_Model_SitemapEnhancedPlus extends CueBlocks_SitemapEnhancedPlus_Model_SitemapEnhancedPlusAbstract
{
    const TYPE_REGULAR = 0;
    const TYPE_IMAGE = 1;
    const TYPE_MOBILE = 2;
    const TYPE_INDEX = 3;

    protected $_logWarning;
    protected $_logNotice;
    protected $_timer;

    protected function _stat($stop = false, $debug = false)
    {
        if (!$stop) {

            $this->_timer = microtime(true);

            if ($debug) {
                Mage::log('-------------------------------------------------------------');
                $this->_totMem = (int)str_replace('M', '', ini_get('memory_limit'));
                $baseMemory = memory_get_usage();
                Mage::log('FreeMem Before: ' . ($this->_totMem - ($baseMemory / 1024 / 1024)));
            }
        } elseif ($stop) {

            $time_start = $this->_timer;
            $time_end = microtime(true);
            $this->_timer = round(($time_end - $time_start), 2);

            if ($debug) {
                $baseMemory = memory_get_usage();
                Mage::log('FreeMem After: ' . ($this->_totMem - ($baseMemory / 1024 / 1024)));
            }
        }
    }

    /**
     * Generate XML file
     *
     * @return string
     */
    public function generateXml($cron = false)
    {
        // start timer
        $this->_stat();

        // initialize variable
        $this->_initVar();
        // generate links
        $this->_processSitemapSources();
        // save model
        $this->save();
        // close disallowed log file
        $this->getDisallowedLogFile()->streamClose();

        // Generate Final message
        $this->addNotice('The sitemap has been generated. - <br/> ');
        $this->genDisallowedReport();
        $this->genOrphanReport();
        $this->genExcludedReport();

        // stop timer
        $this->_stat(true);
        $this->genFileReport();


//     //send Email Report
//     $this->getHelper()->sendEmailTemplate($this->getStoreId(), array('sitemap' => $this, 'frequency' => 'Push'));
    }

    protected function _processSitemapSources()
    {
        try {
            Mage::dispatchEvent('cb_sitemap_plus_process_sources_before', array('sitemap' => $this));
            // CMS
//        Mage::getModel('sitemapEnhancedPlus/processor_cms_custom')->process($this);
            Mage::getModel('sitemapEnhancedPlus/processor_cms_page')->process($this);
            Mage::getModel('sitemapEnhancedPlus/processor_catalog_tag')->process($this);
            Mage::getModel('sitemapEnhancedPlus/processor_catalog_review')->process($this);
            // Category & Products
            Mage::getModel('sitemapEnhancedPlus/processor_catalog_category')->process($this);

            if ($this->getConfig()->getDivideBy() != CueBlocks_SitemapEnhancedPlus_Model_SitemapEnhancedPlus::DIVIDE_BY_MANUFACTURER) {
                Mage::getModel('sitemapEnhancedPlus/processor_catalog_product')->process($this);
                Mage::getModel('sitemapEnhancedPlus/processor_catalog_productOut')->process($this);
            } else { // divide by attribute mode
                Mage::getModel('sitemapEnhancedPlus/processor_catalog_productByAttribute')->process($this);
                Mage::getModel('sitemapEnhancedPlus/processor_catalog_productOutByAttribute')->process($this);
            }
            Mage::dispatchEvent('cb_sitemap_plus_process_sources_after', array('sitemap' => $this));

            // serialize link counter collection
            $this->setSitemapLinkCounters(serialize($this->_linkCollection));

            if ($this->_ioCollection->count() > 1) {
                $this->genIndexSite();
            } else {
                $this->_ioCollection->getLastItem()->closeIoWrite();
            }

        } catch (Exception $e) {
            throw $e;
        }

        // Delete old Files and old Items
        $this->removeFiles();
        // save files model
        $collection = $this->_ioCollection;
        foreach ($collection as $item) {
            $item->save();
        }

        $this->setSitemapTime(Mage::getSingleton('core/date')->gmtDate('Y-m-d H:i:s'));
    }

    /**
     * Generate cms pages sitemap
     */
    protected function genIndexSite()
    {
        $indexFile = $this->_addFile(CueBlocks_SitemapEnhancedPlus_Model_SitemapEnhancedPlus::TYPE_INDEX);

        $collection = $this->_ioCollection;

        foreach ($collection as $item) {
            if ($item->getSitemapFileType() != CueBlocks_SitemapEnhancedPlus_Model_SitemapEnhancedPlus::TYPE_INDEX) {
                $sitemapFileName = $item->removeTempExtension($item->getSitemapFileFilename());
                $fileName = preg_replace('/^\//', '', $this->getSitemapPath() . $sitemapFileName);
                $url = $this->getBaseUrl(true) . $fileName;
                $url = $this->getHelper()->escapeHtml($url);

                $xml = sprintf('<sitemap><loc>%s</loc><lastmod>%s</lastmod></sitemap>', $url, $this->getDate());
                $this->addLink($xml, CueBlocks_SitemapEnhancedPlus_Model_SitemapEnhancedPlus::TYPE_INDEX);
            }
        }

        $indexFile->closeIoWrite();
    }

    public function genOrphanReport()
    {
        $log = '';
        $warning = '';

        if ($this->getConfig()->getCheckOrphan()) {
            // Prepare FileName
            $filename = $this->getHelper()->clearExtension($this->getSitemapFilename());
            $filename .= self::ORPHAN_FILENAME . '.txt';
            $file = $this->_getNewFile($this->getPath(), $filename);
            $Urlpath = preg_replace('/^\//', '', $this->getSitemapPath() . $filename);

            $orphanResult = Mage::getResourceModel('sitemapEnhancedPlus/processor_catalog_product')
                ->getOrphan($this->getStoreId());
            $count = $orphanResult->rowCount();
            if ($count > 0) {
                // Write IDs
                $file->streamWrite('Orphan Product(s) IDs:' . "\n");
                while ($row = $orphanResult->fetch()) {
                    $id = $row['entity_id'];
                    $sku = $row['sku'];
                    $file->streamWrite("ID:$id - $sku \n");
                }
                $url = $this->getHelper()->htmlEscape($this->getBaseUrl(true) . $Urlpath);
                $warning .= $count . " Product(s) are orphans (have no category), please check <a target=\"_blank\" href=\"$url\">$filename</a> for detail.";
                $this->addWarning($warning);
            } else {
                $log .= "(OK) - No Orphan Products (all products are correcly associated).<br/><br/>";
                $this->addNotice($log);
            }
            $file->streamClose();
        }
        return $log;
    }

    public function genExcludedReport()
    {
        $log = '';
        $warning = '';

        if ($this->getConfig()->getDebugCheckExcluded()) {
            // Prepare FileName
            $filename = $this->getHelper()->clearExtension($this->getSitemapFilename());
            $filename .= self::EXCLUDED_FILENAME . '.txt';
            $file = $this->_getNewFile($this->getPath(), $filename);
            $Urlpath = preg_replace('/^\//', '', $this->getSitemapPath() . $filename);

            $result_ids = Mage::getResourceModel('sitemapEnhancedPlus/processor_catalog_product')
                ->getAllExcluded($this->getStoreId());

            $count = count($result_ids);
            if ($count > 0) {
                // Write IDs
                $file->streamWrite("Following product(s) ID(s) was/were not fetched because of any of the below reasons: \n\n1)They are disabled. \n2)They have visibility set to 'Not visible individually'. \n3)They are not assigned to any website.\n4)Some extension settings prevent them to be included in sitemap." . "\n\n");
                foreach ($result_ids as $id) {
                    $file->streamWrite("ID:$id \n");
                }
                $url = $this->getHelper()->htmlEscape($this->getBaseUrl(true) . $Urlpath);
                $warning .= $count . " Product(s) were not fetched for unknown reason, please check <a target=\"_blank\" href=\"$url\">$filename</a> for detail.";
                $this->addWarning($warning);
            } else {
                $log .= "(OK) - All store products has been fetched .<br/>";
                $this->addNotice($log);
            }
            $file->streamClose();
        }
        return $log;
    }

    /**
     * Get the report of the file generation
     *
     * @return string
     */
    public function genFileReport()
    {
        $log = '';
        foreach ($this->_ioCollection as $item) {
            $name = $item->getSitemapFileFilename();
            $size = $item->getIo()->getSize();
            $links = $item->getIo()->getLinks();

            $log .= "- $name (links: $links, size: $size bytes) <br/>";
        }
        $log = 'File Generation Summary:   <br/><br/>' . $log . '<br/>Execution time: ' . $this->_timer . ' sec';
        // notice Obj
        $this->addNotice($log);
        // for mail
        return $log;
    }

    public function addDisallowed($url)
    {
        $this->_disallowed += 1;
        $io = $this->getDisallowedLogFile();
        $io->streamWrite($url . "\n");
    }

    public function genDisallowedReport()
    {
        $log = '';
        if ($this->_disallowed) {
            $filename = $this->_getDisallowedLogFileName();

            $url = $this->getFileUrl($filename);
            $log .= $this->_disallowed . " pages were skipped as per your robots.txt rules and `Category Filter` setting, please check <a target=\"_blank\" href=\"$url\">$filename</a> for detail.";
            $this->addWarning($log);
        }
        // for mail
        return $log;
    }

    public function genWarnReport()
    {
        $log = '';
        if ($this->_logWarning) {
            foreach ($this->_logWarning as $msg) {
                $log .= '- ' . $msg->getMessage() . '<br/>';
            }
        }
        return $log;
    }

    public function addWarning($txt)
    {
        $warning = new Varien_Object();
        $warning->setMessage($txt);

        if (!$this->_logWarning && !$this->_logWarning instanceof Varien_Data_Collection) {
            $this->_logWarning = new Varien_Data_Collection;
        }
        $this->_logWarning->addItem($warning);
    }

    public function genNoticeReport()
    {
        $log = '';
        if ($this->_logNotice) {
            foreach ($this->_logNotice as $msg) {
                $log .= '- ' . $msg->getMessage() . '<br/>';
            }
        }
        return $log;
    }

    public function addNotice($txt)
    {
        $notice = new Varien_Object();
        $notice->setMessage($txt);

        if (!$this->_logNotice && !$this->_logNotice instanceof Varien_Data_Collection) {
            $this->_logNotice = new Varien_Data_Collection;
        }
        $this->_logNotice->addItem($notice);
    }

    /**
     * Get the report of the page links generation
     *
     * @return string
     */
    public function getPageReport()
    {
        $html = '';

        $counters = unserialize($this->getSitemapLinkCounters());
        $tot = $this->getSitemapTotLinks();
        if ($counters) {
            foreach ($counters as $counter) {
                $html .= sprintf(' <tr><td >%s:</td ><td style = "text-align:right;" >%s </td ></tr > ', $counter->getLabel(), $counter->getCount());
                if ($counter->getExtraCounter()) {
                    foreach ($counter->getExtraCounter() as $extraCounter)
                        if ($extraCounter->getCount() > 0) {
                            if ($extraCounter->getExclude()) {
                                $html .= sprintf('<tr style = "font-style: italic;" ><td colspan = "2" >-> %s: %s </td ></tr > ', $extraCounter->getLabel(), $extraCounter->getCount());
                            } else {
                                $html .= sprintf('<tr ><td >-> %s:</td ><td style = "text-align:right;" >%s </td ></tr > ', $extraCounter->getLabel(), $extraCounter->getCount());
                            }
                        }
                }
            }
            $html .= sprintf('<tr style = "font-weight: bold;" ><td > Total pages: </td ><td style = "text-align:right;" >%1$s </td ></tr > ', $tot);
        }
        return $html;
    }

    public function addLinkCounter($counter)
    {
        if ($counter) {
            // Reset Link Counters
            $this->_linkCollection->addItem($counter);
        }
    }

    public function ping()
    {
        $pingModel = Mage::getModel('sitemapEnhancedPlus/pings');
        $pingModel->setStoreId();

        $msg = $pingModel->ping($this);
        return $msg;
    }
}
