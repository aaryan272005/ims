<?php
session_start();
require_once __DIR__ . '/partials/security.php';
require_once __DIR__ . '/database/connection.php';
require_login('login.php');

$_SESSION['table'] = 'products';
$products = include('database/show.php');
$isAdmin = has_role('admin');

function product_image_src(string $imageName, string $productName): string
{
    $imageName = trim($imageName);
    if ($imageName !== '' && is_file(__DIR__ . '/uploads/products/' . $imageName)) {
        return 'uploads/products/' . rawurlencode($imageName);
    }

    $letters = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($productName)) ?? '';
    $label = substr($letters !== '' ? $letters : 'NA', 0, 2);
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 120 120">'
        . '<rect width="120" height="120" rx="16" fill="#eef3f8"/>'
        . '<text x="60" y="69" text-anchor="middle" font-size="34" font-family="Arial, sans-serif" fill="#7a8ca3">' . $label . '</text>'
        . '</svg>';

    return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>View Products ~VyaparTrack</title>
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
                    <h1 class="section_header"><i class="fa fa-list"></i> List of Products</h1>
                    <?php include('partials/flash-response.php'); ?>
                    <div class="users">
                        <p class="userCount productCount"><?= count($products) ?> Products</p>
                        <table class="products users">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Image</th>
                                    <th>Product Name</th>
                                    <th>Description</th>
                                    <th>Supplier</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Created At</th>
                                    <th>Updated At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $index => $product): ?>
                                    <?php
                                    $stockStmt = $conn->prepare('SELECT quantity FROM stock WHERE product_id = ?');
                                    $stockStmt->execute([(int) $product['id']]);
                                    $qty = (int) ($stockStmt->fetchColumn() ?: 0);
                                    $productImageSrc = product_image_src((string) ($product['img'] ?? ''), (string) $product['product_name']);

                                    $supplierStmt = $conn->prepare(
                                        'SELECT s.supplier_name
                                     FROM product_supplier_map psm
                                         JOIN supplier s ON s.id = psm.supplier_id
                                         WHERE psm.product_id = ?
                                         ORDER BY s.supplier_name ASC'
                                    );
                                    $supplierStmt->execute([(int) $product['id']]);
                                    $suppliers = $supplierStmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

                                    $userStmt = $conn->prepare('SELECT first_name FROM users WHERE id = ?');
                                    $userStmt->execute([(int) $product['created_by']]);
                                    $creator = (string) ($userStmt->fetchColumn() ?: '');
                                    ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td>
                                            <img class="productImages" src="<?= htmlspecialchars($productImageSrc, ENT_QUOTES, 'UTF-8') ?>" width="60" alt="<?= htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8') ?>" onerror="this.onerror=null;this.src='data:image/svg+xml;charset=UTF-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22120%22 height=%22120%22 viewBox=%220 0 120 120%22%3E%3Crect width=%22120%22 height=%22120%22 rx=%2216%22 fill=%22%23eef3f8%22/%3E%3Ctext x=%2260%22 y=%2269%22 text-anchor=%22middle%22 font-size=%2234%22 font-family=%22Arial%2C%20sans-serif%22 fill=%22%237a8ca3%22%3ENA%3C/text%3E%3C/svg%3E';">
                                        </td>
                                        <td class="product_name"><?= htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="description"><?= htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= !empty($suppliers) ? htmlspecialchars(implode(', ', $suppliers), ENT_QUOTES, 'UTF-8') : 'No Supplier' ?></td>
                                        <td><?= $qty ?></td>
                                        <td>
                                            <?php if ($qty === 0): ?>
                                                <span style="color:red;font-weight:bold" title="Product stock status">Out of Stock</span>
                                            <?php elseif ($qty < 20): ?>
                                                <span style="color:orange;font-weight:bold" title="Product stock status">Low Stock</span>
                                            <?php else: ?>
                                                <span style="color:green;font-weight:bold" title="Product stock status">In Stock</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($creator, ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= date('M d,Y @h:i:s A', strtotime((string) $product['created_at'])) ?></td>
                                        <td><?= date('M d,Y @h:i:s A', strtotime((string) $product['updated_at'])) ?></td>
                                        <td class="actionCell">
                                            <?php if ($isAdmin): ?>
                                                <a href="#" class="action-btn editProduct editBtn"
                                                    data-pid="<?= (int) $product['id'] ?>"
                                                    data-name="<?= htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8') ?>"
                                                    data-description="<?= htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8') ?>"
                                                    title="Edit this item">
                                                    <i class="fa fa-pencil"></i> Edit
                                                </a>
                                                <a href="#" class="action-btn deleteProduct deleteBtn"
                                                    data-id="<?= (int) $product['id'] ?>"
                                                    data-table="products"
                                                    data-name="<?= htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8') ?>"
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
    <script>
        window.appConfig = {
            csrfToken: "<?= csrf_token() ?>"
        };
    </script>
    <script src="js/dashboard.js"></script>
    <script src="js/tooltips.js?v=<?= filemtime(__DIR__ . '/js/tooltips.js') ?>"></script>
    <script src="js/script.js"></script>
</body>

</html>