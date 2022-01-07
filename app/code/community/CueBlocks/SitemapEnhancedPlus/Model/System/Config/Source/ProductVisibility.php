<?php
/**
 * Created by PhpStorm.
 * User: cueblocks
 * Date: 3/7/14
 * Time: 11:37 AM
 */
class CueBlocks_SitemapEnhancedPlus_Model_System_Config_Source_ProductVisibility
{

    public function toOptionArray()
    {
        $options = array();
        $allOptions = Mage::getModel('catalog/product_visibility')->getOptionArray();
        unset($allOptions[Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE]);

        foreach ($allOptions as $value => $label) {
            $options[] = array('label' => $label, 'value' => $value);
        }

        return $options;
    }

}