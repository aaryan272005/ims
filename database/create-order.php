<?php

require_once __DIR__ . '/../partials/security.php';
require_once __DIR__ . '/../partials/validation.php';
require_once __DIR__ . '/connection.php';

require_admin(false, '../dashboard.php');
require_post_csrf(false, '../order-create.php');

try {
    $rows = validate_order_rows_payload(
        $_POST['product_id'] ?? [],
        $_POST['supplier_id'] ?? [],
        $_POST['quantity'] ?? []
    );

    $created_by = (int) ($_SESSION['user_id'] ?? 0);
    $conn->beginTransaction();

    $stmt = $conn->prepare(
        'INSERT INTO purchase_orders (product_id, supplier_id, quantity_order, quantity_received, quantity_remaining, status, created_by, created_at, updated_at)
         VALUES (?, ?, ?, 0, ?, ?, ?, NOW(), NOW())'
    );

    foreach ($rows as $row) {
        $initialStatus = compute_order_status((int) $row['quantity'], 0);
        $stmt->execute([
            $row['product_id'],
            $row['supplier_id'],
            $row['quantity'],
            $row['quantity'],
            $initialStatus,
            $created_by,
        ]);
    }

    $conn->commit();

    $_SESSION['response'] = [
        'success' => true,
        'message' => 'Order created successfully',
    ];
} catch (Throwable $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    $_SESSION['response'] = [
        'success' => false,
        'message' => $e instanceof InvalidArgumentException ? $e->getMessage() : 'Unable to create order right now',
    ];
}

header('Location: ../order-create.php');
exit();
