<?php
/**
 * SilverTouch Technologies Limited.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.silvertouch.com/MagentoExtensions/LICENSE.txt
 *
 * @category   Sttl
 * @package    Sttl_Productblocks
 * @copyright  Copyright (c) 2011 SilverTouch Technologies Limited. (http://www.silvertouch.com/MagentoExtensions)
 * @license    http://www.silvertouch.com/MagentoExtensions/LICENSE.txt
 */ 
class Magebuzz_Faq_Block_Adminhtml_Faq_Edit_Tab_Ajax_Serializer extends Mage_Core_Block_Template
{
	/**
     * Init serializer
     *
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('faq/edit/serializer.phtml');
        return $this;
    }

    /**
     * Get products of current Product block in JSON format
     *
     */
    public function getProductsJSON()
    {
        $result = array();
        if ($this->getProducts()) {
            $isEntityId = $this->getIsEntityId();
            foreach ($this->getProducts() as $product) {
                $id = $isEntityId ? $product->getEntityId() : $product->getId();
                $result[$id] = $product->toArray(array('qty', 'position'));
                
            }
        }
        return $result ? Zend_Json_Encoder::encode($result) : '{}'; 
    }
     public function getProductJSON()
    {
        $result = array();
        if ($this->getProduct()) {
            $isEntityId = $this->getIsEntityId();
            foreach ($this->getProduct() as $product) {
                $id = $isEntityId ? $product->getEntityId() : $product->getId();
                $result[$id] = $product->toArray(array('qty', 'position'));

            }
        }
        return $result ? Zend_Json_Encoder::encode($result) : '{}';
    }
}