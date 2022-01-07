<?php

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

// Trigger
$trigger = new Magento_Db_Sql_Trigger();

// Setting Name
$trigger->setName('updateStockAfterInsert');

// Set time SQL_TIME_BEFORE / SQL_TIME_AFTER
$trigger->setTime($trigger::SQL_TIME_AFTER);

// Set time SQL_EVENT_INSERT / SQL_EVENT_UPDATE / SQL_EVENT_DELETE
$trigger->setEvent($trigger::SQL_EVENT_INSERT);

// Set target table name
$trigger->setTarget($installer->getTable('stock/stock'));

// Set Body
$trigger->setBody(
'UPDATE mage_cataloginventory_stock_item AS csi SET csi.qty = NEW.qty, csi.is_in_stock = NEW.is_in_stock WHERE csi.product_id = NEW.product_id;'
);
// IF you wants to stop back orders for out of stock products, add below trigger.
// UPDATE mage_cataloginventory_stock_status AS css SET css.stock_status = NEW.stock_status WHERE css.product_id = NEW.product_id;
// Assemble query, returns direct SQL for trigger
$triggerCreateQuery = $trigger->assemble();

// Delete Trigger if Exist
$this->getConnection()->dropTrigger($trigger->getName());

// Adapter initiates query
$this->getConnection()->query($triggerCreateQuery);

$installer->endSetup();