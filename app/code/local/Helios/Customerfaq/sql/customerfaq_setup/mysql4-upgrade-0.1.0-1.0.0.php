<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
ALTER TABLE  {$installer->getTable('customerfaq')} ADD  `is_from` SMALLINT NOT NULL
		
SQLTEXT;

$installer->run($sql);
//demo 
//Mage::getModel('core/url_rewrite')->setId(null);
//demo 
$installer->endSetup();
	 