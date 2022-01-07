<?php

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('stock/stock'))
    ->addColumn(
        'stock_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary'  => true,
        )
    )
    ->addColumn(
        'product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array('unsigned' => true)
    )
    ->addColumn(
        'qty', Varien_Db_Ddl_Table::TYPE_DECIMAL, array(12, 4), array()
    )
    ->addColumn(
        'is_in_stock', Varien_Db_Ddl_Table::TYPE_SMALLINT, 5, array('unsigned' => true)
    )
    ->addColumn(
        'stock_status', Varien_Db_Ddl_Table::TYPE_SMALLINT, 5, array('unsigned' => true)
    )
    ->addIndex(
        $installer->getTable('stock/stock').'_product_id', array('product_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    );

if (!$installer->getConnection()->isTableExists($table->getName())) {
    $installer->getConnection()->createTable($table);
}

$installer->endSetup();