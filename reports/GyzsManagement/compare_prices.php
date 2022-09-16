<?php
session_start();
require_once("../../app/Mage.php");
umask(0);
Mage::app();
?>

<!DOCTYPE html>
<html lang="en">
<?php
//include "config/config.php";
//include "define/constants.php";
include "layout/header.php";?>
<link href="<?php echo $document_root_url; ?>/css/jquery.dataTables.complexHeader.css" rel="stylesheet">
<style>
    table#compare_dt>thead>tr>th {
        width: 366px;
    }
</style>
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

              <div class="table-filter d-flex align-items-center" id="data_filters">                   
                    <!-- Data Search Filter -->
                    <div class="inner-addon">
                        <label for="dtSearch">
                            <img src="<?php echo $document_root_url; ?>/css/svg/search.svg" alt="search-icon">
                            <input type="text" id="dtSearch" class="" placeholder="search">                            
                        </label>
                    </div>
                </div>
                <div class="data-toggle overflow-hidden position-fixed" id="data-content">
                    <div class="datatable w-100 h-100 overflow-auto">
                        <table id="compare_dt" class="table position-relative custom-override-table">
                            <thead style="z-index: 9999999;">
                                <tr>
                                    <th rowspan="2">Ean</th>
                                    <th colspan="5">Mavis</th>
                                    <th colspan="5">Polvo</th>
                                    <th colspan="5">Dozon</th>
                                    <th colspan="5">Transferro</th>
                                </tr>
                                <tr>
                                    <th>SKU</th>
                                    <th>Inkpr</th>            
                                    <th>ideal. Verp</th>
                                    <th>Afw.ideal.verp</th>
                                    <th>Inkpr (per Piece)</th>
                                    <th>SKU</th>
                                    <th>Inkpr</th>            
                                    <th>ideal. Verp</th>
                                    <th>Afw.ideal.verp</th>
                                    <th>Inkpr (per Piece)</th>
                                    <th>SKU</th>
                                    <th>Inkpr</th>            
                                    <th>ideal. Verp</th>
                                    <th>Afw.ideal.verp</th>
                                    <th>Inkpr (per Piece)</th>
                                    <th>SKU</th>
                                    <th>Inkpr</th>            
                                    <th>ideal. Verp</th>
                                    <th>Afw.ideal.verp</th>
                                    <th>Inkpr (per Piece)</th>
                                </tr>
                            </thead>
                        
                            <tfoot>
                                <tr>
                                <th>Ean</th>
                                <th>SKU</th>
                                <th>Inkpr</th>            
                                <th>ideal. Verp</th>
                                <th>Afw.ideal.verp</th>
                                <th>Inkpr(Per Piece)</th>
                                <th>SKU</th>
                                <th>Inkpr</th>            
                                <th>ideal. Verp</th>
                                <th>Afw.ideal.verp</th>
                                <th>Inkpr(Per Piece)</th>
                                <th>SKU</th>
                                <th>Inkpr</th>            
                                <th>ideal. Verp</th>
                                <th>Afw.ideal.verp</th>
                                <th>Inkpr(Per Piece)</th>
                                <th>SKU</th>
                                <th>Inkpr</th>            
                                <th>ideal. Verp</th>
                                <th>Afw.ideal.verp</th>
                                <th>Inkpr(Per Piece)</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <script language="javascript">
    var column_index = <?php echo json_encode($column_index_compare_prices) ?>;
    var document_root_path = "<?php echo $document_root_path ?>";
    var document_root_url = "<?php echo $document_root_url; ?>";
    </script>
<script type="text/javascript" src="<?php echo $document_root_url; ?>/js/compare_prices.js"></script>