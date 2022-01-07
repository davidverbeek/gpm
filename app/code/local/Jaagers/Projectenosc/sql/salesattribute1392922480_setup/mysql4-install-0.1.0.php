<?php
$installer = $this;
$installer->startSetup();

$installer->addAttribute("order", "customer_projectnr", array("type"=>"varchar"));
$installer->endSetup();
	 