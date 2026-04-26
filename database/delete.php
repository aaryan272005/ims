<?php

require_once __DIR__ . '/../partials/security.php';
require_once __DIR__ . '/connection.php';

require_admin(true);
header('Content-Type: application/json');

require_post_csrf(true);

$id = (int) ($_POST['id'] ?? 0);
$table = (string) ($_POST['table'] ?? '');

if ($id <= 0 || $table === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$allowed_tables = ['products', 'supplier', 'purchase_orders', 'users'];
if (!in_array($table, $allowed_tables, true)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Invalid table']);
    exit();
}

try {
    $conn->beginTransaction();

    if ($table === 'supplier') {
        $stmt = $conn->prepare('DELETE FROM product_supplier_map WHERE supplier_id = ?');
        $stmt->execute([$id]);
    }

    if ($table === 'products') {
        $stmt = $conn->prepare('DELETE FROM product_supplier_map WHERE product_id = ?');
        $stmt->execute([$id]);
    }

    if ($table === 'users' && $id === (int) ($_SESSION['user_id'] ?? 0)) {
        throw new InvalidArgumentException('You cannot delete yourself');
    }

    $stmt = $conn->prepare("DELETE FROM {$table} WHERE id = ?");
    $stmt->execute([$id]);

    $conn->commit();
    echo json_encode(['success' => true]);
    exit();
} catch (Throwable $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => $e instanceof InvalidArgumentException ? $e->getMessage() : 'Unable to delete right now',
    ]);
    exit();
}
