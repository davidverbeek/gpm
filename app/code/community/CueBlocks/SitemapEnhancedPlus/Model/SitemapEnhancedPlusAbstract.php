<?php

/**
 * Description of SitemapEnhancedPlus
 * @package   CueBlocks_SitemapEnhancedPlus
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
abstract class CueBlocks_SitemapEnhancedPlus_Model_SitemapEnhancedPlusAbstract extends Mage_Core_Model_Abstract
{
    /**
     * Fix Byte for File Footer
     */
    Const FIX_BYTE = 9;

    /**
     * Fix names appended to files
     */
    Const CUSTOM_PAGES_FILENAME = '_custom';
    Const DISALLOWED_FILENAME = '_disallowed_log';
    Const ORPHAN_FILENAME = '_orphan_log';
    Const EXCLUDED_FILENAME = '_excluded_log';

    const DIVIDE_BY_TOPCATEGORY = 1;
    const DIVIDE_BY_SUBCATEGORY = 2;
    const DIVIDE_BY_MANUFACTURER = 3;
    const TMP_EXT = '.tmp';

    /**
     * helper
     *
     * @var CueBlocks_SitemapEnhancedPlus_Helper_Data
     */
    protected $_helper;
    protected $_sepFileCounter;
    /* @var $_ioCollection Varien_Data_Collection */
    protected $_ioCollection;
    /* @var $_conf Varien_Object */
    protected $_configKey = 'general';
    protected $_mysql_configKey = 'mysql';

    /**
     * disallowed url counter
     *
     * @var integer
     */
    protected $_disallowed;
    /**
     * disallowed log file
     *
     * @var Varien_Io_File
     */
    protected $_disallowed_log_file;

    /**
     * collection of total number of links
     *
     * @var Varien_Data_Collection
     */
    protected $_linkCollection;

    /**
     * Ping model
     *
     * @var CueBlocks_SitemapEnhancedPlus_Model_Pings
     */
    protected $_pingModel;

    public function _construct()
    {
        $this->_init('sitemapEnhancedPlus/sitemapEnhancedPlus');

    }

    public function getHelper()
    {
        if (!$this->_helper)
            $this->_helper = Mage::helper('sitemapEnhancedPlus');

        return $this->_helper;
    }

    public function getConfig()
    {
        if (!$this->getData('config')) {
            $config = Mage::helper('sitemapEnhancedPlus')->getConfig($this->_configKey, $this->getStoreId());
            $this->setConfig($config);
        }
        return $this->getData('config');
    }

    public function getMysqlConfig()
    {
        if (!$this->getData('mysql_config')) {
            $config = Mage::helper('sitemapEnhancedPlus')->getConfig($this->_mysql_configKey, $this->getStoreId());
            $this->setMysqlConfig($config);
        }
        return $this->getData('mysql_config');
    }

    public function _initVar($data = null)
    {
        if ($data !== null) {
            $this->setData($data);
        }
        $this->resetCounters();

        // we remove files on saveAction and on cron
        // $this->removeFiles();

        $this->setDate(Mage::getSingleton('core/date')->gmtDate('Y-m-d'));

        // Link format settings
        $this->setUseUrlRewrites(Mage::getStoreConfig('web/seo/use_rewrites', $this->getStoreId()));
        $this->setUseStoreCode(Mage::getStoreConfig('web/url/use_store', $this->getStoreId()));
        $this->setStoreCode(Mage::app()->getStore($this->getStoreId())->getCode());

        // TIMEOUT FIX
        if ($this->getConfig()->getAvoidTimeout())
            ini_set('max_execution_time', 0);

        $this->_ioCollection = new Varien_Data_Collection();
        $this->_ioCollection->setItemObjectClass('sitemapEnhancedPlus/sitemapEnhancedPlusFiles');
    }

    public function getBaseUrl($direct = false)
    {
        $base = Mage::app()
            ->getStore($this->getStoreId())
            ->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB, false);

        if (!$direct) {
            if (!$this->getUseUrlRewrites()) {
                $base .= 'index.php/';
            }
            if ($this->getUseStoreCode()) {
                $base .= $this->getStoreCode() . '/';
            }
        }
        return $base;
    }

    /**
     * Delete previous files
     *
     * @return string
     */
    public function removeFiles()
    {
        $collection = $this->getFilesCollection();

        foreach ($collection as $item) {
            $filePath = $this->getPath() . $item->getSitemapFileFilename();
            if (file_exists($filePath))
                $res = unlink($filePath);

            $item->delete();
        }
    }

    public function resetCounters()
    {
        // Reset Link Counters
        $this->_linkCollection = new Varien_Data_Collection();
        $this->setSitemapLinkCounters(serialize($this->_linkCollection));
        $this->setSitemapTotLinks(0);
        $this->_disallowed = 0;
    }

    /**
     * Get the Url of the index or sitemap file
     *
     * @return array('filename','url')
     */
    public function getLinkForRobots($onlyUrl = false)
    {
        $collection = $this->getFilesCollection();

        // Link for Search Engine ( index or sitemap )

        if ($collection->count() == 1) {
            $fileName = $collection->getFirstItem()->getSitemapFileFilename();
        } else if ($collection->count() > 1) {
            $collection = $this->getFilesCollection(CueBlocks_SitemapEnhancedPlus_Model_SitemapEnhancedPlus::TYPE_INDEX);
            $fileName = $collection->getFirstItem()->getSitemapFileFilename();
        } else
            $fileName = $this->getSitemapFilename();

        // remove first slash
        $path = preg_replace('/^\//', '', $this->getSitemapPath());

        $url = $this->getBaseUrl(true) . $path . $fileName;

        if ($onlyUrl)
            return $url;
        else
            return array('filename' => $fileName, 'url' => $url);
    }

    public function getPath()
    {
        $pathmap = $this->getConfig()->getPathMap();

        if ($pathmap)
            $pathmap = DS . $pathmap;

        if (is_null($this->_filePath)) {
            $this->_filePath = str_replace('//', '/', Mage::getBaseDir() .
                $pathmap . $this->getSitemapPath());
        }
        return $this->_filePath;
    }

    /**
     * Files Collections
     *
     * @return CueBlocks_SitemapEnhancedPlus_Model_Mysql4_SitemapEnhancedPlusFiles_Collection
     */
    public function getFilesCollection($type = null)
    {
        /* @var $collection CueBlocks_SitemapEnhancedPlus_Model_Mysql4_SitemapEnhancedPlusFiles_Collection */
        $collection = Mage::getModel('sitemapEnhancedPlus/sitemapEnhancedPlusFiles')
            ->getCollection()
            ->setSitemapFilter($this->getId());

        if ($type != null)
            $collection->setTypeFilter($type);

        return $collection;
    }

    protected function _beforeSave()
    {
        $io = new Varien_Io_File();
        $io->setAllowCreateFolders(true);

        $pathmap = $this->getConfig()->getPathMap();

        // this is the path that will be used for save the files ( pathmap )
        $realPath = $io->getCleanPath(Mage::getBaseDir() . DS . $this->getSitemapPath());
        // this is the path that will be displayed
        $realPath_db = $realPath;

        if ($pathmap != '') {
            $realPath = $io->getCleanPath(Mage::getBaseDir() . DS . $pathmap . DS . $this->getSitemapPath());
        }

        /**
         * Check exists and writeable path
         */
        if (!$io->fileExists($realPath, false)) {
            if (!$io->checkAndCreateFolder($realPath)) {
                Mage::throwException(Mage::helper('sitemapEnhancedPlus')->__('Please create the specified folder "%s" before saving the sitemap.', Mage::helper('core')->htmlEscape($realPath)));
            }
        }

        if (!$io->isWriteable($realPath)) {
            Mage::throwException(Mage::helper('sitemapEnhancedPlus')->__('Please make sure that "%s" is writable by web-server.', $realPath));
        }
        /**
         * Check allow filename
         */
        if (!preg_match('#^[a-zA-Z0-9_\.]+$#', $this->getSitemapFilename())) {
            Mage::throwException(Mage::helper('sitemapEnhancedPlus')->__('Please use only letters (a-z or A-Z), numbers (0-9) or underscore (_) in the filename. No spaces or other characters are allowed.'));
        }

        if ($this->getConfig()->getUseCompression())
            $this->setSitemapFilename($this->getHelper()->clearExtension($this->getSitemapFilename()) . '.xml.gz');
        else
            $this->setSitemapFilename($this->getHelper()->clearExtension($this->getSitemapFilename()) . '.xml');

        if (!$this->getHelper()->isUnique($this)) {
            Mage::throwException(Mage::helper('sitemapEnhancedPlus')->__('Please select another filename/path, as another sitemap with same filename already exists on the specified location.'));
        }

        $this->setSitemapPath(rtrim(str_replace(str_replace('\\', '/', Mage::getBaseDir()), '', $realPath_db), '/') . '/');

        return Mage_Core_Model_Abstract::_beforeSave();
    }

    public function addFirstFile($append, $sepCounter = true)
    {
        $filename = $this->getHelper()->clearExtension($this->getSitemapFilename());
        $filename .= $append;
        $this->_addFile(null, $filename);

        if ($sepCounter) {
            // reset the counter
            $this->_sepFileCounter = 1;
        }
    }

    /**
     * Add a file to the sitemap file collection
     *
     * @param $type int
     * @param $filename string
     * @return CueBlocks_SitemapEnhancedPlus_Model_SitemapEnhancedPlusFiles
     */
    protected function _addFile($type = null, $filename = null)
    {
        if ($filename == null) {
            $filename = $this->getSitemapFilename();
        }

        if ($type == null) {
            $type = $this->getSitemapType();
        }

        $filename = $this->getHelper()->clearExtension($filename);
        $ext = $this->getConfig()->getUseCompression() ? '.xml.gz' : '.xml';

        if ($type == CueBlocks_SitemapEnhancedPlus_Model_SitemapEnhancedPlus::TYPE_INDEX) {
            $filename = $filename . '_index';
        }
        $filename = $filename . $ext . self::TMP_EXT;

        /* @var $_file CueBlocks_SitemapEnhancedPlus_Model_SitemapEnhancedPlusFiles */
        $_file = Mage::getModel('sitemapEnhancedPlus/sitemapEnhancedPlusFiles');
        $_file->setSitemapId($this->getId());
        $_file->setSitemapFilePath($this->getPath());
        $_file->setSitemapFileFilename($filename);
        $_file->setSitemapFileType($type);
        $_file->setUseCompression($this->getConfig()->getUseCompression());
        $_file->initIoWrite();

        // close last file
        $lastFile = $this->_ioCollection->getLastItem();
        if ($lastFile)
            $lastFile->closeIoWrite();

        $this->_ioCollection->addItem($_file);

        return $_file;
    }

    public function addLink($xml, $type = null, $useSepCounter = false)
    {
        if ($type == null) {
            $type = $this->getSitemapType();
        }

        $fileModel = $this->_ioCollection->getLastItem();
        $count = $useSepCounter ? $this->_sepFileCounter : $this->_ioCollection->count();

        // check limits
        if ($type != CueBlocks_SitemapEnhancedPlus_Model_SitemapEnhancedPlus::TYPE_INDEX) {

            $fileModelLinks = $fileModel->getIo()->getLinks();
            $fileModelBytes = $fileModel->getIo()->getSize();
            // string bytes
            $strByte = mb_strlen($xml, 'UTF-8');

            $byteLimit = $this->getConfig()->getBytesLimit() - self::FIX_BYTE;

            if (
                ($strByte + $fileModelBytes) >= $byteLimit
                || ($fileModelLinks + 1) >= $this->getConfig()->getLinksLimit()
            ) {
                // add a new file
                $fileModelFilename = $this->getHelper()->clearExtension($fileModel->getSitemapFileFilename());
                $fileModelFilename = str_replace(array('_' . ($count - 1)), '', $fileModelFilename);
                $newFileName = $fileModelFilename . '_' . ($count);
                if ($useSepCounter)
                    $this->_sepFileCounter += 1;

                $this->_addFile($fileModel->getSitemapFileType(), $newFileName);
                $fileModel = $this->_ioCollection->getLastItem();
            }
            $this->setSitemapTotLinks($this->getSitemapTotLinks() + 1);
        }

        $fileModel->getIo()->streamWrite($xml);
        $fileModel->getIo()->increaseLinks(1);
    }

    public function addToRobots()
    {
        $robotModel = Mage::getModel('sitemapEnhancedPlus/robots');
        $fileUrl = $this->getLinkForRobots();
        $fileName = $fileUrl['filename'];
        $url = $fileUrl['url'];

        if (file_exists(BP . DS . $fileName)) {

            if ($robotModel->hasPermission()) {
                $ret = $robotModel->addSitemap($url);
                return $ret;
            } else {
                throw new Mage_Core_Exception("Robots.txt has wrong permission is not possible to read/write it.");
            }
        } else {
            throw new Mage_Core_Exception("Sitemap could not be found in required location: " . BP . DS . $fileName);
        }
    }

    /**
     * Check if the url is allowed in the robots.txt
     *
     * @param $url string
     * @return bool
     */
    public function isUrlAllowed($url)
    {
        $enabled = $this->getConfig()->getParseRobots();

        if ($enabled) {
            $robotModel = Mage::getSingleton('sitemapEnhancedPlus/robots');
            $robotModel->setSitemap($this);
            $isAllowed = $robotModel->isAllowed($url);

            // write in log rhe skipped url
            if (!$isAllowed) {
                $this->_disallowed += 1;
                $io = $this->getDisallowedLogFile();
                $io->streamWrite($url . "\n");
            }
            return $isAllowed;
        } else {
            return true;
        }
    }

    /**
     * Log File for skipped URLS
     *
     * @return Varien_Io_File
     */
    public function getDisallowedLogFile()
    {
        if (!$this->_disallowed_log_file) {
            $filename = $this->_getDisallowedLogFileName();
            $this->_disallowed_log_file = $this->_getNewFile($this->getPath(), $filename);
        }

        return $this->_disallowed_log_file;
    }

    public function _getDisallowedLogFileName()
    {
        $filename = $this->getHelper()->clearExtension($this->getSitemapFilename());
        $filename .= self::DISALLOWED_FILENAME . '.txt';

        return $filename;
    }

    public function getFileUrl($filename)
    {
        $fileName = preg_replace('/^\//', '', $this->getSitemapPath() . $filename);
        $url = $this->getBaseUrl(true) . $fileName;
        return $url;
    }

    public function getFilePath($filename)
    {
        $pathmap = $this->getConfig()->getPathMap();
        $path = BP . DS . $this->getSitemapPath();

        return $path . $filename;
    }

    protected function _getNewFile($path, $filename)
    {
        $filePath = $path . $filename;
        // remove old log
        if (file_exists($filePath))
            $res = unlink($filePath);

        $io = new Varien_Io_File();
        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $path));
        $io->streamOpen($filename);

        return $io;
    }

    public function isSepCategory()
    {
        $divideBy = $this->getConfig()->getDivideBy();
        return ($divideBy == self::DIVIDE_BY_TOPCATEGORY || $divideBy == self::DIVIDE_BY_SUBCATEGORY);
    }

}
