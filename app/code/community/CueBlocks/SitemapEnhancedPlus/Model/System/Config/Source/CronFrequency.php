<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class CueBlocks_SitemapEnhancedPlus_Model_System_Config_Source_CronFrequency
{

    protected static $_options;

    const CRON_DAILY    = 'D';
    const CRON_WEEKLY   = 'W';
    const CRON_MONTHLY  = 'M';

    public function toOptionArray()
    {
        if (!self::$_options) {
            self::$_options = array(
                array(
                    'label' => Mage::helper('cron')->__('Daily (Once in a day)'),
                    'value' => self::CRON_DAILY,
                ),
                array(
                    'label' => Mage::helper('cron')->__('Weekly (Once in a week)'),
                    'value' => self::CRON_WEEKLY,
                ),
                array(
                    'label' => Mage::helper('cron')->__('Monthly (Once in a month)'),
                    'value' => self::CRON_MONTHLY,
                ),
            );
        }
        return self::$_options;
    }

}
