<?php

$installer = $this;
$connection = $installer->getConnection();
$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS `{$this->getTable('garantiesservice_store')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('garantiesservice_store')}` (
  `garantiesservice_id` int(11) NOT NULL,
  `store_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY  (`garantiesservice_id`,`store_id`),
  KEY `FK_BANNERS_STORE_STORE` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Usol Garantiesservice Stores';
");

$connection->addConstraint('FK_BANNERS_STORE_STORE',
    $installer->getTable('garantiesservice_store'), 'store_id',
    $installer->getTable('core_store'), 'store_id',
    'CASCADE', 'CASCADE', true
);

$connection->addConstraint('garantiesservice_store_ibfk_1',
    $installer->getTable('garantiesservice_store'), 'garantiesservice_id',
    $installer->getTable('garantiesservice'), 'garantiesservice_id',
    'CASCADE', 'CASCADE', true
);
$installer->endSetup(); 

?>


