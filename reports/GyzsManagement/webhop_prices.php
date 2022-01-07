
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
                                  <th>Artikelnummer (Artikel)</th>
                                  <th>Ean</th>
                                  <th>Merk</th>
                                  <th>Webshop Afwijkenidealeverpakking</th>
                                  <th>Webshop Idealeverpakking</th>
                                  <th>Webshop Inkoopprijs</th>
                                  <th>Webshop Verkoopprijs</th>
                                  <?php for($d=0;$d<=10;$d++) { 
                                    $cust_group = intval(4027100 + $d);
                                   ?> 
                                  <th class="<?php echo $cust_group; ?>">Verkpr (<?php echo $cust_group; ?>)</th>
                                  <?php }   ?>                                  
                                </tr>
                            </thead>
                            <tfoot style="display: none;">
                                <tr>
                                  <th>ProductId</th>
                                  <th>Naam</th>
                                  <th>Artikelnummer (Artikel)</th>
                                  <th>Ean</th>
                                  <th>Merk</th>
                                  <th>Webshop Afwijkenidealeverpakking</th>
                                  <th>Webshop Idealeverpakking</th>
                                  <th>Webshop Inkoopprijs</th>
                                  <th>Webshop Verkoopprijs</th>
                                  <?php for($d=0;$d<=10;$d++) { 
                                    $cust_group = intval(4027100 + $d);
                                   ?> 
                                  <th class="<?php echo $cust_group; ?>">Verkpr (<?php echo $cust_group; ?>)</th>
                                  <?php }   ?>                                  
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
            <a class="cog" id="cog-content" onclick="toggleFilter()"><img src="<?php echo $document_root_url; ?>/css/svg/filter.svg"
                        alt="filter-icon"></a>
            <div class="content-wrapper d-flex flex-column">

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
                                <option value="1">Buying Price 0</option>
                                <option value="2">Selling Price 0</option>
                                <option value="3">4027100 Price 0</option>
                                <option value="4">4027101 Price 0</option>
                                <option value="5">4027102 Price 0</option>
                                <option value="6">4027103 Price 0</option>
                                <option value="7">4027104 Price 0</option>
                                <option value="8">4027105 Price 0</option>
                                <option value="9">4027106 Price 0</option>
                                <option value="10">4027107 Price 0</option>
                                <option value="11">4027108 Price 0</option>
                                <option value="12">4027109 Price 0</option>
                                <option value="13">4027110 Price 0</option>
                              </optgroup>
                              
                            </select>
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
          <input type="hidden" name="hdn_filters" id="hdn_filters" />
        </div>
        <!-- End of Hiddens -->
    
     <!-- Custom price Js -->
  <script language="javascript">
    var column_index = <?php echo json_encode($column_index) ?>;
    var document_root_path = "<?php echo $document_root_path ?>";
    var document_root_url = "<?php echo $document_root_url; ?>";
    var list = <?php echo json_encode($categories) ?>;
  </script>
  <script src="<?php echo $document_root_url; ?>/js/webshop_price.js"></script>
  <!-- Load Custom price Js Ends -->  
</body>
</html>