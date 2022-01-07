<?php

$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

$varcharTable = $installer->getTable('catalog_category_entity_varchar');
$textTable    = $installer->getTable('catalog_category_entity_text');

// copy all fields but the value_id as this would lead to integrity constraint violations
$fieldsToCopy             = array_keys($installer->getConnection()->describeTable($varcharTable));
$fieldsToCopy             = array_combine($fieldsToCopy, $fieldsToCopy);
$fieldsToCopy['value_id'] = new Zend_Db_Expr ('NULL');

$attributesToMoveFromVarcharToInt = array('custom_filters');

foreach ($attributesToMoveFromVarcharToInt as $attributeCode) {
	$setup->updateAttribute(Mage_Catalog_Model_Category::ENTITY, $attributeCode, 'backend_type', Varien_Db_Ddl_Table::TYPE_TEXT);

	$attributeId = $setup->getAttributeId(Mage_Catalog_Model_Category::ENTITY, $attributeCode);

	// copy data from varchar to text
	$select      = $installer->getConnection()->select()->from($varcharTable, $fieldsToCopy)->where('attribute_id = ?', $attributeId);
	$query       = $installer->getConnection()->insertFromSelect($select, $textTable);
	$installer->run($query);

	// Delete value from Varchar 
	$query = $installer->getConnection()->deleteFromSelect($select, $varcharTable);
	$installer->run($query);
}

$installer->endSetup();
