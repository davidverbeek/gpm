<?php
/**
 * ||GEISSWEB| EU VAT Enhanced
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GEISSWEB End User License Agreement
 * that is available through the world-wide-web at this URL:
 * http://www.geissweb.de/eula/
 *
 * DISCLAIMER
 *
 * Do not edit this file if you wish to update the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to our support for more information.
 *
 * @category    Mage
 * @package     Geissweb_Euvatgrouper
 * @copyright   Copyright (c) 2011 GEISS Weblösungen (http://www.geissweb.de)
 * @license     http://www.geissweb.de/eula/ GEISSWEB End User License Agreement
 */

class Geissweb_Euvatgrouper_Model_System_Config_Source_Oscintegration {

	/**
	 * Options getter
	 * @return array
	 */
	public function toOptionArray()
	{
		return array(
            array('value' => '', 'label' => Mage::helper('euvatgrouper')->__('-- NONE --')),
            array('value' => 'AHEADWORKS_CHECKOUT', 'label' => Mage::helper('euvatgrouper')->__('Aheadworks OnePageCheckout')),
            array('value' => 'AMASTY_CHECKOUT', 'label' => Mage::helper('euvatgrouper')->__('Amasty OnePageCheckout')),
            array('value' => 'CMSMART_CHECKOUT', 'label' => Mage::helper('euvatgrouper')->__('CMSMart OneStepCheckout')),
            array('value' => 'ECOMDEV_CHECKOUT', 'label' => Mage::helper('euvatgrouper')->__('EcomDev CheckItOut')),
            array('value' => 'ECOMTEAM_CHECKOUT', 'label' => Mage::helper('euvatgrouper')->__('Ecommerceteam EasyCheckout')),
            array('value' => 'FME_CHECKOUT', 'label' => Mage::helper('euvatgrouper')->__('FME OneStepCheckout')),
            array('value' => 'GOMAGE_CHECKOUT', 'label' => Mage::helper('euvatgrouper')->__('GoMage LightCheckout')),
            array('value' => 'IWD_CHECKOUT', 'label' => Mage::helper('euvatgrouper')->__('IWD One Step/Page Checkout')),
            array('value' => 'KALLYAS_OPC', 'label' => Mage::helper('euvatgrouper')->__('Kallyas One Page Checkout')),
            array('value' => 'MAGEGIANT_OPC', 'label' => Mage::helper('euvatgrouper')->__('MageGiant OPC')),
            array('value' => 'MAGESTORE_CHECKOUT', 'label' => Mage::helper('euvatgrouper')->__('MageStore OneStepCheckout')),
            array('value' => 'MAGEMART_QUICKCHECKOUT', 'label' => Mage::helper('euvatgrouper')->__('Magemart Quickcheckout')),
            array('value' => 'NEXTBITS_CHECKOUTNEXT', 'label' => Mage::helper('euvatgrouper')->__('Nextbits CheckoutNext')),
            array('value' => 'OCODEWIRE_ONESTEPCHECKOUT', 'label' => Mage::helper('euvatgrouper')->__('Ocodewire OneStepCheckout')),
			array('value' => 'ONESTEP_CHECKOUT', 'label' => Mage::helper('euvatgrouper')->__('IDEV OneStepCheckout')),
            array('value' => 'TM_CHECKOUT', 'label' => Mage::helper('euvatgrouper')->__('TM FireCheckout')),
            array('value' => 'UNI_OPC', 'label' => Mage::helper('euvatgrouper')->__('Unicode ExpressCheckout')),
            array('value' => 'VINAGENTO_CHECKOUT', 'label' => Mage::helper('euvatgrouper')->__('Vinagento Oscheckout')),
		);
	}
}