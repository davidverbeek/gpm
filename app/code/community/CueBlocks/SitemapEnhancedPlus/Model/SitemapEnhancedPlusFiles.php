<?php

/**
 * Description of SitemapEnhancedPlusFiles
 * @package   CueBlocks_SitemapEnhancedPlus
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhancedPlus_Model_SitemapEnhancedPlusFiles extends Mage_Core_Model_Abstract
{

    /**
     * Io File Model
     *
     * @var CueBlocks_SitemapEnhancedPlus_Model_SitemapEnhancedPlusIoFile
     */
    protected $_io;

    public function _construct()
    {
        $this->_init('sitemapEnhancedPlus/sitemapEnhancedPlusFiles');
    }

    public function initIoWrite()
    {
        if ($this->_io == null) {
            $this->_io = Mage::getModel('sitemapEnhancedPlus/sitemapEnhancedPlusIoFile');
            $this->_io->setAllowCreateFolders(true);
            $this->_io->open(array('path' => $this->getSitemapFilePath()));

            if ($this->_io->fileExists($this->getSitemapFileFilename()) && !$this->_io->isWriteable($this->getSitemapFileFilename())) {
                Mage::throwException(Mage::helper('sitemap')->__('File "%s" cannot be saved. Please, make sure the directory "%s" is writeable by web server.', $this->getSitemapFileFilename(), $this->getPath()));
            }

            $this->_io->init($this->getSitemapFileFilename(), $this->getUseCompression(), $this->getSitemapFileType());
            $this->addHeader();
        }
    }

    public function getIo()
    {
        return $this->_io;
    }

    public function getContent()
    {
        $size = filesize($this->getSitemapFileFilename());

        $this->_io = Mage::getModel('sitemapEnhancedPlus/sitemapEnhancedPlusIoFile');
        $this->_io->open(array('path' => $this->getSitemapFilePath()));
        $this->_io->init($this->getSitemapFileFilename(), $this->getUseCompression());
        $this->_io->streamClose();
//        $content = $this->_io->streamRead($size);

//        return $content;
    }

    public function addHeader()
    {
        $this->getIo()->streamWrite('<?xml version="1.0" encoding="UTF-8"?>' . "\n");

        //Add comment to head
        $this->getIo()->streamWrite("<!--- Thank you for using the 'XML Sitemap Plus Generator & Splitter' Extension of CueBlocks.com -->" . "\n");

        switch ($this->getSitemapFileType()) {
            case CueBlocks_SitemapEnhancedPlus_Model_SitemapEnhancedPlus::TYPE_REGULAR:
                $header = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
                break;
            case CueBlocks_SitemapEnhancedPlus_Model_SitemapEnhancedPlus::TYPE_IMAGE:
                $header = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
                xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';
                break;
            case CueBlocks_SitemapEnhancedPlus_Model_SitemapEnhancedPlus::TYPE_MOBILE:
                $header = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
  xmlns:mobile="http://www.google.com/schemas/sitemap-mobile/1.0">';
                break;
            case CueBlocks_SitemapEnhancedPlus_Model_SitemapEnhancedPlus::TYPE_INDEX:
                $header = '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
                break;
        }
        $this->getIo()->streamWrite($header);
    }

    public function closeIoWrite()
    {
        if ($this->getIo() != null) {
            if ($this->getSitemapFileType() == CueBlocks_SitemapEnhancedPlus_Model_SitemapEnhancedPlus::TYPE_INDEX)
                $this->getIo()->streamWrite('</sitemapindex>');
            else
                $this->getIo()->streamWrite('</urlset>');

            $this->getIo()->streamClose();
            $this->setLinksCount($this->getIo()->getLinks());
        }
    }

    protected function _beforeSave()
    {
        parent::_beforeSave();
        $tmpfilename = $this->getSitemapFileFilename();
        $newFilename = $this->removeTempExtension($tmpfilename);
        $this->getIo()->mv($tmpfilename,$newFilename);
        $this->setSitemapFileFilename($newFilename);

        return $this;
    }

    public function removeTempExtension($tmpfilename)
    {
        $tmpExt = CueBlocks_SitemapEnhancedPlus_Model_SitemapEnhancedPlusAbstract::TMP_EXT;
        return str_replace($tmpExt, '', $tmpfilename);
    }
}

