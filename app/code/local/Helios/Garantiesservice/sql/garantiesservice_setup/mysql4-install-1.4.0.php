<?php

$installer = $this;

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS {$installer->getTable('garantiesservice')};
CREATE TABLE {$installer->getTable('garantiesservice')} (                                  
		   `garantiesservice_id` int(11) unsigned NOT NULL auto_increment,  
		   `title` varchar(255) NOT NULL default '',               
		   `imageicon` varchar(255) NOT NULL default '',         
		   `filethumbgrid` text,
		   `shortcontent` text NOT NULL,                                                                   
		   `longcontent` text NOT NULL,                                
		   `status` smallint(6) NOT NULL default '0',              
		   `created_time` datetime default NULL,                   
		   `update_time` datetime default NULL,                    
		   PRIMARY KEY  (`garantiesservice_id`)                             
		 ) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$installer->getTable('garantiesservice_store')};
CREATE TABLE {$installer->getTable('garantiesservice_store')} (                                 
		 `garantiesservice_id` int(11) NOT NULL,                               
		 `store_id` smallint(5) unsigned NOT NULL,                    
		 PRIMARY KEY  (`garantiesservice_id`,`store_id`),                      
		 KEY `FK_BANNERS_STORE_STORE` (`store_id`)                    
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Garantiesservice Stores';
");
$installer->endSetup();
?>