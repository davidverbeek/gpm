<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("
    ALTER TABLE `{$this->getTable('sales_flat_quote')}` ADD `is_cart_auto` tinyint(1) default 1;
");

$installer->endSetup();
