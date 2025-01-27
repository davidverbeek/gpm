<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "live_gyzs_admin_management";
//$dbname = "gpm";


// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

?>