<?php
include "define/constants.php";

$imported_file_path = $document_root_path."/pm_logs/Imported_from_supplier";
$exported_file_path = $document_root_path."/pm_logs/Exported_to_webshop";
$mysql_file_path = $document_root_path."/pm_logs/pm_dumps";

$type = $_REQUEST['type'];
$response_data = array();
switch($type) {
	case "delete_imported_log":
		$file_name_to_delete = $_REQUEST['file_name'];
		if(unlink($imported_file_path."/".$file_name_to_delete)) {
			$response_data['msg'] = "Success";	
		}
	break;

	case "delete_exported_log":
		$file_name_to_delete = $_REQUEST['file_name'];
		if(unlink($exported_file_path."/".$file_name_to_delete)) {
			$response_data['msg'] = "Success";	
		}
	break;

	case "delete_mysql_log":
		$file_name_to_delete = $_REQUEST['file_name'];
		if(unlink($mysql_file_path."/".$file_name_to_delete)) {
			$response_data['msg'] = "Success";	
		}
	break;
}

echo json_encode($response_data); 



?>