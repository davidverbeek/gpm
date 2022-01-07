<?php
$installer = $this;
$installer->startSetup();

$installer->addAttribute("order", "mavis_ordernr", array("type"=>"varchar"));
$installer->addAttribute("order", "pbm", array("type"=>"varchar"));
$installer->endSetup();
	 