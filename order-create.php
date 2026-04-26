<?php

require_once __DIR__ . '/partials/security.php';

require_login('login.php');

if (!has_role('admin')) {
    $_SESSION['response'] = [
        'success' => false,
        'message' => 'Order creation is admin only.',
    ];
    header('Location: order-view.php');
    exit();
}

$_SESSION['table'] = 'products';
$_SESSION['redirect_to'] = 'order-create.php';
$products = include('database/show.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Orders ~VyaparTrack</title>
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
                    <h1 class="section_header"><i class="fa fa-plus"></i> Create Order</h1>
                    <form action="database/create-order.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                        <div class="align-right">
                            <button type="button" class="orderProductBtn">Add Another Product</button>
                        </div>
                        <div id="orderProductList">
                            <div class="orderProductRow" id="productRowTemplate">
                                <div class="align-right">
                                    <button type="button" class="removeProductRowBtn"><i class="fa fa-trash"></i> Remove</button>
                                </div>
                                <div>
                                    <label>PRODUCT NAME</label>
                                    <select class="product_name">
                                        <option value="">Select Product</option>
                                        <?php foreach ($products as $product): ?>
                                            <option value="<?= (int) $product['id'] ?>"><?= htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8') ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="supplierRows"></div>
                            </div>
                        </div>
                        <div class="align-right">
                            <button type="submit" class="orderProductSubmitBtn">Submit</button>
                        </div>
                    </form>
                    <?php include('partials/flash-response.php'); ?>
                </div>
            </div>
        </div>
    </div>
    <script src="js/dashboard.js"></script>
    <script src="js/tooltips.js?v=<?= filemtime(__DIR__ . '/js/tooltips.js') ?>"></script>
    <script src="js/script.js"></script>
</body>
</html>
