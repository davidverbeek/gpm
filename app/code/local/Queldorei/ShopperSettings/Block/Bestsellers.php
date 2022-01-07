<?php
/**
 * @version   1.0 12.0.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

class Queldorei_ShopperSettings_Block_Bestsellers extends Mage_Catalog_Block_Product_Abstract
{
    public function __construct(){
        parent::_construct();
        $this->setData('bestsellers', Mage::getStoreConfig('shoppersettings/catalog/bestsellers'));
    }

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getBestsellers()
    {
       /* $id = $this->getData('bestsellers');
        if (  empty($id) ) return null;

	    $productIds = explode(',',$this->getData('bestsellers'));
        $products = Mage::getModel("catalog/product")
		    ->getCollection()
            ->addUrlRewrite()
	        ->addStoreFilter()
		    ->addAttributeToSelect("*")
		    ->addAttributeToFilter('entity_id', array('in' => $productIds));

        return $products;*/
        $visibility = array(
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG
        );

        $storeId = Mage::app()->getStore()->getId();
        $_productCollection = Mage::getResourceModel('reports/product_collection')
            ->addAttributeToSelect('*')
            ->addOrderedQty()
            ->addAttributeToFilter('visibility', $visibility)
            ->setOrder('ordered_qty', 'desc');
			$_productCollection->getSelect()->limit(3);

        return $_productCollection;
    }
}