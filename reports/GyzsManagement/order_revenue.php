
<?php
session_start();

require_once("../../app/Mage.php");
umask(0);
Mage::app();

if(!isset($_SESSION["price_id"])) {
  header("Location:index.php");
}
?>

<!DOCTYPE html>
<html lang="en">


<?php
include "define/constants.php";
include "layout/header.php";
?>

<body>
    <main>
        <!-- Sidebar -->
          <?php include "layout/left.php"; ?>
        <!-- End of Sidebar -->

       
       
        <!-- Datatable and header  -->
        <section class="content-toggle" id="main-content">
            <div class="content-bg-blur h-100">
                

            <!-- Topbar -->
              <?php include "layout/top.php"; ?>
            <!-- End of Topbar --> 

                
                <div class="table-filter d-flex align-items-center" id="data_filters">

                    <div class="col-3">
                    <div class="md-form">
                        <label for="from">Van (yyyy/mm/dd)</label>
                        <input type="text" class="form-control form-control-sm" id="from" placeholder="Van" required>
                        
                    </div>
                    </div>
                    <div class="col-3">
                        <div class="md-form">
                          <label for="to">Naar (yyyy/mm/dd)</label>
                          <input type="text" class="form-control form-control-sm" id="to" placeholder="Naar" required>
                            
                        </div>
                    </div>
                    <div class="col-3">
                        </br>
                        <button class="btn btn-primary" id="btnsubmit" type="submit">Bestelgegevens ophalen</button>
                    </div>
                </div>

                <div class="data-toggle overflow-hidden position-fixed" id="data-content">
                    <div class="datatable w-100 h-100 overflow-auto">
                        <!-- content Table -->
                        <table id="product" class="table position-relative custom-override-table">
                            <thead style="z-index: 9999999;">
                                <tr>
                                  <th id="test">Artinr</th>
                                  <th>Naam</th>
                                  <th>Aantal</th>
                                  <th>Inkpr Ex</th>
                                  <th>Verkp Ex</th>
                                  <th>Omzet Ex</th>
                                  <th>Inkwrde Ex</th>
                                  <th>Marge Totaal €</th>
                                  <th>Marge %</th>
                                  <th>Aantal Bol</th>
                                  <th>Omzet Bol Ex</th>
                                </tr>
                            </thead>
                            <tfoot style="display: none;">
                                <tr>
                                  <th>Artinr</th>
                                  <th>Naam</th>
                                  <th>Aantal</th>
                                  <th>Inkpr Ex</th>
                                  <th>Verkp Ex</th>
                                  <th>Omzet Ex</th>
                                  <th>Inkwrde Ex</th>
                                  <th>Marge Totaal €</th>
                                  <th>Marge %</th>
                                  <th>Aantal Bol</th>
                                  <th>Omzet Bol Ex</th>
                                </tr>
                                <tr class="hr-line">
                                    <td colspan="200" class="position-absolute hr-rule-search"></td>
                                </tr>
                            </tfoot>
                            
                        </table>
                    </div>
                </div>

            </div>
        </section>
        <!-- Filter Datatable settings -->   
    </main>

    <!-- Hiddens -->
        <div>
          <input type="hidden" name="hdn_selectedcategories" id="hdn_selectedcategories" />
          <input type="hidden" name="hdn_selectedbrand" id="hdn_selectedbrand" />
          
          <input type="hidden" name="hdn_processfilename_n" id="hdn_processfilename_n" value="" />
          <input type="hidden" name="hdn_filters" id="hdn_filters" />
          
          <input type="hidden" name="hdn_percentage_increase_column" id="hdn_percentage_increase_column" value="17" />
          <input type="hidden" name="hdn_log_column" id="hdn_log_column" value="18" />
        </div>
        <!-- End of Hiddens -->

<!-- Modals -->
    <?php include "layout/modals.php"; ?>
  <!-- End of Modals -->
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/js/bootstrap-datepicker.min.js"></script>

<script language="javascript">

  $(document).ready(function() {
    

    var to = $('#to');
    var from = $('#from');
    var container = "body";
    var options = {
        format: 'yyyy/mm/dd',
        container: container,
        todayHighlight: true,
        autoclose: true,
        orientation: 'top',
    };
    to.datepicker(options);
    from.datepicker(options);

    $("#btnsubmit").button().click(function(){
        var dateFrom = $("#from").val();
        var dateTo = $("#to").val();
        var table = $('#product').DataTable( {
        "processing": true,
        "serverSide": true,
        "pageLength": 200,
        "fixedHeader": true,
        "searching": false,
        initComplete: function () {

      
          // Apply the search //add code for search
          this.api().columns().every( function () {
              var that = this;

               $('.table tfoot tr').insertAfter($('.table thead tr'));
                  // Pagination added after Table
                  $(".dataTables_wrapper div:nth-child(3)").insertAfter($('.datatable'));
                  $("input", this.footer()).on("keyup change clear", function () {
                      if (that.search() !== this.value) {
                          that.search(this.value).draw();
                      }

                });

          } ); 
      },
        "ajax": {
              "url": "<?php echo $document_root_url; ?>/scripts/order_revenue_data.php",
              "type": "POST",
              "data": function ( d ) {
                  d.dateFrom = dateFrom,
                  d.dateTo = dateTo
              }
            },
        "bDestroy": true
    } );

    $('.table tfoot th').each( function () {
      var title = $(this).text();
      if(title != "Brand") {
       $(this).html( '<input type="text" class="txtsearch" placeholder="Search '+title+'" />' );
      }
     });

    $('.table tfoot tr').insertAfter($('.table thead tr'));

      table.draw(); 
      // table.column( 1 ).visible( false );
      // var idx = table.column( 0 ).index( 'visible' );
      // alert( idx );

    });    
    // var table = $('#product').DataTable();
 
    // #column3_search is a <input type="text"> element
    // $('#test').on( 'keyup', function () {
    //     table.search( this.value ).draw();
    // } );

} );
</script>
  <!-- Load Custom price Js Ends -->  
</body>
</html>