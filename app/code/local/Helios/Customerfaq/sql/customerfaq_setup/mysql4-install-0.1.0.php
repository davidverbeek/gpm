<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
create table {$installer->getTable('customerfaq')}(customerfaq_id int not null auto_increment, customer_id int not null, name varchar(100),question text,answer text,is_move int,primary key (customerfaq_id));
 
SQLTEXT;

$installer->run($sql);
//demo 
//Mage::getModel('core/url_rewrite')->setId(null);
//demo 
$installer->endSetup();
	 