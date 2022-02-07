<?php

  
// Get the image and convert into string
//$img = file_get_contents('https://cdn.gyzs.nl/media/catalog/product/cache/3/image/700x700/9df78eab33525d08d6e5fb8d27136e95/1/0/1091125.png');
  
// Encode the image string data into base64
//$data = base64_encode($img);
  
// Display the output
//echo $data;
//exit;
/*
$categories = array();
$categories["categories"][0]["name"] = "ParentCat";
$categories["categories"][0]["is_active"] = true;
$categories["categories"][0]["id"] = 156;
$categories["categories"][0]["custom_attributes"][0]["attribute_code"] = "custom_filter";
$categories["categories"][0]["custom_attributes"][0]["value"] = "heyparent";


$categories["categories"][0]["child"][0]["name"] = "child One";
$categories["categories"][0]["child"][0]["is_active"] = true;
$categories["categories"][0]["child"][0]["id"] = 157;
$categories["categories"][0]["child"][0]["custom_attributes"][0]["attribute_code"] = "custom_filter";
$categories["categories"][0]["child"][0]["custom_attributes"][0]["value"] = "heychildone";


$categories["categories"][0]["child"][1]["name"] = "child One One";
$categories["categories"][0]["child"][1]["is_active"] = true;
$categories["categories"][0]["child"][1]["id"] = 161;
$categories["categories"][0]["child"][1]["custom_attributes"][0]["attribute_code"] = "custom_filter";
$categories["categories"][0]["child"][1]["custom_attributes"][0]["value"] = "heychildoneone";


$categories["categories"][0]["child"][0]["child"][0]["name"] = "child Two";
$categories["categories"][0]["child"][0]["child"][0]["is_active"] = true;
$categories["categories"][0]["child"][0]["child"][0]["id"] = 158;
$categories["categories"][0]["child"][0]["child"][0]["custom_attributes"][0]["attribute_code"] = "custom_filter";
$categories["categories"][0]["child"][0]["child"][0]["custom_attributes"][0]["value"] = "heychildtwo";


$categories["categories"][0]["child"][0]["child"][0]["name"] = "last child for two";
$categories["categories"][0]["child"][0]["child"][0]["is_active"] = true;
$categories["categories"][0]["child"][0]["child"][0]["id"] = 163;
$categories["categories"][0]["child"][0]["child"][0]["custom_attributes"][0]["attribute_code"] = "custom_filter";
$categories["categories"][0]["child"][0]["child"][0]["custom_attributes"][0]["value"] = "heylastchildfortwo";


$categories["categories"][0]["child"][1]["child"][0]["name"] = "child Two Two";
$categories["categories"][0]["child"][1]["child"][0]["is_active"] = true;
$categories["categories"][0]["child"][1]["child"][0]["id"] = 162;
$categories["categories"][0]["child"][1]["child"][0]["custom_attributes"][0]["attribute_code"] = "custom_filter";
$categories["categories"][0]["child"][1]["child"][0]["custom_attributes"][0]["value"] = "heychildtwotwo";


//$categories["categories"][0]["child"][1]["child"][0]["name"] = "last child for Two Two";
//$categories["categories"][0]["child"][1]["child"][0]["is_active"] = true;
//$categories["categories"][0]["child"][1]["child"][0]["id"] = 164;
//$categories["categories"][0]["child"][1]["child"][0]["custom_attributes"][0]["attribute_code"] = "custom_filter";
//$categories["categories"][0]["child"][1]["child"][0]["custom_attributes"][0]["value"] = "heylastchildfortwotwo";


$categories["categories"][1]["name"] = "New Parent";
$categories["categories"][1]["is_active"] = true;
$categories["categories"][1]["id"] = 160;
$categories["categories"][1]["custom_attributes"][0]["attribute_code"] = "custom_filter";
$categories["categories"][1]["custom_attributes"][0]["value"] = "heynewparent";

$categories["categories"][1]["child"][0]["name"] = "last child for Two Two";
$categories["categories"][1]["child"][0]["is_active"] = true;
$categories["categories"][1]["child"][0]["id"] = 164;
$categories["categories"][1]["child"][0]["custom_attributes"][0]["attribute_code"] = "custom_filter";
$categories["categories"][1]["child"][0]["custom_attributes"][0]["value"] = "heylastchildfortwotwo";



$category_data = json_encode($categories);
echo $category_data;

$array_category_data = json_decode($category_data,true);

getAllCategories($array_category_data,0);

function getAllCategories($array_category_data,$parent_id) {
    foreach($array_category_data as $category) {
        echo "<pre>";
        print_r($category);
        $new_parent_id = 1;
        if(isset($category["child"])) {
            getAllCategories($category["child"],$new_parent_id);
        }

    }
}

exit;

*/

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
<div id="showloader"><span class="loader_txt" style="display:none;">Please Wait....<br>Calculating Averages</span></div>

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

                    <!-- Data Length Filter -->
                    <!--
                    <div class="select-opt">
                        <label for="length_change" class="bold">show
                            <select name="length_change" id="length_change">
                                <option value='5'>5</option>
                                <option value='10'>10</option>
                                <option value='15'>15</option>
                                <option value='100' selected="selected">100</option>
                            </select>
                            entries
                        </label>
                    </div>
                    -->

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
                                  <th>ProductId</th>
                                  <th>Naam</th>
                                  <th>SKU</th>
                                  <th>Ean</th>
                                  <th>Ideal.verp</th>
                                  <th>Afw.Ideal.verp</th>
                                  <th>PM Inkpr</th>
                                  <th>PM Vkpr</th>
                                  <th>Competitor Data</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </section>
        <!-- Filter Datatable settings -->
        <section class="filter-wrapper" id="filter-content">
            <a class="cog" id="cog-content" onclick="toggleFilter()"><img src="<?php echo $document_root_url; ?>/css/svg/filter.svg"
                        alt="filter-icon"></a>
            <div class="content-wrapper d-flex flex-column">

                <!-- set-price-card-details -->
                <!-- <div class=" set-price-card card">
                    <div class="hl-title tital-c3">
                        <span class="border-hl"></span>
                        <span class="colum-title title">Set Prices</span>
                    </div>
                    <div>
                        <div class="data data-price d-flex row">
                            <button class="btn btn-purple btn-sm no-modal col-5" type="button" id="btnsellingprice">
                                <i class="fas fa-euro-sign"></i>Selling Price
                            </button>
                        
                            <button class="btn btn-purple btn-sm col-5" type="button" id="btnprofitmargin">
                                <i class="fas fa-euro-sign"></i>Profit Marg.(BP)
                            </button>
                            <div>&nbsp;</div>

                            <button class="btn btn-purple btn-sm col-5"  type="button" id="btnprofitmarginsp">
                                <i class="fas fa-euro-sign"></i>Profit Marg.SP
                            </button>

                            <button class="btn btn-purple btn-sm col-5" type="button" id="btndiscount">
                                <i class="fas fa-euro-sign"></i>Discount On GP
                            </button>
                        </div>

                            
                    </div>
                </div> -->

                <div class=" set-price-card card">
                    <div class="hl-title tital-c3">
                        <span class="border-hl"></span>
                        <span class="colum-title title">Miscellaneous</span>
                    </div>
                    <div>
                        <div class="data data-price d-flex row">
                            <!-- <button class="btn btn-purple btn-sm no-modal col-5" type="button" id="btnactivate">
                                <i class="fa fa-check-circle" aria-hidden="true"></i>Activate Updated
                            </button> -->

                           <button class="btn btn-purple btn-sm no-modal col-5" type="button" id="btnexport">
                                <i class="fas fa-file-export"></i>Export Data
                                <span id="loading-img-export" style="display: none;"></span>
                            </button>
                   
                            
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
          <input type="hidden" name="hdn_selectedbrand" id="hdn_selectedbrand" />
          
          <input type="hidden" name="hdn_processfilename_n" id="hdn_processfilename_n" value="" />
          <input type="hidden" name="hdn_filters" id="hdn_filters" />
          
          <input type="hidden" name="hdn_percentage_increase_column" id="hdn_percentage_increase_column" value="9" />
          <input type="hidden" name="hdn_log_column" id="hdn_log_column" value="18" />

          <input type="hidden" name="hdn_stijging_text" id="hdn_stijging_text" />

        </div>
        <!-- End of Hiddens -->

  <!-- Modals -->
    <?php include "layout/modals.php"; ?>
  <!-- End of Modals -->
    
     <!-- Custom price Js -->
  <script language="javascript">
    var column_index = <?php echo json_encode($column_index) ?>;
    var document_root_path = "<?php echo $document_root_path ?>";
    var document_root_url = "<?php echo $document_root_url; ?>";
    var list = <?php echo json_encode($categories) ?>;
    var all_updated_categories = <?php echo json_encode($all_updated_categories) ?>;
    var all_product_categories = <?php echo file_get_contents("pm_logs/pm_product_categories.txt") ?>;
    var product_category_id = <?php echo file_get_contents("pm_logs/product_cat_id.txt") ?>;   
  </script>
  <script src="<?php echo $document_root_url; ?>/js/competitor-price.js"></script>
  <!-- Load Custom price Js Ends -->  
</body>
</html>
