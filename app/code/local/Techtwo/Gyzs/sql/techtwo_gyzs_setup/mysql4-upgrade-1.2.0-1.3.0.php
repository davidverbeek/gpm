<?php
$installer = $this;

/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$setup->addAttribute('catalog_product', 'featured_order', array(
	'backend'       => '',
	'source'        => '',
	'group'			=> 'Featured',
	'label'         => 'Featured order',
	'input'         => 'text',
	'class'         => 'validate-number',
	'global'        => true,
	'visible'       => true,
	'required'      => false,
	'user_defined'  => false,
	'default'       => '0',
	'visible_on_front' => false
));


$installer->endSetup();