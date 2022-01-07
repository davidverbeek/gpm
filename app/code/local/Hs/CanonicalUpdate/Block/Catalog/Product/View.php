<?php
class Hs_CanonicalUpdate_Block_Catalog_Product_View extends Mage_Catalog_Block_Product_View
{
  /**
     * Add meta information from product to head block
     *
     * @return Mage_Catalog_Block_Product_View
     */
    protected function _prepareLayout()
    {
        $fullUrl = '';
        $this->getLayout()->createBlock('catalog/breadcrumbs');
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $product = $this->getProduct();
            $title = $product->getMetaTitle();
            if ($title) {
                $headBlock->setTitle($title);
            }
            $keyword = $product->getMetaKeyword();
            $currentCategory = Mage::registry('current_category');
            if ($keyword) {
                $headBlock->setKeywords($keyword);
            } elseif($currentCategory) {
                $headBlock->setKeywords($product->getName());
            }
            $description = $product->getMetaDescription();
            if ($description) {
                $headBlock->setDescription( ($description) );
            } else {
                $headBlock->setDescription(Mage::helper('core/string')->substr($product->getDescription(), 0, 255));
            }
            if ($this->helper('catalog/product')->canUseCanonicalTag()) {
                $headBlock->removeItem('link_rel', $product->getUrlModel()->getUrl($product));
                $fullUrl =  $this->getFullProductUrl($product);
                $fullUrl = str_replace(' ', '?___SID=U', $fullUrl);
                $headBlock->addLinkRel('canonical', $fullUrl);
            }
        }

        return parent::_prepareLayout();
    }

    public function getFullProductUrl(Mage_Catalog_Model_Product $product = null){

        // Force display deepest child category as request path.
        $categories = $product->getCategoryCollection();
        $deepCatId = 0;
        $path = '';
        $productPath = false;

        foreach ($categories as $category) {
            // Look for the deepest path and save.
            if (substr_count($category->getData('path'), '/') > substr_count($path, '/')) {
                $path = $category->getData('path');
                $deepCatId = $category->getId();
            }
        }

        // Load category.
        $category = Mage::getModel('catalog/category')->load($deepCatId);

        // Remove .html from category url_path.
        $categoryPath = str_replace('.html', '',  $category->getData('url_path'));

        // Get product url path if set.
        $productUrlPath = $product->getData('url_path');

        // Get product request path if set.
        $productRequestPath = $product->getData('request_path');

        // If URL path is not found, try using the URL key.
        if ($productUrlPath === null && $productRequestPath === null) {
            $productUrlPath = $product->getData('url_key');
        }

        // Now grab only the product path including suffix (if any).
        if ($productUrlPath) {
            $path = explode('/', $productUrlPath);
            $productPath = array_pop($path);
        } elseif ($productRequestPath) {
            $path = explode('/', $productRequestPath);
            $productPath = array_pop($path);
        }

        // Now set product request path to be our full product url including deepest category url path.
        if ($productPath !== false) {
            if ($categoryPath) {
                // Only use the category path is one is found.
                $product->setData('request_path', $categoryPath . '/' . $productPath);
            } else {
                $product->setData('request_path', $productPath);
            }
        }

        return $product->getProductUrl();
    }
}

