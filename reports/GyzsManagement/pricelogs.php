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
<style>
.table {
    font-size: 10px;
}
#pricelogsdt tr th {
    text-align: center;
}

#pricelogsdt tr td {
    text-align: center;
}
#pricelogsdt_length, #pricelogsdt_filter {
    display: none;
}
</style>

<div>
  
  <input type="hidden" name="hdn_productid" id="hdn_productid" value="<?php echo $_REQUEST['pid']; ?>" />
  
   
        
          <div id="data-content">          
                 
          <table id="pricelogsdt"  class="table table-bordered table-striped table-hover" style="width: 100%; font-size: 9px;">
            <thead>
                <tr class="header_first_tr">
                    <th></th>
                    <th colspan="6">Webshop</th>
                    <th colspan="6">Price Management</th>
                    <th></th>
                </tr>
                <tr>  
                    <th>Updated Date</th>
                    <th>Net Unit Price</th>
                    <th>Gross Unit Price</th>
                    <th>Idealever pakking</th>
                    <th>Afwijkenidealeverpakking</th>
                    <th>Buying Price</th>
                    <th>Selling Price</th>
                    <th>Net Unit Price</th>
                    <th>Gross Unit Price</th>
                    <th>Idealever pakking</th>
                    <th>Afwijkenidealever pakking</th>
                    <th>Buying Price</th>
                    <th>Selling Price</th>
                    <th>Updated By</th>
                    <th>Is Viewed</th>
                    <th>History Id</th>
                    <th>Product Id</th>
                    <th>Files Changed</th>
                </tr>
            </thead>
          </table>
      </div>

      
  
   
</div>

 <!-- Custom price Js -->
   <script language="javascript">
    var document_root_url = "<?php echo $document_root_url; ?>";
    var column_index_price_log = <?php echo json_encode($column_index_price_log) ?>;
    $(document).ready(function() {
        var sku = "<?php echo $_REQUEST['s']; ?>";
        var ean = "<?php echo $_REQUEST['e']; ?>";
        var product_id = "<?php echo $_REQUEST['pid']; ?>";

        $("#his_sku").html(sku);
        $("#his_ean").html(ean);

        var table = $('#pricelogsdt').DataTable({
            "processing": true,
            "serverSide": true,
            "pageLength": 50,
            "ordering" : true,
            "order": [[column_index_price_log["updated_date_time"], "desc"]],
            "columnDefs": [
              {
                "targets": [column_index_price_log["is_viewed"], column_index_price_log["history_id"], column_index_price_log["product_id"], column_index_price_log["fields_changed"] ],
                "visible": false
              },
              {
                'targets': [column_index_price_log["updated_date_time"]],
                'type': 'date' 
              }
            ],
            "rowCallback": function( row, data ) {
              if(data[column_index_price_log["is_viewed"]] == "No") {
                  $(row).addClass("new_log");
                }
                if(data[column_index_price_log["fields_changed"]]) {
                   var fc = $.parseJSON(data[column_index_price_log["fields_changed"]]);
                  $.each(fc, function (key, val) {
                    //$('td', row).eq(11).addClass('cell_changed');
                    $('td', row).eq(column_index_price_log[val]).addClass('cell_changed');
                  }); 
                }
              },
              "ajax": {
                "url": document_root_url+"/scripts/create_query_logs.php",
                "type": "POST",
                "data": function ( d ) {
                    d.product_id = $('#hdn_productid').val()
                }
              }
        });

        $.ajax({
           url: document_root_url+'/scripts/process_data_price_management.php',
           data: ({ product_id: product_id, type: 'get_history', change_status: 1})
        });

        

      });
  </script>
  

