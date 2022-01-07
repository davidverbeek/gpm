<?php
$installer = $this;
$installer->startSetup();
$installer->run("	
	-- DROP TABLE IF EXISTS {$this->getTable('faq_store')};
CREATE TABLE {$this->getTable('faq_store')} (
  `auto_id` int(11) unsigned NOT NULL auto_increment,
	`faq_id` int(11) unsigned NOT NULL,
  `store_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`auto_id`),
	FOREIGN KEY (`faq_id`) REFERENCES {$this->getTable('faq')} (`faq_id`) ON DELETE CASCADE ON UPDATE CASCADE
	
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	
	-- DROP TABLE IF EXISTS {$this->getTable('faq_category_store')};
CREATE TABLE {$this->getTable('faq_category_store')} (
  `auto_id` int(11) unsigned NOT NULL auto_increment,
	`category_id` int(11) unsigned NOT NULL,
  `store_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`auto_id`),
	FOREIGN KEY (`category_id`) REFERENCES {$this->getTable('faq_category')} (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE
	
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE {$this->getTable('faq')} ADD `url_key` varchar(255) NOT NULL default '';
ALTER TABLE {$this->getTable('faq')} ADD `products` text NOT NULL default '';
ALTER TABLE {$this->getTable('faq_category')} ADD `url_key` varchar(255) NOT NULL default '';

");
$installer->endSetup(); 