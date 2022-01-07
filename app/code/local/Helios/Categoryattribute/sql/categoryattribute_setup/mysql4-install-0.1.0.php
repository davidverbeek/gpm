<?php
$installer = $this;
$installer->startSetup();


$installer->addAttribute("catalog_category", "category_short_headline",  array(
    "type"     => "varchar",
    "backend"  => "",
    "frontend" => "",
    "label"    => "Short headline",
    "input"    => "text",
    "class"    => "",
    "source"   => "",
    "global"   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    "visible"  => true,
    "required" => false,
    "user_defined"  => false,
    "default" => "",
    "searchable" => false,
    "filterable" => false,
    "comparable" => false,
    "group" => "General Information",
    "visible_on_front"  => true,
    "unique"     => false,
    "note"       => ""
	));

$installer->addAttribute("catalog_category", "category_level3_banner",  array(
	'type' => 'varchar',
	'label' => 'Level 3 banner Image',
	'note' => 'Minimum width x Height (707px x 266px)',
	'input' => 'image',
	"frontend" => "",
	'backend' => 'catalog/category_attribute_backend_image',
	'required' => false,
	'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
	'visible' => true,
	'required' => false,
	'user_defined' => false,
	'visible_on_front' => true,
	'used_in_product_listing' => false,
	'is_html_allowed_on_front' => false,
        "group" => "General Information",
        "default" => "",
        "searchable" => false,
        "filterable" => false,
        "comparable" => false,
        "unique"     => false,
        "note"       => ""
	));
$installer->endSetup();
	 