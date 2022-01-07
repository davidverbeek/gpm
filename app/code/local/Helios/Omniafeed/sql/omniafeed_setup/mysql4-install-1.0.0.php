<?php

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('potentialcount/potentialcount'))
    ->addColumn(
        'potential_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary'  => true,
        )
    )
    ->addColumn(
        'sku', Varien_Db_Ddl_Table::TYPE_VARCHAR, 55, array('unsigned' => true)
    )
    ->addColumn(
        'monthdiffcount', Varien_Db_Ddl_Table::TYPE_DECIMAL, array(12, 4), array()
    )
    ->addColumn(
        'sold_qty_last_year_partial', Varien_Db_Ddl_Table::TYPE_DECIMAL, array(12, 4), array()
    )
    ->addColumn(
        'sold_qty_last_year', Varien_Db_Ddl_Table::TYPE_DECIMAL, array(12, 4), array()
    )
    ->addColumn(
        'total_sold_qty', Varien_Db_Ddl_Table::TYPE_DECIMAL, array(12, 4), array()
    )
    ->addColumn(
        'stock_sold', Varien_Db_Ddl_Table::TYPE_DECIMAL, array(12, 4), array()
    )
    ->addColumn(
        'yearly_sales', Varien_Db_Ddl_Table::TYPE_DECIMAL, array(12, 4), array()
    )
    ->addColumn(
        'potential', Varien_Db_Ddl_Table::TYPE_VARCHAR, 15, array()
    )

    ->addIndex(
        $installer->getTable('potentialcount/potentialcount').'_sku', array('sku'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    );

if (!$installer->getConnection()->isTableExists($table->getName())) {
    $installer->getConnection()->createTable($table);
}

$installer->endSetup();