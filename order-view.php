<?php

require_once __DIR__ . '/partials/security.php';
require_once __DIR__ . '/database/connection.php';

require_login('login.php');
require_roles(['admin', 'user'], false, 'dashboard.php', 'Sales access is limited to dashboard, products, and POS.');

$isAdmin = has_role('admin');

$query = "
    SELECT
        po.id,
        po.quantity_order,
        po.quantity_received,
        p.product_name,
        s.supplier_name,
        u.first_name,
        u.last_name,
        po.created_at
    FROM purchase_orders po
    LEFT JOIN products p ON po.product_id = p.id
    LEFT JOIN supplier s ON po.supplier_id = s.id
    LEFT JOIN users u ON po.created_by = u.id
    ORDER BY po.created_at DESC
";
$stmt = $conn->prepare($query);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Orders ~VyaparTrack</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/order.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div id="DashboardMainContainer">
        <?php include('partials/app-sidebar.php'); ?>
        <div class="DashboardContent_container">
            <?php include('partials/app-topNav.php'); ?>
            <div class="dashboardContent">
                <div class="dashboard_content_main">
                    <h1 class="section_header"><i class="fa fa-list"></i> List of Purchase Orders</h1>
                    <?php include('partials/flash-response.php'); ?>
                    <div class="users">
                        <p class="userCount orderCount"><?= count($orders) ?> Orders</p>
                        <table class="orders">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th>Supplier</th>
                                    <th>Qty Ordered</th>
                                    <th>Qty Received</th>
                                    <th>Qty Remaining</th>
                                    <th>Status</th>
                                    <th>Ordered By</th>
                                    <th>Created At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($orders)): ?>
                                    <?php foreach ($orders as $index => $order): ?>
                                        <?php
                                        $orderedQty = (int) $order['quantity_order'];
                                        $receivedQty = (int) $order['quantity_received'];
                                        $remainingQty = max(0, $orderedQty - $receivedQty);
                                        $orderStatus = $receivedQty <= 0 ? 'pending' : ($receivedQty < $orderedQty ? 'incomplete' : 'complete');
                                        ?>
                                        <tr id="orderRow<?= (int) $order['id'] ?>">
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars((string) $order['product_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars((string) $order['supplier_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= $orderedQty ?></td>
                                            <td><?= $receivedQty ?></td>
                                            <td><?= $remainingQty ?></td>
                                            <td>
                                                <span class="status status-<?= htmlspecialchars($orderStatus, ENT_QUOTES, 'UTF-8') ?>" title="Order status">
                                                    <?= htmlspecialchars($orderStatus, ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars(trim(((string) $order['first_name']) . ' ' . ((string) $order['last_name'])), ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= date('M d,Y @h:i:s A', strtotime((string) $order['created_at'])) ?></td>
                                            <td class="actionCell">
                                                <?php if ($isAdmin): ?>
                                                    <button class="updateOrderBtn action-btn"
                                                        data-id="<?= (int) $order['id'] ?>"
                                                        data-product="<?= htmlspecialchars((string) $order['product_name'], ENT_QUOTES, 'UTF-8') ?>"
                                                        data-ordered="<?= $orderedQty ?>"
                                                        data-received="<?= $receivedQty ?>"
                                                        data-supplier="<?= htmlspecialchars((string) $order['supplier_name'], ENT_QUOTES, 'UTF-8') ?>"
                                                        data-status="<?= htmlspecialchars($orderStatus, ENT_QUOTES, 'UTF-8') ?>"
                                                        title="Edit this item">
                                                        <i class="fa fa-edit"></i> Update
                                                    </button>
                                                <?php else: ?>
                                                    <span style="color:#999;">View Only</span>
                                                <?php endif; ?>
                                                <button class="viewDeliveryBtn action-btn" data-id="<?= (int) $order['id'] ?>" title="View delivery history">
                                                    <i class="fa fa-truck"></i> Deliveries
                                                </button>
                                                <?php if ($isAdmin): ?>
                                                    <button class="deleteOrderBtn action-btn deleteBtn"
                                                        data-id="<?= (int) $order['id'] ?>"
                                                        data-name="<?= htmlspecialchars((string) $order['product_name'], ENT_QUOTES, 'UTF-8') ?>"
                                                        title="Delete this item">
                                                        <i class="fa fa-trash"></i> Delete
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" style="text-align:center">No Orders Found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/order.js"></script>
    <script src="js/dashboard.js"></script>
    <script src="js/tooltips.js?v=<?= filemtime(__DIR__ . '/js/tooltips.js') ?>"></script>
    <script src="js/script.js"></script>
</body>
</html>
