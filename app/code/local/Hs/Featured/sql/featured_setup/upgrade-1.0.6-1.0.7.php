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

$installer->addAttribute('catalog_product', 'dhlservicepunt', array(
        'group'                     => 'General',
        'input'                     => 'select',
        'type'                      => 'int',
        'label'                     => 'Disable DHL servicepoint',
        'source'                    => 'eav/entity_attribute_source_boolean',
        'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible'                   => 1,
        'required'                  => 0,
        'visible_on_front'          => 0,
        'is_html_allowed_on_front'  => 0,
        'is_configurable'           => 0,
        'searchable'                => 0,
        'filterable'                => 0,
        'comparable'                => 0,
        'unique'                    => false,
        'user_defined'              => true,
        'default'                   => 0,
        'is_user_defined'           => false,
        'used_in_product_listing'   => true
));

$attributeGroupName = 'General';

$entityTypeId = $installer->getEntityTypeId('catalog_product');

$sets = Mage::getModel('eav/entity_attribute_set')->getResourceCollection();

foreach($sets as $set) {
    //Do for all attribute sets
    $attributeSetId = $set->getData('attribute_set_id');
    $installer->addAttributeGroup($entityTypeId, $attributeSetId, $attributeGroupName, null);

    $attributeGroupId = $installer->getAttributeGroupId($entityTypeId, $attributeSetId, $attributeGroupName);
    $attributeId = $installer->getAttributeId($entityTypeId, 'dhlservicepunt');
    $installer->addAttributeToGroup($entityTypeId, $attributeSetId, $attributeGroupId, $attributeId, null);
}

$installer->endSetup();
         