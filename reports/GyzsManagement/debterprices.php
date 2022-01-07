<?php
session_start();

require_once("../../app/Mage.php");
umask(0);
Mage::app();

if(!isset($_SESSION["price_id"])) {
  header("Location:index.php");
}
include "define/constants.php";
?>
<div>
    Debter prices will be here

</div>