<?php
session_start();
include "../lib/SimpleXLSXGen.php";
$exported_data_webshop_prices = $_SESSION['exported_data_webshop_prices'];
SimpleXLSXGen::fromArray($exported_data_webshop_prices)->downloadAs("webshop_data.xlsx");

?>