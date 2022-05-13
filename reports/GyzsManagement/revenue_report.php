
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
include "config/config.php";
include "define/constants.php";
include "layout/header.php";

// Get Updated records categories
$sql_updated_recs = "SELECT  DISTINCT(mccp.category_id) FROM mage_catalog_category_product AS mccp, price_management_data AS pmd WHERE mccp.product_id = pmd.product_id AND pmd.is_updated = '1'";
$result_updated_recs = $conn->query($sql_updated_recs);
$allUpdatedRecords = $result_updated_recs->fetch_all(MYSQLI_ASSOC);
$all_updated_categories = array();
foreach($allUpdatedRecords as $updated_rec) {
    $all_updated_categories[] = $updated_rec["category_id"];
}

// Get Revenue
$sql_revenue = "SELECT * FROM gyzsrevenuedata ORDER BY sku_vericale_som DESC LIMIT 1";
$res = $conn->query($sql_revenue);
$rev_data = $res->fetch_all(MYSQLI_ASSOC);
$rev_prev_date = explode("To", $rev_data[0]['reportdate']);

$sql_sum = "SELECT SUM(sku_refund_revenue_amount) AS tot_refund_amount, SUM(sku_abs_margin) AS tot_abs_margin FROM gyzsrevenuedata";
$res_sum = $conn->query($sql_sum);
$res_sum_data = $res_sum->fetch_all(MYSQLI_ASSOC);

