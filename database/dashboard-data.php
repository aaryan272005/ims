<?php

include('connection.php');

$statusQuery = "
    SELECT
        CASE
            WHEN po.quantity_received <= 0 THEN 'pending'
            WHEN po.quantity_received < po.quantity_order THEN 'incomplete'
            ELSE 'complete'
        END AS stats,
        COUNT(*) AS total
    FROM purchase_orders po
    GROUP BY stats
";

$stmt = $conn->prepare($statusQuery);
$stmt->execute();
$statusData = $stmt->fetchAll(PDO::FETCH_ASSOC);

$supplierQuery = "
    SELECT
        s.supplier_name,
        COUNT(po.id) AS total
    FROM supplier s
    LEFT JOIN purchase_orders po ON po.supplier_id = s.id
    GROUP BY s.id, s.supplier_name
    ORDER BY total DESC, s.supplier_name ASC
";

$stmt = $conn->prepare($supplierQuery);
$stmt->execute();
$supplierData = $stmt->fetchAll(PDO::FETCH_ASSOC);

$deliveryQuery = "SELECT DATE(date_received) as day, SUM(quantity_received) as total FROM delivery_history GROUP BY DATE(date_received) ORDER BY day ASC ";

$stmt = $conn->prepare($deliveryQuery);
$stmt->execute();
$deliveryData = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "status" => $statusData,
    "supplier" => $supplierData,
    "delivery" => $deliveryData
]);
