<?php
include "define/constants.php";
?>

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Price Management</title>

    <?php if(strpos($_SERVER['REQUEST_URI'], 'settings') > 0) { ?>
      <script src="<?php echo $document_root_url; ?>/js/jquery-3.5.1.js"></script>
      <script src="<?php echo $document_root_url; ?>/js/index.js"></script>
      <link href="<?php echo $document_root_url; ?>/css/all.css" rel="stylesheet" type="text/css">
      <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.0/css/all.css" integrity="sha384-lKuwvrZot6UHsBSfcMvOkWwlCMgc0TaWr+30HWe3a4ltaBwTZhyTEggF5tJv8tbt" crossorigin="anonymous">
      <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
      <link href="<?php echo $document_root_url; ?>/css/settings.css" rel="stylesheet" type="text/css">
      <link href="<?php echo $document_root_url; ?>/css/sb-admin.min.css" rel="stylesheet">
    <?php } else {  ?>
    <script src="<?php echo $document_root_url; ?>/js/jquery-3.3.1.slim.min.js"></script>
    <script src="<?php echo $document_root_url; ?>/js/simTree.js"></script>
    <script src="<?php echo $document_root_url; ?>/js/jquery-3.5.1.js"></script>
    <script src="<?php echo $document_root_url; ?>/js/datatables.min.js"></script>
    <script src="<?php echo $document_root_url; ?>/js/dataTables.bootstrap4.min.js"></script>
    <script src="<?php echo $document_root_url; ?>/js/index.js"></script>
    <link rel="stylesheet" href="css/dataTables.bootstrap4.min.css" type="text/css">
    <link href="<?php echo $document_root_url; ?>/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="<?php echo $document_root_url; ?>/css/simTree.css" rel="stylesheet" type="text/css">
    <link href="<?php echo $document_root_url; ?>/css/all.css" rel="stylesheet" type="text/css">
    <link href="<?php echo $document_root_url; ?>/css/style.css" rel="stylesheet" type="text/css">
    <link href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="<?php echo $document_root_url; ?>/js/jquery-ui.js"></script>
    <script src="<?php echo $document_root_url; ?>/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.0/css/all.css" integrity="sha384-lKuwvrZot6UHsBSfcMvOkWwlCMgc0TaWr+30HWe3a4ltaBwTZhyTEggF5tJv8tbt" crossorigin="anonymous">
    <?php } ?>
    
</head>

<style>
  .selling_price, .profit_margin, .discount_on_gross, .profit_margin_sp, .db_sp, .db_m_bp, .db_m_sp, .db_d_gp {
    border: none;
    background-color: none;
    background:  transparent;
    outline: 0;
    width: 80px;
  }
  .selling_price:focus, .profit_margin:focus, .discount_on_gross:focus, .profit_margin_sp:focus, .db_sp:focus, .db_m_bp:focus, .db_m_sp:focus, .db_d_gp:focus {
      border: none;
      background-color: none;
      outline: 0;
  }

  
  .table td  {
    padding: 0.09rem;
    color: #000000;
  }
  .form-control {
    font-size: 10px;
  }

  .editable_column {
   background-color: #ffffcc !important;
   color: #212529 !important;
  }

  .db_sp_editable_column {
  /* background-color: #90EE90 !important; */ 
  }
  .db_m_bp_editable_column {
   background-color: #7AC3FF !important;   
  }
  .db_m_sp_editable_column {
   background-color: #FFFD6E !important; 
  }
  .db_d_gp_editable_column {
   background-color: #FF6B6B !important;   
  }


  .p_s_p_pos {
    width: 5%;
    border-top: 1px solid #ced4da;
    border-bottom: 1px solid #ced4da;
    padding-left: 2%;
    padding-top: 1%;
    cursor: pointer;
    background: #e6ffe7;
  }
  .p_s_p_neg {
    width: 5%;
    border-top: 1px solid #ced4da;
    border-bottom: 1px solid #ced4da;
    padding-left: 2%;
    padding-top: 1%;
    cursor: pointer;
    background: #ffc2c2;
  }
  .btn-group-xs > .btn, .btn-xs {
  padding: .15rem .2rem;
  font-size: 10px;
  line-height: .2;
  border-radius: .2rem;
}
.clshistorytitle {
  font-size: 10px;
}
.badge-notify{
   background:#dc3545;
   position:relative;
   top: -7px;
   left: -4px;
   color:#ffffff;
  }
  .row_updated {
    background-color: #e9f6ec !important;
  }
  

  #loading-img, #loading-img-ec, #loading-img-ec-be {
    background: url(images/loading.gif) center center no-repeat;
    width: 64px;
    height: 64px;
    left: 197px;
    top: 2px;
  }

  #loading-img-export, .loading-img-update {
    background: url(images/loader.gif) center center no-repeat;
    width: 16px;
    height: 8px;
    margin-left: 5px;
  }

  
  table.dataTable tbody tr.selected {
    background-color: #D1EAFF !important;
    border: 2px solid #6FA3D9 !important;
  }
  .bar {
    height: 20px;
  }
  #import_errors {
    height: 100px;
    overflow-y: scroll;
    font-size: 11px;
    margin-top: 20px;
  }
  #msg_progress_complete {
    text-align: center;
  }
  #import_summary {
    color: #1cc88a;
    margin-bottom: 10px;
    font-size: 11px;
  }

