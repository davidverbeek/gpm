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
 * Articles of category block
 */
class AW_Kbase_Block_Category extends AW_Kbase_Block_List_Container
{
    protected function _prepareCollection()
    {
        $this->_collection = Mage::getResourceModel('kbase/article_collection')
            ->addCategoryFilter($this->getCategoryId())
            ->addTags()
            ->addStatusFilter()
            ->addStoreFilter();

        return parent::_prepareCollection();
    }

    protected function _preparePage()
    {
        parent::_preparePage();

        $this
            ->setBlockType('category')
            ->setTitle($this->getCategory()->getCategoryName())
            ->setRatingEnabled(Mage::getStoreConfig('kbase/general/rating_enabled'));

        $this->addBreadcrumb(
                $this->getCategory()->getCategoryName(),
                false
            );
    }

}
