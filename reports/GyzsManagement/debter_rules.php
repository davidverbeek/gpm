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
<!--<link href="<?php echo $document_root_url; ?>/css/sb-admin.min.css" rel="stylesheet">-->
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

.form-control {
      font-size: 10px;
    }

.form-group {
    margin-bottom: 1rem;
}

.bg-primary {
    background-color: #3a3d99!important;
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

                <div class="" id="data_filters1" style=" align-items-center">
                  <div class="form-group">
                    <div class="card">
                      <div class="card-header bg-primary text-white">Add and Update Customer Group</div>
                      <div class="card-body">
                        <div class="form-group row">
                          <label class="col-sm-2 col-form-label">Customer Group</label>
                          <div class="col-sm-10">
                            <div style="margin-bottom:10px;" >
                                  <div style="margin-bottom:10px;">
                                    <select id="sel_debt_group" name="sel_debt_group" class="custom-select custom-select-sm form-control form-control-sm ddfields" style="font-size: 12px;width:30%;" class="required">
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
                            <div class="row" style="margin-bottom:10px;">
                              <div class="col-lg-6">
                                <button type="button" class="btn btn-primary" id="btnsave">Save changes</button>
                              </div>
                            </div>
                          </div><!--col-sm-10-->
                        </div><!--row-->
                      </div><!--card-body-->
                  </div><!--card-->
                </div><!--form-group-->

                <div class="form-group">
                    <div class="card">
                      <div class="card-header bg-primary text-white">Copy Categories</div>
                      <div class="card-body">
                        <div class="form-group row">
                          <!--From dropdown-->
                          <div style="margin-bottom:10px;">
                            <select id="parent_debt_group" name="parent_debt_group" class="custom-select custom-select-sm form-control form-control-sm copyfields" style="font-size: 12px;width:30%;" class="required">
                              <option value="">Copy from customer group</option>
                              <?php foreach($all_customer_groups as $g_id=>$g_value) { ?>
                                <option value="<?php echo $g_id; ?>"><?php echo $g_value; ?></option>
                              <?php } ?>  
                            </select>
                          </div>
                        </div>

                        <div class="form-group row">
                          <!--To dropdown -->
                          <div style="margin-bottom:10px;">
                            <select id="child_debt_group" name="child_debt_group" class="custom-select custom-select-sm form-control form-control-sm copyfields" style="font-size: 12px;width:30%;" class="required">
                              <option value="">Copy to customer group</option>
                              <?php foreach($all_customer_groups as $g_id=>$g_value) { ?>
                                <option value="<?php echo $g_id; ?>"><?php echo $g_value; ?></option>
                              <?php } ?>  
                            </select>
                          </div>
                        </div>

                        <div class="row">
                    <div class="col-lg-6">
                       <input type="button" name="btncopy" id="btncopy" class="btn btn-primary"  value="Copy Categories" />
                    </div>
                  </div>
                     </div>
                    </div>
                  </div>
              </div>
        </section>
    </main>

     <!-- Hiddens -->
        <div>
          <input type="hidden" name="hdn_selectedcategories" id="hdn_selectedcategories" />
          <input type="hidden" name="hdn_existingcategories" id="hdn_existingcategories" />
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