.modal.fade .modal-dialog.modal-dialog-zoom {-webkit-transform: translate(0,0)scale(.5);transform: translate(0,0)scale(.5);}
.modal.show .modal-dialog.modal-dialog-zoom {-webkit-transform: translate(0,0)scale(1);transform: translate(0,0)scale(1);}

.new_log {
  background-color: #f7d0d4 !important;
}

.cell_changed {
  background-color: #c2c3e0 !important;
  color: #3a3d99 !important;  
}
.alert-success, {
  font-size: 12px;
}
.update_loader {
  font-size: 11px;
  margin-left: 10px;
  display: none;
}
.history_link {
  color: #323584;
  font-style: italic;
  cursor: pointer;
}
thead .header_first_tr th {
  background-color: #c2c3e0a9;
  color: black;
  font-size: 11px;
}
#ui-datepicker-div {
  font-size: 11.5px;
}
.alert, .prev_data {
  font-size: 10px;
}
.search_brand {
  margin-bottom: 11px;
}

</style>

<?php
$top_image = "";
if(strpos($_SERVER['REQUEST_URI'], 'feedroas') > 0) {
  $header_txt = "Roas Feed";
  $page_type = "feedroas";
  $top_image =  '<img alt="GYZS" src="'.$document_root_url.'/css/svg/feed_purple.svg">';
} else if(strpos($_SERVER['REQUEST_URI'], 'setprice') > 0){
  $header_txt = "Manage Prices";
  $page_type = "setprice";
  $top_image = '<i class="fas fa-euro-sign" style="font-size:22px; color:#3a3d99; font-weight:bold;"></i>';
} else if(strpos($_SERVER['REQUEST_URI'], 'currentroas') > 0){
  $header_txt = "Roas Current";
  $page_type = "currentroas";
  $top_image =  '<img alt="GYZS" src="'.$document_root_url.'/css/svg/feed_purple.svg">';
} else if(strpos($_SERVER['REQUEST_URI'], 'settings') > 0){
  $header_txt = "Settings";
  $page_type = "settings";
  $top_image =  '<img alt="GYZS" src="'.$document_root_url.'/css/svg/settings-purple.svg">';
} else if(strpos($_SERVER['REQUEST_URI'], 'googleroas') > 0){
  $header_txt = "Google Roas";
  $page_type = "googleroas";
} else if(strpos($_SERVER['REQUEST_URI'], 'revenue_report') > 0){
  $header_txt = "Revenue Report";
  $page_type = "revenue_report";
} else if(strpos($_SERVER['REQUEST_URI'], 'bol_commission') > 0){
  $header_txt = "Bol Commissions";
  $page_type = "Bol Commissions";
  $top_image = '<i class="fas fa-hands-helping" style="font-size:22px; color:#3a3d99; font-weight:bold; margin-right:10px;"></i>';
} else if(strpos($_SERVER['REQUEST_URI'], 'bol_minimum') > 0){
  $header_txt = "Bol Minimum Prices";
  $page_type = "bol_minimum";
  $top_image = '<i class="fas fa-euro-sign" style="font-size:22px; color:#3a3d99; font-weight:bold;"></i>';
} else if(strpos($_SERVER['REQUEST_URI'], 'importedlog') > 0){
  $header_txt = "Import Logs";
  $page_type = "importedlog";
  $top_image = '<i class="fas fa-history" style="font-size:22px; color:#3a3d99; font-weight:bold;"></i>';
} else if(strpos($_SERVER['REQUEST_URI'], 'exportedlog') > 0){
  $header_txt = "Exported Logs";
  $page_type = "exportedlog";
  $top_image = '<i class="fas fa-history" style="font-size:22px; color:#3a3d99; font-weight:bold;"></i>';
} else if(strpos($_SERVER['REQUEST_URI'], 'backup_restore') > 0){
  $header_txt = "Price Management Backups";
  $page_type = "backup_restore";
  $top_image = '<i class="fas fa-history" style="font-size:22px; color:#3a3d99; font-weight:bold;"></i>';
} else if(strpos($_SERVER['REQUEST_URI'], 'webhop_prices') > 0){
  $header_txt = "Webshop Prices";
  $page_type = "webhop_prices";
  $top_image = '<i class="fas fa-euro-sign" style="font-size:22px; color:#3a3d99; font-weight:bold;"></i>';
} else if(strpos($_SERVER['REQUEST_URI'], 'debter_rules') > 0) {
  $header_txt = "Debter Rules";
  $page_type  = "debter_rules";
  $top_image  = '<i style="font-size:22px; color:#3a3d99; font-weight:bold;"></i>';
}

?>
