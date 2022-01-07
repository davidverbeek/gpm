<?php
$installer = $this;
$installer->startSetup();
$installer->run("	
	-- DROP TABLE IF EXISTS {$this->getTable('faq_in_categories')};
CREATE TABLE {$this->getTable('faq_in_categories')} (
  `item_id` int(11) unsigned NOT NULL auto_increment,
	`faq_id` int(11) unsigned NOT NULL,
	`category_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`item_id`),
	FOREIGN KEY (`faq_id`) REFERENCES `{$this->getTable('faq')}` (`faq_id`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`category_id`) REFERENCES {$this->getTable('catalog_category_entity')} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
	
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	-- DROP TABLE IF EXISTS {$this->getTable('faq_suggest_question')};
CREATE TABLE {$this->getTable('faq_suggest_question')} (
  `id` int(11) unsigned NOT NULL auto_increment,
	`name` varchar(255) NOT NULL default '',
	`email` varchar(255) NOT NULL default '',
	`phone` varchar(255) NOT NULL default '',
	`message` text NOT NULL default '',
	`status` smallint(6) NOT NULL default '0',
  PRIMARY KEY (`id`)
	
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");
$installer->endSetup(); 