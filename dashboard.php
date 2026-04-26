<?php
require_once __DIR__ . '/partials/security.php';
require_once __DIR__ . '/database/connection.php';

require_login('login.php');

$user_id = (int) ($_SESSION['user_id'] ?? 0);
if ($user_id <= 0) {
    die('Invalid session');
}

$stmt = $conn->prepare('SELECT * FROM users WHERE id = :id');
$stmt->bindParam(':id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    die('User not found');
}

$role = $_SESSION['role'] ?? 'user';

$stmt = $conn->query('SELECT COUNT(*) as total FROM products');
$products = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $conn->query('SELECT COUNT(*) as total FROM supplier');
$supplier = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $conn->query('SELECT COUNT(*) as total FROM users');
$total_users = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $conn->query('SELECT COUNT(*) as total FROM purchase_orders');
$total_orders = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard ~ VyaparTrack</title>

    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://code.highcharts.com/highcharts.js"></script>

    <style>
        .dashboardCards {
            display: flex;
            gap: 20px;
            margin-top: 25px;
            flex-wrap: wrap;
        }

        .dashboardCard {
            flex: 1;
            min-width: 220px;
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .dashboardCard i {
            font-size: 30px;
            color: #3498db;
        }

        .dashboardCard h3 {
            margin: 0;
            font-size: 26px;
        }

        .dashboardCard p {
            margin: 0;
            color: #777;
        }

        .dashboardCharts {
            display: flex;
            gap: 20px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .chartBox {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            flex: 1;
            min-width: 450px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        #orderStatusChart,
        #supplierProductChart,
        #deliveryHistoryChart {
            height: 350px;
        }
    </style>

</head>

<body>

    <div id="DashboardMainContainer">

        <?php include('partials/app-sidebar.php'); ?>

        <div class="DashboardContent_container" id="DashboardContent_container">

            <?php include('partials/app-topNav.php'); ?>

            <div class="dashboardContent">

                <div class="dashboard_content_main">

                    <h2>
                        Welcome, <?= htmlspecialchars($user['first_name'], ENT_QUOTES, 'UTF-8'); ?> (<?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8'); ?>)
                    </h2>

                    <?php include('partials/flash-response.php'); ?>

                    <p>
                        This is your dashboard. Use the sidebar to manage reports, products, suppliers, orders, and users.
                    </p>

                    <?php if ($role === 'admin'): ?>
                        <p style="color:green;"><b>You have admin access.</b></p>
                    <?php elseif ($role === 'sales'): ?>
                        <p style="color:#555;"><b>You have sales access.</b> You can manage POS, monitor the dashboard, and view product inventory.</p>
                        <p>
                            Quick links:
                            <a href="product-view.php">View Products</a> |
                            <a href="pos.php">POS Billing</a>
                        </p>
                    <?php else: ?>
                        <p style="color:#555;"><b>You are in view-only mode.</b> You can monitor stock and orders while admin users handle create/update actions.</p>
                        <p>
                            Quick links:
                            <a href="product-view.php">View Products</a> |
                            <a href="order-view.php">View Orders</a> |
                            <a href="reports.php">Reports</a>
                        </p>
                    <?php endif; ?>

                    <div class="dashboardCards">

                        <div class="dashboardCard" title="Total products in inventory">
                            <i class="fa fa-box"></i>
                            <div>
                                <h3><?= $products ?></h3>
                                <p>Total Products</p>
                            </div>
                        </div>

                        <div class="dashboardCard" title="Total registered suppliers">
                            <i class="fa fa-truck"></i>
                            <div>
                                <h3><?= $supplier ?></h3>
                                <p>Total Suppliers</p>
                            </div>
                        </div>

                        <div class="dashboardCard" title="Total system users">
                            <i class="fa fa-users"></i>
                            <div>
                                <h3><?= $total_users ?></h3>
                                <p>Total Users</p>
                            </div>
                        </div>

                        <div class="dashboardCard" title="Total purchase orders">
                            <i class="fa fa-shopping-cart"></i>
                            <div>
                                <h3><?= $total_orders ?></h3>
                                <p>Total Orders</p>
                            </div>
                        </div>
                    </div>

                    <div class="dashboardCharts">

                        <div class="chartBox">
                            <h3>Purchase Orders By Status</h3>
                            <div id="orderStatusChart"></div>
                        </div>

                        <div class="chartBox">
                            <h3>Orders Handled by Each Supplier</h3>
                            <div id="supplierProductChart"></div>
                        </div>

                    </div>

                    <div class="chartBox">
                        <h3>Delivery History Per Day</h3>
                        <div id="deliveryHistoryChart"></div>
                    </div>

                </div>
            </div>

        </div>

    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/highcharts/10.3.3/highcharts.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/dashboard.js"></script>
    <script src="js/dashboard-charts.js?v=<?= filemtime(__DIR__ . '/js/dashboard-charts.js') ?>"></script>
    <script src="js/tooltips.js?v=<?= filemtime(__DIR__ . '/js/tooltips.js') ?>"></script>
    <script src="js/script.js"></script>

</body>

</html>
