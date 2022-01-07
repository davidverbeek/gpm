<?php
session_start();
include "../lib/SimpleXLSXGen.php";
$exported_revenue_data = $_SESSION['exported_revenue_data'];
SimpleXLSXGen::fromArray($exported_revenue_data)->downloadAs("revenue_data.xlsx");

?>