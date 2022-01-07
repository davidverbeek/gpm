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
 * @package     Mage_Newsletter
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Newsletter install
 *
 * @category   Mage
 * @package    Mage_Newsletter
 * @author     Magento Core Team <core@magentocommerce.com>
 */
$installer = $this;
$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$entityTypeId     = $setup->getEntityTypeId('customer');
$attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $setup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

$installer->addAttribute("customer_address", "company_type",  array(
    "type"     => "varchar",
    "backend"  => "",
    "label"    => "Company Type",
    "input"    => "text",
    "source"   => "",
    "visible"  => true,
    "required" => false,
    "default" => "",
    "frontend" => "",
    "unique"     => false,
    "note"       => "Company Type"

        ));

 $attribute   = Mage::getModel("eav/config")->getAttribute("customer_address", "company_type");


$setup->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'company_type',
    '999'  //sort_order
);

$used_in_forms=array();

//$used_in_forms[]="adminhtml_customer";
$used_in_forms[]="checkout_register";
//$used_in_forms[]="customer_account_create";
$used_in_forms[]="customer_address_edit";
$used_in_forms[]="adminhtml_checkout";
        $attribute->setData("used_in_forms", $used_in_forms)
                ->setData("is_used_for_customer_segment", true)
                ->setData("is_system", 0)
                ->setData("is_user_defined", 1)
                ->setData("is_visible", 1)
                ->setData("sort_order", 100)
                ;
        $attribute->save();
        
/* btw number */        
        $installer->addAttribute("customer_address", "btw_number",  array(
    "type"     => "varchar",
    "backend"  => "",
    "label"    => "BTW Number",
    "input"    => "text",
    "source"   => "",
    "visible"  => true,
    "required" => false,
    "default" => "",
    "frontend" => "",
    "unique"     => false,
    "note"       => "BTW Number"

        ));

 $attribute   = Mage::getModel("eav/config")->getAttribute("customer_address", "btw_number");


$setup->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'btw_number',
    '999'  //sort_order
);

$used_in_forms=array();

//$used_in_forms[]="adminhtml_customer";
$used_in_forms[]="checkout_register";
//$used_in_forms[]="customer_account_create";
$used_in_forms[]="customer_address_edit";
$used_in_forms[]="adminhtml_checkout";
        $attribute->setData("used_in_forms", $used_in_forms)
                ->setData("is_used_for_customer_segment", true)
                ->setData("is_system", 0)
                ->setData("is_user_defined", 1)
                ->setData("is_visible", 1)
                ->setData("sort_order", 100)
                ;
        $attribute->save();
 
/* Referentie */        
$installer->addAttribute("customer_address", "referentie",  array(
    "type"     => "varchar",
    "backend"  => "",
    "label"    => "Referentie",
    "input"    => "text",
    "source"   => "",
    "visible"  => true,
    "required" => false,
    "default" => "",
    "frontend" => "",
    "unique"     => false,
    "note"       => "Referentie"

        ));

 $attribute   = Mage::getModel("eav/config")->getAttribute("customer_address", "referentie");


$setup->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'referentie',
    '999'  //sort_order
);

$used_in_forms=array();

//$used_in_forms[]="adminhtml_customer";
$used_in_forms[]="checkout_register";
//$used_in_forms[]="customer_account_create";
$used_in_forms[]="customer_address_edit";
$used_in_forms[]="adminhtml_checkout";
        $attribute->setData("used_in_forms", $used_in_forms)
                ->setData("is_used_for_customer_segment", true)
                ->setData("is_system", 0)
                ->setData("is_user_defined", 1)
                ->setData("is_visible", 1)
                ->setData("sort_order", 100)
                ;
        $attribute->save();
         
        
 /* mobile no */       

$installer->addAttribute("customer_address", "mobile_no",  array(
    "type"     => "varchar",
    "backend"  => "",
    "label"    => "Mobile no",
    "input"    => "text",
    "source"   => "",
    "visible"  => true,
    "required" => false,
    "default" => "",
    "frontend" => "",
    "unique"     => false,
    "note"       => "Mobile No"

        ));

 $attribute   = Mage::getModel("eav/config")->getAttribute("customer_address", "mobile_no");


$setup->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'mobile_no',
    '999'  //sort_order
);

$used_in_forms=array();

//$used_in_forms[]="adminhtml_customer";
$used_in_forms[]="checkout_register";
//$used_in_forms[]="customer_account_create";
$used_in_forms[]="customer_address_edit";
$used_in_forms[]="adminhtml_checkout";
        $attribute->setData("used_in_forms", $used_in_forms)
                ->setData("is_used_for_customer_segment", true)
                ->setData("is_system", 0)
                ->setData("is_user_defined", 1)
                ->setData("is_visible", 1)
                ->setData("sort_order", 100)
                ;
        $attribute->save();
        
$installer->run("ALTER TABLE `mage_sales_flat_quote` ADD `delivery_type` INT( 10 ) NOT NULL ");

$this->endSetup();
$installer->endSetup();
