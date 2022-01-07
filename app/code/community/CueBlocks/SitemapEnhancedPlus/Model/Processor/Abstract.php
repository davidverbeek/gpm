<?php

/**
 * Description of SitemapEnhancedPlus
 * @package   CueBlocksgetSitemap()EnhancedPlus
 * @company   CueBlocks - http://www.cueblocks.com/
 */

abstract class CueBlocks_SitemapEnhancedPlus_Model_Processor_Abstract extends Varien_Object
{

    const CONFIG_BASE_PATH = 'sitemap_enhanced_plus/';

    /**
     * model name for link source
     *
     * @var string
     */
    protected $_sourceModel;

    /**
     * config key
     *
     * @var string
     */
    protected $_configKey;

    /**
     * model file suffix
     *
     * @var string
     */
    protected $_fileName;

    /**
     * page counter
     *
     * @var string
     */
    protected $_counter;
    protected $_counterLabel;

    /**
     * first File
     * generate a separate file for the current processor
     *
     * @var bool
     */
    protected $_firstFile = TRUE;

    protected $_mysql_currentPage = 0;
    protected $_mysql_lastPage = 0;
    protected $_mysql_sizePage = 0;

    public function isEnabled()
    {
        return $this->getConfig()->getEnabled();
    }

    protected function _increaseLinkCounter()
    {
        if (!$this->_counter) {
            $this->_initCounter();
        }

        $this->_counter->setCount((int)$this->_counter->getCount() + 1);
    }

    protected function _initCounter()
    {
        $counter = new Varien_Object();
        $counter->setLabel($this->_counterLabel);
        $counter->setCount(0);

        $this->_counter = $counter;
    }

    public function getConfig()
    {
        if ($this->getData('config') == null) {
            $config = Mage::helper('sitemapEnhancedPlus')->getConfig($this->_configKey, $this->getSitemap()->getStoreId());
            $this->setConfig($config);
        }
        return $this->getData('config');
    }

    public function getQueryModel($usePagination = false)
    {
        $queryModel = Mage::getResourceModel($this->_sourceModel);
        $queryModel->init($this->_getQueryModelConfig($usePagination));
        return $queryModel;
    }

    /**
     * @param bool $usePagination
     * @return Varien_Object
     */
    public function _getQueryModelConfig($usePagination = false)
    {
        $data = array(
            'mysql_use_pagination' => $usePagination,
            'mysql_page_size' => $this->_mysql_sizePage,
            'mysql_page_current' => $this->_mysql_currentPage,
            'mysql_page_last' => $this->_mysql_lastPage,
            'store_id' => $this->getSitemap()->getStoreId(),
        );
        $config = new Varien_Object($data);
        return $config;
    }

    protected function _getDate($row)
    {
        $date = $this->getSitemap()->getDate();

        // Real Date
        $realDateEnabled = $this->getConfig()->getEnabledRealdate();
        if ($realDateEnabled && isset($row['updated_at'])) {
            $date = $row['updated_at'];
        }

        return $date;
    }

    protected function _getFileName($row = null)
    {
        return $this->_fileName;
    }

    protected function _preProcess()
    {
        return $this;
    }

    /**
     * Process link source collection
     */
    public function process($sitemap)
    {
        $this->setSitemap($sitemap);

        if ($this->isEnabled()) {
            $this->_preProcess();
            if (!$this->_usePagination()) {
                $this->_getProcessCollection();
            } else {
                $this->_getProcessCollectionWithPagination();
            }
            $this->_postProcess();
        }
        return $this;
    }

    protected function _postProcess()
    {
        if ($this->_counter)
            $this->getSitemap()->addLinkCounter($this->_counter);
    }

    protected function _getProcessCollection($addFirst = true)
    {
        $queryCollection = $this->getQueryModel()->getCollection();

        while ($row = $queryCollection->fetch()) {

            if ($this->_filterRow($row)) {
                continue;
            }

            if ($addFirst && $this->_firstFile) {
                $this->_firstFile = FALSE;
                $this->getSitemap()->addFirstFile($this->_getFileName($row));
            }
            if ($xml = $this->_processRow($row)) {
                $this->_addUrl($xml);
            }
        }
        unset($queryCollection);
    }

    protected function _usePagination()
    {
        if ($this->getSitemap()->getMysqlConfig()->getUsePagination()) {
            $paginationSize = $this->getSitemap()->getMysqlConfig()->getPaginationSize();
            $collectionSize = $this->getQueryModel()->getCount();

            if ($collectionSize > $paginationSize) {
                $this->_initPagination($collectionSize, $paginationSize);
                return true;
            }
        }
        return false;
    }

    protected function _initPagination($count, $pageSize)
    {
        $lastPage = 1;

        if ($count > 0) {
            $lastPage = ceil($count / $pageSize);
        }
        $this->_mysql_currentPage = 1;
        $this->_mysql_lastPage = $lastPage;
        $this->_mysql_sizePage = $pageSize;

        return $this;
    }

    protected function _getProcessCollectionWithPagination($addFirst = true)
    {
        while ($this->_mysql_currentPage <= $this->_mysql_lastPage) {

            $queryCollection = $this->getQueryModel(true)->getCollection();

            while ($row = $queryCollection->fetch()) {
                if ($this->_filterRow($row)) {
                    continue;
                }
                if ($addFirst && $this->_firstFile) {
                    $this->_firstFile = FALSE;
                    $this->getSitemap()->addFirstFile($this->_getFileName($row));
                }
                if ($xml = $this->_processRow($row)) {
                    $this->_addUrl($xml);
                }
            }
            $this->_mysql_currentPage++;
        }
        unset($queryCollection);
    }

    protected function _filterRow($row)
    {
        return false;
    }

    protected function _processRow($row)
    {
        $xml = '';
        $extraXml = '';

        $url = htmlspecialchars($this->getSitemap()->getBaseUrl() . $this->_getUrl($row));
        $date = $this->_getDate($row);
        $extraXml = $this->_getExtraXml();

        if ($this->getSitemap()->getSitemapType() == CueBlocks_SitemapEnhancedPlus_Model_SitemapEnhancedPlus::TYPE_MOBILE)
            $xml = sprintf('<url><loc>%s</loc><lastmod>%s</lastmod>%s<mobile:mobile/></url>', $url, $date, $extraXml);
        else
            $xml = sprintf('<url><loc>%s</loc><lastmod>%s</lastmod>%s</url>', $url, $date, $extraXml);

        if (!$this->isUrlAllowed($url)) {
            return false;
        }
        return $xml;
    }

    protected function isUrlAllowed($url)
    {
        return $this->getSitemap()->isUrlAllowed($url);
    }

    protected function _addUrl($xml)
    {
        $this->getSitemap()->addLink($xml, null, true);
        $this->_increaseLinkCounter();
        return true;
    }

    protected function _getExtraXml()
    {
        $extraXml = '';

        $frequency = (string)$this->getConfig()->getChangefreq();
        $priority = (string)$this->getConfig()->getPriority();

        if ($frequency)
            $extraXml = sprintf('<changefreq>%s</changefreq>', $frequency);
        if ($priority)
            $extraXml .= sprintf('<priority>%.1f</priority>', $priority);

        return $extraXml;
    }

    abstract protected function _getUrl($row);

}