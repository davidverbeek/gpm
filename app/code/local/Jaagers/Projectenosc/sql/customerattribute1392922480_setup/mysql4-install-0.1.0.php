<?php
$installer = $this;
$installer->startSetup();


$installer->addAttribute("customer_address", "mavis_adresid",  array(
    "type"     => "varchar",
    "backend"  => "",
    "label"    => "Mavis Adres ID",
    "input"    => "text",
    "source"   => "",
    "visible"  => true,
    "required" => false,
    "default" => "",
    "frontend" => "",
    "unique"     => false,
    "note"       => ""

	));

        $attribute   = Mage::getSingleton("eav/config")->getAttribute("customer_address", "mavis_adresid");

        
$used_in_forms=array();

$used_in_forms[]="adminhtml_customer_address";
        $attribute->setData("used_in_forms", $used_in_forms)
		->setData("is_used_for_customer_segment", true)
		->setData("is_system", 0)
		->setData("is_user_defined", 1)
		->setData("is_visible", 0)
		->setData("sort_order", 100)
		;
        $attribute->save();
	
	
	

$installer->addAttribute("customer_address", "mavis_projectadresid",  array(
    "type"     => "varchar",
    "backend"  => "",
    "label"    => "Mavis Project Adres ID",
    "input"    => "text",
    "source"   => "",
    "visible"  => true,
    "required" => false,
    "default" => "",
    "frontend" => "",
    "unique"     => false,
    "note"       => ""

	));

        $attribute   = Mage::getSingleton("eav/config")->getAttribute("customer_address", "mavis_projectadresid");

        
$used_in_forms=array();

$used_in_forms[]="adminhtml_customer_address";
        $attribute->setData("used_in_forms", $used_in_forms)
		->setData("is_used_for_customer_segment", true)
		->setData("is_system", 0)
		->setData("is_user_defined", 1)
		->setData("is_visible", 0)
		->setData("sort_order", 100)
		;
        $attribute->save();
	
	
	
$installer->endSetup();
	 