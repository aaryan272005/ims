<?php
require_once __DIR__ . '/security.php';

$user = $_SESSION['user'] ?? ['first_name' => 'User', 'last_name' => ''];
$role = current_user_role();
$isAdmin = has_role('admin');
$isSales = has_role('sales');
$canViewReports = !$isSales;
$canViewSuppliers = !$isSales;
$canViewOrders = !$isSales;
$canViewUsers = !$isSales;
$canUsePos = $isAdmin || $isSales;
$current_page = basename($_SERVER['PHP_SELF']);

$productPages = ['product-view.php'];
$supplierPages = $canViewSuppliers ? ['supplier-view.php'] : [];
$orderPages = $canViewOrders ? ['order-view.php'] : [];
$userPages = $canViewUsers ? ['users-view.php'] : [];

if ($isAdmin) {
    $productPages[] = 'product-add.php';
    $supplierPages[] = 'supplier-add.php';
    $orderPages[] = 'order-create.php';
    $userPages[] = 'users-add.php';
}
?>

<div class="DashboardSidebar" id="DashboardSidebar">

    <div class="dashboard_logo">
        <span class="hindi">&#2357;&#2381;&#2351;&#2366;&#2346;&#2366;&#2352;</span>
        <span class="english">Track</span>
    </div>

    <div class="dashboardSidebar_User">
        <img src="images/user/user1.png" alt="User Avatar">
        <span><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name'], ENT_QUOTES, 'UTF-8') ?></span>
        <small class="userRoleTag"><?= $isAdmin ? 'Admin Access' : ($isSales ? 'Sales Access' : 'User Access') ?></small>
    </div>

    <ul class="dashboard_menu_list">

        <li class="liMenu <?= ($current_page === 'dashboard.php') ? 'active' : '' ?>">
            <a href="dashboard.php">
                <i class="fa-solid fa-dashboard"></i>
                <span class="menuText">Dashboard</span>
            </a>
        </li>

        <?php if ($canViewReports): ?>
            <li class="liMenu <?= ($current_page === 'reports.php') ? 'active' : '' ?>">
                <a href="reports.php">
                    <i class="fa-solid fa-chart-line"></i>
                    <span class="menuText">Reports</span>
                </a>
            </li>
        <?php endif; ?>

        <li class="liMenu has-submenu <?= in_array($current_page, $productPages, true) ? 'open active' : '' ?>">
            <a href="javascript:void(0)">
                <i class="fa-solid fa-tag"></i>
                <span class="menuText">Product</span>
                <i class="fa fa-angle-down arrow"></i>
            </a>
            <ul class="sub-menu">
                <li>
                    <a href="product-view.php" class="<?= ($current_page === 'product-view.php') ? 'active' : '' ?>">
                        <i class="fa-solid fa-circle"></i>
                        <span class="menuText">View Products</span>
                    </a>
                </li>
                <?php if ($isAdmin): ?>
                    <li>
                        <a href="product-add.php" class="<?= ($current_page === 'product-add.php') ? 'active' : '' ?>">
                            <i class="fa-solid fa-circle"></i>
                            <span class="menuText">Add Products</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </li>

        <?php if ($canViewSuppliers): ?>
            <li class="liMenu has-submenu <?= in_array($current_page, $supplierPages, true) ? 'open active' : '' ?>">
                <a href="javascript:void(0)">
                    <i class="fa-solid fa-truck"></i>
                    <span class="menuText">Supplier</span>
                    <i class="fa fa-angle-down arrow"></i>
                </a>
                <ul class="sub-menu">
                    <li>
                        <a href="supplier-view.php" class="<?= ($current_page === 'supplier-view.php') ? 'active' : '' ?>">
                            <i class="fa-solid fa-circle"></i>
                            <span class="menuText">View Supplier</span>
                        </a>
                    </li>
                    <?php if ($isAdmin): ?>
                        <li>
                            <a href="supplier-add.php" class="<?= ($current_page === 'supplier-add.php') ? 'active' : '' ?>">
                                <i class="fa-solid fa-circle"></i>
                                <span class="menuText">Add Supplier</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </li>
        <?php endif; ?>

        <?php if ($canViewOrders): ?>
            <li class="liMenu has-submenu <?= in_array($current_page, $orderPages, true) ? 'open active' : '' ?>">
                <a href="javascript:void(0)">
                    <i class="fa-solid fa-cart-plus"></i>
                    <span class="menuText">Purchase Order</span>
                    <i class="fa fa-angle-down arrow"></i>
                </a>
                <ul class="sub-menu">
                    <li>
                        <a href="order-view.php" class="<?= ($current_page === 'order-view.php') ? 'active' : '' ?>">
                            <i class="fa-solid fa-circle"></i>
                            <span class="menuText">View Orders</span>
                        </a>
                    </li>
                    <?php if ($isAdmin): ?>
                        <li>
                            <a href="order-create.php" class="<?= ($current_page === 'order-create.php') ? 'active' : '' ?>">
                                <i class="fa-solid fa-circle"></i>
                                <span class="menuText">Create Order</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </li>
        <?php endif; ?>

        <?php if ($canUsePos): ?>
            <li class="liMenu <?= ($current_page === 'pos.php') ? 'active' : '' ?>">
                <a href="pos.php">
                    <i class="fa-solid fa-store"></i>
                    <span class="menuText">POS</span>
                </a>
            </li>
        <?php endif; ?>

        <?php if ($canViewUsers): ?>
            <li class="liMenu has-submenu <?= in_array($current_page, $userPages, true) ? 'open active' : '' ?>">
                <a href="javascript:void(0)">
                    <i class="fa-solid fa-user"></i>
                    <span class="menuText">User</span>
                    <i class="fa fa-angle-down arrow"></i>
                </a>
                <ul class="sub-menu">
                    <li>
                        <a href="users-view.php" class="<?= ($current_page === 'users-view.php') ? 'active' : '' ?>">
                            <i class="fa-solid fa-circle"></i>
                            <span class="menuText">View Users</span>
                        </a>
                    </li>
                    <?php if ($isAdmin): ?>
                        <li>
                            <a href="users-add.php" class="<?= ($current_page === 'users-add.php') ? 'active' : '' ?>">
                                <i class="fa-solid fa-circle"></i>
                                <span class="menuText">Add Users</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </li>
        <?php endif; ?>

    </ul>

</div>
