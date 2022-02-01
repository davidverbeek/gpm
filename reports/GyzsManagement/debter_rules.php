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
// Get Updated records categories

$sql = "SELECT * FROM price_management_customer_groups ORDER BY magento_id";
$all_customer_groups = array();

if ($result = $conn->query($sql)) {
   while ($row = $result->fetch_assoc()) {
     $all_customer_groups[$row['magento_id']] = $row['customer_group_name'];
   }
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

.wrap-float > * {
  display: block;
  float: left;
  /* Optional */
 /* max-width: 100%;*/
  
}


</style>

<body>
    <main>
        <!-- Sidebar -->
          <?php include "layout/left.php"; ?>
        <!-- End of Sidebar -->
        
        <!-- form fields  -->
        <section class="content-toggle" id="main-content">
            <div class="content-bg-blur h-100">
                

                <!-- Topbar -->
                <?php include "layout/top.php"; ?>
                <!-- End of Topbar --> 

                <div class="table-filter" id="data_filters1" style=" align-items-center">
                  
                    <div style="margin-bottom:10px;" >
                        
                          <div style="margin-bottom:10px;">
                            <select id="sel_debt_group" name="sel_debt_group" class="form-select ddfields" style="font-size: 12px;width:30%;" class="required">
                              <option value="">Select customer group</option>
                              <?php foreach($all_customer_groups as $g_id=>$g_value) { ?>
                                <option value="<?php echo $g_id; ?>"><?php echo $g_value; ?></option>
                              <?php } ?>  
                            </select>
                           
                          </div>
                          <div style="">
                          <span style=""><a href="#" id="linkCategories" style="display:none;">Add More Categories</a></span>
                          </div>
                        
                       
                    </div>
                    <div class="" style="margin-bottom:10px;">
                      <button type="button" class="btn btn-primary" id="btnsave">Save changes</button>
                    </div>
                </div>
        </section>
    </main>

     <!-- Hiddens -->
        <div>
          <input type="hidden" name="hdn_selectedcategories" id="hdn_selectedcategories" />
          <input type="hidden" name="hdn_existingcategories" id="hdn_existingcategories" />
          <input type="hidden" name="hdn_processfilename_n" id="hdn_processfilename_n" value="" />
        </div>
        <!-- End of Hiddens -->

         <!-- Custom price Js -->
  <script language="javascript">
    var document_root_path = "<?php echo $document_root_path ?>";
    var document_root_url = "<?php echo $document_root_url; ?>";
    var list = <?php echo json_encode($categories) ?>;
    var all_updated_categories = <?php echo json_encode($all_updated_categories) ?>;
  </script>
  <script src="<?php echo $document_root_url; ?>/js/debter_rules.js"></script>
  <!-- Load Custom price Js Ends -->  
