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
 * Page breadcrumbs
 */
class AW_Kbase_Block_General_Footer extends Mage_Core_Block_Template
{
    public function addFooterLinks()
    {
        if (Mage::helper('kbase')->isModuleOutputDisabled() || !Mage::helper('kbase')->getFrontendEnabled()) return;
        $label = Mage::getStoreConfig('kbase/general/title');
        $title = 'To go '.$label.' Home Page';
        $this->getParentBlock()->addLink($label, 'kbase', $title, true, array(), 150, null);
    }
}