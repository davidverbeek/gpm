<?php

/**
 * Description of SitemapEnhancedPlusIoFile
 * @package   CueBlocks_SitemapEnhancedPlus
 * @company    CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhancedPlus_Model_SitemapEnhancedPlusIoFile extends Varien_Io_File
{

    /**
     * File type: 'index' or 'sitemap'
     *
     * @var string
     */
    protected $_type;

    /**
     * File Size
     *
     * @var int
     */
    protected $_size = 0;

    /**
     * Links counter
     *
     * @var int
     */
    protected $_links = 0;

    /**
     * function definition
     *
     * @var string
     */
    protected $_openFunction = 'fopen';
    protected $_readFunction = 'fread';
    protected $_writeFunction = 'fwrite';
    protected $_closeFunction = 'fclose';

    public function getLinks()
    {
        return $this->_links;
    }

    public function getSize()
    {
        return $this->_size;
    }

    public function increaseLinks($links)
    {
        $this->_links += $links;
    }

    public function increaseSize($bytes)
    {
        $this->_size += $bytes;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function setGzFunctions()
    {
        $this->_openFunction = 'gzopen';
        $this->_readFunction = 'gzread';
        $this->_writeFunction = 'gzwrite';
        $this->_closeFunction = 'gzclose';
    }

    public function init($fileName, $isCompressed = false, $type = 'sitemap', $mode = 'w+', $chmod = 0666)
    {
        $this->_type = $type;

        if ($type != 'index' && $isCompressed) {
            $this->setGzFunctions();
            $mode = 'w9';
        }

        return $this->streamOpen($fileName, $mode, $chmod);
    }

    /**
     * Open file in stream mode
     * For set folder for file use open method
     *
     * @param string $fileName
     * @param string $mode
     * @return bool
     */
    public function streamOpen($fileName, $mode = 'w+', $chmod = 0666)
    {
        $writeableMode = preg_match('#^[wax]#i', $mode);
        if ($writeableMode && !is_writeable($this->_cwd)) {
            throw new Exception('Permission denied for write to ' . $this->_cwd);
        }

        if (!ini_get('auto_detect_line_endings')) {
            ini_set('auto_detect_line_endings', 1);
        }

        @chdir($this->_cwd);
        $this->_streamHandler = call_user_func(@$this->_openFunction, $fileName, $mode);
        @chdir($this->_iwd);
        if ($this->_streamHandler === false) {
            throw new Exception('Error write to file ' . $fileName);
        }

        $this->_streamFileName = $fileName;
        $this->_streamChmod = $chmod;
        return true;
    }

    /**
     * Binary-safe file write
     *
     * @param string $str
     * @return bytes
     */
    public function streamWrite($str)
    {
        if (!$this->_streamHandler) {
            return false;
        }
        try {
            $bytes = call_user_func(@$this->_writeFunction, $this->_streamHandler, $str);
            $this->increaseSize($bytes);

            return $bytes;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Binary-safe file read
     *
     * @param int $length
     * @return string
     */
    public function streamRead($length = 1024)
    {
        if (!$this->_streamHandler) {
            return false;
        }
        if (feof($this->_streamHandler)) {
            return false;
        }
//        return @call_user_func(@$this->_readFunction, $this->_streamHandler, $length);
        return fread($this->_streamHandler, $length);
    }

    /**
     * Close an open file pointer
     * Set chmod on a file
     *
     * @return bool
     */
    public function streamClose()
    {
        if (!$this->_streamHandler) {
            return false;
        }

        if ($this->_streamLocked) {
            $this->streamUnlock();
        }

        call_user_func(@$this->_closeFunction, $this->_streamHandler);

        $this->chmod($this->_streamFileName, $this->_streamChmod);
        $this->_streamHandler = null;

        return true;
    }

}
