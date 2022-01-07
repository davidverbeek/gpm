<?php 
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    require_once("app/Mage.php");
    umask(0);
    Mage::app();
?>
<?php
    //Mage::getModel('orderfeed/cron')->monthorderFeeds();
    //Mage::getModel('orderfeed/cron')->orderFeeds();
//Mage::getModel('deliverytime/cron')->getStock();
//Mage::getModel('deliverytime/cron')->getUpdatedRestStocks();
Mage::getModel('deliverytime/cron')->getRestDeliverytime();
?>