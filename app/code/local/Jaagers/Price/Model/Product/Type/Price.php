<?php

class Jaagers_Price_Model_Product_Type_Price extends Mage_Catalog_Model_Product_Type_Price
{
    /**
     * Get product group price
     *
     * @param Mage_Catalog_Model_Product $product
     * @return float
     */
    public function getGroupPrice($product)
    {

        $groupPrices = $product->getData('group_price');

        if (is_null($groupPrices)) {
            $attribute = $product->getResource()->getAttribute('group_price');
            if ($attribute) {
                $attribute->getBackend()->afterLoad($product);
                $groupPrices = $product->getData('group_price');
            }
        }

        if (is_null($groupPrices) || !is_array($groupPrices)) {
            return $product->getPrice();
        }

        $customerGroup = $this->_getCustomerGroupId($product);
        $customerGroups = explode(',',$customerGroup);

        $matchedPrice = $product->getPrice();
        foreach ($groupPrices as $groupPrice) {
            #if ($groupPrice['cust_group'] == $customerGroup && $groupPrice['website_price'] < $matchedPrice) {
            if (in_array($groupPrice['cust_group'],$customerGroups) && $groupPrice['website_price'] < $matchedPrice) {
                $matchedPrice = $groupPrice['website_price'];
                break;
            }
        }

        return $matchedPrice;
    }

}
