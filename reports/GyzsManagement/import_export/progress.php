<?php
include "../define/constants.php";
session_start();
//commented on 1st july and added same code in a condition
//$text = file_get_contents($document_root_path."/import_export/progress.txt");
$text = "";
if(file_get_contents($document_root_path."/import_export/progress.txt")) {
	$text = file_get_contents($document_root_path."/import_export/progress.txt");
}
echo $text;