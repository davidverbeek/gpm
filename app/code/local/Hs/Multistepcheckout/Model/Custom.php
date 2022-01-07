<?php

class Hs_Multistepcheckout_Model_Custom {


    public function productEan()
    {
        // get customer collection
        $products = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect();
        // call iterator walk method with collection query string and callback method as parameters
        Mage::getSingleton('core/resource_iterator')->walk($products->getSelect(), array(array($this, 'productCallback')));
    }

    // callback method
    public function productCallback($args)
    {
        $product = Mage::getModel('catalog/product'); // get customer model
        $product->setData($args['row']); // map data to customer model

        var_dump($args['row']);
        echo $product->getEancode();
        $product->setEancode($product->getEancode()); // set value of firstname attribute

        $product->getResource()->saveAttribute($product, 'eancode'); // save only changed attribute instead of whole object


    }

}