// Get categories
$sql_cats = "SELECT  DISTINCT(mccp.category_id) FROM mage_catalog_category_product AS mccp, gyzsrevenuedata AS rd WHERE mccp.product_id = rd.product_id";
$result_cats = $conn->query($sql_cats);
$allCategories = $result_cats->fetch_all(MYSQLI_ASSOC);
$revenue_categories = array();
foreach($allCategories as $cat) {
  $revenue_categories[] = $cat["category_id"];
}
?>
<style>
.loader
{
    position: fixed;
    z-index: 9999999999;
    opacity: 0.5;
    left: 0px;
    top: 0px;
    width: 100%;
    height: 100%;
    background: url('images/loading.gif') 50% 50% no-repeat #f9f9f9;
}
.loader_txt {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align-last: center;
  color: #323584;
  font-weight: bold;
  font-style: italic;
}
</style>
<div id="showloader"><span class="loader_txt" style="display:none;">Please Wait....<br>Filtering Records...</span></div>
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

                <div style="font-size:10px;">
                        <div><b>Date:-</b> <span id="sel_date"><?php echo $rev_data[0]['reportdate']; ?></span></div>
                        <!--
                        <div style="margin-top:5px;">
                            <b>Total BP:-</b> <span id="tot_bp"><?php //echo $rev_data[0]['sku_vericale_som_bp']; ?></span>
                            <b>Total Absolute Margin:-</b> <span id="tot_abs_margin"><?php //echo $res_sum_data[0]['tot_abs_margin']; ?></span>
                            <b>Total Revenue:-</b> <span id="tot_revenue"><?php //echo $rev_data[0]['sku_vericale_som']; ?></span>

                            <b>Total Profit Margin SP :-</b> <span id="tot_pm_sp"><?php //echo round($res_sum_data[0]['tot_abs_margin']/$rev_data[0]['sku_vericale_som'],2); ?></span>
                            <b>Total Refund Amount:-</b> <span id="tot_refund"><?php //echo $res_sum_data[0]['tot_refund_amount']; ?></span>
                        </div>
                    -->
                </div>

                <div class="data-toggle overflow-hidden position-fixed" id="data-content">
                    <div class="datatable w-100 h-100 overflow-auto">
                        <!-- content Table -->
                        <table id="example" class="table position-relative custom-override-table">
                            <thead style="z-index: 9999999;">
                              <th>Id</th>
                              <th>Leverancier</th>
                              <th>Sku</th>
                              <th>Carrier Level</th>
                              <th>Name</th>
                              <th>Merken</th>
                              <th>Afzet (<?php echo $settings_data['roas']['sku_afzet_in_days']?>)</th>
                              <th>Afzet</th>
                              <th>Omzet</th>
                              <th>Vericale som</th>
                              <th>Vericale som (%)</th>
                              <th>Buying Price</th>
                              <th>Selling Price</th>
                              <th>Absolute margin</th>
                              <th>Profit margin BP %</th>
                              <th>Profit margin SP %</th>
                              <th>Vericale som (BP)</th>
                              <th>Vericale som (BP %)</th>

                              <th>Refund Quantities</th>
                              <th>Refund Amount</th>
                              <th>Refund Amount (BP)</th>

                              <th>Abs Mar. Vericale som</th>
                              <th>Abs Mar. Vericale som %</th>

                            </thead>
                            <tfoot style="display: none;" class="tfoothead">
                              <th>Id</th>
                              <th>Leverancier</th>
                              <th>Sku</th>
                              <th>Carrier level</th>
                              <th>Name</th>
                              <th>Merken</th>
                              <th>Afzet (<?php echo $settings_data['roas']['sku_afzet_in_days'] ?>)</th>
                              <th>Afzet</th>
                              <th>Omzet</th>
                              <th>Vericale som</th>
                              <th>Vericale som (%)</th>
                              <th>Buying Price</th>
                              <th>Selling Price</th>
                              <th>Absolute margin</th>
                              <th>Profit margin BP %</th>
                              <th>Profit margin SP %</th>
                              <th>Vericale som (BP)</th>
                              <th>Vericale som (BP %)</th>

                              <th>Refund Quantities</th>
                              <th>Refund Amount</th>
                              <th>Refund Amount (BP)</th>

                              <th>Abs Mar. Vericale som</th>
                              <th>Abs Mar. Vericale som %</th>


                                  <tr class="hr-line">
                                    <td colspan="200" class="position-absolute hr-rule-search"></td>
                                </tr>
                            </tfoot>


                            <tfoot class="tfootsum">
                              <th style="font-size: 0;">Id</th>
                              <th style="font-size: 0;">Leverancier</th>
                              <th style="font-size: 0;">Sku</th>
                              <th style="font-size: 0;">Carrier Level</th>
                              <th style="font-size: 0;">Name</th>
                              <th style="font-size: 0;">Merken</th>
                              <th style="font-size: 0;">Afzet (<?php echo $settings_data['roas']['sku_afzet_in_days']?>)</th>
                              <th style="font-size: 0;">Afzet</th>
                              <th style="font-size: 0;">Omzet</th>
                              <th id="tot_revenue"><?php echo number_format($rev_data[0]['sku_vericale_som'], 2, ',', '.'); ?></th>
                              <th style="font-size: 0;">Vericale som (%)</th>
                              <th style="font-size: 0;">Buying Price</th>
                              <th style="font-size: 0;">Selling Price</th>
                              <th id="tot_abs_margin"><?php echo number_format($res_sum_data[0]['tot_abs_margin'], 2, ',', '.'); ?></th>
                              <th style="font-size: 0;">Profit margin BP %</th>
                              <th id="tot_pm_sp"><?php 
                                    $tot_pm_sp = $res_sum_data[0]['tot_abs_margin']/$rev_data[0]['sku_vericale_som'];    
                                    echo number_format($tot_pm_sp, 2, ',', '.'); 
                                ?> 
                              </th>
                              <th id="tot_bp"><?php echo number_format($rev_data[0]['sku_vericale_som_bp'], 2, ',', '.'); ?></th>
                              <th style="font-size: 0;">Vericale som (BP %)</th>

                              <th style="font-size: 0;">Refund Quantities</th>
                              <th id="tot_refund"><?php echo number_format($res_sum_data[0]['tot_refund_amount'], 2, ',', '.'); ?></th>
                              <th style="font-size: 0;">Refund Amount (BP)</th>

                              <th style="font-size: 0;">Abs Mar. Vericale som</th>
                              <th style="font-size: 0;">Abs Mar. Vericale som %</th>
                            </tfoot>
                            
                        </table>
                    </div>
                </div>
                
            </div>
        </section>
        <!-- Filter Datatable settings -->
        <section class="filter-wrapper" id="filter-content">
            <a class="cog" id="cog-content"><img src="<?php echo $document_root_url; ?>/css/svg/filter.svg"
                        alt="filter-icon"></a>
            <div class="content-wrapper d-flex flex-column">

                <!-- set-price-card-details -->
                <div class=" set-price-card card">
                    <div class="hl-title tital-c3">
                        <span class="border-hl"></span>
                        <span class="colum-title title">Select Date</span>
                    </div>
                    <div>
                        <div class="data data-price d-flex row">
                            <div>
                              <input type="text" id="from" name="from" class="form-control" placeholder="Select From Date" value="<?php echo trim($rev_prev_date[0]); ?>"><br>
                              <input type="text" id="to" name="to" class="form-control" placeholder="Select To Date" value="<?php echo trim($rev_prev_date[1]); ?>"><br>
                              <button type="button" class="btn btn-primary getrevenuedatabtn" style="font-size: 12px; margin:0px;">Get Data</button>
                            </div>

                            <div id="rsp" style="display: none; margin-top: 5px;">Getting ordered data on the fly, so it will take some time please be patient...</div>
                            <div style="margin-top: 5px; color: #c10000;" id="get_data_fetch_errror"></div>           
                        </div>  
                    </div>
                </div>

                <div class=" set-price-card card">
                    <div class="hl-title tital-c3">
                        <span class="border-hl"></span>
                        <span class="colum-title title">Miscellaneous</span>
                    </div>
                    <div>
                        <div class="data data-price d-flex row">
                            <button class="btn btn-purple btn-sm no-modal col-5" type="button" id="btnexport">
                                <i class="fas fa-file-export"></i>Export Data
                                <span id="loading-img-export" style="display: none;"></span>
                            </button>

                            <button class="btn btn-purple btn-sm no-modal col-5" type="button" id="btnsync">
                                <i class="fas fa-sync"></i>Sync Sort Order
                            </button>
                            <div id="magsyncstatus" style="display: none; margin-top:20px;"></div>
                        </div>   
                    </div> 
                </div>


            </div>
        </section>
    </main>

    <!-- Hiddens -->
        <div>
          <input type="hidden" name="hdn_selectedcategories" id="hdn_selectedcategories"  />
          <input type="hidden" name="hdn_selectedbrand" id="hdn_selectedbrand" />
          
          
        </div>
    <!-- End of Hiddens -->

    <!-- Modals -->
        <?php include "layout/modals.php"; ?>
    <!-- End of Modals -->

    <!-- Custom price Js -->
   <script language="javascript">
    var column_index_revenue_report = <?php echo json_encode($column_index_revenue_report) ?>;
    var document_root_path = "<?php echo $document_root_path ?>";
    var document_root_url = "<?php echo $document_root_url; ?>";
    var list = <?php echo json_encode($categories) ?>;
    var settings = <?php echo json_encode($settings_data) ?>;
    var categoriesOfProducts = <?php echo json_encode($revenue_categories) ?>;
    var all_product_categories = <?php echo file_get_contents("pm_logs/pm_product_categories.txt") ?>;
  </script>
  <script src="js/revenue_report.js"></script>
  <script src=
"https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.js"></script>
  
<script>
 $( function() {
    $(function() {
            $( "#from" ).datepicker({
              defaultDate: "+1w",
              dateFormat: 'yy-mm-dd',  
              changeMonth: true,
              numberOfMonths: 2,
              onClose: function( selectedDate ) {
                $( "#to" ).datepicker( "option", "minDate", selectedDate );
              }
            });
            $( "#to" ).datepicker({
              defaultDate: "+1w",
              dateFormat: 'yy-mm-dd',
              changeMonth: true,
              numberOfMonths: 2,
              onClose: function( selectedDate ) {
                $( "#from" ).datepicker( "option", "maxDate", selectedDate );
              }
            });
          });
  } );
</script>



</body>
</html>