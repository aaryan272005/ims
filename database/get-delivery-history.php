<?php

require_once __DIR__ . '/../partials/security.php';
require_once __DIR__ . '/connection.php';

require_roles(['admin', 'user'], false, '../dashboard.php', 'Sales access is limited to dashboard, products, and POS.');

header('Content-Type: application/json');

$order_id = (int) ($_GET['order_id'] ?? 0);

if ($order_id <= 0) {
    echo json_encode([]);
    exit();
}

$stmt = $conn->prepare(
    'SELECT quantity_received, DATE_FORMAT(date_received, "%d-%m-%Y %H:%i:%s") AS date_received
     FROM delivery_history
     WHERE order_id = ?
     ORDER BY date_received DESC'
);
$stmt->execute([$order_id]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
