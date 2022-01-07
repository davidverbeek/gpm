<?php

$installer = $this;
$installer->startSetup();

try
{
    $installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('mavis_magmi')};
CREATE TABLE IF NOT EXISTS `{$this->getTable('mavis_magmi')}` (
 `sku_id` int(10) unsigned NOT NULL auto_increment,
 `sku` varchar(254) NOT NULL default '',
  PRIMARY KEY  (`sku_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Mavis flush products';

");

} catch(Exception $e) { Mage::logException($e); }

$installer->endSetup(); 