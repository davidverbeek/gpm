<?php
session_start();
unset($_SESSION["price_id"]);
unset($_SESSION["price_name"]);
header("Location:index.php");
?>