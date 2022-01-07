<?php
$installer = $this;
$installer->startSetup();

$installer->addAttribute("catalog_product", "custom_product_name",  array(
    'type' => 'text',
    'label' => 'Static Name',
    'input' => 'text',
    'default' => '',
    'sort_order' => 98,
    'required' => false,
    'is_visible_on_front' => false,
    'is_html_allowed_on_front' => true,
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'group' => 'General',
));

$installer->endSetup();
