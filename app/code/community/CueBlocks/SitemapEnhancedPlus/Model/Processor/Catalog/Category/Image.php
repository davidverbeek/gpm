<?php

/** @TODO: NEED TO BE COMPLETED
 * Description of SitemapEnhancedPlus
 * @package   CueBlocks_SitemapEnhancedPlus
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhancedPlus_Model_Processor_Catalog_Category_Image extends CueBlocks_SitemapEnhancedPlus_Model_Processor_Abstract
{
    protected $_sourceModel = 'sitemapEnhancedPlus/processor_catalog_category_image';
    protected $_counterLabel = 'image';

    /**
     * Process link source collection
     * NO PAGINATION
     * NO POST PROCESS
     */
    public function process($sitemap)
    {
        $this->setSitemap($sitemap);

        if ($this->isEnabled()) {
            $this->_preProcess();
            $this->_getProcessCollection();
        }
    }

    /**
     * NO ADD FILE
     *
     * @param bool $addFirst
     * @return string|void
     */
    protected function _getProcessCollection($addFirst = false)
    {
        $imgXml = '';
        $queryCollection = $this->getQueryModel()->getCollection();
        while ($row = $queryCollection->fetch()) {
            $imgXml .= $this->_processRow($row);
        }
        unset($queryCollection);

        return $imgXml;
    }

    public function _getQueryModelConfig($usePagination = false)
    {
        $config = parent::_getQueryModelConfig($usePagination);

        $extraConfigData = array(
            'cat_id' => $this->getCatId(),
        );
        $config->addData($extraConfigData);
        return $config;
    }

    protected function _processRow($row)
    {
        $imgXml = '';

        $imgUrl = $this->_getUrl($row);
        $imgXml .= sprintf('<image:image><image:loc>%s</image:loc></image:image>', $imgUrl);

        $this->_increaseLinkCounter();

        return $imgXml;
    }

    protected function _getUrl($row)
    {
        $url = '';
        $url = $this->getMediaUrl() . 'catalog/category' . $row['path'];

        return $url;
    }

    protected function getMediaUrl()
    {
        return Mage::app()
            ->getStore($this->getSitemap()->getStoreId())
            ->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA, false);
    }

    protected function _increaseLinkCounter()
    {
        if ($this->_counter)
            $this->_counter->setCount((int)$this->_counter->getCount() + 1);
    }

    public function setCounter($counter)
    {
        $this->_counter = $counter;
        return $this;
    }
}