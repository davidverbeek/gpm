<?php
include "../define/constants.php";
session_start();
$text = file_get_contents($document_root_path."/import_export/progress.txt");
echo $text
?>