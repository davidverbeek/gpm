<?php
$installer = $this;
$installer->startSetup();


$installer->addAttribute("catalog_category", "custom_category_description",  array(
    'type' => 'text',
    'label' => 'Static Description',
    'input' => 'textarea',
    'default' => '',
    'sort_order' => 99,
    'required' => false,
    'wysiwyg_enabled' => true,
    'visible_on_front' => false,
    'is_html_allowed_on_front' => true,
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'group' => 'General Information',
));


$installer->addAttribute("catalog_product", "custom_product_description",  array(
    'type' => 'text',
    'label' => 'Static Description',
    'input' => 'textarea',
    'default' => '',
    'sort_order' => 99,
    'required' => false,
    'wysiwyg_enabled' => true,
    'is_visible_on_front' => false,
    'is_html_allowed_on_front' => true,
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'group' => 'General',
));

$installer->endSetup();
