
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
include "config/dbconfig.php";

$get_ec_delivery_time_sql = "SELECT * FROM price_management_ec_deliverytime";
$get_ec_delivery_time_be_sql = "SELECT * FROM price_management_ec_deliverytime_be";

?>
<style>
td.details-control {
    background: url('images/details_open.png') no-repeat 46px center;
    cursor: pointer;
}
tr.shown td.details-control {
    background: url('images/details_close.png') no-repeat 46px center;
}

.details-table {
   width: 100%; 
}
.details-table-wrapper {
   margin-left: 140px;
   width: 88%; 
}
.details-table-head {
   background-color: #d6d7ea;
   text-align: left;
   padding: 5px;
}
.details-table-col1 {
   float: left;
   width: 20%;
   text-align: left;
   padding: 2px; 
}
.details-table-col2 {
   float: left;
   width: 80%;
   text-align: left;
   padding: 2px;
}
.details-table-row1, .details-table-row3, .details-table-row5, .details-table-row7, .details-table-row9, .details-table-row11, .details-table-row13, .details-table-row15, .details-table-row17  {
   background-color: #ffffff;
   border-bottom: 1px solid #d3d3db; 
}
.details-table-row2, .details-table-row4, .details-table-row6, .details-table-row8, .details-table-row10, .details-table-row12, .details-table-row14, .details-table-row16, .details-table-row18   {
   background-color: #f2f2f2; 
   border-bottom: 1px solid #d3d3db; 
}
.clr {
    clear: both;
}

</style>

<body class="fl-width">
    <main>
        <!-- Sidebar -->
          <?php //include "layout/left.php"; ?>
        <!-- End of Sidebar -->

       
       
        <!-- Datatable and header  -->
        <section class="content-toggle" id="main-content">
            <div class="content-bg-blur h-100">
                

            <!-- Topbar -->
              <?php include "layout/top.php"; ?>
            <!-- End of Topbar --> 

                
                <div class="table-filter d-flex align-items-center" id="data_filters">
                    
                    <div><input type="checkbox" name="chkall_sup" id="chkall_sup"> Check All (<span id="check_all_cnt">0</span>)</div>

                    <!-- Data Search Filter -->
                    <div class="inner-addon">
                        <label for="dtSearch">
                            <img src="<?php echo $document_root_url; ?>/css/svg/search.svg" alt="search-icon">
                            <input type="text" id="dtSearch" class="" placeholder="search">                            
                        </label>
                    </div>
                </div>
                <!-- Price Management Table -->
                <div class="data-toggle overflow-hidden position-fixed" id="data-content">
                    <div class="datatable w-100 h-100 overflow-auto">
                        <!-- content Table -->
                        <table id="example" class="table position-relative custom-override-table">
                            <thead style="z-index: 999;">
                                <tr>
                                  <th>Product Id</th>
                                  <th>Sku</th>
                                  <th>Name</th>
                                  <th>Supplier</th>
                                  <th>Bol Min. Price</th>
                                  <th>Buying Price</th>
                                  <th>Updated Date</th>
                                  <th>EC Delivery Time</th>
                                  <th>EC Delivery Time BE</th>
                                  <th>Bol Selling Price</th>
                                  <th>Selling Price</th>
                                 </tr>
                            </thead>
                            <tfoot style="display: none;">
                                <tr>
                                  <th>Product Id</th>
                                  <th>Sku</th>
                                  <th>Name</th>
                                  <th>Supplier</th>
                                  <th>Bol Min. Price</th>
                                  <th>Buying Price</th>
                                  <th>Updated Date</th>
                                  <th>EC Delivery Time</th>
                                  <th>EC Delivery Time BE</th>
                                  <th>Bol Selling Price</th>
                                  <th>Selling Price</th>
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

        <section class="filter-wrapper" id="filter-content">
            <a class="cog" id="cog-content"><img src="<?php echo $document_root_url; ?>/css/svg/filter.svg" alt="filter-icon"></a>
            <div class="content-wrapper d-flex flex-column">

                <div class=" set-price-card card">
                    <div class="hl-title tital-c3">
                        <span class="border-hl"></span>
                        <span class="colum-title title">Update EC Delivery Time</span>
                    </div>
                    <div>
                        <div class="data data-price d-flex">
                            <select class="form-select form-select-sm" aria-label="Default select example" id="select_ec_deliverytime">
                              <option value="">Select EC Delivery Time</option>
                              <?php
                                if ($ec_result = $conn->query($get_ec_delivery_time_sql)) {
                                  while ($ec_row = $ec_result->fetch_assoc()) { ?>
                                    <option value="<?php echo $ec_row['option_id']; ?>"><?php echo $ec_row['option_value']; ?></option>
                                  <?php }
                                }
                               ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class=" set-price-card card">
                    <div class="hl-title tital-c3">
                        <span class="border-hl"></span>
                        <span class="colum-title title">Update EC Delivery Time BE</span>
                    </div>
                    <div>
                        <div class="data data-price d-flex">
                            <select class="form-select form-select-sm" aria-label="Default select example" id="select_ec_deliverytime_be">
                              <option value="">Select EC Delivery Time BE</option>
                              <?php
                                if ($ec_be_result = $conn->query($get_ec_delivery_time_be_sql)) {
                                  while ($ec_be_row = $ec_be_result->fetch_assoc()) { ?>
                                    <option value="<?php echo $ec_be_row['option_id']; ?>"><?php echo $ec_be_row['option_value']; ?></option>
                                  <?php }
                                }
                               ?>
                            </select>
                        </div>
                    </div>
                </div>


            </div>    
        </section>


    </main> 

    <!-- Modals -->
        <?php include "layout/modals.php"; ?>
    <!-- End of Modals -->
   
    
     <!-- Custom price Js -->
  <script language="javascript">
    var document_root_path = "<?php echo $document_root_path ?>";
    var document_root_url = "<?php echo $document_root_url; ?>";
  </script>
  <script src="<?php echo $document_root_url; ?>/js/bol_minimum.js"></script>
  <!-- Load Custom price Js Ends -->  
</body>
</html>
