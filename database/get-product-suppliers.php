<?php

require_once __DIR__ . '/../partials/security.php';
require_once __DIR__ . '/connection.php';

require_roles(['admin', 'user'], false, '../dashboard.php', 'Sales access is limited to dashboard, products, and POS.');

$productId = (int) ($_POST['product_id'] ?? 0);

if ($productId <= 0) {
    exit();
}

$stmt = $conn->prepare(
    "SELECT s.id, s.supplier_name
     FROM product_supplier_map psm
     JOIN supplier s ON s.id = psm.supplier_id
     WHERE psm.product_id = ?
     ORDER BY s.supplier_name ASC"
);
$stmt->execute([$productId]);
$suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($suppliers as $supplier) {
    echo "
<div class='supplierRow'>
    <label>" . htmlspecialchars((string) $supplier['supplier_name'], ENT_QUOTES, 'UTF-8') . "</label>
    <input type='hidden' name='supplier_id[]' value='" . (int) $supplier['id'] . "'>
    <input type='number' name='quantity[]' min='1' placeholder='Enter quantity...' required>
</div>
";
}
