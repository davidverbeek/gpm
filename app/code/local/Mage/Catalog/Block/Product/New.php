<?php

class Mage_Catalog_Block_Product_New extends Mage_Catalog_Block_Product_Abstract {

    public function getSaleCategory() {
        $categoryName = Mage::getStoreConfig('shoppersettings/catalog/salecategory');
        $_category = Mage::getResourceModel('catalog/category_collection')
                ->addFieldToFilter('name', $categoryName)
                ->getFirstItem();
        return $_category;
    }

    public function getSaleProducts() {
        $limit = Mage::getStoreConfig('shoppersettings/catalog/product_slider_limit');
        $_category = $this->getSaleCategory();

        $prodCollection = Mage::getResourceModel('catalog/product_collection')
                ->addCategoryFilter($_category);
        $prodCollection->addAttributeToSelect(array('name', 'sku', 'small_image', 'featured', 'featuredlabel', 'verkoopeenheid', 'afwijkenidealeverpakking', 'prijsfactor', 'artikelstatus', 'idealeverpakking', 'afwijkenidealeverpakking', 'leverancier'));

        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($prodCollection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($prodCollection);

        $prodCollection->getSelect()->limit($limit);
        return $prodCollection;
    }

    public function getSaleCategoryUrl() {
        $_catid = $this->getSaleCategoryId();
        $_caturl = Mage::getModel("catalog/category")->load($_catid)->getUrl();
        if (!isset($_caturl)) {
            $_caturl = '';
        }
        return $_caturl;
    }

    public function getSaleCategoryId() {
        $_category = $this->getSaleCategory();
        return $_category->getId();
    }

}

?>