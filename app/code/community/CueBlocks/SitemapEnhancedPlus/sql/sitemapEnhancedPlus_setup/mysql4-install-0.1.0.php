<?php

/**
 * Description of Data
 * @package   CueBlocks_SitemapEnhancedPlus
 * ** @company   CueBlocks - http://www.cueblocks.com/
 
 */
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer = $this;

$installer->startSetup();

/**
 * Create table 'cb_sitemapenhancedplus'
 */
$installer->run("     
        -- DROP TABLE IF EXISTS {$this->getTable('sitemapEnhancedPlus/sitemapEnhancedPlus')};
        CREATE TABLE IF NOT EXISTS `{$this->getTable('sitemapEnhancedPlus/sitemapEnhancedPlus')}` (
            `sitemap_id` int(11) NOT NULL auto_increment,
            `sitemap_type` int(11) NOT NULL,
            `sitemap_tot_links`   int(10) default '0',
            `sitemap_link_counters`   text ,
            `sitemap_filename`    varchar(256) default NULL,
            `sitemap_path`        tinytext,
            `sitemap_time`        timestamp NULL default NULL,
            `store_id`            smallint(5) unsigned NOT NULL,
            INDEX par_ind (`store_id`),
            FOREIGN KEY (`store_id`) REFERENCES {$this->getTable('core_store')}(`store_id`)
            ON UPDATE CASCADE
            ON DELETE CASCADE,
            PRIMARY KEY  (`sitemap_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

        -- DROP TABLE IF EXISTS {$this->getTable('sitemapEnhancedPlus/sitemapEnhancedPlusFiles')};
        CREATE TABLE IF NOT EXISTS `{$this->getTable('sitemapEnhancedPlus/sitemapEnhancedPlusFiles')}` (
            `sitemap_file_id` int(11) NOT NULL auto_increment,
            `sitemap_id` int(11) NOT NULL,
            `sitemap_file_type` varchar(11) default NULL,
            `sitemap_file_filename` varchar(256) default NULL,
            `sitemap_file_path` tinytext,
            `use_compression` BOOL NOT NULL,
            `links_count` int(10) default '0',
            INDEX par_ind (`sitemap_id`),
            FOREIGN KEY (`sitemap_id`) REFERENCES {$this->getTable('sitemapEnhancedPlus/sitemapEnhancedPlus')}(`sitemap_id`)
            ON UPDATE CASCADE
            ON DELETE CASCADE,
            PRIMARY KEY  (`sitemap_file_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");


/*
 * NOT COMPATIBLE WITH MAGENTO < 1.6
 */
//        
//$sitemapEnhancedPlus = $installer->getConnection()
//        ->newTable($installer->getTable('sitemapEnhancedPlus/sitemapEnhancedPlus'))
//        ->addColumn('sitemap_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
//            'identity' => true,
//            'unsigned' => true,
//            'nullable' => false,
//            'primary'  => true,
//                ), 'Sitemap Id')
//        ->addColumn('sitemap_links', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
//            'nullable' => false,
//            'default'  => '0',
//                ), 'Sitemap Total Links')
//        ->addColumn('sitemap_filename', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
//                ), 'Sitemap Filename')
//        ->addColumn('sitemap_path', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
//                ), 'Sitemap Path')
//        ->addColumn('sitemap_time', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
//            'nullable' => true,
//                ), 'Sitemap Time')
//        ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
//            'unsigned' => true,
//            'nullable' => false,
//            'default'  => '0',
//                ), 'Store id')
//        ->addIndex($installer->getIdxName('sitemapEnhancedPlus/sitemapEnhancedPlus', array('store_id')), array('store_id'))
//        ->addForeignKey($installer->getFkName('sitemapEnhancedPlus/sitemapEnhancedPlus', 'store_id', 'core/store', 'store_id'), 'store_id', $installer->getTable('core/store'), 'store_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
//        ->setComment('CueBlocks SitemapEnhancedPlus');
//
//$installer->getConnection()->createTable($sitemapEnhancedPlus);
//
//$sitemapEnhancedPlusFile = $installer->getConnection()
//        ->newTable($installer->getTable('sitemapEnhancedPlus/sitemapEnhancedPlusFiles'))
//        ->addColumn('sitemap_file_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
//            'identity' => true,
//            'unsigned' => true,
//            'nullable' => false,
//            'primary'  => true,
//                ), 'Sitemap Id')
//        ->addColumn('sitemap_file_type', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
//                ), 'Sitemap File Type')
//        ->addColumn('sitemap_file_filename', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
//                ), 'Sitemap Filename')
//        ->addColumn('sitemap_file_path', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
//                ), 'Sitemap File Path')
//        ->addColumn('sitemap_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
//            'unsigned' => true,
//            'nullable' => false,
//            'default'  => '0',
//                ), 'Store id')
//        ->addIndex($installer->getIdxName('sitemapEnhancedPlus/sitemapEnhancedPlusFiles', array('sitemap_id')), array('sitemap_id'))
//        ->addForeignKey($installer->getFkName('sitemapEnhancedPlus/sitemapEnhancedPlusFiles', 'sitemap_id', 'sitemapEnhancedPlus/sitemapEnhancedPlus', 'sitemap_id'), 'sitemap_id', $installer->getTable('sitemapEnhancedPlus/sitemapEnhancedPlus'), 'sitemap_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
//        ->setComment('CueBlocks SitemapEnhancedPlus Files');
//
//$installer->getConnection()->createTable($sitemapEnhancedPlusFile);

$installer->endSetup();
