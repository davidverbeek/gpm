<?php
/**
 * Upgrade script
 *
 * @category   Scommerce
 * @package    Scommerce_GDPR
 * @author     Scommerce Mage <core@scommerce-mage.co.uk>
 */
$installer = Mage::getResourceModel('catalog/setup', 'catalog_setup');
$installer->startSetup();
$connection = $installer->getConnection();
$setup = new Mage_Sales_Model_Mysql4_Setup('sales_setup');

$tableName = $installer->getTable('sales_flat_quote_address');
$columnName = 'processed_value';

if ($connection->tableColumnExists($tableName, $columnName) === false) {
    $installer->getConnection()->addColumn(
        $installer->getTable('sales_flat_quote_address'),
        'processed_value',
        'int NULL DEFAULT NULL'
    );
}
$setup->addAttribute('quote_address', 'processed_value', array('type' => 'int', 'visible' => false));

$installer->endSetup();