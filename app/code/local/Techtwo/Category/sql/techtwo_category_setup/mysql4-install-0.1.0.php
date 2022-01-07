<?php

$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

$setup->addAttribute('catalog_category', 'heading_tag', array(
    'group'         => 'General',
    'input'         => 'text',
    'type'          => 'varchar',
    'label'         => 'Heading Tag',
    'backend'       => '',
    'visible'       => 1,
    'required'      => 0,
    'user_defined'  => 1,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));

$setup->addAttribute('catalog_category', 'alt_tag_bestandnaam', array(
    'group'         => 'General',
    'input'         => 'text',
    'type'          => 'varchar',
    'label'         => 'Alt Tag Bestandsnaam',
    'backend'       => '',
    'visible'       => 1,
    'required'      => 0,
    'user_defined'  => 1,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));

$setup->addAttribute('catalog_category', 'marge', array(
    'group'         => 'General',
    'input'         => 'text',
    'type'          => 'varchar',
    'label'         => 'Marge',
    'backend'       => '',
    'visible'       => 1,
    'required'      => 0,
    'user_defined'  => 1,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));

$installer->endSetup();
