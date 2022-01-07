<?php

/**
 * Description of SitemapEnhancedPlus
 * @package   CueBlocks_SitemapEnhancedPlus
 * @company   CueBlocks - http://www.cueblocks.com/
 */
class CueBlocks_SitemapEnhancedPlus_Model_Processor_Catalog_Product_Image extends CueBlocks_SitemapEnhancedPlus_Model_Processor_Catalog_Product_Abstract
{

    const CUSTOM_LICENSE_URL = 1;

    protected $_sourceModel = 'sitemapEnhancedPlus/processor_catalog_product_image';
    protected $_counterLabel = 'image';
    protected $_configKey = 'image';

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

        $imageConfig = $this->getConfig()->getData();
        $config->addData($imageConfig);
        return $config;
    }

    protected function _processRow($row)
    {
        $imgXml = '';
        $title = '';
        $caption = '';
        /**
         * EXCLUDE VALUE SHOULD NOT BE USED:
         * ALSO IF AN IMAGE IS EXCLUDED IT IS SHOWN
         * IF ASSIGNED TO SOMETHING
         */
//            $img_enabled = true;

//            if ($imageRow['disabled'] === '1') {
//                $img_enabled = false;
//            } elseif ($imageRow['disabled'] === null && $imageRow['disabled_default'] === '1') {
//                $img_enabled = false;
//            }

//            if ($img_enabled) {
        $location = sprintf('<image:loc>%s</image:loc>', $this->_getUrl($row));
        if ($row['label']) {
            $title = sprintf('<image:title>%s</image:title>', htmlspecialchars($row['label']));
        }
        if ($row['short_description']) {
            $caption = sprintf('<image:caption>%s</image:caption>', htmlspecialchars($row['short_description']));
        }
        $license = $this->_getImageLicense();

        $imgXml .= sprintf('<image:image>%s %s %s %s</image:image>', $location, $title, $caption, $license);
        $this->_increaseLinkCounter();

        return $imgXml;
    }

    protected function _getUrl($row)
    {
        $url = '';
        $url = htmlspecialchars($this->getMediaUrl() . 'catalog/product' . $row['path']);

        return $url;
    }

    protected function getMediaUrl()
    {
        return Mage::app()
            ->getStore($this->getSitemap()->getStoreId())
            ->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA, false);
    }

    protected function _getImageLicense()
    {
        $license = '';
        $licenseTypeUrl = $this->getConfig()->getLicenseType();

        if ((bool)$licenseTypeUrl) {
            if ($licenseTypeUrl == self::CUSTOM_LICENSE_URL) {
                $licenseTypeUrl = $this->getConfig()->getLicenseTypeCustom();
            }
            // check that at list we have a content for the license
            if ($licenseTypeUrl) {
                $license = sprintf('<image:license>%s</image:license>', $licenseTypeUrl);
            }
        }

        return $license;
    }
}