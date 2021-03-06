<?php

/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 * 
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Kbase
 * @copyright  Copyright (c) 2009-2010 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */
/*
 * Article block
 */
class AW_Kbase_Block_Article extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('aw_kbase/article.phtml');
    }

    /*
     * Adds page layout breadcrumbs
     * @see AW_Kbase_Block_General_Breadcrumbs::addItem()
     */
    public function addBreadcrumb($name, $url=false, $title='')
    {
        if($this->getChild('kbase_breadcrumbs'))
            $this->getChild('kbase_breadcrumbs')
                ->addItem($name, $url, $title);

        return $this;
    }

    public function preparePage()
    {
        $this
            ->addBreadcrumb(
                    $this->__('Home'),
                    Mage::getBaseUrl(),
                    $this->__('Go to Home Page')
                )
            ->addBreadcrumb(
                    Mage::getStoreConfig('kbase/general/title'),
                    AW_Kbase_Helper_Url::getUrl(AW_Kbase_Helper_Url::URL_TYPE_MAIN),
                    $this->__('Go to %s homepage', Mage::getStoreConfig('kbase/general/title'))
                );

        if($this->getCategory())
            $this->addBreadcrumb(
                    $this->getCategory()->getCategoryName(),
                    AW_Kbase_Helper_Url::getUrl(AW_Kbase_Helper_Url::URL_TYPE_CATEGORY, $this->getCategory()->getData())
                );

        $this->addBreadcrumb(
                    $this->getArticle()->getArticleTitle(),
                    false
                );

        if( ($head = $this->getLayout()->getBlock('head'))
        &&  $breadcrumbs = $this->getChild('kbase_breadcrumbs')
        )   $head->setTitle($breadcrumbs->getPageTitle());

        return $this;
    }

    protected function _toHtml()
    {
        $this->setRatingEnabled(Mage::getStoreConfig('kbase/general/rating_enabled'));

        // processing article text
        $processorModelName = AW_Kbase_Helper_Data::mageVersionIsAbove13()
                                ? 'widget/template_filter'
                                : 'core/email_template_filter';

        $processor = Mage::getModel($processorModelName);

        if($processor instanceof Mage_Core_Model_Email_Template_Filter)
            $this->setProcessedText($processor->filter($this->getArticle()->getArticleText()));
        else
            $this->setProcessedText($this->getArticle()->getArticleText());

        return parent::_toHtml();
    }

}
