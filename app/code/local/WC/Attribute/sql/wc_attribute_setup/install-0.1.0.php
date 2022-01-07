<?php
// Add Weight attribute back

/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer = $this;

$installer->startSetup();

// 1. Add Weight Attribute from app/code/core/Mage/Catalog/Model/Resource/Setup.php class
$attributeData = array(
    'type'          => 'decimal',
    'label'         => 'Weight',
    'input'         => 'weight',
    'sort_order'    => 5,
    'apply_to'      => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
);

$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'weight', $attributeData);

// 2. Data Upgrade script from app/code/core/Mage/Catalog/data/catalog_setup/data-upgrade-1.6.0.0.8-1.6.0.0.9.php

/** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
$attribute = $installer->getAttribute(Mage_Catalog_Model_Product::ENTITY, 'weight');

if ($attribute) {
    $installer->updateAttribute($attribute['entity_type_id'], $attribute['attribute_id'],
        'frontend_input',  $attribute['attribute_code']);
}

// 3. Sql Install script from app/code/core/Mage/Bundle/sql/bundle_setup/mysql4-install-0.1.0.php

$fieldList = array('weight');
foreach ($fieldList as $field) {
    $applyTo = explode(',', $installer->getAttribute(Mage_Catalog_Model_Product::ENTITY, $field, 'apply_to'));
    if (!in_array('bundle', $applyTo)) {
        $applyTo[] = 'bundle';
        $installer->updateAttribute(Mage_Catalog_Model_Product::ENTITY, $field, 'apply_to', join(',', $applyTo));
    }
}

$installer->endSetup();
