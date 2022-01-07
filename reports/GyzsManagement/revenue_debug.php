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
#revenue_debug_log tr th {
    text-align: center;
}

#revenue_debug_log tr td {
    text-align: center;
}
#revenue_debug_log_length, #revenue_debug_log_filter {
    display: none;
}
.input_validate {
    width: 70px;
}
</style>

<div>
  
  <input type="hidden" name="hdn_productsku" id="hdn_productsku" value="<?php echo $_REQUEST['psku']; ?>" />
  <input type="hidden" name="hdn_date_range" id="hdn_date_range" value="<?php echo $_REQUEST['d']; ?>" />
  <div class="alert alert-success" role="alert" style="display:none;"></div>
  <div class="alert alert-danger" role="alert" style="display:none;"></div>
        
        <div id="data-content">               
          <table id="revenue_debug_log"  class="table table-bordered table-striped table-hover" style="width: 100%; font-size: 9px;">
            <thead>
                <tr>  
                    <th>Created Date</th>
                    <th>Order ID</th>
                    <th>Quantity Ordered</th>
                    <th>Quantity Refunded</th>
                    <th>Base Cost</th>
                    <th>Base Price</th>
                    <th>Cost<br>(If Afw.Ideal.verp = 0: Base Cost * Qty * Ideal.verp)<br>(If Afw.Ideal.verp = 1: Base Cost * Qty)</th>
                    <th>Price<br>(Base Price * Qty)</th>
                    <th>Absolute Margin</th>
                    <th>Afw.Ideal.verp</th>
                    <th>Ideal.verp</th>
                </tr>
            </thead>
          </table>
      </div>   
</div>

 <!-- Custom price Js -->
   <script language="javascript">
    var document_root_url = "<?php echo $document_root_url; ?>";
    //var column_index_price_log = <?php echo json_encode($column_index_price_log) ?>;
    $(document).ready(function() {
        var sku = "<?php echo $_REQUEST['psku']; ?>";
        var date_range = "<?php echo $_REQUEST['d']; ?>";
        
        $("#rev_deb_sku").html(sku);
        $("#rev_deb_date").html(date_range);

        var table = $('#revenue_debug_log').DataTable({
            "processing": true,
            "serverSide": true,
            "pageLength": 50,
            "ordering" : true,
            "order": [[0, "desc"]],
            "columnDefs": [
                {
                  "targets": [2],
                  "render": function ( data, type, row ) {
                    
                    if(row[8] < 0) {
                        return '<input type="text" class="update_order_data qty_ordered input_validate" value="'+data+'" />';
                    } else {
                        return data;
                    }
                  },
                },
                {
                  "targets": [4],
                  "render": function ( data, type, row ) {
                    
                    if(row[8] < 0) {
                        return '<input type="text" class="update_order_data base_cost input_validate" value="'+data+'" />';
                    } else {
                        return data;
                    }
                  },
                },
                {
                  "targets": [5],
                  "render": function ( data, type, row ) {
                    
                    if(row[8] < 0) {
                        return '<input type="text" class="update_order_data base_price input_validate" value="'+data+'" />';
                    } else {
                        return data;
                    }
                  },
                },
            ],
              "ajax": {
                "url": document_root_url+"/scripts/create_revenue_debug_query.php",
                "type": "POST",
                "data": function ( d ) {
                    d.product_sku = $('#hdn_productsku').val(),
                    d.hdn_date_range = $('#hdn_date_range').val()
                }
              }
        });

        $(document).on("keyup", ".input_validate" , function(evt) {
             var self = $(this);
             self.val(self.val().replace(/[^0-9\.]/g, ''));
             if ((evt.which != 46 || self.val().indexOf('.') != -1) && (evt.which < 48 || evt.which > 57)) 
             {
               evt.preventDefault();
             }
        });

        $('#revenue_debug_log tbody').on("keyup",".update_order_data",function(e) {
            var keyCode = e.keyCode || e.which; 
            if (keyCode == 13) { 
               /*var index = $(this).closest('tr').index();
               var product_id = table.cells({ row: index, column: column_index["product_id"] }).data()[0];
               var profit_margin = parseFloat($(this).val());

               var buying_price = table.cells({ row: index, column: column_index["gyzs_buying_price"] }).data()[0];
               var webshop_supplier_gross_price = table.cells({ row: index, column: column_index["webshop_supplier_gross_price"] }).data()[0];
               var webshop_idealeverpakking = table.cells({ row: index, column: column_index["webshop_idealeverpakking"] }).data()[0];
               var webshop_afwijkenidealeverpakking = table.cells({ row: index, column: column_index["webshop_afwijkenidealeverpakking"] }).data()[0];
               var webshop_selling_price = table.cells({ row: index, column: column_index["gyzs_selling_price"] }).data()[0];

               var pmd_buying_price = table.cells({ row: index, column: column_index["buying_price"] }).data()[0];
               var supplier_gross_price = table.cells({ row: index, column: column_index["supplier_gross_price"] }).data()[0];
               var idealeverpakking = table.cells({ row: index, column: column_index["idealeverpakking"] }).data()[0];
               var afwijkenidealeverpakking = table.cells({ row: index, column: column_index["afwijkenidealeverpakking"] }).data()[0];
               var prev_profit_margin = table.cells({ row: index, column: column_index["profit_percentage"] }).data()[0];
                */

                var update_type = ($(this).attr("class")).split(" ");
                var update_val = parseFloat($(this).val());
                var sku = $("#hdn_productsku").val();
                
                var index = $(this).closest('tr').index();
                var orderid = table.cells({ row: index, column:1}).data()[0];

                //alert(update_type[1]+"==="+update_val+"===="+sku+"===="+orderid);
            
                $.ajax({
                 url: document_root_url+'/scripts/revenue_process_data.php',
                 method:"POST",
                 data: ({ type: 'update_mag_order',
                          update_type: update_type[1],
                          update_val: update_val,
                          sku: sku,
                          orderid: orderid
                        }),
                 success: function(response_data){
                    var resp_obj = jQuery.parseJSON(response_data);
                    if(resp_obj["msg"]) {
                        table.ajax.reload( null, false );
                        if(resp_obj["msg"]["error"]) {
                            $(".alert-success").hide();    
                            $(".alert-danger").show();
                            $(".alert-danger").html(resp_obj["msg"]["error"]);    
                        } else if(resp_obj["msg"]["success"]) { 
                            $(".alert-danger").hide();    
                            $(".alert-success").show();
                            $(".alert-success").html(resp_obj["msg"]["success"]);
                        }
                    }      
                 }
              });
            } 
        });



      });
  </script>
  

