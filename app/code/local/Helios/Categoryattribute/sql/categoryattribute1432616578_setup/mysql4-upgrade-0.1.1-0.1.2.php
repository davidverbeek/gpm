<?php

$installer = $this;//Mage::getResourceModel('catalog/setup', 'catalog_setup');
$installer->startSetup();

$installer->getConnection()->addColumn(
    $this->getTable('catalog_product_entity_media_gallery_value'),
    'mavis_image',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => true,
        'default'  => null,
        'comment'  => '0: No , 1: Yes',
        'after'    => 'disabled'
    )
);

$installer->endSetup();