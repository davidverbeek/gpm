<?php
session_start();
include "../lib/SimpleXLSXGen.php";
$exported_data = $_SESSION['exported_data'];
SimpleXLSXGen::fromArray($exported_data)->downloadAs("price_management_data.xlsx");

?>