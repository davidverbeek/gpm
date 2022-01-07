<?php

$installer = $this;

$installer->startSetup();

$installer->run("

	ALTER TABLE {$this->getTable('faq_suggest_question')} ADD `request_free_sample` smallint(6) NOT NULL default '0' AFTER `message`;

    ");

$installer->endSetup(); 