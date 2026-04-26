<?php
require_once __DIR__ . '/partials/security.php';

require_login('login.php');

if (($_SESSION['role'] ?? '') !== 'admin') {
    $_SESSION['response'] = [
        'success' => false,
        'message' => 'You have view-only access. User creation is admin only.',
    ];
    header('Location: users-view.php');
    exit();
}

$_SESSION['table'] = 'users';
$_SESSION['redirect_to'] = 'users-add.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Users ~ VyaparTrack</title>

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

                    <h1 class="section_header">
                        <i class="fa fa-plus"></i> Create User
                    </h1>

                    <div id="userAddFormContainer">

                        <form action="database/add.php" method="POST" class="userForm">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

                            <label>First Name:</label>
                            <input type="text" name="first_name" maxlength="50" required>

                            <label>Last Name:</label>
                            <input type="text" name="last_name" maxlength="50" required>

                            <label>Email:</label>
                            <input type="email" name="email" maxlength="100" required>

                            <label>Password:</label>
                            <input type="password" name="password" minlength="8" required>

                            <label>Access Role:</label>
                            <select name="role" required>
                                <option value="user" selected>User Access</option>
                                <option value="sales">Sales Access</option>
                                <option value="admin">Admin Access</option>
                            </select>

                            <button type="submit" class="userFormBtn">
                                <i class="fa fa-plus"></i> Add User
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
