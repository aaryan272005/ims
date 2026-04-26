<?php

require_once __DIR__ . '/partials/security.php';

require_login('login.php');
require_roles(['admin', 'user'], false, 'dashboard.php', 'Sales access is limited to dashboard, products, and POS.');

$_SESSION['table'] = 'users';
$users = include('database/show.php');
$isAdmin = has_role('admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Users ~VyaparTrack</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div id="DashboardMainContainer">
        <?php include('partials/app-sidebar.php'); ?>
        <div class="DashboardContent_container" id="DashboardContent_container">
            <?php include('partials/app-topNav.php'); ?>
            <div class="dashboardContent">
                <div class="dashboard_content_main">
                    <h1 class="section_header"><i class="fa fa-list"></i> List of Users</h1>
                    <?php include('partials/flash-response.php'); ?>
                    <div class="users">
                        <p class="userCount"><?= count($users) ?> Users</p>
                        <table class="users">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Created At</th>
                                    <th>Updated At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $index => $user): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td class="fname"><?= htmlspecialchars($user['first_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="lname"><?= htmlspecialchars($user['last_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="email"><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="role"><?= htmlspecialchars(ucfirst($user['role'] ?? 'user'), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= date('M d,Y @h:i:s A', strtotime((string) $user['created_at'])) ?></td>
                                        <td><?= date('M d,Y @h:i:s A', strtotime((string) $user['updated_at'])) ?></td>
                                        <td class="actionCell">
                                            <?php if ($isAdmin): ?>
                                                <a href="#" class="action-btn editUser editBtn"
                                                   data-userid="<?= (int) $user['id'] ?>"
                                                   data-fname="<?= htmlspecialchars($user['first_name'], ENT_QUOTES, 'UTF-8') ?>"
                                                   data-lname="<?= htmlspecialchars($user['last_name'], ENT_QUOTES, 'UTF-8') ?>"
                                                   data-email="<?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?>"
                                                   title="Edit this item">
                                                    <i class="fa fa-pencil"></i> Edit
                                                </a>
                                                <a href="#" class="action-btn deleteUser deleteBtn"
                                                   data-userid="<?= (int) $user['id'] ?>"
                                                   data-fname="<?= htmlspecialchars($user['first_name'], ENT_QUOTES, 'UTF-8') ?>"
                                                   data-lname="<?= htmlspecialchars($user['last_name'], ENT_QUOTES, 'UTF-8') ?>"
                                                   title="Delete this item">
                                                    <i class="fa fa-trash"></i> Delete
                                                </a>
                                            <?php else: ?>
                                                <span style="color:#999;">View Only</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/dashboard.js"></script>
    <script src="js/tooltips.js?v=<?= filemtime(__DIR__ . '/js/tooltips.js') ?>"></script>
    <script src="js/script.js"></script>
</body>
</html>
