DROP TABLE IF EXISTS `cb_sitemapenhanced_plus_files`;
DROP TABLE IF EXISTS `cb_sitemapenhanced_plus`;
DELETE FROM `core_config_data` WHERE `path` LIKE '%sitemapEnhancedPlus%';
DELETE FROM `core_resource` WHERE CONVERT(`core_resource`.`code` USING utf8) = 'sitemapEnhancedPlus_setup' LIMIT 1;