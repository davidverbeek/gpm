<?php
$installer = $this;
$installer->startSetup();
/** @var Magento_Db_Adapter_Pdo_Mysql $connection */
$connection = $installer->getConnection();
// add servicepoint_id to quote address table
$connection->addColumn(
	$this->getTable('sales/quote_address'),
	'qls_servicepoint_code',
	array(
		'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
		'length'    => 255,
		'nullable'  => true,
		'comment'   => 'QLS Servicepoint Code'
	)
);

// add servicepoint_id to order address table
$connection->addColumn(
	$this->getTable('sales/order_address'),
	'qls_servicepoint_code',
	array(
		'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
		'length'    => 255,
		'nullable'  => true,
		'comment'   => 'QLS Servicepoint Code'
	)
);

$installer->endSetup();
