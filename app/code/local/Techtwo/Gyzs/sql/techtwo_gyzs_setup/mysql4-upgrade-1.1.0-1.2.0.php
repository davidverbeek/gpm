<?php

/* @var $installer Mage_Sales_Model_Entity_Setup */
$installer = $this;

$installer->getConnection()->addColumn($installer->getTable('sales_flat_order'), 'mavis_order_id', 'INT UNSIGNED NULL');