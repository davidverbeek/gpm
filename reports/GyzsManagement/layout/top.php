<ul class="nav nav-menu position-relative" id="nav-wrapper">
    <li class="nav-item <?php if (strpos($_SERVER['REQUEST_URI'], 'setprice') > 0) { ?> active  <?php } ?> ">
        <a class="nav-link setting" href="setprice.php">
            <i class="fas fa-euro-sign"></i>
            <span>Manage Prices</span>
        </a>
    </li>
    <li class="nav-item <?php if (strpos($_SERVER['REQUEST_URI'], 'debter_rules') > 0) { ?> active  <?php } ?> ">
        <a class="nav-link setting" href="debter_rules.php">
            <span>Debter Rules</span>
        </a>
    </li>
    <li class="nav-item parent overflow-none <?php if (strpos($_SERVER['REQUEST_URI'], 'bol_commission') > 0 || strpos($_SERVER['REQUEST_URI'], 'bol_minimum') > 0) { ?> active  <?php } ?> ">
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
    <li class="nav-item parent overflow-none <?php if (strpos($_SERVER['REQUEST_URI'], 'importedlog') > 0 || strpos($_SERVER['REQUEST_URI'], 'exportedlog') > 0 || strpos($_SERVER['REQUEST_URI'], 'backup_restore') > 0 || strpos($_SERVER['REQUEST_URI'], 'webhop_prices') > 0) { ?> active  <?php } ?> ">
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

    <li class="nav-item <?php if (strpos($_SERVER['REQUEST_URI'], 'revenue_report') > 0) { ?> active  <?php } ?> ">
        <a class="nav-link setting" href="revenue_report.php">
            <i class="fas fa-euro-sign"></i>
            <span>Revenue Report</span>
        </a>
    </li>

    <li class="nav-item <?php if (strpos($_SERVER['REQUEST_URI'], 'settings') > 0) { ?> active  <?php } ?> ">
        <a class="nav-link setting" href="settings.php" style="transition: 0.3s;">
            <img alt="GYZS" src="css/svg/settings-purple.svg" style="transition: 0.3s;">
            <span>Settings</span>
        </a>
    </li>
</ul>
<header class="header nav px-3 d-flex" id="header-content">
    <div class="navbar overflow-hidden p-0">
        <a class="navbar-brand" href="#" id="navbar-brand" style="display: none;">
            <img alt="GYZS" id="gif" src="<?php echo $document_root_url; ?>/css/gif/GYZS_black.gif">

        </a>
    </div>
    <div class="roas d-flex">
        <?php echo $top_image; ?>
        <span><?php echo $header_txt; ?></span>
    </div>
    <div class="notification nav-item ms-auto">
        <a class="nav-link p-0 m-2" href="#">
            <img style="width: 18px;" alt="GYZS" src="<?php echo $document_root_url; ?>/css/svg/notification-bell_1.svg">
        </a>
    </div>
    <div class="admin nav-item">
        <a class="nav-link pe-0 ps-0" href="#">
            <span>Welcome Admin</span>
            <img alt="GYZS" src="<?php echo $document_root_url; ?>/css/svg/admin.svg">
            
        </a>
        <a class="nav-link pe-0 ps-0" style="font-size: 23px; color: #323584; margin-left: 5px; " href="#" data-bs-toggle="modal" data-bs-target="#logoutModal" title="Logout">
            <i class="fas fa-sign-out-alt"></i>
        </a>
    </div>
</header>
