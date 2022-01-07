<?php

$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

$setup->addAttribute('catalog_category', 'custom_filters', array(
    'group'         => 'General',
    'input'         => 'text',
    'type'          => 'varchar',
    'label'         => 'Custom Filters',
    'backend'       => '',
    'visible'       => 1,
    'required'      => 0,
    'user_defined'  => 1,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));

$installer->endSetup();
