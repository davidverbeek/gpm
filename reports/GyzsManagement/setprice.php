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
// Get debter product ids
$sql = "SELECT customer_group_name, product_ids  FROM price_management_customer_groups JOIN price_management_debter_categories ON price_management_debter_categories.customer_group = price_management_customer_groups.magento_id";

if ($result = $conn->query($sql)) {
  $debter_data = array();
  while($row = $result->fetch_assoc()) {
    $group_number = substr($row['customer_group_name'], -3);
    $debter_data[$group_number] = $row["product_ids"];
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

.striped_span {
    display: inline-block;
    padding-right: 47px;
    background:transparent;
    background-color:none;
}

.refreshicon {
    color: #323584;
    font-size: 16px;
    padding: 40px,40px;
    cursor: pointer;
}

.search_group_dd {
    margin-top: -30px;
    margin-left: -42px;
    position: absolute;
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

                    <div>
                        <input type="checkbox" name="chkall" id="chkall"/> Check All (<span id="check_all_cnt">0</span>)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="checkbox" name="chkavges" id="chkavges"/> Averages Marge Verkpr %&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="checkbox" name="chkbulkupdates" id="chkbulkupdates"/> Enable Bulk Update&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <i class="fas fa-sync refreshicon" aria-hidden="true" id="reset_btn_id" title="Reset filters"></i>
                    </div>

                  
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
                                  <th>Leverancier</th>
                                  <th>Naam</th>
                                  <th>SKU <i class="fas fa-envelope-open" style="font-size:12px; cursor:pointer; color:#3a3d99" title="Mark all as read"></i></th>
                                  <th>Ean</th>
                                  <th>Merk</th>
                                  <th>Afzet(<?php echo $settings_data['roas']['sku_afzet_in_days']?>)</th>
                                  <th>Brutopr</th>
                                  <th>Webshop Gross Price</th>
                                  <th>Korting brutopr</th>
                                  <th>Nettopr Lev</th>
                                  <th>Webshop Net Price</th>
                                  <th>Ideal.verp</th>
                                  <th>Webshop Idealeverpakking</th>
                                  <th>Afw.Ideal.verp</th>
                                  <th>Webshop Afwijkenidealeverpakking</th>
                                  <th>PM Inkpr</th>

                                  <th>Cat Gem</th>
                                  <th>Merk Gem</th>
                                  <th>Cat Merk Gem</th>

                                  <th>WS Inkpr</th>
                                  <th>WS Verkpr</th>
                                  <th>Korting bruto vkpr</th>
                                  <th>PM Vkpr</th>
                                  <th>Marge Inkpr %</th>
                                  <th>Marge Verkpr %</th>
                                  <th>Korting Brupr %</th>
                                  <th>Stijging %</th>

                                  <?php for($d=0;$d<=10;$d++) { 
                                    $cust_group = intval(4027100 + $d);
                                   ?> 
                                  <th class="<?php echo $cust_group; ?>">Verkpr<br>(<?php echo $cust_group; ?>)</th>
                                  <th class="<?php echo $cust_group; ?>">Marge Inkpr %<br>(<?php echo $cust_group; ?>)</th>
                                  <th class="<?php echo $cust_group; ?>">Marge Verkpr %<br>(<?php echo $cust_group; ?>)</th>
                                  <th class="<?php echo $cust_group; ?>">Korting Brutpr %<br>(<?php echo $cust_group; ?>)</th>

                                  <?php }   ?>

                                  <th>Is Updated</th>
                                  <th>Is Activated</th>
                                  <th>Magento Updated</th>
                                  
                                </tr>
                            </thead>
                            <tfoot style="display: none;">
                                <tr>
                                  <th>ProductId</th>
                                  <th>Leverancier</th>
                                  <th>Naam</th>
                                  <th>SKU <i class="fas fa-envelope-open" style="font-size:12px; cursor:pointer; color:#3a3d99" title="Mark all as read"></i></th>
                                  <th>Ean</th>
                                  <th>Merk</th>
                                  <th>Afzet(<?php echo $settings_data['roas']['sku_afzet_in_days']?>)</th>
                                  <th>Brutopr</th>
                                  <th>Webshop Gross Price</th>
                                  <th>Korting brutopr</th>
                                  <th>Nettopr Lev</th>
                                  <th>Webshop Net Price</th>
                                  <th>Ideal.verp</th>
                                  <th>Webshop Idealeverpakking</th>
                                  <th>Afw.Ideal.verp</th>
                                  <th>Webshop Afwijkenidealeverpakking</th>
                                  <th>PM Inkpr</th>
                                  <th>Cat Gem</th>
                                  <th>Merk Gem</th>
                                  <th>Cat Merk Gem</th>
                                  <th>WS Inkpr</th>
                                  <th>WS Verkpr</th>
                                  <th>Korting bruto vkpr</th>
                                  <th>PM Vkpr</th>
                                  <th>Marge Inkpr %</th>
                                  <th>Marge Verkpr %</th>
                                  <th>Korting Brupr %</th>
                                  <th>Stijging %</th>

                                  <?php for($d=0;$d<=10;$d++) { 
                                    $cust_group = intval(4027100 + $d);
                                   ?> 
                                  <th class="<?php echo $cust_group; ?>">Verkpr<br>(<?php echo $cust_group; ?>)</th>
                                  <th class="<?php echo $cust_group; ?>">Marge Inkpr %<br>(<?php echo $cust_group; ?>)</th>
                                  <th class="<?php echo $cust_group; ?>">Marge Verkpr %<br>(<?php echo $cust_group; ?>)</th>
                                  <th class="<?php echo $cust_group; ?>">Korting Brutpr %<br>(<?php echo $cust_group; ?>)</th>

                                  <?php }   ?>

                                  <th>Is Updated</th>
                                  <th>Is Activated</th>
                                  <th>Magento Updated</th>
                                  
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
        <section class="filter-wrapper" id="filter-content">
            <a class="cog" id="cog-content" ><img src="<?php echo $document_root_url; ?>/css/svg/filter.svg"
                        alt="filter-icon"></a>
            <div class="content-wrapper d-flex flex-column">

                <!-- set-price-card-details -->
                <div class=" set-price-card card">
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
                </div>

                <div class=" set-price-card card">
                    <div class="hl-title tital-c3">
                        <span class="border-hl"></span>
                        <span class="colum-title title">Miscellaneous</span>
                    </div>
                    <div>
                        <div class="data data-price d-flex row">
                            <button class="btn btn-purple btn-sm no-modal col-5" type="button" id="btnactivate">
                                <i class="fa fa-check-circle" aria-hidden="true"></i>Activate Updated
                            </button>

                           <button class="btn btn-purple btn-sm no-modal col-5" type="button" id="btnexport">
                                <i class="fas fa-file-export"></i>Export Data
                                <span id="loading-img-export" style="display: none;"></span>
                            </button>

                             <div>&nbsp;</div>
                             <button class="btn btn-purple btn-sm no-modal col-5" type="button" id="btnimport" data-bs-toggle="modal" data-bs-target="#ImportModal">
                                <i class="fas fa-file-import"></i></i>Import Data 
                             </button>

                             <!-- <button class="btn btn-purple btn-sm no-modal col-5" type="button" id="btnundo" data-bs-toggle="modal" data-bs-target="#UndoModal">
                                <i class="fas fa-undo"></i></i>Undo Selling Price 
                             </button> -->

                           <!-- <button class="btn btn-danger btn-sm no-modal col-5" type="button" id="btnactivateimmed">
                                <i class="fas fa-check-circle" aria-hidden="true"></i>Activate Immediately
                            </button>
                            <div>&nbsp;</div>

                            <button class="btn btn-danger btn-sm no-modal col-5" type="button" id="btnrefreshmagento">
                                <i class="fas fa-sync"></i>Refresh Magento
                            </button>
                            -->
                            
                        </div>   
                    </div> 
                </div> 

                
                <div class=" set-price-card card">
                    <div class="hl-title tital-c3">
                        <span class="border-hl"></span>
                        <span class="colum-title title">Filters</span>
                    </div>
                    

                    <div>
                        <div class="data data-price d-flex">
                            <select class="form-select form-select-sm" aria-label="Default select example" id="filter_with">
                              <option value="0">Select filters</option>
                              <optgroup label="Related to price">
                                <option value="1">Buying Price > Selling Price (Webshop)</option>
                                <option value="2">Profit Margin on selling price < 40% (Webshop)</option>
                                <option value="3">Debter Selling Price > Webshop Selling Price (Debter)</option>
                                <option value="4">Profit Margin on selling price < 40% (Debter)</option>
                                <option value="6">Increase in Buying Price</option>
                                <option value="7">Decrease in Buying Price</option>
                                <option value="8">Increase in Selling Price</option>
                                <option value="9">Decrease in Selling Price</option>
                              </optgroup>
                              <optgroup label="General">
                                <option value="5">Show Updated</option>
                                <option value="10">Stijging % <=</option>
                                <option value="11">Stijging % Between</option>
                                <option value="12">Stijging % >=</option>    
                              </optgroup>
                            </select>
                        </div>    
                    </div>
                </div>                    
                
                <!-- sort-colum-card-details -->
                <div class="sort-colum-card card">
                    <div class="hl-title tital-c3">
                        <span class="border-hl"></span>
                        <span class="colum-title title">View/Hide Colums</span>
                    </div>
                    <div>
                        <div class="data data-select row">
                            <label for="id" class="col-12" style="background:#dddeee; padding: 5px;">
                                <input type="checkbox" value="all" name="all" class="show_cols_all"><span>All</span>
                            </label> 
                            <label for="id" class="col-6">
                                <input type="checkbox" value="1" name="id" class="show_cols open_by_default"><span>Leverancier</span>
                            </label>
                            <label for="sku" class="col-6">
                                <input type="checkbox" value="2" name="sku" class="show_cols open_by_default"><span>Naam</span>
                            </label>
                            <label for="name" class="col-6">
                                <input type="checkbox" value="3" name="name" class="show_cols open_by_default"><span>SKU</span>
                            </label>
                            <label for="brand-a" class="col-6">
                                <input type="checkbox" value="4" name="ean" class="show_cols"><span>Ean</span>
                            </label>
                            <label for="brand-a" class="col-6">
                                <input type="checkbox" value="5" name="brand" class="show_cols open_by_default"><span>Merk</span>
                            </label>
                            <label for="afzet" class="col-6">
                                <input type="checkbox" value="6" name="afzet" class="show_cols"><span>Afzet(<?php echo $settings_data['roas']['sku_afzet_in_days']?>)</span>
                            </label>
                            <label for="brand-a" class="col-6">
                                <input type="checkbox" value="7" name="supplier_gross_price" class="show_cols"><span>Brutopr</span>
                            </label>
                            <label for="brand-a" class="col-6">
                                <input type="checkbox" value="8" name="s_d_o_gp" class="show_cols"><span>Korting brutopr</span>
                            </label>
                            <label for="brand-a" class="col-6">
                                <input type="checkbox" value="9" name="s_n_p" class="show_cols"><span>Nettopr Lev</span>
                            </label>
                            <label for="brand-a" class="col-6">
                                <input type="checkbox" value="11" name="idealeverpakking" class="show_cols"><span>Ideal.verp</span>
                            </label>
                            <label for="brand-a" class="col-6">
                                <input type="checkbox" value="13" name="afwijkenidealeverpakking" class="show_cols"><span>Afw.Ideal.verp</span>
                            </label>
                            <label for="brand-a" class="col-6">
                                <input type="checkbox" value="15" name="buying_price" class="show_cols open_by_default"><span>PM Inkpr</span>
                            </label>
                            
                            <label for="brand-a" class="col-6">
                                <input type="checkbox" value="16" name="a_p_b" class="show_cols chav"><span>Cat Gem</span>
                            </label>
                            <label for="brand-a" class="col-6">
                                <input type="checkbox" value="17" name="a_p_c" class="show_cols chav"><span>Merk Gem</span>
                            </label>
                            <label for="brand-a" class="col-6">
                                <input type="checkbox" value="18" name="a_p_c_p_b" class="show_cols chav"><span>Cat Merk Gem</span>
                            </label>
                            <label for="brand-a" class="col-6">
                                <input type="checkbox" value="19" name="g_b_p" class="show_cols"><span>WS Inkpr</span>
                            </label>

                            <label for="brand-a" class="col-6">
                                <input type="checkbox" value="20" name="g_s_p" class="show_cols"><span>WS Verkpr</span>
                            </label>
                            <label for="brand-a" class="col-6">
                                <input type="checkbox" value="21" name="g_d_o_gp" class="show_cols"><span>Korting bruto vkpr</span>
                            </label>

                            <label for="brand-a" class="col-6">
                                <input type="checkbox" value="22" name="g_d_o_gp" class="show_cols open_by_default"><span>PM Vkpr</span>
                            </label>
                            <label for="brand-a" class="col-6">
                                <input type="checkbox" value="23" name="g_d_o_gp" class="show_cols open_by_default"><span>Marge Inkpr %</span>
                            </label>
                            <label for="brand-a" class="col-6">
                                <input type="checkbox" value="24" name="g_d_o_gp" class="show_cols open_by_default"><span>Marge Verkpr %</span>
                            </label>
                            <label for="brand-a" class="col-6">
                                <input type="checkbox" value="25" name="g_d_o_gp" class="show_cols open_by_default"><span>Korting Brupr %</span>
                            </label>


                            <label for="brand-a" class="col-6">
                                <input type="checkbox" value="26" name="per_increase" class="show_cols open_by_default"><span>Stijging %</span>
                            </label>

                            <div style="clear:both;"></div>

                            
                            <label for="id" class="col-2" style="background:#dddeee; padding: 5px;">
                                <input type="checkbox" value="all_dsp" name="all_dsp" class="show_cols_all_dsp"><span>All SP</span>
                            </label>

                            <label for="id" class="col-3" style="background:#dddeee; padding: 5px;">
                                <input type="checkbox" value="all_dmbp" name="all_dmbp" class="show_cols_all_dmbp"><span>All Marg.BP</span>
                            </label>

                            <label for="id" class="col-3" style="background:#dddeee; padding: 5px;">
                                <input type="checkbox" value="all_dmbp" name="all_dmbp" class="show_cols_all_dmsp"><span>All Marg.SP</span>
                            </label>

                            <label for="id" class="col-4" style="background:#dddeee; padding: 5px;">
                                <input type="checkbox" value="all_dmbp" name="all_dmbp" class="show_cols_all_ddgp"><span>All Discount GP</span>
                            </label>

                            <div style="clear:both;"></div>
                                <label for="btnDebCategories" class="col-12" style="display:none;">
                                    <button class="btn btn-purple btn-sm no-modal col-7" type="button" id="btnDebCategories" style="float:right;">
                                        <i class="fa fa-filter"></i>Apply Debter Categories
                                        <span class="loading-img-update" style="display: none;"></span>
                                    </button>
                                </label>
                            <div style="clear:both;"></div>
                            <?php 
                                $deb_col_idx = 28;
                            for($hc=0;$hc<=10;$hc++) { 
                                $h_cust_group = intval(4027100 + $hc);
                            ?>
                            <label for="brand-a" class="col-6">
                                <input type="checkbox" value="<?php echo $deb_col_idx; ?>" name="<?php echo $h_cust_group; ?>" class="show_cols_dsp show_deb_cols"><span>SP (<?php echo $h_cust_group; ?>)</span>
                            </label>
                            <?php $deb_col_idx++; ?>
                            <label for="brand-a" class="col-6">
                                <input type="checkbox" value="<?php echo $deb_col_idx; ?>" name="<?php echo $h_cust_group; ?>" class="show_cols_dmbp show_deb_cols"><span>Marg.BP (<?php echo $h_cust_group; ?>)</span>
                            </label>
                            <?php $deb_col_idx++; ?>
                            <label for="brand-a" class="col-6">
                                <input type="checkbox" value="<?php echo $deb_col_idx; ?>" name="<?php echo $h_cust_group; ?>" class="show_cols_dmsp show_deb_cols"><span>Marg.SP (<?php echo $h_cust_group; ?>)</span>
                            </label>
                            <?php $deb_col_idx++; ?>
                            <label for="brand-a" class="col-6">
                                <input type="checkbox" value="<?php echo $deb_col_idx; ?>" name="<?php echo $h_cust_group; ?>" class="show_cols_ddgp show_deb_cols"><span>Discount GP (<?php echo $h_cust_group; ?>)</span>
                            </label>

                            <?php $deb_col_idx++; } ?>    

                            <!-- <label for="brand-a" class="col-6">
                                <input type="checkbox" value="24" name="update_logs" class="show_cols"><span>Update logs</span>
                            </label> -->
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
          <input type="hidden" name="hdn_group_search_text" id="hdn_group_search_text" />

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
    var debter_product_data = <?php echo json_encode($debter_data) ?>; 
  </script>
  <script src="<?php echo $document_root_url; ?>/js/setprice.js"></script>
  <!-- Load Custom price Js Ends -->  
</body>
</html>
