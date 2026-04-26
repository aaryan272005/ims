<?php

require_once __DIR__ . '/partials/security.php';

require_login('login.php');

if (!has_role('admin')) {
    $_SESSION['response'] = [
        'success' => false,
        'message' => 'Supplier creation is admin only.',
    ];
    header('Location: supplier-view.php');
    exit();
}

$_SESSION['table'] = 'supplier';
$_SESSION['redirect_to'] = 'supplier-add.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Supplier ~VyaparTrack</title>
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
                    <h1 class="section_header"><i class="fa fa-plus"></i> Create Supplier</h1>
                    <div id="userAddFormContainer">
                        <form action="database/add.php" method="POST" class="userForm">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                            <label>Supplier Name:</label>
                            <input type="text" placeholder="Enter supplier name..." name="supplier_name" maxlength="100" required>

                            <label>Location:</label>
                            <input type="text" placeholder="Enter product supplier location..." name="supplier_location" maxlength="100" required>

                            <label>Email:</label>
                            <input type="email" placeholder="Enter supplier email..." name="email" maxlength="100" required>

                            <button type="submit" class="userFormBtn">
                                <i class="fa fa-plus"></i> Create Supplier
                            </button>
                        </form>
                        <?php include('partials/flash-response.php'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="js/dashboard.js"></script>
    <script src="js/tooltips.js?v=<?= filemtime(__DIR__ . '/js/tooltips.js') ?>"></script>
</body>
</html>
