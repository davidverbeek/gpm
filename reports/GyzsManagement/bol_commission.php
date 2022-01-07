
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
                            <thead style="z-index: 9999999;">
                                <tr>
                                  <th>Id</th>  
                                  <th>Sku</th>
                                  <th>VerpakkingsEAN</th>
                                  <th>Condition</th>
                                  <th>UnitPrice</th>
                                  <th>Fixed Amout</th>
                                  <th>Percentage</th>
                                  <th>Total Cost</th>
                                  <th>Updated Date</th>
                                 </tr>
                            </thead>
                            <tfoot style="display: none;">
                                <tr>
                                  <th>Id</th>  
                                  <th>Sku</th>
                                  <th>VerpakkingsEAN</th>
                                  <th>Condition</th>
                                  <th>UnitPrice</th>
                                  <th>Fixed Amout</th>
                                  <th>Percentage</th>
                                  <th>Total Cost</th>
                                  <th>Updated Date</th>
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
    </main>    
    
     <!-- Custom price Js -->
  <script language="javascript">
    var document_root_path = "<?php echo $document_root_path ?>";
    var document_root_url = "<?php echo $document_root_url; ?>";
  </script>
  <script src="<?php echo $document_root_url; ?>/js/bol_commission.js"></script>
  <!-- Load Custom price Js Ends -->  
</body>
</html>