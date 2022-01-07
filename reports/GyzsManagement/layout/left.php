<?php
 $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
  $sql = "SELECT distinct(mcce.entity_id), mcce.parent_id, mccev.value
          FROM
          mage_catalog_category_entity AS mcce
          INNER JOIN
          mage_catalog_category_entity_varchar AS mccev ON mccev.entity_id = mcce.entity_id
          AND mccev.attribute_id = '".CATEGORYNAME_ATTRIBUTE_ID."'";
  $allcategories = $connection->fetchAll($sql);

  $categories = array();
  $temp = 0;
  foreach($allcategories as $row) {
      $categories[$temp]["id"] = $row["entity_id"];
      $categories[$temp]["pid"] = $row["parent_id"];
      $categories[$temp]["name"] = $row["value"];
      $temp++;
  }
?>

 <aside class="sidebar-toggle d-flex flex-row position-fixed" id="sidebar">
            <!-- Navigation -->
            <section class="position-relative h-100 w-100">
                <!-- Header Brand -->
                <div class="navbar overflow-hidden">
                    <a class="navbar-brand" href="#">
                        <img alt="GYZS" class="brand-bg d-block" src="<?php echo $document_root_url; ?>/css/gif/GYZS1.gif" id="brand-logo">
                    </a>
                </div>
                <!-- Navbar Popup icon -->
                <div class="slider-blue position-absolute" onclick="toggleSidebar()" id="toggle-slider">
                    <img src="<?php echo $document_root_url; ?>/css/svg/slider_menu_blue.svg" alt="slider-menu" id="slider">
                </div>
                <div class="d-flex flex-column w-100 h-100 position-absolute top-0" style="padding-top: 64px;">
                    <!-- Brand-header -->
                    <div class="category overflow-hidden w-100 position-absolute">
                        <img alt="GYZS" src="css/svg/catagory.svg">
                        <span>Category</span>
                    </div>
                    <!-- Catalog-tree Links -->
                    <ul class="list-group position-relative w-100">
                        <li id="sim-tree">
                            <div id="tree"></div>
                        </li>
                    </ul>
                    <!-- Navigation page link -->
                    <ul class="nav nav-menu position-relative" id="nav-wrapper">
                        <!-- <li class="nav-item parent overflow-none <?php if(strpos($_SERVER['REQUEST_URI'], 'feedroas') > 0 || strpos($_SERVER['REQUEST_URI'], 'currentroas') > 0 || strpos($_SERVER['REQUEST_URI'], 'googleroas') > 0 ) { ?> active  <?php } ?> ">
                            
                                <a class="nav-link feed w-100" href="#">
                                    <img alt="GYZS" src="<?php echo $document_root_url; ?>/css/svg/feed.svg">
                                    <span>Manage Roas</span>
                                </a>
                                

                            <ul class="sub-menu nav" id="nest-nav">
                                <li class="nav-item">
                                    <a class="nav-link current" href="feedroas.php">
                                        Feed Roas
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link current" href="currentroas.php">
                                        Current Roas
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link current" href="googleroas.php">
                                        Google Roas
                                    </a>
                                </li>

                            </ul>
                        </li> -->

                       <!-- <li class="nav-item <?php if(strpos($_SERVER['REQUEST_URI'], 'setprice') > 0) { ?> active  <?php } ?> ">
                            <a class="nav-link" href="setprice.php" style="transition: 0.3s;">
                                <i class="fas fa-euro-sign"></i>
                                <span>Manage Prices</span>
                            </a>
                        </li>


                        <li class="nav-item parent overflow-none <?php if(strpos($_SERVER['REQUEST_URI'], 'bol_commission') > 0 || strpos($_SERVER['REQUEST_URI'], 'bol_minimum') > 0) { ?> active  <?php } ?> ">
                            
                                <a class="nav-link feed w-100" href="#">
                                    <img alt="GYZS" src="<?php echo $document_root_url; ?>/images/bol.png" width="20" height="20">
                                    <span>Bol</span>
                                </a>
                                
                            
                            <ul class="sub-menu nav" id="nest-nav">
                                <li class="nav-item">
                                    <a class="nav-link" href="bol_commission.php">
                                        Bol Commission
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="bol_minimum.php">
                                        Bol Minimum
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item parent overflow-none <?php if(strpos($_SERVER['REQUEST_URI'], 'importedlog') > 0 || strpos($_SERVER['REQUEST_URI'], 'exportedlog') > 0 || strpos($_SERVER['REQUEST_URI'], 'backup_restore') > 0) { ?> active  <?php } ?> ">
                            
                                <a class="nav-link feed w-100" href="#">
                                    <i class="fas fa-history"></i>
                                    <span>Logs</span>
                                </a>
                                
                            
                            <ul class="sub-menu nav" id="nest-nav">
                                <li class="nav-item">
                                    <a class="nav-link" href="importedlog.php">
                                        Imported Logs
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="exportedlog.php">
                                        Exported Logs
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="backup_restore.php">
                                        DB Backups
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="webhop_prices.php">
                                        Webshop Prices
                                    </a>
                                </li>
                            </ul>
                        </li>

                        

                        <li class="nav-item <?php if(strpos($_SERVER['REQUEST_URI'], 'settings') > 0) { ?> active  <?php } ?> ">
                            <a class="nav-link setting" href="settings.php" style="transition: 0.3s;">
                                <img alt="GYZS" src="css/svg/settings.svg" style="transition: 0.3s;">
                                <span>Settings</span>
                            </a>
                        </li> -->
                    </ul>
                </div>
            </section>
        </aside>
