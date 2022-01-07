<?php
$installer = $this;
$installer->startSetup();

$attr = Mage::getResourceModel('catalog/eav_attribute')->loadByCode('catalog_product', 'deliverytime');
if (!$attr->getId()) {
    $attribute = $installer->addAttribute('catalog_product', 'deliverytime', array(
        'type'              => 'varchar',
        'input'             => 'text',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Delivery Time',
        'source'            => 'eav/entity_attribute_source_table',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'required'          => false,
        'user_defined'      => true,
        'unique'            => false,
        'default'           => '',
    ));
}

$attributeGroupName = 'General';

$entityTypeId = $installer->getEntityTypeId('catalog_product');

$sets = Mage::getModel('eav/entity_attribute_set')->getResourceCollection();

foreach($sets as $set) {
    //Do for all attribute sets
    $attributeSetId = $set->getData('attribute_set_id');
    $installer->addAttributeGroup($entityTypeId, $attributeSetId, $attributeGroupName, null);

    $attributeGroupId = $installer->getAttributeGroupId($entityTypeId, $attributeSetId, $attributeGroupName);
    $attributeId = $installer->getAttributeId($entityTypeId, 'deliverytime');
    $installer->addAttributeToGroup($entityTypeId, $attributeSetId, $attributeGroupId, $attributeId, null);
}

$installer->endSetup();
	 