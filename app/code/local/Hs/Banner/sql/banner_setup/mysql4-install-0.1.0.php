<?php

$installer = $this;
$installer->startSetup();

$table = $installer->getConnection()
	->newTable($installer->getTable('hs_banner'))
	->addColumn('banner_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'identity' => true,
		'unsigned' => true,
		'nullable' => false,
		'primary' => true,
	), 'Banner Id')
	->addColumn('image', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
		'unsigned' => true,
		'nullable' => false
	), 'Banner Image')
	->addColumn('link', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
		'unsigned' => true,
		'nullable' => true
	), 'Banner Link')
	->addColumn('status', Varien_Db_Ddl_Table::TYPE_INTEGER, 3, array(
		'unsigned' => true,
		'nullable' => false,
		'default' => '0',
	), '0=Inactive,1=Active');
$installer->getConnection()->createTable($table);

/**
 * Create table notify_settings
 */
$table = $installer->getConnection()
	->newTable($installer->getTable('hs_banner_category'))
	->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'identity' => true,
		'unsigned' => true,
		'nullable' => false,
		'primary' => true,
	), 'Id')
	->addColumn('banner_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned' => true,
		'nullable' => false
	), 'Banner Id')
	->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned' => true,
		'nullable' => false
	), 'Category Id');
$installer->getConnection()->createTable($table);
$installer->endSetup();