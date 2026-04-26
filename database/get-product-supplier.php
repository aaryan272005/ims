<?php

require_once __DIR__ . '/../partials/security.php';
require_once __DIR__ . '/connection.php';

require_roles(['admin', 'user'], false, '../dashboard.php', 'Sales access is limited to dashboard, products, and POS.');

$product_id = (int) ($_GET['product_id'] ?? 0);

if ($product_id <= 0) {
    exit();
}

$stmt = $conn->prepare(
    "SELECT DISTINCT s.id, s.supplier_name
     FROM supplier s
     JOIN (
        SELECT supplier_id AS mapped_supplier_id
        FROM product_supplier_map
        WHERE product_id = :product_id

        UNION

        SELECT supplier AS mapped_supplier_id
        FROM productsupplier
        WHERE product = :product_id
     ) mapped ON mapped.mapped_supplier_id = s.id
     ORDER BY s.supplier_name ASC"
);

$stmt->execute(['product_id' => $product_id]);
$suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($suppliers as $supplier) {
    ?>
    <div class="row" style="margin-top:25px">
        <div>
            <p class="supplierName"><?= htmlspecialchars((string) $supplier['supplier_name'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>

        <div>
            <input type="hidden" name="supplier_id[]" value="<?= (int) $supplier['id'] ?>">

            <label>Quantity:</label>
            <input type="number" class="appFormInput" name="quantity[]" min="1" placeholder="Enter Quantity" required>
        </div>
    </div>
    <?php
}
