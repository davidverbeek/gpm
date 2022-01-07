<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("
    ALTER TABLE `{$this->getTable('sales_flat_quote')}` ADD `status` enum('open','pending','confirmed','converted') default 'open';
");

$installer->endSetup();
