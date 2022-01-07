<?php
/**
 * @copyright   Copyright (c) 2013 AZeBiz Co. LTD
 */
class Magebuzz_Faq_Model_Status extends Varien_Object {
    const STATUS_ENABLED	= 1;
    const STATUS_DISABLED	= 0;

		// Const status for suggestion
		
		const STATUS_CONVERTED	= 1;
    const STATUS_PENDING	= 0;
		
    static public function getOptionArray()
    {
        return array(
            self::STATUS_ENABLED    => Mage::helper('faq')->__('Enabled'),
            self::STATUS_DISABLED   => Mage::helper('faq')->__('Disabled')
        );
    }
		
		static public function getSuggestOptionArray()
    {
        return array(
            self::STATUS_PENDING    => Mage::helper('faq')->__('Pending'),
            self::STATUS_CONVERTED   => Mage::helper('faq')->__('Converted')
        );
    }
}