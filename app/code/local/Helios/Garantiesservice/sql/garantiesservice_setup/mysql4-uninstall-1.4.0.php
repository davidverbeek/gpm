<?php
	
$installer = $this;

$installer->startSetup();

$installer->run("
DROP TABLE `{$this->getTable('garantiesservice')}`;
DROP TABLE `{$this->getTable('garantiesservice_store')}`;

");

$installer->endSetup(); 
