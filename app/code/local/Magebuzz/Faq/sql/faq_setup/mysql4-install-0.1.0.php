<?php

$installer = $this;

$installer->startSetup();

$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('faq')};
CREATE TABLE {$this->getTable('faq')} (
  `faq_id` int(11) unsigned NOT NULL auto_increment,
  `question` text NOT NULL default '',
  `answer` text NOT NULL default '',
  `is_active` smallint(6) NOT NULL default '1',
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`faq_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- DROP TABLE IF EXISTS {$this->getTable('faq_category')};
CREATE TABLE {$this->getTable('faq_category')} (
  `category_id` int(11) unsigned NOT NULL auto_increment,
  `category_name` VARCHAR(255) NOT NULL,
  `is_active` smallint(6) NOT NULL default '1',
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS {$this->getTable('faq_category_item')};
CREATE TABLE {$this->getTable('faq_category_item')} (
  `category_id` INT(11) UNSIGNED NOT NULL,
  `faq_id` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`category_id`,`faq_id`),
  CONSTRAINT `FK_FAQ_CATEGORY_ITEM_FAQ_CATEGORY` FOREIGN KEY (`category_id`) REFERENCES `{$this->getTable('faq_category')}` (`category_id`) ON DELETE CASCADE,
  CONSTRAINT `FK_FAQ_CATEGORY_ITEM_FAQ` FOREIGN KEY (`faq_id`) REFERENCES `{$this->getTable('faq')}` (`faq_id`) ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='FAQ Items to Cateories';
    ");


$installer->endSetup(); 
