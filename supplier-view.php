<?php

require_once __DIR__ . '/partials/security.php';
require_once __DIR__ . '/database/connection.php';

require_login('login.php');
require_roles(['admin', 'user'], false, 'dashboard.php', 'Sales access is limited to dashboard, products, and POS.');

$isAdmin = has_role('admin');

$query = "
    SELECT
        s.id,
        s.supplier_name,
        s.supplier_location,
        s.email,
        s.created_at,
        s.updated_at,
        u.first_name,
        u.last_name,
        GROUP_CONCAT(DISTINCT p.product_name ORDER BY p.product_name SEPARATOR ', ') AS products
    FROM supplier s
    LEFT JOIN users u ON s.created_by = u.id
    LEFT JOIN product_supplier_map psm ON psm.supplier_id = s.id
    LEFT JOIN products p ON p.id = psm.product_id
    GROUP BY s.id, s.supplier_name, s.supplier_location, s.email, s.created_at, s.updated_at, u.first_name, u.last_name
    ORDER BY s.created_at DESC
";
$stmt = $conn->prepare($query);
$stmt->execute();
$suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Suppliers ~VyaparTrack</title>
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
                    <h1 class="section_header"><i class="fa fa-list"></i> List of Suppliers</h1>
                    <?php include('partials/flash-response.php'); ?>
                    <div class="users">
                        <p class="userCount supplierCount"><?= count($suppliers) ?> Suppliers</p>
                        <table class="suppliers">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Supplier Name</th>
                                    <th>Supplier Location</th>
                                    <th>Contact Details</th>
                                    <th>Products</th>
                                    <th>Created By</th>
                                    <th>Created At</th>
                                    <th>Updated At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($suppliers as $index => $supplier): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td class="supplierName"><?= htmlspecialchars($supplier['supplier_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="supplierLocation"><?= htmlspecialchars($supplier['supplier_location'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="supplierEmail"><?= htmlspecialchars($supplier['email'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= $supplier['products'] !== null && $supplier['products'] !== '' ? htmlspecialchars($supplier['products'], ENT_QUOTES, 'UTF-8') : '-' ?></td>
                                        <td><?= htmlspecialchars(trim(((string) $supplier['first_name']) . ' ' . ((string) $supplier['last_name'])), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= date('M d,Y @h:i:s A', strtotime((string) $supplier['created_at'])) ?></td>
                                        <td><?= date('M d,Y @h:i:s A', strtotime((string) $supplier['updated_at'])) ?></td>
                                        <td class="actionCell">
                                            <?php if ($isAdmin): ?>
                                                <a href="#" class="action-btn editSupplier editBtn"
                                                   data-id="<?= (int) $supplier['id'] ?>"
                                                   data-name="<?= htmlspecialchars($supplier['supplier_name'], ENT_QUOTES, 'UTF-8') ?>"
                                                   data-location="<?= htmlspecialchars($supplier['supplier_location'], ENT_QUOTES, 'UTF-8') ?>"
                                                   data-email="<?= htmlspecialchars($supplier['email'], ENT_QUOTES, 'UTF-8') ?>"
                                                   title="Edit this item">
                                                    <i class="fa fa-pencil"></i> Edit
                                                </a>
                                                <a href="#" class="action-btn deleteSupplier deleteBtn"
                                                   data-id="<?= (int) $supplier['id'] ?>"
                                                   data-name="<?= htmlspecialchars($supplier['supplier_name'], ENT_QUOTES, 'UTF-8') ?>"
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
