<?php
class Hs_CanonicalUpdate_Block_Catalog_Category_View extends Mage_Catalog_Block_Category_View
{
  /**
     * Add meta information from category to head block
     *
     * @return Mage_Catalog_Block_Category_View
     */
  protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $fullUrl = '';
        $this->getLayout()->createBlock('catalog/breadcrumbs');

        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $category = $this->getCurrentCategory();
            if ($title = $category->getMetaTitle()) {
                $headBlock->setTitle($title);
            }
            if ($description = $category->getMetaDescription()) {
                $headBlock->setDescription($description);
            }
            if ($keywords = $category->getMetaKeywords()) {
                $headBlock->setKeywords($keywords);
            }
            if ($this->helper('catalog/category')->canUseCanonicalTag()) {
                $headBlock->removeItem('link_rel', $category->getUrl());
                $p = $this->getClearUrl();
                if($p != ""){
                    $fullUrl = strstr($category->getUrl(),".html", true) . ".html?".$p;;
                    $headBlock->addLinkRel('canonical', $fullUrl);
                }else{
                    $fullUrl = str_replace('?___SID=U', '', $category->getUrl());
                    $headBlock->addLinkRel('canonical', $fullUrl);
                }
            }
            /*
            want to show rss feed in the url
            */
            if ($this->IsRssCatalogEnable() && $this->IsTopCategory()) {
                $title = $this->helper('rss')->__('%s RSS Feed',$this->getCurrentCategory()->getName());
                $headBlock->addItem('rss', $this->getRssLink(), 'title="'.$title.'"');
            }
        }

        return $this;
    }
    public function getClearUrl()
    {
        $request = Mage::getUrl('*/*/*', array('_current'=>true, '_use_rewrite'=>true));
        if(strpos($request,'p=') !== false ){
            $query_string = substr($request,strpos($request,'?'));
            $query_string = substr($query_string,strpos($query_string,'p='));
        }
        else{
            $query_string = '';
        }
        return $query_string;
    }
}

