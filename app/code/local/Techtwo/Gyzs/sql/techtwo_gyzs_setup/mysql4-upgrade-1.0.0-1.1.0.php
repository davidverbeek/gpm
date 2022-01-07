<?php
/**
* Creates an multiselect attribute named 'Label' in Prices.. since in prices you make special_price
*/

/* @var $this Mage_Core_Model_Resource_Setup */
$this->startSetup();
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');


$setup->addAttribute('catalog_product', 'label', array(
    'group'                     => 'Prices',
    'label'                     => 'Label',
    'note'                      => '',
    'type'                      => 'varchar',    //backend_type ( select uses int ! )
    'input'                     => 'multiselect', //frontend_input ( see Varien_Data_Form_Element_* )
    'frontend_class'            => '',
    'backend'                   => 'eav/entity_attribute_backend_array',
    'frontend'                  => '',
    'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'required'                  => false,
    'visible_on_front'          => false,
    'user_defined'              => false,
    'option'                    => array (
                                    'order'  => array( 'a' => 0, 'b' => 1, 'c' => 2, 'd' => 3, 'e' => 4, 'f' => 5 ),
                                    'value'  => array(
                                        'a' => array( 0 => 'Aanbieding'),
                                        'b' => array( 0 => 'Actie'),
                                        'c' => array( 0 => 'Korting 10%'),
                                        'd' => array( 0 => 'Korting 20%'),
                                        'e' => array( 0 => 'Korting 30%'),
                                        'f' => array( 0 => 'Nieuw')
                                    ),
    ),

    'is_configurable'           => false,
    'used_in_product_listing'   => true,
    'sort_order'                => '',
));



$this->endSetup();