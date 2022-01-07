<?php

/**
 * Description of SitemapEnhancedPlus
 * @package   CueBlocks_SitemapEnhancedPlus
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhancedPlus_Model_Processor_Catalog_Category extends CueBlocks_SitemapEnhancedPlus_Model_Processor_Abstract
{
    protected $_configKey = 'category';
    protected $_sourceModel = 'sitemapEnhancedPlus/processor_catalog_category';
    protected $_fileName = '_cat';
    protected $_counterLabel = 'Category';
    protected $_topCategory = null;

    protected $_prodCounter;
    protected $_imageCounter;

//    protected function __getProcessCollection()
//    {
//
// excluded category list
//       $excludedCat = $this->getConfig()->getExcludedCategory() ? explode(',', $this->getConfig()->getExcludedCategory()) : null;
//
//    ...
//    }

    public function _getQueryModelConfig($usePagination = false)
    {
        $config = parent::_getQueryModelConfig($usePagination);

        $extraConfigData = array(
            'cat_id' => $this->getCatId(),
            'add_path_order' => $this->isDivideByTopCategory(),
            'exclude_category_ids' => null,
        );
        $config->addData($extraConfigData);
        return $config;
    }

    public function isDivideByTopCategory()
    {
        return ($this->getSitemap()->getConfig()->getDivideBy() == CueBlocks_SitemapEnhancedPlus_Model_SitemapEnhancedPlusAbstract::DIVIDE_BY_TOPCATEGORY);
    }

    protected function _getUrl($row)
    {
        $url = '';

        $catId = $row['entity_id'];
        $url = !empty($row['url']) ? $row['url'] : 'catalog/category/view/id/' . $catId;

        if (empty($row['url'])) {
            $this->getSitemap()->addWarning("Category (ID:$catId) has no URL rewrite. Link: $url");
        }

        return $url;
    }

    protected function _getFileName($row = null)
    {
        if ($this->getSitemap()->isSepCategory()) {
            // add a new file
            $urlarr = explode('/', $row['url']);
            // extract only the url key and remove the ".html"
            // multiple category can have same url key,
            // entity_id is required because it make file name unique
            $name = explode('.', end($urlarr));
            $name = $row['entity_id'] . $name[0];
            $catName = preg_replace('/[^a-zA-Z0-9\.]/', '_', $name); // remove all non-alphanumeric chars
            $filename = $this->getSitemap()->getHelper()->clearExtension($this->getSitemapFilename());
            $filename .= $this->_fileName . '_' . $catName;

            return $filename;
        } else {
            return parent::_getFileName($row);
        }
    }

    protected function _preProcess()
    {
        // no first file for separate category
        if ($this->getSitemap()->isSepCategory()) {
            $this->_firstFile = FALSE;
            $this->_prodCounter = new Varien_Object();
            $this->_imageCounter = new Varien_Object();
        }
        parent::_preProcess();
    }

    protected function _processRow($row)
    {
        $xml = parent::_processRow($row);

        if (!$this->getSitemap()->isSepCategory()) {
            return $xml;
        }

        if ($this->getSitemap()->isSepCategory() && $xml) {

            $divideBy = $this->getSitemap()->getConfig()->getDivideBy();
            switch ($divideBy) {
                case CueBlocks_SitemapEnhancedPlus_Model_SitemapEnhancedPlusAbstract::DIVIDE_BY_SUBCATEGORY:
                    $this->getSitemap()->addFirstFile($this->_getFileName($row));
                    break;
                case CueBlocks_SitemapEnhancedPlus_Model_SitemapEnhancedPlusAbstract::DIVIDE_BY_TOPCATEGORY:
                    // cat. are order by path
                    $topArray = $this->_getTopCategory();
                    if (in_array($row['entity_id'], $topArray)) {
                        $this->getSitemap()->addFirstFile($this->_getFileName($row));
                    }
                    break;
            }
            // print url to sitemap file
            $this->_addUrl($xml);
            // get product link for this category
            Mage::getModel('sitemapEnhancedPlus/processor_catalog_productByCat')
                ->setCounter($this->_prodCounter)
                ->setImageCounter($this->_imageCounter)
                ->setCatId($row['entity_id'])
                ->process($this->getSitemap());
            // avoid to add 2 times this category link
            return false;
        }
    }

    protected function _postProcess()
    {
        if ($this->getSitemap()->isSepCategory()) {
            if ($this->_counter) {
                $extraCounter = new Varien_Data_Collection();
                $extraCounter->addItem($this->_prodCounter);
                $extraCounter->addItem($this->_imageCounter);
                $this->_counter->setExtraCounter($extraCounter);
            }
        }
        parent::_postProcess();
    }

    protected function _getTopCategory()
    {
        if (!$this->_topCategory) {
            $store = Mage::getModel('core/store')->load($this->getSitemap()->getStoreId());
            $parent = $store->getRootCategoryId();
            $child = Mage::getModel('catalog/category')
                ->load($parent)
                ->getChildren();
            $this->_topCategory = explode(',', $child);
        }
        return $this->_topCategory;
    }

    protected function _filterRow($row)
    {
        $filterIt = parent::_filterRow($row);
        if (!$filterIt && $this->getSitemap()->getConfig()->getUseCategoryFilter()) {
            $filterHelper = Mage::helper('sitemapEnhancedPlus/categoryFilter');
            $id = $row['entity_id'];
            $filterIt = $filterHelper->filterCategory($id, $this->getSitemap());

            if ($filterIt) {
                $url = 'CAT-URL:' . htmlspecialchars($this->getSitemap()->getBaseUrl() . $this->_getUrl($row));
                $this->getSitemap()->addDisallowed($url);
            }
        }
        return $filterIt;
    }
}
