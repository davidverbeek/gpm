<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Cms
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/**
 * Create table 'cms/block'
 */
/*$table = $installer->getConnection()
    ->newTable($installer->getTable('featured/featuredlabel'))
    ->addColumn('label_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Block ID')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false,
        ), 'Label Name')
    ->addColumn('option_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 255, array(
        'nullable'  => false,
        ), 'Label Option Id')
    ->addColumn('image', Varien_Db_Ddl_Table::TYPE_TEXT, '2M', array(
        ), 'Image')
    ->addColumn('image_hover', Varien_Db_Ddl_Table::TYPE_TEXT, '2M', array(
        ), 'Image Hover')
    ->addColumn('bgcolor', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'BG color')
    ->addColumn('bghovercolor', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'BG hover color')
    ->addColumn('textcolor', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Text Color')
    ->addColumn('texthovercolor', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Text hover color')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Creation Time')
    ->addColumn('update_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Modification Time')
    ->addColumn('is_active', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => '1',
        ), 'Is Label Active')
    ->setComment('Featured Label Table');
$installer->getConnection()->createTable($table);

*/
$installer->endSetup();
