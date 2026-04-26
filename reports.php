<?php

require_once __DIR__ . '/partials/security.php';

require_login('login.php');
require_roles(['admin', 'user'], false, 'dashboard.php', 'Sales access is limited to dashboard, products, and POS.');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports ~VyaparTrack</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div id="DashboardMainContainer">
        <?php include('partials/app-sidebar.php'); ?>
        <div class="DashboardContent_container">
            <?php include('partials/app-topNav.php'); ?>
            <div class="dashboardContent">
                <div class="dashboard_content_main">
                    <?php include('partials/flash-response.php'); ?>
                    <div class="dashboard-content-container">
                        <h1 class="dashboard-title">Reports</h1>
                        <div class="reports-container">
                            <div class="report-box">
                                <div class="report-title">Export Products</div>
                                <div class="report-buttons">
                                    <a href="database/export.php?type=products&format=excel"><button class="btn-report">EXCEL</button></a>
                                    <a href="database/export.php?type=products&format=pdf"><button class="btn-report">PDF</button></a>
                                </div>
                            </div>
                            <div class="report-box">
                                <div class="report-title">Export Suppliers</div>
                                <div class="report-buttons">
                                    <a href="database/export.php?type=suppliers&format=excel"><button class="btn-report">EXCEL</button></a>
                                    <a href="database/export.php?type=suppliers&format=pdf"><button class="btn-report">PDF</button></a>
                                </div>
                            </div>
                            <div class="report-box">
                                <div class="report-title">Export Deliveries</div>
                                <div class="report-buttons">
                                    <a href="database/export.php?type=deliveries&format=excel"><button class="btn-report">EXCEL</button></a>
                                    <a href="database/export.php?type=deliveries&format=pdf"><button class="btn-report">PDF</button></a>
                                </div>
                            </div>
                            <div class="report-box report-highlight">
                                <div class="report-title">Export Purchase Orders</div>
                                <div class="report-buttons">
                                    <a href="database/export.php?type=orders&format=excel"><button class="btn-report">EXCEL</button></a>
                                    <a href="database/export.php?type=orders&format=pdf"><button class="btn-report">PDF</button></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="js/dashboard.js"></script>
    <script src="js/tooltips.js?v=<?= filemtime(__DIR__ . '/js/tooltips.js') ?>"></script>
</body>
</html>
