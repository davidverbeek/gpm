<?php
/**
 * Creates 5 attributes in the group "Mavis attributen"
* 
* !!!!!!!!!!! NOTE THIS SCRIPT FAILS IN: !!!!!!!!!!!
* apply_to = simple is never set
* comparable = never set
*
* since it's just 5 I decided to set this myself.
* I see it in -> Mage_Catalog_Model_Resource_Eav_Mysql4_Setup , bu it's not working
 */

/* @var $this Mage_Core_Model_Resource_Setup */

$this->startSetup();

// Create the attribute
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');


$group_name = 'Mavis attributen';

$attribute_group = $setup->getAttributeGroup(4, 'default', $group_name );
if ($attribute_group)
{
    /*
    echo "Attribute groep info<br />\r\n";
    echo "<pre>";
    print_r($attribute_group);
    echo "</pre><br />\r\n";
    */
}
else
{
    throw new Exception('No attribute group found named "'.$attribute_group.'"');
}

$attributes = array(
    'categorie1',
    'categorie2',
    'categorie3',
    'categorie4',
    'categorie5'
);

foreach ( $attributes as $attribute )
{

    $setup->addAttribute('catalog_product', $attribute,  array(
        'group'                 => $group_name,
        'type'                  => 'varchar',
        'backend'               => '',
        'frontend'              => '',
        'label'                 => $attribute,
        'input'                 => 'text',
        'global'                => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible'               => true,
        'required'              => false,
        'user_defined'          => true,
        'default'               => '',
        'visible'               => true,
        'visible_on_front'      => false,
        'visible_in_advanced_search' => false,
        'searchable'            => true,
        'filterable_in_search'  => false,
        'unique'                => false,
        'comparable'            => true,
        'is_html_allowed_on_front' => false, // yes i know is_ is used here, check Mage_Catalog_Model_Resource_Eav_Mysql4_Setup - Jonathan
        'is_configurable'          => false,
        'used_in_product_listing'  => false,
        'apply_to'                 => 'simple'
    ));

    // $setup->updateAttribute('catalog_product', $attribute, 'is_comparable', false);
    // $setup->updateAttribute('catalog_product', $attribute, 'apply_to', false);

}

$this->endSetup();

/*
is_global
is_user_defined
is_filterable
is_visible
is_required
is_visible_on_front
is_searchable
is_unique
is_configurable
frontend_class
is_visible_in_advanced_search
is_comparable
is_filterable_in_search
is_used_for_price_rules
position
is_html_allowed_on_front
used_in_product_listing
used_for_sort_by
frontend_input
backend_type
view_name
frontend_label
default_value
apply_to
*/